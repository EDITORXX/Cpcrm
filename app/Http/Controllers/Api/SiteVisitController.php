<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\SiteVisitCreated;
use App\Models\SiteVisit;
use App\Models\Lead;
use App\Models\Prospect;
use App\Services\TelecallerTaskService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SiteVisitController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        $query = SiteVisit::with(['lead', 'creator', 'assignedTo']);

        // Role-based filtering
        if ($user->isAdmin() || $user->isCrm()) {
            // Admin and CRM can see all site visits
            // No additional filtering needed
        } elseif ($user->isSalesHead()) {
            // Sales Head can see all site visits from their entire team hierarchy
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
            // Sales Manager sees site visits from their direct team members
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->where(function($q) use ($teamMemberIds, $user) {
                $q->where('created_by', $user->id);
                if ($teamMemberIds->isNotEmpty()) {
                    $q->orWhereIn('created_by', $teamMemberIds)
                      ->orWhereIn('assigned_to', $teamMemberIds);
                }
            })->where('is_dead', false);
        } elseif ($user->isSalesExecutive() || $user->isTelecaller()) {
            $query->where('assigned_to', $user->id);
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

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->has('closer_status')) {
            $query->where('closer_status', $request->closer_status);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('property_name', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($leadQuery) use ($search) {
                      $leadQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter
        if ($request->has('date_filter') && $request->date_filter) {
            $dateFilter = $request->date_filter;
            $today = now()->startOfDay();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('scheduled_at', $today);
                    break;
                case 'this_week':
                    $query->whereBetween('scheduled_at', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereBetween('scheduled_at', [
                        $today->copy()->startOfMonth(),
                        $today->copy()->endOfMonth()
                    ]);
                    break;
                case 'this_year':
                    $query->whereBetween('scheduled_at', [
                        $today->copy()->startOfYear(),
                        $today->copy()->endOfYear()
                    ]);
                    break;
                case 'custom':
                    if ($request->has('date_from') && $request->has('date_to')) {
                        $query->whereBetween('scheduled_at', [
                            $request->date_from . ' 00:00:00',
                            $request->date_to . ' 23:59:59'
                        ]);
                    }
                    break;
            }
        }

        $visits = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));

        return response()->json($visits);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'property_name' => 'nullable|string|max:255',
            'property_address' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'visit_notes' => 'nullable|string',
            // New form fields
            'customer_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:16',
            'employee' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'date_of_visit' => 'nullable|date',
            'project' => 'nullable|string|max:255',
            'budget_range' => 'nullable|in:Under 50 Lac,50 Lac – 1 Cr,1 Cr – 2 Cr,2 Cr – 3 Cr,Above 3 Cr',
            'team_leader' => 'nullable|string|max:255',
            'property_type' => 'nullable|in:Plot/Villa,Flat,Commercial,Just Exploring',
            'payment_mode' => 'nullable|in:Self Fund,Loan',
            'tentative_period' => 'nullable|in:Within 1 Month,Within 3 Months,Within 6 Months,More than 6 Months',
            'lead_type' => 'nullable|in:New Visit,Revisited,Meeting,Prospect',
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

        $validated = $validator->validated();
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'scheduled';
        $validated['verification_status'] = 'pending';

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $filename = 'site-visits/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $photoPaths[] = $filename;
            }
            $validated['photos'] = $photoPaths;
        }

        $siteVisit = SiteVisit::create($validated);

        // Update lead status based on lead_type
        if (isset($validated['lead_id'])) {
            $lead = Lead::find($validated['lead_id']);
            if ($lead) {
                $leadType = $validated['lead_type'] ?? null;
                if ($leadType === 'Revisited') {
                    $lead->updateStatusIfAllowed('revisited_scheduled');
                } else {
                    // Default to visit_scheduled for 'New Visit' or other types
                    $lead->updateStatusIfAllowed('visit_scheduled');
                }
            }
        }

        // Fire event (wrap in try-catch to handle broadcasting errors)
        try {
            event(new SiteVisitCreated($siteVisit));
        } catch (\Exception $e) {
            // Broadcasting errors (like Pusher) shouldn't stop site visit creation
            // Log but continue - the site visit is successfully created
            \Log::warning("Broadcasting error in SiteVisitController (non-critical): " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Site visit scheduled successfully',
            'data' => $siteVisit->load(['lead', 'creator', 'assignedTo']),
        ], 201);
    }

    public function show(SiteVisit $siteVisit)
    {
        $user = request()->user();

        // Check access
        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $siteVisit->load(['lead', 'creator', 'assignedTo']);

        return response()->json($siteVisit);
    }

    public function update(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'property_name' => 'sometimes|string|max:255',
            'property_address' => 'nullable|string',
            'scheduled_at' => 'sometimes|date',
            'completed_at' => 'nullable|date',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,rescheduled',
            'visit_notes' => 'nullable|string',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $siteVisit->update($validated);

        // Update lead status if visit completed
        if (isset($validated['status']) && $validated['status'] === 'completed') {
            if ($siteVisit->lead) {
                $leadType = $siteVisit->lead_type ?? null;
                if ($leadType === 'Revisited') {
                    $siteVisit->lead->updateStatusIfAllowed('revisited_completed');
                } else {
                    $siteVisit->lead->updateStatusIfAllowed('visit_done');
                }
            }
        }

        return response()->json($siteVisit->load(['lead', 'creator', 'assignedTo']));
    }

    /**
     * Mark site visit as completed
     */
    public function complete(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access
        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($siteVisit->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Site visit already completed',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'visit_notes' => 'nullable|string',
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
                $filename = 'site-visits/proof/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $proofPhotoPaths[] = $filename;
            }
        }

        $data = $validator->validated();
        unset($data['proof_photos']); // Remove from update data
        $data['completion_proof_photos'] = $proofPhotoPaths;
        
        $siteVisit->markAsCompleted();
        $siteVisit->update($data);

        // Update lead status based on lead_type
        if ($siteVisit->lead) {
            $leadType = $siteVisit->lead_type ?? null;
            if ($leadType === 'Revisited') {
                $siteVisit->lead->updateStatusIfAllowed('revisited_completed');
            } else {
                // Default to visit_done for 'New Visit' or other types
                $siteVisit->lead->updateStatusIfAllowed('visit_done');
            }
        }

        // Send verification notification to CRM/Admin
        $crmUsers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'crm']);
        })->get();

        foreach ($crmUsers as $crmUser) {
            $actionUrl = url('/crm/verifications');
            $customerName = $siteVisit->customer_name ?? ($siteVisit->lead ? $siteVisit->lead->name : 'Customer');
            $this->notificationService->notifyNewVerification(
                $crmUser,
                'site_visit',
                'New Site Visit Verification',
                "Site visit for '{$customerName}' requires verification",
                $actionUrl,
                [
                    'site_visit_id' => $siteVisit->id,
                    'customer_name' => $customerName,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Site visit completed with proof photos. Awaiting verification.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo']),
        ]);
    }

    /**
     * Reschedule a site visit
     */
    public function reschedule(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access
        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Can only reschedule scheduled site visits
        if ($siteVisit->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Can only reschedule site visits with status "scheduled"',
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

        // Update site visit with new scheduled time
        $oldScheduledAt = $siteVisit->scheduled_at;
        $siteVisit->scheduled_at = $request->scheduled_at;
        $siteVisit->status = 'scheduled'; // Keep as scheduled
        $siteVisit->is_rescheduled = true;
        $siteVisit->reschedule_count = ($siteVisit->reschedule_count ?? 0) + 1;
        $siteVisit->rescheduled_at = now();
        $siteVisit->rescheduled_by = $user->id;
        $siteVisit->reschedule_reason = $request->reason;
        // Reset verification status to pending (verification required after reschedule)
        $siteVisit->verification_status = 'pending';
        $siteVisit->verified_by = null;
        $siteVisit->verified_at = null;
        $siteVisit->rejection_reason = null;
        $siteVisit->save();

        // Create calling task 30 minutes before new scheduled time
        $taskService = app(TelecallerTaskService::class);
        $taskService->createCallTaskBeforeScheduled($siteVisit, $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Site visit rescheduled successfully. Verification required.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo', 'rescheduledBy']),
        ]);
    }

    /**
     * Verify a site visit (CRM/Admin only)
     */
    public function verify(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isCrm()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($siteVisit->verification_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Site visit already verified',
            ], 422);
        }

        if ($siteVisit->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Site visit must be completed before verification',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'lead_status' => 'required|in:hot,warm,cold,junk',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notes = $request->input('notes');
        $leadStatus = $request->input('lead_status');
        $siteVisit->verify($user->id, $notes, $leadStatus);

        return response()->json([
            'success' => true,
            'message' => 'Site visit verified successfully. This counts as a Site Visit achievement.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'verifiedBy']),
        ]);
    }

    /**
     * Reject a site visit (CRM/Admin only)
     */
    public function reject(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isCrm()) {
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

        $siteVisit->reject($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Site visit rejected',
            'data' => $siteVisit->fresh(['lead', 'creator', 'verifiedBy']),
        ]);
    }

    /**
     * Convert verified site visit to closer (deprecated - use requestCloser)
     */
    public function convertToCloser(Request $request, SiteVisit $siteVisit)
    {
        // Redirect to requestCloser for backward compatibility
        return $this->requestCloser($request, $siteVisit);
    }

    /**
     * Verify closer (CRM/Admin only)
     */
    public function verifyCloser(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isCrm()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($siteVisit->closer_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Closer already verified',
            ], 422);
        }

        if ($siteVisit->closer_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Closer must be pending before verification',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'lead_status' => 'required|in:hot,warm,cold,junk',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notes = $request->input('notes');
        $leadStatus = $request->input('lead_status');
        $siteVisit->verifyCloser($user->id, $notes, $leadStatus);

        return response()->json([
            'success' => true,
            'message' => 'Closer verified successfully. This counts as a Closer achievement.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'closerVerifiedBy']),
        ]);
    }

    /**
     * Reject closer (CRM/Admin only)
     */
    public function rejectCloser(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isCrm()) {
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

        if ($siteVisit->closer_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Closer must be pending before rejection',
            ], 422);
        }

        $siteVisit->rejectCloser($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Closer rejected',
            'data' => $siteVisit->fresh(['lead', 'creator', 'closerVerifiedBy']),
        ]);
    }

    /**
     * Request closer with proof photos
     */
    public function requestCloser(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $siteVisit->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Site visit must be verified before requesting closer
        if ($siteVisit->verification_status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Site visit must be verified before requesting closer.',
            ], 422);
        }

        // Check if already converted
        if ($siteVisit->closer_status !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Site visit is already converted to closer.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
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
                $filename = 'site-visits/closer-proof/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $proofPhotoPaths[] = $filename;
            }
        }

        try {
            $siteVisit->closer_status = 'pending';
            $siteVisit->converted_to_closer_at = now();
            $siteVisit->closer_request_proof_photos = $proofPhotoPaths;
            $siteVisit->save();

            return response()->json([
                'success' => true,
                'message' => 'Closer request submitted with proof photos. Awaiting verification.',
                'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark site visit as dead
     */
    public function markDead(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $siteVisit->created_by !== $user->id) {
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

        $siteVisit->markAsDead($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Site visit marked as dead successfully',
            'data' => $siteVisit->fresh(['lead', 'creator', 'markedDeadBy']),
        ]);
    }

    private function canAccessSiteVisit($user, SiteVisit $siteVisit): bool
    {
        if ($user->canViewAllLeads()) {
            return true;
        }

        if ($user->isSalesManager()) {
            return $siteVisit->created_by === $user->id;
        }

        return $siteVisit->assigned_to === $user->id || $siteVisit->created_by === $user->id;
    }
}

