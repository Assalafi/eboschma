<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\ActivityLog;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all permissions without pagination
        $permissions = Permission::orderBy('name', 'ASC')->get();
        
        // No need to pre-process groupedPermissions as it's now handled in the view
        $page = 'permissions.index';
        return view('page', compact('page', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get existing permission groups to help with categorization
        $existingGroups = [];
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'general';
            if (!in_array($group, $existingGroups)) {
                $existingGroups[] = $group;
            }
        }
        
        sort($existingGroups);
        
        $page = 'permissions.create';
        return view('page', compact('page', 'existingGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required|string',
            'group' => 'required|string',
            'action' => 'required|string',
        ]);
        
        $permissionName = $request->input('group') . '.' . $request->input('action');
        
        // Check if permission already exists
        if (Permission::where('name', $permissionName)->exists()) {
            return redirect()->route('permissions.index')
                ->with('error', "Permission '$permissionName' already exists.");
        }
        
        // Create the permission
        $permission = Permission::create(['name' => $permissionName]);
        
        // Log the activity
        ActivityLog::log(
            'create',
            'permission',
            $permission->id,
            $permission->name,
            [
                'group' => $request->input('group'),
                'action' => $request->input('action')
            ]
        );
        
        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::findOrFail($id);
        $page = 'permissions.show';
        return view('page', compact('page', 'permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);
        
        // Get the group and action from the permission name
        $parts = explode('.', $permission->name);
        $group = $parts[0] ?? 'general';
        $action = $parts[1] ?? '';
        
        // Get existing permission groups to help with categorization
        $existingGroups = [];
        $permissions = Permission::all();
        foreach ($permissions as $p) {
            $parts = explode('.', $p->name);
            $g = $parts[0] ?? 'general';
            if (!in_array($g, $existingGroups)) {
                $existingGroups[] = $g;
            }
        }
        
        sort($existingGroups);
        
        $page = 'permissions.edit';
        return view('page', compact('page', 'permission', 'group', 'action', 'existingGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required|string',
            'group' => 'required|string',
            'action' => 'required|string',
        ]);
        
        $permission = Permission::findOrFail($id);
        $newPermissionName = $request->input('group') . '.' . $request->input('action');
        
        // Check if new name already exists and it's not the current permission
        if ($newPermissionName !== $permission->name && Permission::where('name', $newPermissionName)->exists()) {
            return redirect()->route('permissions.edit', $id)
                ->with('error', "Permission '$newPermissionName' already exists.");
        }
        
        // Store the old name for logging
        $oldName = $permission->name;
        
        // Update the permission
        $permission->name = $newPermissionName;
        $permission->save();
        
        // Log the activity
        ActivityLog::log(
            'update',
            'permission',
            $permission->id,
            $permission->name,
            [
                'old' => ['name' => $oldName],
                'new' => ['name' => $permission->name]
            ]
        );
        
        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $permission = Permission::findOrFail($id);
        
        // Check if permission is in use by any role
        $roleCount = DB::table('role_has_permissions')->where('permission_id', $id)->count();
        if ($roleCount > 0) {
            return redirect()->route('permissions.index')
                ->with('error', "Cannot delete permission '{$permission->name}' as it is used by {$roleCount} role(s).");
        }
        
        // Store permission info before deletion for logging
        $permissionId = $permission->id;
        $permissionName = $permission->name;
        
        // Log the activity before deletion
        ActivityLog::log(
            'delete',
            'permission',
            $permissionId,
            $permissionName
        );
        
        // Delete the permission
        $permission->delete();
        
        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully');
    }
}
