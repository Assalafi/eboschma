<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DebugRolesPermissions extends Command
{
    protected $signature = 'app:debug-roles-permissions';
    protected $description = 'Debug roles and permissions in the system';

    public function handle()
    {
        // Check if su@admin exists
        $this->info("Checking if su@admin exists...");
        $admin = User::where('email', 'su@admin')->first();
        if ($admin) {
            $this->info("Found user: {$admin->name} (ID: {$admin->old_id})");
        } else {
            $this->error("User with email su@admin not found.");
        }
        
        // Get all roles
        $this->info("\nExisting Roles:");
        $roles = Role::all();
        $this->table(
            ['ID', 'Name', 'Guard Name', 'Created At'],
            $roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'created_at' => $role->created_at,
                ];
            })
        );
        
        // Get all permissions
        $this->info("\nExisting Permissions:");
        $permissions = Permission::all();
        if ($permissions->count() > 0) {
            $sample = $permissions->take(5);
            $this->table(
                ['ID', 'Name', 'Guard Name', 'Created At'],
                $sample->map(function($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                        'created_at' => $permission->created_at,
                    ];
                })
            );
            $this->info("Showing 5 of {$permissions->count()} permissions");
        } else {
            $this->warn("No permissions found in the system.");
        }
        
        // Check configured guards
        $this->info("\nConfigured Guards:");
        $guards = config('auth.guards');
        foreach ($guards as $name => $config) {
            $this->info("- {$name}: " . json_encode($config));
        }
        
        // Create a super admin role
        $this->info("\nAttempting to create Super Admin role for each guard...");
        
        foreach (array_keys($guards) as $guard) {
            try {
                $roleName = "SuperAdmin-{$guard}";
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if (!$role) {
                    $role = Role::create(['name' => $roleName, 'guard_name' => $guard]);
                    $this->info("Created role {$roleName} for guard {$guard}");
                } else {
                    $this->info("Role {$roleName} already exists for guard {$guard}");
                }
                
                // Try to assign this role to the admin user
                if ($admin) {
                    try {
                        $admin->assignRole($role);
                        $this->info("Assigned role {$roleName} to {$admin->name}");
                    } catch (\Exception $e) {
                        $this->error("Failed to assign role {$roleName} to {$admin->name}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $this->error("Failed to create role for guard {$guard}: " . $e->getMessage());
            }
        }
        
        // Display user roles
        if ($admin) {
            $this->info("\nUser {$admin->name} roles:");
            foreach (array_keys($guards) as $guard) {
                $roles = $admin->getRoleNames($guard);
                $this->info("- Guard {$guard}: " . implode(', ', $roles->toArray()));
            }
        }
        
        return 0;
    }
}
