<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Log out any authenticated account whose access has been disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach (['web', 'staff'] as $guard) {
            if (!Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();

            if ($user && !$user->is_active) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your account has been deactivated. Please contact an administrator.',
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been deactivated. Please contact an administrator.',
                ]);
            }
        }

        return $next($request);
    }
}
