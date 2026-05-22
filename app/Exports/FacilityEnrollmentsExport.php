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
    protected $programId;
    protected $dateFrom;
    protected $dateTo;
    protected $gender;

    public function __construct($facilityId, $facilityName, $programId = null, $dateFrom = null, $dateTo = null, $gender = null)
    {
        $this->facilityId = $facilityId;
        $this->facilityName = $facilityName;
        $this->programId = $programId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->gender = $gender;
    }

    public function collection()
    {
        $enrollments = collect();

        $dateFromSql = $this->dateFrom ? $this->dateFrom . ' 00:00:00' : null;
        $dateToSql = $this->dateTo ? $this->dateTo . ' 23:59:59' : null;

        // Get beneficiaries for this facility
        $beneficiaryQuery = Beneficiary::where('facility_id', $this->facilityId)
            ->where('status', '!=', 'draft')
            ->with('creator');
        
        if ($this->programId) {
            $beneficiaryQuery->where('program_id', $this->programId);
        }
        if ($this->gender) {
            $beneficiaryQuery->where('gender', $this->gender);
        }
        if ($dateFromSql && $dateToSql) {
            $beneficiaryQuery->whereBetween('created_at', [$dateFromSql, $dateToSql]);
        }
        
        $beneficiaries = $beneficiaryQuery->get();

        foreach ($beneficiaries as $beneficiary) {
            $enrollments->push([
                'Type' => 'Beneficiary',
                'BOSCHMA ID' => $beneficiary->boschma_no ?? 'N/A',
                'Full Name' => $beneficiary->fullname ?? 'N/A',
                'Gender' => $beneficiary->gender ?? 'N/A',
                'Date of Birth' => $beneficiary->date_of_birth ?? 'N/A',
                'NIN' => $beneficiary->nin ?? 'N/A',
                'Category' => $beneficiary->category ?? 'N/A',
                'Occupation' => $beneficiary->occupation ?? 'N/A',
                'Marital Status' => $beneficiary->marital_status ?? 'N/A',
                'Facility' => $beneficiary->facility->name ?? 'N/A',
                'Phone' => $beneficiary->phone_no ?? 'N/A',
                'Email' => $beneficiary->email ?? 'N/A',
                'Address' => $beneficiary->contact_address ?? 'N/A',
                'Status' => ucfirst($beneficiary->status ?? 'N/A'),
                'Enrolled By' => $beneficiary->creator->fullname ?? 'N/A',
                'Created At' => $beneficiary->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Get spouses for beneficiaries in this facility
        $spouseQuery = Spouse::where('facility_id', $this->facilityId)
            ->with('beneficiary.creator');
        
        if ($this->gender) {
            $spouseQuery->where('gender', $this->gender);
        }

        if ($this->programId || ($dateFromSql && $dateToSql)) {
            $spouseQuery->whereHas('beneficiary', function($q) use ($dateFromSql, $dateToSql) {
                if ($this->programId) {
                    $q->where('program_id', $this->programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
                }
            });
        }
        
        $spouses = $spouseQuery->get();

        foreach ($spouses as $spouse) {
            $enrollments->push([
                'Type' => 'Spouse',
                'BOSCHMA ID' => $spouse->boschma_no ?? 'N/A',
                'Full Name' => $spouse->name ?? 'N/A',
                'Gender' => $spouse->gender ?? 'N/A',
                'Date of Birth' => $spouse->dob ?? 'N/A',
                'NIN' => $spouse->nin ?? 'N/A',
                'Category' => 'N/A',
                'Occupation' => 'N/A',
                'Marital Status' => 'Married',
                'Facility' => $spouse->beneficiary->facility->name ?? 'N/A',
                'Phone' => $spouse->phone_no ?? 'N/A',
                'Email' => $spouse->email ?? 'N/A',
                'Address' => 'N/A',
                'Status' => ucfirst($spouse->beneficiary->status ?? 'N/A'),
                'Enrolled By' => $spouse->beneficiary->creator->fullname ?? 'N/A',
                'Created At' => $spouse->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Get children for beneficiaries in this facility
        $childQuery = Child::where('facility_id', $this->facilityId)
            ->with('beneficiary.creator');
        
        if ($this->gender) {
            $childQuery->where('gender', $this->gender);
        }

        if ($this->programId || ($dateFromSql && $dateToSql)) {
            $childQuery->whereHas('beneficiary', function($q) use ($dateFromSql, $dateToSql) {
                if ($this->programId) {
                    $q->where('program_id', $this->programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
                }
            });
        }
        
        $children = $childQuery->get();

        foreach ($children as $child) {
            $enrollments->push([
                'Type' => 'Child',
                'BOSCHMA ID' => $child->boschma_no ?? 'N/A',
                'Full Name' => $child->name ?? 'N/A',
                'Gender' => $child->gender ?? 'N/A',
                'Date of Birth' => $child->dob ?? 'N/A',
                'NIN' => $child->nin ?? 'N/A',
                'Category' => 'N/A',
                'Occupation' => 'N/A',
                'Marital Status' => 'Single',
                'Facility' => $child->beneficiary->facility->name ?? 'N/A',
                'Phone' => $child->phone_no ?? 'N/A',
                'Email' => $child->email ?? 'N/A',
                'Address' => 'N/A',
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
            'NIN',
            'Category',
            'Occupation',
            'Marital Status',
            'Facility',
            'Phone',
            'Email',
            'Address',
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
            'A1:P1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
