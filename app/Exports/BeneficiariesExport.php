<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BeneficiariesExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $query;
    protected $serialNumber = 1;
    protected $facilityName;

    // Number of branding rows inserted above column headers
    const HEADER_ROWS = 6;

    public function __construct($query, string $facilityName = 'ALL FACILITIES')
    {
        $this->query = clone $query;
        $this->facilityName = $facilityName;
        $this->query->with(['facility']);
        if (empty($this->query->getQuery()->orders)) {
            $this->query->orderBy('fullname');
        }
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['S/N', 'FULLNAME', 'GENDER', 'DOB', 'AGE GROUP', 'MARITAL STATUS', 'PHONE', 'NIN', 'IDNUMBER'];
    }

    public function map($beneficiary): array
    {
        $age = null;
        if (!empty($beneficiary->date_of_birth)) {
            try {
                $age = \Carbon\Carbon::parse($beneficiary->date_of_birth)->age;
            } catch (\Exception $e) {}
        }

        if ($age === null)       $ageGroup = 'Unknown';
        elseif ($age <= 5)       $ageGroup = '0-5';
        elseif ($age <= 17)      $ageGroup = '6-17';
        elseif ($age <= 35)      $ageGroup = '18-35';
        elseif ($age <= 50)      $ageGroup = '36-50';
        elseif ($age <= 64)      $ageGroup = '51-64';
        else                     $ageGroup = '65+';

        return [
            $this->serialNumber++,
            strtoupper($beneficiary->fullname),
            strtoupper($beneficiary->gender ?? ''),
            $beneficiary->date_of_birth ? \Carbon\Carbon::parse($beneficiary->date_of_birth)->format('Y-m-d') : 'N/A',
            $ageGroup,
            strtoupper($beneficiary->marital_status ?? 'N/A'),
            (string) ($beneficiary->phone_no ?? 'N/A'),
            (string) ($beneficiary->nin ?? 'N/A'),
            $beneficiary->boschma_no ?? 'N/A',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'I'; // 9 columns

                // Push existing data (headings + rows) down by HEADER_ROWS rows
                $sheet->insertNewRowBefore(1, self::HEADER_ROWS);

                // ── Row 1: Agency name ──────────────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'BORNO STATE CONTRIBUTORY HEALTHCARE MANAGEMENT AGENCY (BOSCHMA)');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF01542B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // ── Row 2: Wellness subtitle ────────────────────────────────
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Wellness for sustainable development');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FFFF0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Row 3: BHCPF with green bottom border ───────────────────
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'BASIC HEALTH CARE PROVISION FUND');
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFF0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF01542B']]],
                ]);

                // ── Row 4: blank separator ──────────────────────────────────
                $sheet->getRowDimension(4)->setRowHeight(6);

                // ── Row 5: "BENEFICIARY LIST" title with black bottom border ─
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->setCellValue('A5', 'BENEFICIARY LIST');
                $sheet->getStyle('A5')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF000000']]],
                ]);

                // ── Row 6: Health Facility ──────────────────────────────────
                $sheet->mergeCells("B6:{$lastCol}6");
                $sheet->setCellValue('A6', 'HEALTH FACILITY:');
                $sheet->setCellValue('B6', $this->facilityName);
                $sheet->getStyle('A6')->getFont()->setBold(true);
                $sheet->getStyle('B6')->getFont()->setBold(true);
                $sheet->getRowDimension(6)->setRowHeight(16);

                // ── Row 7 (column headers, now shifted down) ─────────────────
                $headingRow = self::HEADER_ROWS + 1;
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF999999']],
                    ],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(16);
            },
        ];
    }
}
