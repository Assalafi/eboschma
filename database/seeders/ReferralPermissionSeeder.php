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

        $createdPermissions = [];
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'staff'
            ]);
            $createdPermissions[] = $permission->name;
        }

        // Assign all referral permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($createdPermissions);
        }

        // Assign referral permissions to staff role (view, create, edit only)
        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $staffRole->givePermissionTo(['referral.view', 'referral.create', 'referral.edit']);
        }

        // Assign view-only permissions to viewer role if it exists
        $viewerRole = Role::where('name', 'viewer')->first();
        if ($viewerRole) {
            $viewerRole->givePermissionTo(['referral.view']);
        }

        // Assign full permissions to manager role if it exists
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo(['referral.view', 'referral.create', 'referral.edit', 'referral.delete']);
        }

        $this->command->info('✅ Referral permissions created and assigned to roles successfully!');
    }
}
