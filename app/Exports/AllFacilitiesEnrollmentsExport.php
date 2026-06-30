<?php

namespace App\Exports;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllFacilitiesEnrollmentsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $programId;
    protected $lga;
    protected $dateFrom;
    protected $dateTo;
    protected $gender;

    public function __construct($programId = null, $lga = null, $dateFrom = null, $dateTo = null, $gender = null)
    {
        $this->programId = $programId;
        $this->lga = $lga;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->gender = $gender;
    }

    /**
     * Returns a LazyCollection backed by DB cursors so only one record
     * is held in PHP memory at a time — essential for 200k+ rows.
     */
    public function collection(): LazyCollection
    {
        $programId = $this->programId;
        $lga       = $this->lga;
        $dateFrom  = $this->dateFrom ? $this->dateFrom . ' 00:00:00' : null;
        $dateTo    = $this->dateTo   ? $this->dateTo   . ' 23:59:59' : null;
        $gender    = $this->gender;

        return LazyCollection::make(function () use ($programId, $lga, $dateFrom, $dateTo, $gender) {

            // ── Beneficiaries ────────────────────────────────────────────────
            $bQuery = DB::table('beneficiaries as b')
                ->leftJoin('facilities as f', 'f.id', '=', 'b.facility_id')
                ->leftJoin('staff as s', 's.id', '=', 'b.created_by')
                ->select([
                    DB::raw("'Beneficiary' as record_type"),
                    'b.boschma_no',
                    'b.fullname as full_name',
                    'b.gender',
                    'b.date_of_birth as dob',
                    'b.nin',
                    'b.category',
                    'b.occupation',
                    'b.marital_status',
                    'f.name as facility_name',
                    'f.lga as facility_lga',
                    'b.phone_no as phone',
                    'b.email',
                    'b.contact_address as address',
                    'b.status',
                    's.fullname as enrolled_by',
                    'b.created_at',
                ])
                ->where('b.status', '!=', 'draft');

            if ($programId) $bQuery->where('b.program_id', $programId);
            if ($lga)       $bQuery->where('f.lga', $lga);
            if ($gender)    $bQuery->where('b.gender', $gender);
            if ($dateFrom && $dateTo) {
                $bQuery->whereBetween('b.created_at', [$dateFrom, $dateTo]);
            }

            foreach ($bQuery->orderBy('f.name')->orderBy('b.created_at')->cursor() as $row) {
                yield [
                    'Type'           => 'Beneficiary',
                    'BOSCHMA ID'     => $row->boschma_no    ?? 'N/A',
                    'Full Name'      => $row->full_name     ?? 'N/A',
                    'Gender'         => $row->gender        ?? 'N/A',
                    'Date of Birth'  => $row->dob           ?? 'N/A',
                    'NIN'            => $row->nin           ?? 'N/A',
                    'Category'       => $row->category      ?? 'N/A',
                    'Occupation'     => $row->occupation    ?? 'N/A',
                    'Marital Status' => $row->marital_status ?? 'N/A',
                    'Facility'       => $row->facility_name ?? 'N/A',
                    'Facility LGA'   => $row->facility_lga  ?? 'N/A',
                    'Phone'          => $row->phone         ?? 'N/A',
                    'Email'          => $row->email         ?? 'N/A',
                    'Address'        => $row->address       ?? 'N/A',
                    'Status'         => ucfirst($row->status ?? 'N/A'),
                    'Enrolled By'    => $row->enrolled_by   ?? 'N/A',
                    'Created At'     => $row->created_at    ?? 'N/A',
                ];
            }

            // ── Spouses ──────────────────────────────────────────────────────
            $sQuery = DB::table('spouses as sp')
                ->leftJoin('beneficiaries as b', 'b.id', '=', 'sp.beneficiary_id')
                ->leftJoin('facilities as f', 'f.id', '=', 'sp.facility_id')
                ->leftJoin('staff as s', 's.id', '=', 'b.created_by')
                ->select([
                    DB::raw("'Spouse' as record_type"),
                    'sp.boschma_no',
                    'sp.name as full_name',
                    'sp.gender',
                    'sp.dob',
                    'sp.nin',
                    DB::raw("'N/A' as category"),
                    DB::raw("'N/A' as occupation"),
                    DB::raw("'Married' as marital_status"),
                    'f.name as facility_name',
                    'f.lga as facility_lga',
                    'sp.phone',
                    'sp.email',
                    DB::raw("'N/A' as address"),
                    'b.status',
                    's.fullname as enrolled_by',
                    'sp.created_at',
                ]);

            if ($programId) $sQuery->where('b.program_id', $programId);
            if ($lga)       $sQuery->where('f.lga', $lga);
            if ($gender)    $sQuery->where('sp.gender', $gender);
            if ($dateFrom && $dateTo) {
                $sQuery->whereBetween('b.created_at', [$dateFrom, $dateTo]);
            }

            foreach ($sQuery->orderBy('f.name')->orderBy('sp.created_at')->cursor() as $row) {
                yield [
                    'Type'           => 'Spouse',
                    'BOSCHMA ID'     => $row->boschma_no    ?? 'N/A',
                    'Full Name'      => $row->full_name     ?? 'N/A',
                    'Gender'         => $row->gender        ?? 'N/A',
                    'Date of Birth'  => $row->dob           ?? 'N/A',
                    'NIN'            => $row->nin           ?? 'N/A',
                    'Category'       => 'N/A',
                    'Occupation'     => 'N/A',
                    'Marital Status' => 'Married',
                    'Facility'       => $row->facility_name ?? 'N/A',
                    'Facility LGA'   => $row->facility_lga  ?? 'N/A',
                    'Phone'          => $row->phone         ?? 'N/A',
                    'Email'          => $row->email         ?? 'N/A',
                    'Address'        => 'N/A',
                    'Status'         => ucfirst($row->status ?? 'N/A'),
                    'Enrolled By'    => $row->enrolled_by   ?? 'N/A',
                    'Created At'     => $row->created_at    ?? 'N/A',
                ];
            }

            // ── Children ─────────────────────────────────────────────────────
            $cQuery = DB::table('children as ch')
                ->leftJoin('beneficiaries as b', 'b.id', '=', 'ch.beneficiary_id')
                ->leftJoin('facilities as f', 'f.id', '=', 'ch.facility_id')
                ->leftJoin('staff as s', 's.id', '=', 'b.created_by')
                ->select([
                    DB::raw("'Child' as record_type"),
                    'ch.boschma_no',
                    'ch.name as full_name',
                    'ch.gender',
                    'ch.dob',
                    'ch.nin',
                    DB::raw("'N/A' as category"),
                    DB::raw("'N/A' as occupation"),
                    DB::raw("'Single' as marital_status"),
                    'f.name as facility_name',
                    'f.lga as facility_lga',
                    DB::raw("'N/A' as phone"),
                    DB::raw("'N/A' as email"),
                    DB::raw("'N/A' as address"),
                    'b.status',
                    's.fullname as enrolled_by',
                    'ch.created_at',
                ]);

            if ($programId) $cQuery->where('b.program_id', $programId);
            if ($lga)       $cQuery->where('f.lga', $lga);
            if ($gender)    $cQuery->where('ch.gender', $gender);
            if ($dateFrom && $dateTo) {
                $cQuery->whereBetween('b.created_at', [$dateFrom, $dateTo]);
            }

            foreach ($cQuery->orderBy('f.name')->orderBy('ch.created_at')->cursor() as $row) {
                yield [
                    'Type'           => 'Child',
                    'BOSCHMA ID'     => $row->boschma_no    ?? 'N/A',
                    'Full Name'      => $row->full_name     ?? 'N/A',
                    'Gender'         => $row->gender        ?? 'N/A',
                    'Date of Birth'  => $row->dob           ?? 'N/A',
                    'NIN'            => $row->nin           ?? 'N/A',
                    'Category'       => 'N/A',
                    'Occupation'     => 'N/A',
                    'Marital Status' => 'Single',
                    'Facility'       => $row->facility_name ?? 'N/A',
                    'Facility LGA'   => $row->facility_lga  ?? 'N/A',
                    'Phone'          => 'N/A',
                    'Email'          => 'N/A',
                    'Address'        => 'N/A',
                    'Status'         => ucfirst($row->status ?? 'N/A'),
                    'Enrolled By'    => $row->enrolled_by   ?? 'N/A',
                    'Created At'     => $row->created_at    ?? 'N/A',
                ];
            }
        });
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
            'Facility LGA',
            'Phone',
            'Email',
            'Address',
            'Status',
            'Enrolled By',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'All Facilities - Enrollments';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:Q1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F84AB']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
