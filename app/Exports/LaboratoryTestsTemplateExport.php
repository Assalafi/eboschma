<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaboratoryTestsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            [
                'Complete Blood Count (CBC)',
                'Complete blood count test including RBC, WBC, platelets',
                'Blood',
                '5000.00'
            ],
            [
                'Urine Analysis',
                'Basic urine analysis including pH, protein, glucose',
                'Urine',
                '2500.00'
            ],
            [
                'Malaria Test',
                'Rapid diagnostic test for malaria parasites',
                'Blood',
                '1500.00'
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'name',
            'description',
            'sample_type',
            'price'
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E3F2FD']
                ]
            ],
            'A' => ['width' => 30],
            'B' => ['width' => 50],
            'C' => ['width' => 20],
            'D' => ['width' => 15],
        ];
    }
}
