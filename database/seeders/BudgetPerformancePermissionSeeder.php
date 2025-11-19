<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class BudgetPerformancePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the permission for both guards
        Permission::create(['name' => 'report.budget-performance', 'guard_name' => 'web']);
        Permission::create(['name' => 'report.budget-performance', 'guard_name' => 'staff']);
        
        $this->command->info('Budget Performance Report permissions created successfully.');
    }
}
