<?php

namespace App\Http\Controllers;

use App\Services\TelecallerDashboardService;
use Illuminate\Http\Request;

class TelecallerController extends Controller
{
    /**
     * Show telecaller dashboard
     */
    public function dashboard(Request $request, TelecallerDashboardService $service)
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to access the dashboard.');
        }
        
        $dateRange = $request->get('date_range', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $service->getDashboardData($userId, $dateRange, $startDate, $endDate);
        return view('telecaller.sections.dashboard', compact('data', 'dateRange', 'startDate', 'endDate'));
    }

    /**
     * Show telecaller tasks page
     */
    public function tasks()
    {
        return view('telecaller.sections.tasks');
    }

    /**
     * Show telecaller leads page
     */
    public function leads()
    {
        return view('telecaller.sections.leads');
    }

    /**
     * Show telecaller reports page
     */
    public function reports()
    {
        return view('telecaller.sections.reports');
    }

    /**
     * Show telecaller verification pending page
     */
    public function verificationPending()
    {
        return view('telecaller.sections.verification-pending');
    }

    /**
     * Show telecaller profile page
     */
    public function profile()
    {
        return view('telecaller.sections.profile');
    }
}
