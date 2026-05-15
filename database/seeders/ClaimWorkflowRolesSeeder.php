<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClaimWorkflowRolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Workflow-specific permissions
        $permissions = [
            'claim.verify'          => 'Verify claims (first approval stage)',
            'claim.approve'         => 'Approve claims (RO/approver stage)',
            'claim.es-approve'      => 'Executive Secretary approval',
            'claim.finance-approve' => 'Finance approval / mark as paid',
            'claim.edit-items'      => 'Edit medication qty/price and service costs',
            'claim.batch-approve'   => 'Batch approve multiple claims at once',
        ];

        foreach ($permissions as $name => $desc) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'staff']);
        }

        // ── Roles ──────────────────────────────────────────────
        // 1. Claim Verifier
        $verifier = Role::firstOrCreate(['name' => 'claim-verifier', 'guard_name' => 'staff']);
        $verifier->syncPermissions([
            'claim.view', 'claim.verify', 'claim.edit-items',
        ]);

        // 2. Claim Approver (RO)
        $approver = Role::firstOrCreate(['name' => 'claim-approver', 'guard_name' => 'staff']);
        $approver->syncPermissions([
            'claim.view', 'claim.approve',
        ]);

        // 3. Executive Secretary
        $es = Role::firstOrCreate(['name' => 'claim-es', 'guard_name' => 'staff']);
        $es->syncPermissions([
            'claim.view', 'claim.es-approve', 'claim.batch-approve',
        ]);

        // 4. Finance
        $finance = Role::firstOrCreate(['name' => 'claim-finance', 'guard_name' => 'staff']);
        $finance->syncPermissions([
            'claim.view', 'claim.finance-approve', 'claim.batch-approve',
        ]);

        // Super-admin gets every permission automatically via Spatie gate
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        // Admin also gets all claim permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                'claim.verify', 'claim.approve', 'claim.es-approve',
                'claim.finance-approve', 'claim.edit-items', 'claim.batch-approve',
            ]);
        }
    }
}
