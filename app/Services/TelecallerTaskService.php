<?php

namespace App\Services;

use App\Models\TelecallerTask;
use App\Models\Meeting;
use App\Models\SiteVisit;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelecallerTaskService
{
    /**
     * Create a calling task for a telecaller when lead is assigned
     */
    public function createCallingTask(Lead $lead, User $telecaller, ?int $createdBy = null): TelecallerTask
    {
        $createdBy = $createdBy ?? auth()->id() ?? 1; // Fallback to user ID 1 if no auth

        $task = TelecallerTask::create([
            'lead_id' => $lead->id,
            'assigned_to' => $telecaller->id,
            'task_type' => 'calling',
            'status' => 'pending',
            'scheduled_at' => now(),
            'created_by' => $createdBy,
        ]);

        Log::info('Calling task created', [
            'task_id' => $task->id,
            'lead_id' => $lead->id,
            'telecaller_id' => $telecaller->id,
        ]);

        return $task;
    }

    /**
     * Create a CNP retry task
     */
    public function createCnpRetryTask(Lead $lead, User $telecaller, ?int $createdBy = null): TelecallerTask
    {
        $createdBy = $createdBy ?? auth()->id() ?? 1;

        $task = TelecallerTask::create([
            'lead_id' => $lead->id,
            'assigned_to' => $telecaller->id,
            'task_type' => 'cnp_retry',
            'status' => 'pending',
            'scheduled_at' => now()->addDay(), // Schedule for next day
            'created_by' => $createdBy,
        ]);

        return $task;
    }

    /**
     * Create a calling task 30 minutes before meeting/site visit scheduled time
     */
    public function createCallTaskBeforeScheduled($item, int $createdBy = null): ?TelecallerTask
    {
        try {
            // Determine if it's a Meeting or SiteVisit
            if ($item instanceof Meeting) {
                $leadId = $item->lead_id;
                $scheduledAt = $item->scheduled_at;
                $itemType = 'Meeting';
            } elseif ($item instanceof SiteVisit) {
                $leadId = $item->lead_id;
                $scheduledAt = $item->scheduled_at;
                $itemType = 'Site Visit';
            } else {
                Log::warning('Invalid item type for creating call task', ['item' => get_class($item)]);
                return null;
            }

            if (!$leadId || !$scheduledAt) {
                Log::warning('Missing lead_id or scheduled_at for call task creation', [
                    'lead_id' => $leadId,
                    'scheduled_at' => $scheduledAt,
                ]);
                return null;
            }

            // Get the lead and find the telecaller assigned to it
            $lead = Lead::with('activeAssignments.assignedTo')->find($leadId);
            if (!$lead) {
                Log::warning('Lead not found for call task creation', ['lead_id' => $leadId]);
                return null;
            }

            // Get telecaller from active assignment
            $telecaller = null;
            if ($lead->activeAssignments && $lead->activeAssignments->count() > 0) {
                $assignment = $lead->activeAssignments->first();
                $assignedUser = $assignment->assignedTo;
                
                // Check if assigned user is a telecaller
                if ($assignedUser && $assignedUser->role && $assignedUser->role->slug === 'telecaller') {
                    $telecaller = $assignedUser;
                }
            }

            if (!$telecaller) {
                Log::warning('No telecaller found for lead', ['lead_id' => $leadId]);
                return null;
            }

            // Calculate task scheduled time (30 minutes before meeting/visit)
            $taskScheduledAt = Carbon::parse($scheduledAt)->subMinutes(30);

            // Don't create task if the time has already passed
            if ($taskScheduledAt->isPast()) {
                Log::info('Skipping call task creation - scheduled time is in the past', [
                    'item_type' => $itemType,
                    'item_id' => $item->id,
                    'scheduled_at' => $scheduledAt,
                    'task_scheduled_at' => $taskScheduledAt,
                ]);
                return null;
            }

            // Check if a similar task already exists
            $existingTask = TelecallerTask::where('lead_id', $leadId)
                ->where('assigned_to', $telecaller->id)
                ->where('scheduled_at', $taskScheduledAt)
                ->where('status', 'pending')
                ->first();

            if ($existingTask) {
                Log::info('Call task already exists', [
                    'task_id' => $existingTask->id,
                    'lead_id' => $leadId,
                ]);
                return $existingTask;
            }

            $createdBy = $createdBy ?? auth()->id() ?? 1;

            // Create the task
            $task = TelecallerTask::create([
                'lead_id' => $leadId,
                'assigned_to' => $telecaller->id,
                'task_type' => 'calling',
                'status' => 'pending',
                'scheduled_at' => $taskScheduledAt,
                'notes' => "Reminder call 30 min before scheduled {$itemType}",
                'created_by' => $createdBy,
            ]);

            Log::info('Call task created before scheduled item', [
                'task_id' => $task->id,
                'lead_id' => $leadId,
                'telecaller_id' => $telecaller->id,
                'item_type' => $itemType,
                'item_id' => $item->id,
                'scheduled_at' => $scheduledAt,
                'task_scheduled_at' => $taskScheduledAt,
            ]);

            return $task;
        } catch (\Exception $e) {
            Log::error('Failed to create call task before scheduled item', [
                'error' => $e->getMessage(),
                'item_type' => get_class($item),
                'item_id' => $item->id ?? null,
            ]);
            return null;
        }
    }
}

