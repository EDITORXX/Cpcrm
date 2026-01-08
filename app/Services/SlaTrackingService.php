<?php

namespace App\Services;

use App\Models\SlaTracking;
use App\Models\SmartImportLeadAssignment;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SlaTrackingService
{
    /**
     * Start SLA tracking for a lead assignment
     */
    public function startSlaTracking(int $assignmentId, int $slaMinutes): SlaTracking
    {
        return SlaTracking::create([
            'lead_assignment_id' => $assignmentId,
            'sla_minutes' => $slaMinutes,
            'started_at' => Carbon::now(),
            'status' => 'pending',
        ]);
    }

    /**
     * Mark SLA as met (first contact made)
     */
    public function markSlaAsMet(int $assignmentId): void
    {
        $assignment = SmartImportLeadAssignment::find($assignmentId);
        if (!$assignment) {
            return;
        }

        $slaTracking = $assignment->slaTracking;
        if ($slaTracking && $slaTracking->status === 'pending') {
            $slaTracking->markAsMet();
            $assignment->update([
                'sla_met_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Check and handle SLA breaches
     */
    public function checkSlaBreaches(): array
    {
        $breached = [];
        $pendingSlas = SlaTracking::where('status', 'pending')
            ->with('leadAssignment.lead')
            ->get();

        foreach ($pendingSlas as $sla) {
            if ($sla->checkBreach()) {
                $breached[] = $sla;
                
                // Update assignment
                $sla->leadAssignment->update([
                    'sla_breached' => true,
                ]);

                // Trigger escalation if configured
                $this->triggerEscalation($sla);
            }
        }

        return $breached;
    }

    /**
     * Trigger escalation for breached SLA
     */
    protected function triggerEscalation(SlaTracking $sla): void
    {
        $assignment = $sla->leadAssignment;
        $execution = $assignment->execution;
        $automation = $execution->automation;

        // Check if escalation is configured
        if (!$automation->escalation_user_id) {
            return; // No escalation configured
        }

        // Check if already escalated
        if ($assignment->escalated_at) {
            return; // Already escalated
        }

        try {
            DB::beginTransaction();

            // Reassign to escalation user
            $lead = $assignment->lead;
            $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

            \App\Models\LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_to' => $automation->escalation_user_id,
                'assigned_by' => $automation->created_by,
                'assignment_type' => 'primary',
                'assigned_at' => Carbon::now(),
                'is_active' => true,
            ]);

            // Update assignment record
            $assignment->update([
                'escalated_at' => Carbon::now(),
                'escalated_to' => $automation->escalation_user_id,
            ]);

            // Update SLA tracking
            $sla->update([
                'status' => 'escalated',
                'escalated_at' => Carbon::now(),
                'escalated_to' => $automation->escalation_user_id,
            ]);

            DB::commit();

            // TODO: Send notification to escalation user
            // event(new SlaEscalated($assignment, $automation->escalation_user_id));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SLA escalation error: " . $e->getMessage());
        }
    }

    /**
     * Get SLA status for assignment
     */
    public function getSlaStatus(int $assignmentId): ?array
    {
        $assignment = SmartImportLeadAssignment::find($assignmentId);
        if (!$assignment || !$assignment->slaTracking) {
            return null;
        }

        $sla = $assignment->slaTracking;
        $deadline = $sla->started_at->copy()->addMinutes($sla->sla_minutes);
        $now = Carbon::now();

        return [
            'status' => $sla->status,
            'sla_minutes' => $sla->sla_minutes,
            'started_at' => $sla->started_at,
            'deadline' => $deadline,
            'time_remaining' => $now->lessThan($deadline) ? $now->diffInMinutes($deadline) : 0,
            'is_breached' => $sla->status === 'breached' || $sla->status === 'escalated',
            'first_contact_at' => $sla->first_contact_at,
            'escalated_to' => $sla->escalated_to,
        ];
    }

    /**
     * Get SLA compliance metrics
     */
    public function getComplianceMetrics(array $filters = []): array
    {
        $query = SlaTracking::query();

        if (isset($filters['start_date'])) {
            $query->where('started_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('started_at', '<=', $filters['end_date']);
        }

        $total = $query->count();
        $met = $query->where('status', 'met')->count();
        $breached = $query->whereIn('status', ['breached', 'escalated'])->count();
        $pending = $query->where('status', 'pending')->count();

        $complianceRate = $total > 0 ? ($met / $total) * 100 : 0;

        return [
            'total' => $total,
            'met' => $met,
            'breached' => $breached,
            'pending' => $pending,
            'compliance_rate' => round($complianceRate, 2),
        ];
    }
}

