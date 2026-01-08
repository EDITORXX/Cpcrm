<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\GoogleSheetsConfig;
use App\Models\SheetAssignmentConfig;
use App\Models\SheetPercentageConfig;
use App\Models\User;
use App\Models\Role;
use App\Events\LeadAssigned;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadAssignmentService
{
    protected $limitService;
    protected $statusService;
    protected static $roundRobinCounters = [];

    public function __construct(
        TelecallerLimitService $limitService,
        TelecallerStatusService $statusService
    ) {
        $this->limitService = $limitService;
        $this->statusService = $statusService;
    }

    /**
     * Main assignment method with priority logic
     */
    public function assignLead(Lead $lead, ?int $sheetConfigId = null, int $assignedBy, ?string $method = null): ?int
    {
        DB::beginTransaction();
        try {
            $assignedUserId = null;
            $assignmentMethod = null;

            // Priority 1: Linked Telecaller (if sheet has linked_telecaller_id)
            if ($sheetConfigId) {
                $sheetConfig = GoogleSheetsConfig::find($sheetConfigId);
                if ($sheetConfig && $sheetConfig->linked_telecaller_id) {
                    $assignedUserId = $this->tryAssignToLinkedTelecaller($lead, $sheetConfig, $assignedBy);
                    if ($assignedUserId) {
                        $assignmentMethod = 'linked_telecaller';
                    }
                }
            }

            // Priority 2: Assigned User (if lead already has assigned_to)
            if (!$assignedUserId && $lead->activeAssignments()->exists()) {
                $existingAssignment = $lead->activeAssignments()->first();
                $assignedUserId = $existingAssignment->assigned_to;
                $assignmentMethod = 'existing_assignment';
            }

            // Priority 3: Auto-Assignment Config (if sheet has config)
            if (!$assignedUserId && $sheetConfigId) {
                $sheetAssignmentConfig = SheetAssignmentConfig::where('google_sheets_config_id', $sheetConfigId)
                    ->where('auto_assign_enabled', true)
                    ->first();

                if ($sheetAssignmentConfig) {
                    $assignedUserId = $this->assignByConfig($lead, $sheetAssignmentConfig, $assignedBy);
                    if ($assignedUserId) {
                        $assignmentMethod = $sheetAssignmentConfig->assignment_method;
                    }
                }
            }

            // Priority 4: Manual (if method specified or telecaller_id provided)
            if (!$assignedUserId && ($method === 'manual' || request()->has('telecaller_id'))) {
                $telecallerId = request()->input('telecaller_id');
                if ($telecallerId) {
                    $assignedUserId = $this->assignManually($lead, $telecallerId, $assignedBy);
                    if ($assignedUserId) {
                        $assignmentMethod = 'manual';
                    }
                }
            }

            if ($assignedUserId && $assignmentMethod) {
                // Create assignment record
                $assignment = $this->createAssignmentRecord($lead, $assignedUserId, $assignedBy, $assignmentMethod, $sheetConfigId, $sheetAssignmentConfig->id ?? null);
                
                // Increment daily counts
                $this->limitService->incrementAssignedCount($assignedUserId, $sheetConfigId, $sheetAssignmentConfig->id ?? null);

                // Fire LeadAssigned event - listener will auto-create calling task for telecaller
                if (!$lead->is_blocked) {
                    event(new LeadAssigned($lead, $assignedUserId, $assignedBy));
                }

                DB::commit();
                return $assignedUserId;
            }

            DB::rollBack();
            return null;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lead assignment error for lead {$lead->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Try to assign to linked telecaller
     */
    protected function tryAssignToLinkedTelecaller(Lead $lead, GoogleSheetsConfig $sheetConfig, int $assignedBy): ?int
    {
        $telecallerId = $sheetConfig->linked_telecaller_id;

        // Check if telecaller can receive assignment
        $canReceive = $this->statusService->canReceiveAssignment($telecallerId);
        if (!$canReceive['can_receive']) {
            return null;
        }

        // Check daily limits
        $limitCheck = $this->limitService->checkDailyLimits($telecallerId, $sheetConfig->id);
        if (!$limitCheck['is_allowed']) {
            return null;
        }

        return $telecallerId;
    }

    /**
     * Assign by sheet assignment config
     */
    protected function assignByConfig(Lead $lead, SheetAssignmentConfig $config, int $assignedBy): ?int
    {
        switch ($config->assignment_method) {
            case 'round_robin':
                return $this->assignByRoundRobin($lead, $config, $assignedBy);
            case 'first_available':
                return $this->assignByFirstAvailable($lead, $config, $assignedBy);
            case 'percentage':
                return $this->assignByPercentage($lead, $config, $assignedBy);
            default:
                return null;
        }
    }

    /**
     * Manual assignment
     */
    public function assignManually(Lead $lead, int $telecallerId, int $assignedBy): ?int
    {
        // Check if telecaller can receive assignment
        $canReceive = $this->statusService->canReceiveAssignment($telecallerId);
        if (!$canReceive['can_receive']) {
            return null;
        }

        // Check daily limits
        $limitCheck = $this->limitService->checkDailyLimits($telecallerId);
        if (!$limitCheck['is_allowed']) {
            return null;
        }

        return $telecallerId;
    }

    /**
     * Round Robin assignment
     */
    protected function assignByRoundRobin(Lead $lead, SheetAssignmentConfig $config, int $assignedBy): ?int
    {
        $telecallers = $this->getAvailableTelecallersForConfig($config);
        
        if ($telecallers->isEmpty()) {
            return null;
        }

        $telecallerIds = $telecallers->pluck('id')->toArray();
        $configKey = $config->id;

        // Initialize counter if not exists
        if (!isset(self::$roundRobinCounters[$configKey])) {
            self::$roundRobinCounters[$configKey] = 0;
        }

        // Get next telecaller in rotation
        $index = self::$roundRobinCounters[$configKey] % count($telecallerIds);
        $telecallerId = $telecallerIds[$index];

        // Check if this telecaller can receive assignment
        $canReceive = $this->statusService->canReceiveAssignment($telecallerId);
        $limitCheck = $this->limitService->checkDailyLimits($telecallerId, $config->google_sheets_config_id, $config->id);

        if ($canReceive['can_receive'] && $limitCheck['is_allowed']) {
            self::$roundRobinCounters[$configKey]++;
            return $telecallerId;
        }

        // Try next telecaller
        for ($i = 1; $i < count($telecallerIds); $i++) {
            $nextIndex = ($index + $i) % count($telecallerIds);
            $nextTelecallerId = $telecallerIds[$nextIndex];

            $canReceive = $this->statusService->canReceiveAssignment($nextTelecallerId);
            $limitCheck = $this->limitService->checkDailyLimits($nextTelecallerId, $config->google_sheets_config_id, $config->id);

            if ($canReceive['can_receive'] && $limitCheck['is_allowed']) {
                self::$roundRobinCounters[$configKey] = $nextIndex + 1;
                return $nextTelecallerId;
            }
        }

        return null;
    }

    /**
     * First Available assignment (minimum pending calls)
     */
    protected function assignByFirstAvailable(Lead $lead, SheetAssignmentConfig $config, int $assignedBy): ?int
    {
        $telecallers = $this->getAvailableTelecallersForConfig($config);
        
        if ($telecallers->isEmpty()) {
            return null;
        }

        $bestTelecaller = null;
        $minPending = PHP_INT_MAX;

        foreach ($telecallers as $telecaller) {
            $canReceive = $this->statusService->canReceiveAssignment($telecaller->id);
            $limitCheck = $this->limitService->checkDailyLimits($telecaller->id, $config->google_sheets_config_id, $config->id);

            if ($canReceive['can_receive'] && $limitCheck['is_allowed']) {
                $pendingCount = $canReceive['pending_count'];
                if ($pendingCount < $minPending) {
                    $minPending = $pendingCount;
                    $bestTelecaller = $telecaller->id;
                }
            }
        }

        return $bestTelecaller;
    }

    /**
     * Percentage-based assignment
     */
    protected function assignByPercentage(Lead $lead, SheetAssignmentConfig $config, int $assignedBy): ?int
    {
        $percentageConfigs = SheetPercentageConfig::where('sheet_assignment_config_id', $config->id)
            ->with('user')
            ->get()
            ->filter(function ($pc) {
                return $pc->user && $pc->user->is_active;
            });

        if ($percentageConfigs->isEmpty()) {
            return null;
        }

        // Build weighted array
        $weightedArray = [];
        foreach ($percentageConfigs as $pc) {
            $canReceive = $this->statusService->canReceiveAssignment($pc->user_id);
            $limitCheck = $this->limitService->checkDailyLimits($pc->user_id, $config->google_sheets_config_id, $config->id);

            if ($canReceive['can_receive'] && $limitCheck['is_allowed']) {
                // Add user ID multiple times based on percentage
                $weight = (int) ($pc->percentage * 100);
                for ($i = 0; $i < $weight; $i++) {
                    $weightedArray[] = $pc->user_id;
                }
            }
        }

        if (empty($weightedArray)) {
            return null;
        }

        // Random selection from weighted array
        return $weightedArray[array_rand($weightedArray)];
    }

    /**
     * Get available telecallers for config
     */
    protected function getAvailableTelecallersForConfig(SheetAssignmentConfig $config): \Illuminate\Support\Collection
    {
        $telecallers = $this->statusService->getAvailableTelecallers();

        // If linked telecaller is set, prioritize them
        if ($config->linked_telecaller_id) {
            $linked = $telecallers->firstWhere('id', $config->linked_telecaller_id);
            if ($linked) {
                return collect([$linked]);
            }
        }

        return $telecallers;
    }

    /**
     * Create assignment record
     */
    protected function createAssignmentRecord(Lead $lead, int $assignedTo, int $assignedBy, string $method, ?int $sheetConfigId = null, ?int $sheetAssignmentConfigId = null): LeadAssignment
    {
        // Deactivate existing assignments
        $lead->assignments()->update([
            'is_active' => false,
            'unassigned_at' => now(),
        ]);

        // Create new assignment
        return LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'assignment_type' => 'primary',
            'assignment_method' => $method,
            'assigned_at' => now(),
            'is_active' => true,
            'sheet_config_id' => $sheetConfigId,
            'sheet_assignment_config_id' => $sheetAssignmentConfigId,
        ]);
    }

    /**
     * Bulk assign leads
     */
    public function bulkAssignLeads(array $leadIds, int $telecallerId, int $assignedBy): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($leadIds as $leadId) {
            $lead = Lead::find($leadId);
            if (!$lead) {
                $results['failed']++;
                $results['errors'][] = "Lead {$leadId} not found";
                continue;
            }

            $assigned = $this->assignManually($lead, $telecallerId, $assignedBy);
            if ($assigned) {
                $this->createAssignmentRecord($lead, $assigned, $assignedBy, 'manual');
                $this->limitService->incrementAssignedCount($assigned);
                
                // Fire LeadAssigned event - listener will auto-create calling task for telecaller
                if (!$lead->is_blocked) {
                    event(new LeadAssigned($lead, $assigned, $assignedBy));
                }
                
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to assign lead {$leadId}";
            }
        }

        return $results;
    }

    /**
     * Auto-assign unassigned leads (called by scheduled job)
     */
    public function autoAssignUnassignedLeads(): array
    {
        $results = [
            'assigned' => 0,
            'failed' => 0,
        ];

        // Get all sheets with auto-assign enabled
        $configs = SheetAssignmentConfig::where('auto_assign_enabled', true)
            ->with('googleSheetsConfig')
            ->get();

        foreach ($configs as $config) {
            // Get unassigned leads from this sheet
            $unassignedLeads = Lead::whereDoesntHave('activeAssignments')
                ->whereHas('assignments', function ($q) use ($config) {
                    $q->where('sheet_config_id', $config->google_sheets_config_id);
                })
                ->limit(100) // Process in batches
                ->get();

            foreach ($unassignedLeads as $lead) {
                $assigned = $this->assignByConfig($lead, $config, 1); // System user
                if ($assigned) {
                    $this->createAssignmentRecord($lead, $assigned, 1, $config->assignment_method, $config->google_sheets_config_id, $config->id);
                    $this->limitService->incrementAssignedCount($assigned, $config->google_sheets_config_id, $config->id);
                    
                    // Fire LeadAssigned event - listener will auto-create calling task for telecaller
                    if (!$lead->is_blocked) {
                        event(new LeadAssigned($lead, $assigned, 1));
                    }
                    
                    $results['assigned']++;
                } else {
                    $results['failed']++;
                }
            }
        }

        return $results;
    }
}

