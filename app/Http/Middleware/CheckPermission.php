<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // First, check if the permission exists
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            $permissions = explode('|', $permission);
            foreach ($permissions as $perm) {
                $perm = trim($perm);
                try {
                    // Try first with the 'web' guard (default for users)
                    if ($user->hasPermissionTo($perm, 'web')) {
                        return $next($request);
                    }
                    
                    // If that doesn't work, try with the 'staff' guard
                    if ($user->hasPermissionTo($perm, 'staff')) {
                        return $next($request);
                    }
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    // Continue checking other permissions
                }
            }
            
            // For super admin/admin users, grant special override access
            if ($user->hasRole('Super Admin', 'staff') || $user->hasRole('super-admin', 'web') || $user->hasRole('Admin', 'staff') || $user->hasRole('admin', 'web') || $user->hasRole('Super Admin') || $user->hasRole('Admin')) {
                \Illuminate\Support\Facades\Log::info('Admin override', [
                    'user' => $user->email,
                    'permission' => $permission,
                    'url' => $request->fullUrl()
                ]);
                
                return $next($request);
            }
            
            // Otherwise show access denied
            return response()->view('errors.permission-denied', [
                'permission' => $permission,
                'page' => 'errors.permission-denied'
            ], 403);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            // Log this issue as it's a configuration problem
            \Illuminate\Support\Facades\Log::error('Permission does not exist', [
                'permission' => $permission,
                'user' => Auth::user()->email,
                'message' => $e->getMessage()
            ]);
            
            // Redirect to a nicer error page
            return response()->view('errors.system-error', [
                'message' => 'There was a system configuration error. Please contact an administrator.',
                'page' => 'errors.system-error'
            ], 500);
        }
        
        return $next($request);
    }
}
