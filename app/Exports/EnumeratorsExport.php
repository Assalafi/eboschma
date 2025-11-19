<?php

namespace App\Exports;

use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnumeratorsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $enumeratorId;

    public function __construct($enumeratorId = null)
    {
        $this->enumeratorId = $enumeratorId;
    }

    public function collection()
    {
        if ($this->enumeratorId) {
            // Export specific enumerator
            $enumerators = Staff::role('Enumerator')
                ->where('id', $this->enumeratorId)
                ->withCount(['beneficiaries' => function($query) {
                    $query->where('status', '!=', 'draft');
                }])
                ->withCount(['beneficiaries as main_facility_enrollments' => function($query) {
                    $query->where('status', '!=', 'draft')
                          ->whereNotNull('facility_id');
                }])
                ->get();
        } else {
            // Export all enumerators
            $enumerators = Staff::role('Enumerator')
                ->withCount(['beneficiaries' => function($query) {
                    $query->where('status', '!=', 'draft');
                }])
                ->withCount(['beneficiaries as main_facility_enrollments' => function($query) {
                    $query->where('status', '!=', 'draft')
                          ->whereNotNull('facility_id');
                }])
                ->orderBy('beneficiaries_count', 'desc')
                ->get();
        }

        // Calculate unique facilities including spouses and children for each enumerator
        foreach ($enumerators as $enumerator) {
            // Get all facility IDs from beneficiaries for this enumerator
            $beneficiaryFacilities = $enumerator->beneficiaries()
                ->where('status', '!=', 'draft')
                ->whereNotNull('facility_id')
                ->pluck('facility_id');
            
            // Get spouses for beneficiaries created by this enumerator
            $spouseFacilities = DB::table('spouses')
                ->join('beneficiaries', 'spouses.beneficiary_id', '=', 'beneficiaries.id')
                ->where('beneficiaries.created_by', $enumerator->id)
                ->whereNotNull('spouses.facility_id')
                ->pluck('spouses.facility_id');
            
            // Get children for beneficiaries created by this enumerator
            $childrenFacilities = DB::table('children')
                ->join('beneficiaries', 'children.beneficiary_id', '=', 'beneficiaries.id')
                ->where('beneficiaries.created_by', $enumerator->id)
                ->whereNotNull('children.facility_id')
                ->pluck('children.facility_id');
            
            // Combine all facility IDs and get unique count
            $allFacilities = $beneficiaryFacilities
                ->merge($spouseFacilities)
                ->merge($childrenFacilities)
                ->unique()
                ->filter();
            
            $enumerator->unique_facilities_count = $allFacilities->count();
        }

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
                'Phone' => $enumerator->phone ?? 'N/A',
                'Total Enrollments' => $enumerator->beneficiaries_count ?? 0,
                'Main Facility Enrollments' => $enumerator->main_facility_enrollments ?? 0,
                'Unique Facilities' => $enumerator->unique_facilities_count ?? 0,
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
            'Phone',
            'Total Enrollments',
            'Main Facility Enrollments',
            'Unique Facilities',
            'Performance Rating',
            'Created At'
        ];
    }

    public function title(): string
    {
        return 'Enumerator Performance Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:H1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
