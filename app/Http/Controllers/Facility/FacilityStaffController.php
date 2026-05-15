<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffPosition;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// DB
use Illuminate\Support\Facades\DB;

class FacilityStaffController extends Controller
{
    /**
     * Check if current user has admin position
     */
    private function isAdmin(): bool
    {
        $user = Auth::guard('web')->user();
        $adminPositions = [
            'Hospital Administrator',
            'Admin', 
            'AAAA',
            'AAA',
            'AA'
        ];
        
        return $user && in_array($user->staffPosition->name ?? '', $adminPositions);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check admin permission
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage staff.');
        }
        $facilityId = Auth::guard('web')->user()->facility_id;
        $search = $request->get('search', '');
        $position = $request->get('position', '');
        
        // Start with staff query for current facility only
        $query = User::with(['staffPosition'])
            ->where('facility_id', $facilityId);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply position filter
        if ($position) {
            $query->where('staff_position_id', $position);
        }
        
        $staff = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get statistics for current facility
        $stats = [
            'total_staff' => User::where('facility_id', $facilityId)->count(),
            'with_photo' => User::where('facility_id', $facilityId)->whereNotNull('passport')->count(),
            'new_this_month' => User::where('facility_id', $facilityId)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count()
        ];
        
        $staffPositions = StaffPosition::orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        
        return view('facility.staff.index', compact('staff', 'stats', 'staffPositions', 'search', 'position', 'roles'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Check admin permission
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage staff.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        $staffPositions = StaffPosition::orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        
        return view('facility.staff.create', compact('facilityId', 'staffPositions', 'roles'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check admin permission
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage staff.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'staff_position_id' => 'required|exists:staff_positions,id',
            'role_id' => 'required|array|min:1',
            'role_id.*' => 'required|exists:roles,id',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            $data = $request->except(['passport', 'password_confirmation', 'role_id']);
            $data['password'] = Hash::make($request->password);
            $data['facility_id'] = $facilityId;
            
            // Handle passport upload
            if ($request->hasFile('passport')) {
                $passport = $request->file('passport');
                $passportPath = $passport->store('staff-passports', 'public');
                $data['passport'] = $passportPath;
            }
            
            $user = User::create($data);
            // Assign multiple roles
            foreach ($request->role_id as $roleId) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                    'model_uuid' => $user->id,
                ]);
            }
            return redirect()->route('facility.staff.index')
                ->with('success', 'Staff member added successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating facility staff: ' . $e->getMessage());
            return back()->with('error', 'Failed to add staff member. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // Check admin permission
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage staff.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $staff = User::where('id', $id)
            ->where('facility_id', $facilityId)
            ->firstOrFail();
            
        $staffPositions = StaffPosition::orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        
        return view('facility.staff.edit', compact('staff', 'staffPositions', 'roles'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        //dd($request->all());
        // Check admin permission
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage staff.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $staff = User::where('id', $id)
            ->where('facility_id', $facilityId)
            ->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'staff_position_id' => 'required|exists:staff_positions,id',
            'role_id' => 'required|array|min:1',
            'role_id.*' => 'required|exists:roles,id',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            $data = $request->except(['passport', 'password_confirmation', 'role_id']);
            
            // Handle password update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }
            
            // Handle passport upload
            if ($request->hasFile('passport')) {
                // Delete old passport if exists
                if ($staff->passport) {
                    Storage::disk('public')->delete($staff->passport);
                }
                
                $passport = $request->file('passport');
                $passportPath = $passport->store('staff-passports', 'public');
                $data['passport'] = $passportPath;
            }
            
            $staff->update($data);
            
            // Remove existing roles and assign new ones
            DB::table('model_has_roles')
                ->where('model_id', $id)
                ->where('model_type', 'App\Models\User')
                ->delete();
            
            foreach ($request->role_id as $roleId) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $id,
                    'model_uuid' => $id,
                ]);
            }
            
            return redirect()->route('facility.staff.index')
                ->with('success', 'Staff member updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating facility staff: ' . $e->getMessage());
            return back()->with('error', 'Failed to update staff member. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        // Check admin permission
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage staff.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        try {
            $staff = User::where('id', $id)
                ->where('facility_id', $facilityId)
                ->firstOrFail();
            
            // Prevent deletion of the currently logged-in user
            if ($staff->id === Auth::guard('web')->id()) {
                return back()->with('error', 'You cannot delete your own account.');
            }
            
            // Delete passport if exists
            if ($staff->passport) {
                Storage::disk('public')->delete($staff->passport);
            }
            
            $staff->delete();
            
            return redirect()->route('facility.staff.index')
                ->with('success', 'Staff member deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting facility staff: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete staff member. Please try again.');
        }
    }
}
