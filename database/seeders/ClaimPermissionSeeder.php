<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClaimPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define claims permissions
        $permissions = [
            'claim.view' => 'View Claims',
            'claim.create' => 'Create Claims',
            'claim.edit' => 'Edit Claims',
            'claim.delete' => 'Delete Claims',
            'claim.approve' => 'Approve Claims',
            'claim.pay' => 'Pay Claims',
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

        // Assign permissions to roles (web guard)
        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($admin) {
            $admin->givePermissionTo(array_keys($permissions));
        }

        $staff = Role::where('name', 'staff')->where('guard_name', 'web')->first();
        if ($staff) {
            $staff->givePermissionTo([
                'claim.view',
                'claim.create',
                'claim.edit',
                'claim.approve',
            ]);
        }

        $manager = Role::where('name', 'manager')->where('guard_name', 'web')->first();
        if ($manager) {
            $manager->givePermissionTo([
                'claim.view',
                'claim.approve',
                'claim.pay',
            ]);
        }

        $viewer = Role::where('name', 'viewer')->where('guard_name', 'web')->first();
        if ($viewer) {
            $viewer->givePermissionTo('claim.view');
        }

        // Create and assign to staff guard roles specifically
        $adminStaff = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'staff']);
        $adminStaff->givePermissionTo(array_keys($permissions));

        $staffStaff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'staff']);
        $staffStaff->givePermissionTo([
            'claim.view',
            'claim.create',
            'claim.edit',
            'claim.approve',
        ]);

        $managerStaff = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'staff']);
        $managerStaff->givePermissionTo([
            'claim.view',
            'claim.approve',
            'claim.pay',
        ]);

        $viewerStaff = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'staff']);
        $viewerStaff->givePermissionTo('claim.view');
    }
}
