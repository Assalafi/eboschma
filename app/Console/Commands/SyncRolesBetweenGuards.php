<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class SyncRolesBetweenGuards extends Command
{
    protected $signature = 'app:sync-roles-between-guards';
    protected $description = 'Synchronize roles and permissions between web and staff guards';

    public function handle()
    {
        $this->info('Starting role synchronization between guards...');
        
        // Step 1: Get all roles from both guards
        $webRoles = Role::where('guard_name', 'web')->get();
        $staffRoles = Role::where('guard_name', 'staff')->get();
        
        $this->info("Found {$webRoles->count()} roles for 'web' guard");
        $this->info("Found {$staffRoles->count()} roles for 'staff' guard");
        
        // Step 2: Ensure all web roles exist for staff guard
        $this->info("\nCreating missing staff guard roles...");
        $createdCount = 0;
        
        foreach ($webRoles as $webRole) {
            // Check if this role exists for staff guard
            $existingStaffRole = Role::where('name', $webRole->name)
                                    ->where('guard_name', 'staff')
                                    ->first();
            
            if (!$existingStaffRole) {
                // Create the role for staff guard
                Role::create([
                    'name' => $webRole->name,
                    'guard_name' => 'staff'
                ]);
                
                $this->info("Created role '{$webRole->name}' for staff guard");
                $createdCount++;
            }
        }
        
        if ($createdCount === 0) {
            $this->info("No new staff roles needed to be created");
        } else {
            $this->info("Created {$createdCount} new roles for staff guard");
        }
        
        // Step 3: Ensure all staff roles exist for web guard
        $this->info("\nCreating missing web guard roles...");
        $createdCount = 0;
        
        foreach ($staffRoles as $staffRole) {
            // Skip the auto-generated ones from the previous step
            if (in_array($staffRole->name, $webRoles->pluck('name')->toArray())) {
                continue;
            }
            
            // Check if this role exists for web guard
            $existingWebRole = Role::where('name', $staffRole->name)
                                 ->where('guard_name', 'web')
                                 ->first();
            
            if (!$existingWebRole) {
                // Create the role for web guard
                Role::create([
                    'name' => $staffRole->name,
                    'guard_name' => 'web'
                ]);
                
                $this->info("Created role '{$staffRole->name}' for web guard");
                $createdCount++;
            }
        }
        
        if ($createdCount === 0) {
            $this->info("No new web roles needed to be created");
        } else {
            $this->info("Created {$createdCount} new roles for web guard");
        }
        
        // Step 4: Get all permissions from both guards
        $webPermissions = Permission::where('guard_name', 'web')->get();
        $staffPermissions = Permission::where('guard_name', 'staff')->get();
        
        $this->info("\nFound {$webPermissions->count()} permissions for 'web' guard");
        $this->info("Found {$staffPermissions->count()} permissions for 'staff' guard");
        
        // Step 5: Ensure all web permissions exist for staff guard
        $this->info("\nCreating missing staff guard permissions...");
        $createdCount = 0;
        
        foreach ($webPermissions as $webPermission) {
            // Check if this permission exists for staff guard
            $existingStaffPermission = Permission::where('name', $webPermission->name)
                                              ->where('guard_name', 'staff')
                                              ->first();
            
            if (!$existingStaffPermission) {
                // Create the permission for staff guard
                Permission::create([
                    'name' => $webPermission->name,
                    'guard_name' => 'staff'
                ]);
                
                $this->info("Created permission '{$webPermission->name}' for staff guard");
                $createdCount++;
            }
        }
        
        if ($createdCount === 0) {
            $this->info("No new staff permissions needed to be created");
        } else {
            $this->info("Created {$createdCount} new permissions for staff guard");
        }
        
        // Step 6: Sync role permissions between guards
        $this->info("\nSynchronizing role permissions between guards...");
        
        // For each role that exists in both guards, sync the permissions
        foreach ($webRoles as $webRole) {
            $staffRole = Role::where('name', $webRole->name)
                           ->where('guard_name', 'staff')
                           ->first();
            
            if ($staffRole) {
                // Get all permission names from the web role
                $permissionNames = $webRole->permissions->pluck('name')->toArray();
                
                if (!empty($permissionNames)) {
                    // Assign these permissions to the staff role
                    $staffRole->syncPermissions($permissionNames);
                    $this->info("Synced " . count($permissionNames) . " permissions for role '{$webRole->name}'");
                }
            }
        }
        
        $this->info("\nRole and permission synchronization complete!");
        
        return 0;
    }
}
