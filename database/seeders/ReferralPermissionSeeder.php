<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ReferralPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Referral permissions
        $permissions = [
            'referral.view' => 'View referrals and referral analytics',
            'referral.create' => 'Create new referrals', 
            'referral.edit' => 'Edit and update referrals',
            'referral.delete' => 'Delete referrals',
            'referral.settings' => 'Manage referral system settings',
        ];

        // Create permissions for both 'web' and 'staff' guards
        $guards = ['web', 'staff'];
        foreach ($guards as $guard) {
            foreach ($permissions as $name => $description) {
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard
                ]);
            }
        }

        // Assign all referral permissions to admin role (web guard)
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        // Assign referral permissions to staff role (web guard)
        $staffRole = Role::where('name', 'staff')->where('guard_name', 'web')->first();
        if ($staffRole) {
            $staffRole->givePermissionTo(['referral.view', 'referral.create', 'referral.edit']);
        }

        // Assign view-only permissions to viewer role if it exists (web guard)
        $viewerRole = Role::where('name', 'viewer')->where('guard_name', 'web')->first();
        if ($viewerRole) {
            $viewerRole->givePermissionTo(['referral.view']);
        }

        // Assign full permissions to manager role if it exists (web guard)
        $managerRole = Role::where('name', 'manager')->where('guard_name', 'web')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo(['referral.view', 'referral.create', 'referral.edit', 'referral.delete']);
        }

        // Create and assign to staff guard roles specifically
        $adminStaffRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'staff']);
        $adminStaffRole->givePermissionTo(array_keys($permissions));

        $staffStaffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'staff']);
        $staffStaffRole->givePermissionTo(['referral.view', 'referral.create', 'referral.edit']);

        $viewerStaffRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'staff']);
        $viewerStaffRole->givePermissionTo(['referral.view']);

        $managerStaffRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'staff']);
        $managerStaffRole->givePermissionTo(['referral.view', 'referral.create', 'referral.edit', 'referral.delete']);

        $this->command->info('✅ Referral permissions created and assigned to roles successfully!');
    }
}
