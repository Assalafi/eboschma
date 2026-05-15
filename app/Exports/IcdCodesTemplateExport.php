<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IcdCodesTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            [
                'A00.0',
                'Cholera due to Vibrio cholerae 01, biovar cholerae',
                'Certain infectious and parasitic diseases'
            ],
            [
                'A00.1',
                'Cholera due to Vibrio cholerae 01, biovar eltor',
                'Certain infectious and parasitic diseases'
            ],
            [
                'A01.0',
                'Typhoid fever',
                'Certain infectious and parasitic diseases'
            ],
            [
                'I10',
                'Essential (primary) hypertension',
                'Diseases of the circulatory system'
            ],
            [
                'E11.9',
                'Type 2 diabetes mellitus without complications',
                'Endocrine, nutritional and metabolic diseases'
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'code',
            'description',
            'category'
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
            'A' => ['width' => 15],
            'B' => ['width' => 60],
            'C' => ['width' => 20],
        ];
    }
}
