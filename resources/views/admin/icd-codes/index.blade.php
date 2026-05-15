@extends('layouts.app')

@section('title', 'ICD Codes')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">ICD Codes</h4>
                                <p class="text-muted mb-0">Manage International Classification of Diseases codes</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fe fe-upload me-1"></i> Import
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('icd-codes.upload') }}">
                                                <i class="fe fe-upload me-2"></i>Upload Excel File
                                            </a></li>
                                        <li><a class="dropdown-item" href="{{ route('icd-codes.template') }}">
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
                                        <i class="fe fe-plus me-1"></i> Add Code
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('icd-codes.create') }}">
                                                <i class="fe fe-plus me-2"></i>Add Single Code
                                            </a></li>
                                        <li><a class="dropdown-item" href="{{ route('icd-codes.bulk.create') }}">
                                                <i class="fe fe-list me-2"></i>Bulk Add Codes
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
                                <label class="form-label text-muted small">Filter by Category</label>
                                <select class="form-select form-select-sm" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $key => $category)
                                        <option value="{{ $key }}">{{ $key }} -
                                            {{ Str::limit($category, 40) }}</option>
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
                                                <h6 class="mb-0 text-white">Total Codes</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['total'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-file-text"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 text-white">Categories</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['categories'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-grid"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="icdCodesTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ICD Code</th>
                                        <th>Description</th>
                                        <th>Category</th>
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
            var table = $('#icdCodesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('icd-codes.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.category = $('#categoryFilter').val();
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
                        data: 'code',
                        name: 'code',
                        className: 'font-monospace'
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
                        data: 'category_badge',
                        name: 'category'
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
                    searchPlaceholder: "Search ICD codes..."
                }
            });

            // Filter handlers
            $('#categoryFilter').on('change', function() {
                table.ajax.reload();
            });

            $('#clearFilters').on('click', function() {
                $('#categoryFilter').val('');
                table.ajax.reload();
            });

            // Export functionality
            $('#exportExcel').on('click', function(e) {
                e.preventDefault();

                var btn = $(this);
                var originalHtml = btn.html();
                btn.html('<i class="fe fe-loader fe-spin me-2"></i>Exporting...').prop('disabled', true);

                $.get("{{ route('icd-codes.export') }}", function(response) {
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
