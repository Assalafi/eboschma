<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ReferralPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Granular referral permissions — one per action.
        $permissions = [
            'referral.view'            => 'View referrals',
            'referral.create'          => 'Create new referrals',
            'referral.edit'            => 'Edit / update referrals',
            'referral.delete'          => 'Delete referrals',
            'referral.approve'         => 'Approve referrals (single & bulk)',
            'referral.reject'          => 'Reject referrals (single & bulk)',
            'referral.complete'        => 'Mark referrals as completed',
            'referral.pay-commission'  => 'Pay referral commissions',
            'referral.send-reminder'   => 'Send referral reminders',
            'referral.assign'          => 'Assign / unassign referrals to staff',
            'referral.note'            => 'Add notes to referrals',
            'referral.export'          => 'Export referrals',
            'referral.analytics'       => 'View referral analytics',
            'referral.settings'        => 'Manage referral system settings',
        ];

        // Create permissions for both 'web' and 'staff' guards
        foreach (['web', 'staff'] as $guard) {
            foreach (array_keys($permissions) as $name) {
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        $all = array_keys($permissions);

        // Grant everything to existing administrator-style roles (both guards)
        $adminNames = [
            'super-admin', 'Super Admin', 'Super Administrator',
            'Boschma Administrator', 'System Administrator',
            'Administrator', 'Admin', 'admin',
        ];
        foreach (['web', 'staff'] as $guard) {
            Role::whereIn('name', $adminNames)->where('guard_name', $guard)->get()
                ->each(fn($role) => $role->givePermissionTo($all));
        }

        // Sensible defaults for legacy roles, if they exist
        Role::where('name', 'manager')->get()->each(function ($role) use ($all) {
            $role->givePermissionTo($all);
        });

        Role::where('name', 'staff')->get()->each(function ($role) {
            $role->givePermissionTo([
                'referral.view', 'referral.create', 'referral.edit',
                'referral.approve', 'referral.reject', 'referral.note',
                'referral.export', 'referral.analytics',
            ]);
        });

        Role::where('name', 'viewer')->get()->each(function ($role) {
            $role->givePermissionTo(['referral.view', 'referral.analytics']);
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✅ Referral permissions created and assigned to roles successfully!');
    }
}
