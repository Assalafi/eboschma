<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }
    
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Log the login attempt
        \Log::info('Login attempt', ['email' => $request->email]);
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Try staff login first
        $staff = Staff::where('email', $request->email)->first();
        
        // Log if staff was found
        if ($staff) {
            \Log::info('Staff found', ['email' => $staff->email, 'id' => $staff->id]);
        } else {
            \Log::info('Staff not found');
        }
        
        if ($staff && Hash::check($request->password, $staff->password)) {
            \Log::info('Staff password check passed');
            
            try {
                Auth::guard('staff')->login($staff);
                \Log::info('Staff login successful');
                $request->session()->regenerate();
            
            // Store staff email in session
            session(['user_email' => $staff->email]);
            session()->put('session', date('Y'));
            session()->put('sector', 'basic');
            
            \Log::info('Staff session set, redirecting');
            
            // Check if user has Customer Care role and redirect to crm
            if ($staff->hasRole('Customer Care')) {
                return redirect()->route('crm.index');
            }
            
            // Check if user has BODMA role and redirect to drug-stock-requests
            if ($staff->hasRole('BODMA')) {
                return redirect()->route('drug-stock-requests.index');
            }
            
            return redirect()->intended('/');
            } catch (\Exception $e) {
                \Log::error('Staff login error: ' . $e->getMessage());
                return back()->withErrors([
                    'email' => 'Login error: ' . $e->getMessage(),
                ]);
            }
        } else if ($staff) {
            \Log::info('Staff password check failed');
        }

        // If staff login fails, try regular user login
        \Log::info('Trying regular user login');
        
        try {
            if (Auth::attempt($credentials)) {
                \Log::info('Regular user login successful');
                $request->session()->regenerate();
                
                // Store user email in session
                session(['user_email' => $request->email]);
                session()->put('session', date('Y'));
                session()->put('sector', 'basic');
                
                $user = Auth::user();
                if ($user && method_exists($user, 'hasRole') && $user->hasRole('Customer Care')) {
                    return redirect()->route('crm.index');
                }
                
                return redirect()->intended('/');
            } else {
                \Log::info('Regular user login failed');
            }
        } catch (\Exception $e) {
            \Log::error('User login error: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Login error: ' . $e->getMessage(),
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        // Logout from both guards
        Auth::guard('staff')->logout();
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
