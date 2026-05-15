<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WardManagementPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions for Wards
        $wardPermissions = [
            'wards.view',
            'wards.create',
            'wards.edit',
            'wards.delete',
        ];

        // Define permissions for Rooms
        $roomPermissions = [
            'rooms.view',
            'rooms.create',
            'rooms.edit',
            'rooms.delete',
        ];

        // Define permissions for Beds
        $bedPermissions = [
            'beds.view',
            'beds.create',
            'beds.edit',
            'beds.delete',
        ];

        // Define permissions for Nurse Ward Assignments
        $nurseWardPermissions = [
            'nurse-ward.view',
            'nurse-ward.create',
            'nurse-ward.edit',
            'nurse-ward.delete',
        ];

        // Define permissions for Doctor Ward Assignments
        $doctorWardPermissions = [
            'doctor-ward.view',
            'doctor-ward.create',
            'doctor-ward.edit',
            'doctor-ward.delete',
        ];

        // Define permissions for Facility Services
        $facilityServicesPermissions = [
            'facility-services.view',
            'facility-services.create',
            'facility-services.edit',
            'facility-services.delete',
        ];

        $allPermissions = array_merge(
            $wardPermissions,
            $roomPermissions,
            $bedPermissions,
            $nurseWardPermissions,
            $doctorWardPermissions,
            $facilityServicesPermissions
        );

        // Create permissions for both 'web' and 'staff' guards
        $guards = ['web', 'staff'];
        foreach ($guards as $guard) {
            foreach ($allPermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => $guard]
                );
            }
        }

        // Assign all permissions to super-admin
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($allPermissions);
        }

        // Assign all permissions to admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($allPermissions);
        }

        // Assign view and create permissions to data-entry (if exists)
        $dataEntry = Role::where('name', 'data-entry')->first();
        if ($dataEntry) {
            try {
                $dataEntry->givePermissionTo([
                    'wards.view',
                    'wards.create',
                    'rooms.view',
                    'rooms.create',
                    'beds.view',
                    'beds.create',
                    'nurse-ward.view',
                    'nurse-ward.create',
                    'doctor-ward.view',
                    'doctor-ward.create',
                ]);
            } catch (\Exception $e) {
                $this->command->warn('Could not assign permissions to data-entry role: ' . $e->getMessage());
            }
        }

        // Assign view permissions to viewer (if exists)
        $viewer = Role::where('name', 'viewer')->first();
        if ($viewer) {
            try {
                $viewer->givePermissionTo([
                    'wards.view',
                    'rooms.view',
                    'beds.view',
                    'nurse-ward.view',
                    'doctor-ward.view',
                ]);
            } catch (\Exception $e) {
                $this->command->warn('Could not assign permissions to viewer role: ' . $e->getMessage());
            }
        }

        $this->command->info('Ward management permissions seeded successfully!');
    }
}
