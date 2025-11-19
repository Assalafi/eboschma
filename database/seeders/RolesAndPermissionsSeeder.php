<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        // Staff management permissions
        $staffPermissions = [
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete'
        ];

        // Department management permissions
        $departmentPermissions = [
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
            'department.data-entry'
        ];

        // Budget management permissions
        $budgetPermissions = [
            'budget.view',
            'budget.create',
            'budget.edit',
            'budget.delete',
            'budget.upload'
        ];

        // Financial report permissions
        $reportPermissions = [
            'report.trial-balance',
            'report.journal',
            'report.general-ledger',
            'report.economic-codes'
        ];
        
        // System management permissions
        $systemPermissions = [
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'permission.view',
            'permission.create',
            'permission.edit',
            'permission.delete',
            'activity.logs.view',
        ];

        // Create all permissions
        $allPermissions = array_merge($staffPermissions, $departmentPermissions, $budgetPermissions, $reportPermissions, $systemPermissions);
        foreach($allPermissions as $permission) {
            // Only create permission if it doesn't already exist
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        // Create roles and assign permissions
        
        // Super Admin role - has all permissions
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if (!$superAdminRole) {
            $superAdminRole = Role::create(['name' => 'super-admin']);
        }
        $superAdminRole->syncPermissions(Permission::all());
        
        // Admin role - has most permissions except some critical ones
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'admin']);
        }
        $adminRole->syncPermissions([
            // All staff permissions
            'staff.view', 'staff.create', 'staff.edit',
            
            // All department permissions
            'department.view', 'department.create', 'department.edit', 'department.data-entry',
            
            // Limited budget permissions
            'budget.view', 'budget.create', 'budget.edit',
            
            // All report permissions
            'report.trial-balance', 'report.journal', 'report.general-ledger', 'report.economic-codes',
            
            // Activity logs permission
            'activity.logs.view'
        ]);

        // Finance role - focused on budgets and reports
        $financeRole = Role::where('name', 'finance')->first();
        if (!$financeRole) {
            $financeRole = Role::create(['name' => 'finance']);
        }
        $financeRole->syncPermissions([
            // Read-only for staff
            'staff.view',
            
            // View departments
            'department.view',
            
            // All budget permissions
            'budget.view', 'budget.create', 'budget.edit', 'budget.upload',
            
            // All report permissions
            'report.trial-balance', 'report.journal', 'report.general-ledger', 'report.economic-codes'
        ]);

        // Department Head role - can manage their department
        $departmentHeadRole = Role::where('name', 'department-head')->first();
        if (!$departmentHeadRole) {
            $departmentHeadRole = Role::create(['name' => 'department-head']);
        }
        $departmentHeadRole->syncPermissions([
            // View staff
            'staff.view',
            
            // Manage department
            'department.view', 'department.data-entry',
            
            // View budgets
            'budget.view',
            
            // View reports
            'report.trial-balance', 'report.journal', 'report.general-ledger', 'report.economic-codes'
        ]);

        // Data Entry role - limited to data entry functions
        $dataEntryRole = Role::where('name', 'data-entry')->first();
        if (!$dataEntryRole) {
            $dataEntryRole = Role::create(['name' => 'data-entry']);
        }
        $dataEntryRole->syncPermissions([
            'department.view', 
            'department.data-entry',
            'budget.view',
            'report.trial-balance'
        ]);

        // Assign super-admin role to user with ID 1 if exists
        $user = DB::table('users')->where('id', 1)->first();
        if ($user) {
            $user = \App\Models\User::find(1);
            $user->assignRole('super-admin');
        }
    }
}
