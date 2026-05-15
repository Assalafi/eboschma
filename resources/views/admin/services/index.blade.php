@extends('layouts.app')

@section('title', 'Services Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Services Management</h4>
                        <p class="text-muted mb-0">Manage system services and their types</p>
                    </div>
                    <div>
                        <a href="{{ route('services.bulk.create') }}" class="btn btn-success me-2">
                            <i class="ti-plus me-1"></i> Bulk Create
                        </a>
                        <a href="{{ route('services.create') }}" class="btn btn-primary">
                            <i class="ti-plus me-1"></i> Add Service
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="ti-package" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $stats['total'] }}</h5>
                                <p class="card-text text-muted small">Total Services</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="ti-star" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $stats['primary'] }}</h5>
                                <p class="card-text text-muted small">Primary Services</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-secondary mb-2">
                                    <i class="ti-layers" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $stats['secondary'] }}</h5>
                                <p class="card-text text-muted small">Secondary Services</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="ti-stats-up" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title mb-1">
                                    {{ number_format(($stats['primary'] / max($stats['total'], 1)) * 100, 1) }}%</h5>
                                <p class="card-text text-muted small">Primary Rate</p>
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
                                    placeholder="Search services...">
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Service Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    @foreach ($types as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-outline-primary" onclick="clearFilters()">
                                        <i class="ti-refresh me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Services Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Created</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                var dataTable = $('#servicesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('services.index') }}',
                        data: function(d) {
                            d.search = $('#search').val();
                            d.type = $('#type').val();
                        }
                    },
                    columns: [{
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'type_badge',
                            name: 'type',
                            orderable: false
                        },
                        {
                            data: 'price_formatted',
                            name: 'price',
                            className: 'text-end'
                        },
                        {
                            data: 'created_at_formatted',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            className: 'text-center'
                        }
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    pageLength: 25,
                    responsive: true,
                    language: {
                        search: "",
                        searchPlaceholder: "Search services...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ services",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });

                // Apply filters on change
                $('#search, #type').on('keyup change', function() {
                    dataTable.ajax.reload();
                });

                // Clear filters function
                window.clearFilters = function() {
                    $('#search').val('');
                    $('#type').val('');
                    dataTable.ajax.reload();
                };
            });
        </script>
    @endpush
@endsection
