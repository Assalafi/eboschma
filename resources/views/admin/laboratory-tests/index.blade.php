@extends('layouts.app')

@section('title', 'Laboratory Tests')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Laboratory Tests</h4>
                                <p class="text-muted mb-0">Manage laboratory tests and pricing</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fe fe-upload me-1"></i> Import
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('laboratory-tests.upload') }}">
                                                <i class="fe fe-upload me-2"></i>Upload Excel File
                                            </a></li>
                                        <li><a class="dropdown-item" href="{{ route('laboratory-tests.template') }}">
                                                <i class="fe fe-download me-2"></i>Download Template
                                            </a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown"
                                        data-bs-toggle="dropdown">
                                        <i class="fe fe-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" id="exportExcel">
                                                <i class="fe fe-file-text me-2"></i>Export to Excel
                                            </a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fe fe-plus me-1"></i> Add Test
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('laboratory-tests.create') }}">
                                                <i class="fe fe-plus me-2"></i>Add Single Test
                                            </a></li>
                                        <li><a class="dropdown-item" href="{{ route('laboratory-tests.bulk.create') }}">
                                                <i class="fe fe-list me-2"></i>Bulk Add Tests
                                            </a></li>
                                    </ul>
                                </div>
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

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Filter by Sample Type</label>
                                <select class="form-select form-select-sm" id="sampleTypeFilter">
                                    <option value="">All Sample Types</option>
                                    @foreach ($sampleTypes as $key => $type)
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Clear Filters</label>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearFilters">
                                    <i class="fe fe-x me-1"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 text-white">Total Tests</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['total'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-activity"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 text-white">Blood Tests</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['blood_tests'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-trending-up"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 text-white">Avg Price</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['avg_price'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-dollar-sign"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="laboratoryTestsTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Sample Type</th>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#laboratoryTestsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('laboratory-tests.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.sample_type = $('#sampleTypeFilter').val();
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function(xhr, error, code) {
                        console.log('DataTables Ajax Error:', xhr.responseText);
                        alert('Error loading data. Please check console for details.');
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data, type, row) {
                            return data ? data.substring(0, 100) + (data.length > 100 ? '...' :
                                '') : '<span class="text-muted">No description</span>';
                        }
                    },
                    {
                        data: 'sample_type_badge',
                        name: 'sample_type'
                    },
                    {
                        data: 'formatted_price',
                        name: 'price'
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
                    searchPlaceholder: "Search tests..."
                }
            });

            // Filter handlers
            $('#sampleTypeFilter').on('change', function() {
                table.ajax.reload();
            });

            $('#clearFilters').on('click', function() {
                $('#sampleTypeFilter').val('');
                table.ajax.reload();
            });

            // Export functionality
            $('#exportExcel').on('click', function(e) {
                e.preventDefault();

                var btn = $(this);
                var originalHtml = btn.html();
                btn.html('<i class="fe fe-loader fe-spin me-2"></i>Exporting...').prop('disabled', true);

                $.get("{{ route('laboratory-tests.export') }}", function(response) {
                    if (response.success) {
                        // Create download link
                        var link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Show success message
                        alert('Export completed successfully!');
                    } else {
                        alert('Export failed: ' + response.message);
                    }
                }).fail(function() {
                    alert('Export failed. Please try again.');
                }).always(function() {
                    btn.html(originalHtml).prop('disabled', false);
                });
            });

            // Style the search input
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
        });
    </script>
@endpush
