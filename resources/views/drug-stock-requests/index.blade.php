@extends('layouts.app')

@section('title', 'Drug Stock Requests')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .status-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .status-card.active {
            border-color: var(--tblr-primary);
            box-shadow: 0 4px 12px rgba(var(--tblr-primary-rgb), 0.25);
        }

        .status-card[data-status="pending"].active {
            border-color: #f59f00;
        }

        .status-card[data-status="approved"].active {
            border-color: #2fb344;
        }

        .status-card[data-status="rejected"].active {
            border-color: #d63939;
        }

        .status-card[data-status="dispensed"].active {
            border-color: #4299e1;
        }

        #facilitySection {
            display: none;
        }

        #facilitySection.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="page-pretitle">Stock Management</div>
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Drug Stock Requests
                    </h2>
                    <div class="text-muted mt-1">Click a status card below to view facilities with requests</div>
                </div>
                <div class="col-md-4 d-print-none">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('drug-stock-requests.create') }}" class="btn btn-primary">
                            <i class="ti-plus me-1"></i>New Request
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('drug-stock-requests.index') }}" class="card mb-3">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-end">
                        @if($isBoschmaAdmin)
                        <div class="col-md-4">
                            <label class="form-label">Facility</label>
                            <select name="facility_id" id="filter-facility" class="form-select">
                                <option value="">All Facilities</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" id="filter-date-from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" id="filter-date-to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti-filter me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Clickable Status Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card status-card" data-status="pending">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Pending</div>
                                <div class="ms-auto">
                                    <span class="text-yellow"><i class="ti-clock"></i></span>
                                </div>
                            </div>
                            <div class="h1 mb-1">{{ $stats['pending'] }}</div>
                            <div class="status-label text-yellow">Awaiting approval</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card status-card" data-status="approved">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Approved</div>
                                <div class="ms-auto">
                                    <span class="text-green"><i class="ti-check"></i></span>
                                </div>
                            </div>
                            <div class="h1 mb-1">{{ $stats['approved'] }}</div>
                            <div class="status-label text-green">Ready for dispensing</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card status-card" data-status="rejected">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Rejected</div>
                                <div class="ms-auto">
                                    <span class="text-red"><i class="ti-x"></i></span>
                                </div>
                            </div>
                            <div class="h1 mb-1">{{ $stats['rejected'] }}</div>
                            <div class="status-label text-red">Not approved</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card status-card" data-status="dispensed">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Dispensed</div>
                                <div class="ms-auto">
                                    <span class="text-blue"><i class="ti-package"></i></span>
                                </div>
                            </div>
                            <div class="h1 mb-1">{{ $stats['dispensed'] }}</div>
                            <div class="status-label text-blue">Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facility Table (hidden by default, shown on card click) -->
            <div id="facilitySection">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti-building me-2"></i>Facilities with
                            <span id="selectedStatusLabel" class="badge bg-primary ms-1">-</span> requests
                        </h3>
                        <div class="card-actions">
                            <button type="button" class="btn btn-ghost-secondary btn-sm" id="clearFilter">
                                <i class="ti-x me-1"></i>Clear Filter
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="facilityTable" class="table table-vcenter table-hover">
                                <thead>
                                    <tr>
                                        <th>Facility</th>
                                        <th>Requests</th>
                                        <th>Total Quantity</th>
                                        <th>Total Cost</th>
                                        <th>Latest Request</th>
                                        <th class="w-1">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prompt when no card selected -->
            <div id="promptSection" class="text-center py-5">
                <div class="mb-3">
                    <i class="ti-hand-point-up" style="font-size: 3rem; color: #667382;"></i>
                </div>
                <h3 class="text-muted">Select a status card above</h3>
                <p class="text-muted">Click on Pending, Approved, Rejected, or Dispensed to see facilities with those
                    requests</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let facilityTable = null;
        let selectedStatus = '';

        const statusColors = {
            'pending': 'warning',
            'approved': 'success',
            'rejected': 'danger',
            'dispensed': 'info'
        };

        const statusLabels = {
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'dispensed': 'Dispensed'
        };

        // Click handler for status cards
        $('.status-card').on('click', function() {
            var status = $(this).data('status');

            // Toggle if already selected
            if (selectedStatus === status) {
                clearSelection();
                return;
            }

            selectedStatus = status;

            // Update card active states
            $('.status-card').removeClass('active');
            $(this).addClass('active');

            // Update label
            var color = statusColors[status];
            $('#selectedStatusLabel').attr('class', 'badge bg-' + color + ' ms-1').text(statusLabels[status]);

            // Show facility section, hide prompt
            $('#promptSection').hide();
            $('#facilitySection').addClass('show');

            // Load or reload DataTable
            if (facilityTable) {
                facilityTable.ajax.reload();
            } else {
                initFacilityTable();
            }
        });

        // Clear filter
        $('#clearFilter').on('click', function() {
            clearSelection();
        });

        function clearSelection() {
            selectedStatus = '';
            $('.status-card').removeClass('active');
            $('#facilitySection').removeClass('show');
            $('#promptSection').show();
            if (facilityTable) {
                facilityTable.destroy();
                facilityTable = null;
                $('#facilityTable tbody').empty();
            }
        }

        function initFacilityTable() {
            facilityTable = $('#facilityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('drug-stock-requests.index') }}',
                    data: function(d) {
                        d.view = 'facilities';
                        d.status = selectedStatus;
                        d.facility_id = $('#filter-facility').length ? $('#filter-facility').val() : '';
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                    }
                },
                columns: [{
                        data: 'facility_info'
                    },
                    {
                        data: 'request_count_fmt',
                        className: 'text-center'
                    },
                    {
                        data: 'total_quantity_fmt',
                        className: 'text-end'
                    },
                    {
                        data: 'total_cost_fmt',
                        className: 'text-end'
                    },
                    {
                        data: 'latest_request_fmt'
                    },
                    {
                        data: 'action',
                        orderable: false
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                language: {
                    emptyTable: 'No facilities found with ' + statusLabels[selectedStatus] + ' requests'
                }
            });
        }
    </script>
@endpush
