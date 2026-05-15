<?php

namespace App\Exports;

use App\Models\BeneficiaryCategory;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class BeneficiariesTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        $categories = BeneficiaryCategory::orderBy('name')->pluck('name')->toArray();
        $firstCat = $categories[0] ?? 'GL/STEP';

        // Return sample data rows to help users understand the format
        return [
            [
                'BOSCHMA001', // boschma_number
                'John Doe', // name
                '1990-01-15', // dob
                'Male', // gender
                '08012345678', // phone
                '12345678901', // nin
                'Married', // marital_status
                'Kanuri', // tribe
                'Islam', // religion
                $firstCat, // category
            ],
            [
                'BOSCHMA002',
                'Jane Smith',
                '1985-06-20',
                'Female',
                '08098765432',
                '98765432101',
                'Single',
                'Hausa',
                'Christianity',
                $categories[1] ?? 'Others',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'boschma_number',
            'name',
            'dob',
            'gender',
            'phone',
            'nin',
            'marital_status',
            'tribe',
            'religion',
            'category',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '4F84AB']
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get category names for dropdown
                $categories = BeneficiaryCategory::orderBy('name')->pluck('name')->toArray();

                if (!empty($categories)) {
                    $categoryList = '"' . implode(',', $categories) . '"';

                    // Apply dropdown validation to category column (J) for rows 2-1000
                    for ($row = 2; $row <= 1000; $row++) {
                        $validation = $sheet->getCell("J{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Invalid Category');
                        $validation->setError('Please select a category from the list.');
                        $validation->setPromptTitle('Category');
                        $validation->setPrompt('Select a beneficiary category.');
                        $validation->setFormula1($categoryList);
                    }

                    // Also add gender dropdown to column D
                    $genderList = '"Male,Female"';
                    for ($row = 2; $row <= 1000; $row++) {
                        $validation = $sheet->getCell("D{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($genderList);
                    }
                }
            },
        ];
    }
}
