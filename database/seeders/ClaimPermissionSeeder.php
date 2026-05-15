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

        // Create permissions if they don't exist
        $createdPermissions = [];
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'staff'
            ]);
            $createdPermissions[] = $permission->name;
        }

        // Assign permissions to roles
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($createdPermissions);
        }

        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $staff->givePermissionTo([
                'claim.view',
                'claim.create',
                'claim.edit',
                'claim.approve',
            ]);
        }

        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $manager->givePermissionTo([
                'claim.view',
                'claim.approve',
                'claim.pay',
            ]);
        }

        $viewer = Role::where('name', 'viewer')->first();
        if ($viewer) {
            $viewer->givePermissionTo('claim.view');
        }
    }
}
