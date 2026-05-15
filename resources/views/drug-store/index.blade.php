@extends('layouts.app')

@section('title', 'Drug Store - Central Inventory')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .nav-tabs .nav-link.active {
            color: #206bc4 !important;
            background-color: #f8fafc !important;
            border-color: #dee2e6 #dee2e6 #f8fafc !important;
        }

        .nav-tabs .nav-link.active:hover {
            color: #206bc4 !important;
            background-color: #f8fafc !important;
            border-color: #dee2e6 #dee2e6 #f8fafc !important;
        }

        .nav-tabs .nav-link:not(.active):hover {
            color: #206bc4 !important;
            background-color: #f8fafc !important;
        }
    </style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="page-pretitle">Central Inventory</div>
                    <h2 class="page-title">
                        <i class="ti-home me-2 text-primary"></i>Drug Store
                    </h2>
                    <div class="text-muted mt-1">Manage central drug inventory and track stock levels</div>
                </div>
                <div class="col-md-4 d-print-none">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('drug-store.stock-in-form') }}" class="btn btn-primary">
                            <i class="ti-plus me-1"></i>Stock In
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
                                <div class="subheader">Total Drugs</div>
                            </div>
                            <div class="h1 mb-3">{{ number_format($stats['total_drugs']) }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-primary">
                                        <i class="ti-package"></i> {{ $stats['drugs_in_store'] }} in store
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
                                <div class="subheader">Store Available</div>
                            </div>
                            <div class="h1 mb-3 {{ $stats['total_available'] < 0 ? 'text-danger' : '' }}">
                                {{ number_format($stats['total_available']) }}
                                @if ($stats['total_available'] < 0)
                                    <small class="badge bg-danger ms-2">Deficit</small>
                                @endif
                            </div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="{{ $stats['total_available'] < 0 ? 'text-danger' : 'text-green' }}">
                                        <i
                                            class="{{ $stats['total_available'] < 0 ? 'ti-alert-triangle' : 'ti-check' }}"></i>
                                        {{ $stats['total_available'] < 0 ? 'Units owed to store' : 'Units in central store' }}
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
                                <div class="subheader">Dispensed to Facilities</div>
                            </div>
                            <div class="h1 mb-3">{{ number_format($stats['total_dispensed']) }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-blue">
                                        <i class="ti-truck"></i> Total units sent out
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
                                <div class="subheader">Store Value</div>
                            </div>
                            <div class="h1 mb-3">₦{{ number_format($stats['total_value'], 2) }}</div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="me-auto">
                                    <span class="text-yellow">
                                        <i class="ti-money"></i> Current inventory value
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Cards -->
            @if ($stats['low_stock_count'] > 0 || $stats['near_expiry_count'] > 0)
                <div class="row mb-3">
                    @if ($stats['low_stock_count'] > 0)
                        <div class="col-md-6">
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="ti-alert-triangle me-2 fs-2"></i>
                                <div>
                                    <strong>{{ $stats['low_stock_count'] }} drug(s)</strong> are running low on stock (&le;
                                    50 units).
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($stats['near_expiry_count'] > 0)
                        <div class="col-md-6">
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="ti-clock me-2 fs-2"></i>
                                <div>
                                    <strong>{{ $stats['near_expiry_count'] }} batch(es)</strong> are expiring within 30
                                    days.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="storeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-drugs-tab" data-bs-toggle="tab" data-bs-target="#all-drugs-pane"
                        type="button" role="tab">
                        <i class="ti-list me-1"></i>All Drugs <span
                            class="badge bg-primary ms-1">{{ $stats['total_drugs'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="store-inventory-tab" data-bs-toggle="tab"
                        data-bs-target="#store-inventory-pane" type="button" role="tab">
                        <i class="ti-package me-1"></i>Store Inventory <span
                            class="badge bg-success ms-1">{{ $stats['drugs_in_store'] }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="storeTabsContent">
                <!-- All Drugs Tab -->
                <div class="tab-pane fade show active" id="all-drugs-pane" role="tabpanel">
                    <!-- Filters -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Filters</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Store Availability</label>
                                    <select id="all_drugs_availability_filter" class="form-select">
                                        <option value="">All</option>
                                        <option value="available">In Store</option>
                                        <option value="not_available">Not in Store</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price Range</label>
                                    <select id="price_range_filter" class="form-select">
                                        <option value="">All Prices</option>
                                        <option value="low">Low (&lt; ₦100)</option>
                                        <option value="medium">Medium (₦100 - ₦1000)</option>
                                        <option value="high">High (&gt; ₦1000)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Has Requests</label>
                                    <select id="has_requests_filter" class="form-select">
                                        <option value="">All</option>
                                        <option value="yes">Has Open Requests</option>
                                        <option value="no">No Open Requests</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" id="all_drugs_search" class="form-control"
                                        placeholder="Search drugs...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Drugs Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="allDrugsTable" class="table table-vcenter table-hover">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Unit Price</th>
                                            <th>Store Available</th>
                                            <th>Dispensed to Facilities</th>
                                            <th>Open Requests</th>
                                            <th class="w-1">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Inventory Tab -->
                <div class="tab-pane fade" id="store-inventory-pane" role="tabpanel">
                    <!-- Filters -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Filters</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Stock Status</label>
                                    <select id="stock_status_filter" class="form-select">
                                        <option value="">All</option>
                                        <option value="in_stock">In Stock</option>
                                        <option value="low_stock">Low Stock (&le; 50)</option>
                                        <option value="out_of_stock">Out of Stock</option>
                                        <option value="near_expiry">Near Expiry</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Store Inventory</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="storeTable" class="table table-vcenter table-hover">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Available</th>
                                            <th>Received</th>
                                            <th>Dispensed</th>
                                            <th>Batches</th>
                                            <th>Expiry Status</th>
                                            <th>Value</th>
                                            <th class="w-1">Actions</th>
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
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // All Drugs table (default active tab)
            let allDrugsTable = $('#allDrugsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('drug-store.index') }}',
                    data: function(d) {
                        d.tab = 'all_drugs';
                        d.availability = $('#all_drugs_availability_filter').val();
                        d.price_range = $('#price_range_filter').val();
                        d.has_requests = $('#has_requests_filter').val();
                        d.search = $('#all_drugs_search').val();
                    }
                },
                columns: [{
                        data: 'drug_info'
                    },
                    {
                        data: 'unit_price_fmt'
                    },
                    {
                        data: 'store_available'
                    },
                    {
                        data: 'dispensed_to_fac'
                    },
                    {
                        data: 'requests_info'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                responsive: true,
                searching: false, // Disable default search since we have custom search
                lengthChange: true
            });

            // Store inventory table (lazy-loaded on tab click)
            let storeTable = null;

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                if (e.target.id === 'store-inventory-tab' && !storeTable) {
                    storeTable = $('#storeTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ route('drug-store.index') }}',
                            data: function(d) {
                                d.tab = 'store';
                                d.stock_status = $('#stock_status_filter').val();
                            }
                        },
                        columns: [{
                                data: 'drug_info'
                            },
                            {
                                data: 'available'
                            },
                            {
                                data: 'received'
                            },
                            {
                                data: 'dispensed'
                            },
                            {
                                data: 'batches'
                            },
                            {
                                data: 'expiry_info'
                            },
                            {
                                data: 'value'
                            },
                            {
                                data: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ],
                        order: [
                            [1, 'desc']
                        ],
                        pageLength: 25,
                        responsive: true
                    });
                }

                // Adjust column widths when tab becomes visible
                setTimeout(function() {
                    $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    }).columns.adjust();
                }, 200);
            });

            // All Drugs filter handlers
            $('#all_drugs_availability_filter, #price_range_filter, #has_requests_filter').on('change', function() {
                allDrugsTable.draw();
            });

            // Search with debounce
            let searchTimeout;
            $('#all_drugs_search').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    allDrugsTable.draw();
                }, 300);
            });

            $('#stock_status_filter').on('change', function() {
                if (storeTable) {
                    storeTable.draw();
                }
            });
        });
    </script>
@endpush
