@extends('layouts.app')

@section('title', 'Referrals Management')

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">Referrals Management</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
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
                                <div class="h1 mb-3" id="stat-total">{{ $stats['total'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>All system referrals</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Accepted</div>
                                </div>
                                <div class="h1 mb-3" id="stat-accepted">{{ $stats['accepted'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>✅ Referrals accepted</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Completed</div>
                                </div>
                                <div class="h1 mb-3" id="stat-completed">{{ $stats['completed'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>🎉 Successfully completed</div>
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
                                <div class="h1 mb-3" id="stat-pending">{{ $stats['pending'] ?? 0 }}</div>
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
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="completed">Completed</option>
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
                                    <div class="col-md-2 mb-3">
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
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small text-muted mb-1">From Facility</label>
                                        <select id="filter_from_facility" class="form-select">
                                            <option value="">All Facilities</option>
                                            @if(isset($facilities))
                                                @foreach($facilities as $facility)
                                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small text-muted mb-1">To Facility</label>
                                        <select id="filter_to_facility" class="form-select">
                                            <option value="">All Facilities</option>
                                            @if(isset($facilities))
                                                @foreach($facilities as $facility)
                                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small text-muted mb-1">Start Date</label>
                                        <input type="date" id="filter_start_date" class="form-control">
                                    </div>
                                    <div class="col-md-3 mb-3">
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
                                                <th>Approved By</th>
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
                $('#referralsTable').on('xhr.dt', function (e, settings, json, xhr) {
                    if (json && json.stats) {
                        $('#stat-total').text(json.stats.total);
                        $('#stat-accepted').text(json.stats.accepted);
                        $('#stat-completed').text(json.stats.completed);
                        $('#stat-pending').text(json.stats.pending);
                    }
                }).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('referrals.index') }}',
                        type: 'GET',
                        data: function (d) {
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
                            data: 'approved_by',
                            name: 'approved_by',
                            orderable: false,
                            searchable: false
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
                        [6, 'desc']
                    ],
                    pageLength: 25,
                    language: {
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                    }
                });

                $('#btn_filter').click(function() {
                    $('#referralsTable').DataTable().ajax.reload();
                });

                // Auto-filter when changing dropdowns or dates
                $('#filter_program, #filter_status, #filter_type, #filter_from_facility, #filter_to_facility, #filter_start_date, #filter_end_date').change(function() {
                    $('#referralsTable').DataTable().ajax.reload();
                });

                // Debounced search
                var searchTimer;
                $('#filter_search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        $('#referralsTable').DataTable().ajax.reload();
                    }, 400);
                });

                $('#btn_reset').click(function() {
                    $('#filter_search, #filter_program, #filter_status, #filter_type, #filter_from_facility, #filter_to_facility, #filter_start_date, #filter_end_date').val('');
                    $('#referralsTable').DataTable().ajax.reload();
                });
            });

            function showRejectModal(id) {
                var form = document.getElementById('rejectForm');
                form.action = '/referrals/' + id + '/reject';
                var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
                modal.show();
            }

            function showApproveModal(id) {
                var form = document.getElementById('approveForm');
                form.action = '/referrals/' + id + '/approve';
                var modal = new bootstrap.Modal(document.getElementById('approveModal'));
                modal.show();
            }
        </script>
        
        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Referral</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveModalLabel">Approve Referral</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">Are you sure you want to approve this referral?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Confirm Approval</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endsection
