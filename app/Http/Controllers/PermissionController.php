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
        // Get all permissions for staff guard only
        $permissions = Permission::where('guard_name', 'staff')->orderBy('name', 'ASC')->get();
        
        // No need to pre-process groupedPermissions as it's now handled in the view
        $page = 'permissions.index';
        return view('page', compact('page', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get existing permission groups from staff guard to help with categorization
        $existingGroups = [];
        $permissions = Permission::where('guard_name', 'staff')->get();
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
        \Log::info('Permission creation started', [
            'request_data' => $request->all(),
            'user_id' => auth('staff')->id(),
            'user_email' => auth('staff')->user()->email ?? 'unknown'
        ]);

        $this->validate($request, [
            'group' => 'required|string',
            'action' => 'required|string',
        ]);
        
        $permissionName = $request->input('group') . '.' . $request->input('action');
        
        \Log::info('Permission name generated', [
            'permission_name' => $permissionName,
            'group' => $request->input('group'),
            'action' => $request->input('action')
        ]);
        
        // Check if permission already exists for staff guard
        $existingPermission = Permission::where('name', $permissionName)->where('guard_name', 'staff')->first();
        
        \Log::info('Checking existing permission', [
            'permission_name' => $permissionName,
            'exists' => !is_null($existingPermission),
            'existing_permission' => $existingPermission ? $existingPermission->toArray() : null
        ]);
        
        if ($existingPermission) {
            \Log::warning('Permission already exists', [
                'permission_name' => $permissionName,
                'existing_id' => $existingPermission->id
            ]);
            
            return redirect()->route('permissions.index')
                ->with('error', "Permission '$permissionName' already exists.");
        }
        
        try {
            \Log::info('Attempting to create permission', [
                'permission_name' => $permissionName,
                'guard_name' => 'staff'
            ]);
            
            // Create the permission with staff guard
            $permission = Permission::create([
                'name' => $permissionName,
                'guard_name' => 'staff'
            ]);
            
            \Log::info('Permission created successfully', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'created_at' => $permission->created_at
            ]);
            
            // Verify it was actually saved
            $savedPermission = Permission::find($permission->id);
            \Log::info('Verifying saved permission', [
                'found_in_db' => !is_null($savedPermission),
                'saved_data' => $savedPermission ? $savedPermission->toArray() : null
            ]);
            
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
                
        } catch (\Exception $e) {
            \Log::error('Permission creation failed', [
                'permission_name' => $permissionName,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->route('permissions.index')
                ->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple newly created resources in storage.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        \Log::info('Bulk permission creation started', [
            'request_data' => $request->all(),
            'user_id' => auth('staff')->id(),
            'user_email' => auth('staff')->user()->email ?? 'unknown'
        ]);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.group' => 'required|string|max:255',
            'permissions.*.action' => 'required|string|max:255',
        ]);

        $created = 0;
        $errors = [];
        $duplicates = [];

        foreach ($request->permissions as $index => $permissionData) {
            // Skip empty rows
            if (empty($permissionData['group']) && empty($permissionData['action'])) {
                continue;
            }

            try {
                $permissionName = $permissionData['group'] . '.' . $permissionData['action'];
                
                \Log::info('Processing permission', [
                    'index' => $index,
                    'permission_name' => $permissionName,
                    'group' => $permissionData['group'],
                    'action' => $permissionData['action']
                ]);

                // Check if permission already exists for staff guard
                $existingPermission = Permission::where('name', $permissionName)
                    ->where('guard_name', 'staff')
                    ->first();

                if ($existingPermission) {
                    \Log::info('Permission already exists, skipping', [
                        'permission_name' => $permissionName,
                        'existing_id' => $existingPermission->id
                    ]);
                    $duplicates[] = $permissionName;
                    continue;
                }

                // Create the permission with staff guard
                $permission = Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'staff'
                ]);

                \Log::info('Permission created successfully', [
                    'permission_id' => $permission->id,
                    'permission_name' => $permission->name,
                    'guard_name' => $permission->guard_name
                ]);

                // Log the activity
                ActivityLog::log(
                    'create',
                    'permission',
                    $permission->id,
                    $permission->name,
                    [
                        'group' => $permissionData['group'],
                        'action' => $permissionData['action'],
                        'bulk_created' => true
                    ]
                );

                $created++;

            } catch (\Exception $e) {
                \Log::error('Permission creation failed', [
                    'index' => $index,
                    'permission_name' => $permissionName ?? 'unknown',
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString()
                ]);

                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        // Build success message
        $message = "Successfully created {$created} permission(s).";
        
        if (!empty($duplicates)) {
            $message .= " " . count($duplicates) . " permission(s) already existed: " . implode(', ', array_slice($duplicates, 0, 3));
            if (count($duplicates) > 3) {
                $message .= " and " . (count($duplicates) - 3) . " more.";
            }
        }
        
        if (!empty($errors)) {
            $message .= " Some rows had errors: " . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " and " . (count($errors) - 3) . " more errors.";
            }
        }

        \Log::info('Bulk permission creation completed', [
            'created' => $created,
            'duplicates' => count($duplicates),
            'errors' => count($errors),
            'total_processed' => count($request->permissions)
        ]);

        return redirect()->route('permissions.index')
            ->with('success', $message);
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
