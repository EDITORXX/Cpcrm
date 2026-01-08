<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\TelecallerProfile;
use App\Models\SystemSettings;
use App\Services\UserAvailabilityService;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Regenerate CSRF token to prevent 419 errors
        $request->session()->regenerateToken();
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email'));
        }
        
        // Check maintenance mode - only allow admin to login
        if (SystemSettings::isMaintenanceMode()) {
            if (!$user->isAdmin()) {
                return back()->withErrors([
                    'email' => SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can login during maintenance mode.'),
                ])->withInput($request->only('email'))->with('maintenance_mode', true);
            }
        }

        // Ensure role relationship is loaded before login
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        // Mark attendance for telecallers
        if ($user->isTelecaller()) {
            $this->markTelecallerAttendance($user);
            // Generate API token for telecaller and store in session
            $token = $user->createToken('web-login-token')->plainTextToken;
            $request->session()->put('telecaller_api_token', $token);
            // Store password in session for auto-fill (temporary, cleared on logout)
            $request->session()->put('user_password_for_change', $request->password);
        }

        // Generate API token for sales managers
        if ($user->isSalesManager()) {
            $token = $user->createToken('web-login-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        // Redirect based on user role
        return $this->redirectBasedOnRole($user);
    }

    public function logout(Request $request)
    {
        try {
            // Clear telecaller API token from session
            $request->session()->forget('telecaller_api_token');
            // Clear stored password from session
            $request->session()->forget('user_password_for_change');
            // Clear sales manager API token
            $request->session()->forget('api_token');

            // Logout user
            Auth::logout();

            // Invalidate and regenerate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Exception $e) {
            // Even if there's an error (like expired session), try to logout
            Auth::logout();
        }

        // Always redirect to login page
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Quick login for development/testing - logs in user by ID
     */
    public function quickLogin(Request $request, $userId)
    {
        $user = User::where('id', $userId)
            ->where('is_active', true)
            ->with('role')
            ->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'User not found or inactive.']);
        }
        
        // Check maintenance mode - only allow admin to login
        if (SystemSettings::isMaintenanceMode()) {
            if (!$user->isAdmin()) {
                return redirect()->route('login')->withErrors([
                    'email' => SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can login during maintenance mode.'),
                ])->with('maintenance_mode', true);
            }
        }
        
        // Ensure role relationship is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        // Mark attendance for telecallers
        if ($user->isTelecaller()) {
            $this->markTelecallerAttendance($user);
            $token = $user->createToken('web-login-token')->plainTextToken;
            $request->session()->put('telecaller_api_token', $token);
        }

        // Generate API token for sales managers
        if ($user->isSalesManager()) {
            $token = $user->createToken('web-login-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return $this->redirectBasedOnRole($user);
    }

    private function redirectBasedOnRole($user)
    {
        $role = $user->role->slug ?? '';

        return match($role) {
            'telecaller' => redirect()->route('telecaller.dashboard'),
            'sales_manager' => $user->isSalesHead() 
                ? redirect()->route('sales-head.dashboard')
                : redirect()->route('sales-manager.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'crm', 'sales_executive' => redirect()->route('dashboard'),
            default => redirect('/'),
        };
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
}

