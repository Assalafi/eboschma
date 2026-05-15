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

        foreach($allPermissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        // Assign permissions to existing roles
        
        // Super Admin gets all permissions
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($allPermissions);
        }

        // Admin gets all service management permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($allPermissions);
        }

        // Data Entry role gets view and create permissions only
        $dataEntryRole = Role::where('name', 'data-entry')->first();
        if ($dataEntryRole) {
            $dataEntryRole->givePermissionTo([
                'service-categories.view',
                'service-categories.create',
                'service-types.view',
                'service-types.create',
                'service-items.view',
                'service-items.create'
            ]);
        }

        // Viewer role gets view permissions only
        $viewerRole = Role::where('name', 'viewer')->first();
        if ($viewerRole) {
            $viewerRole->givePermissionTo([
                'service-categories.view',
                'service-types.view',
                'service-items.view'
            ]);
        }

        $this->command->info('Service management permissions seeded successfully.');
    }
}
