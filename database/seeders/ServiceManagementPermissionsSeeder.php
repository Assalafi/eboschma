<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ServiceManagementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Service Categories permissions
        $serviceCategoryPermissions = [
            'service-categories.view',
            'service-categories.create',
            'service-categories.edit',
            'service-categories.delete'
        ];

        // Service Types permissions
        $serviceTypePermissions = [
            'service-types.view',
            'service-types.create',
            'service-types.edit',
            'service-types.delete'
        ];

        // Service Items permissions
        $serviceItemPermissions = [
            'service-items.view',
            'service-items.create',
            'service-items.edit',
            'service-items.delete'
        ];

        // Create all permissions if they don't exist
        $allPermissions = array_merge(
            $serviceCategoryPermissions,
            $serviceTypePermissions,
            $serviceItemPermissions
        );

        // Create permissions for both 'web' and 'staff' guards
        $guards = ['web', 'staff'];
        foreach ($guards as $guard) {
            foreach ($allPermissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard
                ]);
            }
        }

        // Assign permissions to existing roles for both guards
        foreach ($guards as $guard) {
            // Super Admin gets all permissions
            $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
            $superAdmin->givePermissionTo($allPermissions);

            // Admin gets all service management permissions
            $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $admin->givePermissionTo($allPermissions);

            // Data Entry role gets view and create permissions only
            $dataEntry = Role::firstOrCreate(['name' => 'data-entry', 'guard_name' => $guard]);
            $dataEntry->givePermissionTo([
                'service-categories.view',
                'service-categories.create',
                'service-types.view',
                'service-types.create',
                'service-items.view',
                'service-items.create'
            ]);

            // Viewer role gets view permissions only
            $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);
            $viewer->givePermissionTo([
                'service-categories.view',
                'service-types.view',
                'service-items.view'
            ]);
        }

        $this->command->info('Service management permissions seeded successfully.');
    }
}
