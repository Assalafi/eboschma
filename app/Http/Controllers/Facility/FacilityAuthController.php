<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FacilityAuthController extends Controller
{
    /**
     * Show the facility staff login form.
     * If already authenticated (e.g. as admin), log out first so the session is clean.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        // Log out any existing session (both staff/admin and facility guards)
        if (Auth::guard('web')->check() || Auth::guard('staff')->check()) {
            Auth::guard('web')->logout();
            Auth::guard('staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('facility.auth.login');
    }

    /**
     * Handle a facility staff login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::guard('web')->user();
            
            // Log the successful login
            Log::info('Facility staff login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'facility_id' => $user->facility_id,
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('facility.dashboard');
        }

        Log::warning('Facility staff login failed', [
            'email' => $request->email,
            'ip_address' => $request->ip(),
        ]);

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
    }

    /**
     * Log the facility staff out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        
        Log::info('Facility staff logout', [
            'user_id' => $user->id,
            'email' => $user->email,
            'facility_id' => $user->facility_id,
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('facility.login');
    }
}
