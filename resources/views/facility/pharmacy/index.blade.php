@extends('layouts.facility')

@section('title', 'Pharmacy Management')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Pharmacy
                            Management</h1>
                        <p class="text-muted mb-0">Manage drug inventory and stock levels for your facility</p>
                    </div>
                    <div>
                        <a href="{{ route('facility.pharmacy.low-stock') }}" class="btn btn-warning me-2">
                            <i class="ti-alert-triangle me-1"></i> Low Stock Alerts
                        </a>
                        <a href="{{ route('facility.pharmacy.stock') }}" class="btn btn-info me-2">
                            <i class="ti-package me-1"></i> Update Stock
                        </a>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle"
                                data-bs-toggle="dropdown">
                                <i class="ti-plus me-1"></i> Add Drugs
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('facility.pharmacy.create') }}">
                                        <i class="ti-plus me-2"></i>Add Single Drug
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('facility.pharmacy.bulk-create') }}">
                                        <i class="ti-list me-2"></i>Bulk Add Drugs
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('facility.pharmacy.import') }}">
                                        <i class="ti-upload me-2"></i>Import Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown">
                                <i class="ti-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('facility.pharmacy.export') }}">
                                        <i class="ti-file me-2"></i>Export to Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('facility.pharmacy.download-template') }}">
                                        <i class="ti-download me-2"></i>Download Template
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-primary text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-package" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_drugs'] }}</h3>
                                        <p class="text-muted mb-0 small">Total Drugs</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-success text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-check-circle" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-success">{{ $stats['in_stock'] }}</h3>
                                        <p class="text-muted mb-0 small">In Stock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-warning text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-alert-triangle" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['low_stock'] }}</h3>
                                        <p class="text-muted mb-0 small">Low Stock (≤10)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-danger text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-close-circle" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-danger">{{ $stats['out_of_stock'] }}</h3>
                                        <p class="text-muted mb-0 small">Out of Stock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Statistics Row -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-orange text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-alert-triangle" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-orange">{{ $stats['near_expiry'] ?? 0 }}</h3>
                                        <p class="text-muted mb-0 small">Near Expiry (≤30 days)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-info text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-package" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-info">
                                            {{ $stats['unique_drugs'] ?? $stats['total_drugs'] }}</h3>
                                        <p class="text-muted mb-0 small">Unique Drug Types</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Value Card -->
                <div class="card border-0 shadow-sm mb-4"
                    style="border-radius: 12px; background: linear-gradient(135deg, #01542B 0%, #027a48 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-white">
                                <h5 class="card-title mb-2">Total Inventory Value</h5>
                                <h2 class="mb-0 fw-bold">₦{{ number_format($stats['total_value'], 2) }}</h2>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-20 text-white"
                                style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                <i class="ti-money" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label fw-semibold text-dark">Filter by Dosage Form</label>
                                <select class="form-select" id="dosage_form" name="dosage_form">
                                    <option value="">All Forms</option>
                                    @foreach ($dosageForms as $form)
                                        <option value="{{ $form }}">{{ $form }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-3 mb-md-0">
                                <label class="form-label fw-semibold text-dark">&nbsp;</label>
                                <p class="text-muted small mb-0">
                                    <i class="ti-info-alt me-1"></i>Use the search box in the table below to find specific
                                    drugs
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drugs Table -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom" style="padding: 1.5rem;">
                        <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                            <i class="ti-package me-2 text-primary"></i>Drug Inventory
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="drugsTable" class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 fw-semibold text-dark">Drug Name</th>
                                        <th class="border-0 fw-semibold text-dark">Dosage Form</th>
                                        <th class="border-0 fw-semibold text-dark">Strength</th>
                                        <th class="border-0 fw-semibold text-dark">Unit</th>
                                        <th class="border-0 fw-semibold text-dark">Unit Price</th>
                                        <th class="border-0 fw-semibold text-dark">Stock Level</th>
                                        <th class="border-0 fw-semibold text-dark">Status</th>
                                        <th class="border-0 fw-semibold text-dark">Total Value</th>
                                        <th class="border-0 fw-semibold text-center text-dark">Actions</th>
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
    </div>

    <!-- Stock Details Modal -->
    <div class="modal fade" id="stockDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Stock Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="stockDetailsContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading stock details...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table th {
            border-bottom: 2px solid #e9ecef !important;
        }

        .table td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f8f9fa !important;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .bg-orange {
            background-color: #fd7e14 !important;
        }

        .text-orange {
            color: #fd7e14 !important;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .avatar {
            object-fit: cover;
        }

        @media print {

            .btn,
            .btn-group {
                display: none !important;
            }

            .table {
                font-size: 12px;
            }
        }
    </style>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Show stock details function
        function showStockDetails(drugId) {
            $('#stockDetailsModal').modal('show');

            // Reset content
            $('#stockDetailsContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading stock details...</p>
                </div>
            `);

            // Fetch stock details
            $.ajax({
                url: '/drugs/' + drugId + '/stock-details',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let html = `
                            <div class="mb-4">
                                <h6 class="text-primary mb-3">Drug Information</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Name:</strong> ${response.drug.name}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Dosage Form:</strong> ${response.drug.dosage_form}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Strength:</strong> ${response.drug.strength}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Stock:</strong> <span class="badge bg-primary">${response.total_stock.toLocaleString()} ${response.drug.unit}</span>
                                    </div>
                                </div>
                            </div>
                        `;

                        if (response.stock_by_facility.length > 0) {
                            html += '<h6 class="text-primary mb-3">Stock by Facility</h6>';

                            response.stock_by_facility.forEach(function(facility) {
                                html += `
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">${facility.facility_name}</h6>
                                                <div>
                                                    <span class="badge bg-success">${facility.total_quantity.toLocaleString()} units</span>
                                                    ${facility.near_expiry > 0 ? '<span class="badge bg-warning ms-2">⚠️ ' + facility.near_expiry.toLocaleString() + ' expiring soon</span>' : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Batch Number</th>
                                                            <th>Quantity</th>
                                                            <th>Expiry Date</th>
                                                            <th>Days Until Expiry</th>
                                                            <th>Supplier</th>
                                                            <th>Unit Cost</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                `;

                                facility.batches.forEach(function(batch) {
                                    html += `
                                        <tr>
                                            <td>${batch.batch_number}</td>
                                            <td>${batch.quantity_remaining.toLocaleString()}</td>
                                            <td>${batch.expiry_date}</td>
                                            <td>${batch.days_until_expiry} days</td>
                                            <td>${batch.supplier || '-'}</td>
                                            <td>${batch.unit_cost}</td>
                                            <td>${batch.status_badge}</td>
                                        </tr>
                                    `;
                                });

                                html += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html += `
                                <div class="alert alert-info">
                                    <i class="ti-info-alt me-2"></i>
                                    No stock available for this drug.
                                </div>
                            `;
                        }

                        $('#stockDetailsContent').html(html);
                    }
                },
                error: function(xhr) {
                    $('#stockDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="ti-alert me-2"></i>
                            Failed to load stock details. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Initialize DataTables
        $(document).ready(function() {
            let table = $('#drugsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('facility.pharmacy.index') }}',
                    data: function(d) {
                        d.dosage_form = $('#dosage_form').val();
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
                        data: 'name'
                    },
                    {
                        data: 'dosage_form'
                    },
                    {
                        data: 'strength'
                    },
                    {
                        data: 'unit'
                    },
                    {
                        data: 'unit_price_formatted'
                    },
                    {
                        data: 'stock_level',
                        orderable: false
                    },
                    {
                        data: 'stock_status',
                        orderable: false
                    },
                    {
                        data: 'total_value',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                responsive: true
            });

            // Dosage form filter
            $('#dosage_form').on('change', function() {
                table.draw();
            });
        });
    </script>
@endsection
