<?php

namespace App\Exports;

use App\Models\Beneficiary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BeneficiariesReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $programId;
    protected $lga;
    protected $gender;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($programId = null, $lga = null, $gender = null, $dateFrom = null, $dateTo = null)
    {
        $this->programId = $programId;
        $this->lga = $lga;
        $this->gender = $gender;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        $query = Beneficiary::with(['program', 'facility', 'spouse', 'children'])
            ->where('status', '!=', 'draft');

        if ($this->programId) {
            $query->where('program_id', $this->programId);
        }
        if ($this->lga) {
            $query->where('lga', $this->lga);
        }
        if ($this->gender) {
            $query->where('gender', $this->gender);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'BOSCHMA No',
            'Full Name',
            'Gender',
            'Phone No',
            'LGA',
            'Program',
            'Facility',
            'Dependents (Spouses)',
            'Dependents (Children)',
            'Total Dependents',
            'Status',
            'Date Enrolled'
        ];
    }

    public function map($beneficiary): array
    {
        $spousesCount = $beneficiary->spouse ? 1 : 0;
        $childrenCount = $beneficiary->children->count();
        $totalDependents = $spousesCount + $childrenCount;

        return [
            $beneficiary->boschma_no,
            $beneficiary->fullname,
            $beneficiary->gender,
            $beneficiary->phone_no,
            $beneficiary->lga,
            $beneficiary->program ? $beneficiary->program->name : '--',
            $beneficiary->facility ? $beneficiary->facility->name : '--',
            $spousesCount,
            $childrenCount,
            $totalDependents,
            ucfirst($beneficiary->status),
            $beneficiary->created_at ? $beneficiary->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:L1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
