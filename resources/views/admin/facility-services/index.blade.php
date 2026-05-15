@extends('layouts.app')

@section('title', 'Facility Services')

@push('styles')
    <style>
        #servicesTable td.wrap-cell {
            max-width: 200px;
            white-space: normal;
            word-wrap: break-word;
        }

        #servicesTable th {
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Facility Services</h4>
                                    <p class="text-muted mb-0">Manage services assigned to facilities</p>
                                </div>
                                @can('facility-services.create')
                                    <div>
                                        <a href="{{ route('facility-services.create') }}" class="btn btn-primary">
                                            <i class="fe fe-plus me-1"></i> Assign Services
                                        </a>
                                    </div>
                                @endcan
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                            <small>Total Assignments</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['available'] }}</h3>
                                            <small>Available</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['facilities_count'] }}</h3>
                                            <small>Facilities with Services</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select id="filter_facility" class="form-select">
                                        <option value="">All Facilities</option>
                                        @foreach ($facilities as $facility)
                                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter_category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter_type" class="form-select">
                                        <option value="">All Service Types</option>
                                        @foreach ($serviceTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter_item_type" class="form-select">
                                        <option value="">All (Primary/Secondary)</option>
                                        <option value="Primary">Primary</option>
                                        <option value="Secondary">Secondary</option>
                                    </select>
                                </div>
                            </div>

                            <div id="bulkActions" class="mb-3" style="display: none;">
                                <button type="button" id="bulkDeleteBtn" class="btn btn-danger">
                                    <i class="fe fe-trash me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table id="servicesTable" class="table table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;"><input type="checkbox" id="selectAll"></th>
                                            <th>Facility</th>
                                            <th>Service Name</th>
                                            <th>Type</th>
                                            <th>Service Type</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Availability</th>
                                            <th>Actions</th>
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
    <script>
        $(document).ready(function() {
            var selectedIds = [];

            var table = $('#servicesTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('facility-services.index') }}',
                    data: function(d) {
                        d.facility_id = $('#filter_facility').val();
                        d.category_id = $('#filter_category').val();
                        d.type_id = $('#filter_type').val();
                        d.item_type = $('#filter_item_type').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        width: '30px'
                    },
                    {
                        data: 'facility_name',
                        name: 'facility_name',
                        className: 'wrap-cell'
                    },
                    {
                        data: 'service_name',
                        name: 'service_name',
                        className: 'wrap-cell'
                    },
                    {
                        data: 'item_type',
                        name: 'item_type',
                        searchable: false
                    },
                    {
                        data: 'service_type',
                        name: 'service_type',
                        className: 'wrap-cell'
                    },
                    {
                        data: 'category',
                        name: 'category',
                        className: 'wrap-cell'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'availability',
                        name: 'availability',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function() {
                    // Re-check boxes that were previously selected
                    $('.row-checkbox').each(function() {
                        if (selectedIds.indexOf($(this).val()) !== -1) {
                            $(this).prop('checked', true);
                        }
                    });
                    updateBulkUI();
                }
            });

            function updateBulkUI() {
                $('#selectedCount').text(selectedIds.length);
                if (selectedIds.length > 0) {
                    $('#bulkActions').show();
                } else {
                    $('#bulkActions').hide();
                }
                // Update select all checkbox state
                var allChecked = $('.row-checkbox').length > 0 && $('.row-checkbox:not(:checked)').length === 0;
                $('#selectAll').prop('checked', allChecked);
            }

            // Select All checkbox
            $('#selectAll').on('change', function() {
                var isChecked = $(this).prop('checked');
                $('.row-checkbox').each(function() {
                    $(this).prop('checked', isChecked);
                    var id = $(this).val();
                    if (isChecked && selectedIds.indexOf(id) === -1) {
                        selectedIds.push(id);
                    } else if (!isChecked) {
                        selectedIds = selectedIds.filter(function(v) {
                            return v !== id;
                        });
                    }
                });
                updateBulkUI();
            });

            // Individual checkbox
            $(document).on('change', '.row-checkbox', function() {
                var id = $(this).val();
                if ($(this).prop('checked')) {
                    if (selectedIds.indexOf(id) === -1) selectedIds.push(id);
                } else {
                    selectedIds = selectedIds.filter(function(v) {
                        return v !== id;
                    });
                }
                updateBulkUI();
            });

            // Bulk Delete
            $('#bulkDeleteBtn').on('click', function() {
                if (selectedIds.length === 0) return;
                if (!confirm('Are you sure you want to delete ' + selectedIds.length +
                        ' service assignment(s)? This cannot be undone.')) return;

                $.ajax({
                    url: '{{ route('facility-services.bulk-delete') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            selectedIds = [];
                            updateBulkUI();
                            table.draw();
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message || 'Failed to delete.');
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });

            $('#filter_facility, #filter_type, #filter_item_type').change(function() {
                table.draw();
            });

            $('#filter_category').change(function() {
                var categoryId = $(this).val();
                var typeSelect = $('#filter_type');

                if (categoryId) {
                    $.get('{{ route('facility-services.types-by-category') }}', {
                            category_id: categoryId
                        })
                        .done(function(data) {
                            typeSelect.empty().append('<option value="">All Types</option>');
                            $.each(data, function(key, value) {
                                typeSelect.append('<option value="' + value.id + '">' + value
                                    .name + '</option>');
                            });
                        });
                } else {
                    typeSelect.empty().append('<option value="">All Types</option>');
                }
                table.draw();
            });
        });
    </script>
@endpush
