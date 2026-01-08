<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\TelecallerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BuilderController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectCollateralController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\UnitTypeController;
use App\Http\Controllers\Api\ProjectDetailController;
use App\Http\Controllers\Api\Crm\AuthController as CrmAuthController;
use App\Http\Controllers\Api\Crm\DashboardController as CrmDashboardController;
use App\Http\Controllers\Api\Crm\LeadController as CrmLeadController;
use App\Http\Controllers\Api\Crm\UserController as CrmUserController;
use App\Http\Controllers\Api\Crm\TransferController as CrmTransferController;
use App\Http\Controllers\Api\Crm\BlacklistController as CrmBlacklistController;
use App\Http\Controllers\Api\Crm\TargetController as CrmTargetController;
use App\Http\Controllers\Api\Crm\VerificationController as CrmVerificationController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\InterestedProjectNameController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Telecaller public routes
Route::post('/telecaller/login', [TelecallerController::class, 'login']);

// Debug/Test route (no auth needed for testing)
Route::post('/test/csrf-check', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'CSRF check passed!',
        'data' => [
            'is_api_route' => $request->is('api/*'),
            'has_bearer_token' => (bool) $request->bearerToken(),
            'authorization_header' => $request->header('Authorization') ? 'Present' : 'Missing',
            'csrf_token' => $request->header('X-CSRF-TOKEN') ? 'Present' : 'Missing',
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
        ],
    ]);
});


// Database query test endpoint
Route::post('/test/db-query', function (Request $request) {
    try {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Direct database query test
        $meetingsCount = \App\Models\Meeting::where('verification_status', 'pending')
            ->where('status', 'completed')
            ->count();

        $meetings = \App\Models\Meeting::where('verification_status', 'pending')
            ->where('status', 'completed')
            ->select('id', 'customer_name', 'phone', 'status', 'verification_status', 'created_by', 'scheduled_at', 'completed_at')
            ->limit(5)
            ->get();

        $siteVisitsCount = \App\Models\SiteVisit::where('verification_status', 'pending')
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'success' => true,
            'meetings_count' => $meetingsCount,
            'meetings_sample' => $meetings,
            'site_visits_count' => $siteVisitsCount,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role->name ?? 'N/A',
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
})->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Targets
    Route::get('/targets/my-targets', [TargetController::class, 'myTargets']);
    Route::get('/targets/team-progress', [TargetController::class, 'teamProgress'])->middleware('role:sales_manager');
    Route::get('/targets/overview', [TargetController::class, 'overview'])->middleware('role:admin,crm');

    // Leads
    Route::apiResource('leads', LeadController::class);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign']);

    // Site Visits
    Route::apiResource('site-visits', SiteVisitController::class);

    // Follow-ups
    Route::apiResource('follow-ups', FollowUpController::class);

    // Interested Project Names
    Route::get('/interested-project-names', [InterestedProjectNameController::class, 'index']);

    // Users (Admin only)
    Route::apiResource('users', UserController::class)->middleware('permission:manage_users');

    // Telecaller routes
    Route::prefix('telecaller')->middleware('role:telecaller')->group(function () {
        // Auth routes
        Route::get('/whoami', [TelecallerController::class, 'whoami']);
        Route::post('/logout', [TelecallerController::class, 'logout']);
        
        // Dashboard & Stats
        Route::get('/stats', [TelecallerController::class, 'getStats']);
        Route::get('/top-performers', [TelecallerController::class, 'getTopPerformers']);
        
        // Dashboard API endpoints
        Route::get('/dashboard', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'index']);
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'stats']);
        Route::get('/dashboard/urgent-tasks', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'urgentTasks']);
        Route::get('/dashboard/schedule', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'schedule']);
        Route::get('/dashboard/performance', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'performance']);
        
        // Leads & Calls
        Route::get('/leads', [TelecallerController::class, 'getLeads']);
        Route::get('/calling-queue', [TelecallerController::class, 'getCallingQueue']);
        Route::get('/completed-calls', [TelecallerController::class, 'getCompletedCalls']);
        Route::get('/follow-up-calls', [TelecallerController::class, 'getFollowUpCalls']);
        Route::get('/cnp-calls', [TelecallerController::class, 'getCnpCalls']);
        Route::get('/prospects', [TelecallerController::class, 'getProspects']);
        Route::get('/prospects/list', [TelecallerController::class, 'getProspects']); // Alias for verification pending
        
        // Tasks
        Route::get('/tasks', [TelecallerController::class, 'getTasks']);
        Route::get('/tasks/stats', [TelecallerController::class, 'getTaskStats']);
        Route::post('/tasks/{task}/initiate-call', [TelecallerController::class, 'initiateCall']);
        Route::post('/tasks/{task}/call-outcome', [TelecallerController::class, 'callOutcome']);
        Route::post('/tasks/{taskId}/outcome', [TelecallerController::class, 'recordOutcome']);
        
        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/notifications/unread', [\App\Http\Controllers\Api\NotificationController::class, 'getUnread']);
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/{notification}/click', [\App\Http\Controllers\Api\NotificationController::class, 'markAsClicked']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        
        // Broadcasts
        Route::get('/broadcast/unread', [\App\Http\Controllers\Api\BroadcastController::class, 'getUnreadBroadcasts']);
        Route::post('/broadcast/{broadcast}/read', [\App\Http\Controllers\Api\BroadcastController::class, 'markAsRead']);
        
        // Admin/CRM Broadcast sending
        Route::middleware('role:admin,crm')->group(function () {
            Route::post('/broadcast/send', [\App\Http\Controllers\Api\BroadcastController::class, 'sendBroadcast']);
        });
        
        // Actions
        Route::post('/update-call-status', [TelecallerController::class, 'updateCallStatus']);
        Route::post('/mark-cnp', [TelecallerController::class, 'markCnp']);
        Route::post('/mark-broker', [TelecallerController::class, 'markBroker']);
        Route::post('/schedule-follow-up', [TelecallerController::class, 'scheduleFollowUp']);
        Route::post('/create-prospect', [TelecallerController::class, 'createProspect']);
        Route::post('/prospects/create', [TelecallerController::class, 'createProspectFromTask']);
        Route::post('/recall-assignment', [TelecallerController::class, 'recallAssignment']);
        Route::post('/blacklist-number', [TelecallerController::class, 'blacklistNumber']);
        
        // Supporting
        Route::get('/users', [TelecallerController::class, 'getUsers']);
        
        // Profile
        Route::get('/profile', [TelecallerController::class, 'getProfile']);
        Route::put('/profile', [TelecallerController::class, 'updateProfile']);
        Route::post('/profile/picture', [TelecallerController::class, 'uploadProfilePicture']);
        Route::post('/profile/password', [TelecallerController::class, 'changePassword']);
        Route::post('/profile/availability', [TelecallerController::class, 'updateAvailability']);
        
        // Call Tracking (Legacy - kept for backward compatibility)
        Route::post('/call-logs', [TelecallerController::class, 'saveCallLog']);
        Route::get('/call-logs', [TelecallerController::class, 'getCallLogs']);
        Route::get('/call-statistics', [TelecallerController::class, 'getCallStatistics']);
    });

    // Enhanced Call Logs API (for all roles)
    Route::prefix('call-logs')->name('api.call-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CallLogController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\CallLogController::class, 'store']);
        Route::post('/bulk-sync', [\App\Http\Controllers\Api\CallLogController::class, 'bulkSync']);
        Route::get('/statistics', [\App\Http\Controllers\Api\CallLogController::class, 'getStatistics']);
        Route::get('/team-statistics', [\App\Http\Controllers\Api\CallLogController::class, 'getTeamStatistics']);
        Route::get('/dashboard-stats', [\App\Http\Controllers\Api\CallLogController::class, 'getDashboardStats']);
        Route::get('/{id}', [\App\Http\Controllers\Api\CallLogController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\CallLogController::class, 'update']);
    });

    // Sales Manager routes (accessible by Admin, CRM, Sales Head, and Sales Manager)
    Route::prefix('sales-manager')->middleware('role:admin,crm,sales_head,sales_manager')->group(function () {
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Api\SalesManagerController::class, 'getProfile']);
        Route::put('/profile', [\App\Http\Controllers\Api\SalesManagerController::class, 'updateProfile']);
        Route::post('/profile/picture', [\App\Http\Controllers\Api\SalesManagerController::class, 'uploadProfilePicture']);
        Route::post('/profile/password', [\App\Http\Controllers\Api\SalesManagerController::class, 'changePassword']);
        
        // Team management
        Route::get('/team/member/{memberId}', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTeamMemberDetails']);
        Route::get('/team/performance', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTeamPerformance']);
        
        // Achievements
        Route::get('/achievements', [\App\Http\Controllers\Api\SalesManagerController::class, 'getAchievements']);
        
        // Prospects
        Route::get('/prospects', [\App\Http\Controllers\Api\SalesManagerController::class, 'getProspects']);
        Route::post('/prospects', [\App\Http\Controllers\Api\SalesManagerController::class, 'createProspect']);
        Route::get('/prospects/pending', [\App\Http\Controllers\Api\Crm\VerificationController::class, 'getPending']);
        Route::post('/prospects/{prospect}/verify', [\App\Http\Controllers\Api\Crm\VerificationController::class, 'verify']);
        Route::post('/prospects/{prospect}/reject', [\App\Http\Controllers\Api\Crm\VerificationController::class, 'reject']);
        
        // Tasks
        Route::get('/tasks', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTasks']);
        Route::get('/tasks/{task}', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTask']);
        Route::post('/tasks/{task}/update-lead', [\App\Http\Controllers\Api\SalesManagerController::class, 'updateLeadFromTask']);
        
        // Meetings
        Route::get('/meetings', [\App\Http\Controllers\Api\MeetingController::class, 'index']);
        Route::post('/meetings', [\App\Http\Controllers\Api\MeetingController::class, 'store']);
        Route::get('/meetings/{meeting}', [\App\Http\Controllers\Api\MeetingController::class, 'show']);
        Route::put('/meetings/{meeting}', [\App\Http\Controllers\Api\MeetingController::class, 'update']);
        Route::post('/meetings/{meeting}/complete', [\App\Http\Controllers\Api\MeetingController::class, 'complete']);
        Route::post('/meetings/{meeting}/cancel', [\App\Http\Controllers\Api\MeetingController::class, 'cancel']);
        Route::post('/meetings/{meeting}/reschedule', [\App\Http\Controllers\Api\MeetingController::class, 'reschedule']);
        Route::post('/meetings/{meeting}/convert-to-site-visit', [\App\Http\Controllers\Api\MeetingController::class, 'convertToSiteVisit']);
        Route::post('/meetings/{meeting}/mark-dead', [\App\Http\Controllers\Api\MeetingController::class, 'markDead']);
        Route::post('/meetings/{meeting}/verify', [\App\Http\Controllers\Api\MeetingController::class, 'verify']);
        Route::post('/meetings/{meeting}/reject', [\App\Http\Controllers\Api\MeetingController::class, 'reject']);
        
        // Site Visits
        Route::post('/site-visits', [\App\Http\Controllers\Api\SiteVisitController::class, 'store']);
        Route::post('/site-visits/{siteVisit}/complete', [\App\Http\Controllers\Api\SiteVisitController::class, 'complete']);
        Route::post('/site-visits/{siteVisit}/reschedule', [\App\Http\Controllers\Api\SiteVisitController::class, 'reschedule']);
        Route::post('/site-visits/{siteVisit}/convert-to-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'convertToCloser']);
        Route::post('/site-visits/{siteVisit}/request-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'requestCloser']);
        Route::post('/site-visits/{siteVisit}/mark-dead', [\App\Http\Controllers\Api\SiteVisitController::class, 'markDead']);
    });

    // CRM routes
    Route::prefix('crm')->group(function () {
        // Authentication
        Route::post('/login', [CrmAuthController::class, 'login']);
        
        Route::middleware(['auth:sanctum', 'crm'])->group(function () {
            // Auth routes
            Route::get('/whoami', [CrmAuthController::class, 'whoami']);
            Route::post('/logout', [CrmAuthController::class, 'logout']);
            
            // Dashboard
            Route::get('/dashboard/stats', [CrmDashboardController::class, 'getStats']);
            Route::get('/dashboard/telecaller-stats', [CrmDashboardController::class, 'getTelecallerStats']);
            Route::get('/dashboard/daily-prospects', [CrmDashboardController::class, 'getDailyProspects']);
            
            // Leads
            Route::post('/add-lead', [CrmLeadController::class, 'addLead']);
            Route::get('/imported-leads', [CrmLeadController::class, 'getImportedLeads']);
            Route::post('/assign-leads', [CrmLeadController::class, 'assignLeads']);
            
            // Users
            Route::get('/users', [CrmUserController::class, 'index']);
            Route::get('/roles', [CrmUserController::class, 'getRoles']);
            Route::post('/users', [CrmUserController::class, 'store']);
            Route::put('/users/{id}', [CrmUserController::class, 'update']);
            Route::delete('/users/{id}', [CrmUserController::class, 'destroy']);
            
            // Transfer
            Route::post('/transfer-leads', [CrmTransferController::class, 'transfer']);
            
            // Blacklist
            Route::get('/blacklist', [CrmBlacklistController::class, 'index']);
            Route::post('/blacklist', [CrmBlacklistController::class, 'store']);
            Route::delete('/blacklist/{id}', [CrmBlacklistController::class, 'destroy']);
            
            // Targets
            Route::get('/targets', [CrmTargetController::class, 'index']);
            Route::post('/targets', [CrmTargetController::class, 'store']);
            Route::put('/targets/{id}', [CrmTargetController::class, 'update']);
            
            // Verifications
            Route::get('/pending-verifications', [CrmVerificationController::class, 'getPending']);
            Route::get('/verifications/pending-prospects', [CrmVerificationController::class, 'getPending']);
            Route::post('/verify-prospect/{prospect}', [CrmVerificationController::class, 'verify']);
            Route::post('/reject-prospect/{prospect}', [CrmVerificationController::class, 'reject']);
            
            // Meeting & Site Visit Verifications
            Route::post('/meetings/{meeting}/verify', [\App\Http\Controllers\Api\MeetingController::class, 'verify']);
            Route::post('/meetings/{meeting}/reject', [\App\Http\Controllers\Api\MeetingController::class, 'reject']);
            Route::post('/site-visits/{siteVisit}/verify', [\App\Http\Controllers\Api\SiteVisitController::class, 'verify']);
            Route::post('/site-visits/{siteVisit}/reject', [\App\Http\Controllers\Api\SiteVisitController::class, 'reject']);
            Route::post('/site-visits/{siteVisit}/verify-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'verifyCloser']);
            Route::post('/site-visits/{siteVisit}/reject-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'rejectCloser']);
        });
    });

    // Admin routes (for verification)
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin,crm'])->group(function () {
        // Dead Leads/Items
        Route::get('/dead-leads', function (Request $request) {
            $query = \App\Models\Lead::where('is_dead', true)
                ->with(['markedDeadBy', 'creator']);
            
            if ($request->has('dead_at_stage')) {
                $query->where('dead_at_stage', $request->dead_at_stage);
            }
            
            $leads = $query->latest('marked_dead_at')->paginate($request->get('per_page', 50));
            return response()->json($leads);
        });
        
        Route::get('/dead-meetings', function (Request $request) {
            $query = \App\Models\Meeting::where('is_dead', true)
                ->with(['markedDeadBy', 'creator', 'lead']);
            
            $meetings = $query->latest('marked_dead_at')->paginate($request->get('per_page', 50));
            return response()->json($meetings);
        });
        
        Route::get('/dead-site-visits', function (Request $request) {
            $query = \App\Models\SiteVisit::where('is_dead', true)
                ->with(['markedDeadBy', 'creator', 'lead']);
            
            $visits = $query->latest('marked_dead_at')->paginate($request->get('per_page', 50));
            return response()->json($visits);
        });
        
        // Meeting and Site Visit details for CRM/Admin
        Route::get('/meetings/{meeting}', function (Request $request, $meetingId) {
            try {
                $meeting = \App\Models\Meeting::with(['lead', 'prospect', 'creator', 'assignedTo', 'verifiedBy'])->findOrFail($meetingId);
                return response()->json([
                    'data' => $meeting,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Meeting not found',
                    'message' => $e->getMessage(),
                ], 404);
            }
        });

        Route::get('/site-visits/{siteVisit}', function (Request $request, $siteVisitId) {
            try {
                $siteVisit = \App\Models\SiteVisit::with(['lead', 'creator', 'assignedTo'])->findOrFail($siteVisitId);
                return response()->json([
                    'data' => $siteVisit,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Site visit not found',
                    'message' => $e->getMessage(),
                ], 404);
            }
        });

        // Prospect details for CRM/Admin
        Route::get('/prospects/{prospect}', function (Request $request, $prospectId) {
            try {
                $prospect = \App\Models\Prospect::with([
                    'lead', 
                    'createdBy', 
                    'assignedManager', 
                    'telecaller', 
                    'manager', 
                    'verifiedBy',
                    'assignment'
                ])->findOrFail($prospectId);
                return response()->json([
                    'data' => $prospect,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Prospect not found',
                    'message' => $e->getMessage(),
                ], 404);
            }
        });

        Route::get('/verifications/pending', function (Request $request) {
            try {
                $user = $request->user();
                if (!$user) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                // Get all pending meetings - only 'pending' verification_status
                // Optimize: Eager load relationships and select only needed columns
                $meetings = \App\Models\Meeting::where('verification_status', 'pending')
                    ->where('status', 'completed')
                    ->with([
                        'lead:id,name,phone',
                        'prospect:id,customer_name',
                        'creator:id,name',
                        'assignedTo:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'budget_range', 'property_type',
                        'meeting_notes', 'employee', 'occupation', 'date_of_visit',
                        'project', 'payment_mode', 'tentative_period', 'lead_type',
                        'team_leader', 'lead_id', 'prospect_id', 'created_by', 'assigned_to',
                        'photos', 'completion_proof_photos'
                    ])
                    ->get()
                    ->map(function($meeting) {
                    return [
                        'id' => $meeting->id,
                        'customer_name' => $meeting->customer_name,
                        'phone' => $meeting->phone,
                        'scheduled_at' => $meeting->scheduled_at ? $meeting->scheduled_at->toIso8601String() : null,
                        'completed_at' => $meeting->completed_at ? $meeting->completed_at->toIso8601String() : null,
                        'status' => $meeting->status,
                        'verification_status' => $meeting->verification_status,
                        'budget_range' => $meeting->budget_range,
                        'property_type' => $meeting->property_type,
                        'meeting_notes' => $meeting->meeting_notes,
                        'employee' => $meeting->employee,
                        'occupation' => $meeting->occupation,
                        'date_of_visit' => $meeting->date_of_visit ? $meeting->date_of_visit->toIso8601String() : null,
                        'project' => $meeting->project,
                        'payment_mode' => $meeting->payment_mode,
                        'tentative_period' => $meeting->tentative_period,
                        'lead_type' => $meeting->lead_type,
                        'team_leader' => $meeting->team_leader,
                        'photos' => $meeting->photos ?? [],
                        'completion_proof_photos' => $meeting->completion_proof_photos ?? [],
                        'lead' => $meeting->lead ? ['id' => $meeting->lead->id, 'name' => $meeting->lead->name, 'phone' => $meeting->lead->phone] : null,
                        'prospect' => $meeting->prospect ? ['id' => $meeting->prospect->id, 'customer_name' => $meeting->prospect->customer_name] : null,
                        'creator' => $meeting->creator ? ['id' => $meeting->creator->id, 'name' => $meeting->creator->name] : null,
                        'assignedTo' => $meeting->assignedTo ? ['id' => $meeting->assignedTo->id, 'name' => $meeting->assignedTo->name] : null,
                    ];
                });
                
                // Get all pending site visits - only 'pending' verification_status
                // Optimize: Eager load relationships and select only needed columns
                $siteVisits = \App\Models\SiteVisit::where('verification_status', 'pending')
                    ->where('status', 'completed')
                    ->where(function($query) {
                        $query->whereNull('closer_status')
                              ->orWhere('closer_status', '!=', 'pending');
                    })
                    ->with([
                        'lead:id,name,phone',
                        'creator:id,name',
                        'assignedTo:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'property_name', 'property_address',
                        'budget_range', 'visit_notes', 'closer_status', 'project',
                        'property_type', 'lead_type', 'lead_id', 'created_by', 'assigned_to',
                        'photos', 'completion_proof_photos'
                    ])
                    ->get()
                    ->map(function($visit) {
                    return [
                        'id' => $visit->id,
                        'customer_name' => $visit->customer_name,
                        'phone' => $visit->phone,
                        'scheduled_at' => $visit->scheduled_at ? $visit->scheduled_at->toIso8601String() : null,
                        'completed_at' => $visit->completed_at ? $visit->completed_at->toIso8601String() : null,
                        'status' => $visit->status,
                        'verification_status' => $visit->verification_status,
                        'property_name' => $visit->property_name,
                        'property_address' => $visit->property_address,
                        'budget_range' => $visit->budget_range,
                        'visit_notes' => $visit->visit_notes,
                        'closer_status' => $visit->closer_status,
                        'project' => $visit->project,
                        'property_type' => $visit->property_type,
                        'lead_type' => $visit->lead_type,
                        'employee' => $visit->employee,
                        'photos' => $visit->photos ?? [],
                        'completion_proof_photos' => $visit->completion_proof_photos ?? [],
                        'closer_request_proof_photos' => $visit->closer_request_proof_photos ?? [],
                        'lead' => $visit->lead ? ['id' => $visit->lead->id, 'name' => $visit->lead->name, 'phone' => $visit->lead->phone] : null,
                        'creator' => $visit->creator ? ['id' => $visit->creator->id, 'name' => $visit->creator->name] : null,
                        'assignedTo' => $visit->assignedTo ? ['id' => $visit->assignedTo->id, 'name' => $visit->assignedTo->name] : null,
                    ];
                });
                
                return response()->json([
                    'meetings' => array_values($meetings->toArray()),
                    'site_visits' => array_values($siteVisits->toArray()),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading pending verifications: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to load pending verifications',
                    'message' => $e->getMessage(),
                    'meetings' => [],
                    'site_visits' => [],
                ], 500);
            }
        });
        
        Route::get('/verifications/pending-closers', function (Request $request) {
            try {
                $closers = \App\Models\SiteVisit::where('closer_status', 'pending')
                    ->where('verification_status', 'verified')
                    ->with([
                        'lead:id,name,phone',
                        'creator:id,name',
                        'assignedTo:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'property_name', 'budget_range',
                        'visit_notes', 'closer_status', 'lead_id', 'created_by', 'assigned_to',
                        'photos', 'completion_proof_photos', 'closer_request_proof_photos'
                    ])
                    ->get()
                    ->map(function($visit) {
                        return [
                            'id' => $visit->id,
                            'customer_name' => $visit->customer_name,
                            'phone' => $visit->phone,
                            'scheduled_at' => $visit->scheduled_at ? $visit->scheduled_at->toIso8601String() : null,
                            'completed_at' => $visit->completed_at ? $visit->completed_at->toIso8601String() : null,
                            'status' => $visit->status,
                            'verification_status' => $visit->verification_status,
                            'property_name' => $visit->property_name,
                            'property_address' => $visit->property_address,
                            'budget_range' => $visit->budget_range,
                            'visit_notes' => $visit->visit_notes,
                            'closer_status' => $visit->closer_status,
                            'photos' => $visit->photos ?? [],
                            'completion_proof_photos' => $visit->completion_proof_photos ?? [],
                            'closer_request_proof_photos' => $visit->closer_request_proof_photos ?? [],
                            'lead' => $visit->lead ? ['id' => $visit->lead->id, 'name' => $visit->lead->name, 'phone' => $visit->lead->phone] : null,
                            'creator' => $visit->creator ? ['id' => $visit->creator->id, 'name' => $visit->creator->name] : null,
                            'assignedTo' => $visit->assignedTo ? ['id' => $visit->assignedTo->id, 'name' => $visit->assignedTo->name] : null,
                        ];
                    });
                
                return response()->json([
                    'data' => array_values($closers->toArray()),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading pending closers: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to load pending closers',
                    'message' => $e->getMessage(),
                    'data' => [],
                ], 500);
            }
        });
    });

    // Builder routes
    Route::prefix('builders')->group(function () {
        Route::get('/', [BuilderController::class, 'index']);
        Route::post('/', [BuilderController::class, 'store'])->middleware('role:admin,crm');
        Route::get('/{builder}', [BuilderController::class, 'show']);
        Route::put('/{builder}', [BuilderController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{builder}', [BuilderController::class, 'destroy'])->middleware('role:admin,crm');
        
        // Builder logo upload
        Route::post('/{builder}/logo', [BuilderController::class, 'uploadLogo'])->middleware('role:admin,crm');
        
        // Builder contacts
        Route::post('/{builder}/contacts', [BuilderController::class, 'addContact'])->middleware('role:admin,crm');
        Route::put('/{builder}/contacts/{contact}', [BuilderController::class, 'updateContact'])->middleware('role:admin,crm');
        Route::delete('/{builder}/contacts/{contact}', [BuilderController::class, 'deleteContact'])->middleware('role:admin,crm');
        
        // Builder projects (nested)
        Route::get('/{builder}/projects', [ProjectController::class, 'index']);
        Route::post('/{builder}/projects', [ProjectController::class, 'store'])->middleware('role:admin,crm');
    });

    // Project routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware('role:admin,crm');
        
        // Project detail (with contacts and collaterals)
        Route::get('/{project}/detail', [ProjectDetailController::class, 'show']);
        
        // Project collaterals
        Route::get('/{project}/collaterals', [ProjectCollateralController::class, 'index']);
        Route::get('/{project}/collaterals/buttons', [ProjectCollateralController::class, 'buttons']);
        Route::post('/{project}/collaterals', [ProjectCollateralController::class, 'store'])->middleware('role:admin,crm');
        
        // Pricing
        Route::get('/{project}/pricing', [PricingController::class, 'show']);
        Route::put('/{project}/pricing', [PricingController::class, 'update'])->middleware('role:admin,crm');
        
        // Unit types
        Route::get('/{project}/unit-types', [UnitTypeController::class, 'index']);
        Route::post('/{project}/unit-types', [UnitTypeController::class, 'store'])->middleware('role:admin,crm');
    });

    // Collateral routes (standalone)
    Route::prefix('collaterals')->group(function () {
        Route::put('/{collateral}', [ProjectCollateralController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{collateral}', [ProjectCollateralController::class, 'destroy'])->middleware('role:admin,crm');
    });

    // Unit type routes (standalone)
    Route::prefix('unit-types')->group(function () {
        Route::put('/{unitType}', [UnitTypeController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{unitType}', [UnitTypeController::class, 'destroy'])->middleware('role:admin,crm');
    });

    // Dynamic Forms API
    Route::prefix('forms')->group(function () {
        Route::get('/{identifier}', [\App\Http\Controllers\Api\DynamicFormController::class, 'getForm']);
        Route::get('/{identifier}/render', [\App\Http\Controllers\Api\DynamicFormController::class, 'renderForm']);
        Route::post('/{identifier}/submit', [\App\Http\Controllers\Api\DynamicFormController::class, 'submitForm']);
    });
});

