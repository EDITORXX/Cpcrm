<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Project;
use App\Models\LeadAssignment;
use App\Models\Prospect;
use App\Models\Role;
use App\Events\LeadAssigned;
use App\Services\LeadActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Lead::with(['creator', 'activeAssignments.assignedTo']);

        // Sales Head specific filtering - only show verified prospects, verified site visits, and closed leads
        if ($user->isSalesHead()) {
            // Get team member IDs
            $teamMemberIds = $user->getAllTeamMemberIds();
            
            // Only show leads assigned to team members
            if (!empty($teamMemberIds)) {
                $query->whereHas('activeAssignments', function ($q) use ($teamMemberIds) {
                    $q->whereIn('assigned_to', $teamMemberIds);
                });
            } else {
                // If no team members, show empty
                $query->whereRaw('1 = 0');
            }
            
            // Filter leads that are:
            // 1. Verified prospects (status = 'verified_prospect')
            // 2. Verified site visits (status = 'visit_done' or 'revisited_completed' AND has verified site visit)
            // 3. Closed leads (status = 'closed' or 'dead')
            
            $query->where(function($q) {
                // Verified prospects
                $q->where('status', 'verified_prospect')
                // Verified site visits (visit_done or revisited_completed with verified site visit)
                ->orWhere(function($subQ) {
                    $subQ->whereIn('status', ['visit_done', 'revisited_completed'])
                         ->whereHas('siteVisits', function($visitQ) {
                             $visitQ->where('status', 'completed')
                                    ->whereNotNull('verified_at');
                         });
                })
                // Closed leads
                ->orWhereIn('status', ['closed', 'dead']);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by lead type (Prospect, Visit, Revisit, Meeting, Closer)
        if ($request->has('lead_type_filter') && $request->lead_type_filter) {
            $type = $request->lead_type_filter;
            
            if ($type === 'prospect') {
                // Only verified prospects - leads with status verified_prospect OR leads with verified/approved prospects
                $query->where(function($q) {
                    $q->where('status', 'verified_prospect')
                      ->orWhereHas('prospects', function($subQ) {
                          $subQ->whereIn('verification_status', ['verified', 'approved']);
                      });
                });
            } elseif ($type === 'visit') {
                // Site visits with lead_type = 'New Visit'
                $query->where(function($q) {
                    $q->whereIn('status', ['visit_scheduled', 'visit_done'])
                      ->orWhereHas('siteVisits', function($subQ) {
                          $subQ->where('lead_type', 'New Visit');
                      });
                });
            } elseif ($type === 'revisit') {
                // Revisits with lead_type = 'Revisited'
                $query->where(function($q) {
                    $q->whereIn('status', ['revisited_scheduled', 'revisited_completed'])
                      ->orWhereHas('siteVisits', function($subQ) {
                          $subQ->where('lead_type', 'Revisited');
                      });
                });
            } elseif ($type === 'meeting') {
                // Meetings - leads with meeting_scheduled or meeting_completed status OR leads that have meetings
                $query->where(function($q) {
                    $q->whereIn('status', ['meeting_scheduled', 'meeting_completed'])
                      ->orWhereHas('meetings');
                });
            } elseif ($type === 'closer') {
                // Closer requests - site visits with closer_status pending or not null
                $query->whereHas('siteVisits', function($subQ) {
                    $subQ->where(function($closerQ) {
                        $closerQ->where('closer_status', 'pending')
                                ->orWhereNotNull('closer_status');
                    });
                });
            }
        }

        // Filter by user (telecaller)
        if ($request->has('user_id') && $request->user_id) {
            $query->whereHas('activeAssignments', function($q) use ($request) {
                $q->where('assigned_to', $request->user_id);
            });
        }

        $leads = $query->latest()->paginate(15);
        $statuses = ['new', 'connected', 'verified_prospect', 'meeting_scheduled', 'meeting_completed', 'visit_scheduled', 'visit_done', 'revisited_scheduled', 'revisited_completed', 'closed', 'dead', 'on_hold'];
        
        // Get telecallers for filter dropdown
        $telecallers = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->where('slug', Role::TELECALLER);
            })
            ->orderBy('name')
            ->get();

        return view('leads.index', compact('leads', 'statuses', 'telecallers'));
    }

    public function create()
    {
        $users = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', ['sales_manager', 'sales_executive', 'telecaller']);
            })
            ->with('role')
            ->get();

        $projects = Project::where('is_active', true)->orderBy('name')->get();

        return view('leads.create', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'preferred_location' => 'nullable|string|max:255',
            'preferred_size' => 'nullable|string|max:255',
            'preferred_projects' => 'nullable|array',
            'preferred_projects.*' => 'nullable|exists:projects,id',
            'use_end_use' => 'nullable|string|in:End User,2nd Investments',
            'budget' => 'nullable|string|in:Under ₹1 Cr,₹1.1 Cr – ₹2 Cr,Above ₹2 Cr',
            'source' => 'nullable|in:website,referral,walk_in,call,social_media,other,custom',
            'custom_source' => 'required_if:source,custom|nullable|string|max:255',
            'property_type' => 'nullable|in:apartment,villa,plot,commercial,other',
            'possession_status' => 'nullable|string|in:Ready to Move,Under Construction',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $validated['created_by'] = $request->user()->id;
            $validated['status'] = 'new';

            // Handle preferred projects array - convert to JSON string
            if (isset($validated['preferred_projects']) && is_array($validated['preferred_projects'])) {
                $validated['preferred_projects'] = json_encode($validated['preferred_projects']);
            }

            // Handle custom source
            if ($request->source === 'custom' && $request->has('custom_source')) {
                $validated['source'] = $request->custom_source;
            }
            unset($validated['custom_source']);

            $lead = Lead::create($validated);

            // Assign lead if user selected
            if ($request->has('assigned_to') && $request->assigned_to) {
                $this->assignLead($lead, $request->assigned_to, $request->user()->id);
            }

            DB::commit();

            return redirect()
                ->route('leads.index')
                ->with('success', "Lead '{$lead->name}' created successfully" . ($request->assigned_to ? ' and assigned.' : '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to create lead: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Check access permissions
        if (!$this->canAccessLead($user, $lead)) {
            abort(403, 'You do not have permission to view this lead.');
        }

        // Load all relationships
        $lead->load([
            'creator',
            'assignments.assignedTo',
            'assignments.assignedBy',
            'activeAssignments.assignedTo',
            'siteVisits.creator',
            'siteVisits.assignedTo',
            'siteVisits.verifiedBy',
            'followUps.creator',
            'meetings.creator',
            'meetings.assignedTo',
            'meetings.verifiedBy',
            'prospects.createdBy',
            'prospects.verifiedBy',
            'prospects.interestedProjects',
            'callLogs.user',
            'tasks.assignedTo',
            'markedDeadBy',
            'verifiedBy',
        ]);

        // Get activity timeline
        $activityService = new LeadActivityService();
        $timeline = $activityService->getTimeline($lead);

        return view('leads.show', compact('lead', 'timeline'));
    }

    public function shortDetails(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Check access permissions
        if (!$this->canAccessLead($user, $lead)) {
            return response()->json(['error' => 'You do not have permission to view this lead.'], 403);
        }

        // Load necessary relationships including prospects with lead_score and all manager relationships
        $lead->load([
            'activeAssignments.assignedTo.role',
            'creator',
            'prospects' => function($query) {
                $query->whereNotNull('lead_score')
                      ->orderBy('lead_score', 'desc')
                      ->with(['telecaller.role', 'assignedManager.role', 'manager.role', 'verifiedBy.role', 'interestedProjects']);
            }
        ]);

        // Get the highest lead score from prospects
        $leadScore = $lead->prospects->max('lead_score');

        return response()->json([
            'data' => $lead,
            'lead_score' => $leadScore
        ]);
    }

    private function canAccessLead($user, Lead $lead): bool
    {
        // Admin and CRM can see all leads
        if ($user->isAdmin() || $user->isCrm()) {
            return true;
        }

        // Sales Head can see leads from their team
        if ($user->isSalesHead()) {
            $teamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($teamMemberIds)) {
                return $lead->activeAssignments()->whereIn('assigned_to', $teamMemberIds)->exists() ||
                       $lead->prospects()->whereIn('telecaller_id', $teamMemberIds)->exists();
            }
            return false;
        }

        // Sales Manager can see leads from their team's verified prospects
        if ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            return $lead->prospects()
                ->whereIn('telecaller_id', $teamMemberIds)
                ->whereIn('verification_status', ['verified', 'approved'])
                ->exists() ||
                $lead->activeAssignments()->whereIn('assigned_to', $teamMemberIds)->exists();
        }

        // Telecaller and Sales Executive can see assigned leads
        if ($user->isTelecaller() || $user->isSalesExecutive()) {
            return $lead->activeAssignments()->where('assigned_to', $user->id)->exists();
        }

        return false;
    }

    private function assignLead(Lead $lead, int $assignedTo, int $assignedBy): void
    {
        // Deactivate existing assignments
        $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

        // Create new assignment
        LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'assignment_type' => 'primary',
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Fire event
        event(new LeadAssigned($lead, $assignedTo, $assignedBy));
    }
}

