<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LeadActivityService
{
    /**
     * Get complete timeline of all activities for a lead
     */
    public function getTimeline(Lead $lead): Collection
    {
        $activities = collect();

        // 1. Lead Created
        if ($lead->created_at) {
            $activities->push([
                'type' => 'created',
                'title' => 'Lead Created',
                'description' => "Lead '{$lead->name}' was created",
                'user' => $lead->creator,
                'timestamp' => $lead->created_at,
                'icon' => 'fa-plus-circle',
                'color' => '#10b981', // green
                'metadata' => [
                    'source' => $lead->source,
                    'status' => $lead->status,
                ],
            ]);
        }

        // 2. Activity Logs
        $activityLogs = ActivityLog::where('model_type', 'Lead')
            ->where('model_id', $lead->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($activityLogs as $log) {
            $activities->push([
                'type' => $this->getActivityType($log->action),
                'title' => $this->getActivityTitle($log),
                'description' => $log->description ?? $this->getActivityDescription($log),
                'user' => $log->user,
                'timestamp' => $log->created_at,
                'icon' => $this->getActivityIcon($log->action),
                'color' => $this->getActivityColor($log->action),
                'metadata' => [
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'action' => $log->action,
                ],
            ]);
        }

        // 3. Lead Assignments
        foreach ($lead->assignments()->with(['assignedTo', 'assignedBy'])->orderBy('assigned_at', 'desc')->get() as $assignment) {
            $activities->push([
                'type' => 'assigned',
                'title' => 'Lead Assigned',
                'description' => $assignment->is_active 
                    ? "Assigned to {$assignment->assignedTo->name}"
                    : "Unassigned from {$assignment->assignedTo->name}",
                'user' => $assignment->assignedBy,
                'timestamp' => $assignment->assigned_at ?? $assignment->created_at,
                'icon' => $assignment->is_active ? 'fa-user-plus' : 'fa-user-minus',
                'color' => $assignment->is_active ? '#3b82f6' : '#ef4444',
                'metadata' => [
                    'assigned_to' => $assignment->assignedTo->name,
                    'is_active' => $assignment->is_active,
                ],
            ]);
        }

        // 4. Call Logs
        foreach ($lead->callLogs()->with('user')->orderBy('created_at', 'desc')->get() as $callLog) {
            $callType = $callLog->direction === 'inbound' ? 'Inbound' : 'Outbound';
            $duration = $callLog->duration ? $this->formatDuration($callLog->duration) : 'N/A';
            
            $activities->push([
                'type' => 'call',
                'title' => "{$callType} Call",
                'description' => "{$callType} call with {$lead->name}. Duration: {$duration}",
                'user' => $callLog->user,
                'timestamp' => $callLog->created_at,
                'icon' => $callLog->direction === 'inbound' ? 'fa-phone-alt' : 'fa-phone',
                'color' => $callLog->direction === 'inbound' ? '#10b981' : '#3b82f6',
                'metadata' => [
                    'direction' => $callLog->direction,
                    'duration' => $callLog->duration,
                    'recording_url' => $callLog->recording_url,
                    'status' => $callLog->status,
                ],
            ]);
        }

        // 5. Site Visits
        foreach ($lead->siteVisits()->with(['creator', 'assignedTo'])->orderBy('created_at', 'desc')->get() as $siteVisit) {
            $activities->push([
                'type' => 'site_visit',
                'title' => 'Site Visit ' . ucfirst($siteVisit->status),
                'description' => "Site visit {$siteVisit->status} for {$lead->name}" . 
                    ($siteVisit->scheduled_at ? " on " . $siteVisit->scheduled_at->format('M d, Y') : ''),
                'user' => $siteVisit->creator,
                'timestamp' => $siteVisit->created_at,
                'icon' => $this->getSiteVisitIcon($siteVisit->status),
                'color' => $this->getSiteVisitColor($siteVisit->status),
                'metadata' => [
                    'status' => $siteVisit->status,
                    'scheduled_at' => $siteVisit->scheduled_at,
                    'verification_status' => $siteVisit->verification_status,
                    'property_name' => $siteVisit->property_name,
                ],
            ]);

            // If verified, add verification activity
            if ($siteVisit->verified_at && $siteVisit->verifiedBy) {
                $activities->push([
                    'type' => 'site_visit_verified',
                    'title' => 'Site Visit Verified',
                    'description' => "Site visit verified by {$siteVisit->verifiedBy->name}",
                    'user' => $siteVisit->verifiedBy,
                    'timestamp' => $siteVisit->verified_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981',
                    'metadata' => [
                        'verification_status' => $siteVisit->verification_status,
                    ],
                ]);
            }
        }

        // 6. Follow-ups
        foreach ($lead->followUps()->with('creator')->orderBy('created_at', 'desc')->get() as $followUp) {
            $activities->push([
                'type' => 'followup',
                'title' => 'Follow-up ' . ucfirst($followUp->status),
                'description' => "Follow-up {$followUp->status}" . 
                    ($followUp->scheduled_at ? " scheduled for " . $followUp->scheduled_at->format('M d, Y h:i A') : ''),
                'user' => $followUp->creator,
                'timestamp' => $followUp->created_at,
                'icon' => $this->getFollowUpIcon($followUp->status),
                'color' => $this->getFollowUpColor($followUp->status),
                'metadata' => [
                    'type' => $followUp->type,
                    'status' => $followUp->status,
                    'scheduled_at' => $followUp->scheduled_at,
                    'completed_at' => $followUp->completed_at,
                ],
            ]);
        }

        // 7. Meetings
        foreach ($lead->meetings()->with(['creator', 'assignedTo', 'verifiedBy'])->orderBy('created_at', 'desc')->get() as $meeting) {
            $activities->push([
                'type' => 'meeting',
                'title' => 'Meeting ' . ucfirst($meeting->status),
                'description' => "Meeting {$meeting->status}" . 
                    ($meeting->scheduled_at ? " scheduled for " . $meeting->scheduled_at->format('M d, Y h:i A') : ''),
                'user' => $meeting->creator,
                'timestamp' => $meeting->created_at,
                'icon' => $this->getMeetingIcon($meeting->status),
                'color' => $this->getMeetingColor($meeting->status),
                'metadata' => [
                    'status' => $meeting->status,
                    'scheduled_at' => $meeting->scheduled_at,
                    'verification_status' => $meeting->verification_status,
                    'customer_name' => $meeting->customer_name,
                ],
            ]);

            // If verified, add verification activity
            if ($meeting->verified_at && $meeting->verifiedBy) {
                $activities->push([
                    'type' => 'meeting_verified',
                    'title' => 'Meeting Verified',
                    'description' => "Meeting verified by {$meeting->verifiedBy->name}",
                    'user' => $meeting->verifiedBy,
                    'timestamp' => $meeting->verified_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981',
                    'metadata' => [
                        'verification_status' => $meeting->verification_status,
                    ],
                ]);
            }
        }

        // 8. Prospects
        foreach ($lead->prospects()->with(['createdBy', 'verifiedBy'])->orderBy('created_at', 'desc')->get() as $prospect) {
            $activities->push([
                'type' => 'prospect',
                'title' => 'Prospect Created',
                'description' => "Prospect created for {$lead->name}" . 
                    ($prospect->lead_score ? " with lead score: {$prospect->lead_score}/5" : ''),
                'user' => $prospect->createdBy,
                'timestamp' => $prospect->created_at,
                'icon' => 'fa-user-check',
                'color' => '#8b5cf6',
                'metadata' => [
                    'verification_status' => $prospect->verification_status,
                    'lead_score' => $prospect->lead_score,
                ],
            ]);

            // If verified, add verification activity
            if ($prospect->verified_at && $prospect->verifiedBy) {
                $activities->push([
                    'type' => 'prospect_verified',
                    'title' => 'Prospect Verified',
                    'description' => "Prospect verified by {$prospect->verifiedBy->name}",
                    'user' => $prospect->verifiedBy,
                    'timestamp' => $prospect->verified_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981',
                    'metadata' => [
                        'verification_status' => $prospect->verification_status,
                    ],
                ]);
            }
        }

        // 9. Status Changes (from lead history or activity logs)
        // This is already covered in ActivityLog, but we can add explicit status change tracking
        if ($lead->marked_dead_at) {
            $activities->push([
                'type' => 'status_changed',
                'title' => 'Lead Marked as Dead',
                'description' => "Lead marked as dead. Reason: {$lead->dead_reason}",
                'user' => $lead->markedDeadBy,
                'timestamp' => $lead->marked_dead_at,
                'icon' => 'fa-times-circle',
                'color' => '#ef4444',
                'metadata' => [
                    'status' => 'dead',
                    'reason' => $lead->dead_reason,
                    'stage' => $lead->dead_at_stage,
                ],
            ]);
        }

        // Sort by timestamp (newest first)
        return $activities->sortByDesc('timestamp')->values();
    }

    private function getActivityType(string $action): string
    {
        return match($action) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'assigned' => 'assigned',
            default => 'activity',
        };
    }

    private function getActivityTitle($log): string
    {
        if ($log->old_values && $log->new_values && isset($log->old_values['status']) && isset($log->new_values['status'])) {
            return 'Status Changed';
        }
        
        return ucfirst($log->action);
    }

    private function getActivityDescription($log): string
    {
        if ($log->old_values && $log->new_values) {
            if (isset($log->old_values['status']) && isset($log->new_values['status'])) {
                return "Status changed from '{$log->old_values['status']}' to '{$log->new_values['status']}'";
            }
            
            $changes = [];
            foreach ($log->new_values as $key => $value) {
                if (isset($log->old_values[$key]) && $log->old_values[$key] != $value) {
                    $changes[] = "{$key}: {$log->old_values[$key]} → {$value}";
                }
            }
            
            return !empty($changes) ? implode(', ', $changes) : 'Updated';
        }
        
        return ucfirst($log->action);
    }

    private function getActivityIcon(string $action): string
    {
        return match($action) {
            'created' => 'fa-plus-circle',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            'assigned' => 'fa-user-plus',
            default => 'fa-info-circle',
        };
    }

    private function getActivityColor(string $action): string
    {
        return match($action) {
            'created' => '#10b981',
            'updated' => '#3b82f6',
            'deleted' => '#ef4444',
            'assigned' => '#8b5cf6',
            default => '#6b7280',
        };
    }

    private function getSiteVisitIcon(string $status): string
    {
        return match($status) {
            'scheduled' => 'fa-calendar-alt',
            'completed' => 'fa-check-circle',
            'cancelled' => 'fa-times-circle',
            default => 'fa-map-marker-alt',
        };
    }

    private function getSiteVisitColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function getFollowUpIcon(string $status): string
    {
        return match($status) {
            'scheduled' => 'fa-calendar-check',
            'completed' => 'fa-check-circle',
            'missed' => 'fa-exclamation-circle',
            'cancelled' => 'fa-times-circle',
            default => 'fa-clock',
        };
    }

    private function getFollowUpColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'missed' => '#f59e0b',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function getMeetingIcon(string $status): string
    {
        return match($status) {
            'scheduled' => 'fa-calendar-alt',
            'completed' => 'fa-check-circle',
            'cancelled' => 'fa-times-circle',
            default => 'fa-handshake',
        };
    }

    private function getMeetingColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        
        if ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        }
        
        return "{$secs}s";
    }
}
