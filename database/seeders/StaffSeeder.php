<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Staff::create([
            'email' => 'staff@example.com',
            'fullname' => 'Staff User',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
        ]);
    }
}
