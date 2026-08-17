@extends('layouts.facility')

@section('title', 'Facility Claims')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Claims Management</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Claims</li>
                </ol>
            </div>
            <div class="page-rightheader ml-auto">
                <a href="{{ route('facility.claims.billable') }}" class="btn btn-success">
                    <i class="ti-plus mr-1"></i> Create New Claim
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h2>
                        <div class="text-muted">Total Claims</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-0 text-warning">{{ number_format($stats['submitted'] ?? 0) }}</h2>
                        <div class="text-muted">Pending / In Review</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-0 text-success">{{ number_format($stats['approved'] ?? 0) }}</h2>
                        <div class="text-muted">Approved</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-0 text-success">{{ number_format($stats['paid'] ?? 0) }}</h2>
                        <div class="text-muted">Paid</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-0 text-danger">{{ number_format($stats['rejected'] ?? 0) }}</h2>
                        <div class="text-muted">Rejected</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-0 text-primary">₦{{ number_format($stats['total_amount'] ?? 0, 2) }}</h2>
                        <div class="text-muted">Total Value</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Claims</h3>
                    </div>
                    <div class="card-body">
                        <!-- Rich Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3 mb-2">
                                <label class="form-label small text-muted mb-1">Search</label>
                                <input type="text" id="searchFilter" class="form-control form-control-sm"
                                    placeholder="Claim #, patient, enrollee...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select id="statusFilter" class="form-control form-control-sm">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="verified">Verified</option>
                                    <option value="approved">Approved</option>
                                    <option value="es_approved">ES Approved</option>
                                    <option value="paid">Paid</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small text-muted mb-1">Claim Type</label>
                                <select id="claimTypeFilter" class="form-control form-control-sm">
                                    <option value="">All Types</option>
                                    <option value="outpatient">Outpatient</option>
                                    <option value="inpatient">Inpatient</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="referral">Referral</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small text-muted mb-1">From</label>
                                <input type="date" id="dateFromFilter" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small text-muted mb-1">To</label>
                                <input type="date" id="dateToFilter" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                    onclick="resetFilters()"><i class="ti-reload"></i> Reset</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="claimsTable" class="table table-bordered table-hover text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Claim Info</th>
                                        <th>Encounter Info</th>
                                        <th>Amounts</th>
                                        <th>Status</th>
                                        <th>Created</th>
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
    @push('scripts')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                var table = $('#claimsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ajax: {
                        url: '{{ route('facility.claims.list') }}',
                        type: 'GET',
                        data: function(d) {
                            d.search = $('#searchFilter').val();
                            d.status = $('#statusFilter').val();
                            d.claim_type = $('#claimTypeFilter').val();
                            d.date_from = $('#dateFromFilter').val();
                            d.date_to = $('#dateToFilter').val();
                        }
                    },
                    columns: [{
                            data: 'claim_info',
                            name: 'claim_number'
                        },
                        {
                            data: 'encounter_info',
                            name: 'service_date'
                        },
                        {
                            data: 'amounts',
                            name: 'total_amount',
                            orderable: false
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
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
                        [4, 'desc']
                    ],
                    pageLength: 25,
                    language: {
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                    }
                });

                function reload() { table.ajax.reload(null, false); }

                $('#statusFilter, #claimTypeFilter, #dateFromFilter, #dateToFilter').on('change', reload);

                var searchTimer;
                $('#searchFilter').on('keyup', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(reload, 400);
                });

                window.resetFilters = function() {
                    $('#searchFilter, #statusFilter, #claimTypeFilter, #dateFromFilter, #dateToFilter').val('');
                    reload();
                };
            });

            function deleteClaim(id) {
                if (confirm('Are you sure you want to delete this claim? This action cannot be undone.')) {
                    $.ajax({
                        url: '/facility/claims/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert(response.success);
                            $('#claimsTable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON.error || 'Error deleting claim');
                        }
                    });
                }
            }
        </script>
    @endpush
@endsection
