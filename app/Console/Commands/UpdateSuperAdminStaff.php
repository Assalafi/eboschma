<?php

namespace App\Console\Commands;

use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSuperAdminStaff extends Command
{
    protected $signature = 'app:update-super-admin-staff';
    protected $description = 'Update the su@admin staff record to ensure it shows in the staff list';

    public function handle()
    {
        $staff = Staff::where('email', 'su@admin')->first();
        
        if (!$staff) {
            $this->error('Staff record for su@admin not found. Please run app:make-super-admin first.');
            return 1;
        }
        
        $this->info("Found staff record for su@admin: ID {$staff->id}");
        
        // Check if the staff appears in the list - we'll make it more visible
        $this->info("Ensuring staff record is complete...");
        
        // The Staff model shows only these fields exist
        if (empty($staff->fullname)) {
            $staff->fullname = 'Super Administrator';
        }
        
        if (empty($staff->phone)) {
            $staff->phone = '123-456-7890';
        }
        
        // Save any updates
        $staff->save();
        $this->info("Updated staff record with necessary fields");
        
        // Check if the staff is assigned to Super Admin role
        $roleAssignments = DB::table('model_has_roles')
            ->where('model_id', $staff->id)
            ->where('model_type', 'App\\Models\\Staff')
            ->get();
            
        if ($roleAssignments->isEmpty()) {
            $this->warn("No role assignments found for this staff member");
            
            // Get the Super Admin role
            $superAdminRole = DB::table('roles')
                ->where('name', 'Super Admin')
                ->where('guard_name', 'staff')
                ->first();
                
            if ($superAdminRole) {
                // Assign the role
                DB::table('model_has_roles')->insert([
                    'role_id' => $superAdminRole->id,
                    'model_type' => 'App\\Models\\Staff',
                    'model_id' => $staff->id
                ]);
                
                $this->info("Assigned Super Admin role to staff record");
            } else {
                $this->error("Super Admin role not found");
            }
        } else {
            $this->info("Staff has " . $roleAssignments->count() . " role assignments");
        }
        
        // Verify the staff record is properly linked to a user
        $user = DB::table('users')->where('email', 'su@admin')->first();
        if ($user) {
            $this->info("Confirmed user record exists with matching email");
        } else {
            $this->error("No user record found with email su@admin");
        }
        
        $this->info("Super Admin staff record update complete");
        
        return 0;
    }
}
