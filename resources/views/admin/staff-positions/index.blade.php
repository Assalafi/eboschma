@extends('layouts.app')

@section('title', 'Staff Positions')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Facility Staff Positions</h4>
                                <p class="text-muted mb-0">Manage positions for facility staff members</p>
                            </div>
                            <div>
                                <a href="{{ route('staff-positions.bulk.create') }}" class="btn btn-success me-2">
                                    <i class="fe fe-layers me-1"></i> Bulk Create
                                </a>
                                <a href="{{ route('staff-positions.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-1"></i> Add Position
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

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 text-white">Total Positions</h6>
                                                <h3 class="mb-0 text-white">{{ $stats['total'] }}</h3>
                                            </div>
                                            <div class="fs-1">
                                                <i class="fe fe-briefcase"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="staffPositionsTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Position Name</th>
                                        <th>Description</th>
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
            $('#staffPositionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('staff-positions.index') }}",
                    type: 'GET',
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
                            return data ? data : '<span class="text-muted">-</span>';
                        }
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
                    searchPlaceholder: "Search positions..."
                }
            });

            // Style the search input
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
        });
    </script>
@endpush
