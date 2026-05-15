@extends('layouts.app')

@section('title', 'Drugs Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Drugs Management</h4>
                        <p class="text-muted mb-0">Manage pharmaceutical drugs inventory and information</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if (Auth::user()->can('drugs.create'))
                            <a href="{{ route('drugs.bulk.create') }}" class="btn btn-outline-primary">
                                <i class="ti-plus me-1"></i> Bulk Create
                            </a>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#importModal">
                                <i class="ti-upload me-1"></i> Import
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="exportWithFilters()">
                                <i class="ti-download me-1"></i> Export
                            </button>
                            <a href="{{ route('drugs.create') }}" class="btn btn-primary">
                                <i class="ti-plus me-1"></i> Add Drug
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="ti-package" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-0">{{ $stats['total'] }}</h5>
                                <p class="card-text small">Total Drugs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="ti-layers" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-0">{{ $stats['tablets'] }}</h5>
                                <p class="card-text small">Tablets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="ti-layers-alt" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-0">{{ $stats['capsules'] }}</h5>
                                <p class="card-text small">Capsules</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="ti-cup" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-0">{{ $stats['liquids'] }}</h5>
                                <p class="card-text small">Liquids</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm bg-warning-subtle">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="ti-alert" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-0">{{ $stats['low_stock'] }}</h5>
                                <p class="card-text small">Low Stock</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm bg-danger-subtle">
                            <div class="card-body text-center">
                                <div class="text-danger mb-2">
                                    <i class="ti-close" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-0">{{ $stats['out_of_stock'] }}</h5>
                                <p class="card-text small">Out of Stock</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="Search drugs...">
                            </div>
                            <div class="col-md-3">
                                <label for="dosage_form" class="form-label">Dosage Form</label>
                                <select class="form-select" id="dosage_form" name="dosage_form">
                                    <option value="">All Forms</option>
                                    @foreach ($dosageForms as $form)
                                        <option value="{{ $form }}">{{ $form }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ti-search me-1"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                        <i class="ti-refresh me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="drugsTable">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Name</th>
                                        <th>Dosage Form</th>
                                        <th>Strength</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Stock Level</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Drugs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('drugs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose File</label>
                            <input type="file" class="form-control" id="file" name="file"
                                accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Supported formats: XLSX, XLS, CSV (Max: 10MB)</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ti-info-alt me-2"></i>
                            <strong>Instructions:</strong><br>
                            1. Download the template below<br>
                            2. Fill in your drug data<br>
                            3. Upload the completed file
                        </div>
                        <div class="text-center">
                            <a href="{{ route('drugs.download.template') }}" class="btn btn-outline-primary">
                                <i class="ti-download me-1"></i> Download Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Delete Drugs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkDeleteForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <div class="modal-body">
                        <p>Are you sure you want to delete the selected drugs? This action cannot be undone.</p>
                        <p><strong id="deleteCount">0</strong> drugs will be deleted.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti-trash me-1"></i> Delete
                        </button>
                    </div>
                </form>
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
@endsection

@push('styles')
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .badge {
            font-size: 0.75rem;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#drugsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('drugs.index') }}',
                    data: function(d) {
                        d.search = $('#search').val();
                        d.dosage_form = $('#dosage_form').val();
                        console.log('Sending AJAX request with data:', d);
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
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input drug-checkbox" value="' +
                                row.id + '">';
                        }
                    },
                    {
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
                        data: 'description',
                        render: function(data, type, row) {
                            if (data && data.length > 50) {
                                return data.substring(0, 50) + '...';
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return '<div class="action-buttons">' + row.action + '</div>';
                        }
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                pageLength: 25,
                responsive: true
            });

            // Filter form submission
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filters
            window.resetFilters = function() {
                $('#filterForm')[0].reset();
                table.draw();
            };

            // Select all checkbox
            $('#selectAll').on('change', function() {
                $('.drug-checkbox').prop('checked', $(this).prop('checked'));
                updateBulkActions();
            });

            // Individual checkboxes
            $(document).on('change', '.drug-checkbox', function() {
                updateSelectAll();
                updateBulkActions();
            });

            function updateSelectAll() {
                let allChecked = $('.drug-checkbox').length === $('.drug-checkbox:checked').length;
                $('#selectAll').prop('checked', allChecked);
            }

            function updateBulkActions() {
                let selectedCount = $('.drug-checkbox:checked').length;
                $('#deleteCount').text(selectedCount);

                if (selectedCount > 0) {
                    $('#bulkDeleteBtn').show();
                } else {
                    $('#bulkDeleteBtn').hide();
                }
            }

            // Bulk delete
            $('#bulkDeleteBtn').on('click', function() {
                let selectedIds = [];
                $('.drug-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                $('#bulkDeleteForm').attr('action', '{{ route('drugs.bulk.delete') }}');
                $('#bulkDeleteForm input[name="drug_ids"]').remove();
                $('<input>').attr({
                    type: 'hidden',
                    name: 'drug_ids[]',
                    value: selectedIds
                }).appendTo('#bulkDeleteForm');

                $('#bulkDeleteModal').modal('show');
            });

            // Bulk delete form submission
            $('#bulkDeleteForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#bulkDeleteModal').modal('hide');
                        table.draw();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                });
            });
        });

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

        // Export with current filters
        function exportWithFilters() {
            const search = $('#search').val();
            const dosageForm = $('#dosage_form').val();

            let exportUrl = '{{ route('drugs.export') }}';
            const params = new URLSearchParams();

            if (search) {
                params.append('search', search);
            }
            if (dosageForm) {
                params.append('dosage_form', dosageForm);
            }

            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }

            window.location.href = exportUrl;
        }
    </script>
@endpush
