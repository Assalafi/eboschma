<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Http\Controllers\EhrReportController;

class EhrReportExport implements WithMultipleSheets
{
    protected $section;
    protected $facilityId;
    protected $programId;
    protected $dateFrom;
    protected $dateTo;
    protected $controller;

    public function __construct($section, $facilityId, $programId, $dateFrom, $dateTo, EhrReportController $controller)
    {
        $this->section    = $section;
        $this->facilityId = $facilityId;
        $this->programId  = $programId;
        $this->dateFrom   = $dateFrom;
        $this->dateTo     = $dateTo;
        $this->controller = $controller;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->section === 'all' || $this->section === 'overview') {
            $sheets[] = new EmsKpiSheet($this->controller->exportKpis($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo), $this->dateFrom, $this->dateTo);
        }
        if ($this->section === 'all' || $this->section === 'encounters') {
            $sheets[] = new EmsEncountersByFacilitySheet($this->controller->exportEncountersByFacility($this->programId, $this->dateFrom, $this->dateTo));
            $sheets[] = new EmsEncountersByStatusSheet($this->controller->exportEncountersByStatus($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo));
        }
        if ($this->section === 'all' || $this->section === 'consultations') {
            $sheets[] = new EmsTopDoctorsSheet($this->controller->exportTopDoctors($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo));
            $sheets[] = new EmsTopDiagnosesSheet($this->controller->exportTopDiagnoses($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo));
        }
        if ($this->section === 'all' || $this->section === 'pharmacy') {
            $sheets[] = new EmsTopDrugsSheet($this->controller->exportTopDrugs($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo));
        }
        if ($this->section === 'all' || $this->section === 'laboratory') {
            $sheets[] = new EmsTopLabTestsSheet($this->controller->exportTopLabTests($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo));
        }
        if ($this->section === 'all' || $this->section === 'staff') {
            $perf = $this->controller->exportStaffPerformance($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo);
            $sheets[] = new EmsStaffDoctorsSheet($perf['doctors']);
            $sheets[] = new EmsStaffNursesSheet($perf['nurses']);
            $sheets[] = new EmsStaffPharmacistsSheet($perf['pharmacists']);
            $sheets[] = new EmsStaffLabTechsSheet($perf['lab_techs']);
        }
        if ($this->section === 'all' || $this->section === 'facility_comparison') {
            $sheets[] = new EmsFacilityComparisonSheet($this->controller->exportFacilityComparison($this->programId, $this->dateFrom, $this->dateTo));
        }

        return $sheets;
    }
}

// ── Helper Trait ──────────────────────────────────────────────────────
trait EmsSheetStyle
{
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
            'A1:Z1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '016634']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}

// ── KPI Sheet ─────────────────────────────────────────────────────────
class EmsKpiSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $kpis, $dateFrom, $dateTo;
    public function __construct($kpis, $dateFrom, $dateTo) { $this->kpis = $kpis; $this->dateFrom = $dateFrom; $this->dateTo = $dateTo; }
    public function collection()
    {
        return new Collection([
            ['Total Encounters', $this->kpis['total_encounters']],
            ['Completed Encounters', $this->kpis['completed_encounters']],
            ['Completion Rate', $this->kpis['completion_rate'] . '%'],
            ['Unique Patients', $this->kpis['unique_patients']],
            ['Total Consultations', $this->kpis['total_consultations']],
            ['Total Prescriptions', $this->kpis['total_prescriptions']],
            ['Units Dispensed', $this->kpis['total_dispensations']],
            ['Medication Cost', '₦' . number_format($this->kpis['total_med_cost'], 2)],
            ['Lab Orders', $this->kpis['total_lab_orders']],
            ['Vitals Taken', $this->kpis['vitals_taken']],
            ['', ''],
            ['Period', $this->dateFrom . ' to ' . $this->dateTo],
            ['Report Generated', now()->format('Y-m-d H:i:s')],
        ]);
    }
    public function headings(): array { return ['Metric', 'Value']; }
    public function title(): string { return 'Overview KPIs'; }
}

// ── Encounters by Facility ────────────────────────────────────────────
class EmsEncountersByFacilitySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($r) => [
            $r->facility_name, $r->total, $r->completed, $r->active,
            $r->total > 0 ? round(($r->completed / $r->total) * 100, 1) . '%' : '0%',
        ]));
    }
    public function headings(): array { return ['Facility', 'Total', 'Completed', 'Active', 'Completion Rate']; }
    public function title(): string { return 'Encounters by Facility'; }
}

// ── Encounters by Status ──────────────────────────────────────────────
class EmsEncountersByStatusSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return new Collection($this->data->map(fn($r) => [$r->status, $r->count])); }
    public function headings(): array { return ['Status', 'Count']; }
    public function title(): string { return 'Encounters by Status'; }
}

// ── Top Doctors ───────────────────────────────────────────────────────
class EmsTopDoctorsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($d) => [
            $d->doctor_name, $d->facility_name ?? 'N/A', $d->consultations, $d->completed,
            $d->consultations > 0 ? round(($d->completed / $d->consultations) * 100, 1) . '%' : '0%',
        ]));
    }
    public function headings(): array { return ['Doctor', 'Facility', 'Consultations', 'Completed', 'Completion Rate']; }
    public function title(): string { return 'Doctor Performance'; }
}

// ── Top Diagnoses ─────────────────────────────────────────────────────
class EmsTopDiagnosesSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($d) => [$d->diagnosis_description, $d->icd_code ?? '', $d->diagnosis_type, $d->count]));
    }
    public function headings(): array { return ['Diagnosis', 'ICD Code', 'Type', 'Occurrences']; }
    public function title(): string { return 'Top Diagnoses'; }
}

// ── Top Drugs ─────────────────────────────────────────────────────────
class EmsTopDrugsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($d) => [$d->drug_name, $d->dosage_form ?? '', $d->strength ?? '', $d->times_prescribed, $d->total_qty]));
    }
    public function headings(): array { return ['Drug', 'Dosage Form', 'Strength', 'Times Prescribed', 'Total Qty']; }
    public function title(): string { return 'Top Drugs Prescribed'; }
}

// ── Top Lab Tests ─────────────────────────────────────────────────────
class EmsTopLabTestsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($d) => [$d->test_name, $d->times_ordered, $d->completed, $d->pending]));
    }
    public function headings(): array { return ['Test', 'Times Ordered', 'Completed', 'Pending']; }
    public function title(): string { return 'Top Lab Tests'; }
}

// ── Staff Sheets ──────────────────────────────────────────────────────
class EmsStaffDoctorsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($d) => [$d->name, $d->facility_name ?? 'N/A', $d->total_consultations, $d->completed, $d->unique_patients, $d->active_days, $d->avg_per_day, $d->completion_rate . '%']));
    }
    public function headings(): array { return ['Doctor', 'Facility', 'Consultations', 'Completed', 'Unique Patients', 'Active Days', 'Avg/Day', 'Completion Rate']; }
    public function title(): string { return 'Doctors Performance'; }
}

class EmsStaffNursesSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($n) => [$n->name, $n->facility_name ?? 'N/A', $n->total_vitals, $n->unique_patients, $n->active_days, $n->avg_per_day]));
    }
    public function headings(): array { return ['Nurse', 'Facility', 'Vitals Taken', 'Unique Patients', 'Active Days', 'Avg/Day']; }
    public function title(): string { return 'Nurses Performance'; }
}

class EmsStaffPharmacistsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($p) => [$p->name, $p->facility_name ?? 'N/A', $p->total_dispensations, $p->total_qty, '₦' . number_format($p->total_cost, 2), $p->active_days, $p->avg_per_day]));
    }
    public function headings(): array { return ['Pharmacist', 'Facility', 'Dispensations', 'Total Qty', 'Total Cost', 'Active Days', 'Avg/Day']; }
    public function title(): string { return 'Pharmacists Performance'; }
}

class EmsStaffLabTechsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($l) => [$l->name, $l->facility_name ?? 'N/A', $l->total_results, $l->active_days, $l->avg_per_day]));
    }
    public function headings(): array { return ['Lab Technician', 'Facility', 'Results Reported', 'Active Days', 'Avg/Day']; }
    public function title(): string { return 'Lab Technicians Performance'; }
}

// ── Facility Comparison ───────────────────────────────────────────────
class EmsFacilityComparisonSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($f) => [$f->facility_name, $f->total_encounters, $f->completed, $f->completion_rate . '%', $f->consultations, $f->rx_items, $f->lab_items]));
    }
    public function headings(): array { return ['Facility', 'Encounters', 'Completed', 'Completion Rate', 'Consultations', 'Rx Items', 'Lab Items']; }
    public function title(): string { return 'Facility Comparison'; }
}
