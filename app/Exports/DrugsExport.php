<?php

namespace App\Exports;

use App\Models\Drug;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrugsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $facilityId;

    /**
     * Constructor
     */
    public function __construct($facilityId)
    {
        $this->facilityId = $facilityId;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Drugs Inventory';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Drug::where('facility_id', $this->facilityId)
            ->select('id', 'name', 'description', 'dosage_form', 'strength', 'unit', 'quantity', 'unit_price', 'created_at', 'updated_at')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param Drug $drug
     * @return array
     */
    public function map($drug): array
    {
        return [
            $drug->id,
            $drug->name,
            $drug->description ?? '',
            $drug->dosage_form,
            $drug->strength,
            $drug->unit,
            $drug->quantity,
            '₦' . number_format($drug->unit_price, 2),
            '₦' . number_format($drug->quantity * $drug->unit_price, 2),
            $drug->created_at->format('Y-m-d H:i:s'),
            $drug->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Dosage Form',
            'Strength',
            'Unit',
            'Quantity',
            'Unit Price',
            'Total Value',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E0E0E0'
                    ]
                ],
            ],
            // Auto-size columns
            'A' => ['autosize' => true],
            'B' => ['autosize' => true],
            'C' => ['autosize' => true],
            'D' => ['autosize' => true],
            'E' => ['autosize' => true],
            'F' => ['autosize' => true],
            'G' => ['autosize' => true],
            'H' => ['autosize' => true],
            'I' => ['autosize' => true],
            'J' => ['autosize' => true],
            'K' => ['autosize' => true],
        ];
    }
}
