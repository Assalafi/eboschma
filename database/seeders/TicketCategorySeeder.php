<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TicketCategory;

class TicketCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Enrollment Issues',
                'description' => 'Problems with beneficiary enrollment, registration, or data entry',
                'color' => '#dc3545',
                'is_active' => true
            ],
            [
                'name' => 'ID Card Problems',
                'description' => 'Issues related to ID card generation, printing, or quality',
                'color' => '#fd7e14',
                'is_active' => true
            ],
            [
                'name' => 'Contribution Payment',
                'description' => 'Questions or issues about contribution payments and deductions',
                'color' => '#ffc107',
                'is_active' => true
            ],
            [
                'name' => 'Healthcare Services',
                'description' => 'Complaints or inquiries about healthcare services and facilities',
                'color' => '#28a745',
                'is_active' => true
            ],
            [
                'name' => 'Account Information',
                'description' => 'Requests to update personal information, contact details, etc.',
                'color' => '#17a2b8',
                'is_active' => true
            ],
            [
                'name' => 'Technical Support',
                'description' => 'Technical issues with the system, portal access, or mobile app',
                'color' => '#6f42c1',
                'is_active' => true
            ],
            [
                'name' => 'Policy Questions',
                'description' => 'Inquiries about healthcare policies, coverage, and regulations',
                'color' => '#6c757d',
                'is_active' => true
            ],
            [
                'name' => 'Feedback & Suggestions',
                'description' => 'General feedback, suggestions for improvement, or compliments',
                'color' => '#20c997',
                'is_active' => true
            ],
            [
                'name' => 'Complaints',
                'description' => 'Formal complaints about service quality or staff behavior',
                'color' => '#e83e8c',
                'is_active' => true
            ],
            [
                'name' => 'Other',
                'description' => 'Miscellaneous inquiries that don\'t fit other categories',
                'color' => '#343a40',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            TicketCategory::create($category);
        }
    }
}
