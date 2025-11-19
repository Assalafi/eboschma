<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class CivilServantsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Return array of sample data with headers.
     * Shows various scenarios and DP number formats - only dp_no is required.
     */
    public function array(): array
    {
        return [
            // Example 1: Alphanumeric DP number with full data
            [
                'DP001',
                '12345678901', 
                '22123456789',
                'John Doe',
                '1985-06-15',
                'Lagos',
                'Lagos Island', 
                'Male',
                'Ministry of Finance'
            ],
            // Example 2: Numeric DP number with partial data
            [
                '12345',
                '', // Optional NIN
                '', // Optional BVN  
                'Jane Smith',
                '1990-03-22',
                'Abuja',
                'Municipal',
                'Female', 
                'Ministry of Health'
            ],
            // Example 3: Leading zeros preserved with apostrophe prefix
            [
                "'00001",  // Apostrophe preserves leading zeros
                '98765432109',
                '',
                'Ahmed Ibrahim', 
                '1988-11-12',
                'Kano',
                'Kano Municipal',
                'Male',
                'Ministry of Education'
            ],
            // Example 4: Minimal - only dp_no required
            [
                'CS999',
                '',  // All other fields are optional
                '',
                '', 
                '',
                '',
                '',
                '',
                ''
            ]
        ];
    }

    /**
     * Define column headings.
     * Note: Only dp_no is required, all other fields are optional.
     */
    public function headings(): array
    {
        return [
            'dp_no',  // Required field - do not change column name
            'nin', 
            'bvn',
            'fullname',
            'dob',
            'state',
            'lga',
            'gender',
            'mda'
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0066CC']
                ]
            ],
            
            // Add borders to all cells with data (1 header + 4 examples = 5 rows)
            'A1:I5' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]
        ];
    }
}
