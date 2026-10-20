@extends('layouts.app')

@section('title', 'Referrals Management')

@section('content')
    <div class="container-fluid ref-wrap">

        {{-- PAGE HEAD --}}
        <div class="ref-pagehead">
            <div>
                <div class="ref-eyebrow">
                    <a href="{{ route('dashboard') }}">Dashboard</a><span>/</span><span>Referrals</span>
                </div>
                <h1 class="ref-title">Referrals Management</h1>
                <p class="ref-subtitle">Review, approve and track patient &amp; service referrals across facilities</p>
            </div>
        </div>

        {{-- STATS --}}
        <div class="ref-stats">
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-primary"><i class="fas fa-share-square"></i></div>
                <div>
                    <div class="ref-stat-label">Total Referrals</div>
                    <div class="ref-stat-number" id="stat-total">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="ref-stat-note">All system referrals</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-success"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="ref-stat-label">Accepted</div>
                    <div class="ref-stat-number" id="stat-accepted">{{ number_format($stats['accepted'] ?? 0) }}</div>
                    <div class="ref-stat-note">Approved referrals</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-info"><i class="fas fa-flag-checkered"></i></div>
                <div>
                    <div class="ref-stat-label">Completed</div>
                    <div class="ref-stat-number" id="stat-completed">{{ number_format($stats['completed'] ?? 0) }}</div>
                    <div class="ref-stat-note">Successfully completed</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-warning"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="ref-stat-label">Pending</div>
                    <div class="ref-stat-number" id="stat-pending">{{ number_format($stats['pending'] ?? 0) }}</div>
                    <div class="ref-stat-note">Awaiting action</div>
                </div>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="ref-panel">
            <div class="ref-panel-head">
                <h3><i class="fas fa-filter"></i> Filters</h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleRefFilters()">
                    <i class="fas fa-sliders-h"></i> <span id="refFilterToggleText">Hide</span>
                </button>
            </div>
            <div class="ref-panel-body" id="refFiltersBody">
                <div class="row g-3">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label ref-field-label">Search</label>
                        <div class="ref-input-icon">
                            <i class="fas fa-search"></i>
                            <input type="text" id="filter_search" class="form-control"
                                placeholder="Auth code, patient, enrollee...">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">Status</label>
                        <select id="filter_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">Type</label>
                        <select id="filter_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="service">Service</option>
                            <option value="patient">Patient</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label ref-field-label">Program</label>
                        <select id="filter_program" class="form-select">
                            <option value="">All Programs</option>
                            @if (isset($programs))
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">From Facility</label>
                        <select id="filter_from_facility" class="form-select">
                            <option value="">All Facilities</option>
                            @if (isset($facilities))
                                @foreach ($facilities as $facility)
                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">To Facility</label>
                        <select id="filter_to_facility" class="form-select">
                            <option value="">All Facilities</option>
                            @if (isset($facilities))
                                @foreach ($facilities as $facility)
                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">Start Date</label>
                        <input type="date" id="filter_start_date" class="form-control">
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">End Date</label>
                        <input type="date" id="filter_end_date" class="form-control">
                    </div>
                    <div class="col-md-4 col-lg-2 d-flex align-items-end gap-2">
                        <button type="button" id="btn_filter" class="btn btn-primary flex-fill">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <button type="button" id="btn_reset" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="fas fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="ref-panel ref-table-panel">
            <div class="ref-panel-head">
                <h3><i class="fas fa-list-ul"></i> All Referrals</h3>
                <span class="ref-count">Live</span>
            </div>
            <div class="ref-panel-body p-0">
                <div class="table-responsive">
                    <table id="referralsTable" class="table ref-dt mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Auth Code</th>
                                <th>Patient</th>
                                <th>Facility</th>
                                <th>Status</th>
                                <th>Approved By</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

        <script>
            function toggleRefFilters() {
                var body = document.getElementById('refFiltersBody');
                if (!body) return;
                var hidden = body.style.display === 'none';
                body.style.display = hidden ? '' : 'none';
                var t = document.getElementById('refFilterToggleText');
                if (t) t.textContent = hidden ? 'Hide' : 'Show';
            }

            $(document).ready(function() {
                var table = $('#referralsTable').on('xhr.dt', function(e, settings, json) {
                    if (json && json.stats) {
                        $('#stat-total').text(Number(json.stats.total || 0).toLocaleString());
                        $('#stat-accepted').text(Number(json.stats.accepted || 0).toLocaleString());
                        $('#stat-completed').text(Number(json.stats.completed || 0).toLocaleString());
                        $('#stat-pending').text(Number(json.stats.pending || 0).toLocaleString());
                    }
                }).DataTable({
                    processing: true,
                    serverSide: true,
                    // Preserve current page / filters across reloads (e.g. after approve/reject redirect)
                    stateSave: true,
                    ajax: {
                        url: '{{ route('referrals.index') }}',
                        type: 'GET',
                        data: function(d) {
                            d.search = $('#filter_search').val();
                            d.program_id = $('#filter_program').val();
                            d.status = $('#filter_status').val();
                            d.referral_type = $('#filter_type').val();
                            d.from_facility_id = $('#filter_from_facility').val();
                            d.to_facility_id = $('#filter_to_facility').val();
                            d.start_date = $('#filter_start_date').val();
                            d.end_date = $('#filter_end_date').val();
                        }
                    },
                    columns: [
                        { data: 'referral_info', name: 'id' },
                        { data: 'patient_info', name: 'encounter.patient.firstname', orderable: false },
                        { data: 'facility_info', name: 'from_facility_id', orderable: false },
                        { data: 'status_badge', name: 'status' },
                        { data: 'approved_by', name: 'approved_by', orderable: false, searchable: false },
                        { data: 'date', name: 'created_at' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                    ],
                    order: [[5, 'desc']],
                    pageLength: 25,
                    language: {
                        processing: '<span class="ref-dt-loading"><span class="spinner-border spinner-border-sm text-success"></span> Loading…</span>',
                        emptyTable: 'No referrals found',
                        zeroRecords: 'No matching referrals'
                    }
                });

                $('#btn_filter').click(function() {
                    table.ajax.reload();
                });

                $('#filter_program, #filter_status, #filter_type, #filter_from_facility, #filter_to_facility, #filter_start_date, #filter_end_date').change(function() {
                    table.ajax.reload();
                });

                var searchTimer;
                $('#filter_search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        table.ajax.reload();
                    }, 400);
                });

                $('#btn_reset').click(function() {
                    $('#filter_search, #filter_program, #filter_status, #filter_type, #filter_from_facility, #filter_to_facility, #filter_start_date, #filter_end_date').val('');
                    try { table.state.clear(); } catch (e) {}
                    table.ajax.reload();
                });
            });

            // Submit a referral action via a dynamically created form
            function submitReferralAction(url, data) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                var token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);

                if (data) {
                    Object.keys(data).forEach(function(key) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = data[key];
                        form.appendChild(input);
                    });
                }

                document.body.appendChild(form);
                form.submit();
            }

            function showApproveModal(id) {
                swal({
                    title: 'Approve Referral?',
                    text: 'Are you sure you want to approve this referral?',
                    type: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#016634',
                    confirmButtonText: 'Yes, approve',
                    cancelButtonText: 'Cancel',
                    closeOnConfirm: true
                }, function(confirmed) {
                    if (confirmed) {
                        submitReferralAction('/referrals/' + id + '/approve');
                    }
                });
            }

            function showRejectModal(id) {
                var form = document.getElementById('rejectForm');
                if (form) form.action = '/referrals/' + id + '/reject';
                var ta = document.getElementById('rejectReasonInput');
                if (ta) ta.value = '';
                var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
                modal.show();
            }
        </script>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Referral</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="rejectReasonInput" class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejectReasonInput" class="form-control" rows="3"
                                placeholder="Explain why this referral is being rejected..." required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endpush

    @push('styles')
        <style>
            :root {
                --ref-primary: #016634;
                --ref-primary-dark: #014525;
                --ref-success: #10b981;
                --ref-warning: #f59e0b;
                --ref-info: #3b82f6;
                --ref-danger: #ef4444;
                --ref-border: #e2e8f0;
                --ref-muted: #64748b;
                --ref-ink: #0f172a;
            }

            .ref-wrap { padding-top: .25rem; }

            /* Page head */
            .ref-pagehead { margin-bottom: 18px; }
            .ref-eyebrow { font-size: 12px; letter-spacing: .05em; text-transform: uppercase; color: var(--ref-muted); font-weight: 600; margin-bottom: 6px; }
            .ref-eyebrow a { color: var(--ref-primary); text-decoration: none; }
            .ref-eyebrow span { margin: 0 6px; opacity: .6; }
            .ref-title { font-size: 24px; font-weight: 700; color: var(--ref-ink); margin: 0 0 4px; }
            .ref-subtitle { color: var(--ref-muted); font-size: 13.5px; margin: 0; }

            /* Stats */
            .ref-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; margin-bottom: 18px; }
            .ref-stat-card {
                background: #fff; border: 1px solid var(--ref-border); border-radius: 14px;
                padding: 16px 18px; display: flex; align-items: center; gap: 14px;
                box-shadow: 0 1px 3px rgba(15,23,42,.04); transition: transform .15s ease, box-shadow .15s ease;
            }
            .ref-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.08); }
            .ref-stat-icon {
                width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
                display: flex; align-items: center; justify-content: center; font-size: 18px;
            }
            .ref-stat-icon.is-primary { background: rgba(1,102,52,.10); color: var(--ref-primary); }
            .ref-stat-icon.is-success { background: rgba(16,185,129,.12); color: #059669; }
            .ref-stat-icon.is-info { background: rgba(59,130,246,.12); color: #2563eb; }
            .ref-stat-icon.is-warning { background: rgba(245,158,11,.14); color: #b45309; }
            .ref-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--ref-muted); font-weight: 700; }
            .ref-stat-number { font-size: 24px; font-weight: 700; color: var(--ref-ink); line-height: 1.15; }
            .ref-stat-note { font-size: 12px; color: var(--ref-muted); }

            /* Panels */
            .ref-panel { background: #fff; border: 1px solid var(--ref-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.04); margin-bottom: 18px; overflow: hidden; }
            .ref-panel-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--ref-border); background: #f8fafc; }
            .ref-panel-head h3 { margin: 0; font-size: 14px; font-weight: 700; color: var(--ref-ink); display: flex; align-items: center; gap: 8px; }
            .ref-panel-head h3 i { color: var(--ref-primary); }
            .ref-panel-body { padding: 18px; }
            .ref-count { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #059669; background: rgba(16,185,129,.12); padding: 3px 10px; border-radius: 999px; }

            .ref-field-label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--ref-muted); font-weight: 700; margin-bottom: 6px; }
            .ref-input-icon { position: relative; }
            .ref-input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; }
            .ref-input-icon .form-control { padding-left: 32px; }

            /* DataTable */
            .ref-table-panel .dataTables_wrapper { padding: 0 0 4px; }
            .ref-dt { border-collapse: collapse !important; font-size: 13px; }
            .ref-dt thead th {
                background: #f8fafc !important; color: var(--ref-muted) !important;
                text-transform: uppercase; letter-spacing: .04em; font-size: 11px; font-weight: 700;
                padding: 12px 16px !important; border-bottom: 1px solid var(--ref-border) !important;
                border-top: none !important; white-space: nowrap;
            }
            .ref-dt tbody td { padding: 13px 16px !important; border-bottom: 1px solid #f1f5f9 !important; vertical-align: middle; }
            .ref-dt tbody tr:hover td { background: #f8fafc !important; }
            .ref-dt tbody tr:last-child td { border-bottom: none !important; }
            .ref-dt-loading { display: inline-flex; align-items: center; gap: 8px; color: var(--ref-muted); font-size: 13px; }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate { padding: 12px 16px; font-size: 13px; color: var(--ref-muted); }
            .dataTables_wrapper .dataTables_length select,
            .dataTables_wrapper .dataTables_filter input { border: 1px solid var(--ref-border); border-radius: 8px; }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: var(--ref-primary) !important;
                border-color: var(--ref-primary) !important;
                color: #fff !important;
                border-radius: 8px;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: rgba(1,102,52,.08) !important;
                border-color: transparent !important;
                color: var(--ref-primary) !important;
                border-radius: 8px;
            }
            table.dataTable thead .sorting:before,
            table.dataTable thead .sorting_asc:before,
            table.dataTable thead .sorting_desc:before { opacity: .35; }

            .btn-primary { background-color: var(--ref-primary); border-color: var(--ref-primary); }
            .btn-primary:hover, .btn-primary:focus { background-color: var(--ref-primary-dark); border-color: var(--ref-primary-dark); }

            @media (max-width: 640px) {
                .ref-panel-body { padding: 14px; }
                .ref-dt thead th, .ref-dt tbody td { padding: 10px 12px !important; }
            }
        </style>
    @endpush
@endsection
