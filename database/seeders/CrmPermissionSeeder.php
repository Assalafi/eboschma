<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CrmPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create CRM permissions
        $permissions = [
            'crm.view' => 'View CRM tickets',
            'crm.create' => 'Create CRM tickets', 
            'crm.edit' => 'Edit CRM tickets',
            'crm.delete' => 'Delete CRM tickets',
        ];

        $createdPermissions = [];
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'staff'
            ]);
            $createdPermissions[] = $permission->name;
        }

        // Assign CRM permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($createdPermissions);
        }

        // Assign CRM permissions to staff role (view, create, edit only)
        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $staffRole->givePermissionTo(['crm.view', 'crm.create', 'crm.edit']);
        }
    }
}
