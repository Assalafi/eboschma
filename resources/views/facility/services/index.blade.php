@extends('layouts.facility')

@section('title', 'Facility Services')

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
                                    <p class="text-muted mb-0">Manage services offered by your facility</p>
                                </div>
                                <div>
                                    <a href="{{ route('facility.services.create') }}" class="btn btn-primary">
                                        <i class="fe fe-plus me-1"></i> Add Services
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                            <small>Total Services</small>
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
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select id="filter_category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select id="filter_type" class="form-select">
                                        <option value="">All Types</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="servicesTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Service Name</th>
                                            <th>Type</th>
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
            var table = $('#servicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('facility.services.index') }}',
                    data: function(d) {
                        d.category_id = $('#filter_category').val();
                        d.type_id = $('#filter_type').val();
                    }
                },
                columns: [{
                        data: 'service_name',
                        name: 'service_name'
                    },
                    {
                        data: 'service_type',
                        name: 'service_type'
                    },
                    {
                        data: 'category',
                        name: 'category'
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
                ]
            });

            $('#filter_category').change(function() {
                var categoryId = $(this).val();
                var typeSelect = $('#filter_type');

                if (categoryId) {
                    $.get('{{ route('facility.services.types-by-category') }}', {
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

            $('#filter_type').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
