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
use Illuminate\Support\Facades\Log;

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
        
        // Get sales executives (previously telecallers) for filter dropdown
        $telecallers = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->where('slug', Role::SALES_EXECUTIVE);
            })
            ->orderBy('name')
            ->get();

        return view('leads.index', compact('leads', 'statuses', 'telecallers'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Disable old form for sales executive and manager - use centralized form instead
        if ($user->isSalesExecutive() || $user->isSalesManager() || $user->isSalesHead()) {
            return redirect()
                ->route('leads.index')
                ->with('info', 'Old lead creation form is disabled. Please use the centralized lead requirement form by editing an existing lead or contact admin for new lead creation.');
        }
        
        // All active users for Assign dropdown (sare user jo system mein hain)
        $users = User::where('is_active', true)
            ->whereHas('role')
            ->with('role')
            ->orderBy('name')
            ->get();

        $projects = Project::where('is_active', true)->orderBy('name')->get();

        return view('leads.create', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        // Full form: name and phone required; all other fields optional (CRM and non-CRM)
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
            $validated['created_by'] = $user->id;
            $validated['status'] = 'new';
            
            // Handle preferred projects array - convert to JSON string (CRM and non-CRM)
            if (isset($validated['preferred_projects']) && is_array($validated['preferred_projects'])) {
                $validated['preferred_projects'] = json_encode($validated['preferred_projects']);
            }

            // Handle custom source
            if ($request->source === 'custom' && $request->has('custom_source')) {
                $validated['source'] = $request->custom_source;
            }
            unset($validated['custom_source']);

            // CRM default source when not provided
            if ($user->isCrm() && empty($validated['source'])) {
                $validated['source'] = 'crm_manual';
            }

            $lead = Lead::create($validated);

            // Assign lead if user selected (CRM and non-CRM); auto-creates calling task for assignee
            if ($request->filled('assigned_to')) {
                try {
                    $this->assignLead($lead, (int) $request->assigned_to, $user->id);
                } catch (\Exception $e) {
                    // Pusher/broadcast errors (e.g. 404 when not configured) must not fail lead creation
                    if (str_contains($e->getMessage(), 'Pusher') || str_contains($e->getMessage(), 'broadcast')) {
                        \Illuminate\Support\Facades\Log::warning('Lead assignment broadcast failed; lead and assignment saved.', ['error' => $e->getMessage()]);
                    } else {
                        throw $e;
                    }
                }
            }

            DB::commit();

            if ($user->isCrm()) {
                $msg = $request->filled('assigned_to')
                    ? "Lead '{$lead->name}' created successfully and assigned. A calling task has been created for the assigned user."
                    : "Lead '{$lead->name}' created successfully. You can now fill detailed requirements using the centralized form.";
                return redirect()
                    ->route('leads.show', $lead->id)
                    ->with('success', $msg);
            }

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
        try {
            $user = $request->user();
            
            // Explicitly load user role to ensure it's available in the view
            if ($user && !$user->relationLoaded('role')) {
                $user->load('role');
            }

            // Check access permissions
            if (!$this->canAccessLead($user, $lead)) {
                abort(403, 'You do not have permission to view this lead.');
            }

            $layout = 'layouts.app';
            if ($user) {
                if ($user->isAdmin() || $user->isCrm()) {
                    $layout = 'layouts.app';
                } elseif ($user->isSalesHead() && !$user->isAdmin() && !$user->isCrm()) {
                    $layout = 'sales-head.layout';
                } elseif ($user->isSalesManager()) {
                    $layout = 'sales-manager.layout';
                } elseif ($user->isAssistantSalesManager()) {
                    $layout = 'sales-head.layout';
                } elseif ($user->isSalesExecutive()) {
                    $layout = 'telecaller.layout';
                } elseif ($user->relationLoaded('role') && $user->role) {
                    switch ($user->role->slug) {
                        case \App\Models\Role::SALES_MANAGER:
                            $layout = 'sales-manager.layout';
                            break;
                        case \App\Models\Role::ASSISTANT_SALES_MANAGER:
                            $layout = 'sales-head.layout';
                            break;
                        case \App\Models\Role::SALES_EXECUTIVE:
                            $layout = 'telecaller.layout';
                            break;
                        default:
                            $layout = 'layouts.app';
                    }
                }
            }

            // Load all relationships
            $lead->load([
                'creator',
                'assignments.assignedTo',
                'assignments.assignedBy',
                'activeAssignments.assignedTo',
                'formFieldValues',
                'callLogs' => function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orderBy('start_time', 'asc');
                },
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
            
            // Calculate response time data
            $responseTimeData = $this->calculateResponseTime($lead, $user);

            return view('leads.show', compact('lead', 'timeline', 'responseTimeData', 'layout'));
        } catch (\Exception $e) {
            Log::error('Error loading lead details: ' . $e->getMessage(), [
                'lead_id' => $lead->id ?? null,
                'user_id' => $request->user()?->id,
                'error' => $e->getTraceAsString(),
            ]);

            // Return view with error message instead of throwing
            return view('leads.show', [
                'lead' => $lead,
                'timeline' => [],
                'responseTimeData' => null,
                'layout' => $layout ?? 'layouts.app',
                'error' => 'An error occurred while loading lead details. Please refresh the page.',
            ]);
        }
    }

    public function edit(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Check access permissions
        if (!$this->canAccessLead($user, $lead)) {
            abort(403, 'You do not have permission to edit this lead.');
        }

        // Load lead with form field values
        $lead->load('formFieldValues');

        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Check access permissions
        if (!$this->canAccessLead($user, $lead)) {
            abort(403, 'You do not have permission to update this lead.');
        }

        $userRole = $user->role->slug;

        // Basic lead fields validation (name and phone - always required)
        $validationRules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ];

        // Get visible fields for user's role
        $visibleFields = \App\Models\LeadFormField::active()
            ->visibleToRole($userRole)
            ->get();

        // Validate fields dynamically
        foreach ($visibleFields as $field) {
            $rule = [];
            
            if ($field->is_required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }
            
            // Add field type validation
            switch ($field->field_type) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                case 'time':
                    $rule[] = 'date_format:H:i';
                    break;
            }
            
            $validationRules[$field->field_key] = $rule;
        }

        // Special validation for conditional fields
        if ($request->has('final_status') && $request->final_status === 'Follow Up') {
            $validationRules['follow_up_date'] = ['required', 'date'];
            $validationRules['follow_up_time'] = ['required', 'date_format:H:i'];
        }

        $validated = $request->validate($validationRules);

        DB::beginTransaction();
        try {
            // Update basic lead fields (name and phone)
            $lead->name = $validated['name'];
            $lead->phone = $validated['phone'];
            
            // Save dynamic form field values
            foreach ($visibleFields as $field) {
                if ($request->has($field->field_key)) {
                    $value = $request->input($field->field_key);
                    // Only save if value is not empty or if it's a required field
                    if (!empty($value) || $field->is_required) {
                        $lead->setFormFieldValue($field->field_key, $value ?? '', $user->id);
                    }
                }
            }

            // Update tracking flags based on role
            if ($userRole === 'telecaller') {
                $lead->form_filled_by_telecaller = true;
            } elseif ($userRole === 'sales_executive') {
                $lead->form_filled_by_executive = true;
            } elseif (in_array($userRole, ['sales_manager', 'sales_head'])) {
                $lead->form_filled_by_manager = true;
            }

            $lead->save();

            // Handle follow-up task creation
            if (isset($validated['final_status']) && $validated['final_status'] === 'Follow Up' 
                && isset($validated['follow_up_date']) && isset($validated['follow_up_time'])) {
                
                $followUpDateTime = \Carbon\Carbon::parse($validated['follow_up_date'] . ' ' . $validated['follow_up_time']);
                
                // Create follow-up task
                $taskService = app(\App\Services\TelecallerTaskService::class);
                $taskService->createFollowUpTask(
                    $lead,
                    $user->id,
                    $validated['follow_up_date'],
                    $validated['follow_up_time'],
                    $user->id
                );
            }

            DB::commit();

            $roleMessage = [
                'telecaller' => 'Basic requirements saved. Lead moved to Executive for review.',
                'sales_executive' => 'Lead status updated.' . (isset($validated['final_status']) && $validated['final_status'] === 'Follow Up' ? ' Follow-up task created.' : ''),
                'sales_manager' => 'Lead requirements finalized.',
                'crm' => 'All lead requirements saved successfully.',
                'admin' => 'Lead requirements updated successfully.',
                'sales_head' => 'Lead requirements updated successfully.',
            ];

            return redirect()
                ->route('leads.show', $lead->id)
                ->with('success', $roleMessage[$userRole] ?? 'Lead requirements updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to update lead: ' . $e->getMessage()])
                ->withInput();
        }
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
            'formFieldValues',
            'prospects' => function($query) {
                $query->whereNotNull('lead_score')
                      ->orderBy('lead_score', 'desc')
                      ->with(['telecaller.role', 'assignedManager.role', 'manager.role', 'verifiedBy.role', 'interestedProjects']);
            }
        ]);

        // Get the highest lead score from prospects
        $leadScore = $lead->prospects->max('lead_score');
        
        // Get form fields array
        $formFields = $lead->getFormFieldsArray();

        return response()->json([
            'data' => $lead,
            'lead_score' => $leadScore,
            'form_fields' => $formFields
        ]);
    }

    /**
     * Calculate response time for current user
     */
    private function calculateResponseTime(Lead $lead, $user): array
    {
        $assignedAt = null;
        $calledAt = null;
        $responseTime = null;
        
        // Get assignment time for current user
        $assignment = $lead->activeAssignments()
            ->where('assigned_to', $user->id)
            ->first();
        
        if ($assignment) {
            $assignedAt = $assignment->assigned_at;
        }
        
        // Get call time - check CallLog first, then CrmAssignment, then Lead.last_contacted_at
        $callLog = $lead->callLogs()
            ->where('user_id', $user->id)
            ->orderBy('start_time', 'asc')
            ->first();
        
        if ($callLog && $callLog->start_time) {
            $calledAt = $callLog->start_time;
        } else {
            // Check CrmAssignment
            $crmAssignment = \App\Models\CrmAssignment::where('lead_id', $lead->id)
                ->where('assigned_to', $user->id)
                ->whereNotNull('called_at')
                ->orderBy('called_at', 'asc')
                ->first();
            
            if ($crmAssignment && $crmAssignment->called_at) {
                $calledAt = $crmAssignment->called_at;
            } elseif ($lead->last_contacted_at) {
                $calledAt = $lead->last_contacted_at;
            }
        }
        
        // Calculate response time
        if ($assignedAt && $calledAt && $calledAt->gt($assignedAt)) {
            $responseTime = $assignedAt->diffInMinutes($calledAt);
        }
        
        return [
            'assigned_at' => $assignedAt,
            'called_at' => $calledAt,
            'response_time_minutes' => $responseTime,
            'has_responded' => $calledAt !== null,
        ];
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
            
            // Check if lead is directly assigned to this sales manager
            if ($lead->activeAssignments()->where('assigned_to', $user->id)->where('is_active', true)->exists()) {
                return true;
            }
            
            // Check if lead is assigned to team members
            if ($teamMemberIds->isNotEmpty() && $lead->activeAssignments()->whereIn('assigned_to', $teamMemberIds)->where('is_active', true)->exists()) {
                return true;
            }
            
            // Check if lead came from verified prospects of team members
            if ($teamMemberIds->isNotEmpty()) {
                return $lead->prospects()
                    ->whereIn('telecaller_id', $teamMemberIds)
                    ->whereIn('verification_status', ['verified', 'approved'])
                    ->exists();
            }
            
            return false;
        }

        // Sales Executive and Assistant Sales Manager can see assigned leads or leads from their prospects
        if ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            return $lead->activeAssignments()->where('assigned_to', $user->id)->exists() ||
                   $lead->prospects()->where('telecaller_id', $user->id)->exists();
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

