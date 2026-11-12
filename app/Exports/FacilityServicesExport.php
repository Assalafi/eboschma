<?php

namespace App\Exports;

use App\Models\FacilityService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FacilityServicesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $facilityId;

    public function __construct($facilityId)
    {
        $this->facilityId = $facilityId;
    }

    public function collection()
    {
        return FacilityService::where('facility_id', $this->facilityId)
            ->with('serviceItem.serviceType.serviceCategory')
            ->orderBy('is_available', 'desc')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Service Name',
            'Category',
            'Type',
            'Price',
            'Availability',
            'Configured At',
            'Last Updated',
        ];
    }

    public function map($facilityService): array
    {
        $serviceItem = $facilityService->serviceItem;
        $serviceType = $serviceItem?->serviceType;
        $category = $serviceType?->serviceCategory;

        return [
            $serviceItem?->name ?? 'Service item unavailable',
            $category?->name ?? '',
            $serviceType?->name ?? '',
            $serviceItem?->price !== null ? number_format((float) $serviceItem->price, 2) : '',
            $facilityService->is_available ? 'Available' : 'Unavailable',
            optional($facilityService->created_at)->format('Y-m-d H:i:s'),
            optional($facilityService->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '4F84AB'],
                ],
            ],
        ];
    }
}
