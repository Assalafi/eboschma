<?php

namespace Database\Seeders;

use App\Models\CivilServant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CivilServantSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create sample civil servants data
        $civilServants = [
            [
                'dp_no' => 'CS001',
                'nin' => '12345678901',
                'bvn' => '22123456789',
                'fullname' => 'John Doe',
                'dob' => '1985-06-15',
                'state' => 'Lagos',
                'lga' => 'Lagos Island',
                'gender' => 'Male',
                'mda' => 'Ministry of Finance',
            ],
            [
                'dp_no' => 'CS002',
                'nin' => '98765432109',
                'bvn' => '22987654321',
                'fullname' => 'Jane Smith',
                'dob' => '1990-03-22',
                'state' => 'Abuja',
                'lga' => 'Municipal',
                'gender' => 'Female',
                'mda' => 'Ministry of Health',
            ],
            [
                'dp_no' => 'CS003',
                'nin' => '11122233344',
                'bvn' => null,
                'fullname' => 'Ahmed Ibrahim',
                'dob' => '1988-11-12',
                'state' => 'Kano',
                'lga' => 'Kano Municipal',
                'gender' => 'Male',
                'mda' => 'Ministry of Education',
            ],
            [
                'dp_no' => 'CS004',
                'nin' => null,
                'bvn' => '22555666777',
                'fullname' => 'Fatima Hassan',
                'dob' => '1992-09-05',
                'state' => 'Kaduna',
                'lga' => 'Kaduna North',
                'gender' => 'Female',
                'mda' => 'Ministry of Agriculture',
            ],
            [
                'dp_no' => 'CS005',
                'nin' => '55544433322',
                'bvn' => '22444555666',
                'fullname' => 'Chidi Okwu',
                'dob' => '1987-12-30',
                'state' => 'Anambra',
                'lga' => 'Onitsha',
                'gender' => 'Male',
                'mda' => 'Ministry of Commerce',
            ],
            [
                'dp_no' => 'CS006',
                'nin' => '77788899900',
                'bvn' => null,
                'fullname' => 'Blessing Adebayo',
                'dob' => '1991-04-18',
                'state' => 'Oyo',
                'lga' => 'Ibadan North',
                'gender' => 'Female',
                'mda' => 'Ministry of Works',
            ],
            [
                'dp_no' => 'CS007',
                'nin' => '33344455566',
                'bvn' => '22111222333',
                'fullname' => 'Emmanuel Uche',
                'dob' => '1989-07-25',
                'state' => 'Rivers',
                'lga' => 'Port Harcourt',
                'gender' => 'Male',
                'mda' => 'Ministry of Transportation',
            ],
            [
                'dp_no' => 'CS008',
                'nin' => null,
                'bvn' => null,
                'fullname' => 'Aisha Musa',
                'dob' => '1993-01-14',
                'state' => 'Borno',
                'lga' => 'Maiduguri',
                'gender' => 'Female',
                'mda' => 'Ministry of Women Affairs',
            ],
            [
                'dp_no' => 'CS009',
                'nin' => '99988877766',
                'bvn' => '22777888999',
                'fullname' => 'Tunde Afolabi',
                'dob' => '1986-10-08',
                'state' => 'Osun',
                'lga' => 'Osogbo',
                'gender' => 'Male',
                'mda' => 'Ministry of Information',
            ],
            [
                'dp_no' => 'CS010',
                'nin' => '66677788899',
                'bvn' => '22333444555',
                'fullname' => 'Grace Nnenna',
                'dob' => '1994-02-28',
                'state' => 'Enugu',
                'lga' => 'Enugu East',
                'gender' => 'Female',
                'mda' => 'Ministry of Environment',
            ],
        ];

        foreach ($civilServants as $data) {
            CivilServant::create($data);
        }

        $this->command->info('Civil servants seeded successfully!');
    }
}
