<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffPositionsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            [
                'name' => 'staff-positions.view',
                'guard_name' => 'staff',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'staff-positions.create',
                'guard_name' => 'staff',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'staff-positions.edit',
                'guard_name' => 'staff',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'staff-positions.delete',
                'guard_name' => 'staff',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($permissions as $permission) {
            // Check if permission already exists
            $exists = DB::table('permissions')
                ->where('name', $permission['name'])
                ->where('guard_name', $permission['guard_name'])
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert($permission);
                echo "✓ Created permission: {$permission['name']}\n";
            } else {
                echo "- Permission already exists: {$permission['name']}\n";
            }
        }

        // Assign permissions to admin role if it exists
        $adminRole = DB::table('roles')
            ->where('name', 'Admin')
            ->where('guard_name', 'staff')
            ->first();

        if ($adminRole) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', ['staff-positions.view', 'staff-positions.create', 'staff-positions.edit', 'staff-positions.delete'])
                ->where('guard_name', 'staff')
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $adminRole->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
            echo "✓ Assigned permissions to Admin role\n";
        }

        echo "\n✅ Staff Positions permissions seeded successfully!\n";
    }
}
