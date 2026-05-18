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

        // Assign CRM permissions to admin role (web guard)
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        // Assign CRM permissions to staff role (web guard)
        $staffRole = Role::where('name', 'staff')->where('guard_name', 'web')->first();
        if ($staffRole) {
            $staffRole->givePermissionTo(['crm.view', 'crm.create', 'crm.edit']);
        }

        // Create and assign to staff guard roles specifically
        $adminStaffRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'staff']);
        $adminStaffRole->givePermissionTo(array_keys($permissions));

        $staffStaffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'staff']);
        $staffStaffRole->givePermissionTo(['crm.view', 'crm.create', 'crm.edit']);
    }
}
