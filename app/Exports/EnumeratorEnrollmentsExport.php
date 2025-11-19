<?php

namespace App\Exports;

use App\Models\Beneficiary;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnumeratorEnrollmentsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $enumeratorId;
    protected $enumeratorName;

    public function __construct($enumeratorId, $enumeratorName)
    {
        $this->enumeratorId = $enumeratorId;
        $this->enumeratorName = $enumeratorName;
    }

    public function collection()
    {
        // Get all enrollments by this enumerator
        $enrollments = Beneficiary::where('created_by', $this->enumeratorId)
            ->with('facility')
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        return new Collection($enrollments->map(function($enrollment) {
            return [
                'BOSCHMA ID' => $enrollment->boschma_no ?? 'N/A',
                'Beneficiary Name' => $enrollment->fullname ?? 'N/A',
                'Gender' => $enrollment->gender ?? 'N/A',
                'Date of Birth' => $enrollment->date_of_birth ?? 'N/A',
                'Phone' => $enrollment->phone_no ?? 'N/A',
                'Email' => $enrollment->email ?? 'N/A',
                'Facility' => $enrollment->facility->name ?? 'N/A',
                'Status' => ucfirst($enrollment->status ?? 'N/A'),
                'Enrollment Date' => $enrollment->created_at->format('Y-m-d H:i:s')
            ];
        }));
    }

    public function headings(): array
    {
        return [
            'BOSCHMA ID',
            'Beneficiary Name',
            'Gender',
            'Date of Birth',
            'Phone',
            'Email',
            'Facility',
            'Status',
            'Enrollment Date'
        ];
    }

    public function title(): string
    {
        return $this->enumeratorName . ' - Enrollments';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:I1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
