@extends('layouts.app')

@section('title', 'Facility Staff')

@section('content')
    <div class="container-fluid ref-wrap">

        {{-- PAGE HEAD --}}
        <div class="ref-pagehead d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <div class="ref-eyebrow">
                    <a href="{{ route('dashboard') }}">Dashboard</a><span>/</span><span>Facility Staff</span>
                </div>
                <h1 class="ref-title">Facility Staff</h1>
                <p class="ref-subtitle">Manage staff members across all facilities and control account access</p>
            </div>
            <div>
                <a href="{{ route('facility-staff.create') }}" class="btn btn-primary">
                    <i class="fe fe-user-plus me-1"></i> Add Staff
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- STATS --}}
        <div class="ref-stats">
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-primary"><i class="fe fe-users"></i></div>
                <div>
                    <div class="ref-stat-label">Total Staff</div>
                    <div class="ref-stat-number" id="st-total">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="ref-stat-note">All facility staff</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-success"><i class="fe fe-user-check"></i></div>
                <div>
                    <div class="ref-stat-label">Active</div>
                    <div class="ref-stat-number" id="st-active">{{ number_format($stats['active'] ?? 0) }}</div>
                    <div class="ref-stat-note">Can sign in</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-danger"><i class="fe fe-user-x"></i></div>
                <div>
                    <div class="ref-stat-label">Inactive</div>
                    <div class="ref-stat-number" id="st-inactive">{{ number_format($stats['inactive'] ?? 0) }}</div>
                    <div class="ref-stat-note">Access disabled</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-info"><i class="fe fe-camera"></i></div>
                <div>
                    <div class="ref-stat-label">With Photo</div>
                    <div class="ref-stat-number" id="st-photo">{{ number_format($stats['with_photo'] ?? 0) }}</div>
                    <div class="ref-stat-note">Passport uploaded</div>
                </div>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="ref-panel">
            <div class="ref-panel-head">
                <h3><i class="fe fe-filter"></i> Filters</h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleRefFilters()">
                    <i class="fe fe-sliders"></i> <span id="refFilterToggleText">Hide</span>
                </button>
            </div>
            <div class="ref-panel-body" id="refFiltersBody">
                <div class="row g-3">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label ref-field-label">Search</label>
                        <div class="ref-input-icon">
                            <i class="fe fe-search"></i>
                            <input type="text" id="filter_search" class="form-control"
                                placeholder="Name, email or phone...">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label ref-field-label">Facility</label>
                        <select class="form-select" id="facilityFilter">
                            <option value="">All Facilities</option>
                            @foreach ($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">Position</label>
                        <select class="form-select" id="positionFilter">
                            <option value="">All Positions</option>
                            @foreach ($staffPositions as $position)
                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label ref-field-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2 d-flex align-items-end gap-2">
                        <button type="button" id="btn_filter" class="btn btn-primary flex-fill">
                            <i class="fe fe-filter"></i> Apply
                        </button>
                        <button type="button" id="btn_reset" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="fe fe-rotate-ccw"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="ref-panel ref-table-panel">
            <div class="ref-panel-head">
                <h3><i class="fe fe-list"></i> All Facility Staff</h3>
                <span class="ref-count">Live</span>
            </div>
            <div class="ref-panel-body p-0">
                <div class="table-responsive">
                    <table id="facilityStaffTable" class="table ref-dt w-100">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Facility</th>
                                <th>Role</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleRefFilters() {
            var body = document.getElementById('refFiltersBody');
            var text = document.getElementById('refFilterToggleText');
            if (!body) return;
            body.classList.toggle('d-none');
            text.textContent = body.classList.contains('d-none') ? 'Show' : 'Hide';
        }

        $(document).ready(function() {
            var table = $('#facilityStaffTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('facility-staff.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.facility_id = $('#facilityFilter').val();
                        d.staff_position_id = $('#positionFilter').val();
                        d.is_active = $('#statusFilter').val();
                        d.search = $('#filter_search').val();
                    },
                    error: function(xhr) {
                        console.log('DataTables Ajax Error:', xhr.responseText);
                    }
                },
                columns: [{
                        data: 'passport',
                        name: 'passport',
                        orderable: false,
                        className: 'text-center'
                    },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: function(data) {
                            return data ? data : '<span class="text-muted">-</span>';
                        }
                    },
                    { data: 'facility_name', name: 'facility_name' },
                    { data: 'role_name', name: 'role_name' },
                    { data: 'position_name', name: 'position_name' },
                    { data: 'status_badge', name: 'is_active', orderable: false },
                    { data: 'created_at_formatted', name: 'created_at' },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        className: 'text-center'
                    }
                ],
                order: [[1, 'asc']],
                pageLength: 25,
                responsive: true,
                language: {
                    emptyTable: 'No facility staff found',
                    zeroRecords: 'No matching staff found'
                }
            });

            $('#btn_filter').on('click', function() {
                table.ajax.reload();
            });

            $('#btn_reset').on('click', function() {
                $('#filter_search').val('');
                $('#facilityFilter').val('');
                $('#positionFilter').val('');
                $('#statusFilter').val('');
                table.ajax.reload();
            });

            $('#filter_search').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.ajax.reload();
                }
            });

            $('#facilityFilter, #positionFilter, #statusFilter').on('change', function() {
                table.ajax.reload();
            });

            // Activate / deactivate
            $(document).on('click', '.js-toggle-status', function() {
                var btn = $(this);
                var url = btn.data('url');
                var wasActive = btn.data('active') == 1;
                var text = wasActive ? 'Deactivate this staff member?' : 'Activate this staff member?';

                if (!confirm(text)) return;

                btn.prop('disabled', true);
                $.ajax({
                    url: url,
                    method: 'PATCH',
                    data: { _token: '{{ csrf_token() }}' },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        if (res.success) {
                            table.ajax.reload(null, false);
                            if (res.stats) {
                                $('#st-total').text(Number(res.stats.total).toLocaleString());
                                $('#st-active').text(Number(res.stats.active).toLocaleString());
                                $('#st-inactive').text(Number(res.stats.inactive).toLocaleString());
                                $('#st-photo').text(Number(res.stats.with_photo).toLocaleString());
                            }
                        }
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Something went wrong. Please try again.';
                        alert(msg);
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
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

        .ref-pagehead { margin-bottom: 18px; }
        .ref-eyebrow { font-size: 12px; letter-spacing: .05em; text-transform: uppercase; color: var(--ref-muted); font-weight: 600; margin-bottom: 6px; }
        .ref-eyebrow a { color: var(--ref-primary); text-decoration: none; }
        .ref-eyebrow span { margin: 0 6px; opacity: .6; }
        .ref-title { font-size: 24px; font-weight: 700; color: var(--ref-ink); margin: 0 0 4px; }
        .ref-subtitle { color: var(--ref-muted); font-size: 13.5px; margin: 0; }

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
        .ref-stat-icon.is-danger { background: rgba(239,68,68,.12); color: #dc2626; }
        .ref-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--ref-muted); font-weight: 700; }
        .ref-stat-number { font-size: 24px; font-weight: 700; color: var(--ref-ink); line-height: 1.15; }
        .ref-stat-note { font-size: 12px; color: var(--ref-muted); }

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

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { padding: 12px 16px; font-size: 13px; color: var(--ref-muted); }
        .dataTables_wrapper .dataTables_length select { border: 1px solid var(--ref-border); border-radius: 8px; }
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

        .st-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 700; letter-spacing: .03em;
            padding: 4px 10px; border-radius: 999px; white-space: nowrap;
        }
        .st-badge .st-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .st-badge.is-active { background: rgba(16,185,129,.12); color: #047857; }
        .st-badge.is-active .st-dot { background: #10b981; }
        .st-badge.is-inactive { background: rgba(239,68,68,.10); color: #b91c1c; }
        .st-badge.is-inactive .st-dot { background: #ef4444; }

        .st-actions { display: inline-flex; align-items: center; gap: 6px; }
        .st-actions .btn { display: inline-flex; align-items: center; justify-content: center; }
        .st-actions .btn-outline-primary { color: var(--ref-primary); border-color: rgba(1,102,52,.35); }
        .st-actions .btn-outline-primary:hover { background: var(--ref-primary); border-color: var(--ref-primary); color: #fff; }
        .st-actions .btn-outline-success { color: #059669; border-color: rgba(16,185,129,.4); }
        .st-actions .btn-outline-success:hover { background: #059669; border-color: #059669; color: #fff; }
        .st-actions .btn-outline-warning { color: #b45309; border-color: rgba(245,158,11,.45); }
        .st-actions .btn-outline-warning:hover { background: #f59e0b; border-color: #f59e0b; color: #fff; }
        .st-actions .btn-outline-danger { color: #dc2626; border-color: rgba(239,68,68,.4); }
        .st-actions .btn-outline-danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }

        .btn-primary { background-color: var(--ref-primary); border-color: var(--ref-primary); }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--ref-primary-dark); border-color: var(--ref-primary-dark); }

        @media (max-width: 640px) {
            .ref-panel-body { padding: 14px; }
            .ref-dt thead th, .ref-dt tbody td { padding: 10px 12px !important; }
        }
    </style>
@endpush
