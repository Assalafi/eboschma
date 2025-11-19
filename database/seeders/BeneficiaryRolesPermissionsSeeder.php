<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Staff;

class BeneficiaryRolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions for beneficiary enrollment system
        $permissions = [
            // Beneficiary permissions
            'beneficiary.view' => 'View beneficiaries',
            'beneficiary.create' => 'Create beneficiaries',
            'beneficiary.edit' => 'Edit beneficiaries',
            'beneficiary.delete' => 'Delete beneficiaries',
            'beneficiary.approve' => 'Approve beneficiaries',
            'beneficiary.export' => 'Export beneficiary data',
            
            // Contribution permissions
            'contribution.view' => 'View contributions',
            'contribution.create' => 'Create contributions',
            'contribution.edit' => 'Edit contributions',
            'contribution.delete' => 'Delete contributions',
            'contribution.import' => 'Import contributions',
            
            // Facility permissions
            'facility.view' => 'View facilities',
            'facility.create' => 'Create facilities',
            'facility.edit' => 'Edit facilities',
            'facility.delete' => 'Delete facilities',
            
            // Program permissions
            'program.view' => 'View programs',
            'program.create' => 'Create programs',
            'program.edit' => 'Edit programs',
            'program.delete' => 'Delete programs',
            
            // Staff permissions
            'staff.view' => 'View staff',
            'staff.create' => 'Create staff',
            'staff.edit' => 'Edit staff',
            'staff.delete' => 'Delete staff',
            
            // Role & Permission management
            'role.view' => 'View roles',
            'role.create' => 'Create roles',
            'role.edit' => 'Edit roles',
            'role.delete' => 'Delete roles',
            'permission.manage' => 'Manage permissions',
            
            // System settings
            'settings.view' => 'View system settings',
            'settings.edit' => 'Edit system settings',
            
            // Reports
            'report.view' => 'View reports',
            'report.export' => 'Export reports',
            
            // Activity logs
            'logs.view' => 'View activity logs',
        ];

        // Create permissions with staff guard
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'staff']
            );
        }

        // ============================================
        // SUPER ADMIN ROLE
        // ============================================
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['guard_name' => 'staff']
        );
        // Super Admin has all permissions for staff guard
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'staff')->get());

        // ============================================
        // ENUMERATOR ROLE
        // ============================================
        $enumeratorRole = Role::firstOrCreate(
            ['name' => 'Enumerator'],
            ['guard_name' => 'staff']
        );
        // Enumerators can only create and view beneficiaries
        $enumeratorRole->syncPermissions([
            'beneficiary.view',
            'beneficiary.create',
            'beneficiary.edit',
            'facility.view',
            'program.view',
        ]);

        // ============================================
        // ADMINISTRATOR ROLE
        // ============================================
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'staff']
        );
        // Administrators can manage most things except roles/permissions
        $adminRole->syncPermissions([
            'beneficiary.view',
            'beneficiary.create',
            'beneficiary.edit',
            'beneficiary.delete',
            'beneficiary.approve',
            'beneficiary.export',
            'contribution.view',
            'contribution.create',
            'contribution.edit',
            'contribution.delete',
            'contribution.import',
            'facility.view',
            'facility.create',
            'facility.edit',
            'facility.delete',
            'program.view',
            'program.create',
            'program.edit',
            'program.delete',
            'staff.view',
            'report.view',
            'report.export',
            'logs.view',
        ]);

        // ============================================
        // DATA VIEWER ROLE
        // ============================================
        $viewerRole = Role::firstOrCreate(
            ['name' => 'Data Viewer'],
            ['guard_name' => 'staff']
        );
        // Viewers can only view data
        $viewerRole->syncPermissions([
            'beneficiary.view',
            'contribution.view',
            'facility.view',
            'program.view',
            'report.view',
        ]);

        // ============================================
        // APPROVER ROLE
        // ============================================
        $approverRole = Role::firstOrCreate(
            ['name' => 'Approver'],
            ['guard_name' => 'staff']
        );
        // Approvers can view and approve beneficiaries
        $approverRole->syncPermissions([
            'beneficiary.view',
            'beneficiary.approve',
            'facility.view',
            'program.view',
            'report.view',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        
        // Assign Super Admin role to existing admin or create new one
        $superAdmin = Staff::where('email', 'admin@boschma.com')->first();
        
        if ($superAdmin) {
            // Assign role if user exists but doesn't have it
            if (!$superAdmin->hasRole('Super Admin')) {
                $superAdmin->assignRole('Super Admin');
                $this->command->info('Super Admin role assigned to existing user: admin@boschma.com');
            } else {
                $this->command->info('Super Admin role already assigned');
            }
        } else {
            $this->command->info('Creating default Super Admin user...');
            $superAdmin = Staff::create([
                'fullname' => 'System Administrator',
                'email' => 'admin@boschma.com',
                'phone' => '08000000000',
                'password' => bcrypt('password'), // Change this!
            ]);
            $superAdmin->assignRole('Super Admin');
            $this->command->info('Super Admin created: admin@boschma.com / password');
        }
    }
}
