<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EhrReportExport;

class EhrReportController extends Controller
{
    /**
     * Main EHR Report Dashboard
     */
    public function index(Request $request)
    {
        $facilityId = $request->get('facility_id');
        $programId  = $request->get('program_id');
        $dateFrom   = $request->get('date_from', Carbon::now()->subDays(90)->toDateString());
        $dateTo     = $request->get('date_to', Carbon::now()->toDateString());

        $facilities = DB::table('facilities')->orderBy('name')->get(['id', 'name']);
        $programs   = DB::table('programs')->where('status', 1)->orderBy('name')->get(['id', 'name']);

        // ── 1. Overview KPIs ──────────────────────────────────────────
        $kpis = $this->getKpis($facilityId, $programId, $dateFrom, $dateTo);

        // ── 2. Encounter Analytics ────────────────────────────────────
        $encountersByStatus   = $this->getEncountersByStatus($facilityId, $programId, $dateFrom, $dateTo);
        $encountersByFacility = $this->getEncountersByFacility($programId, $dateFrom, $dateTo);
        $encountersByProgram  = $this->getEncountersByProgram($facilityId, $dateFrom, $dateTo);
        $encounterTrend       = $this->getEncounterTrend($facilityId, $programId, $dateFrom, $dateTo);
        $encountersByNature   = $this->getEncountersByNature($facilityId, $programId, $dateFrom, $dateTo);

        // ── 3. Consultation Metrics ───────────────────────────────────
        $consultationStats    = $this->getConsultationStats($facilityId, $programId, $dateFrom, $dateTo);
        $topDoctors           = $this->getTopDoctors($facilityId, $programId, $dateFrom, $dateTo);
        $topDiagnoses         = $this->getTopDiagnoses($facilityId, $programId, $dateFrom, $dateTo);

        // ── 4. Pharmacy & Medication ──────────────────────────────────
        $pharmacyStats        = $this->getPharmacyStats($facilityId, $programId, $dateFrom, $dateTo);
        $prescriptionsByStatus = $this->getPrescriptionsByStatus($facilityId, $programId, $dateFrom, $dateTo);
        $topDrugs             = $this->getTopDrugs($facilityId, $programId, $dateFrom, $dateTo);
        $dispensationTrend    = $this->getDispensationTrend($facilityId, $programId, $dateFrom, $dateTo);

        // ── 5. Laboratory / Services ──────────────────────────────────
        $labStats             = $this->getLabStats($facilityId, $programId, $dateFrom, $dateTo);
        $labByStatus          = $this->getLabByStatus($facilityId, $programId, $dateFrom, $dateTo);
        $topLabTests          = $this->getTopLabTests($facilityId, $programId, $dateFrom, $dateTo);

        // ── 6. Waiting Queue Monitor ──────────────────────────────────
        $waitingQueue         = $this->getWaitingQueue($facilityId, $programId);

        // ── 7. Staff Performance ──────────────────────────────────────
        $staffPerformance     = $this->getStaffPerformance($facilityId, $programId, $dateFrom, $dateTo);

        // ── 8. Facility Comparison ────────────────────────────────────
        $facilityComparison   = $this->getFacilityComparison($programId, $dateFrom, $dateTo);

        return view('reports.ehr', compact(
            'facilities', 'programs', 'facilityId', 'programId', 'dateFrom', 'dateTo',
            'kpis',
            'encountersByStatus', 'encountersByFacility', 'encountersByProgram',
            'encounterTrend', 'encountersByNature',
            'consultationStats', 'topDoctors', 'topDiagnoses',
            'pharmacyStats', 'prescriptionsByStatus', 'topDrugs', 'dispensationTrend',
            'labStats', 'labByStatus', 'topLabTests',
            'waitingQueue',
            'staffPerformance',
            'facilityComparison'
        ));
    }

    // ── KPIs ──────────────────────────────────────────────────────────

    private function getKpis($facilityId, $programId, $dateFrom, $dateTo)
    {
        $enc = DB::table('encounters')
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->whereBetween('visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"]);

        $totalEncounters   = (clone $enc)->count();
        $completedEnc      = (clone $enc)->where('status', 'Completed')->count();
        $uniquePatients    = (clone $enc)->distinct('patient_id')->count('patient_id');

        $totalConsultations = DB::table('clinical_consultations as cc')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        $totalPrescriptions = DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        $totalDispensations = DB::table('pharmacy_dispensations as pd')
            ->join('prescription_items as pi', 'pd.prescription_item_id', '=', 'pi.id')
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->where('pd.quantity_dispensed', '>', 0)
            ->sum('pd.quantity_dispensed');

        $totalMedCost = DB::table('pharmacy_dispensations as pd')
            ->join('prescription_items as pi', 'pd.prescription_item_id', '=', 'pi.id')
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->where('pd.quantity_dispensed', '>', 0)
            ->sum('pd.cost_of_medication');

        $totalLabOrders = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        $vitalsTaken = DB::table('vital_signs as v')
            ->join('encounters as e', 'v.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        $totalServiceCost = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->join('service_items as si', 'soi.service_item_id', '=', 'si.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->sum('si.price');

        // Previous period for comparison
        $daysDiff = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        $prevFrom = Carbon::parse($dateFrom)->subDays($daysDiff)->toDateString();
        $prevTo   = Carbon::parse($dateFrom)->subDay()->toDateString();

        $prevEncounters = DB::table('encounters')
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->whereBetween('visit_date', ["$prevFrom 00:00:00", "$prevTo 23:59:59"])
            ->count();

        $prevConsultations = DB::table('clinical_consultations as cc')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$prevFrom 00:00:00", "$prevTo 23:59:59"])
            ->count();

        return [
            'total_encounters'    => $totalEncounters,
            'completed_encounters'=> $completedEnc,
            'unique_patients'     => $uniquePatients,
            'total_consultations' => $totalConsultations,
            'total_prescriptions' => $totalPrescriptions,
            'total_dispensations'  => $totalDispensations,
            'total_med_cost'      => $totalMedCost,
            'total_lab_orders'    => $totalLabOrders,
            'vitals_taken'        => $vitalsTaken,
            'total_service_cost'  => $totalServiceCost,
            'completion_rate'     => $totalEncounters > 0 ? round(($completedEnc / $totalEncounters) * 100, 1) : 0,
            'enc_change'          => $prevEncounters > 0 ? round((($totalEncounters - $prevEncounters) / $prevEncounters) * 100, 1) : ($totalEncounters > 0 ? 100 : 0),
            'consult_change'      => $prevConsultations > 0 ? round((($totalConsultations - $prevConsultations) / $prevConsultations) * 100, 1) : ($totalConsultations > 0 ? 100 : 0),
        ];
    }

    // ── Encounter Analytics ───────────────────────────────────────────

    private function getEncountersByStatus($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('encounters')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->whereBetween('visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();
    }

    private function getEncountersByFacility($programId, $dateFrom, $dateTo)
    {
        return DB::table('encounters as e')
            ->join('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('f.name as facility_name', 'f.id as facility_id', DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN e.status != 'Completed' AND e.status != 'Cancelled' THEN 1 ELSE 0 END) as active"))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('f.id', 'f.name')
            ->orderByDesc('total')
            ->get();
    }

    private function getEncountersByProgram($facilityId, $dateFrom, $dateTo)
    {
        return DB::table('encounters as e')
            ->join('programs as p', 'e.program_id', '=', 'p.id')
            ->select('p.name as program_name', 'p.id as program_id', DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed"))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total')
            ->get();
    }

    private function getEncounterTrend($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('encounters')
            ->select(DB::raw('DATE(visit_date) as date'), DB::raw('COUNT(*) as count'))
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->whereBetween('visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy(DB::raw('DATE(visit_date)'))
            ->orderBy('date')
            ->get();
    }

    private function getEncountersByNature($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('encounters')
            ->select('nature_of_visit', DB::raw('COUNT(*) as count'))
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->whereBetween('visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->whereNotNull('nature_of_visit')
            ->groupBy('nature_of_visit')
            ->orderByDesc('count')
            ->get();
    }

    // ── Consultation Metrics ──────────────────────────────────────────

    private function getConsultationStats($facilityId, $programId, $dateFrom, $dateTo)
    {
        $base = DB::table('clinical_consultations as cc')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"]);

        $total     = (clone $base)->count();
        $completed = (clone $base)->where('cc.status', 'Completed')->count();
        $withDiag  = (clone $base)->whereExists(function($q) {
            $q->select(DB::raw(1))
              ->from('clinical_diagnoses')
              ->whereColumn('clinical_diagnoses.clinical_consultation_id', 'cc.id');
        })->count();
        $withRx    = (clone $base)->whereExists(function($q) {
            $q->select(DB::raw(1))
              ->from('prescriptions as rx')
              ->whereColumn('rx.clinical_consultation_id', 'cc.id');
        })->count();

        $totalDiagnoses = DB::table('clinical_diagnoses')
            ->join('clinical_consultations as cc', 'clinical_diagnoses.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        return [
            'total'          => $total,
            'completed'      => $completed,
            'with_diagnosis' => $withDiag,
            'with_prescription' => $withRx,
            'total_diagnoses' => $totalDiagnoses,
            'avg_diagnoses'   => $total > 0 ? round($totalDiagnoses / $total, 1) : 0,
        ];
    }

    private function getTopDoctors($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('clinical_consultations as cc')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->join('users as u', 'cc.doctor_id', '=', 'u.id')
            ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('u.id', 'u.name as doctor_name', 'f.name as facility_name',
                DB::raw('COUNT(*) as consultations'),
                DB::raw("SUM(CASE WHEN cc.status = 'Completed' THEN 1 ELSE 0 END) as completed"))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('u.id', 'u.name', 'f.name')
            ->orderByDesc('consultations')
            ->limit(15)
            ->get();
    }

    private function getTopDiagnoses($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('clinical_diagnoses as cd')
            ->join('clinical_consultations as cc', 'cd.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->leftJoin('icd_codes as ic', 'cd.icd_code_id', '=', 'ic.id')
            ->select('ic.description as diagnosis_description', 'ic.code as icd_code', 'cd.diagnosis_type', DB::raw('COUNT(*) as count'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('ic.description', 'ic.code', 'cd.diagnosis_type')
            ->orderByDesc('count')
            ->limit(20)
            ->get();
    }

    // ── Pharmacy & Medication ─────────────────────────────────────────

    private function getPharmacyStats($facilityId, $programId, $dateFrom, $dateTo)
    {
        $rxBase = DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"]);

        $totalRx      = (clone $rxBase)->count();
        $dispensedRx   = (clone $rxBase)->where('p.status', 'Dispensed')->count();
        $pendingRx     = (clone $rxBase)->where('p.status', 'Pending')->count();
        $partialRx     = (clone $rxBase)->where('p.status', 'Partially Dispensed')->count();

        $totalItems = DB::table('prescription_items as pi')
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        $dispensedItems = DB::table('prescription_items as pi')
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->where('pi.dispensing_status', 'Dispensed')
            ->count();

        return [
            'total_prescriptions' => $totalRx,
            'dispensed'          => $dispensedRx,
            'pending'            => $pendingRx,
            'partial'            => $partialRx,
            'total_items'        => $totalItems,
            'dispensed_items'    => $dispensedItems,
            'fulfillment_rate'   => $totalItems > 0 ? round(($dispensedItems / $totalItems) * 100, 1) : 0,
        ];
    }

    private function getPrescriptionsByStatus($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->select('p.status', DB::raw('COUNT(*) as count'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('p.status')
            ->orderByDesc('count')
            ->get();
    }

    private function getTopDrugs($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('prescription_items as pi')
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->join('drugs as d', 'pi.drug_id', '=', 'd.id')
            ->select('d.name as drug_name', 'd.dosage_form', 'd.strength',
                DB::raw('COUNT(*) as times_prescribed'),
                DB::raw('SUM(pi.quantity) as total_qty'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('d.name', 'd.dosage_form', 'd.strength')
            ->orderByDesc('times_prescribed')
            ->limit(20)
            ->get();
    }

    private function getDispensationTrend($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('pharmacy_dispensations as pd')
            ->join('prescription_items as pi', 'pd.prescription_item_id', '=', 'pi.id')
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->select(DB::raw('DATE(pd.dispensing_date_time) as date'),
                DB::raw('SUM(CASE WHEN pd.quantity_dispensed > 0 THEN pd.quantity_dispensed ELSE 0 END) as qty'),
                DB::raw('SUM(CASE WHEN pd.cost_of_medication > 0 THEN pd.cost_of_medication ELSE 0 END) as cost'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy(DB::raw('DATE(pd.dispensing_date_time)'))
            ->orderBy('date')
            ->get();
    }

    // ── Laboratory / Services ─────────────────────────────────────────

    private function getLabStats($facilityId, $programId, $dateFrom, $dateTo)
    {
        $base = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"]);

        $total     = (clone $base)->count();
        $completed = (clone $base)->where('soi.status', 'completed')->count();
        $pending   = (clone $base)->where('soi.status', 'pending')->count();
        $inProg    = (clone $base)->where('soi.status', 'in_progress')->count();

        $resultsReported = DB::table('service_results as sr')
            ->join('service_order_items as soi', 'sr.service_order_item_id', '=', 'soi.id')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->count();

        return [
            'total'           => $total,
            'completed'       => $completed,
            'pending'         => $pending,
            'in_progress'     => $inProg,
            'results_reported'=> $resultsReported,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    private function getLabByStatus($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->select('soi.status', DB::raw('COUNT(*) as count'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('soi.status')
            ->orderByDesc('count')
            ->get();
    }

    private function getTopLabTests($facilityId, $programId, $dateFrom, $dateTo)
    {
        return DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->join('service_items as si', 'soi.service_item_id', '=', 'si.id')
            ->select('si.name as test_name', DB::raw('COUNT(*) as times_ordered'),
                DB::raw("SUM(CASE WHEN soi.status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN soi.status = 'pending' THEN 1 ELSE 0 END) as pending"))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('si.name')
            ->orderByDesc('times_ordered')
            ->limit(15)
            ->get();
    }

    // ── Waiting Queue Monitor ─────────────────────────────────────────

    private function getWaitingQueue($facilityId, $programId)
    {
        // Awaiting Pharmacy: pending prescriptions count
        $awaitingPharmacy = DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->where('p.status', 'Pending')
            ->count();

        // Awaiting Lab: pending/authorized lab order items count
        $awaitingLab = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereIn('soi.status', ['pending', 'authorized'])
            ->count();

        $inConsultation = DB::table('encounters')
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->where('status', 'In Consultation')
            ->count();

        $triaged = DB::table('encounters')
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->where('status', 'Triaged')
            ->count();

        $registered = DB::table('encounters')
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->where('status', 'Registered')
            ->count();

        // Get base facility stats
        $baseFacilityStats = DB::table('encounters as e')
            ->join('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('e.facility_id', 'f.name as facility_name',
                DB::raw("SUM(CASE WHEN e.status = 'Registered' THEN 1 ELSE 0 END) as registered"),
                DB::raw("SUM(CASE WHEN e.status = 'Triaged' THEN 1 ELSE 0 END) as triaged"),
                DB::raw("SUM(CASE WHEN e.status = 'In Consultation' THEN 1 ELSE 0 END) as in_consultation"))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereIn('e.status', ['Registered', 'Triaged', 'In Consultation'])
            ->groupBy('e.facility_id', 'f.name')
            ->get()
            ->keyBy('facility_id');

        // Get awaiting pharmacy per facility
        $pharmacyByFacility = DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->select('e.facility_id', DB::raw('COUNT(*) as awaiting_pharmacy'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->where('p.status', 'Pending')
            ->groupBy('e.facility_id')
            ->get()
            ->keyBy('facility_id');

        // Get awaiting lab per facility
        $labByFacility = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->select('e.facility_id', DB::raw('COUNT(*) as awaiting_lab'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereIn('soi.status', ['pending', 'authorized'])
            ->groupBy('e.facility_id')
            ->get()
            ->keyBy('facility_id');

        // Merge all facility stats
        $waitingByFacility = $baseFacilityStats->map(function($facility) use ($pharmacyByFacility, $labByFacility) {
            $facility->awaiting_pharmacy = $pharmacyByFacility->get($facility->facility_id)->awaiting_pharmacy ?? 0;
            $facility->awaiting_lab = $labByFacility->get($facility->facility_id)->awaiting_lab ?? 0;
            return $facility;
        })->values()->sortByDesc('registered')->values();

        return [
            'awaiting_pharmacy' => $awaitingPharmacy,
            'awaiting_lab'      => $awaitingLab,
            'in_consultation'   => $inConsultation,
            'triaged'           => $triaged,
            'registered'        => $registered,
            'total_waiting'     => $awaitingPharmacy + $awaitingLab + $inConsultation + $triaged + $registered,
            'by_facility'       => $waitingByFacility,
        ];
    }

    // ── Staff Performance ─────────────────────────────────────────────

    private function getStaffPerformance($facilityId, $programId, $dateFrom, $dateTo)
    {
        // Doctors - by consultations
        $doctors = DB::table('clinical_consultations as cc')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->join('users as u', 'cc.doctor_id', '=', 'u.id')
            ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('u.id', 'u.name', 'f.name as facility_name',
                DB::raw('COUNT(*) as total_consultations'),
                DB::raw("SUM(CASE WHEN cc.status = 'Completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw('COUNT(DISTINCT DATE(e.visit_date)) as active_days'),
                DB::raw('COUNT(DISTINCT e.patient_id) as unique_patients'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('u.id', 'u.name', 'f.name')
            ->orderByDesc('total_consultations')
            ->get()
            ->map(function($d) {
                $d->avg_per_day = $d->active_days > 0 ? round($d->total_consultations / $d->active_days, 1) : 0;
                $d->completion_rate = $d->total_consultations > 0 ? round(($d->completed / $d->total_consultations) * 100, 1) : 0;
                $d->role = 'Doctor';
                return $d;
            });

        // Nurses - by vitals taken
        $nurses = DB::table('vital_signs as v')
            ->join('encounters as e', 'v.encounter_id', '=', 'e.id')
            ->join('users as u', 'v.taken_by', '=', 'u.id')
            ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('u.id', 'u.name', 'f.name as facility_name',
                DB::raw('COUNT(*) as total_vitals'),
                DB::raw('COUNT(DISTINCT DATE(e.visit_date)) as active_days'),
                DB::raw('COUNT(DISTINCT e.patient_id) as unique_patients'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('u.id', 'u.name', 'f.name')
            ->orderByDesc('total_vitals')
            ->get()
            ->map(function($n) {
                $n->avg_per_day = $n->active_days > 0 ? round($n->total_vitals / $n->active_days, 1) : 0;
                $n->role = 'Nurse';
                return $n;
            });

        // Pharmacists - by dispensations
        $pharmacists = DB::table('pharmacy_dispensations as pd')
            ->join('users as u', 'pd.dispensing_officer_id', '=', 'u.id')
            ->leftJoin('prescription_items as pi', 'pd.prescription_item_id', '=', 'pi.id')
            ->leftJoin('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->leftJoin('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->leftJoin('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('u.id', 'u.name', 'f.name as facility_name',
                DB::raw('COUNT(*) as total_dispensations'),
                DB::raw('SUM(CASE WHEN pd.quantity_dispensed > 0 THEN pd.quantity_dispensed ELSE 0 END) as total_qty'),
                DB::raw('SUM(CASE WHEN pd.cost_of_medication > 0 THEN pd.cost_of_medication ELSE 0 END) as total_cost'),
                DB::raw('COUNT(DISTINCT DATE(pd.dispensing_date_time)) as active_days'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween(DB::raw('DATE(pd.dispensing_date_time)'), [$dateFrom, $dateTo])
            ->where('pd.quantity_dispensed', '>', 0)
            ->groupBy('u.id', 'u.name', 'f.name')
            ->orderByDesc('total_dispensations')
            ->get()
            ->map(function($p) {
                $p->avg_per_day = $p->active_days > 0 ? round($p->total_dispensations / $p->active_days, 1) : 0;
                $p->role = 'Pharmacist';
                return $p;
            });

        // Lab Techs - by results reported
        $labTechs = DB::table('service_results as sr')
            ->join('users as u', 'sr.reported_by', '=', 'u.id')
            ->leftJoin('service_order_items as soi', 'sr.service_order_item_id', '=', 'soi.id')
            ->leftJoin('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->leftJoin('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('u.id', 'u.name', 'f.name as facility_name',
                DB::raw('COUNT(*) as total_results'),
                DB::raw('COUNT(DISTINCT DATE(sr.reported_at)) as active_days'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween(DB::raw('DATE(sr.reported_at)'), [$dateFrom, $dateTo])
            ->groupBy('u.id', 'u.name', 'f.name')
            ->orderByDesc('total_results')
            ->get()
            ->map(function($l) {
                $l->avg_per_day = $l->active_days > 0 ? round($l->total_results / $l->active_days, 1) : 0;
                $l->role = 'Lab Technician';
                return $l;
            });

        // Receptionists (Front Desk) - by encounters registered
        $receptionists = DB::table('encounters as e')
            ->join('users as u', 'e.officer_in_charge_id', '=', 'u.id')
            ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('u.id', 'u.name', 'f.name as facility_name',
                DB::raw('COUNT(*) as total_encounters'),
                DB::raw('COUNT(DISTINCT DATE(e.visit_date)) as active_days'),
                DB::raw('COUNT(DISTINCT e.patient_id) as unique_patients'))
            ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
            ->when($programId, fn($q) => $q->where('e.program_id', $programId))
            ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
            ->groupBy('u.id', 'u.name', 'f.name')
            ->orderByDesc('total_encounters')
            ->get()
            ->map(function($r) {
                $r->avg_per_day = $r->active_days > 0 ? round($r->total_encounters / $r->active_days, 1) : 0;
                $r->role = 'Receptionist';
                return $r;
            });

        return [
            'doctors'       => $doctors,
            'nurses'        => $nurses,
            'pharmacists'   => $pharmacists,
            'lab_techs'     => $labTechs,
            'receptionists' => $receptionists,
        ];
    }

    // ── Facility Comparison ───────────────────────────────────────────

    private function getFacilityComparison($programId, $dateFrom, $dateTo)
    {
        $facilities = DB::table('facilities')->orderBy('name')->get(['id', 'name']);

        $result = [];
        foreach ($facilities as $fac) {
            $encBase = DB::table('encounters')
                ->where('facility_id', $fac->id)
                ->when($programId, fn($q) => $q->where('program_id', $programId))
                ->whereBetween('visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"]);

            $totalEnc = (clone $encBase)->count();
            if ($totalEnc === 0) continue;

            $completedEnc = (clone $encBase)->where('status', 'Completed')->count();

            $consultations = DB::table('clinical_consultations as cc')
                ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                ->where('e.facility_id', $fac->id)
                ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
                ->count();

            $rxItems = DB::table('prescription_items as pi')
                ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
                ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                ->where('e.facility_id', $fac->id)
                ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
                ->count();

            $labItems = DB::table('service_order_items as soi')
                ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
                ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
                ->where('e.facility_id', $fac->id)
                ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                ->whereBetween('e.visit_date', ["$dateFrom 00:00:00", "$dateTo 23:59:59"])
                ->count();

            $result[] = (object)[
                'facility_id'    => $fac->id,
                'facility_name'  => $fac->name,
                'total_encounters' => $totalEnc,
                'completed'      => $completedEnc,
                'completion_rate'=> round(($completedEnc / $totalEnc) * 100, 1),
                'consultations'  => $consultations,
                'rx_items'       => $rxItems,
                'lab_items'      => $labItems,
            ];
        }

        usort($result, fn($a, $b) => $b->total_encounters <=> $a->total_encounters);

        return collect($result);
    }

    // ── Export ─────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $section    = $request->get('section', 'all');
        $facilityId = $request->get('facility_id');
        $programId  = $request->get('program_id');
        $dateFrom   = $request->get('date_from', Carbon::now()->subDays(90)->toDateString());
        $dateTo     = $request->get('date_to', Carbon::now()->toDateString());

        $filename = 'ehr_report_' . $section . '_' . $dateFrom . '_to_' . $dateTo . '.xlsx';

        return Excel::download(
            new EhrReportExport($section, $facilityId, $programId, $dateFrom, $dateTo, $this),
            $filename
        );
    }

    // ── Drilldown (AJAX) ─────────────────────────────────────────────

    public function drilldown(Request $request)
    {
        $type       = $request->get('type');
        $facilityId = $request->get('facility_id');
        $programId  = $request->get('program_id');
        $dateFrom   = $request->get('date_from', Carbon::now()->subDays(90)->toDateString());
        $dateTo     = $request->get('date_to', Carbon::now()->toDateString());
        $status     = $request->get('status');
        $extra      = $request->get('extra');
        $perPage    = min((int) $request->get('per_page', 50), 10000);
        $page       = max((int) $request->get('page', 1), 1);
        $offset     = ($page - 1) * $perPage;
        $dateRange  = ["$dateFrom 00:00:00", "$dateTo 23:59:59"];

        $total = 0;
        $nameCol = true; // whether to insert patient name column

        switch ($type) {
            case 'encounters':
            case 'completed_encounters':
                $query = DB::table('encounters as e')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->leftJoin('programs as pr', 'e.program_id', '=', 'pr.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'f.name as facility', 'pr.name as program', 'e.status', 'e.visit_date', 'e.nature_of_visit')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                if ($type === 'completed_encounters') $query = $query->where('e.status', 'Completed');
                if ($status) $query = $query->where('e.status', $status);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Facility', 'Program', 'Status', 'Visit Date', 'Nature'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->facility, $r->program, $r->status, $r->visit_date, $r->nature_of_visit]);
                break;

            case 'unique_patients':
                $query = DB::table('encounters as e')
                    ->join('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.id', 'pt.file_number', 'pt.enrollee_number', 'pt.enrollee_type', 'f.name as facility', DB::raw('COUNT(*) as visits'), DB::raw('MAX(e.visit_date) as last_visit'))
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->groupBy('pt.id', 'pt.file_number', 'pt.enrollee_number', 'pt.enrollee_type', 'f.name');
                $total = DB::table(DB::raw("({$query->toSql()}) as sub"))->mergeBindings($query)->count();
                $rows = $query->orderByDesc('visits')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Type', 'Facility', 'Visits', 'Last Visit'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->enrollee_type, $r->facility, $r->visits, $r->last_visit]);
                break;

            case 'consultations':
                $query = DB::table('clinical_consultations as cc')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as doctor', 'cc.status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($status, fn($q) => $q->where('cc.status', $status));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Doctor', 'Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->doctor, $r->status, $r->facility, $r->visit_date]);
                break;

            case 'prescriptions':
                $query = DB::table('prescriptions as p')
                    ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'p.status', 'f.name as facility', 'p.created_at')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($status, fn($q) => $q->where('p.status', $status));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('p.created_at')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Status', 'Facility', 'Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->status, $r->facility, $r->created_at]);
                break;

            case 'dispensations':
            case 'med_cost':
                $query = DB::table('pharmacy_dispensations as pd')
                    ->join('prescription_items as pi', 'pd.prescription_item_id', '=', 'pi.id')
                    ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
                    ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'pd.quantity_dispensed', 'pd.cost_of_medication', 'f.name as facility', 'pd.dispensing_date_time')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('pd.dispensing_date_time')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Qty', 'Cost (₦)', 'Facility', 'Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->quantity_dispensed, number_format($r->cost_of_medication, 2), $r->facility, $r->dispensing_date_time]);
                break;

            case 'lab_orders':
                $query = DB::table('service_order_items as soi')
                    ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
                    ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
                    ->join('service_items as si', 'soi.service_item_id', '=', 'si.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'si.name as test', 'soi.status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($status, fn($q) => $q->where('soi.status', $status));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Test', 'Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->test, $r->status, $r->facility, $r->visit_date]);
                break;

            case 'vitals':
                $query = DB::table('vital_signs as v')
                    ->join('encounters as e', 'v.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'v.taken_by', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as taken_by', 'v.temperature', 'v.blood_pressure_systolic', 'v.blood_pressure_diastolic', 'v.pulse_rate', 'f.name as facility', 'v.created_at')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('v.created_at')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Taken By', 'Temp', 'BP (Sys/Dia)', 'Pulse', 'Facility', 'Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->taken_by, $r->temperature, $r->blood_pressure_systolic . '/' . $r->blood_pressure_diastolic, $r->pulse_rate, $r->facility, $r->created_at]);
                break;

            case 'service_cost':
                $query = DB::table('service_order_items as soi')
                    ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
                    ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
                    ->join('service_items as si', 'soi.service_item_id', '=', 'si.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'si.name as service', 'si.price', 'soi.status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('si.price')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Service', 'Price (₦)', 'Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->service, number_format($r->price, 2), $r->status, $r->facility, $r->visit_date]);
                break;

            case 'encounters_by_nature':
                $query = DB::table('encounters as e')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->leftJoin('programs as pr', 'e.program_id', '=', 'pr.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'f.name as facility', 'pr.name as program', 'e.status', 'e.visit_date', 'e.nature_of_visit')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('e.nature_of_visit', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Facility', 'Program', 'Status', 'Visit Date', 'Nature'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->facility, $r->program, $r->status, $r->visit_date, $r->nature_of_visit]);
                break;

            case 'consultations_with_diagnosis':
                $query = DB::table('clinical_diagnoses as cd')
                    ->join('clinical_consultations as cc', 'cd.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('icd_codes as ic', 'cd.icd_code_id', '=', 'ic.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as doctor', 'ic.description as diagnosis', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Doctor', 'Diagnosis', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->doctor, $r->diagnosis, $r->facility, $r->visit_date]);
                break;

            case 'consultations_with_prescription':
                $query = DB::table('prescriptions as p')
                    ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as doctor', 'p.status as rx_status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Doctor', 'Rx Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->doctor, $r->rx_status, $r->facility, $r->visit_date]);
                break;

            case 'diagnoses':
                $query = DB::table('clinical_diagnoses as cd')
                    ->join('clinical_consultations as cc', 'cd.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('icd_codes as ic', 'cd.icd_code_id', '=', 'ic.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'ic.description as diagnosis', 'ic.code as icd_code', 'cd.diagnosis_type', 'u.name as doctor', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('ic.description', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Diagnosis', 'ICD Code', 'Type', 'Doctor', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->diagnosis, $r->icd_code, $r->diagnosis_type, $r->doctor, $r->facility, $r->visit_date]);
                break;

            case 'drug_prescriptions':
                $query = DB::table('prescription_items as pi')
                    ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
                    ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('drugs as d', 'pi.drug_id', '=', 'd.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'd.name as drug_name', 'pi.quantity', 'pi.dosage', 'u.name as doctor', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('d.name', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Drug', 'Qty', 'Dosage', 'Doctor', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->drug_name, $r->quantity, $r->dosage, $r->doctor, $r->facility, $r->visit_date]);
                break;

            case 'lab_test_orders':
                $query = DB::table('service_order_items as soi')
                    ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
                    ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
                    ->join('service_items as si', 'soi.service_item_id', '=', 'si.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'si.name as test', 'soi.status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('si.name', $extra))
                    ->when($status, fn($q) => $q->where('soi.status', $status));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Test', 'Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->test, $r->status, $r->facility, $r->visit_date]);
                break;

            case 'staff_consultations':
                $query = DB::table('clinical_consultations as cc')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as doctor', 'cc.status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('cc.doctor_id', $extra))
                    ->when($status, fn($q) => $q->where('cc.status', $status));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Doctor', 'Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->doctor, $r->status, $r->facility, $r->visit_date]);
                break;

            case 'staff_vitals':
                $query = DB::table('vital_signs as v')
                    ->join('encounters as e', 'v.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'v.taken_by', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as nurse', 'v.temperature', 'v.blood_pressure_systolic', 'v.blood_pressure_diastolic', 'v.pulse_rate', 'f.name as facility', 'v.created_at')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('v.taken_by', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('v.created_at')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Nurse', 'Temp', 'BP', 'Pulse', 'Facility', 'Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->nurse, $r->temperature, $r->blood_pressure_systolic . '/' . $r->blood_pressure_diastolic, $r->pulse_rate, $r->facility, $r->created_at]);
                break;

            case 'staff_dispensations':
                $query = DB::table('pharmacy_dispensations as pd')
                    ->join('prescription_items as pi', 'pd.prescription_item_id', '=', 'pi.id')
                    ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
                    ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('drugs as d', 'pi.drug_id', '=', 'd.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'pd.dispensing_officer_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'd.name as drug_name', 'pd.quantity_dispensed', 'pd.cost_of_medication', 'u.name as pharmacist', 'f.name as facility', 'pd.dispensing_date_time')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('pd.dispensing_officer_id', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('pd.dispensing_date_time')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Drug', 'Qty', 'Cost (₦)', 'Pharmacist', 'Facility', 'Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->drug_name, $r->quantity_dispensed, number_format($r->cost_of_medication, 2), $r->pharmacist, $r->facility, $r->dispensing_date_time]);
                break;

            case 'staff_lab_results':
                $query = DB::table('service_results as sr')
                    ->join('service_order_items as soi', 'sr.service_order_item_id', '=', 'soi.id')
                    ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
                    ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
                    ->join('service_items as si', 'soi.service_item_id', '=', 'si.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'sr.reported_by', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'si.name as test', 'sr.result_value', 'u.name as lab_tech', 'f.name as facility', 'sr.reported_at')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('sr.reported_by', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('sr.reported_at')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Test', 'Result', 'Lab Tech', 'Facility', 'Reported At'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->test, $r->result_value, $r->lab_tech, $r->facility, $r->reported_at]);
                break;

            case 'staff_encounters':
                $query = DB::table('encounters as e')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->leftJoin('programs as pr', 'e.program_id', '=', 'pr.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'f.name as facility', 'pr.name as program', 'e.status', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange)
                    ->when($extra, fn($q) => $q->where('e.officer_in_charge_id', $extra));
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Facility', 'Program', 'Status', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->facility, $r->program, $r->status, $r->visit_date]);
                break;

            case 'encounters_by_status':
            case 'encounters_by_facility':
                $query = DB::table('encounters as e')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->leftJoin('programs as pr', 'e.program_id', '=', 'pr.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'f.name as facility', 'pr.name as program', 'e.status', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereBetween('e.visit_date', $dateRange);
                if ($status === 'active') {
                    $query = $query->where('e.status', '!=', 'Completed')->where('e.status', '!=', 'Cancelled');
                } elseif ($status) {
                    $query = $query->where('e.status', $status);
                }
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Facility', 'Program', 'Status', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->facility, $r->program, $r->status, $r->visit_date]);
                break;

            case 'awaiting_pharmacy':
                $query = DB::table('prescriptions as p')
                    ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
                    ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'cc.doctor_id', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as doctor', 'p.status as prescription_status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->where('p.status', 'Pending');
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Doctor', 'Prescription Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->doctor, $r->prescription_status, $r->facility, $r->visit_date]);
                break;

            case 'awaiting_lab':
                $query = DB::table('service_order_items as soi')
                    ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
                    ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
                    ->leftJoin('service_items as si', 'soi.service_item_id', '=', 'si.id')
                    ->leftJoin('patients as pt', 'e.patient_id', '=', 'pt.id')
                    ->leftJoin('users as u', 'so.ordered_by', '=', 'u.id')
                    ->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')
                    ->select('pt.file_number', 'pt.enrollee_number', 'u.name as ordered_by', 'si.name as test_name', 'soi.status', 'f.name as facility', 'e.visit_date')
                    ->when($facilityId, fn($q) => $q->where('e.facility_id', $facilityId))
                    ->when($programId, fn($q) => $q->where('e.program_id', $programId))
                    ->whereIn('soi.status', ['pending', 'authorized']);
                $total = (clone $query)->count();
                $rows = $query->orderByDesc('e.visit_date')->offset($offset)->limit($perPage)->get();
                $columns = ['SN', 'File #', 'Enrollee #', 'Patient Name', 'Ordered By', 'Test Name', 'Status', 'Facility', 'Visit Date'];
                $mapped = $rows->map(fn($r, $i) => [$offset + $i + 1, $r->file_number, $r->enrollee_number, '', $r->ordered_by, $r->test_name, $r->status, $r->facility, $r->visit_date]);
                break;

            default:
                $nameCol = false;
                return response()->json(['columns' => [], 'rows' => [], 'title' => 'Unknown', 'total' => 0]);
        }

        // Batch lookup patient names from beneficiaries/spouses/children via enrollee_number
        if ($nameCol && $mapped->isNotEmpty()) {
            $enrollees = $rows->pluck('enrollee_number')->filter()->unique()->values()->all();
            $nameMap = [];
            if (!empty($enrollees)) {
                $bNames = DB::table('beneficiaries')->whereIn('boschma_no', $enrollees)->pluck('fullname', 'boschma_no');
                $sNames = DB::table('spouses')->whereIn('boschma_no', $enrollees)->pluck('name', 'boschma_no');
                $cNames = DB::table('children')->whereIn('boschma_no', $enrollees)->pluck('name', 'boschma_no');
                foreach ($enrollees as $en) {
                    $nameMap[$en] = $bNames[$en] ?? $sNames[$en] ?? $cNames[$en] ?? '';
                }
            }
            $nameColIdx = array_search('Patient Name', $columns);
            if ($nameColIdx !== false) {
                $mapped = $mapped->map(function ($row) use ($nameColIdx, $nameMap) {
                    $enrollee = $row[2] ?? null; // enrollee_number is always index 2
                    $row[$nameColIdx] = $nameMap[$enrollee] ?? '';
                    return $row;
                });
            }
        }

        return response()->json([
            'columns'      => $columns,
            'rows'         => $mapped->values(),
            'title'        => ucwords(str_replace('_', ' ', $type)) . ($status ? " ($status)" : ''),
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $perPage,
            'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ]);
    }

    // Public accessors for export class
    public function exportEncountersByFacility($programId, $dateFrom, $dateTo) { return $this->getEncountersByFacility($programId, $dateFrom, $dateTo); }
    public function exportEncountersByStatus($facilityId, $programId, $dateFrom, $dateTo) { return $this->getEncountersByStatus($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportTopDoctors($facilityId, $programId, $dateFrom, $dateTo) { return $this->getTopDoctors($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportTopDiagnoses($facilityId, $programId, $dateFrom, $dateTo) { return $this->getTopDiagnoses($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportTopDrugs($facilityId, $programId, $dateFrom, $dateTo) { return $this->getTopDrugs($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportTopLabTests($facilityId, $programId, $dateFrom, $dateTo) { return $this->getTopLabTests($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportFacilityComparison($programId, $dateFrom, $dateTo) { return $this->getFacilityComparison($programId, $dateFrom, $dateTo); }
    public function exportStaffPerformance($facilityId, $programId, $dateFrom, $dateTo) { return $this->getStaffPerformance($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportKpis($facilityId, $programId, $dateFrom, $dateTo) { return $this->getKpis($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportPharmacyStats($facilityId, $programId, $dateFrom, $dateTo) { return $this->getPharmacyStats($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportLabStats($facilityId, $programId, $dateFrom, $dateTo) { return $this->getLabStats($facilityId, $programId, $dateFrom, $dateTo); }
    public function exportWaitingQueue($facilityId, $programId) { return $this->getWaitingQueue($facilityId, $programId); }
}
