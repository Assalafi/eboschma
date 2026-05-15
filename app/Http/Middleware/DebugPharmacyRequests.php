<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DebugPharmacyRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('=== DEBUG PHARMACY REQUESTS MIDDLEWARE ===');
        Log::info('Route: ' . $request->route()->getName());
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->method());
        
        // Check authentication
        if (Auth::guard('staff')->check()) {
            Log::info('AUTHENTICATION: PASSED - User is logged in');
            $user = Auth::guard('staff')->user();
            Log::info('User ID: ' . $user->id);
            Log::info('User Email: ' . $user->email);
            
            // Check roles
            $roles = $user->getRoleNames()->toArray();
            Log::info('User Roles: [' . implode(', ', $roles) . ']');
            
            // Check if user has drugs.view permission
            if ($user->can('drugs.view')) {
                Log::info('PERMISSION: PASSED - User has drugs.view permission');
            } else {
                Log::warning('PERMISSION: FAILED - User does not have drugs.view permission');
            }
            
        } else {
            Log::warning('AUTHENTICATION: FAILED - User is not logged in');
        }
        
        Log::info('=== END DEBUG MIDDLEWARE ===');
        
        return $next($request);
    }
}
