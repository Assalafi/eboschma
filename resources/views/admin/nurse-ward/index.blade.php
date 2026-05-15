@extends('layouts.app')

@section('title', 'Nurse Ward Assignments')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Nurse Ward Assignments</h4>
                                    <p class="text-muted mb-0">Manage nurse assignments to wards</p>
                                </div>
                                <div>
                                    @can('nurse-ward.create')
                                        <a href="{{ route('nurse-ward.create') }}" class="btn btn-primary">
                                            <i class="fe fe-plus me-1"></i> Assign Nurses
                                        </a>
                                    @endcan
                                </div>
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
                                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                                            <small>Active Assignments</small>
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
                                <div class="col-md-4">
                                    <select id="filter_ward" class="form-select">
                                        <option value="">All Wards</option>
                                        @foreach ($wards as $ward)
                                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="nurseWardTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nurse</th>
                                            <th>Email</th>
                                            <th>Ward</th>
                                            <th>Facility</th>
                                            <th>Assigned Date</th>
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
            var table = $('#nurseWardTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('nurse-ward.index') }}',
                    data: function(d) {
                        d.facility_id = $('#filter_facility').val();
                        d.ward_id = $('#filter_ward').val();
                    }
                },
                columns: [{
                        data: 'nurse_name',
                        name: 'nurse_name'
                    },
                    {
                        data: 'nurse_email',
                        name: 'nurse_email'
                    },
                    {
                        data: 'ward_name',
                        name: 'ward_name'
                    },
                    {
                        data: 'facility_name',
                        name: 'facility_name'
                    },
                    {
                        data: 'assigned_date_formatted',
                        name: 'assigned_date_formatted'
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

            $('#filter_facility, #filter_ward').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
