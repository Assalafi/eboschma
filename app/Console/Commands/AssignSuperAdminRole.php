<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AssignSuperAdminRole extends Command
{
    protected $signature = 'app:assign-super-admin {email?}';
    protected $description = 'Assign Super Admin role to a specified user or to a predefined admin user';

    public function handle()
    {
        $email = $this->argument('email') ?: 'su@admin';
        
        $this->info("Looking for user with email: {$email}");
        
        // Find the user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }
        
        $this->info("Found user: {$user->name} (ID: {$user->old_id})");
        
        // Find or create Super Admin role
        $role = Role::where('name', 'Super Admin')->first();
        
        if (!$role) {
            $this->info("Super Admin role not found. Creating it now...");
            $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'staff']);
            $this->info("Super Admin role created successfully.");
            
            // Grant all existing permissions to this role
            try {
                // Use the Spatie Permission model to ensure we only get valid permissions
                $permissions = \Spatie\Permission\Models\Permission::where('guard_name', 'staff')->get();
                $this->info("Found {$permissions->count()} valid permissions to assign.");
                
                if ($permissions->count() > 0) {
                    $role->syncPermissions($permissions);
                    $this->info("Assigned all available permissions to Super Admin role.");
                } else {
                    $this->warn("No permissions found to assign. You'll need to create and assign permissions later.");
                }
            } catch (\Exception $e) {
                $this->error("Error while assigning permissions: {$e->getMessage()}");
                $this->warn("Continuing without assigning permissions.");
            }
        }
        
        // Assign role to user
        if ($user->hasRole('Super Admin', 'staff')) {
            $this->info("User already has Super Admin role.");
        } else {
            $user->assignRole('Super Admin', 'staff');
            $this->info("Successfully assigned Super Admin role to {$user->name}.");
        }
        
        return 0;
    }
}
