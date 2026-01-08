<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SalesManagerProfile;
use App\Models\Prospect;
use App\Models\Target;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesManagerController extends Controller
{
    /**
     * Get profile data with team members
     */
    public function getProfile(Request $request)
    {
        $user = $request->user()->load('role', 'manager', 'salesManagerProfile');
        
        // Get team members (telecallers and sales executives under this manager)
        $teamMembers = User::where('manager_id', $user->id)
            ->with(['role', 'telecallerProfile'])
            ->get()
            ->map(function($member) {
                // Get today's stats for the team member
                $todayProspects = Prospect::where('telecaller_id', $member->id)
                    ->whereDate('created_at', Carbon::today())
                    ->count();
                
                $isAbsent = false;
                $absentReason = null;
                
                if ($member->telecallerProfile) {
                    $isAbsent = $member->telecallerProfile->is_absent ?? false;
                    $absentReason = $member->telecallerProfile->absent_reason ?? null;
                }
                
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'role' => $member->role->name ?? '-',
                    'profile_picture' => $member->profile_picture_url,
                    'is_active' => $member->is_active,
                    'is_absent' => $isAbsent,
                    'absent_reason' => $absentReason,
                    'joined_at' => $member->created_at ? $member->created_at->format('d M Y') : '-',
                    'today_prospects' => $todayProspects,
                ];
            });
        
        // Get activity history (last 10 activities)
        $activityHistory = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['action', 'ip_address', 'created_at']);

        // Get team stats
        $teamStats = [
            'total_members' => $teamMembers->count(),
            'active_members' => $teamMembers->where('is_active', true)->count(),
            'available_members' => $teamMembers->filter(function($member) {
                return !($member['is_absent'] ?? false);
            })->count(),
            'today_prospects' => $teamMembers->sum('today_prospects'),
        ];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture_url,
                'role' => $user->role->name ?? 'Sales Manager',
                'manager' => $user->manager ? $user->manager->name : null,
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
            ],
            'team_members' => $teamMembers,
            'team_stats' => $teamStats,
            'activity_history' => $activityHistory->map(function ($log) {
                return [
                    'action' => $log->action,
                    'ip' => $log->ip_address,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Update profile (name, email, phone)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $user = $user->fresh(['role', 'manager']);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->name ?? 'Sales Manager',
                'manager' => $user->manager ? $user->manager->name : 'Not Assigned',
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
            ],
        ]);
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:2048', // Max 2MB
            ]);

            $user = $request->user();

            // Delete old profile picture if exists
            if ($user->profile_picture) {
                $oldPath = $user->profile_picture;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Store new profile picture
            $file = $request->file('profile_picture');
            $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');

            // Update user profile picture
            $user->update([
                'profile_picture' => $path,
            ]);

            // Refresh to get updated URL
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'profile_picture' => $user->profile_picture_url,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Get team member details
     */
    public function getTeamMemberDetails(Request $request, $memberId)
    {
        $manager = $request->user();
        
        $member = User::where('id', $memberId)
            ->where('manager_id', $manager->id)
            ->with(['role', 'telecallerProfile'])
            ->firstOrFail();

        // Get member's performance stats
        $todayProspects = Prospect::where('telecaller_id', $member->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        $weekProspects = Prospect::where('telecaller_id', $member->id)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        
        $monthProspects = Prospect::where('telecaller_id', $member->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return response()->json([
            'success' => true,
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'role' => $member->role->name,
                'profile_picture' => $member->profile_picture_url,
                'is_active' => $member->is_active,
                'is_absent' => $member->telecallerProfile->is_absent ?? false,
                'absent_reason' => $member->telecallerProfile->absent_reason ?? null,
                'absent_until' => $member->telecallerProfile->absent_until ?? null,
                'performance' => [
                    'today_prospects' => $todayProspects,
                    'week_prospects' => $weekProspects,
                    'month_prospects' => $monthProspects,
                ],
            ],
        ]);
    }

    /**
     * Get team performance overview
     */
    public function getTeamPerformance(Request $request)
    {
        $manager = $request->user();
        
        // Get all team members
        $teamMembers = User::where('manager_id', $manager->id)->pluck('id');
        
        // Get prospects created by team today
        $todayProspects = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Get prospects created by team this week
        $weekProspects = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        
        // Get prospects created by team this month
        $monthProspects = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        // Get top performers
        $topPerformers = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('telecaller_id, COUNT(*) as prospect_count')
            ->groupBy('telecaller_id')
            ->orderByDesc('prospect_count')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $user = User::find($item->telecaller_id);
                return [
                    'name' => $user ? $user->name : 'Unknown',
                    'prospect_count' => $item->prospect_count,
                ];
            });

        return response()->json([
            'success' => true,
            'performance' => [
                'today_prospects' => $todayProspects,
                'week_prospects' => $weekProspects,
                'month_prospects' => $monthProspects,
                'top_performers' => $topPerformers,
            ],
        ]);
    }

    /**
     * Get achievements (target vs achieved) for sales manager
     */
    public function getAchievements(Request $request)
    {
        $user = $request->user();
        
        // Get current month's target
        $target = Target::where('user_id', $user->id)
            ->whereYear('target_month', Carbon::now()->year)
            ->whereMonth('target_month', Carbon::now()->month)
            ->first();

        if (!$target) {
            return response()->json([
                'success' => true,
                'message' => 'No target set for current month',
                'meetings' => ['target' => 0, 'achieved' => 0, 'percentage' => 0],
                'site_visits' => ['target' => 0, 'achieved' => 0, 'percentage' => 0],
                'closers' => ['target' => 0, 'achieved' => 0, 'percentage' => 0],
            ]);
        }

        return response()->json([
            'success' => true,
            'meetings' => $target->getAchievementProgress('meetings'),
            'site_visits' => $target->getAchievementProgress('visits'),
            'closers' => $target->getAchievementProgress('closers'),
        ]);
    }

    /**
     * Get team prospects for Sales Manager (also accessible by Admin, CRM, Sales Head)
     */
    public function getProspects(Request $request)
    {
        $user = $request->user();
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Query prospects
        $query = Prospect::with(['telecaller', 'manager', 'lead', 'createdBy']);
        
        // Role-based filtering
        if ($user->isAdmin() || $user->isCrm()) {
            // Admin and CRM can see all prospects
            // No additional filtering needed
        } elseif ($user->isSalesHead()) {
            // Sales Head can see all prospects from their entire team hierarchy
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->where(function($q) use ($allTeamMemberIds, $user) {
                    $q->whereIn('telecaller_id', $allTeamMemberIds)
                      ->orWhere('manager_id', $user->id)
                      ->orWhereIn('manager_id', $allTeamMemberIds);
                });
            } else {
                // No team members, show only their own
                $query->where('manager_id', $user->id);
            }
        } elseif ($user->isSalesManager()) {
            // Sales Manager can see prospects from their direct team members
            $teamMemberIds = $user->teamMembers()->pluck('id');
            
            $query->where(function($q) use ($teamMemberIds, $user) {
                // Prospects created by team members
                if ($teamMemberIds->isNotEmpty()) {
                    $q->whereIn('telecaller_id', $teamMemberIds);
                }
                // OR prospects assigned to this manager
                $q->orWhere('manager_id', $user->id);
            });
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
        
        // Get base query for counts (before search filter)
        $baseQuery = clone $query;
        
        // Filter by verification status
        if ($request->has('verification_status') && $request->verification_status !== 'all') {
            $status = $request->verification_status;
            // Map frontend values to database values
            if ($status === 'pending_verification') {
                $query->whereIn('verification_status', ['pending', 'pending_verification']);
            } elseif ($status === 'verified') {
                $query->whereIn('verification_status', ['verified', 'approved']);
            } else {
                $query->where('verification_status', $status);
            }
        }
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('preferred_location', 'like', "%{$search}%");
            });
        }
        
        $prospects = $query->latest()->paginate($request->get('per_page', 15));
        
        // Calculate counts for status filters (without search filter)
        // Note: Database uses 'pending' but frontend expects 'pending_verification'
        // Database uses 'approved' but frontend expects 'verified'
        $counts = [
            'all' => (clone $baseQuery)->count(),
            'pending_verification' => (clone $baseQuery)->whereIn('verification_status', ['pending', 'pending_verification'])->count(),
            'verified' => (clone $baseQuery)->whereIn('verification_status', ['verified', 'approved'])->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', 'rejected')->count(),
        ];
        
        return response()->json([
            ...$prospects->toArray(),
            'counts' => $counts
        ]);
    }

    /**
     * Create prospect (Manager can create directly)
     */
    public function createProspect(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'budget' => 'nullable|numeric',
            'preferred_location' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'purpose' => 'nullable|in:end_user,investment',
            'possession' => 'nullable|string|max:255',
            'remark' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['manager_id'] = $user->id;
        $data['created_by'] = $user->id;
        $data['verification_status'] = 'pending';

        // If lead_id provided, link it
        if (isset($data['lead_id'])) {
            $lead = Lead::find($data['lead_id']);
            if ($lead) {
                $data['telecaller_id'] = null; // Manager created, not from telecaller
            }
        }

        $prospect = Prospect::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Prospect created successfully',
            'data' => $prospect->load(['manager', 'lead']),
        ], 201);
    }

    /**
     * Get tasks assigned to current sales manager/executive
     */
    public function getTasks(Request $request)
    {
        $user = $request->user();
        
        // Query tasks assigned to this user
        $query = Task::where('assigned_to', $user->id)
            ->with(['lead.prospects', 'assignedTo', 'creator']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($leadQ) use ($search) {
                      $leadQ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $tasks = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));

        // Add overdue flag and enrich data
        $tasks->getCollection()->transform(function($task) {
            $task->is_overdue = $task->isOverdue();
            $task->scheduled_at_formatted = $task->scheduled_at ? $task->scheduled_at->format('Y-m-d H:i:s') : null;
            
            // Get prospect lead_status if available
            if ($task->lead) {
                $prospect = $task->lead->prospects()->latest()->first();
                if ($prospect) {
                    $task->lead->lead_status = $prospect->lead_status;
                }
            }
            
            return $task;
        });

        // Return paginated response
        return response()->json([
            'success' => true,
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'last_page' => $tasks->lastPage(),
        ]);
    }

    /**
     * Get single task details with lead and prospect data
     */
    public function getTask(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Verify task is assigned to current user
        if ($task->assigned_to !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task',
            ], 403);
        }

        $task->load(['lead.prospects', 'assignedTo', 'creator']);
        $task->is_overdue = $task->isOverdue();
        $task->scheduled_at_formatted = $task->scheduled_at ? $task->scheduled_at->format('Y-m-d H:i:s') : null;

        // Get prospect lead_status if available
        if ($task->lead) {
            $prospect = $task->lead->prospects()->latest()->first();
            if ($prospect) {
                $task->lead->lead_status = $prospect->lead_status;
                $task->prospect = $prospect;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    /**
     * Update lead details and prospect from task form with verify/reject actions
     */
    public function updateLeadFromTask(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Verify task is assigned to current user
        if ($task->assigned_to !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task',
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:verify,reject',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'budget' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'purpose' => 'nullable|in:end_user,investment',
            'possession' => 'nullable|string|max:255',
            'lead_status' => 'required|in:hot,warm,cold,junk',
            'manager_remark' => 'nullable|string',
            'interested_projects' => 'required_if:action,verify|array|min:1',
            'interested_projects.*' => 'exists:interested_project_names,id',
        ]);

        DB::beginTransaction();
        try {
            $action = $request->input('action');
            $lead = $task->lead;
            $prospect = null;

            // Get or create prospect
            if ($task->lead_id && $lead) {
                $prospect = $lead->prospects()->latest()->first();
                
                // Update lead
                $lead->update([
                    'name' => $request->input('customer_name'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'address' => $request->input('address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'pincode' => $request->input('pincode'),
                    'preferred_location' => $request->input('preferred_location'),
                    'preferred_size' => $request->input('size'),
                    'budget' => $request->input('budget'),
                    'investment' => $request->input('budget'),
                ]);
            }

            // Update or create prospect
            $prospectData = [
                'customer_name' => $request->input('customer_name'),
                'phone' => $request->input('phone'),
                'budget' => $request->input('budget'),
                'preferred_location' => $request->input('preferred_location'),
                'size' => $request->input('size'),
                'purpose' => $request->input('purpose'),
                'possession' => $request->input('possession'),
                'lead_status' => $request->input('lead_status'),
                'manager_remark' => $request->input('manager_remark'),
            ];

            if ($action === 'verify') {
                $prospectData['verification_status'] = 'verified';
                $prospectData['verified_at'] = now();
                $prospectData['verified_by'] = $user->id;
                
                if ($prospect) {
                    $prospect->update($prospectData);
                } else if ($lead) {
                    $prospectData['lead_id'] = $lead->id;
                    $prospectData['manager_id'] = $user->id;
                    $prospectData['assigned_manager'] = $user->id;
                    $prospectData['created_by'] = $user->id;
                    $prospect = Prospect::create($prospectData);
                }
                
                // Sync interested projects
                if ($prospect && $request->has('interested_projects')) {
                    $prospect->interestedProjects()->sync($request->input('interested_projects'));
                }
            } else { // reject
                $prospectData['verification_status'] = 'rejected';
                $prospectData['rejection_reason'] = $request->input('manager_remark') ?: 'Rejected by manager';
                
                if ($prospect) {
                    $prospect->update($prospectData);
                } else if ($lead) {
                    $prospectData['lead_id'] = $lead->id;
                    $prospectData['manager_id'] = $user->id;
                    $prospectData['assigned_manager'] = $user->id;
                    $prospectData['created_by'] = $user->id;
                    $prospect = Prospect::create($prospectData);
                }
            }

            // Mark task as completed
            $task->markAsCompleted();

            DB::commit();

            $message = $action === 'verify' 
                ? 'Prospect verified and task marked as completed successfully'
                : 'Prospect rejected and task marked as completed successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $task->fresh(['lead.prospects']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating prospect from task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
