@extends('layouts.app')

@section('title', 'Wards')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Wards Management</h4>
                                    <p class="text-muted mb-0">Manage hospital wards</p>
                                </div>
                                <div>
                                    @can('wards.create')
                                        <a href="{{ route('wards.create') }}" class="btn btn-primary">
                                            <i class="fe fe-plus me-1"></i> Add Wards
                                        </a>
                                    @endcan
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                            <small>Total Wards</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                                            <small>Active Wards</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select id="filter_facility" class="form-select">
                                        <option value="">All Facilities</option>
                                        @foreach ($facilities as $facility)
                                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="wardsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Facility</th>
                                            <th>Rooms</th>
                                            <th>Beds</th>
                                            <th>Status</th>
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
            var table = $('#wardsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('wards.index') }}',
                    data: function(d) {
                        d.facility_id = $('#filter_facility').val();
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'facility_name',
                        name: 'facility_name'
                    },
                    {
                        data: 'rooms_count',
                        name: 'rooms_count',
                        searchable: false
                    },
                    {
                        data: 'beds_count',
                        name: 'beds_count',
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
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

            $('#filter_facility').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
