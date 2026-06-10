@extends('layouts.facility')

@section('title', 'Pharmacy Stock Requests')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="page-pretitle">
                        Pharmacy Management
                    </div>
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Stock Requests
                    </h2>
                    <div class="text-muted mt-1">
                        Track and manage drug stock requests for your facility
                    </div>
                </div>
                <div class="col-md-4 d-print-none">
                    <div class="d-flex justify-content-end gap-2">
                        @if($hasWallet ?? false)
                        <a href="{{ route('facility.pharmacy.stock-requests.bulk') }}" class="btn btn-success">
                            <i class="ti-package me-1"></i>Quick Bulk Request
                        </a>
                        <a href="{{ route('facility.pharmacy.stock-requests.create') }}" class="btn btn-primary">
                            <i class="ti-plus me-1"></i>New Request
                        </a>
                        @endif
                        <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-secondary">
                            <i class="ti-arrow-left me-1"></i>Back to Pharmacy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Statistics Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Pending</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">Last 30 days</a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['pending'] }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-yellow">
                                        <i class="ti-clock"></i> Awaiting approval
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Approved</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">Last 30 days</a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['approved'] }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-green">
                                        <i class="ti-check"></i> Approved for dispensing
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Rejected</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">Last 30 days</a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['rejected'] }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-red">
                                        <i class="ti-x"></i> Not approved
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Dispensed</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">Last 30 days</a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['dispensed'] }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-blue">
                                        <i class="ti-package"></i> Completed
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select id="status_filter" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select id="priority_filter" class="form-select">
                                <option value="">All Priorities</option>
                                @foreach ($priorities as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <p class="text-muted small mb-0">
                                <i class="ti-info-alt me-1"></i>Use the search box in the table below to find specific
                                requests
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your Stock Requests</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Drug</th>
                                    <th>Program</th>
                                    <th>Quantity</th>
                                    <th>Cost</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Requested</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rejection Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rejectionReason"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            let table = $('#requestsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('facility.pharmacy.stock-requests') }}',
                    data: function(d) {
                        d.status = $('#status_filter').val();
                        d.priority = $('#priority_filter').val();
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables AJAX Error:', {
                            status: xhr.status,
                            responseText: xhr.responseText,
                            error: error,
                            code: code
                        });
                    }
                },
                columns: [{
                        data: 'request_id'
                    },
                    {
                        data: 'drug_info'
                    },
                    {
                        data: 'program_name'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'cost'
                    },
                    {
                        data: 'priority'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'requested'
                    },
                    {
                        data: 'action',
                        orderable: false
                    }
                ],
                order: [
                    [7, 'desc']
                ],
                pageLength: 25,
                responsive: true,
                rawColumns: ['request_id', 'drug_info', 'priority', 'status', 'requested', 'action']
            });

            // Status filter
            $('#status_filter').on('change', function() {
                table.draw();
            });

            // Priority filter
            $('#priority_filter').on('change', function() {
                table.draw();
            });
        });

        function showRejectionReason(requestId) {
            // Fetch rejection reason via AJAX
            fetch(`/facility/pharmacy/stock-requests/${requestId}/rejection-reason`)
                .then(response => response.json())
                .then(data => {
                    const reason = data.reason || 'No reason provided';
                    document.getElementById('rejectionReason').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="ti-alert-triangle me-2"></i>
                            <strong>Request Rejected:</strong><br>
                            ${reason}
                        </div>
                    `;
                    new bootstrap.Modal(document.getElementById('rejectionModal')).show();
                })
                .catch(error => {
                    console.error('Error fetching rejection reason:', error);
                });
        }
    </script>
@endpush
