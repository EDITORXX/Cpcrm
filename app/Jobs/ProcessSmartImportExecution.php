<?php

namespace App\Jobs;

use App\Models\SmartImportExecution;
use App\Models\SmartImportAutomation;
use App\Models\Lead;
use App\Models\SmartImportLeadAssignment;
use App\Events\LeadAssigned;
use App\Services\SmartImportService;
use App\Services\SmartAssignmentService;
use App\Services\SlaTrackingService;
use App\Services\DuplicateDetectionService;
use App\Services\TaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSmartImportExecution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $execution;

    public function __construct(SmartImportExecution $execution)
    {
        $this->execution = $execution;
    }

    public function handle(
        SmartImportService $importService,
        SmartAssignmentService $assignmentService,
        SlaTrackingService $slaService,
        DuplicateDetectionService $duplicateService,
        TaskService $taskService
    ): void {
        $execution = $this->execution;
        $automation = $execution->automation;

        try {
            $execution->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Load file
            $filePath = Storage::disk('public')->path($automation->file_path);
            $file = new \Illuminate\Http\UploadedFile(
                $filePath,
                basename($automation->file_path),
                mime_content_type($filePath),
                null,
                true
            );

            // Parse file
            $rows = $importService->parseFile($file);
            $mappedData = $importService->mapColumns($rows, $automation->column_mapping);

            $imported = 0;
            $skipped = 0;
            $failed = 0;
            $duplicate = 0;
            $queued = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($mappedData as $index => $rowData) {
                try {
                    // Validate required fields
                    if (empty($rowData['name']) || empty($rowData['phone'])) {
                        $skipped++;
                        $errors[] = "Row " . ($index + 1) . ": Missing name or phone";
                        continue;
                    }

                    // Check for duplicates
                    $phone = $duplicateService->sanitizePhone($rowData['phone']);
                    $isDuplicate = $duplicateService->isDuplicate($phone);

                    if ($isDuplicate) {
                        $existingLead = Lead::where('phone', $phone)->first();
                        
                        if ($automation->duplicate_handling === 'skip') {
                            $duplicate++;
                            continue;
                        } elseif ($automation->duplicate_handling === 'merge' || $automation->duplicate_handling === 'update') {
                            $lead = $importService->handleDuplicate($rowData, $automation->duplicate_handling, $existingLead);
                            if (!$lead) {
                                $duplicate++;
                                continue;
                            }
                        } else {
                            $duplicate++;
                            continue;
                        }
                    } else {
                        // Create new lead
                        $lead = Lead::create([
                            'name' => $rowData['name'],
                            'phone' => $phone,
                            'email' => $rowData['email'] ?? null,
                            'address' => $rowData['address'] ?? null,
                            'city' => $rowData['city'] ?? null,
                            'state' => $rowData['state'] ?? null,
                            'pincode' => $rowData['pincode'] ?? null,
                            'source' => $rowData['source'] ?? 'other',
                            'status' => 'new',
                            'created_by' => $automation->created_by,
                            'preferred_location' => $rowData['preferred_location'] ?? null,
                            'budget' => $rowData['budget'] ?? null,
                            'notes' => $rowData['notes'] ?? null,
                        ]);
                    }

                    // Assign lead
                    $automationConfig = [
                        'assignment_mode' => $automation->assignment_mode,
                        'distribution_config' => $automation->distribution_config,
                        'conditions' => $automation->conditions,
                        'fallback_user_id' => $automation->fallback_user_id,
                    ];

                    $assignedUserId = $assignmentService->assignLead($lead, $automationConfig, $automation->created_by);

                    // Create assignment record
                    $assignment = SmartImportLeadAssignment::create([
                        'execution_id' => $execution->id,
                        'lead_id' => $lead->id,
                        'assigned_to' => $assignedUserId,
                        'assigned_by' => $automation->created_by,
                        'rule_applied' => 'Automation rule',
                        'priority_level' => 0,
                        'assignment_method' => $automation->assignment_mode,
                        'is_queued' => !$assignedUserId,
                        'queued_reason' => !$assignedUserId ? 'User not available or limit reached' : null,
                        'sla_started_at' => $automation->sla_minutes ? now() : null,
                    ]);

                    // Start SLA tracking if configured
                    if ($assignedUserId && $automation->sla_minutes) {
                        $slaService->startSlaTracking($assignment->id, $automation->sla_minutes);
                    }

                    // Fire LeadAssigned event (this will create CrmAssignment and Task for telecallers via listener)
                    // Note: SmartAssignmentService also fires this event, but we fire it here too to ensure it's fired
                    if ($assignedUserId) {
                        event(new LeadAssigned($lead, $assignedUserId, $automation->created_by));
                    }

                    // Create phone call task if auto_create_call_task is enabled
                    // Note: This is a fallback if event listener doesn't create task
                    // The listener should handle it, but keeping this for backward compatibility
                    if ($assignedUserId && ($automation->auto_create_call_task ?? true)) {
                        try {
                            // Check if task already exists (created by listener)
                            $existingTask = \App\Models\Task::where('lead_id', $lead->id)
                                ->where('assigned_to', $assignedUserId)
                                ->where('type', 'phone_call')
                                ->where('status', 'pending')
                                ->first();
                            
                            if (!$existingTask) {
                                $taskService->createPhoneCallTask($lead, $assignedUserId, $automation->created_by);
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to create phone call task for lead {$lead->id}: " . $e->getMessage());
                        }
                    }

                    if ($assignedUserId) {
                        $imported++;
                    } else {
                        $queued++;
                    }

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    Log::error("Lead import error at row " . ($index + 1) . ": " . $e->getMessage());
                }
            }

            // Update execution
            $execution->update([
                'status' => 'completed',
                'imported_leads' => $imported,
                'skipped_leads' => $skipped,
                'failed_leads' => $failed,
                'duplicate_leads' => $duplicate,
                'queued_leads' => $queued,
                'execution_log' => [
                    'errors' => array_slice($errors, 0, 100), // Limit to first 100 errors
                ],
                'completed_at' => now(),
            ]);

            // Update automation
            $automation->update([
                'last_executed_at' => now(),
                'execution_count' => $automation->execution_count + 1,
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Execution processing error: " . $e->getMessage());
            
            $execution->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
