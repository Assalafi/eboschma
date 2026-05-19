<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClaimsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $claims;

    public function __construct($claims)
    {
        $this->claims = $claims;
    }

    public function collection()
    {
        return $this->claims instanceof Collection ? $this->claims : collect($this->claims);
    }

    public function headings(): array
    {
        return [
            'Claim ID',
            'Authorization Code',
            'Beneficiary Name',
            'BOSCHMA ID',
            'Facility Name',
            'Claim Type',
            'Service Date',
            'Diagnosis',
            'Total Amount (₦)',
            'Status',
            'Created By',
            'Created At',
        ];
    }

    public function map($claim): array
    {
        // Handle FacilityClaim or fallback gracefully to legacy Claim
        $isFacilityClaim = isset($claim->claim_number);

        return [
            $isFacilityClaim ? $claim->claim_number : 'CLM-' . $claim->id,
            $isFacilityClaim ? $claim->enrollee_number : $claim->authorization_code,
            $isFacilityClaim ? ($claim->patient_name ?? 'N/A') : ($claim->beneficiary_name ?? 'N/A'),
            $isFacilityClaim ? ($claim->boschma_no ?? 'N/A') : ($claim->boschma_id ?? 'N/A'),
            $isFacilityClaim ? (optional($claim->facility)->name ?? 'N/A') : ($claim->healthcare_provider ?? optional($claim->facility)->name ?? 'N/A'),
            ucfirst(str_replace('_', ' ', $claim->claim_type ?? 'N/A')),
            $claim->service_date ? \Carbon\Carbon::parse($claim->service_date)->format('Y-m-d') : 'N/A',
            $isFacilityClaim ? ($claim->diagnoses ? $claim->diagnoses->pluck('icd_code')->join(', ') : 'N/A') : ($claim->diagnosis ?? 'N/A'),
            $isFacilityClaim ? ($claim->total_amount ?? 0) : ($claim->claim_amount ?? 0),
            ucfirst($claim->status ?? 'N/A'),
            $isFacilityClaim ? (optional($claim->submittedBy)->name ?? 'System') : (optional($claim->creator)->name ?? 'N/A'),
            $claim->created_at ? \Carbon\Carbon::parse($claim->created_at)->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
