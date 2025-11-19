<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MobileAuthController extends Controller
{
    /**
     * Login for mobile app (Super Admin and Enumerators only)
     */
    public function login(Request $request)
    {
        Log::info('🔐 Mobile Login Attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            Log::warning('❌ Mobile Login Validation Failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff) {
            Log::warning('❌ Mobile Login Failed - User Not Found', [
                'email' => $request->email
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user is Super Admin or Enumerator (using Spatie roles)
        $allowedRoles = ['Super Admin', 'super-admin', 'Enumerator', 'enumerator'];
        $hasAllowedRole = false;
        
        foreach ($allowedRoles as $role) {
            if ($staff->hasRole($role)) {
                $hasAllowedRole = true;
                break;
            }
        }
        
        if (!$hasAllowedRole) {
            Log::warning('❌ Mobile Login Failed - Unauthorized Role', [
                'email' => $request->email,
                'roles' => $staff->getRoleNames()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only Super Admins and Enumerators can access the mobile app.'
            ], 403);
        }

        if (!Hash::check($request->password, $staff->password)) {
            Log::warning('❌ Mobile Login Failed - Invalid Password', [
                'email' => $request->email
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create API token
        $token = $staff->createToken('mobile-app')->plainTextToken;

        Log::info('✅ Mobile Login Successful', [
            'staff_id' => $staff->id,
            'email' => $staff->email,
            'roles' => $staff->getRoleNames()->toArray()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $staff->id,
                    'fullname' => $staff->fullname,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'role' => $staff->getRoleNames()->first() ?? 'N/A',
                    'is_super_admin' => $staff->hasRole(['Super Admin', 'super-admin']),
                ],
            ]
        ], 200);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Log::info('🚪 Mobile Logout', [
            'staff_id' => $request->user()->id,
            'email' => $request->user()->email
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $staff = $request->user();
        
        Log::info('👤 Profile Retrieved', [
            'staff_id' => $staff->id,
            'email' => $staff->email
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $staff->id,
                'fullname' => $staff->fullname,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'role' => $staff->role->name ?? 'N/A',
                'is_super_admin' => strtolower($staff->role->name ?? '') === 'super admin',
                'status' => $staff->status,
                'created_at' => $staff->created_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $staff = $request->user();
        
        Log::info('🔐 Password Update Request', [
            'staff_id' => $staff->id,
            'email' => $staff->email
        ]);

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            Log::warning('❌ Password Update Validation Failed', [
                'staff_id' => $staff->id,
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $staff->password)) {
            Log::warning('❌ Password Update Failed - Incorrect Current Password', [
                'staff_id' => $staff->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // Update password
        $staff->password = Hash::make($request->new_password);
        $staff->save();

        Log::info('✅ Password Updated Successfully', [
            'staff_id' => $staff->id,
            'email' => $staff->email
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ], 200);
    }
}
