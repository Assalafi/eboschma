<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DrugStockRequestPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'drug-stock-requests.view',
            'drug-stock-requests.create',
            'drug-stock-requests.edit',
            'drug-stock-requests.delete',
            'drug-stock-requests.approve',
            'drug-stock-requests.reject',
            'drug-stock-requests.dispense',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'staff',
            ]);
        }

        // Full access for super-admin style roles
        $adminRoles = Role::whereIn('name', [
            'super-admin',
            'Super Admin',
            'Super Administrator',
            'Boschma Administrator',
            'System Administrator',
            'Admin',
        ])->where('guard_name', 'staff')->get();

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        // BODMA gets operational permissions (approval can be delegated separately
        // by granting drug-stock-requests.approve to another role/user).
        $bodmaRoles = Role::whereIn('name', ['BODMA', 'bodma', 'Bodma', 'BODMA Administrator'])
            ->where('guard_name', 'staff')
            ->get();

        foreach ($bodmaRoles as $role) {
            $role->givePermissionTo([
                'drug-stock-requests.view',
                'drug-stock-requests.create',
                'drug-stock-requests.edit',
                'drug-stock-requests.reject',
                'drug-stock-requests.dispense',
            ]);
        }

        $this->command->info('Drug Stock Request permissions created and assigned successfully.');
    }
}
