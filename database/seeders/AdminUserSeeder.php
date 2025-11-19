<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('admin123');
        $uuid = Str::uuid()->toString();
        
        // Create admin staff entry using direct DB insertion to avoid UUID issues
        if (!DB::table('staff')->where('email', 'admin@boschma.org')->exists()) {
            // Insert admin staff
            DB::table('staff')->insert([
                'id' => $uuid,
                'email' => 'admin@boschma.org',
                'fullname' => 'BOSCHMA Admin',
                'phone' => '08012345678',
                'password' => $password,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Admin staff created successfully.');
        } else {
            $this->command->info('Admin staff already exists.');
        }
        
        // Create admin role for staff if it doesn't exist
        if (!DB::table('roles')->where('name', 'admin')->where('guard_name', 'staff')->exists()) {
            DB::table('roles')->insert([
                'name' => 'admin',
                'guard_name' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Admin role for staff created.');
        }
        
        // Get the staff admin role ID
        $staffAdminRole = DB::table('roles')->where('name', 'admin')->where('guard_name', 'staff')->first();
        $staffRecord = DB::table('staff')->where('email', 'admin@boschma.org')->first();
        
        // Assign admin role to staff
        if ($staffAdminRole && $staffRecord && 
            !DB::table('model_has_roles')
                ->where('role_id', $staffAdminRole->id)
                ->where('model_id', $staffRecord->id)
                ->where('model_type', 'App\\Models\\Staff')
                ->exists()) {
            
            try {
                DB::table('model_has_roles')->insert([
                    'role_id' => $staffAdminRole->id,
                    'model_type' => 'App\\Models\\Staff',
                    'model_id' => $staffRecord->id,
                ]);
                $this->command->info('Admin role assigned to staff.');
            } catch (\Exception $e) {
                $this->command->error('Could not assign role to staff: ' . $e->getMessage());
            }
        }
        
        $this->command->info('Demo admin user created successfully!');
        $this->command->info('Email: admin@boschma.org');
        $this->command->info('Password: admin123');
    }
}
