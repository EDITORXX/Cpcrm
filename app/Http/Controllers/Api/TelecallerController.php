<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelecallerService;
use App\Models\User;
use App\Models\Task;
use App\Models\CrmAssignment;
use App\Models\TelecallerProfile;
use App\Models\UserAvailability;
use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\TelecallerTask;
use App\Models\Prospect;
use App\Models\AppNotification;
use App\Services\TelecallerTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class TelecallerController extends Controller
{
    protected $telecallerService;
    protected $taskService;

    public function __construct(TelecallerService $telecallerService, TelecallerTaskService $taskService)
    {
        $this->telecallerService = $telecallerService;
        $this->taskService = $taskService;
    }

    /**
     * Login for telecaller
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->with('role')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is telecaller
        if (!$user->isTelecaller()) {
            throw ValidationException::withMessages([
                'email' => ['This account is not authorized for telecaller access.'],
            ]);
        }

        $token = $user->createToken('telecaller-token')->plainTextToken;

        // Mark attendance for telecaller
        $this->markTelecallerAttendance($user);

        return response()->json([
            'user' => $user->load('role', 'manager'),
            'token' => $token,
        ]);
    }

    /**
     * Mark telecaller attendance on login
     */
    private function markTelecallerAttendance(User $user): void
    {
        // Update UserAvailability - mark as online
        $availability = UserAvailability::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_online' => false,
                'timezone' => 'Asia/Kolkata',
                'current_day_leads' => 0,
                'is_available' => false,
            ]
        );
        
        $availability->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
        $availability->updateAvailability();

        // Update TelecallerProfile - mark as present (not absent)
        $profile = TelecallerProfile::firstOrCreate(['user_id' => $user->id]);
        if ($profile->is_absent) {
            $profile->update([
                'is_absent' => false,
                'absent_reason' => null,
                'absent_until' => null,
            ]);
        }
    }

    /**
     * Get current user (whoami)
     */
    public function whoami(Request $request)
    {
        return response()->json($request->user()->load('role', 'manager'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        $telecallerId = $request->user()->id;
        $stats = $this->telecallerService->getStats($telecallerId);

        return response()->json($stats);
    }

    /**
     * Get top performers
     */
    public function getTopPerformers(Request $request)
    {
        $limit = $request->input('limit', 1);
        $performers = $this->telecallerService->getTopPerformers($limit);

        return response()->json([
            'top_performers' => $performers,
        ]);
    }

    /**
     * Get calling queue
     */
    public function getCallingQueue(Request $request)
    {
        $telecallerId = $request->user()->id;
        $queue = $this->telecallerService->getCallingQueue($telecallerId);

        return response()->json([
            'queue' => $queue,
        ]);
    }

    /**
     * Get completed calls
     */
    public function getCompletedCalls(Request $request)
    {
        $telecallerId = $request->user()->id;
        
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $calls = $this->telecallerService->getCompletedCalls($telecallerId, $filters);

        return response()->json([
            'calls' => $calls,
        ]);
    }

    /**
     * Get follow-up calls
     */
    public function getFollowUpCalls(Request $request)
    {
        $telecallerId = $request->user()->id;
        $calls = $this->telecallerService->getFollowUpCalls($telecallerId);

        return response()->json([
            'calls' => $calls,
        ]);
    }

    /**
     * Get CNP calls
     */
    public function getCnpCalls(Request $request)
    {
        $telecallerId = $request->user()->id;
        $calls = $this->telecallerService->getCnpCalls($telecallerId);

        return response()->json([
            'calls' => $calls,
        ]);
    }

    /**
     * Get prospects
     */
    public function getProspects(Request $request)
    {
        try {
            $telecallerId = $request->user()->id;
            $status = $request->input('status', 'pending'); // pending, approved, rejected, all
            
            $query = Prospect::where('telecaller_id', $telecallerId)
                ->with(['lead', 'manager', 'verifiedBy', 'telecaller'])
                ->latest('created_at');

            // Filter by verification status
            if ($status && $status !== 'all') {
                // Map frontend status to database values
                // Database uses: pending, pending_verification, approved, verified, rejected
                $statusMap = [
                    'pending' => ['pending', 'pending_verification'],
                    'approved' => ['approved', 'verified'],
                    'verified' => ['approved', 'verified'], // Alias
                    'rejected' => 'rejected',
                ];
                $dbStatus = $statusMap[$status] ?? $status;
                if (is_array($dbStatus)) {
                    $query->whereIn('verification_status', $dbStatus);
                } else {
                    $query->where('verification_status', $dbStatus);
                }
            }

            $perPage = $request->get('per_page', 20);
            $prospects = $query->paginate($perPage);

            $formattedProspects = $prospects->map(function($prospect) {
                return [
                    'id' => $prospect->id,
                    'lead_id' => $prospect->lead_id,
                    'customer_name' => $prospect->customer_name,
                    'phone' => $prospect->phone,
                    'budget' => $prospect->budget,
                    'preferred_location' => $prospect->preferred_location,
                    'size' => $prospect->size,
                    'purpose' => $prospect->purpose,
                    'possession' => $prospect->possession,
                    'remark' => $prospect->remark,
                    'verification_status' => $prospect->verification_status,
                    'manager_name' => $prospect->manager->name ?? 'Not Assigned',
                    'manager_id' => $prospect->manager_id,
                    'verified_by_name' => $prospect->verifiedBy->name ?? null,
                    'verified_at' => $prospect->verified_at ? $prospect->verified_at->format('Y-m-d H:i:s') : null,
                    'rejection_reason' => $prospect->rejection_reason,
                    'manager_remark' => $prospect->manager_remark,
                    'created_at' => $prospect->created_at ? $prospect->created_at->format('Y-m-d H:i:s') : null,
                    'lead_name' => $prospect->lead->name ?? '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedProspects->values()->all(),
                'pagination' => [
                    'current_page' => $prospects->currentPage(),
                    'per_page' => $prospects->perPage(),
                    'total' => $prospects->total(),
                    'last_page' => $prospects->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Prospects Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load prospects: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Update call status (mark as interested/not interested)
     */
    public function updateCallStatus(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:crm_assignments,id',
            'status' => 'required|in:called_interested,called_not_interested',
            'remark' => 'required|string',
        ]);

        $telecallerId = $request->user()->id;
        $assignmentId = $request->input('assignment_id');
        $status = $request->input('status');
        $remark = $request->input('remark');

        if ($status === 'called_not_interested') {
            $result = $this->telecallerService->markNotInterested($assignmentId, $telecallerId, $remark);
        } else {
            // For interested, we create a prospect (handled in createProspect endpoint)
            return response()->json([
                'success' => false,
                'message' => 'Use create-prospect endpoint for interested leads',
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Mark as CNP
     */
    public function markCnp(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:crm_assignments,id',
            'remark' => 'required|string',
        ]);

        $telecallerId = $request->user()->id;
        $assignmentId = $request->input('assignment_id');
        $remark = $request->input('remark');

        $result = $this->telecallerService->markCnp($assignmentId, $telecallerId, $remark);

        return response()->json($result);
    }

    /**
     * Mark as Broker
     */
    public function markBroker(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:crm_assignments,id',
            'remark' => 'required|string',
        ]);

        $telecallerId = $request->user()->id;
        $assignmentId = $request->input('assignment_id');
        $remark = $request->input('remark');

        $result = $this->telecallerService->markAsBroker($assignmentId, $telecallerId, $remark);

        return response()->json($result);
    }

    /**
     * Schedule follow-up
     */
    public function scheduleFollowUp(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:crm_assignments,id',
            'follow_up_date' => 'required|date|after_or_equal:today',
            'follow_up_time' => 'required|string',
            'follow_up_notes' => 'nullable|string',
        ]);

        $telecallerId = $request->user()->id;
        $assignmentId = $request->input('assignment_id');
        $date = $request->input('follow_up_date');
        $time = $request->input('follow_up_time');
        $notes = $request->input('follow_up_notes');

        $result = $this->telecallerService->scheduleFollowUp($assignmentId, $telecallerId, $date, $time, $notes);

        return response()->json($result);
    }

    /**
     * Create prospect from assignment
     */
    public function createProspect(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:crm_assignments,id',
            'budget' => 'nullable|numeric',
            'preferred_location' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'possession' => 'nullable|string|max:255',
            'remark' => 'required|string',
            'assigned_manager' => 'nullable|exists:users,id',
            'lead_score' => 'required|integer|min:1|max:5',
        ]);

        $telecallerId = $request->user()->id;
        $assignmentId = $request->input('assignment_id');

        $data = [
            'budget' => $request->input('budget'),
            'preferred_location' => $request->input('preferred_location'),
            'size' => $request->input('size'),
            'purpose' => $request->input('purpose'),
            'possession' => $request->input('possession'),
            'remark' => $request->input('remark'),
            'assigned_manager' => $request->input('assigned_manager'),
            'lead_score' => $request->input('lead_score'),
        ];

        $result = $this->telecallerService->createProspect($assignmentId, $telecallerId, $data);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Recall assignment (move back to queue)
     */
    public function recallAssignment(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:crm_assignments,id',
        ]);

        $telecallerId = $request->user()->id;
        $assignmentId = $request->input('assignment_id');

        $result = $this->telecallerService->recallAssignment($assignmentId, $telecallerId);

        return response()->json($result);
    }

    /**
     * Blacklist number
     */
    public function blacklistNumber(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);

        $telecallerId = $request->user()->id;
        $phone = $request->input('phone');
        $reason = $request->input('reason', 'Broker');

        $result = $this->telecallerService->blacklistNumber($phone, $telecallerId, $reason);

        return response()->json($result);
    }

    /**
     * Get users/managers list for assignment dropdown
     */
    public function getUsers(Request $request)
    {
        // Get managers and sales managers (for assignment dropdown)
        $users = User::whereHas('role', function($q) {
                $q->whereIn('slug', ['sales_manager', 'sales_executive']);
            })
            ->where('is_active', true)
            ->with('role')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role->name,
                ];
            });

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Get tasks with filters (pending, completed, rescheduled, all)
     */
    public function getTasks(Request $request)
    {
        try {
            $telecallerId = $request->user()->id;
            $status = $request->input('status', 'pending'); // pending, completed, rescheduled, all
            $taskType = $request->input('task_type', ''); // calling, follow_up, cnp_retry

            $query = TelecallerTask::where('assigned_to', $telecallerId)
                ->with(['lead' => function($q) {
                    $q->with(['activeAssignments' => function($q2) {
                        $q2->with(['assignedTo' => function($q3) {
                            $q3->with('manager');
                        }]);
                    }]);
                }, 'assignedTo']);

            // Filter by status
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            // Filter by task type
            if ($taskType) {
                $query->where('task_type', $taskType);
            }

            $perPage = $request->get('per_page', 50);
            // Sort: Overdue tasks first (oldest first), then upcoming tasks
            $tasks = $query->orderByRaw('CASE WHEN scheduled_at < NOW() THEN 0 ELSE 1 END')
                ->orderBy('scheduled_at', 'asc')
                ->paginate($perPage);

            $formattedTasks = $tasks->map(function($task) {
                $lead = $task->lead;
                $assignment = $lead ? $lead->activeAssignments->first() : null;
                $manager = $assignment?->assignedTo?->manager ?? null;
                
                return [
                    'id' => $task->id,
                    'lead_id' => $task->lead_id,
                    'lead_name' => $lead->name ?? '-',
                    'lead_phone' => $lead->phone ?? '-',
                    'lead_email' => $lead->email ?? null,
                    'manager_name' => $manager->name ?? 'Not Assigned',
                    'manager_id' => $manager->id ?? null,
                    'task_type' => $task->task_type,
                    'status' => $task->status,
                    'scheduled_at' => $task->scheduled_at ? $task->scheduled_at->format('Y-m-d H:i:s') : null,
                    'completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d H:i:s') : null,
                    'outcome' => $task->outcome,
                    'notes' => $task->notes,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedTasks->values()->all(),
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'last_page' => $tasks->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Tasks Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tasks: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get task statistics
     */
    public function getTaskStats(Request $request)
    {
        $telecallerId = $request->user()->id;

        $stats = [
            'pending' => Task::where('assigned_to', $telecallerId)
                ->where('type', 'phone_call')
                ->where('status', 'pending')
                ->count(),
            'in_progress' => Task::where('assigned_to', $telecallerId)
                ->where('type', 'phone_call')
                ->where('status', 'in_progress')
                ->count(),
            'completed' => Task::where('assigned_to', $telecallerId)
                ->where('type', 'phone_call')
                ->where('status', 'completed')
                ->count(),
            'today_pending' => Task::where('assigned_to', $telecallerId)
                ->where('type', 'phone_call')
                ->where('status', 'pending')
                ->whereDate('created_at', Carbon::today())
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Initiate call from task
     */
    public function initiateCall(Request $request, Task $task)
    {
        // Verify task belongs to user
        if ($task->assigned_to !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($task->status !== 'pending') {
            return response()->json([
                'error' => 'Task is not in pending status',
            ], 400);
        }

        $task->markAsInProgress();

        // Get CrmAssignment if exists
        $assignment = CrmAssignment::where('lead_id', $task->lead_id)
            ->where('assigned_to', $task->assigned_to)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Call initiated',
            'task' => $task->fresh(['lead']),
            'assignment_id' => $assignment ? $assignment->id : null,
        ]);
    }

    /**
     * Handle call outcome from task
     */
    public function callOutcome(Request $request, Task $task)
    {
        // Verify task belongs to user
        if ($task->assigned_to !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($task->status !== 'in_progress') {
            return response()->json([
                'error' => 'Task must be in progress',
            ], 400);
        }

        $request->validate([
            'outcome' => 'required|in:interested,not_interested,cnp,call_later,broker',
        ]);

        $telecallerId = $request->user()->id;
        $outcome = $request->input('outcome');

        // Get CrmAssignment
        $assignment = CrmAssignment::where('lead_id', $task->lead_id)
            ->where('assigned_to', $telecallerId)
            ->first();

        if (!$assignment) {
            return response()->json([
                'error' => 'Assignment not found',
            ], 404);
        }

        DB::beginTransaction();
        try {
            switch ($outcome) {
                case 'interested':
                    // Return success with flag to show form
                    return response()->json([
                        'success' => true,
                        'outcome' => 'interested',
                        'message' => 'Please fill the interested lead form',
                        'task_id' => $task->id,
                        'assignment_id' => $assignment->id,
                        'lead_data' => $this->telecallerService->getLeadDataForTask($task),
                    ]);

                case 'not_interested':
                    $request->validate([
                        'remark' => 'required|string',
                    ]);
                    $this->telecallerService->markNotInterested($assignment->id, $telecallerId, $request->input('remark'));
                    // Update lead status to connected
                    $lead = Lead::find($task->lead_id);
                    if ($lead) {
                        $lead->updateStatusIfAllowed('connected');
                    }
                    $task->markAsCompleted();
                    break;

                case 'cnp':
                    $request->validate([
                        'remark' => 'required|string',
                    ]);
                    $this->telecallerService->markCnp($assignment->id, $telecallerId, $request->input('remark'));
                    // Don't complete task if CNP count < 2, task should remain pending for next call
                    if ($assignment->fresh()->cnp_count >= 2) {
                        $task->markAsCompleted();
                    } else {
                        $task->update(['status' => 'pending']); // Reset to pending for next call
                    }
                    break;

                case 'call_later':
                    $request->validate([
                        'follow_up_date' => 'required|date|after_or_equal:today',
                        'follow_up_time' => 'required|string',
                        'follow_up_notes' => 'nullable|string',
                    ]);
                    $this->telecallerService->scheduleFollowUp(
                        $assignment->id,
                        $telecallerId,
                        $request->input('follow_up_date'),
                        $request->input('follow_up_time'),
                        $request->input('follow_up_notes')
                    );
                    // Update lead status to connected
                    $lead = Lead::find($task->lead_id);
                    if ($lead) {
                        $lead->updateStatusIfAllowed('connected');
                    }
                    $task->markAsCompleted();
                    break;

                case 'broker':
                    $request->validate([
                        'remark' => 'required|string',
                    ]);
                    $this->telecallerService->markAsBroker($assignment->id, $telecallerId, $request->input('remark'));
                    // Update lead status to connected
                    $lead = Lead::find($task->lead_id);
                    if ($lead) {
                        $lead->updateStatusIfAllowed('connected');
                    }
                    $task->markAsCompleted();
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Call outcome processed successfully',
                'task' => $task->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to process outcome: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get profile data
     */
    public function getProfile(Request $request)
    {
        $user = $request->user()->load('role', 'manager', 'telecallerProfile');
        
        // Get activity history (last 10 activities)
        $activityHistory = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['action', 'ip_address', 'user_agent', 'created_at']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture_url,
                'role' => $user->role->name ?? 'Telecaller',
                'manager' => $user->manager ? $user->manager->name : null,
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
            ],
            'profile' => [
                'is_absent' => $user->telecallerProfile->is_absent ?? false,
                'absent_reason' => $user->telecallerProfile->absent_reason ?? null,
                'absent_until' => $user->telecallerProfile->absent_until ? $user->telecallerProfile->absent_until->format('Y-m-d H:i:s') : null,
                'max_pending_leads' => $user->telecallerProfile->max_pending_leads ?? null,
            ],
            'activity_history' => $activityHistory->map(function ($log) {
                return [
                    'action' => $log->action,
                    'ip' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Update profile (name, email and phone)
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
                'role' => $user->role->name ?? 'Telecaller',
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
     * Update availability status
     */
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'is_absent' => 'required|boolean',
            'absent_reason' => 'nullable|string|max:255',
            'absent_until' => 'nullable|date|after_or_equal:today',
        ]);

        $user = $request->user();
        
        $profile = TelecallerProfile::firstOrCreate(['user_id' => $user->id]);
        
        $profile->update([
            'is_absent' => $request->is_absent,
            'absent_reason' => $request->absent_reason,
            'absent_until' => $request->absent_until ? Carbon::parse($request->absent_until) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->is_absent ? 'Marked as absent' : 'Marked as available',
            'profile' => [
                'is_absent' => $profile->is_absent,
                'absent_reason' => $profile->absent_reason,
                'absent_until' => $profile->absent_until ? $profile->absent_until->format('Y-m-d H:i:s') : null,
            ],
        ]);
    }

    /**
     * Get leads assigned to the telecaller
     */
    public function getLeads(Request $request)
    {
        try {
            $telecallerId = $request->user()->id;
            
            \Log::info('Telecaller getLeads called', [
                'telecaller_id' => $telecallerId,
                'request_params' => $request->all(),
            ]);
            
            // First, let's check if there are any assignments for this telecaller
            $assignmentCount = DB::table('lead_assignments')
                ->where('assigned_to', $telecallerId)
                ->where('is_active', true)
                ->count();
            
            \Log::info('Assignment count for telecaller', [
                'telecaller_id' => $telecallerId,
                'assignment_count' => $assignmentCount,
            ]);
            
            // Query leads with active assignments to this telecaller
            // Use assignments relationship directly and filter in whereHas
            $query = Lead::whereHas('assignments', function($q) use ($telecallerId) {
                $q->where('assigned_to', $telecallerId)
                  ->where('is_active', true);
            })
            ->with(['assignments' => function($q) use ($telecallerId) {
                $q->where('assigned_to', $telecallerId)
                  ->where('is_active', true);
            }, 'assignments.assignedTo', 'creator'])
            ->latest();

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('leads.name', 'like', "%{$search}%")
                      ->orWhere('leads.email', 'like', "%{$search}%")
                      ->orWhere('leads.phone', 'like', "%{$search}%")
                      ->orWhere('leads.city', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('leads.status', $request->status);
            }

            $perPage = $request->get('per_page', 50);
            $leads = $query->paginate($perPage);

            // Log for debugging
            \Log::info('Telecaller Leads Query', [
                'telecaller_id' => $telecallerId,
                'total_leads' => $leads->total(),
                'current_page' => $leads->currentPage(),
                'leads_count' => $leads->count(),
            ]);

            // Format leads data
            $formattedLeads = $leads->map(function($lead) {
                $assignment = $lead->assignments->first();
                return [
                    'id' => $lead->id,
                    'name' => $lead->name ?? '-',
                    'phone' => $lead->phone ?? '-',
                    'email' => $lead->email ?? null,
                    'city' => $lead->city ?? null,
                    'state' => $lead->state ?? null,
                    'status' => $lead->status ?? 'new',
                    'last_contacted_at' => $lead->last_contacted_at ? $lead->last_contacted_at->format('Y-m-d H:i:s') : null,
                    'next_followup_at' => $lead->next_followup_at ? $lead->next_followup_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : null,
                    'assigned_at' => $assignment && $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d H:i:s') : ($lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : null),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedLeads->values()->all(),
                'pagination' => [
                    'current_page' => $leads->currentPage(),
                    'per_page' => $leads->perPage(),
                    'total' => $leads->total(),
                    'last_page' => $leads->lastPage(),
                    'from' => $leads->firstItem(),
                    'to' => $leads->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Telecaller Leads Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load leads: ' . $e->getMessage(),
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 50,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ], 500);
        }
    }

    /**
     * Record call outcome for TelecallerTask
     */
    public function recordOutcome(Request $request, $taskId)
    {
        try {
            $telecallerId = $request->user()->id;
            
            $request->validate([
                'outcome' => 'required|in:interested,not_interested,cnp,call_again,block',
                'scheduled_at' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $task = TelecallerTask::where('id', $taskId)
                ->where('assigned_to', $telecallerId)
                ->with('lead')
                ->firstOrFail();

            $outcome = $request->outcome;
            $lead = $task->lead;

            DB::beginTransaction();

            switch ($outcome) {
                case 'not_interested':
                    $lead->update(['status' => 'dead']);
                    $task->update([
                        'status' => 'completed',
                        'outcome' => 'not_interested',
                        'completed_at' => now(),
                        'notes' => $request->notes,
                    ]);
                    break;

                case 'cnp':
                    $lead->increment('cnp_count');
                    $cnpCount = $lead->fresh()->cnp_count;
                    
                    // If CNP count is less than 2, create a retry task
                    if ($cnpCount < 2) {
                        $this->taskService->createCnpRetryTask($lead, $task->assignedTo, $telecallerId);
                    }
                    // If CNP count reaches 2, task is completed automatically (no new task created)
                    
                    $task->update([
                        'status' => 'completed',
                        'outcome' => 'cnp',
                        'completed_at' => now(),
                        'notes' => $request->notes ?? "CNP count: {$cnpCount}",
                    ]);
                    break;

                case 'call_again':
                    if (!$request->scheduled_at) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Scheduled date and time is required for rescheduling.',
                        ], 400);
                    }
                    
                    // Check if original scheduled_at was within 10 minutes (notification would have been sent)
                    $originalScheduledAt = $task->scheduled_at;
                    $tenMinutesBeforeOriginal = $originalScheduledAt->copy()->subMinutes(10);
                    
                    // If notification was sent and we're rescheduling, cancel it
                    if ($task->notification_sent_at && now() >= $tenMinutesBeforeOriginal) {
                        // Delete related notifications
                        \App\Models\AppNotification::where('telecaller_task_id', $task->id)
                            ->where('user_id', $task->assigned_to)
                            ->where('type', 'call_reminder')
                            ->delete();
                        
                        // Reset notification_sent_at
                        $task->notification_sent_at = null;
                    }
                    
                    $task->update([
                        'status' => 'rescheduled',
                        'outcome' => 'rescheduled',
                        'scheduled_at' => $request->scheduled_at,
                        'notes' => $request->notes,
                        'notification_sent_at' => null, // Reset so new notification can be sent if needed
                    ]);
                    break;

                case 'block':
                    $lead->update([
                        'is_blocked' => true,
                        'blocked_at' => now(),
                        'blocked_reason' => $request->notes ?? 'Blocked by telecaller',
                    ]);
                    $task->update([
                        'status' => 'completed',
                        'outcome' => 'block',
                        'completed_at' => now(),
                        'notes' => $request->notes,
                    ]);
                    break;

                case 'interested':
                    $task->update([
                        'status' => 'completed',
                        'outcome' => 'interested',
                        'completed_at' => now(),
                        'notes' => $request->notes,
                    ]);
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Outcome recorded successfully',
                'task' => $task->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Record Outcome Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to record outcome: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create prospect from interested lead (for TelecallerTask flow)
     */
    public function createProspectFromTask(Request $request)
    {
        try {
            $telecallerId = $request->user()->id;
            
            // Manually validate to ensure JSON response
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'lead_id' => 'required|exists:leads,id',
                'customer_name' => 'required|string|max:255',
                'phone' => 'required|string',
                'budget' => 'nullable|string',
                'preferred_location' => 'nullable|string',
                'size' => 'nullable|string',
                'purpose' => 'required|in:end_user,investment',
                'possession' => 'nullable|string',
                'remark' => 'required|string',
                'lead_score' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $lead = Lead::findOrFail($request->lead_id);
            $assignment = $lead->activeAssignments->first();
            $manager = $assignment?->assignedTo?->manager ?? $request->user()->manager;

            if (!$manager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager not found for this lead.',
                ], 400);
            }

            $prospect = Prospect::create([
                'lead_id' => $lead->id,
                'telecaller_id' => $telecallerId,
                'manager_id' => $manager->id,
                'customer_name' => $request->customer_name,
                'phone' => $request->phone,
                'budget' => $request->budget,
                'preferred_location' => $request->preferred_location,
                'size' => $request->size,
                'purpose' => $request->purpose,
                'possession' => $request->possession,
                'remark' => $request->remark,
                'lead_score' => $request->lead_score,
                'verification_status' => 'pending_verification',
                'created_by' => $telecallerId,
            ]);

            // Also update the task status if task_id is provided
            if ($request->has('task_id')) {
                $task = TelecallerTask::find($request->task_id);
                if ($task && $task->assigned_to === $telecallerId) {
                    $task->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'outcome' => 'interested',
                    ]);
                }
            }

            // Update lead status to connected when prospect is created
            $lead->updateStatusIfAllowed('connected');

            // Fire event to create verification call task for manager/senior manager
            if ($prospect->verification_status === 'pending_verification') {
                event(new \App\Events\ProspectSentForVerification($prospect));
            }

            return response()->json([
                'success' => true,
                'message' => 'Prospect created successfully and sent for manager verification',
                'prospect' => $prospect->load('manager', 'telecaller'),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Create Prospect Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create prospect: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save call log from Flutter app
     */
    public function saveCallLog(Request $request)
    {
        try {
            $request->validate([
                'lead_id' => 'required|exists:leads,id',
                'phone_number' => 'required|string',
                'start_time' => 'required|date',
                'end_time' => 'nullable|date',
                'duration' => 'required|integer|min:0',
                'call_type' => 'required|in:incoming,outgoing',
                'task_id' => 'nullable|exists:telecaller_tasks,id',
                'status' => 'nullable|in:completed,missed,rejected,busy',
            ]);

            $telecallerId = $request->user()->id;

            $callLog = DB::table('call_logs')->insert([
                'telecaller_id' => $telecallerId,
                'lead_id' => $request->lead_id,
                'task_id' => $request->task_id,
                'phone_number' => $request->phone_number,
                'call_type' => $request->call_type,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration' => $request->duration,
                'status' => $request->status ?? 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call log saved successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Save Call Log Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save call log: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get call logs with filters
     */
    public function getCallLogs(Request $request)
    {
        try {
            $telecallerId = $request->user()->id;

            $query = DB::table('call_logs')
                ->where('telecaller_id', $telecallerId)
                ->orderBy('start_time', 'desc');

            // Filter by date range
            if ($request->has('from_date')) {
                $query->where('start_time', '>=', $request->from_date);
            }
            if ($request->has('to_date')) {
                $query->where('start_time', '<=', $request->to_date);
            }

            // Filter by call type
            if ($request->has('call_type')) {
                $query->where('call_type', $request->call_type);
            }

            // Filter by lead_id
            if ($request->has('lead_id')) {
                $query->where('lead_id', $request->lead_id);
            }

            $perPage = $request->get('per_page', 50);
            $callLogs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $callLogs->items(),
                'pagination' => [
                    'current_page' => $callLogs->currentPage(),
                    'per_page' => $callLogs->perPage(),
                    'total' => $callLogs->total(),
                    'last_page' => $callLogs->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Call Logs Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load call logs: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get call statistics
     */
    public function getCallStatistics(Request $request)
    {
        try {
            $telecallerId = $request->user()->id;
            $today = Carbon::today();
            $startOfWeek = Carbon::now()->startOfWeek();

            // Today's statistics
            $todayStats = DB::table('call_logs')
                ->where('telecaller_id', $telecallerId)
                ->whereDate('start_time', $today)
                ->selectRaw('
                    COUNT(*) as total_calls,
                    SUM(CASE WHEN call_type = "incoming" THEN 1 ELSE 0 END) as incoming_calls,
                    SUM(CASE WHEN call_type = "outgoing" THEN 1 ELSE 0 END) as outgoing_calls,
                    SUM(duration) as total_talking_time,
                    AVG(duration) as average_duration
                ')
                ->first();

            // Calls per hour (today)
            $callsPerHour = DB::table('call_logs')
                ->where('telecaller_id', $telecallerId)
                ->whereDate('start_time', $today)
                ->selectRaw('HOUR(start_time) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->pluck('count', 'hour')
                ->toArray();

            // This week's statistics
            $weekStats = DB::table('call_logs')
                ->where('telecaller_id', $telecallerId)
                ->where('start_time', '>=', $startOfWeek)
                ->selectRaw('
                    COUNT(*) as total_calls,
                    SUM(duration) as total_talking_time
                ')
                ->first();

            // Daily breakdown for this week
            $dailyBreakdown = DB::table('call_logs')
                ->where('telecaller_id', $telecallerId)
                ->where('start_time', '>=', $startOfWeek)
                ->selectRaw('DATE(start_time) as date, COUNT(*) as count, SUM(duration) as total_time')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'today' => [
                    'total_calls' => (int)($todayStats->total_calls ?? 0),
                    'incoming_calls' => (int)($todayStats->incoming_calls ?? 0),
                    'outgoing_calls' => (int)($todayStats->outgoing_calls ?? 0),
                    'total_talking_time' => (int)($todayStats->total_talking_time ?? 0),
                    'average_duration' => (int)($todayStats->average_duration ?? 0),
                    'calls_per_hour' => $callsPerHour,
                ],
                'this_week' => [
                    'total_calls' => (int)($weekStats->total_calls ?? 0),
                    'total_talking_time' => (int)($weekStats->total_talking_time ?? 0),
                    'daily_breakdown' => $dailyBreakdown,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Call Statistics Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

}
