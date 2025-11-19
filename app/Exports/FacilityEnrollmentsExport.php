<?php

namespace App\Exports;

use App\Models\Facility;
use App\Models\Beneficiary;
use App\Models\Spouse;
use App\Models\Child;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FacilityEnrollmentsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $facilityId;
    protected $facilityName;

    public function __construct($facilityId, $facilityName)
    {
        $this->facilityId = $facilityId;
        $this->facilityName = $facilityName;
    }

    public function collection()
    {
        $enrollments = collect();

        // Get beneficiaries for this facility
        $beneficiaries = Beneficiary::where('facility_id', $this->facilityId)
            ->where('status', '!=', 'draft')
            ->with('creator')
            ->get();

        foreach ($beneficiaries as $beneficiary) {
            $enrollments->push([
                'Type' => 'Beneficiary',
                'BOSCHMA ID' => $beneficiary->boschma_no ?? 'N/A',
                'Full Name' => $beneficiary->fullname ?? 'N/A',
                'Gender' => $beneficiary->gender ?? 'N/A',
                'Date of Birth' => $beneficiary->date_of_birth ?? 'N/A',
                'Phone' => $beneficiary->phone_no ?? 'N/A',
                'Email' => $beneficiary->email ?? 'N/A',
                'Status' => ucfirst($beneficiary->status ?? 'N/A'),
                'Enrolled By' => $beneficiary->creator->fullname ?? 'N/A',
                'Created At' => $beneficiary->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Get spouses for beneficiaries in this facility
        $spouses = Spouse::where('facility_id', $this->facilityId)
            ->with('beneficiary.creator')
            ->get();

        foreach ($spouses as $spouse) {
            $enrollments->push([
                'Type' => 'Spouse',
                'BOSCHMA ID' => $spouse->boschma_no ?? 'N/A',
                'Full Name' => $spouse->fullname ?? 'N/A',
                'Gender' => $spouse->gender ?? 'N/A',
                'Date of Birth' => $spouse->date_of_birth ?? 'N/A',
                'Phone' => $spouse->phone_no ?? 'N/A',
                'Email' => $spouse->email ?? 'N/A',
                'Status' => ucfirst($spouse->beneficiary->status ?? 'N/A'),
                'Enrolled By' => $spouse->beneficiary->creator->fullname ?? 'N/A',
                'Created At' => $spouse->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Get children for beneficiaries in this facility
        $children = Child::where('facility_id', $this->facilityId)
            ->with('beneficiary.creator')
            ->get();

        foreach ($children as $child) {
            $enrollments->push([
                'Type' => 'Child',
                'BOSCHMA ID' => $child->boschma_no ?? 'N/A',
                'Full Name' => $child->fullname ?? 'N/A',
                'Gender' => $child->gender ?? 'N/A',
                'Date of Birth' => $child->date_of_birth ?? 'N/A',
                'Phone' => $child->phone_no ?? 'N/A',
                'Email' => $child->email ?? 'N/A',
                'Status' => ucfirst($child->beneficiary->status ?? 'N/A'),
                'Enrolled By' => $child->beneficiary->creator->fullname ?? 'N/A',
                'Created At' => $child->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Sort by created_at date
        return $enrollments->sortByDesc('Created At')->values();
    }

    public function headings(): array
    {
        return [
            'Type',
            'BOSCHMA ID',
            'Full Name',
            'Gender',
            'Date of Birth',
            'Phone',
            'Email',
            'Status',
            'Enrolled By',
            'Created At'
        ];
    }

    public function title(): string
    {
        return $this->facilityName . ' - Enrollments';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:J1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
