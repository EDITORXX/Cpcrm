<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\SiteVisit;
use App\Models\FollowUp;
use App\Models\User;
use App\Services\TargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $targetService;

    public function __construct(TargetService $targetService)
    {
        $this->targetService = $targetService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $data = [
            'stats' => $this->getStats($user),
            'recent_leads' => $this->getRecentLeads($user),
            'upcoming_followups' => $this->getUpcomingFollowups($user),
            'upcoming_site_visits' => $this->getUpcomingSiteVisits($user),
        ];

        // Add target progress for telecallers
        if ($user->isTelecaller()) {
            $targetProgress = $this->targetService->getTargetProgress($user->id);
            $data['targets'] = $targetProgress;
        }

        // Add team target progress for managers
        if ($user->isSalesManager()) {
            $teamProgress = $this->targetService->getTeamTargetsProgress($user->id);
            $data['team_targets'] = $teamProgress;
        }

        // Add Sales Head specific data
        if ($user->isSalesHead()) {
            $data['sales_head_data'] = $this->getSalesHeadData($user);
        }

        // Add system overview for admin/CRM
        if ($user->isAdmin() || $user->isCrm()) {
            $overview = $this->targetService->getSystemOverview();
            $data['target_overview'] = $overview;
        }

        return response()->json($data);
    }

    private function getStats($user)
    {
        $leadQuery = Lead::query();
        $siteVisitQuery = SiteVisit::query();
        $followUpQuery = FollowUp::query();

        // Apply role-based filtering
        if ($user->isSalesExecutive() || $user->isTelecaller()) {
            $leadIds = Lead::whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            })->pluck('id');

            $leadQuery->whereIn('id', $leadIds);
            $siteVisitQuery->where('assigned_to', $user->id);
            $followUpQuery->where('created_by', $user->id);
        } elseif ($user->isSalesHead()) {
            // Sales Head sees all team data including nested teams
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $leadIds = Lead::whereHas('activeAssignments', function ($q) use ($allTeamMemberIds) {
                    $q->whereIn('assigned_to', $allTeamMemberIds);
                })->pluck('id');

                $leadQuery->whereIn('id', $leadIds);
                $siteVisitQuery->whereIn('assigned_to', $allTeamMemberIds);
                $followUpQuery->whereIn('created_by', $allTeamMemberIds);
            } else {
                // No team members, return empty results
                $leadQuery->whereRaw('1 = 0');
                $siteVisitQuery->whereRaw('1 = 0');
                $followUpQuery->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            // Only get leads from verified prospects of team members
            $leadIds = Lead::whereHas('prospects', function ($q) use ($teamMemberIds) {
                $q->whereIn('telecaller_id', $teamMemberIds)
                  ->whereIn('verification_status', ['verified', 'approved']);
            })->pluck('id');

            $leadQuery->whereIn('id', $leadIds);
            $siteVisitQuery->whereIn('assigned_to', $teamMemberIds);
            $followUpQuery->whereIn('created_by', $teamMemberIds);
        }

        return [
            'total_leads' => $leadQuery->count(),
            'new_leads' => (clone $leadQuery)->where('status', 'new')->count(),
            'qualified_leads' => (clone $leadQuery)->where('status', 'qualified')->count(),
            'closed_won' => (clone $leadQuery)->where('status', 'closed_won')->count(),
            'upcoming_site_visits' => $siteVisitQuery->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->count(),
            'pending_followups' => $followUpQuery->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->count(),
        ];
    }

    private function getRecentLeads($user, $limit = 5)
    {
        $query = Lead::with(['creator', 'activeAssignments.assignedTo']);

        if ($user->isSalesExecutive() || $user->isTelecaller()) {
            $query->whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        } elseif ($user->isSalesHead()) {
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->whereHas('activeAssignments', function ($q) use ($allTeamMemberIds) {
                    $q->whereIn('assigned_to', $allTeamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereHas('activeAssignments', function ($q) use ($teamMemberIds) {
                $q->whereIn('assigned_to', $teamMemberIds);
            });
        }

        return $query->latest()->limit($limit)->get();
    }

    private function getUpcomingFollowups($user, $limit = 5)
    {
        $query = FollowUp::with(['lead', 'creator'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now());

        if ($user->isSalesExecutive() || $user->isTelecaller()) {
            $query->where('created_by', $user->id);
        } elseif ($user->isSalesHead()) {
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->whereIn('created_by', $allTeamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereIn('created_by', $teamMemberIds);
        }

        return $query->orderBy('scheduled_at')->limit($limit)->get();
    }

    private function getUpcomingSiteVisits($user, $limit = 5)
    {
        $query = SiteVisit::with(['lead', 'assignedTo'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now());

        if ($user->isSalesExecutive() || $user->isTelecaller()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isSalesHead()) {
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->whereIn('assigned_to', $allTeamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereIn('assigned_to', $teamMemberIds);
        }

        return $query->orderBy('scheduled_at')->limit($limit)->get();
    }

    /**
     * Get Sales Head specific data
     */
    private function getSalesHeadData($user)
    {
        $allTeamMemberIds = $user->getAllTeamMemberIds();
        
        // Get all Sales Managers
        $salesManagers = User::where('manager_id', $user->id)
            ->whereHas('role', function($q) {
                $q->where('slug', 'sales_manager');
            })
            ->count();

        // Get all Sales Executives
        $salesExecutives = User::whereIn('manager_id', array_merge([$user->id], User::where('manager_id', $user->id)->pluck('id')->toArray()))
            ->whereHas('role', function($q) {
                $q->where('slug', 'sales_executive');
            })
            ->count();

        // Get all Telecallers
        $telecallers = User::whereIn('manager_id', array_merge([$user->id], $allTeamMemberIds))
            ->whereHas('role', function($q) {
                $q->where('slug', 'telecaller');
            })
            ->count();

        return [
            'total_managers' => $salesManagers,
            'total_executives' => $salesExecutives,
            'total_telecallers' => $telecallers,
            'pending_verifications' => Lead::where('needs_verification', true)->count(),
        ];
    }
}
