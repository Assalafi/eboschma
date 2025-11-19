<?php

namespace App\Exports;

use App\Models\Facility;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FacilitiesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if ($this->isTemplate) {
            // Return sample data for template
            return collect([
                [
                    'name' => 'Sample Primary Health Care Center',
                    'lga' => 'Maiduguri',
                    'ward' => 'Bulabulin',
                    'type' => 'Primary Health Care Center',
                ],
                [
                    'name' => 'Sample General Hospital',
                    'lga' => 'Bama',
                    'ward' => 'Central Ward',
                    'type' => 'General Hospital',
                ]
            ]);
        }

        return Facility::all();
    }

    public function headings(): array
    {
        return [
            'Name',
            'LGA',
            'Ward',
            'Type (Optional)',
        ];
    }

    public function map($facility): array
    {
        if ($this->isTemplate) {
            return [
                $facility['name'],
                $facility['lga'],
                $facility['ward'],
                $facility['type'],
            ];
        }

        return [
            $facility->name,
            $facility->lga,
            $facility->ward,
            $facility->type,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:D1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
