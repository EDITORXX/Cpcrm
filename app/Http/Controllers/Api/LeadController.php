<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\LeadAssigned;
use App\Events\LeadStatusUpdated;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Services\LeadTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Lead::with(['creator', 'activeAssignments.assignedTo']);

        // Role-based filtering
        if ($user->isSalesExecutive() || $user->isTelecaller()) {
            $query->whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $allUserIds = $teamMemberIds->merge([$user->id])->toArray();
            
            // Show leads that are:
            // 1. Directly assigned to this sales manager (active or inactive - to show all historical leads)
            // 2. OR assigned to any team member (active or inactive - to show all historical leads)
            // 3. OR came from verified prospects of team members
            $query->where(function ($q) use ($user, $teamMemberIds, $allUserIds) {
                // Leads assigned to manager or any team member (include both active and inactive assignments)
                $q->whereHas('assignments', function ($assignmentQ) use ($allUserIds) {
                    $assignmentQ->whereIn('assigned_to', $allUserIds);
                });
                
                // OR leads from verified prospects of team members
                if ($teamMemberIds->isNotEmpty()) {
                    $q->orWhereHas('prospects', function ($subQ) use ($teamMemberIds) {
                        $subQ->whereIn('telecaller_id', $teamMemberIds)
                             ->whereIn('verification_status', ['verified', 'approved']);
                    });
                }
            });
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Increase default per_page to show all leads (was 15, now 50)
        $perPage = $request->get('per_page', 50);
        $leads = $query->latest()->paginate($perPage);

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'source' => 'nullable|in:website,referral,walk_in,call,social_media,other',
            'property_type' => 'nullable|in:apartment,villa,plot,commercial,other',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'new';

        $lead = Lead::create($validated);

        // Assign lead if provided
        if ($request->has('assigned_to')) {
            $this->assignLead($lead, $request->assigned_to, $request->user()->id);
        }

        return response()->json($lead->load(['creator', 'activeAssignments.assignedTo']), 201);
    }

    public function show(Lead $lead)
    {
        $user = request()->user();

        // Check access
        if (!$this->canAccessLead($user, $lead)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $lead->load([
            'creator',
            'assignments.assignedTo',
            'assignments.assignedBy',
            'siteVisits.assignedTo',
            'followUps.creator',
        ]);

        return response()->json($lead);
    }

    public function update(Request $request, Lead $lead)
    {
        $user = $request->user();

        if (!$this->canAccessLead($user, $lead)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'source' => 'nullable|in:website,referral,walk_in,call,social_media,other',
            'status' => 'sometimes|in:new,connected,verified_prospect,meeting_scheduled,meeting_completed,visit_scheduled,visit_done,revisited_scheduled,revisited_completed,closed,dead,on_hold',
            'property_type' => 'nullable|in:apartment,villa,plot,commercial,other',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $lead->status;
        
        // Handle smart override logic for manual status changes
        if (isset($validated['status']) && $oldStatus !== $validated['status']) {
            $newStatus = $validated['status'];
            
            // If manager manually sets to 'dead' or 'closed', disable auto-updates
            if (in_array($newStatus, ['dead', 'closed'])) {
                $lead->disableAutoUpdate();
            }
            // If changing from 'dead'/'closed' to something else, enable auto-updates
            elseif (in_array($oldStatus, ['dead', 'closed'])) {
                $lead->enableAutoUpdate();
            }
        }
        
        $lead->update($validated);

        // Fire event if status changed
        if (isset($validated['status']) && $oldStatus !== $validated['status']) {
            event(new LeadStatusUpdated($lead, $oldStatus, $validated['status']));
        }

        return response()->json($lead->load(['creator', 'activeAssignments.assignedTo']));
    }

    public function assign(Request $request, Lead $lead)
    {
        $user = $request->user();

        if (!$user->canAssignLeads()) {
            return response()->json(['message' => 'Forbidden. You cannot assign leads.'], 403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $this->assignLead($lead, $validated['assigned_to'], $user->id, $validated['notes'] ?? null);

        return response()->json(['message' => 'Lead assigned successfully']);
    }

    private function assignLead(Lead $lead, int $assignedTo, int $assignedBy, ?string $notes = null): void
    {
        // Deactivate existing assignments
        $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

        // Create new assignment
        LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'assignment_type' => 'primary',
            'notes' => $notes,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Fire event
        event(new LeadAssigned($lead, $assignedTo, $assignedBy));
    }

    /**
     * Get leads pending verification
     */
    public function pendingVerifications(Request $request)
    {
        $user = $request->user();

        // Only Admin or CRM can view pending verifications
        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $leads = Lead::where('needs_verification', true)
            ->with(['verificationRequestedBy', 'pendingManager', 'activeAssignments.assignedTo'])
            ->latest('verification_requested_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    /**
     * Verify and transfer lead to new manager
     */
    public function verifyLead(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Only Admin or CRM can verify leads
        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$lead->needs_verification) {
            return response()->json(['message' => 'Lead does not need verification'], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $leadTransferService = app(LeadTransferService::class);
        $success = $leadTransferService->verifyAndTransferLead($lead, $user->id, $validated['notes'] ?? null);

        if ($success) {
            return response()->json([
                'message' => 'Lead verified and transferred successfully',
                'lead' => $lead->fresh()->load(['verifiedBy', 'activeAssignments.assignedTo'])
            ]);
        }

        return response()->json(['message' => 'Failed to verify and transfer lead'], 500);
    }

    /**
     * Reject verification and keep lead with current assignment
     */
    public function rejectVerification(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Only Admin or CRM can reject verifications
        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$lead->needs_verification) {
            return response()->json(['message' => 'Lead does not need verification'], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $leadTransferService = app(LeadTransferService::class);
        $success = $leadTransferService->rejectVerification($lead, $user->id, $validated['notes'] ?? null);

        if ($success) {
            return response()->json([
                'message' => 'Verification rejected, lead kept with current assignment',
                'lead' => $lead->fresh()->load(['verifiedBy', 'activeAssignments.assignedTo'])
            ]);
        }

        return response()->json(['message' => 'Failed to reject verification'], 500);
    }

    private function canAccessLead($user, Lead $lead): bool
    {
        if ($user->canViewAllLeads()) {
            return true;
        }

        // Check if lead is directly assigned to user
        if ($lead->activeAssignments()->where('assigned_to', $user->id)->exists()) {
            return true;
        }

        // For Sales Managers: allow access to leads that came from their team's verified prospects
        if ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            
            return $lead->prospects()
                ->whereIn('telecaller_id', $teamMemberIds)
                ->whereIn('verification_status', ['verified', 'approved'])
                ->exists();
        }

        return false;
    }
}
