<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DrugStorePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'drug-store.view',
            'drug-store.stock-in',
        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->where('guard_name', 'staff')->exists()) {
                Permission::create(['name' => $permission, 'guard_name' => 'staff']);
            }
        }

        // Assign to super-admin role if it exists
        $superAdminRoles = Role::whereIn('name', [
            'super-admin',
            'Super Admin',
            'Super Administrator',
            'Boschma Administrator',
            'System Administrator',
            'Admin',
        ])->where('guard_name', 'staff')->get();

        foreach ($superAdminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        $this->command->info('Drug Store permissions created and assigned successfully.');
    }
}
