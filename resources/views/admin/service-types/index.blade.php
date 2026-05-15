@extends('layouts.app')

@section('title', 'Service Types')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Service Types</h4>
                                <p class="text-muted mb-0">Manage service types within categories</p>
                            </div>
                            <div>
                                <a href="{{ route('service-types.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-1"></i> Add Service Type
                                </a>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Filter by Category</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary" id="resetFilter">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="serviceTypesTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Service Type</th>
                                        <th class="text-center">Service Items</th>
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
            var table = $('#serviceTypesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('service-types.index') }}',
                    data: function(d) {
                        d.category_id = $('#categoryFilter').val();
                    }
                },
                columns: [{
                        data: 'service_category_name',
                        name: 'service_category_name'
                    },
                    {
                        data: 'name',
                        name: 'service_types.name'
                    },
                    {
                        data: 'service_items_count',
                        name: 'service_items_count',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc'],
                    [1, 'asc']
                ],
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search service types..."
                }
            });

            // Filter by category
            $('#categoryFilter').on('change', function() {
                table.draw();
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#categoryFilter').val('');
                table.draw();
            });
        });
    </script>
@endpush
