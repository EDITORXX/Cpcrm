<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        $userRole = $user->role->slug ?? null;
        
        // Handle sales_head role - if user is Sales Head, treat as sales_head role
        if ($user->isSalesHead() && in_array('sales_head', $roles)) {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
            }
            abort(403, 'Forbidden. Insufficient permissions.');
        }

        return $next($request);
    }
}

