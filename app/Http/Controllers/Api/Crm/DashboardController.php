<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmAssignment;
use App\Models\LeadAssignment;
use App\Models\Prospect;
use App\Models\TelecallerTask;
use App\Models\User;
use App\Models\TelecallerDailyLimit;
use App\Models\TelecallerProfile;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get date range based on filter type
     */
    private function getDateRange($dateRange)
    {
        $today = Carbon::today();
        
        switch ($dateRange) {
            case 'today':
                return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
            case 'this_week':
                return [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
            case 'this_month':
                return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];
            case 'this_year':
                return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
            case 'till_date':
            case 'all_time':
            default:
                return [null, null];
        }
    }

    /**
     * Get top 4 stats cards
     */
    public function getStats(Request $request)
    {
        $dateRange = $request->get('date_range', 'all_time');
        [$startDate, $endDate] = $this->getDateRange($dateRange);

        // Total Assigned Leads: Count of all active LeadAssignment records
        $totalAssignedQuery = LeadAssignment::where('is_active', true);
        if ($startDate && $endDate) {
            $totalAssignedQuery->whereBetween('assigned_at', [$startDate, $endDate]);
        }
        $totalAssigned = $totalAssignedQuery->count();

        // Called Leads: Count of all completed TelecallerTask records
        $calledQuery = TelecallerTask::where('status', 'completed');
        if ($startDate && $endDate) {
            $calledQuery->whereBetween('completed_at', [$startDate, $endDate]);
        }
        $called = $calledQuery->count();

        // Interested: Count of verified/approved prospects
        $interestedQuery = Prospect::whereIn('verification_status', ['verified', 'approved']);
        if ($startDate && $endDate) {
            $interestedQuery->whereBetween('verified_at', [$startDate, $endDate]);
        }
        $interested = $interestedQuery->count();

        // Not Interested: Sum of called_not_interested in CrmAssignment + rejected prospects
        $notInterestedCrmQuery = CrmAssignment::where('call_status', 'called_not_interested');
        if ($startDate && $endDate) {
            $notInterestedCrmQuery->whereBetween('assigned_at', [$startDate, $endDate]);
        }
        $notInterestedCrm = $notInterestedCrmQuery->count();

        $notInterestedProspectsQuery = Prospect::where('verification_status', 'rejected');
        if ($startDate && $endDate) {
            $notInterestedProspectsQuery->whereBetween('verified_at', [$startDate, $endDate]);
        }
        $notInterestedProspects = $notInterestedProspectsQuery->count();

        $notInterested = $notInterestedCrm + $notInterestedProspects;

        return response()->json([
            'total_assigned' => $totalAssigned,
            'called' => $called,
            'not_interested' => $notInterested,
            'interested' => $interested,
        ]);
    }

    /**
     * Get telecaller performance stats
     */
    public function getTelecallerStats(Request $request)
    {
        try {
            $dateRange = $request->get('date_range', 'today');
            [$startDate, $endDate] = $this->getDateRange($dateRange);
            $isToday = $dateRange === 'today';

            // Get all telecallers - always return all, even if no data
            $telecallers = User::whereHas('role', function($q) {
                $q->where('slug', Role::TELECALLER);
            })->get();

            // If no telecallers found, return empty array
            if ($telecallers->isEmpty()) {
                return response()->json([]);
            }

            $result = [];

            foreach ($telecallers as $telecaller) {
                try {
                    $userId = $telecaller->id;
                    $profile = TelecallerProfile::firstOrCreate(['user_id' => $userId]);
                    $dailyLimit = TelecallerDailyLimit::firstOrCreate(['user_id' => $userId]);

                    // Allocated - use LeadAssignment table for actual assigned count
                    $allocatedQuery = LeadAssignment::where('assigned_to', $userId)
                        ->where('is_active', true);
                    if ($startDate && $endDate) {
                        $allocatedQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                    }
                    $allocated = $allocatedQuery->count();

                    // Base query for CrmAssignment stats
                    $baseQuery = CrmAssignment::where('assigned_to', $userId);
                    if ($startDate && $endDate) {
                        $baseQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                    }

                    // Remaining: Leads where call has NOT been made yet
                    // Count LeadAssignments, excluding leads where:
                    // - A completed TelecallerTask exists, OR
                    // - A CrmAssignment exists with call activity (cnp_count > 0 OR call_status != 'pending')
                    $leadIdsWithCalls = DB::table('telecaller_tasks')
                        ->where('assigned_to', $userId)
                        ->where('status', 'completed')
                        ->distinct()
                        ->pluck('lead_id')
                        ->merge(
                            DB::table('crm_assignments')
                                ->where('assigned_to', $userId)
                                ->where(function($q) {
                                    $q->where('cnp_count', '>', 0)
                                      ->orWhere('call_status', '!=', 'pending');
                                })
                                ->distinct()
                                ->pluck('lead_id')
                        )
                        ->unique()
                        ->values();
                    
                    $remainingQuery = LeadAssignment::where('assigned_to', $userId)
                        ->where('is_active', true);
                    
                    if ($leadIdsWithCalls->isNotEmpty()) {
                        $remainingQuery->whereNotIn('lead_id', $leadIdsWithCalls);
                    }
                    
                    if ($startDate && $endDate) {
                        $remainingQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                    }
                    $remaining = $remainingQuery->count();

                    // Called: Count of completed TelecallerTask records (actual calls made)
                    $calledQuery = TelecallerTask::where('assigned_to', $userId)
                        ->where('status', 'completed');
                    if ($startDate && $endDate) {
                        $calledQuery->whereBetween('completed_at', [$startDate, $endDate]);
                    }
                    $called = $calledQuery->count();

                    // Interested: Verified prospects created by this telecaller
                    $interestedQuery = Prospect::where('telecaller_id', $userId)
                        ->whereIn('verification_status', ['verified', 'approved']);
                    if ($startDate && $endDate) {
                        $interestedQuery->whereBetween('verified_at', [$startDate, $endDate]);
                    }
                    $interested = $interestedQuery->count();

                    // Not Interested: Sum of called_not_interested in CrmAssignment + rejected prospects
                    $notInterestedCrm = (clone $baseQuery)
                        ->where('call_status', 'called_not_interested')
                        ->count();
                    
                    $notInterestedProspectsQuery = Prospect::where('telecaller_id', $userId)
                        ->where('verification_status', 'rejected');
                    if ($startDate && $endDate) {
                        $notInterestedProspectsQuery->whereBetween('verified_at', [$startDate, $endDate]);
                    }
                    $notInterestedProspects = $notInterestedProspectsQuery->count();
                    
                    $notInterested = $notInterestedCrm + $notInterestedProspects;

                    // CNP: Pending calls with cnp_count > 0 (call later status)
                    $cnp = (clone $baseQuery)
                        ->where('call_status', 'pending')
                        ->where('cnp_count', '>', 0)
                        ->count();

                    $result[] = [
                        'telecaller_id' => $userId,
                        'telecaller_name' => $telecaller->name,
                        'username' => $telecaller->name,
                        'allocated' => $allocated ?? 0,
                        'called' => $called ?? 0,
                        'remaining' => $remaining ?? 0,
                        'interested' => $interested ?? 0,
                        'not_interested' => $notInterested ?? 0,
                        'cnp' => $cnp ?? 0,
                        'daily_limit' => $dailyLimit->overall_daily_limit ?? 0,
                        'is_absent' => $profile->isCurrentlyAbsent() ?? false,
                        'max_pending_leads' => $profile->max_pending_leads ?? 0,
                    ];
                } catch (\Exception $e) {
                    // Log error but continue with other telecallers
                    Log::error('Error processing telecaller stats for user ' . $telecaller->id . ': ' . $e->getMessage());
                    continue;
                }
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in getTelecallerStats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load telecaller stats: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get daily prospects with filters and pagination
     */
    public function getDailyProspects(Request $request)
    {
        $dateRange = $request->get('date_range', 'all_time');
        [$startDate, $endDate] = $this->getDateRange($dateRange);
        
        $query = Prospect::with(['createdBy', 'assignedManager', 'assignment']);

        // Date filter
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== 'all') {
            $query->where('created_by', $request->user_id);
        }

        // Filter by verification status
        if ($request->has('verification_status') && $request->verification_status !== 'all') {
            $query->where('verification_status', $request->verification_status);
        }

        // Get total count before pagination
        $total = $query->count();

        // Pagination
        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);
        
        $prospects = $query->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Calculate response time and format data
        $formattedProspects = $prospects->map(function($prospect) {
            $responseTime = null;
            if ($prospect->verified_at && $prospect->created_at) {
                $seconds = $prospect->verified_at->diffInSeconds($prospect->created_at);
                $responseTime = $this->formatResponseTime($seconds);
            }

            return [
                'id' => $prospect->id,
                'customer_name' => $prospect->customer_name,
                'phone' => $prospect->phone,
                'budget' => $prospect->budget,
                'preferred_location' => $prospect->preferred_location,
                'size' => $prospect->size,
                'purpose' => $prospect->purpose,
                'possession' => $prospect->possession,
                'notes' => $prospect->notes,
                'employee_remark' => $prospect->employee_remark,
                'manager_remark' => $prospect->manager_remark,
                'verification_status' => $prospect->verification_status,
                'verified_at' => $prospect->verified_at?->format('Y-m-d H:i:s'),
                'created_at' => $prospect->created_at->format('Y-m-d H:i:s'),
                'created_by_name' => $prospect->createdBy->name ?? null,
                'assigned_manager_name' => $prospect->assignedManager->name ?? null,
                'response_time' => $responseTime,
                'response_time_seconds' => $prospect->verified_at && $prospect->created_at 
                    ? $prospect->verified_at->diffInSeconds($prospect->created_at) 
                    : null,
            ];
        });

        // Stats
        $statsQuery = Prospect::query();
        if ($startDate && $endDate) {
            $statsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($request->has('user_id') && $request->user_id !== 'all') {
            $statsQuery->where('created_by', $request->user_id);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending_verification' => (clone $statsQuery)->where('verification_status', 'pending_verification')->count(),
            'verified' => (clone $statsQuery)->where('verification_status', 'verified')->count(),
            'rejected' => (clone $statsQuery)->where('verification_status', 'rejected')->count(),
        ];

        // Stats by user
        $statsByUserQuery = Prospect::query()
            ->select('created_by', DB::raw('COUNT(*) as count'))
            ->groupBy('created_by')
            ->with('createdBy');
        
        if ($startDate && $endDate) {
            $statsByUserQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $statsByUser = $statsByUserQuery->get()->map(function($item) {
            return [
                'user_id' => $item->created_by,
                'username' => $item->createdBy->name ?? 'Unknown',
                'count' => $item->count,
            ];
        });

        // Daily breakdown
        $dailyBreakdownQuery = Prospect::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(createdBy.name, ":", COUNT(*)) SEPARATOR ", ") as users')
            )
            ->groupBy('date', 'created_by')
            ->with('createdBy');
        
        if ($startDate && $endDate) {
            $dailyBreakdownQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        // Simplified daily breakdown - group by date only
        $dailyBreakdown = DB::table('prospects')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($item) {
                // Get users for this date
                $users = Prospect::whereDate('created_at', $item->date)
                    ->select('created_by', DB::raw('COUNT(*) as user_count'))
                    ->groupBy('created_by')
                    ->with('createdBy')
                    ->get()
                    ->map(function($u) {
                        return ($u->createdBy->name ?? 'Unknown') . ':' . $u->user_count;
                    })
                    ->toArray();

                return [
                    'date' => $item->date,
                    'count' => $item->count,
                    'users' => $users,
                ];
            });

        return response()->json([
            'data' => $formattedProspects,
            'stats' => $stats,
            'stats_by_user' => $statsByUser,
            'daily_breakdown' => $dailyBreakdown,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total),
            ],
        ]);
    }

    /**
     * Format response time in human readable format
     */
    private function formatResponseTime($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $result = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) {
                $result .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
            return $result;
        } else {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $result = $days . ' day' . ($days > 1 ? 's' : '');
            if ($hours > 0) {
                $result .= ' ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            return $result;
        }
    }
}
