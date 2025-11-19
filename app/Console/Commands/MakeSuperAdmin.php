<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MakeSuperAdmin extends Command
{
    protected $signature = 'app:make-super-admin {email=su@admin} {--password=admin123}';
    protected $description = 'Configure user as super admin with all necessary permissions';

    public function handle()
    {
        // 1. Find or create the user
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->info("User with email {$email} not found. Creating new user...");
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($this->option('password'))
            ]);
            $this->info("Created new user with ID: {$user->old_id}");
        } else {
            $this->info("Found existing user: {$user->name} (ID: {$user->old_id})");
        }
        
        // 2. Find or create a corresponding staff record
        $staff = Staff::where('email', $email)->first();
        if (!$staff) {
            $this->info("Creating staff record for {$email}...");
            
            // Let's check the Staff model's required columns
            $staffColumns = DB::getSchemaBuilder()->getColumnListing('staff');
            $this->info("Staff table columns: " . implode(', ', $staffColumns));
            
            // Create with all necessary fields
            $staff = Staff::create([
                'fullname' => $user->name,
                'email' => $email,
                'job_title' => 'System Administrator',
                'department' => 'IT Department',
                'phone' => '123456789',
                'status' => 'active',
                // Include password field if it exists (might be removed in migrations)
                'password' => Hash::make($this->option('password'))
            ]);
            $this->info("Created staff record with ID: {$staff->id}");
        } else {
            $this->info("Found existing staff record: {$staff->fullname} (ID: {$staff->id})");
        }
        
        // 3. Check if Super Admin role exists with staff guard
        $role = Role::where('name', 'Super Admin')
                    ->where('guard_name', 'staff')
                    ->first();
                    
        if (!$role) {
            $this->info("Creating 'Super Admin' role with 'staff' guard...");
            $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'staff']);
        } else {
            $this->info("Found existing 'Super Admin' role: {$role->id}");
        }
        
        // 4. Create common permissions if they don't exist
        $basePermissions = [
            // Staff management
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
            // Role management
            'role.view', 'role.create', 'role.edit', 'role.delete',
            // Permission management
            'permission.view', 'permission.create', 'permission.edit', 'permission.delete',
            // Reports
            'report.view', 'report.trial-balance', 'report.journal', 'report.general-ledger',
            // Budget management
            'budget.view', 'budget.create', 'budget.edit', 'budget.delete',
            // Department management
            'department.view', 'department.create', 'department.edit', 'department.delete',
            // Economic code management
            'economic-code.view', 'economic-code.create', 'economic-code.edit', 'economic-code.delete',
            // Activity logs
            'activity.logs.view'
        ];
        
        $this->info("Ensuring all required permissions exist with 'staff' guard...");
        $createdCount = 0;
        
        foreach ($basePermissions as $permName) {
            $permission = Permission::where('name', $permName)
                                    ->where('guard_name', 'staff')
                                    ->first();
            
            if (!$permission) {
                Permission::create(['name' => $permName, 'guard_name' => 'staff']);
                $createdCount++;
            }
        }
        
        $this->info("Created {$createdCount} new permissions");
        
        // 5. Assign all permissions to Super Admin role
        $allPermissions = Permission::where('guard_name', 'staff')->get();
        $role->syncPermissions($allPermissions);
        $this->info("Assigned {$allPermissions->count()} permissions to Super Admin role");
        
        // 6. Modify the model_has_roles table to accept UUIDs and link the role to staff
        $this->info("Modifying database schema for UUIDs and assigning role...");
        
        try {
            // First check if we need to modify the column type
            $columnInfo = DB::select("SHOW COLUMNS FROM model_has_roles WHERE Field = 'model_id'")[0];
            $this->info("Current model_id column type: {$columnInfo->Type}");
            
            if (strpos($columnInfo->Type, 'varchar') === false && strpos($columnInfo->Type, 'char') === false) {
                $this->info("Modifying model_has_roles.model_id to accept UUIDs...");
                
                // First drop any existing data and foreign keys that might prevent modification
                DB::statement('DELETE FROM model_has_roles');
                
                // Change the column type to VARCHAR to support UUIDs
                DB::statement('ALTER TABLE model_has_roles MODIFY model_id VARCHAR(36) NOT NULL');
                $this->info("Schema updated successfully");
            }
            
            // Use the direct roles relationship instead of raw SQL for better reliability
            // and to avoid guard name confusion
            $staff->roles()->sync([$role->id]);
                
            $this->info("Successfully assigned Super Admin role to staff record");
            
        } catch (\Exception $e) {
            $this->error("Error modifying schema or assigning role: " . $e->getMessage());
            $this->warn("You may need to manually modify the model_has_roles table to accept UUID values");
            return 1;
        }
        
        // 7. Also assign Web Super Admin for consistency
        $webRole = Role::where('name', 'super-admin')
                      ->where('guard_name', 'web')
                      ->first();
        
        if ($webRole) {
            // Use the direct roles relation for more reliable assignment
            $user->roles()->sync([$webRole->id]);
            $this->info("Also assigned web super-admin role to user account");
        }
        
        $this->info("=================================================");
        $this->info("SUCCESSFULLY CREATED SUPER ADMIN");
        $this->info("Email: {$email}");
        $this->info("Password: " . $this->option('password') . " (if newly created)");
        $this->info("=================================================");
        
        return 0;
    }
}
