<?php

namespace App\Http\Controllers;

use App\Models\GoogleSheetsConfig;
use App\Models\SmartImportAutomation;
use App\Models\ImportBatch;
use App\Services\LeadImportService;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LeadImportController extends Controller
{
    protected $importService;
    protected $sheetsService;

    public function __construct(
        LeadImportService $importService,
        GoogleSheetsService $sheetsService
    ) {
        $this->importService = $importService;
        $this->sheetsService = $sheetsService;
    }

    /**
     * Lead Import Dashboard
     */
    public function index()
    {
        $configs = GoogleSheetsConfig::where('created_by', auth()->id())
            ->where('is_active', true)
            ->latest()
            ->get();

        $recentImports = ImportBatch::where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total_imports' => ImportBatch::where('user_id', auth()->id())->count(),
            'total_leads_imported' => ImportBatch::where('user_id', auth()->id())->sum('imported_leads'),
            'pending_imports' => ImportBatch::where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'processing'])->count(),
            'failed_imports' => ImportBatch::where('user_id', auth()->id())
                ->where('status', 'failed')->count(),
        ];

        return view('lead-import.index', compact('configs', 'recentImports', 'stats'));
    }

    /**
     * Get Google Sheets Config
     */
    public function getGoogleSheetsConfig(Request $request)
    {
        $configId = $request->get('config_id');

        if ($configId) {
            $config = GoogleSheetsConfig::where('id', $configId)
                ->where('created_by', auth()->id())
                ->firstOrFail();

            // Remove sensitive data
            $configData = $config->toArray();
            unset($configData['api_key'], $configData['refresh_token'], $configData['service_account_json_path']);

            return response()->json([
                'success' => true,
                'config' => $configData,
            ]);
        }

        // Return all active configs
        $configs = GoogleSheetsConfig::where('created_by', auth()->id())
            ->where('is_active', true)
            ->get()
            ->map(function ($config) {
                $data = $config->toArray();
                unset($data['api_key'], $data['refresh_token'], $data['service_account_json_path']);
                return $data;
            });

        return response()->json([
            'success' => true,
            'configs' => $configs,
        ]);
    }

    /**
     * Save Google Sheets Config
     */
    public function saveGoogleSheetsConfig(Request $request)
    {
        $request->validate([
            'config_id' => 'nullable|exists:google_sheets_config,id',
            'sheet_id' => 'required|string',
            'sheet_name' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'service_account_json_path' => 'nullable|string|max:500',
            'range' => 'required|string|max:50',
            'name_column' => 'required|string|size:1|regex:/^[A-Z]$/',
            'phone_column' => 'required|string|size:1|regex:/^[A-Z]$/',
            'notes_column' => 'nullable|string|size:1|regex:/^[A-Z]$/',
            'status_column' => 'required|string|size:1|regex:/^[A-Z]$/',
            'notes_column_sync' => 'required|string|size:1|regex:/^[A-Z]$/',
            'auto_sync_enabled' => 'boolean',
            'sync_interval_minutes' => 'required|integer|min:1',
            'automation_id' => 'nullable|exists:smart_import_automations,id',
        ]);

        try {
            // Extract sheet ID from URL if needed
            $sheetId = GoogleSheetsConfig::extractSheetId($request->sheet_id);
            if (!$sheetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid sheet ID format. Please provide a valid Google Sheet ID or URL.',
                ], 400);
            }

            $data = $request->only([
                'sheet_name',
                'api_key',
                'service_account_json_path',
                'range',
                'name_column',
                'phone_column',
                'notes_column',
                'status_column',
                'notes_column_sync',
                'auto_sync_enabled',
                'sync_interval_minutes',
                'automation_id',
            ]);

            // Apply defaults if not provided
            $data['notes_column'] = $data['notes_column'] ?? 'C';
            $data['status_column'] = $data['status_column'] ?? 'D';
            $data['notes_column_sync'] = $data['notes_column_sync'] ?? 'E';
            $data['sync_interval_minutes'] = $data['sync_interval_minutes'] ?? 5;
            $data['sync_interval_minutes'] = $data['sync_interval_minutes'] ?? 5;

            $data['sheet_id'] = $sheetId;
            $data['created_by'] = auth()->id();

            if ($request->config_id) {
                $config = GoogleSheetsConfig::where('id', $request->config_id)
                    ->where('created_by', auth()->id())
                    ->firstOrFail();
                $config->update($data);
            } else {
                $config = GoogleSheetsConfig::create($data);
            }

            // Remove sensitive data from response
            $configData = $config->toArray();
            unset($configData['api_key'], $configData['refresh_token'], $configData['service_account_json_path']);

            return response()->json([
                'success' => true,
                'config' => $configData,
                'message' => $request->config_id ? 'Configuration updated successfully.' : 'Configuration created successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error("Error saving Google Sheets config: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get All Google Sheets Configs
     */
    public function getAllGoogleSheetsConfigs()
    {
        $configs = GoogleSheetsConfig::where('created_by', auth()->id())
            ->where('is_active', true)
            ->with(['assignmentRule', 'creator'])
            ->latest()
            ->get()
            ->map(function ($config) {
                $data = $config->toArray();
                unset($data['api_key'], $data['refresh_token'], $data['service_account_json_path']);
                return $data;
            });

        return response()->json([
            'success' => true,
            'configs' => $configs,
        ]);
    }

    /**
     * Delete Google Sheets Config
     */
    public function deleteGoogleSheetsConfig($id)
    {
        try {
            $config = GoogleSheetsConfig::where('id', $id)
                ->where('created_by', auth()->id())
                ->firstOrFail();

            // Soft delete
            $config->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Configuration deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete configuration.',
            ], 500);
        }
    }

    /**
     * Sync Google Sheets
     */
    public function syncGoogleSheets(Request $request)
    {
        $request->validate([
            'config_id' => 'required|exists:google_sheets_config,id',
        ]);

        try {
            $config = GoogleSheetsConfig::where('id', $request->config_id)
                ->where('created_by', auth()->id())
                ->where('is_active', true)
                ->firstOrFail();

            $result = $this->sheetsService->syncGoogleSheets($config->id);

            return response()->json([
                'success' => true,
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'total_rows' => $result['imported'] + $result['skipped'],
                'errors' => $result['errors'],
                'is_complete' => empty($result['errors']),
                'last_synced_row' => $result['last_synced_row'],
                'message' => "Successfully imported {$result['imported']} leads. {$result['skipped']} skipped.",
            ]);

        } catch (\Exception $e) {
            Log::error("Google Sheets sync error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show CSV Import Form
     */
    public function showCsvForm()
    {
        $automations = SmartImportAutomation::where('created_by', auth()->id())
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('lead-import.csv', compact('automations'));
    }

    /**
     * Import CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'automation_id' => 'required|exists:smart_import_automations,id',
        ]);

        try {
            $file = $request->file('csv_file');
            
            // Parse CSV
            $leads = $this->importService->parseCsvFile($file);

            if (empty($leads)) {
                return back()->withErrors(['csv_file' => 'No valid leads found in CSV file.']);
            }

            // Store file
            $fileName = 'imports/' . time() . '_' . $file->getClientOriginalName();
            Storage::putFileAs('public', $file, $fileName);

            // Get automation
            $automation = SmartImportAutomation::findOrFail($request->automation_id);
            
            // Import leads using automation config
            $batch = $this->importService->importFromCsvWithAutomation(
                $leads,
                $request->user()->id,
                $automation
            );

            // Update batch with file name
            $batch->update(['file_name' => $fileName]);

            return redirect()
                ->route('lead-import.index')
                ->with('success', "Successfully imported {$batch->imported_leads} leads. {$batch->failed_leads} failed.");

        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => $e->getMessage()]);
        }
    }

    /**
     * Preview CSV
     */
    public function previewCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('csv_file');
            $leads = $this->importService->parseCsvFile($file);

            return response()->json([
                'success' => true,
                'total' => count($leads),
                'preview' => array_slice($leads, 0, 10), // First 10 rows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Import History
     */
    public function history()
    {
        $imports = ImportBatch::where('user_id', auth()->id())
            ->with(['assignmentRule', 'user'])
            ->latest()
            ->paginate(20);

        return view('lead-import.history', compact('imports'));
    }
}
