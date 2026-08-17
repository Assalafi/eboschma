@extends('layouts.facility')

@section('title', 'Referrals Management')

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">Referrals Management</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Referrals</li>
                        </ol>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Total Referrals</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['total'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>All referrals (in/out)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Outgoing</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['outgoing'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>📤 Referred to other facilities</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Incoming</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['incoming'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>📥 Referred to this facility</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Pending</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['pending'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>Awaiting action</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrals Table -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">All Referrals</h3>
                            </div>
                            <div class="card-body">
                                <!-- Filter Section -->
                                <div class="row mb-4">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small text-muted mb-1">Search</label>
                                        <input type="text" id="filter_search" class="form-control"
                                            placeholder="Auth code, patient, enrollee...">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small text-muted mb-1">Status</label>
                                        <select id="filter_status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="pending">Pending</option>
                                            <option value="accepted">Accepted</option>
                                            <option value="completed">Completed</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small text-muted mb-1">Direction</label>
                                        <select id="filter_direction" class="form-select">
                                            <option value="">All Directions</option>
                                            <option value="outgoing">Outgoing</option>
                                            <option value="incoming">Incoming</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small text-muted mb-1">Type</label>
                                        <select id="filter_type" class="form-select">
                                            <option value="">All Types</option>
                                            <option value="service">Service</option>
                                            <option value="patient">Patient</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small text-muted mb-1">Program</label>
                                        <select id="filter_program" class="form-select">
                                            <option value="">All Programs</option>
                                            @if(isset($programs))
                                                @foreach($programs as $program)
                                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small text-muted mb-1">Start Date</label>
                                        <input type="date" id="filter_start_date" class="form-control">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small text-muted mb-1">End Date</label>
                                        <input type="date" id="filter_end_date" class="form-control">
                                    </div>
                                    <div class="col-md-3 mb-3 d-flex align-items-end gap-2">
                                        <button type="button" id="btn_filter" class="btn btn-primary"><i class="ti-filter"></i> Filter</button>
                                        <button type="button" id="btn_reset" class="btn btn-light"><i class="ti-reload"></i> Reset</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="referralsTable" class="table table-bordered table-hover text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Auth code</th>
                                                <th>Patient</th>
                                                <th>Facility</th>
                                                <th>Service</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                var table = $('#referralsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ajax: {
                        url: '{{ route('facility.referrals.index') }}',
                        type: 'GET',
                        data: function (d) {
                            d.search = $('#filter_search').val();
                            d.status = $('#filter_status').val();
                            d.direction = $('#filter_direction').val();
                            d.referral_type = $('#filter_type').val();
                            d.program_id = $('#filter_program').val();
                            d.start_date = $('#filter_start_date').val();
                            d.end_date = $('#filter_end_date').val();
                        }
                    },
                    columns: [{
                            data: 'referral_info',
                            name: 'id'
                        },
                        {
                            data: 'patient_info',
                            name: 'encounter.patient.firstname',
                            orderable: false
                        },
                        {
                            data: 'facility_info',
                            name: 'from_facility_id',
                            orderable: false
                        },
                        {
                            data: 'reason',
                            name: 'reason'
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'date',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [5, 'desc']
                    ],
                    pageLength: 25,
                    language: {
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                    }
                });

                $('#btn_filter').click(function() {
                    table.ajax.reload();
                });

                $('#btn_reset').click(function() {
                    $('#filter_search, #filter_status, #filter_direction, #filter_type, #filter_program, #filter_start_date, #filter_end_date').val('');
                    table.ajax.reload();
                });

                // Live filter on select/date change
                $('#filter_status, #filter_direction, #filter_type, #filter_program, #filter_start_date, #filter_end_date').on('change', function() {
                    table.ajax.reload();
                });

                // Debounced search
                var searchTimer;
                $('#filter_search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() { table.ajax.reload(); }, 400);
                });
            });
        </script>
    @endpush
@endsection
