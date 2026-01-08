<?php

namespace App\Http\Controllers;

use App\Models\SmartImportExecution;
use App\Models\SmartImportLeadAssignment;
use App\Models\SmartImportAutomation;
use App\Models\User;
use App\Services\SlaTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmartImportAnalyticsController extends Controller
{
    protected $slaService;

    public function __construct(SlaTrackingService $slaService)
    {
        $this->slaService = $slaService;
    }

    public function index(Request $request)
    {
        $dateRange = $request->get('date_range', '30'); // days
        $startDate = Carbon::now()->subDays($dateRange);
        $endDate = Carbon::now();

        // Overall Stats
        $stats = [
            'total_executions' => SmartImportExecution::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_leads_imported' => SmartImportExecution::whereBetween('created_at', [$startDate, $endDate])->sum('imported_leads'),
            'total_leads_queued' => SmartImportExecution::whereBetween('created_at', [$startDate, $endDate])->sum('queued_leads'),
            'total_leads_failed' => SmartImportExecution::whereBetween('created_at', [$startDate, $endDate])->sum('failed_leads'),
            'success_rate' => 0,
        ];

        $totalProcessed = $stats['total_leads_imported'] + $stats['total_leads_failed'];
        if ($totalProcessed > 0) {
            $stats['success_rate'] = round(($stats['total_leads_imported'] / $totalProcessed) * 100, 2);
        }

        // Leads per Agent
        $leadsPerAgent = SmartImportLeadAssignment::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('count(*) as total'))
            ->groupBy('assigned_to')
            ->with('assignedTo')
            ->get()
            ->map(function($item) {
                return [
                    'user_name' => $item->assignedTo->name ?? 'Unknown',
                    'total' => $item->total,
                ];
            });

        // Conversion Rate by Source
        $conversionBySource = DB::table('smart_import_lead_assignments')
            ->join('leads', 'smart_import_lead_assignments.lead_id', '=', 'leads.id')
            ->whereBetween('smart_import_lead_assignments.created_at', [$startDate, $endDate])
            ->select('leads.source', DB::raw('count(*) as total'))
            ->groupBy('leads.source')
            ->get();

        // SLA Compliance
        $slaMetrics = $this->slaService->getComplianceMetrics([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Daily Import Trends
        $dailyTrends = SmartImportExecution::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as executions'),
                DB::raw('sum(imported_leads) as leads_imported'),
                DB::raw('sum(failed_leads) as leads_failed')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Overload/Idle Detection
        $userWorkloads = SmartImportLeadAssignment::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('count(*) as assigned_count'))
            ->groupBy('assigned_to')
            ->with('assignedTo')
            ->get()
            ->map(function($item) {
                return [
                    'user_id' => $item->assigned_to,
                    'user_name' => $item->assignedTo->name ?? 'Unknown',
                    'assigned_count' => $item->assigned_count,
                ];
            });

        $avgWorkload = $userWorkloads->avg('assigned_count');
        $overloadedUsers = $userWorkloads->where('assigned_count', '>', $avgWorkload * 1.5);
        $idleUsers = $userWorkloads->where('assigned_count', '<', $avgWorkload * 0.5);

        // Top Performing Automations
        $topAutomations = SmartImportExecution::whereBetween('created_at', [$startDate, $endDate])
            ->select('automation_id', DB::raw('count(*) as execution_count'), DB::raw('sum(imported_leads) as total_leads'))
            ->groupBy('automation_id')
            ->with('automation')
            ->orderByDesc('total_leads')
            ->limit(10)
            ->get();

        return view('smart-import.analytics', compact(
            'stats',
            'leadsPerAgent',
            'conversionBySource',
            'slaMetrics',
            'dailyTrends',
            'overloadedUsers',
            'idleUsers',
            'topAutomations',
            'dateRange'
        ));
    }
}
