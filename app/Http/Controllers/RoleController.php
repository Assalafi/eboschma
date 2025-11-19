<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\ActivityLog;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only show roles from the staff guard to avoid duplicates
        $roles = Role::where('guard_name', 'staff')
                ->orderBy('id', 'DESC')
                ->paginate(5);
                
        $page = 'roles.index';
        return view('page', compact('page', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only get permissions with the staff guard name to avoid duplicates
        $permissions = Permission::where('guard_name', 'staff')->get();
        
        // Group permissions by category for easier UI display
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'general';
            
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }
            
            $groupedPermissions[$group][] = $permission;
        }
        
        $page = 'roles.create';
        return view('page', compact('page', 'groupedPermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:roles,name,NULL,id,guard_name,staff',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        // Create the role for staff guard first
        $staffRole = Role::create([
            'name' => $request->input('name'),
            'guard_name' => 'staff'
        ]);
        
        // Also create the same role for web guard
        $webRole = Role::create([
            'name' => $request->input('name'),
            'guard_name' => 'web'
        ]);
        
        // Sync permissions for the staff guard role (which will also sync to web guard)
        $permissionIds = $request->input('permissions', []);
        $this->syncRolePermissionsWithConsistency($staffRole, $permissionIds);
        
        // Log the activity
        ActivityLog::log(
            'create',
            'role',
            $staffRole->id,
            $staffRole->name,
            [
                'permissions' => $staffRole->permissions->pluck('name')->toArray(),
                'web_role_id' => $webRole->id
            ]
        );
        
        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::find($id);
        
        // Get ALL permissions assigned to this role
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        // Get ALL permissions with the role's guard name
        $permissions = Permission::where('guard_name', $role->guard_name)->get();
        
        // Group permissions by category for easier UI display
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'general';
            
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }
            
            $groupedPermissions[$group][] = $permission;
        }
        
        $page = 'roles.show';
        return view('page', compact('page', 'role', 'rolePermissions', 'groupedPermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::find($id);
        
        // Get ALL permissions assigned to this role, regardless of guard
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        // Get ALL permissions with the role's guard name
        $permissions = Permission::where('guard_name', $role->guard_name)->get();
        
        // Debug information to help diagnose the issue
        \Illuminate\Support\Facades\Log::info('Role: ' . $role->name . ' with ID: ' . $role->id);
        \Illuminate\Support\Facades\Log::info('Permission IDs assigned to role: ' . implode(', ', $rolePermissions));
        \Illuminate\Support\Facades\Log::info('Available permissions for guard "' . $role->guard_name . '":', $permissions->pluck('name', 'id')->toArray());
        
        // Group permissions by category for easier UI display
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'general';
            
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }
            
            $groupedPermissions[$group][] = $permission;
        }
        
        $page = 'roles.edit';
        return view('page', compact('page', 'role', 'rolePermissions', 'groupedPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $role = Role::find($id);
        
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id . ',id,guard_name,' . $role->guard_name,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        $role = Role::find($id);
        $oldData = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray()
        ];
        
        $role->name = $request->input('name');
        $role->save();
        
        $permissionIds = $request->input('permissions', []);
        $this->syncRolePermissionsWithConsistency($role, $permissionIds);
        
        // Log the activity
        ActivityLog::log(
            'update',
            'role',
            $role->id,
            $role->name,
            [
                'old' => $oldData,
                'new' => [
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray()
                ]
            ]
        );
        
        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    /**
     * Helper method to ensure consistent permission syncing
     * This method ensures permissions are properly synced and available
     * when viewing/editing roles later
     */
    private function syncRolePermissionsWithConsistency($role, $permissionIds)
    {
        // First get permissions that match the role's guard
        $guardMatchedPermissions = Permission::whereIn('id', $permissionIds)
            ->where('guard_name', $role->guard_name)
            ->get();
            
        // We'll also need to get the permission names to ensure consistency
        $permissionNames = $guardMatchedPermissions->pluck('name')->toArray();
        
        // Now sync by direct permission objects rather than IDs
        $role->syncPermissions($guardMatchedPermissions);
        
        // Log the permission assignments for debugging
        \Illuminate\Support\Facades\Log::info('Permissions synced for role: ' . $role->name, [
            'permission_names' => $permissionNames,
            'permission_ids' => $guardMatchedPermissions->pluck('id')->toArray()
        ]);
        
        // AUTOMATIC GUARD SYNCHRONIZATION: Find and update equivalent role in other guard
        $otherGuardName = ($role->guard_name === 'web') ? 'staff' : 'web';
        $otherGuardRole = Role::where('name', $role->name)
            ->where('guard_name', $otherGuardName)
            ->first();
            
        if ($otherGuardRole) {
            // Find equivalent permissions in the other guard
            $otherGuardPermissions = Permission::whereIn('name', $permissionNames)
                ->where('guard_name', $otherGuardName)
                ->get();
                
            // Sync permissions to the other guard's role
            $otherGuardRole->syncPermissions($otherGuardPermissions);
            
            \Illuminate\Support\Facades\Log::info('Automatically synced permissions to ' . $otherGuardName . ' guard for role: ' . $otherGuardRole->name, [
                'permission_names' => $otherGuardPermissions->pluck('name')->toArray(),
                'permission_ids' => $otherGuardPermissions->pluck('id')->toArray()
            ]);
            
            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
    
    public function destroy(string $id): RedirectResponse
    {
        // Don't allow deletion of essential roles (super-admin)
        if (in_array($id, [1])) { // Assuming 1 is the super-admin role ID
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete system role');
        }
        
        $role = Role::find($id);
        $roleName = $role->name;
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        // Log the activity before deletion
        ActivityLog::log(
            'delete',
            'role',
            $id,
            $roleName,
            [
                'permissions' => $rolePermissions
            ]
        );
        
        DB::table('roles')->where('id', $id)->delete();
        
        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
