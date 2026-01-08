<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\TelecallerStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TelecallerStatusController extends Controller
{
    protected $statusService;

    public function __construct(TelecallerStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Index - show all telecaller statuses
     */
    public function index()
    {
        $telecallerRoleId = Role::where('slug', Role::TELECALLER)->value('id');
        
        $telecallers = User::where('role_id', $telecallerRoleId)
            ->where('is_active', true)
            ->with(['telecallerProfile', 'telecallerDailyLimit'])
            ->get()
            ->map(function ($telecaller) {
                $profile = $telecaller->telecallerProfile;
                $pendingCount = $this->statusService->getPendingLeadsCount($telecaller->id);
                $canReceive = $this->statusService->canReceiveAssignment($telecaller->id);

                return [
                    'id' => $telecaller->id,
                    'name' => $telecaller->name,
                    'email' => $telecaller->email,
                    'is_absent' => $profile?->is_absent ?? false,
                    'absent_reason' => $profile?->absent_reason,
                    'absent_until' => $profile?->absent_until,
                    'pending_count' => $pendingCount,
                    'max_pending_leads' => $profile?->max_pending_leads ?? 50,
                    'can_receive' => $canReceive['can_receive'],
                ];
            });

        return view('lead-assignment.telecaller-status', compact('telecallers'));
    }

    /**
     * Update telecaller status
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'is_absent' => 'required|boolean',
            'absent_reason' => 'nullable|string|max:500',
            'absent_until' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        
        if (!$user->isTelecaller()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a telecaller.'
            ], 422);
        }

        $absentUntil = $request->absent_until ? Carbon::parse($request->absent_until) : null;

        $profile = $this->statusService->toggleAbsentStatus(
            $user->id,
            $request->is_absent,
            $request->absent_reason,
            $absentUntil
        );

        return response()->json([
            'success' => true,
            'message' => 'Telecaller status updated successfully.',
            'profile' => $profile->fresh(),
        ]);
    }

    /**
     * Get telecaller status (API)
     */
    public function getStatus(Request $request)
    {
        $telecallerId = $request->input('telecaller_id');
        
        if (!$telecallerId) {
            return response()->json(['error' => 'Telecaller ID required'], 422);
        }

        $telecaller = User::findOrFail($telecallerId);
        $canReceive = $this->statusService->canReceiveAssignment($telecaller->id);
        $pendingCount = $this->statusService->getPendingLeadsCount($telecaller->id);
        $profile = $this->statusService->getOrCreateProfile($telecaller->id);

        return response()->json([
            'telecaller' => [
                'id' => $telecaller->id,
                'name' => $telecaller->name,
            ],
            'is_absent' => $profile->isCurrentlyAbsent(),
            'absent_reason' => $profile->absent_reason,
            'absent_until' => $profile->absent_until,
            'pending_count' => $pendingCount,
            'max_pending_leads' => $profile->max_pending_leads,
            'can_receive' => $canReceive['can_receive'],
        ]);
    }
}
