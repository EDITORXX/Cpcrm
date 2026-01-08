<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class DebugController extends Controller
{
    public function testQuickLogin(Request $request)
    {
        $userId = $request->get('user_id');
        $debug = [];
        
        // Check maintenance mode
        try {
            $maintenanceMode = SystemSettings::isMaintenanceMode();
            $debug['maintenance_mode'] = $maintenanceMode;
            $debug['maintenance_message'] = SystemSettings::get('maintenance_message');
        } catch (\Exception $e) {
            $debug['maintenance_mode_error'] = $e->getMessage();
        }
        
        // Check user
        if ($userId) {
            try {
                $user = User::where('id', $userId)
                    ->where('is_active', true)
                    ->with('role')
                    ->first();
                
                if ($user) {
                    $debug['user_found'] = true;
                    $debug['user_id'] = $user->id;
                    $debug['user_name'] = $user->name;
                    $debug['user_email'] = $user->email;
                    $debug['user_is_active'] = $user->is_active;
                    
                    // Check role
                    if ($user->relationLoaded('role')) {
                        $debug['role_loaded'] = true;
                        $debug['role_id'] = $user->role->id ?? 'null';
                        $debug['role_name'] = $user->role->name ?? 'null';
                        $debug['role_slug'] = $user->role->slug ?? 'null';
                        
                        // Check if admin
                        try {
                            $isAdmin = $user->isAdmin();
                            $debug['is_admin'] = $isAdmin;
                            $debug['is_admin_check_error'] = null;
                        } catch (\Exception $e) {
                            $debug['is_admin'] = false;
                            $debug['is_admin_check_error'] = $e->getMessage();
                            $debug['is_admin_check_trace'] = $e->getTraceAsString();
                        }
                    } else {
                        $debug['role_loaded'] = false;
                        // Try to load
                        try {
                            $user->load('role');
                            $debug['role_loaded_after'] = true;
                            $debug['role_slug_after'] = $user->role->slug ?? 'null';
                        } catch (\Exception $e) {
                            $debug['role_load_error'] = $e->getMessage();
                        }
                    }
                    
                    // Check maintenance mode restriction
                    if ($maintenanceMode && !$isAdmin) {
                        $debug['login_blocked'] = true;
                        $debug['login_blocked_reason'] = 'Maintenance mode enabled and user is not admin';
                    } else {
                        $debug['login_blocked'] = false;
                    }
                } else {
                    $debug['user_found'] = false;
                    $debug['user_error'] = 'User not found or inactive';
                }
            } catch (\Exception $e) {
                $debug['user_check_error'] = $e->getMessage();
                $debug['user_check_trace'] = $e->getTraceAsString();
            }
        }
        
        // Check routes
        try {
            $debug['quick_login_route_exists'] = Route::has('quick-login');
            $debug['login_route_exists'] = Route::has('login');
        } catch (\Exception $e) {
            $debug['route_check_error'] = $e->getMessage();
        }
        
        // Check current request
        $debug['current_path'] = $request->path();
        $debug['current_uri'] = $request->getRequestUri();
        $debug['current_method'] = $request->method();
        $debug['is_quick_login_path'] = str_starts_with($request->path(), 'quick-login/');
        $debug['route_is_quick_login'] = $request->routeIs('quick-login');
        
        // Check middleware
        $debug['auth_check'] = Auth::check();
        if (Auth::check()) {
            $debug['current_user_id'] = Auth::id();
            $debug['current_user_name'] = Auth::user()->name ?? 'null';
        }
        
        // Check system_settings table
        try {
            $settings = DB::table('system_settings')->get();
            $debug['system_settings_count'] = $settings->count();
            $debug['system_settings'] = $settings->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            $debug['system_settings_error'] = $e->getMessage();
        }
        
        return view('admin.debug-quick-login', compact('debug', 'userId'));
    }
    
    public function attemptQuickLogin(Request $request, $userId)
    {
        $result = [];
        
        try {
            // Get user
            $user = User::where('id', $userId)
                ->where('is_active', true)
                ->with('role')
                ->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found or inactive',
                    'debug' => $result
                ]);
            }
            
            $result['user_found'] = true;
            $result['user_id'] = $user->id;
            $result['user_name'] = $user->name;
            
            // Check role
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            $result['role_slug'] = $user->role->slug ?? 'null';
            
            // Check if admin
            $isAdmin = $user->isAdmin();
            $result['is_admin'] = $isAdmin;
            
            // Check maintenance mode
            $maintenanceMode = SystemSettings::isMaintenanceMode();
            $result['maintenance_mode'] = $maintenanceMode;
            
            if ($maintenanceMode && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'error' => 'Maintenance mode enabled. Only admin can login.',
                    'debug' => $result
                ]);
            }
            
            // Attempt login
            Auth::login($user, false);
            $request->session()->regenerate();
            
            $result['login_success'] = true;
            $result['redirect_url'] = route('admin.dashboard');
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('admin.dashboard'),
                'debug' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'debug' => $result
            ], 500);
        }
    }
}
