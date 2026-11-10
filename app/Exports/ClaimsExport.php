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
    protected $userStage;

    public function __construct($claims, string $userStage = 'admin')
    {
        $this->claims = $claims;
        $this->userStage = $userStage;
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
            'Date Claimed',
            'Payment Date',
            'Diagnosis',
            'Total Amount (₦)',
            'Stage Status',
            'Verifier Status',
            'Approver Status',
            'ES Status',
            'Finance Status',
            'Created By',
            'Created At',
        ];
    }

    public function map($claim): array
    {
        // Handle FacilityClaim or fallback gracefully to legacy Claim
        $isFacilityClaim = isset($claim->claim_number);
        $stageStatus = $this->resolveStageStatusLabel($claim);

        return [
            $isFacilityClaim ? $claim->claim_number : 'CLM-' . $claim->id,
            $isFacilityClaim ? $claim->enrollee_number : $claim->authorization_code,
            $isFacilityClaim ? ($claim->patient_name ?? 'N/A') : ($claim->beneficiary_name ?? 'N/A'),
            $isFacilityClaim ? ($claim->boschma_no ?? 'N/A') : ($claim->boschma_id ?? 'N/A'),
            $isFacilityClaim ? (optional($claim->facility)->name ?? 'N/A') : ($claim->healthcare_provider ?? optional($claim->facility)->name ?? 'N/A'),
            ucfirst(str_replace('_', ' ', $claim->claim_type ?? 'N/A')),
            $claim->service_date ? \Carbon\Carbon::parse($claim->service_date)->format('Y-m-d') : 'N/A',
            $claim->created_at ? \Carbon\Carbon::parse($claim->created_at)->format('Y-m-d') : 'N/A',
            !empty($claim->payment_date) ? \Carbon\Carbon::parse($claim->payment_date)->format('Y-m-d') : 'N/A',
            $isFacilityClaim ? ($claim->diagnoses ? $claim->diagnoses->pluck('icd_code')->join(', ') : 'N/A') : ($claim->diagnosis ?? 'N/A'),
            $isFacilityClaim ? ($claim->total_amount ?? 0) : ($claim->claim_amount ?? 0),
            $stageStatus,
            ucfirst($claim->verifier_status ?? 'pending'),
            ucfirst($claim->approver_status ?? 'pending'),
            ucfirst($claim->es_status ?? 'pending'),
            ucfirst($claim->finance_status ?? 'pending'),
            $isFacilityClaim ? (optional($claim->submittedBy)->name ?? 'System') : (optional($claim->creator)->name ?? 'N/A'),
            $claim->created_at ? \Carbon\Carbon::parse($claim->created_at)->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    protected function resolveStageStatusLabel($claim): string
    {
        if ($this->userStage === 'verifier') {
            if (($claim->approver_status ?? '') === 'rejected') {
                return 'Rejected by Approver';
            }
            if (($claim->verifier_status ?? '') === 'rejected') {
                return 'Rejected (Back to Facility)';
            }
            if (($claim->verifier_status ?? '') === 'approved') {
                return 'Verified';
            }
            return 'Pending Verification';
        }

        if ($this->userStage === 'approver') {
            if (($claim->es_status ?? '') === 'rejected') {
                return 'Rejected by ES';
            }
            if (($claim->approver_status ?? '') === 'rejected') {
                return 'Rejected (Back to Verifier)';
            }
            if (($claim->approver_status ?? '') === 'approved') {
                return 'Approved';
            }
            return 'Pending Approval';
        }

        if ($this->userStage === 'es') {
            if (($claim->finance_status ?? '') === 'rejected') {
                return 'Rejected by Finance';
            }
            if (($claim->es_status ?? '') === 'rejected') {
                return 'Rejected (Back to Approver)';
            }
            if (($claim->es_status ?? '') === 'approved') {
                return 'ES Approved';
            }
            return 'Pending ES Approval';
        }

        if ($this->userStage === 'finance') {
            if (($claim->finance_status ?? '') === 'rejected') {
                return 'Rejected (Back to ES)';
            }
            if (($claim->finance_status ?? '') === 'paid' || ($claim->status ?? '') === 'paid') {
                return 'Paid';
            }
            return 'Pending Payment';
        }

        if (($claim->verifier_status ?? '') === 'rejected') {
            return 'Rejected by Verifier';
        }
        if (($claim->approver_status ?? '') === 'rejected') {
            return 'Rejected by Approver';
        }
        if (($claim->es_status ?? '') === 'rejected') {
            return 'Rejected by ES';
        }
        if (($claim->finance_status ?? '') === 'rejected') {
            return 'Rejected by Finance';
        }

        return ucfirst(str_replace('_', ' ', $claim->status ?? 'N/A'));
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
