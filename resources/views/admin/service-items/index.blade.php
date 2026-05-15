@extends('layouts.app')

@section('title', 'Service Items')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Service Items</h4>
                                <p class="text-muted mb-0">Manage individual service items with pricing</p>
                            </div>
                            <div>
                                <a href="{{ route('service-items.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-1"></i> Add Service Item
                                </a>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Category</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Service Type</label>
                                <select class="form-select" id="typeFilter">
                                    <option value="">All Service Types</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}"
                                            data-category="{{ $type->service_category_id }}">
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Item Type</label>
                                <select class="form-select" id="itemTypeFilter">
                                    <option value="">All Item Types</option>
                                    @foreach ($itemTypes as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="serviceItemsTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Service Type</th>
                                        <th>Item Name</th>
                                        <th>Item Type</th>
                                        <th>Price</th>
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
            var table = $('#serviceItemsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('service-items.index') }}',
                    data: function(d) {
                        d.category_id = $('#categoryFilter').val();
                        d.type_id = $('#typeFilter').val();
                        d.item_type = $('#itemTypeFilter').val();
                    }
                },
                columns: [{
                        data: 'service_category_name',
                        name: 'service_category_name'
                    },
                    {
                        data: 'service_type_name',
                        name: 'service_type_name'
                    },
                    {
                        data: 'name',
                        name: 'service_items.name'
                    },
                    {
                        data: 'type_badge',
                        name: 'service_items.type',
                        className: 'text-center'
                    },
                    {
                        data: 'price_formatted',
                        name: 'service_items.price',
                        className: 'text-end'
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
                    [1, 'asc'],
                    [2, 'asc']
                ],
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search service items..."
                }
            });

            // Filter by category - also filter service types dropdown
            $('#categoryFilter').on('change', function() {
                var categoryId = $(this).val();
                var typeFilter = $('#typeFilter');

                // Show/hide service types based on category
                typeFilter.find('option').each(function() {
                    var option = $(this);
                    if (option.val() === '') {
                        option.show();
                    } else if (categoryId === '' || option.data('category') == categoryId) {
                        option.show();
                    } else {
                        option.hide();
                        if (option.is(':selected')) {
                            typeFilter.val('');
                        }
                    }
                });

                table.draw();
            });

            // Filter by service type
            $('#typeFilter').on('change', function() {
                table.draw();
            });

            // Filter by item type
            $('#itemTypeFilter').on('change', function() {
                table.draw();
            });

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#categoryFilter').val('');
                $('#typeFilter').val('').find('option').show();
                $('#itemTypeFilter').val('');
                table.draw();
            });
        });
    </script>
@endpush
