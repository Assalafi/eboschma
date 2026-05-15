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
    protected $programId;
    protected $lga;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($programId = null, $lga = null, $dateFrom = null, $dateTo = null, $isTemplate = false)
    {
        $this->programId = $programId;
        $this->lga = $lga;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
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

        $programId = $this->programId;
        $lga = $this->lga;
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;
        
        // Build date range filter
        $dateFromSql = $dateFrom ? $dateFrom . ' 00:00:00' : null;
        $dateToSql = $dateTo ? $dateTo . ' 23:59:59' : null;

        // Start with facilities query
        $facilitiesQuery = Facility::query();
        
        // Apply LGA filter if specified
        if ($lga) {
            $facilitiesQuery->where('lga', $lga);
        }

        return $facilitiesQuery->withCount(['beneficiaries' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->where('status', '!=', 'draft');
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
                }
            }])
            ->withCount(['spouses' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                if ($programId) {
                    $query->whereHas('beneficiary', function($q) use ($programId) {
                        $q->where('program_id', $programId);
                    });
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereHas('beneficiary', function($q) use ($dateFromSql, $dateToSql) {
                        $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
                    });
                }
            }])
            ->withCount(['children' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                if ($programId) {
                    $query->whereHas('beneficiary', function($q) use ($programId) {
                        $q->where('program_id', $programId);
                    });
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereHas('beneficiary', function($q) use ($dateFromSql, $dateToSql) {
                        $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
                    });
                }
            }])
            ->orderBy('beneficiaries_count', 'desc')
            ->get();
    }

    public function headings(): array
    {
        if ($this->isTemplate) {
            return ['Name', 'LGA', 'Ward', 'Type (Optional)'];
        }
        return ['Facility Name', 'LGA', 'Ward', 'Type', 'Beneficiaries', 'Spouses', 'Children', 'Total'];
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

        $total = $facility->beneficiaries_count + $facility->spouses_count + $facility->children_count;
        return [
            $facility->name,
            $facility->lga,
            $facility->ward,
            $facility->type,
            $facility->beneficiaries_count,
            $facility->spouses_count,
            $facility->children_count,
            $total,
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
