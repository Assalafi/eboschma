<?php

namespace App\Exports;

use App\Models\Beneficiary;
use App\Models\Facility;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardExport implements WithMultipleSheets
{
    protected $stats;

    public function __construct($stats)
    {
        $this->stats = $stats;
    }

    public function sheets(): array
    {
        return [
            new DashboardSummarySheet($this->stats),
            new FacilityPerformanceSheet(),
            new EnumeratorPerformanceSheet(),
        ];
    }
}

class DashboardSummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $stats;

    public function __construct($stats)
    {
        $this->stats = $stats;
    }

    public function collection()
    {
        return new Collection([
            ['Total Beneficiaries', $this->stats['total_beneficiaries']],
            ['Active Beneficiaries', $this->stats['active_beneficiaries']],
            ['Pending Beneficiaries', $this->stats['pending_beneficiaries']],
            ['Inactive Beneficiaries', $this->stats['inactive_beneficiaries']],
            ['Total Facilities', $this->stats['total_facilities']],
            ['Total Enumerators', $this->stats['total_enumerators']],
            ['', ''],
            ['Report Generated', now()->format('Y-m-d H:i:s')],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Dashboard Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:B1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}

class FacilityPerformanceSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $facilities = Facility::withCount(['beneficiaries' => function($query) {
                $query->where('status', '!=', 'draft');
            }])
            ->withCount(['spouses' => function($query) {
                $query->whereColumn('facility_id', 'facilities.id');
            }])
            ->withCount(['children' => function($query) {
                $query->whereColumn('facility_id', 'facilities.id');
            }])
            ->get();

        // Calculate total enrollments for each facility
        foreach ($facilities as $facility) {
            $facility->total_enrollments = $facility->beneficiaries_count + $facility->spouses_count + $facility->children_count;
        }

        return new Collection($facilities->map(function($facility) {
            $performance = 'Inactive';
            if ($facility->total_enrollments > 100) {
                $performance = 'Excellent';
            } elseif ($facility->total_enrollments > 50) {
                $performance = 'Good';
            } elseif ($facility->total_enrollments > 0) {
                $performance = 'Average';
            }

            return [
                'Facility Name' => $facility->name ?? 'N/A',
                'LGA' => $facility->lga ?? 'N/A',
                'Total Enrollments' => $facility->total_enrollments ?? 0,
                'Beneficiaries' => $facility->beneficiaries_count ?? 0,
                'Spouses' => $facility->spouses_count ?? 0,
                'Children' => $facility->children_count ?? 0,
                'Performance' => $performance
            ];
        }));
    }

    public function headings(): array
    {
        return [
            'Facility Name',
            'LGA',
            'Total Enrollments',
            'Beneficiaries',
            'Spouses',
            'Children',
            'Performance'
        ];
    }

    public function title(): string
    {
        return 'Facility Performance';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:G1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}

class EnumeratorPerformanceSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $enumerators = Staff::role('Enumerator')
            ->withCount(['beneficiaries' => function($query) {
                $query->where('status', '!=', 'draft');
            }])
            ->orderBy('beneficiaries_count', 'desc')
            ->get();

        return new Collection($enumerators->map(function($enumerator) {
            $rating = 'Inactive';
            if ($enumerator->beneficiaries_count > 50) {
                $rating = 'Excellent';
            } elseif ($enumerator->beneficiaries_count > 20) {
                $rating = 'Good';
            } elseif ($enumerator->beneficiaries_count > 0) {
                $rating = 'Average';
            }

            return [
                'Enumerator Name' => $enumerator->fullname ?? 'N/A',
                'Email' => $enumerator->email ?? 'N/A',
                'Total Enrollments' => $enumerator->beneficiaries_count ?? 0,
                'Performance Rating' => $rating,
                'Created At' => $enumerator->created_at->format('Y-m-d H:i:s')
            ];
        }));
    }

    public function headings(): array
    {
        return [
            'Enumerator Name',
            'Email',
            'Total Enrollments',
            'Performance Rating',
            'Created At'
        ];
    }

    public function title(): string
    {
        return 'Enumerator Performance';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:E1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
