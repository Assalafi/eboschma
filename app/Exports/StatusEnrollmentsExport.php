<?php

namespace App\Exports;

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

class StatusEnrollmentsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function collection()
    {
        $enrollments = collect();

        // Get beneficiaries with this status
        $beneficiaries = Beneficiary::where('status', $this->status)
            ->with(['facility', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($beneficiaries as $beneficiary) {
            $enrollments->push([
                'BOSCHMA ID' => $beneficiary->boschma_no ?? 'N/A',
                'Name' => $beneficiary->fullname ?? 'N/A',
                'Category' => 'Principal',
                'Gender' => $beneficiary->gender ?? 'N/A',
                'Date of Birth' => $beneficiary->date_of_birth ?? 'N/A',
                'Phone' => $beneficiary->phone_no ?? 'N/A',
                'Email' => $beneficiary->email ?? 'N/A',
                'Facility' => $beneficiary->facility->name ?? 'N/A',
                'Status' => ucfirst($beneficiary->status ?? 'N/A'),
                'Enrolled By' => $beneficiary->creator->fullname ?? 'N/A',
                'Enrollment Date' => $beneficiary->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Get spouses where beneficiary has this status
        $spouses = Spouse::whereHas('beneficiary', function($query) {
                $query->where('status', $this->status);
            })
            ->with(['beneficiary.facility', 'beneficiary.creator', 'facility'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($spouses as $spouse) {
            $enrollments->push([
                'BOSCHMA ID' => $spouse->boschma_no ?? 'N/A',
                'Name' => $spouse->name ?? 'N/A',
                'Category' => 'Spouse',
                'Gender' => $spouse->gender ?? 'N/A',
                'Date of Birth' => $spouse->date_of_birth ?? 'N/A',
                'Phone' => $spouse->phone ?? 'N/A',
                'Email' => $spouse->email ?? 'N/A',
                'Facility' => $spouse->facility->name ?? ($spouse->beneficiary->facility->name ?? 'N/A'),
                'Status' => ucfirst($spouse->beneficiary->status ?? 'active'),
                'Enrolled By' => $spouse->beneficiary->creator->fullname ?? 'N/A',
                'Enrollment Date' => $spouse->created_at->format('Y-m-d H:i:s')
            ]);
        }

        // Get children where beneficiary has this status
        $children = Child::whereHas('beneficiary', function($query) {
                $query->where('status', $this->status);
            })
            ->with(['beneficiary.facility', 'beneficiary.creator', 'facility'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($children as $child) {
            $enrollments->push([
                'BOSCHMA ID' => $child->boschma_no ?? 'N/A',
                'Name' => $child->name ?? 'N/A',
                'Category' => 'Child',
                'Gender' => $child->gender ?? 'N/A',
                'Date of Birth' => $child->date_of_birth ?? 'N/A',
                'Phone' => 'N/A',
                'Email' => 'N/A',
                'Facility' => $child->facility->name ?? ($child->beneficiary->facility->name ?? 'N/A'),
                'Status' => ucfirst($child->beneficiary->status ?? 'active'),
                'Enrolled By' => $child->beneficiary->creator->fullname ?? 'N/A',
                'Enrollment Date' => $child->created_at->format('Y-m-d H:i:s')
            ]);
        }

        return $enrollments->sortByDesc('Enrollment Date')->values();
    }

    public function headings(): array
    {
        return [
            'BOSCHMA ID',
            'Name',
            'Category',
            'Gender',
            'Date of Birth',
            'Phone',
            'Email',
            'Facility',
            'Status',
            'Enrolled By',
            'Enrollment Date'
        ];
    }

    public function title(): string
    {
        return ucfirst($this->status) . ' Enrollments';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:K1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
