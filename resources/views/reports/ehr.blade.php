@extends('layouts.app')

@section('title', 'EHR Activity Reports')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Reports</div>
                    <h2 class="page-title">EHR Activity Reports</h2>
                    <div class="text-muted mt-1">Comprehensive monitoring of encounters, consultations, pharmacy, laboratory &amp; staff performance</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="#" class="btn" onclick="location.reload()"><i class="fas fa-sync"></i> Refresh</a>
                        <a href="{{ route('reports.ehr.export', request()->query()) }}" class="btn btn-primary"><i class="fas fa-download"></i> Export All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- ── Filters ──────────────────────────────────────────── --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('reports.ehr') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Facility</label>
                            <select name="facility_id" class="form-select form-select-sm">
                                <option value="">All Facilities</option>
                                @foreach($facilities as $f)
                                    <option value="{{ $f->id }}" {{ $facilityId == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">Program</label>
                            <select name="program_id" class="form-select form-select-sm">
                                <option value="">All Programs</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}" {{ $programId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-filter me-1"></i>Apply</button>
                            <a href="{{ route('reports.ehr') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── 1. Overview KPIs ─────────────────────────────────── --}}
            <div class="row row-deck row-cards mb-4">
                @php
                    $kpiCards = [
                        ['label' => 'Total Encounters', 'value' => $kpis['total_encounters'], 'icon' => 'fas fa-hospital-user', 'color' => 'primary', 'sub' => ($kpis['enc_change'] >= 0 ? '+' : '') . $kpis['enc_change'] . '% vs prev period', 'drill' => 'encounters'],
                        ['label' => 'Unique Patients', 'value' => $kpis['unique_patients'], 'icon' => 'fas fa-users', 'color' => 'info', 'sub' => 'Distinct patients seen', 'drill' => 'unique_patients'],
                        ['label' => 'Consultations', 'value' => $kpis['total_consultations'], 'icon' => 'fas fa-stethoscope', 'color' => 'success', 'sub' => ($kpis['consult_change'] >= 0 ? '+' : '') . $kpis['consult_change'] . '% vs prev period', 'drill' => 'consultations'],
                        ['label' => 'Service Cost', 'value' => '₦' . number_format($kpis['total_service_cost']), 'icon' => 'fas fa-receipt', 'color' => 'teal', 'sub' => 'Total service order value', 'drill' => 'service_cost'],
                        ['label' => 'Prescriptions', 'value' => $kpis['total_prescriptions'], 'icon' => 'fas fa-prescription', 'color' => 'purple', 'sub' => number_format($kpis['total_dispensations']) . ' units dispensed', 'drill' => 'prescriptions'],
                        ['label' => 'Medication Cost', 'value' => '₦' . number_format($kpis['total_med_cost']), 'icon' => 'fas fa-naira-sign', 'color' => 'warning', 'sub' => 'Total dispensation value', 'drill' => 'med_cost'],
                        ['label' => 'Lab Orders', 'value' => $kpis['total_lab_orders'], 'icon' => 'fas fa-flask', 'color' => 'cyan', 'sub' => 'Service order items', 'drill' => 'lab_orders'],
                        ['label' => 'Vitals Taken', 'value' => $kpis['vitals_taken'], 'icon' => 'fas fa-heartbeat', 'color' => 'danger', 'sub' => 'Triage assessments', 'drill' => 'vitals'],
                    ];
                @endphp
                @foreach($kpiCards as $kpi)
                <div class="col-6 col-sm-4 col-lg-3">
                    <div class="card card-sm border-0 shadow-sm drill-card" data-drill-type="{{ $kpi['drill'] ?? '' }}" style="cursor:pointer" title="Click to view details">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-{{ $kpi['color'] }}-lt text-{{ $kpi['color'] }} rounded-3 me-3">
                                    <i class="{{ $kpi['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">{{ $kpi['label'] }}</div>
                                    <div class="h3 mb-0 drill-value">{{ is_numeric($kpi['value']) ? number_format($kpi['value']) : $kpi['value'] }}</div>
                                    <div class="text-muted small" style="font-size:11px">{{ $kpi['sub'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── 2. Live Waiting Queue Monitor ────────────────────── --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="fas fa-clock text-warning me-2"></i>Live Waiting Queue Monitor</h3>
                    <span class="badge bg-{{ $waitingQueue['total_waiting'] > 0 ? 'warning' : 'success' }}">{{ $waitingQueue['total_waiting'] }} patients in queue</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        @php
                            $queueItems = [
                                ['label' => 'Registered', 'count' => $waitingQueue['registered'], 'color' => 'blue', 'icon' => 'fas fa-user-check'],
                                ['label' => 'Triaged', 'count' => $waitingQueue['triaged'], 'color' => 'cyan', 'icon' => 'fas fa-heartbeat'],
                                ['label' => 'In Consultation', 'count' => $waitingQueue['in_consultation'], 'color' => 'green', 'icon' => 'fas fa-stethoscope'],
                                ['label' => 'Awaiting Lab', 'count' => $waitingQueue['awaiting_lab'], 'color' => 'purple', 'icon' => 'fas fa-flask'],
                                ['label' => 'Awaiting Pharmacy', 'count' => $waitingQueue['awaiting_pharmacy'], 'color' => 'orange', 'icon' => 'fas fa-pills'],
                            ];
                        @endphp
                        @foreach($queueItems as $qi)
                        <div class="col">
                            <a href="javascript:void(0)" class="p-3 rounded-3 text-center d-block drill-link" style="background: var(--tblr-{{ $qi['color'] }}-lt, #f0f9ff); text-decoration:none; border-bottom:none" data-drill-type="{{ $qi['label'] === 'Awaiting Lab' ? 'awaiting_lab' : ($qi['label'] === 'Awaiting Pharmacy' ? 'awaiting_pharmacy' : 'encounters_by_status') }}" data-drill-status="{{ $qi['label'] }}">
                                <i class="{{ $qi['icon'] }} mb-1" style="font-size:18px; color: var(--tblr-{{ $qi['color'] }}, #206bc4)"></i>
                                <div class="h2 mb-0 fw-bold" style="color: var(--tblr-{{ $qi['color'] }}, #206bc4)">{{ $qi['count'] }}</div>
                                <div class="text-muted small fw-semibold">{{ $qi['label'] }}</div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @if($waitingQueue['by_facility']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr class="text-muted small">
                                <th>Facility</th><th class="text-center">Registered</th><th class="text-center">Triaged</th><th class="text-center">In Consultation</th><th class="text-center">Awaiting Lab</th><th class="text-center">Awaiting Pharmacy</th>
                            </tr></thead>
                            <tbody>
                            @foreach($waitingQueue['by_facility'] as $wf)
                            <tr>
                                <td class="fw-semibold">{{ $wf->facility_name }}</td>
                                <td class="text-center">@if($wf->registered > 0)<a href="javascript:void(0)" class="badge bg-blue-lt drill-link" data-drill-type="encounters_by_status" data-drill-status="Registered" data-drill-extra="{{ $wf->facility_id ?? '' }}">{{ $wf->registered }}</a>@else <span class="text-muted">0</span> @endif</td>
                                <td class="text-center">@if($wf->triaged > 0)<a href="javascript:void(0)" class="badge bg-cyan-lt drill-link" data-drill-type="encounters_by_status" data-drill-status="Triaged" data-drill-extra="{{ $wf->facility_id ?? '' }}">{{ $wf->triaged }}</a>@else <span class="text-muted">0</span> @endif</td>
                                <td class="text-center">@if($wf->in_consultation > 0)<a href="javascript:void(0)" class="badge bg-green-lt drill-link" data-drill-type="encounters_by_status" data-drill-status="In Consultation" data-drill-extra="{{ $wf->facility_id ?? '' }}">{{ $wf->in_consultation }}</a>@else <span class="text-muted">0</span> @endif</td>
                                <td class="text-center">@if($wf->awaiting_lab > 0)<a href="javascript:void(0)" class="badge bg-purple-lt drill-link" data-drill-type="awaiting_lab" data-drill-extra="{{ $wf->facility_id ?? '' }}">{{ $wf->awaiting_lab }}</a>@else <span class="text-muted">0</span> @endif</td>
                                <td class="text-center">@if($wf->awaiting_pharmacy > 0)<a href="javascript:void(0)" class="badge bg-orange-lt drill-link" data-drill-type="awaiting_pharmacy" data-drill-extra="{{ $wf->facility_id ?? '' }}">{{ $wf->awaiting_pharmacy }}</a>@else <span class="text-muted">0</span> @endif</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── 3. Encounter Analytics ───────────────────────────── --}}
            <div class="row g-4 mb-4">
                {{-- Encounter Trend Chart --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Encounter Trend</h3>
                            <a href="{{ route('reports.ehr.export', array_merge(request()->query(), ['section' => 'encounters'])) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i>Export</a>
                        </div>
                        <div class="card-body">
                            <canvas id="encounterTrendChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                {{-- By Facility --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-hospital text-success me-2"></i>Encounters by Facility</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr class="text-muted small"><th class="ps-3">Facility</th><th class="text-center">Total</th><th class="text-center">Completed</th><th class="text-center">Active</th><th class="text-center">Rate</th></tr></thead>
                                    <tbody>
                                    @foreach($encountersByFacility as $ef)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $ef->facility_name }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="drill-link fw-bold" data-drill-type="encounters_by_facility" data-drill-extra="{{ $ef->facility_id }}">{{ number_format($ef->total) }}</a></td>
                                        <td class="text-center"><a href="javascript:void(0)" class="badge bg-green-lt drill-link" data-drill-type="encounters_by_facility" data-drill-extra="{{ $ef->facility_id }}" data-drill-status="Completed">{{ $ef->completed }}</a></td>
                                        <td class="text-center"><a href="javascript:void(0)" class="badge bg-blue-lt drill-link" data-drill-type="encounters_by_facility" data-drill-extra="{{ $ef->facility_id }}" data-drill-status="active">{{ $ef->active }}</a></td>
                                        <td class="text-center">
                                            @php $rate = $ef->total > 0 ? round(($ef->completed / $ef->total) * 100, 1) : 0; @endphp
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <div class="progress" style="width:50px;height:6px"><div class="progress-bar bg-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}" style="width:{{ $rate }}%"></div></div>
                                                <span class="small">{{ $rate }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- By Program + Nature --}}
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-project-diagram text-purple me-2"></i>By Program</h3></div>
                        <div class="card-body">
                            @foreach($encountersByProgram as $ep)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-semibold" style="font-size:13px">{{ $ep->program_name }}</div>
                                    <div class="text-muted small">{{ $ep->completed }} completed</div>
                                </div>
                                <a href="javascript:void(0)" class="h4 mb-0 drill-link" data-drill-type="encounters" data-drill-extra="{{ $ep->program_id }}">{{ number_format($ep->total) }}</a>
                            </div>
                            @endforeach
                            @if($encountersByProgram->isEmpty())<div class="text-muted text-center py-3">No data</div>@endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-tag text-cyan me-2"></i>Visit Nature</h3></div>
                        <div class="card-body">
                            @foreach($encountersByNature as $en)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span style="font-size:13px">{{ $en->nature_of_visit ?: 'Not specified' }}</span>
                                <a href="javascript:void(0)" class="badge bg-cyan-lt drill-link" data-drill-type="encounters_by_nature" data-drill-extra="{{ $en->nature_of_visit }}">{{ number_format($en->count) }}</a>
                            </div>
                            @endforeach
                            @if($encountersByNature->isEmpty())<div class="text-muted text-center py-3">No data</div>@endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 4. Consultation Metrics ──────────────────────────── --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="fas fa-stethoscope text-success me-2"></i>Consultation Summary</h3>
                            <a href="{{ route('reports.ehr.export', array_merge(request()->query(), ['section' => 'consultations'])) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export</a>
                        </div>
                        <div class="card-body">
                            @php
                                $cStats = [
                                    ['label' => 'Total Consultations', 'val' => $consultationStats['total'], 'icon' => 'fas fa-notes-medical', 'drill' => 'consultations', 'status' => ''],
                                    ['label' => 'Completed', 'val' => $consultationStats['completed'], 'icon' => 'fas fa-check', 'drill' => 'consultations', 'status' => 'Completed'],
                                    ['label' => 'With Diagnosis', 'val' => $consultationStats['with_diagnosis'], 'icon' => 'fas fa-diagnoses', 'drill' => 'consultations_with_diagnosis', 'status' => ''],
                                    ['label' => 'With Prescription', 'val' => $consultationStats['with_prescription'], 'icon' => 'fas fa-prescription', 'drill' => 'consultations_with_prescription', 'status' => ''],
                                    ['label' => 'Total Diagnoses', 'val' => $consultationStats['total_diagnoses'], 'icon' => 'fas fa-clipboard-list', 'drill' => 'diagnoses', 'status' => ''],
                                    ['label' => 'Avg Diagnoses/Consult', 'val' => $consultationStats['avg_diagnoses'], 'icon' => 'fas fa-chart-bar', 'drill' => '', 'status' => ''],
                                ];
                            @endphp
                            @foreach($cStats as $cs)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted" style="font-size:13px"><i class="{{ $cs['icon'] }} me-2 text-success"></i>{{ $cs['label'] }}</span>
                                @if($cs['drill'])
                                <a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="{{ $cs['drill'] }}" data-drill-status="{{ $cs['status'] }}">{{ number_format($cs['val'], is_float($cs['val']) ? 1 : 0) }}</a>
                                @else
                                <span class="fw-bold">{{ number_format($cs['val'], is_float($cs['val']) ? 1 : 0) }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- Top Diagnoses --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-disease text-danger me-2"></i>Top 20 Diagnoses</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:380px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="sticky-top bg-white"><tr class="text-muted small"><th class="ps-3">#</th><th>Diagnosis</th><th>ICD Code</th><th>Type</th><th class="text-center">Count</th></tr></thead>
                                    <tbody>
                                    @foreach($topDiagnoses as $i => $td)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold" style="font-size:12px">{{ $td->diagnosis_description }}</td>
                                        <td><code>{{ $td->icd_code ?: '—' }}</code></td>
                                        <td><span class="badge bg-{{ $td->diagnosis_type === 'Confirmed' ? 'success' : 'warning' }}-lt" style="font-size:10px">{{ $td->diagnosis_type }}</span></td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="diagnoses" data-drill-extra="{{ $td->diagnosis_description }}">{{ $td->count }}</a></td>
                                    </tr>
                                    @endforeach
                                    @if($topDiagnoses->isEmpty())<tr><td colspan="5" class="text-center text-muted py-3">No diagnoses recorded</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Doctor Performance ───────────────────────────────── --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="fas fa-user-md text-primary me-2"></i>Doctor Performance</h3>
                    <a href="{{ route('reports.ehr.export', array_merge(request()->query(), ['section' => 'staff'])) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i>Export Staff</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr class="text-muted small">
                                <th class="ps-3">#</th><th>Doctor</th><th>Facility</th><th class="text-center">Consultations</th><th class="text-center">Completed</th><th class="text-center">Unique Patients</th><th class="text-center">Active Days</th><th class="text-center">Avg/Day</th><th class="text-center">Completion Rate</th>
                            </tr></thead>
                            <tbody>
                            @foreach($topDoctors as $i => $doc)
                            <tr>
                                <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $doc->doctor_name }}</td>
                                <td class="text-muted small">{{ $doc->facility_name ?? 'N/A' }}</td>
                                <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="staff_consultations" data-drill-extra="{{ $doc->doctor_id ?? $doc->id ?? '' }}">{{ $doc->consultations }}</a></td>
                                <td class="text-center"><a href="javascript:void(0)" class="badge bg-green-lt drill-link" data-drill-type="staff_consultations" data-drill-extra="{{ $doc->doctor_id ?? $doc->id ?? '' }}" data-drill-status="Completed">{{ $doc->completed }}</a></td>
                                <td class="text-center">{{ $doc->consultations }}</td>
                                <td class="text-center">—</td>
                                <td class="text-center">—</td>
                                <td class="text-center">
                                    @php $dr = $doc->consultations > 0 ? round(($doc->completed / $doc->consultations) * 100, 1) : 0; @endphp
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <div class="progress" style="width:40px;height:5px"><div class="progress-bar bg-{{ $dr >= 80 ? 'success' : ($dr >= 50 ? 'warning' : 'danger') }}" style="width:{{ $dr }}%"></div></div>
                                        <span class="small">{{ $dr }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if($topDoctors->isEmpty())<tr><td colspan="9" class="text-center text-muted py-3">No consultation data</td></tr>@endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── 5. Pharmacy & Medication ─────────────────────────── --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="fas fa-pills text-purple me-2"></i>Pharmacy Summary</h3>
                            <a href="{{ route('reports.ehr.export', array_merge(request()->query(), ['section' => 'pharmacy'])) }}" class="btn btn-sm btn-outline-purple"><i class="fas fa-download me-1"></i>Export</a>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1"><span class="text-muted small">Fulfillment Rate</span><span class="fw-bold">{{ $pharmacyStats['fulfillment_rate'] }}%</span></div>
                                <div class="progress" style="height:8px"><div class="progress-bar bg-{{ $pharmacyStats['fulfillment_rate'] >= 80 ? 'success' : ($pharmacyStats['fulfillment_rate'] >= 50 ? 'warning' : 'danger') }}" style="width:{{ $pharmacyStats['fulfillment_rate'] }}%"></div></div>
                            </div>
                            @php
                                $rxStats = [
                                    ['label' => 'Total Prescriptions', 'val' => $pharmacyStats['total_prescriptions'], 'badge' => 'secondary', 'drill' => 'prescriptions', 'status' => ''],
                                    ['label' => 'Dispensed', 'val' => $pharmacyStats['dispensed'], 'badge' => 'success', 'drill' => 'prescriptions', 'status' => 'Dispensed'],
                                    ['label' => 'Partially Dispensed', 'val' => $pharmacyStats['partial'], 'badge' => 'warning', 'drill' => 'prescriptions', 'status' => 'Partially Dispensed'],
                                    ['label' => 'Pending', 'val' => $pharmacyStats['pending'], 'badge' => 'danger', 'drill' => 'prescriptions', 'status' => 'Pending'],
                                    ['label' => 'Total Rx Items', 'val' => $pharmacyStats['total_items'], 'badge' => 'info', 'drill' => 'dispensations', 'status' => ''],
                                    ['label' => 'Dispensed Items', 'val' => $pharmacyStats['dispensed_items'], 'badge' => 'success', 'drill' => 'dispensations', 'status' => ''],
                                ];
                            @endphp
                            @foreach($rxStats as $rs)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:13px">
                                <span class="text-muted">{{ $rs['label'] }}</span>
                                <a href="javascript:void(0)" class="badge bg-{{ $rs['badge'] }}-lt fw-bold drill-link" data-drill-type="{{ $rs['drill'] }}" data-drill-status="{{ $rs['status'] }}">{{ number_format($rs['val']) }}</a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- Dispensation Trend --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-chart-area text-warning me-2"></i>Dispensation Trend</h3></div>
                        <div class="card-body">
                            <canvas id="dispensationTrendChart" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Drugs --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-capsules text-teal me-2"></i>Top 20 Most Prescribed Drugs</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr class="text-muted small"><th class="ps-3">#</th><th>Drug</th><th>Dosage Form</th><th>Strength</th><th class="text-center">Times Prescribed</th><th class="text-center">Total Qty</th></tr></thead>
                            <tbody>
                            @foreach($topDrugs as $i => $drug)
                            <tr>
                                <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $drug->drug_name }}</td>
                                <td class="text-muted small">{{ $drug->dosage_form ?: '—' }}</td>
                                <td class="text-muted small">{{ $drug->strength ?: '—' }}</td>
                                <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="drug_prescriptions" data-drill-extra="{{ $drug->drug_name }}">{{ number_format($drug->times_prescribed) }}</a></td>
                                <td class="text-center"><span class="badge bg-teal-lt">{{ number_format($drug->total_qty) }}</span></td>
                            </tr>
                            @endforeach
                            @if($topDrugs->isEmpty())<tr><td colspan="6" class="text-center text-muted py-3">No prescription data</td></tr>@endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── 6. Laboratory / Services ─────────────────────────── --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="fas fa-flask text-cyan me-2"></i>Lab Summary</h3>
                            <a href="{{ route('reports.ehr.export', array_merge(request()->query(), ['section' => 'laboratory'])) }}" class="btn btn-sm btn-outline-cyan"><i class="fas fa-download me-1"></i>Export</a>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1"><span class="text-muted small">Completion Rate</span><span class="fw-bold">{{ $labStats['completion_rate'] }}%</span></div>
                                <div class="progress" style="height:8px"><div class="progress-bar bg-cyan" style="width:{{ $labStats['completion_rate'] }}%"></div></div>
                            </div>
                            @php
                                $labItems = [
                                    ['label' => 'Total Orders', 'val' => $labStats['total'], 'status' => ''],
                                    ['label' => 'Completed', 'val' => $labStats['completed'], 'status' => 'completed'],
                                    ['label' => 'In Progress', 'val' => $labStats['in_progress'], 'status' => 'in_progress'],
                                    ['label' => 'Pending', 'val' => $labStats['pending'], 'status' => 'pending'],
                                    ['label' => 'Results Reported', 'val' => $labStats['results_reported'], 'status' => 'completed'],
                                ];
                            @endphp
                            @foreach($labItems as $li)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:13px">
                                <span class="text-muted">{{ $li['label'] }}</span>
                                <a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="lab_orders" data-drill-status="{{ $li['status'] }}">{{ number_format($li['val']) }}</a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- Top Lab Tests --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-vials text-purple me-2"></i>Most Ordered Tests</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:380px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="sticky-top bg-white"><tr class="text-muted small"><th class="ps-3">#</th><th>Test</th><th class="text-center">Ordered</th><th class="text-center">Completed</th><th class="text-center">Pending</th><th class="text-center">Rate</th></tr></thead>
                                    <tbody>
                                    @foreach($topLabTests as $i => $lt)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold" style="font-size:12px">{{ $lt->test_name }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="lab_test_orders" data-drill-extra="{{ $lt->test_name }}">{{ $lt->times_ordered }}</a></td>
                                        <td class="text-center"><a href="javascript:void(0)" class="badge bg-green-lt drill-link" data-drill-type="lab_test_orders" data-drill-extra="{{ $lt->test_name }}" data-drill-status="completed">{{ $lt->completed }}</a></td>
                                        <td class="text-center"><a href="javascript:void(0)" class="badge bg-orange-lt drill-link" data-drill-type="lab_test_orders" data-drill-extra="{{ $lt->test_name }}" data-drill-status="pending">{{ $lt->pending }}</a></td>
                                        <td class="text-center">
                                            @php $lr = $lt->times_ordered > 0 ? round(($lt->completed / $lt->times_ordered) * 100) : 0; @endphp
                                            <span class="small">{{ $lr }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($topLabTests->isEmpty())<tr><td colspan="6" class="text-center text-muted py-3">No lab data</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 7. Staff Performance ─────────────────────────────── --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-users-cog text-indigo me-2"></i>Staff Performance Overview</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="staffTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#staffDoctors">Doctors ({{ $staffPerformance['doctors']->count() }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#staffNurses">Nurses ({{ $staffPerformance['nurses']->count() }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#staffPharmacists">Pharmacists ({{ $staffPerformance['pharmacists']->count() }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#staffLabTechs">Lab Techs ({{ $staffPerformance['lab_techs']->count() }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#staffReceptionists">Receptionists ({{ $staffPerformance['receptionists']->count() }})</a></li>
                    </ul>
                    <div class="tab-content">
                        {{-- Doctors Tab --}}
                        <div class="tab-pane fade show active" id="staffDoctors">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr class="text-muted small"><th>#</th><th>Name</th><th>Facility</th><th class="text-center">Consultations</th><th class="text-center">Completed</th><th class="text-center">Patients</th><th class="text-center">Days Active</th><th class="text-center">Avg/Day</th><th class="text-center">Rate</th></tr></thead>
                                    <tbody>
                                    @foreach($staffPerformance['doctors'] as $i => $d)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $d->name }}</td>
                                        <td class="text-muted small">{{ $d->facility_name ?? '—' }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="staff_consultations" data-drill-extra="{{ $d->id }}">{{ $d->total_consultations }}</a></td>
                                        <td class="text-center"><a href="javascript:void(0)" class="badge bg-green-lt drill-link" data-drill-type="staff_consultations" data-drill-extra="{{ $d->id }}" data-drill-status="Completed">{{ $d->completed }}</a></td>
                                        <td class="text-center">{{ $d->unique_patients }}</td>
                                        <td class="text-center">{{ $d->active_days }}</td>
                                        <td class="text-center"><span class="badge bg-blue-lt">{{ $d->avg_per_day }}</span></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <div class="progress" style="width:40px;height:5px"><div class="progress-bar bg-{{ $d->completion_rate >= 80 ? 'success' : ($d->completion_rate >= 50 ? 'warning' : 'danger') }}" style="width:{{ $d->completion_rate }}%"></div></div>
                                                <span class="small">{{ $d->completion_rate }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($staffPerformance['doctors']->isEmpty())<tr><td colspan="9" class="text-center text-muted py-3">No data</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- Nurses Tab --}}
                        <div class="tab-pane fade" id="staffNurses">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr class="text-muted small"><th>#</th><th>Name</th><th>Facility</th><th class="text-center">Vitals Taken</th><th class="text-center">Patients</th><th class="text-center">Days Active</th><th class="text-center">Avg/Day</th></tr></thead>
                                    <tbody>
                                    @foreach($staffPerformance['nurses'] as $i => $n)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $n->name }}</td>
                                        <td class="text-muted small">{{ $n->facility_name ?? '—' }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="staff_vitals" data-drill-extra="{{ $n->id }}">{{ $n->total_vitals }}</a></td>
                                        <td class="text-center">{{ $n->unique_patients }}</td>
                                        <td class="text-center">{{ $n->active_days }}</td>
                                        <td class="text-center"><span class="badge bg-cyan-lt">{{ $n->avg_per_day }}</span></td>
                                    </tr>
                                    @endforeach
                                    @if($staffPerformance['nurses']->isEmpty())<tr><td colspan="7" class="text-center text-muted py-3">No data</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- Pharmacists Tab --}}
                        <div class="tab-pane fade" id="staffPharmacists">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr class="text-muted small"><th>#</th><th>Name</th><th>Facility</th><th class="text-center">Dispensations</th><th class="text-center">Qty</th><th class="text-center">Total Cost</th><th class="text-center">Days Active</th><th class="text-center">Avg/Day</th></tr></thead>
                                    <tbody>
                                    @foreach($staffPerformance['pharmacists'] as $i => $p)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $p->name }}</td>
                                        <td class="text-muted small">{{ $p->facility_name ?? '—' }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="staff_dispensations" data-drill-extra="{{ $p->id }}">{{ $p->total_dispensations }}</a></td>
                                        <td class="text-center">{{ number_format($p->total_qty) }}</td>
                                        <td class="text-center">₦{{ number_format($p->total_cost) }}</td>
                                        <td class="text-center">{{ $p->active_days }}</td>
                                        <td class="text-center"><span class="badge bg-purple-lt">{{ $p->avg_per_day }}</span></td>
                                    </tr>
                                    @endforeach
                                    @if($staffPerformance['pharmacists']->isEmpty())<tr><td colspan="8" class="text-center text-muted py-3">No data</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- Lab Techs Tab --}}
                        <div class="tab-pane fade" id="staffLabTechs">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr class="text-muted small"><th>#</th><th>Name</th><th>Facility</th><th class="text-center">Results Reported</th><th class="text-center">Days Active</th><th class="text-center">Avg/Day</th></tr></thead>
                                    <tbody>
                                    @foreach($staffPerformance['lab_techs'] as $i => $l)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $l->name }}</td>
                                        <td class="text-muted small">{{ $l->facility_name ?? '—' }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="staff_lab_results" data-drill-extra="{{ $l->id }}">{{ $l->total_results }}</a></td>
                                        <td class="text-center">{{ $l->active_days }}</td>
                                        <td class="text-center"><span class="badge bg-indigo-lt">{{ $l->avg_per_day }}</span></td>
                                    </tr>
                                    @endforeach
                                    @if($staffPerformance['lab_techs']->isEmpty())<tr><td colspan="6" class="text-center text-muted py-3">No data</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- Receptionists Tab --}}
                        <div class="tab-pane fade" id="staffReceptionists">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr class="text-muted small"><th>#</th><th>Name</th><th>Facility</th><th class="text-center">Encounters Registered</th><th class="text-center">Patients</th><th class="text-center">Days Active</th><th class="text-center">Avg/Day</th></tr></thead>
                                    <tbody>
                                    @foreach($staffPerformance['receptionists'] as $i => $r)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $r->name }}</td>
                                        <td class="text-muted small">{{ $r->facility_name ?? '—' }}</td>
                                        <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="staff_encounters" data-drill-extra="{{ $r->id }}">{{ $r->total_encounters }}</a></td>
                                        <td class="text-center">{{ $r->unique_patients }}</td>
                                        <td class="text-center">{{ $r->active_days }}</td>
                                        <td class="text-center"><span class="badge bg-orange-lt">{{ $r->avg_per_day }}</span></td>
                                    </tr>
                                    @endforeach
                                    @if($staffPerformance['receptionists']->isEmpty())<tr><td colspan="7" class="text-center text-muted py-3">No data</td></tr>@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 8. Facility Comparison ───────────────────────────── --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="fas fa-balance-scale text-teal me-2"></i>Facility Comparison</h3>
                    <a href="{{ route('reports.ehr.export', array_merge(request()->query(), ['section' => 'facility_comparison'])) }}" class="btn btn-sm btn-outline-teal"><i class="fas fa-download me-1"></i>Export</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr class="text-muted small"><th class="ps-3">#</th><th>Facility</th><th class="text-center">Encounters</th><th class="text-center">Completed</th><th class="text-center">Completion Rate</th><th class="text-center">Consultations</th><th class="text-center">Rx Items</th><th class="text-center">Lab Items</th></tr></thead>
                            <tbody>
                            @foreach($facilityComparison as $i => $fc)
                            <tr>
                                <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $fc->facility_name }}</td>
                                <td class="text-center"><a href="javascript:void(0)" class="fw-bold drill-link" data-drill-type="encounters_by_facility" data-drill-extra="{{ $fc->facility_id }}">{{ number_format($fc->total_encounters) }}</a></td>
                                <td class="text-center"><a href="javascript:void(0)" class="badge bg-green-lt drill-link" data-drill-type="encounters_by_facility" data-drill-extra="{{ $fc->facility_id }}" data-drill-status="Completed">{{ number_format($fc->completed) }}</a></td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <div class="progress" style="width:60px;height:6px"><div class="progress-bar bg-{{ $fc->completion_rate >= 80 ? 'success' : ($fc->completion_rate >= 50 ? 'warning' : 'danger') }}" style="width:{{ $fc->completion_rate }}%"></div></div>
                                        <span class="small fw-semibold">{{ $fc->completion_rate }}%</span>
                                    </div>
                                </td>
                                <td class="text-center"><a href="javascript:void(0)" class="drill-link" data-drill-type="consultations" data-drill-extra="{{ $fc->facility_id }}">{{ number_format($fc->consultations) }}</a></td>
                                <td class="text-center"><a href="javascript:void(0)" class="badge bg-purple-lt drill-link" data-drill-type="prescriptions" data-drill-extra="{{ $fc->facility_id }}">{{ number_format($fc->rx_items) }}</a></td>
                                <td class="text-center"><a href="javascript:void(0)" class="badge bg-cyan-lt drill-link" data-drill-type="lab_orders" data-drill-extra="{{ $fc->facility_id }}">{{ number_format($fc->lab_items) }}</a></td>
                            </tr>
                            @endforeach
                            @if($facilityComparison->isEmpty())<tr><td colspan="8" class="text-center text-muted py-3">No facility data for this period</td></tr>@endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── Drilldown Modal ────────────────────────────────────────── --}}
<div class="modal fade" id="drilldownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="drilldownTitle"><i class="fas fa-list me-2"></i>Details</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="drilldownExportBtn" class="btn btn-sm btn-outline-success d-none" onclick="exportDrilldown()"><i class="fas fa-file-excel me-1"></i>Export</button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="drilldownLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2">Loading records...</div>
                </div>
                <div id="drilldownContent" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped mb-0" id="drilldownTable">
                            <thead id="drilldownHead" class="table-light"></thead>
                            <tbody id="drilldownBody"></tbody>
                        </table>
                    </div>
                    <div id="drilldownFooter" class="px-3 py-2 bg-light border-top text-muted small"></div>
                </div>
                <div id="drilldownEmpty" class="d-none text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size:2rem"></i>
                    <div class="text-muted mt-2">No records found</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Encounter Trend Chart ──────────────────────────────────
    const trendData = @json($encounterTrend);
    if (trendData.length > 0) {
        new Chart(document.getElementById('encounterTrendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Encounters',
                    data: trendData.map(d => d.count),
                    borderColor: '#016634',
                    backgroundColor: 'rgba(1,102,52,0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#016634',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return 'Date: ' + context[0].label;
                            },
                            label: function(context) {
                                return 'Encounters: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 } }
                },
                onClick: function(event, elements) {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const date = trendData[index].date;
                        // Update baseParams with specific date
                        const params = {...baseParams, type: 'encounters', date_from: date, date_to: date};
                        openDrilldown('encounters', '', '');
                    }
                }
            }
        });
    }

    
    // ── Dispensation Trend Chart ───────────────────────────────
    const dispData = @json($dispensationTrend);
    if (dispData.length > 0) {
        new Chart(document.getElementById('dispensationTrendChart'), {
            type: 'bar',
            data: {
                labels: dispData.map(d => d.date),
                datasets: [
                    {
                        label: 'Units Dispensed',
                        data: dispData.map(d => d.qty),
                        backgroundColor: 'rgba(139,92,246,0.6)',
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Cost (₦)',
                        data: dispData.map(d => d.cost),
                        type: 'line',
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { font: { size: 10 }, usePointStyle: true } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, position: 'left', ticks: { font: { size: 10 }, precision: 0 }, title: { display: true, text: 'Units', font: { size: 10 } } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 10 } }, title: { display: true, text: 'Cost (₦)', font: { size: 10 } } }
                }
            }
        });
    }

    // ── Drilldown Handler ──────────────────────────────────────
    const drilldownUrl = @json(route('reports.ehr.drilldown'));
    const baseParams = {
        facility_id: @json($facilityId ?? ''),
        program_id: @json($programId ?? ''),
        date_from: @json($dateFrom),
        date_to: @json($dateTo),
    };
    const modal = new bootstrap.Modal(document.getElementById('drilldownModal'));
    let lastDrilldownParams = null;
    let lastDrilldownData = null;
    let drillState = { type: null, status: null, extra: null, page: 1 };

    window.drillPage = function(p) { openDrilldown(drillState.type, drillState.status, drillState.extra, p); };

    function openDrilldown(type, status, extra, page) {
        if (!type) return;
        drillState = { type, status, extra, page: page || 1 };
        document.getElementById('drilldownLoading').classList.remove('d-none');
        document.getElementById('drilldownContent').classList.add('d-none');
        document.getElementById('drilldownEmpty').classList.add('d-none');
        document.getElementById('drilldownExportBtn').classList.add('d-none');
        document.getElementById('drilldownTitle').innerHTML = '<i class="fas fa-list me-2"></i>Loading...';
        modal.show();

        const params = new URLSearchParams({...baseParams, type: type, per_page: 50, page: drillState.page});
        if (status) params.set('status', status);
        if (extra) {
            // Route extra param to the correct query param based on drill type
            if (type === 'encounters_by_facility' || type.indexOf('_by_facility') > -1 || type === 'awaiting_lab' || type === 'awaiting_pharmacy' || (type === 'encounters_by_status' && !isNaN(extra))) {
                params.set('facility_id', extra);
            } else if (type === 'encounters' && !isNaN(extra)) {
                params.set('program_id', extra);
            } else {
                params.set('extra', extra);
            }
        }

        lastDrilldownParams = params.toString();

        fetch(drilldownUrl + '?' + params.toString(), {
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
        })
        .then(r => r.json())
        .then(data => {
            lastDrilldownData = data;
            document.getElementById('drilldownLoading').classList.add('d-none');
            document.getElementById('drilldownTitle').innerHTML = '<i class="fas fa-table me-2"></i>' + (data.title || 'Details');

            if (!data.rows || data.rows.length === 0) {
                document.getElementById('drilldownEmpty').classList.remove('d-none');
                return;
            }

            let thead = '<tr>';
            (data.columns || []).forEach(col => { thead += '<th class="small fw-semibold px-2 py-1">' + col + '</th>'; });
            thead += '</tr>';
            document.getElementById('drilldownHead').innerHTML = thead;

            let tbody = '';
            data.rows.forEach(row => {
                tbody += '<tr>';
                row.forEach(cell => { tbody += '<td class="small px-2 py-1">' + (cell !== null && cell !== undefined ? cell : '—') + '</td>'; });
                tbody += '</tr>';
            });
            document.getElementById('drilldownBody').innerHTML = tbody;
            // Build pagination footer
            var pg = data.page || 1, lp = data.last_page || 1, tot = data.total || 0;
            var fr = ((pg-1)*(data.per_page||50))+1, to = fr+data.rows.length-1;
            var fh = '<div class="d-flex justify-content-between align-items-center w-100"><span>Showing '+fr+'–'+to+' of '+tot.toLocaleString()+'</span>';
            if(lp>1){fh+='<div class="btn-group btn-group-sm">';
            fh+='<button class="btn btn-outline-secondary'+(pg<=1?' disabled':'')+'" onclick="drillPage(1)">&laquo;</button>';
            fh+='<button class="btn btn-outline-secondary'+(pg<=1?' disabled':'')+'" onclick="drillPage('+(pg-1)+')">&lsaquo;</button>';
            fh+='<span class="btn btn-light disabled">'+pg+' / '+lp+'</span>';
            fh+='<button class="btn btn-outline-secondary'+(pg>=lp?' disabled':'')+'" onclick="drillPage('+(pg+1)+')">&rsaquo;</button>';
            fh+='<button class="btn btn-outline-secondary'+(pg>=lp?' disabled':'')+'" onclick="drillPage('+lp+')">&raquo;</button>';
            fh+='</div>';}fh+='</div>';
            document.getElementById('drilldownFooter').innerHTML = fh;
            document.getElementById('drilldownContent').classList.remove('d-none');
            document.getElementById('drilldownExportBtn').classList.remove('d-none');
        })
        .catch(err => {
            document.getElementById('drilldownLoading').classList.add('d-none');
            document.getElementById('drilldownEmpty').classList.remove('d-none');
            document.getElementById('drilldownEmpty').innerHTML = '<i class="fas fa-exclamation-triangle text-danger" style="font-size:2rem"></i><div class="text-muted mt-2">Error loading data</div>';
            console.error('Drilldown error:', err);
        });
    }

    window.exportDrilldown = function() {
        if (!drillState.type) return;
        var btn = document.getElementById('drilldownExportBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';

        // Build params identical to openDrilldown but with per_page=10000 and page=1 to get all records
        var params = new URLSearchParams({...baseParams, type: drillState.type, per_page: 10000, page: 1});
        if (drillState.status) params.set('status', drillState.status);
        if (drillState.extra) {
            var t = drillState.type;
            if (t === 'encounters_by_facility' || t.indexOf('_by_facility') > -1 || t === 'awaiting_lab' || t === 'awaiting_pharmacy' || (t === 'encounters_by_status' && !isNaN(drillState.extra))) {
                params.set('facility_id', drillState.extra);
            } else if (t === 'encounters' && !isNaN(drillState.extra)) {
                params.set('program_id', drillState.extra);
            } else {
                params.set('extra', drillState.extra);
            }
        }

        fetch(drilldownUrl + '?' + params.toString(), {
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
        })
        .then(r => r.json())
        .then(data => {
            var cols = data.columns || [];
            var rows = data.rows || [];
            if (rows.length === 0) { alert('No data to export'); return; }
            var csv = '\uFEFF' + cols.join(',') + '\n';
            rows.forEach(function(row) {
                csv += row.map(function(cell) {
                    var val = (cell !== null && cell !== undefined) ? String(cell) : '';
                    if (val.indexOf(',') > -1 || val.indexOf('"') > -1 || val.indexOf('\n') > -1) {
                        val = '"' + val.replace(/"/g, '""') + '"';
                    }
                    return val;
                }).join(',') + '\n';
            });
            var blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = (data.title || 'drilldown').replace(/[^a-z0-9]/gi, '_') + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        })
        .catch(function(err) { console.error('Export error:', err); alert('Export failed'); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-excel me-1"></i>Export';
        });
    };

    // KPI cards click
    document.querySelectorAll('.drill-card').forEach(card => {
        card.addEventListener('click', function() {
            openDrilldown(this.dataset.drillType, '', '');
        });
    });

    // Inline drill links click
    document.querySelectorAll('.drill-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openDrilldown(this.dataset.drillType, this.dataset.drillStatus || '', this.dataset.drillExtra || '');
        });
    });
});
</script>
@endpush

@push('styles')
<style>
/* Light background utilities */
.bg-primary-lt { background: rgba(13,110,253,.1) !important; }
.bg-success-lt, .bg-green-lt { background: rgba(25,135,84,.1) !important; color: #198754 !important; }
.bg-danger-lt, .bg-red-lt { background: rgba(220,53,69,.1) !important; color: #dc3545 !important; }
.bg-warning-lt, .bg-orange-lt { background: rgba(255,193,7,.12) !important; color: #cc8400 !important; }
.bg-info-lt { background: rgba(13,202,240,.1) !important; }
.bg-secondary-lt { background: rgba(108,117,125,.1) !important; color: #6c757d !important; }
.bg-purple-lt { background: rgba(139,92,246,.1) !important; color: #7c3aed !important; }
.bg-cyan-lt { background: rgba(6,182,212,.1) !important; color: #0891b2 !important; }
.bg-indigo-lt { background: rgba(99,102,241,.1) !important; color: #4f46e5 !important; }
.bg-teal-lt { background: rgba(20,184,166,.1) !important; color: #0d9488 !important; }
.bg-blue-lt { background: rgba(59,130,246,.1) !important; color: #2563eb !important; }
.text-purple { color: #7c3aed !important; }
.text-cyan { color: #0891b2 !important; }
.text-teal { color: #0d9488 !important; }
.text-indigo { color: #4f46e5 !important; }
.btn-outline-purple { border-color: #7c3aed; color: #7c3aed; }
.btn-outline-purple:hover { background: #7c3aed; color: #fff; }
.btn-outline-cyan { border-color: #0891b2; color: #0891b2; }
.btn-outline-cyan:hover { background: #0891b2; color: #fff; }
.btn-outline-teal { border-color: #0d9488; color: #0d9488; }
.btn-outline-teal:hover { background: #0d9488; color: #fff; }
/* Avatar utilities */
.avatar { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; }
.avatar-md { width: 48px; height: 48px; }
/* Page layout */
.page-pretitle { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; font-weight: 600; }
.page-title { font-size: 1.5rem; font-weight: 700; }
.page-header { padding: 1.5rem 0 1rem; }
.page-body { padding: 0; }
/* Card enhancements */
.card-sm .card-body { padding: 1rem; }
.card-title { font-size: .95rem; font-weight: 600; }
/* Nav tabs */
.nav-tabs .nav-link { font-size: 13px; font-weight: 600; }
/* Drilldown clickable elements */
.drill-card { transition: transform .15s, box-shadow .15s; }
.drill-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,.12) !important; }
.drill-link { text-decoration: none; cursor: pointer; border-bottom: 1px dashed currentColor; }
.drill-link:hover { opacity: .75; }
a.badge.drill-link { border-bottom: none; }
a.badge.drill-link:hover { filter: brightness(0.85); opacity: 1; }
</style>
@endpush
