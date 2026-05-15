@extends('layouts.app')

@section('title', 'Facility Staff')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Facility Staff</h4>
                                <p class="text-muted mb-0">Manage staff members across all facilities</p>
                            </div>
                            <div>
                                <a href="{{ route('facility-staff.create') }}" class="btn btn-primary">
                                    <i class="fe fe-user-plus me-1"></i> Add Staff
                                </a>
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
                                <label class="form-label text-muted small">Filter by Facility</label>
                                <select class="form-select form-select-sm" id="facilityFilter">
                                    <option value="">All Facilities</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Filter by Position</label>
                                <select class="form-select form-select-sm" id="positionFilter">
                                    <option value="">All Positions</option>
                                    @foreach ($staffPositions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
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
                                                <h6 class="mb-0 text-white">Total Staff</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['total'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-users"></i>
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
                                                <h6 class="mb-0 text-white">With Photo</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['with_photo'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-camera"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="facilityStaffTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Facility</th>
                                        <th>Role</th>
                                        <th>Position</th>
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
            var table = $('#facilityStaffTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('facility-staff.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.facility_id = $('#facilityFilter').val();
                        d.staff_position_id = $('#positionFilter').val();
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
                        data: 'passport',
                        name: 'passport',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: function(data, type, row) {
                            return data ? data : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: 'facility_name',
                        name: 'facility_name'
                    },
                    {
                        data: 'role_name',
                        name: 'role_name'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name'
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
                    [1, 'asc']
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    search: "",
                    searchPlaceholder: "Search staff..."
                }
            });

            // Filter handlers
            $('#facilityFilter, #positionFilter').on('change', function() {
                table.ajax.reload();
            });

            $('#clearFilters').on('click', function() {
                $('#facilityFilter').val('');
                $('#positionFilter').val('');
                table.ajax.reload();
            });

            // Style the search input
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
        });
    </script>
@endpush
