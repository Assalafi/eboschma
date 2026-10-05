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
        $s = $this->section;

        // Add a sheet, skipping if a sheet with the same title already exists
        $add = function ($sheet) use (&$sheets) {
            $title = method_exists($sheet, 'title') ? $sheet->title() : null;
            foreach ($sheets as $existing) {
                if ($title !== null && method_exists($existing, 'title') && $existing->title() === $title) {
                    return;
                }
            }
            $sheets[] = $sheet;
        };

        // 1. Overview KPIs
        if ($s === 'all' || $s === 'overview') {
            $add(new EmsKpiSheet($this->controller->exportKpis($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo), $this->dateFrom, $this->dateTo));
        }

        // 2. Live Waiting Queue
        if ($s === 'all' || $s === 'waiting_queue') {
            $add(new EmsWaitingQueueSheet($this->controller->exportWaitingQueue($this->facilityId, $this->programId)));
        }

        // 3. Encounter analytics
        if ($s === 'all' || $s === 'encounter_trend') {
            $add(new EmsEncounterTrendSheet($this->controller->exportEncounterTrend($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'encounters' || $s === 'encounters_by_facility') {
            $add(new EmsEncountersByFacilitySheet($this->controller->exportEncountersByFacility($this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'encounters' || $s === 'encounters_by_status') {
            $add(new EmsEncountersByStatusSheet($this->controller->exportEncountersByStatus($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'encounters_by_program') {
            $add(new EmsEncountersByProgramSheet($this->controller->exportEncountersByProgram($this->facilityId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'visit_nature') {
            $add(new EmsVisitNatureSheet($this->controller->exportEncountersByNature($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }

        // 4. Consultation metrics
        if ($s === 'all' || $s === 'consultations' || $s === 'top_doctors') {
            $add(new EmsTopDoctorsSheet($this->controller->exportTopDoctors($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'consultations' || $s === 'top_diagnoses') {
            $add(new EmsTopDiagnosesSheet($this->controller->exportTopDiagnoses($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'consultation_summary') {
            $add(new EmsConsultationSummarySheet($this->controller->exportConsultationStats($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }

        // 5. Pharmacy & medication
        if ($s === 'all' || $s === 'pharmacy' || $s === 'top_drugs') {
            $add(new EmsTopDrugsSheet($this->controller->exportTopDrugs($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'pharmacy_summary') {
            $add(new EmsPharmacySummarySheet($this->controller->exportPharmacyStats($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'dispensation_trend') {
            $add(new EmsDispensationTrendSheet($this->controller->exportDispensationTrend($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }

        // 6. Laboratory / services
        if ($s === 'all' || $s === 'laboratory' || $s === 'top_lab_tests') {
            $add(new EmsTopLabTestsSheet($this->controller->exportTopLabTests($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }
        if ($s === 'all' || $s === 'lab_summary') {
            $add(new EmsLabSummarySheet($this->controller->exportLabStats($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo)));
        }

        // 7. Staff performance
        if ($s === 'all' || $s === 'staff') {
            $perf = $this->controller->exportStaffPerformance($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo);
            $add(new EmsStaffDoctorsSheet($perf['doctors']));
            $add(new EmsStaffNursesSheet($perf['nurses']));
            $add(new EmsStaffPharmacistsSheet($perf['pharmacists']));
            $add(new EmsStaffLabTechsSheet($perf['lab_techs']));
            $add(new EmsStaffReceptionistsSheet($perf['receptionists']));
        }
        if (in_array($s, ['staff_doctors', 'staff_nurses', 'staff_pharmacists', 'staff_lab_techs', 'staff_receptionists'], true)) {
            $perf = $this->controller->exportStaffPerformance($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo);
            if ($s === 'staff_doctors')       $add(new EmsStaffDoctorsSheet($perf['doctors']));
            if ($s === 'staff_nurses')        $add(new EmsStaffNursesSheet($perf['nurses']));
            if ($s === 'staff_pharmacists')   $add(new EmsStaffPharmacistsSheet($perf['pharmacists']));
            if ($s === 'staff_lab_techs')     $add(new EmsStaffLabTechsSheet($perf['lab_techs']));
            if ($s === 'staff_receptionists') $add(new EmsStaffReceptionistsSheet($perf['receptionists']));
        }

        // 8. Facility comparison
        if ($s === 'all' || $s === 'facility_comparison') {
            $add(new EmsFacilityComparisonSheet($this->controller->exportFacilityComparison($this->programId, $this->dateFrom, $this->dateTo)));
        }

        // Fallback so the workbook is never empty
        if (empty($sheets)) {
            $add(new EmsKpiSheet($this->controller->exportKpis($this->facilityId, $this->programId, $this->dateFrom, $this->dateTo), $this->dateFrom, $this->dateTo));
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

class EmsStaffReceptionistsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        return new Collection($this->data->map(fn($r) => [$r->name, $r->facility_name ?? 'N/A', $r->total_encounters, $r->unique_patients, $r->active_days, $r->avg_per_day]));
    }
    public function headings(): array { return ['Receptionist', 'Facility', 'Encounters Registered', 'Unique Patients', 'Active Days', 'Avg/Day']; }
    public function title(): string { return 'Receptionists Performance'; }
}

// ── Waiting Queue ─────────────────────────────────────────────────────
class EmsWaitingQueueSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        $rows = new Collection();
        foreach (($this->data['by_facility'] ?? collect()) as $f) {
            $rows->push([$f->facility_name, $f->registered, $f->triaged, $f->in_consultation, $f->awaiting_lab, $f->awaiting_pharmacy]);
        }
        if ($rows->isEmpty()) {
            $rows->push(['All Facilities', $this->data['registered'] ?? 0, $this->data['triaged'] ?? 0, $this->data['in_consultation'] ?? 0, $this->data['awaiting_lab'] ?? 0, $this->data['awaiting_pharmacy'] ?? 0]);
        }
        return $rows;
    }
    public function headings(): array { return ['Facility', 'Registered', 'Triaged', 'In Consultation', 'Awaiting Lab', 'Awaiting Pharmacy']; }
    public function title(): string { return 'Waiting Queue'; }
}

// ── Encounter Trend ───────────────────────────────────────────────────
class EmsEncounterTrendSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return new Collection($this->data->map(fn($r) => [$r->date, $r->count])); }
    public function headings(): array { return ['Date', 'Encounters']; }
    public function title(): string { return 'Encounter Trend'; }
}

// ── Encounters by Program ─────────────────────────────────────────────
class EmsEncountersByProgramSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return new Collection($this->data->map(fn($r) => [$r->program_name, $r->total, $r->completed])); }
    public function headings(): array { return ['Program', 'Total Encounters', 'Completed']; }
    public function title(): string { return 'Encounters by Program'; }
}

// ── Visit Nature ──────────────────────────────────────────────────────
class EmsVisitNatureSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return new Collection($this->data->map(fn($r) => [$r->nature_of_visit ?: 'Not specified', $r->count])); }
    public function headings(): array { return ['Visit Nature', 'Count']; }
    public function title(): string { return 'Visit Nature'; }
}

// ── Consultation Summary ──────────────────────────────────────────────
class EmsConsultationSummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        $d = $this->data;
        return new Collection([
            ['Total Consultations', $d['total'] ?? 0],
            ['Completed', $d['completed'] ?? 0],
            ['With Diagnosis', $d['with_diagnosis'] ?? 0],
            ['With Prescription', $d['with_prescription'] ?? 0],
            ['Total Diagnoses', $d['total_diagnoses'] ?? 0],
            ['Avg Diagnoses/Consult', $d['avg_diagnoses'] ?? 0],
        ]);
    }
    public function headings(): array { return ['Metric', 'Value']; }
    public function title(): string { return 'Consultation Summary'; }
}

// ── Pharmacy Summary ──────────────────────────────────────────────────
class EmsPharmacySummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        $d = $this->data;
        return new Collection([
            ['Total Prescriptions', $d['total_prescriptions'] ?? 0],
            ['Dispensed', $d['dispensed'] ?? 0],
            ['Partially Dispensed', $d['partial'] ?? 0],
            ['Pending', $d['pending'] ?? 0],
            ['Total Rx Items', $d['total_items'] ?? 0],
            ['Dispensed Items', $d['dispensed_items'] ?? 0],
            ['Fulfillment Rate', ($d['fulfillment_rate'] ?? 0) . '%'],
        ]);
    }
    public function headings(): array { return ['Metric', 'Value']; }
    public function title(): string { return 'Pharmacy Summary'; }
}

// ── Dispensation Trend ────────────────────────────────────────────────
class EmsDispensationTrendSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return new Collection($this->data->map(fn($r) => [$r->date, $r->qty, '₦' . number_format($r->cost, 2)])); }
    public function headings(): array { return ['Date', 'Quantity Dispensed', 'Cost']; }
    public function title(): string { return 'Dispensation Trend'; }
}

// ── Lab Summary ───────────────────────────────────────────────────────
class EmsLabSummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    use EmsSheetStyle;
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection()
    {
        $d = $this->data;
        return new Collection([
            ['Total Orders', $d['total'] ?? 0],
            ['Completed', $d['completed'] ?? 0],
            ['In Progress', $d['in_progress'] ?? 0],
            ['Pending', $d['pending'] ?? 0],
            ['Results Reported', $d['results_reported'] ?? 0],
            ['Completion Rate', ($d['completion_rate'] ?? 0) . '%'],
        ]);
    }
    public function headings(): array { return ['Metric', 'Value']; }
    public function title(): string { return 'Lab Summary'; }
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
