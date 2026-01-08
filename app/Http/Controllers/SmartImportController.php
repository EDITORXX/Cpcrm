<?php

namespace App\Http\Controllers;

use App\Models\SmartImportAutomation;
use App\Models\SmartImportExecution;
use App\Models\User;
use App\Services\SmartImportService;
use App\Services\SmartAssignmentService;
use App\Services\UserAvailabilityService;
use App\Services\SlaTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmartImportController extends Controller
{
    protected $importService;
    protected $assignmentService;
    protected $availabilityService;
    protected $slaService;

    public function __construct(
        SmartImportService $importService,
        SmartAssignmentService $assignmentService,
        UserAvailabilityService $availabilityService,
        SlaTrackingService $slaService
    ) {
        $this->importService = $importService;
        $this->assignmentService = $assignmentService;
        $this->availabilityService = $availabilityService;
        $this->slaService = $slaService;
    }

    /**
     * Dashboard - List all automations
     */
    public function index()
    {
        $automations = SmartImportAutomation::where('created_by', auth()->id())
            ->withCount('executions')
            ->latest()
            ->get();

        $stats = [
            'total_automations' => $automations->count(),
            'active_automations' => $automations->where('status', 'active')->count(),
            'total_executions' => SmartImportExecution::whereHas('automation', function($q) {
                $q->where('created_by', auth()->id());
            })->count(),
            'total_leads_imported' => SmartImportExecution::whereHas('automation', function($q) {
                $q->where('created_by', auth()->id());
            })->sum('imported_leads'),
        ];

        return view('smart-import.index', compact('automations', 'stats'));
    }

    /**
     * Show create wizard
     */
    public function create()
    {
        return view('smart-import.create');
    }

    /**
     * Show simplified create wizard
     */
    public function createSimple()
    {
        $users = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', ['sales_manager', 'sales_executive', 'telecaller']);
            })
            ->with('role')
            ->get();

        return view('crm.automation.create-simple', compact('users'));
    }

    /**
     * Store simplified automation
     */
    public function storeSimple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lead_source' => 'required|in:google_sheet,csv_excel',
            'distribution_mode' => 'required|in:percentage,random,one_sheet_per_user',
            'auto_create_call_task' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $distributionConfig = [];
            $assignmentMode = 'percentage';

            // Build distribution config based on mode
            if ($request->distribution_mode === 'percentage') {
                $percentageValidator = Validator::make($request->all(), [
                    'percentage_user_id' => 'required|exists:users,id',
                ]);
                
                if ($percentageValidator->fails()) {
                    return back()->withErrors($percentageValidator)->withInput();
                }

                $distributionConfig = [
                    [
                        'user_id' => (int)$request->percentage_user_id,
                        'percentage' => 100,
                        'is_active' => true,
                    ]
                ];
                $assignmentMode = 'percentage';
            } elseif ($request->distribution_mode === 'random') {
                $randomValidator = Validator::make($request->all(), [
                    'random_users' => 'required|array|min:1',
                    'random_users.*' => 'exists:users,id',
                ]);

                if ($randomValidator->fails()) {
                    return back()->withErrors($randomValidator)->withInput();
                }

                $userCount = count($request->random_users);
                $percentagePerUser = round(100 / $userCount, 2);

                foreach ($request->random_users as $userId) {
                    $distributionConfig[] = [
                        'user_id' => (int)$userId,
                        'percentage' => $percentagePerUser,
                        'is_active' => true,
                    ];
                }
                // Adjust last user's percentage to ensure total is 100
                if (count($distributionConfig) > 0) {
                    $total = array_sum(array_column($distributionConfig, 'percentage'));
                    $diff = 100 - $total;
                    $distributionConfig[count($distributionConfig) - 1]['percentage'] += $diff;
                }
                $assignmentMode = 'round_robin';
            } elseif ($request->distribution_mode === 'one_sheet_per_user') {
                // This will be configured later when Google Sheets are connected
                $assignmentMode = 'percentage';
                // For now, we'll need to handle this separately when sheets are connected
            }

            // Handle file upload for CSV/Excel
            $filePath = null;
            $fileName = null;
            $fileType = null;
            $columnMapping = null;
            $totalRows = 0;

            if ($request->lead_source === 'csv_excel') {
                $file = $request->file('file');
                if ($file) {
                    $filePath = $this->importService->storeFile($file);
                    $fileName = $file->getClientOriginalName();
                    $fileType = strtolower($file->getClientOriginalExtension());
                    
                    // Auto-detect column mapping
                    $rows = $this->importService->parseFile($file);
                    $headerRow = $rows[0] ?? [];
                    $columnMapping = $this->importService->autoDetectMapping($headerRow);
                    $totalRows = count($rows) - 1; // Exclude header
                }
            }

            $automation = SmartImportAutomation::create([
                'name' => $request->name,
                'description' => $request->description,
                'lead_source' => $request->lead_source,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'total_rows' => $totalRows,
                'column_mapping' => $columnMapping,
                'duplicate_handling' => 'skip',
                'assignment_mode' => $assignmentMode,
                'distribution_mode' => $request->distribution_mode,
                'distribution_config' => $distributionConfig,
                'conditions' => [],
                'auto_create_call_task' => $request->has('auto_create_call_task') ? (bool)$request->auto_create_call_task : true,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // For Google Sheets, create GoogleSheetsConfig
            if ($request->lead_source === 'google_sheet') {
                $validator->addRules([
                    'sheet_id' => 'required|string',
                ]);
                
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                // Extract sheet ID from URL if needed
                $sheetId = \App\Models\GoogleSheetsConfig::extractSheetId($request->sheet_id);
                if (!$sheetId) {
                    return back()
                        ->withErrors(['sheet_id' => 'Invalid Google Sheet URL or ID. Please provide a valid Google Sheet ID or URL.'])
                        ->withInput();
                }

                // Create Google Sheets Config
                \App\Models\GoogleSheetsConfig::create([
                    'sheet_id' => $sheetId,
                    'sheet_name' => $request->sheet_name ?? 'Sheet1',
                    'service_account_json_path' => 'google-service-account.json', // Default path in storage/app
                    'range' => 'A:Z',
                    'name_column' => 'A',
                    'phone_column' => 'B',
                    'status_column' => 'C',
                    'notes_column_sync' => 'D',
                    'auto_sync_enabled' => false, // Manual sync for now
                    'sync_interval_minutes' => 15,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('smart-import.index')
                ->with('success', "Automation '{$automation->name}' created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store simple automation error: " . $e->getMessage());
            return back()
                ->withErrors(['error' => 'Failed to create automation: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Handle file upload (Step 1)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            'duplicate_handling' => 'required|in:skip,merge,update',
        ]);

        try {
            $file = $request->file('file');
            
            // Validate file
            $errors = $this->importService->validateFile($file);
            if (!empty($errors)) {
                return response()->json(['success' => false, 'errors' => $errors], 422);
            }

            // Get preview
            $preview = $this->importService->getPreview($file, 10);
            
            // Detect duplicates
            $rows = $this->importService->parseFile($file);
            $headerRow = $rows[0] ?? [];
            $dataRows = array_slice($rows, 1);
            
            // Auto-detect mapping
            $autoMapping = $this->importService->autoDetectMapping($headerRow);
            
            // Map columns and detect duplicates
            $mappedData = $this->importService->mapColumns($rows, $autoMapping);
            $duplicates = $this->importService->detectDuplicates($mappedData);

            // Store file temporarily
            $filePath = $this->importService->storeFile($file);

            return response()->json([
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => strtolower($file->getClientOriginalExtension()),
                'total_rows' => count($dataRows),
                'preview' => $preview,
                'header_row' => $headerRow,
                'auto_mapping' => $autoMapping,
                'duplicates_count' => count($duplicates),
                'duplicates' => array_slice($duplicates, 0, 10), // First 10 duplicates
            ]);

        } catch (\Exception $e) {
            Log::error("File upload error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save column mapping (Step 2)
     */
    public function mapColumns(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'column_mapping' => 'required|array',
        ]);

        // Store mapping in session for wizard flow
        session(['smart_import.column_mapping' => $request->column_mapping]);
        session(['smart_import.file_path' => $request->file_path]);
        session(['smart_import.file_name' => $request->file_name]);
        session(['smart_import.file_type' => $request->file_type]);
        session(['smart_import.total_rows' => $request->total_rows]);
        session(['smart_import.duplicate_handling' => $request->duplicate_handling ?? 'skip']);

        return response()->json(['success' => true, 'message' => 'Column mapping saved']);
    }

    /**
     * Save distribution rules (Step 3)
     */
    public function saveDistribution(Request $request)
    {
        $request->validate([
            'assignment_mode' => 'required|in:percentage,fixed_count,round_robin',
            'distribution_config' => 'required|array',
        ]);

        // Validate distribution config
        $errors = $this->assignmentService->validateDistributionConfig(
            $request->distribution_config,
            $request->assignment_mode
        );

        if (!empty($errors)) {
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }

        session(['smart_import.assignment_mode' => $request->assignment_mode]);
        session(['smart_import.distribution_config' => $request->distribution_config]);

        return response()->json(['success' => true, 'message' => 'Distribution rules saved']);
    }

    /**
     * Save conditions (Step 4)
     */
    public function saveConditions(Request $request)
    {
        $request->validate([
            'conditions' => 'nullable|array',
            'fallback_user_id' => 'nullable|exists:users,id',
        ]);

        session(['smart_import.conditions' => $request->conditions ?? []]);
        session(['smart_import.fallback_user_id' => $request->fallback_user_id]);
        session(['smart_import.sla_minutes' => $request->sla_minutes]);
        session(['smart_import.escalation_user_id' => $request->escalation_user_id]);

        return response()->json(['success' => true, 'message' => 'Conditions saved']);
    }

    /**
     * Preview/test mode (Step 5)
     */
    public function preview(Request $request)
    {
        try {
            // Get session data
            $filePath = session('smart_import.file_path');
            $columnMapping = session('smart_import.column_mapping');
            $assignmentMode = session('smart_import.assignment_mode');
            $distributionConfig = session('smart_import.distribution_config');
            $conditions = session('smart_import.conditions', []);
            $fallbackUserId = session('smart_import.fallback_user_id');

            if (!$filePath || !$columnMapping) {
                return response()->json(['success' => false, 'message' => 'Missing required data'], 422);
            }

            // Load file and map columns
            $fullPath = Storage::disk('public')->path($filePath);
            $file = new \Illuminate\Http\UploadedFile(
                $fullPath,
                basename($filePath),
                mime_content_type($fullPath),
                null,
                true
            );
            $rows = $this->importService->parseFile($file);
            $mappedData = $this->importService->mapColumns($rows, $columnMapping);

            // Simulate assignment for first 50 rows
            $previewData = array_slice($mappedData, 0, 50);
            $assignmentResults = [];

            foreach ($previewData as $index => $rowData) {
                // Create temporary lead for testing
                $tempLead = new \App\Models\Lead($rowData);
                
                $automationConfig = [
                    'assignment_mode' => $assignmentMode,
                    'distribution_config' => $distributionConfig,
                    'conditions' => $conditions,
                    'fallback_user_id' => $fallbackUserId,
                ];

                // This is a dry run, so we won't actually assign
                // Just simulate the logic
                $assignedUserId = $this->assignmentService->assignLead($tempLead, $automationConfig, auth()->id());
                
                $assignmentResults[] = [
                    'row' => $index + 1,
                    'data' => $rowData,
                    'assigned_to' => $assignedUserId,
                    'user_name' => $assignedUserId ? User::find($assignedUserId)->name : 'Unassigned',
                ];
            }

            // Calculate distribution summary
            $distributionSummary = [];
            foreach ($assignmentResults as $result) {
                $userId = $result['assigned_to'];
                if ($userId) {
                    if (!isset($distributionSummary[$userId])) {
                        $distributionSummary[$userId] = [
                            'user_id' => $userId,
                            'user_name' => $result['user_name'],
                            'count' => 0,
                        ];
                    }
                    $distributionSummary[$userId]['count']++;
                }
            }

            return response()->json([
                'success' => true,
                'preview_results' => $assignmentResults,
                'distribution_summary' => array_values($distributionSummary),
                'total_previewed' => count($previewData),
            ]);

        } catch (\Exception $e) {
            Log::error("Preview error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store automation (Step 6)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $automation = SmartImportAutomation::create([
                'name' => $request->name,
                'description' => $request->description,
                'file_path' => session('smart_import.file_path'),
                'file_name' => session('smart_import.file_name'),
                'file_type' => session('smart_import.file_type'),
                'total_rows' => session('smart_import.total_rows', 0),
                'column_mapping' => session('smart_import.column_mapping'),
                'duplicate_handling' => session('smart_import.duplicate_handling', 'skip'),
                'assignment_mode' => session('smart_import.assignment_mode'),
                'distribution_config' => session('smart_import.distribution_config'),
                'conditions' => session('smart_import.conditions', []),
                'fallback_user_id' => session('smart_import.fallback_user_id'),
                'sla_minutes' => session('smart_import.sla_minutes'),
                'escalation_user_id' => session('smart_import.escalation_user_id'),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Clear session
            session()->forget([
                'smart_import.file_path',
                'smart_import.file_name',
                'smart_import.file_type',
                'smart_import.total_rows',
                'smart_import.column_mapping',
                'smart_import.duplicate_handling',
                'smart_import.assignment_mode',
                'smart_import.distribution_config',
                'smart_import.conditions',
                'smart_import.fallback_user_id',
                'smart_import.sla_minutes',
                'smart_import.escalation_user_id',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Automation created successfully',
                'automation_id' => $automation->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store automation error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Execute automation
     */
    public function execute($id)
    {
        $automation = SmartImportAutomation::findOrFail($id);

        if ($automation->status !== 'draft' && $automation->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Automation must be in draft or active status to execute',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create execution record
            $execution = SmartImportExecution::create([
                'automation_id' => $automation->id,
                'status' => 'pending',
                'total_leads' => $automation->total_rows,
                'executed_by' => auth()->id(),
            ]);

            // Dispatch background job
            \App\Jobs\ProcessSmartImportExecution::dispatch($execution);

            // Update automation status
            if ($automation->status === 'draft') {
                $automation->update(['status' => 'active']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Execution started successfully',
                'execution_id' => $execution->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Execute automation error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start execution: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $automation = SmartImportAutomation::findOrFail($id);
        return view('smart-import.edit', compact('automation'));
    }

    /**
     * Update automation
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return response()->json(['success' => true]);
    }

    /**
     * Show execution history
     */
    public function history()
    {
        $executions = SmartImportExecution::whereHas('automation', function($q) {
            $q->where('created_by', auth()->id());
        })->with('automation')->latest()->paginate(20);

        return view('smart-import.history', compact('executions'));
    }

    /**
     * Show all assignments (for admin override)
     */
    public function assignments(Request $request)
    {
        $query = SmartImportLeadAssignment::with(['lead', 'assignedTo', 'execution.automation']);

        // Filters
        if ($request->has('execution_id')) {
            $query->where('execution_id', $request->execution_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('is_queued')) {
            $query->where('is_queued', $request->is_queued);
        }

        $assignments = $query->latest()->paginate(20);
        $users = User::where('is_active', true)->get();

        return view('smart-import.assignments', compact('assignments', 'users'));
    }

    /**
     * Override assignment (admin only)
     */
    public function overrideAssignment(Request $request, $id)
    {
        $request->validate([
            'new_user_id' => 'required|exists:users,id',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $assignment = SmartImportLeadAssignment::findOrFail($id);
        $lead = $assignment->lead;

        try {
            DB::beginTransaction();

            // Deactivate existing assignments
            $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

            // Create new assignment
            \App\Models\LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_to' => $request->new_user_id,
                'assigned_by' => auth()->id(),
                'assignment_type' => 'primary',
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            // Update smart import assignment record
            $assignment->update([
                'override_user_id' => $request->new_user_id,
                'override_reason' => $request->reason,
                'override_at' => now(),
                'is_queued' => false,
            ]);

            // Log audit
            \App\Models\AutomationAuditLog::create([
                'automation_id' => $assignment->execution->automation_id,
                'action' => 'override',
                'user_id' => auth()->id(),
                'changes' => [
                    'assignment_id' => $assignment->id,
                    'old_user_id' => $assignment->assigned_to,
                    'new_user_id' => $request->new_user_id,
                    'reason' => $request->reason,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment overridden successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Override assignment error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to override assignment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
