<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\SiteVisit;
use App\Models\Lead;
use App\Models\Prospect;
use App\Services\TelecallerTaskService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * List all meetings (accessible by Admin, CRM, Sales Head, Sales Manager)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        $query = Meeting::with(['lead', 'prospect', 'creator', 'assignedTo', 'verifiedBy'])
            ->where('is_converted', false); // Exclude converted meetings

        // Role-based filtering
        if ($user->isAdmin() || $user->isCrm()) {
            // Admin and CRM can see all meetings
            // No additional filtering needed
        } elseif ($user->isSalesHead()) {
            // Sales Head can see all meetings from their entire team hierarchy
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->where(function($q) use ($allTeamMemberIds, $user) {
                    $q->whereIn('created_by', $allTeamMemberIds)
                      ->orWhere('created_by', $user->id)
                      ->orWhereIn('assigned_to', $allTeamMemberIds)
                      ->orWhere('assigned_to', $user->id);
                });
            } else {
                // No team members, show only their own
                $query->where('created_by', $user->id);
            }
            $query->where('is_dead', false);
        } elseif ($user->isSalesManager()) {
            // Sales Manager sees their own meetings and team meetings (excluding dead)
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->where(function($q) use ($teamMemberIds, $user) {
                $q->where('created_by', $user->id);
                if ($teamMemberIds->isNotEmpty()) {
                    $q->orWhereIn('created_by', $teamMemberIds)
                      ->orWhereIn('assigned_to', $teamMemberIds);
                }
            })->where('is_dead', false);
        } else {
            // Other roles - return empty
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'last_page' => 1
            ]);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by verification status
        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        // Filter by lead_id
        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        // Filter by prospect_id
        if ($request->has('prospect_id')) {
            $query->where('prospect_id', $request->prospect_id);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('property_type', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($leadQuery) use ($search) {
                      $leadQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 15);
        $meetings = $query->latest('scheduled_at')->paginate($perPage);

        return response()->json($meetings);
    }

    /**
     * Create a new meeting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:16',
            'employee' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'date_of_visit' => 'required|date',
            'project' => 'nullable|string|max:255',
            'budget_range' => 'required|in:Under 50 Lac,50 Lac – 1 Cr,1 Cr – 2 Cr,2 Cr – 3 Cr,Above 3 Cr',
            'team_leader' => 'nullable|string|max:255',
            'property_type' => 'required|in:Plot/Villa,Flat,Commercial,Just Exploring',
            'payment_mode' => 'required|in:Self Fund,Loan',
            'tentative_period' => 'required|in:Within 1 Month,Within 3 Months,Within 6 Months,More than 6 Months',
            'lead_type' => 'required|in:New Visit,Revisited,Meeting,Prospect',
            'scheduled_at' => 'required|date|after:now',
            'meeting_notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB each
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $data = $validator->validated();
        $data['created_by'] = $user->id;
        $data['status'] = 'scheduled';
        $data['verification_status'] = 'pending';

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $filename = 'meetings/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $photoPaths[] = $filename;
            }
            $data['photos'] = $photoPaths;
        }

        // If lead_id provided, update lead status to meeting_scheduled
        if (isset($data['lead_id'])) {
            $lead = Lead::find($data['lead_id']);
            if ($lead) {
                $lead->updateStatusIfAllowed('meeting_scheduled');
            }
        }

        $meeting = Meeting::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Meeting scheduled successfully',
            'data' => $meeting->load(['lead', 'prospect', 'creator', 'assignedTo']),
        ], 201);
    }

    /**
     * Get a specific meeting
     */
    public function show(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $meeting->load(['lead', 'prospect', 'creator', 'assignedTo', 'verifiedBy']);

        return response()->json($meeting);
    }

    /**
     * Update a meeting
     */
    public function update(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Can't update if already verified
        if ($meeting->verification_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update verified meeting',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:16',
            'employee' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'date_of_visit' => 'sometimes|required|date',
            'project' => 'nullable|string|max:255',
            'budget_range' => 'sometimes|required|in:Under 50 Lac,50 Lac – 1 Cr,1 Cr – 2 Cr,2 Cr – 3 Cr,Above 3 Cr',
            'team_leader' => 'nullable|string|max:255',
            'property_type' => 'sometimes|required|in:Plot/Villa,Flat,Commercial,Just Exploring',
            'payment_mode' => 'sometimes|required|in:Self Fund,Loan',
            'tentative_period' => 'sometimes|required|in:Within 1 Month,Within 3 Months,Within 6 Months,More than 6 Months',
            'lead_type' => 'sometimes|required|in:New Visit,Revisited,Meeting,Prospect',
            'scheduled_at' => 'sometimes|required|date',
            'meeting_notes' => 'nullable|string',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            // Delete old photos
            if ($meeting->photos) {
                foreach ($meeting->photos as $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $filename = 'meetings/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $photoPaths[] = $filename;
            }
            $data['photos'] = $photoPaths;
        }

        $meeting->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Meeting updated successfully',
            'data' => $meeting->fresh(['lead', 'prospect', 'creator', 'assignedTo']),
        ]);
    }

    /**
     * Mark meeting as completed
     */
    public function complete(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($meeting->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Meeting already completed',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'meeting_notes' => 'nullable|string',
            'proof_photos' => 'required|array|min:1',
            'proof_photos.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // Max 5MB per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle proof photo uploads
        $proofPhotoPaths = [];
        if ($request->hasFile('proof_photos')) {
            foreach ($request->file('proof_photos') as $photo) {
                $filename = 'meetings/proof/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $proofPhotoPaths[] = $filename;
            }
        }

        $data = $validator->validated();
        unset($data['proof_photos']); // Remove from update data
        $data['completion_proof_photos'] = $proofPhotoPaths;
        
        $meeting->markAsCompleted();
        $meeting->update($data);

        // Update lead status to meeting_completed
        if ($meeting->lead) {
            $meeting->lead->updateStatusIfAllowed('meeting_completed');
        }

        // Send verification notification to CRM/Admin
        $crmUsers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'crm']);
        })->get();

        foreach ($crmUsers as $crmUser) {
            $actionUrl = url('/crm/verifications');
            $this->notificationService->notifyNewVerification(
                $crmUser,
                'meeting',
                'New Meeting Verification',
                "Meeting '{$meeting->customer_name}' requires verification",
                $actionUrl,
                [
                    'meeting_id' => $meeting->id,
                    'customer_name' => $meeting->customer_name,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Meeting completed with proof photos. Awaiting verification.',
            'data' => $meeting->fresh(['lead', 'prospect', 'creator', 'assignedTo']),
        ]);
    }

    /**
     * Cancel a meeting
     */
    public function cancel(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($meeting->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Meeting already cancelled',
            ], 422);
        }

        $meeting->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meeting cancelled successfully',
            'data' => $meeting->fresh(),
        ]);
    }

    /**
     * Reschedule a meeting
     */
    public function reschedule(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access - Sales Manager can reschedule their own meetings
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Can only reschedule scheduled meetings
        if ($meeting->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Can only reschedule meetings with status "scheduled"',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update meeting with new scheduled time
        $oldScheduledAt = $meeting->scheduled_at;
        $meeting->scheduled_at = $request->scheduled_at;
        $meeting->status = 'scheduled'; // Keep as scheduled
        $meeting->is_rescheduled = true;
        $meeting->reschedule_count = ($meeting->reschedule_count ?? 0) + 1;
        $meeting->rescheduled_at = now();
        $meeting->rescheduled_by = $user->id;
        $meeting->reschedule_reason = $request->reason;
        // Reset verification status to pending (verification required after reschedule)
        $meeting->verification_status = 'pending';
        $meeting->verified_by = null;
        $meeting->verified_at = null;
        $meeting->rejection_reason = null;
        $meeting->save();

        // Create calling task 30 minutes before new scheduled time
        $taskService = app(TelecallerTaskService::class);
        $taskService->createCallTaskBeforeScheduled($meeting, $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Meeting rescheduled successfully. Verification required.',
            'data' => $meeting->fresh(['lead', 'prospect', 'creator', 'assignedTo', 'rescheduledBy']),
        ]);
    }

    /**
     * Verify a meeting (CRM/Admin or Senior)
     */
    public function verify(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Allow CRM/Admin to verify all meetings
        if (!$user->isAdmin() && !$user->isCrm()) {
            // Load creator relationship for hierarchy check
            $meeting->load('creator');
            // For other users, check if they are senior of the meeting creator
            $creator = $meeting->creator;
            if (!$creator) {
                return response()->json(['message' => 'Meeting creator not found'], 404);
            }

            // Check if user is senior of the meeting creator
            if (!$user->isSeniorOf($creator)) {
                return response()->json([
                    'message' => 'Forbidden. Only a senior (Sales Head or Manager) or CRM/Admin can verify this meeting.'
                ], 403);
            }
        }

        if ($meeting->verification_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Meeting already verified',
            ], 422);
        }

        if ($meeting->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Meeting must be completed before verification',
            ], 422);
        }

        $notes = $request->input('notes');
        $meeting->verify($user->id, $notes);

        return response()->json([
            'success' => true,
            'message' => 'Meeting verified successfully',
            'data' => $meeting->fresh(['lead', 'prospect', 'creator', 'verifiedBy']),
        ]);
    }

    /**
     * Reject a meeting (CRM/Admin or Senior)
     */
    public function reject(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Allow CRM/Admin to reject all meetings
        if (!$user->isAdmin() && !$user->isCrm()) {
            // Load creator relationship for hierarchy check
            $meeting->load('creator');
            // For other users, check if they are senior of the meeting creator
            $creator = $meeting->creator;
            if (!$creator) {
                return response()->json(['message' => 'Meeting creator not found'], 404);
            }

            // Check if user is senior of the meeting creator
            if (!$user->isSeniorOf($creator)) {
                return response()->json([
                    'message' => 'Forbidden. Only a senior (Sales Head or Manager) or CRM/Admin can reject this meeting.'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $meeting->reject($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Meeting rejected',
            'data' => $meeting->fresh(['lead', 'prospect', 'creator', 'verifiedBy']),
        ]);
    }

    /**
     * Convert meeting to site visit (1 click conversion)
     */
    public function convertToSiteVisit(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Meeting should be completed before converting
        if ($meeting->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Meeting must be completed before converting to site visit',
            ], 422);
        }

        // Create site visit from meeting data
        $siteVisitData = [
            'lead_id' => $meeting->lead_id,
            'prospect_id' => $meeting->prospect_id,
            'created_by' => $user->id,
            'assigned_to' => $meeting->assigned_to,
            'property_name' => $meeting->project,
            'property_address' => null,
            'scheduled_at' => $meeting->scheduled_at->addDays(1), // Schedule for next day by default
            'status' => 'scheduled',
            'verification_status' => 'pending',
            // Copy all form fields from meeting
            'customer_name' => $meeting->customer_name,
            'phone' => $meeting->phone,
            'employee' => $meeting->employee,
            'occupation' => $meeting->occupation,
            'date_of_visit' => $meeting->date_of_visit,
            'project' => $meeting->project,
            'budget_range' => $meeting->budget_range,
            'team_leader' => $meeting->team_leader,
            'property_type' => $meeting->property_type,
            'payment_mode' => $meeting->payment_mode,
            'tentative_period' => $meeting->tentative_period,
            'lead_type' => $meeting->lead_type,
            'photos' => $meeting->photos, // Copy photos
            'visit_notes' => 'Converted from Meeting #' . $meeting->id,
        ];

        $siteVisit = SiteVisit::create($siteVisitData);

        // Mark meeting as converted and link to site visit
        $meeting->update([
            'converted_to_site_visit_id' => $siteVisit->id,
            'is_converted' => true,
        ]);

        // Update lead status if exists
        if ($meeting->lead_id) {
            $lead = Lead::find($meeting->lead_id);
            if ($lead) {
                $lead->update(['status' => 'visit_scheduled']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Meeting converted to Site Visit successfully! Site visit has been scheduled.',
            'data' => [
                'meeting' => $meeting->fresh(),
                'site_visit' => $siteVisit->load(['lead', 'creator']),
                'site_visit_id' => $siteVisit->id,
            ],
        ]);
    }

    /**
     * Mark meeting as dead
     */
    public function markDead(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $meeting->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $meeting->markAsDead($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Meeting marked as dead successfully',
            'data' => $meeting->fresh(['lead', 'creator', 'markedDeadBy']),
        ]);
    }
}
