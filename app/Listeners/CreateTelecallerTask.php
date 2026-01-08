<?php

namespace App\Listeners;

use App\Events\LeadAssigned;
use App\Events\DashboardUpdate;
use App\Models\CrmAssignment;
use App\Models\TelecallerTask;
use App\Models\User;
use App\Services\TelecallerTaskService;
use Illuminate\Support\Facades\Log;

class CreateTelecallerTask
{
    protected $telecallerTaskService;

    public function __construct(TelecallerTaskService $telecallerTaskService)
    {
        $this->telecallerTaskService = $telecallerTaskService;
    }

    /**
     * Handle the event - Auto-create calling task for telecaller when lead is assigned
     */
    public function handle(LeadAssigned $event): void
    {
        try {
            $assignedUser = User::with('role')->find($event->assignedTo);

            // Check if assigned user is a telecaller
            if (!$assignedUser || !$assignedUser->isTelecaller()) {
                return;
            }

            $lead = $event->lead;

            // Create CrmAssignment if it doesn't exist
            $crmAssignment = CrmAssignment::where('lead_id', $lead->id)
                ->where('assigned_to', $assignedUser->id)
                ->where('call_status', 'pending')
                ->first();

            if (!$crmAssignment) {
                $crmAssignment = CrmAssignment::create([
                    'lead_id' => $lead->id,
                    'customer_name' => $lead->name,
                    'phone' => $lead->phone,
                    'assigned_to' => $assignedUser->id,
                    'assigned_by' => $event->assignedBy,
                    'assigned_at' => now(),
                    'call_status' => 'pending',
                ]);
            }

            // Check if TelecallerTask already exists for this lead and telecaller
            $existingTask = TelecallerTask::where('lead_id', $lead->id)
                ->where('assigned_to', $assignedUser->id)
                ->where('task_type', 'calling')
                ->where('status', 'pending')
                ->first();

            if (!$existingTask) {
                // Auto-create calling task for telecaller
                $task = $this->telecallerTaskService->createCallingTask(
                    $lead,
                    $assignedUser,
                    $event->assignedBy
                );
                
                Log::info("Auto-created calling task for telecaller", [
                    'task_id' => $task->id,
                    'lead_id' => $lead->id,
                    'telecaller_id' => $assignedUser->id,
                ]);
                
                // Broadcast dashboard update
                event(new DashboardUpdate($assignedUser->id, 'task_created', [
                    'lead_id' => $lead->id,
                    'lead_name' => $lead->name,
                    'task_id' => $task->id,
                ]));
            }
        } catch (\Exception $e) {
            Log::error("Failed to create telecaller task for lead {$event->lead->id}: " . $e->getMessage());
        }
    }
}

