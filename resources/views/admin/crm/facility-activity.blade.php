@extends('layouts.app')

@section('title', 'Facility Activity Overview')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti-pulse text-warning me-2"></i> Facility Activity Overview</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('crm.index') }}" class="btn btn-secondary">
                            <i class="ti-arrow-left"></i> Back to CRM
                        </a>
                        <button onclick="location.reload()" class="btn btn-primary">
                            <i class="ti-reload"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
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
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="ti-info-alt me-2"></i> No facility activity found.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drilldown Modal -->
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Drilldown Handler ──────────────────────────────────────
    const drilldownUrl = @json(route('reports.ehr.drilldown'));
    const baseParams = {
        date_from: '{{ \Carbon\Carbon::now()->subDays(15)->toDateString() }}',
        date_to: '{{ \Carbon\Carbon::today()->toDateString() }}',
        @if(auth()->user()->role && auth()->user()->role->name === 'Customer Care')
        facility_id: '{{ auth()->user()->facility_id }}',
        @endif
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
</style>
@endpush
