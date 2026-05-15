@extends('layouts.facility')

@section('title', 'Facility Claims')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Claims Management</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Claims</li>
                </ol>
            </div>
            <div class="page-rightheader ml-auto">
                <a href="{{ route('facility.claims.billable') }}" class="btn btn-success">
                    <i class="ti-plus mr-1"></i> Create New Claim
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Claims</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="claimsTable" class="table table-bordered table-hover text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Claim Info</th>
                                        <th>Encounter Info</th>
                                        <th>Amounts</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
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
    @push('scripts')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#claimsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('facility.claims.list') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'claim_info',
                            name: 'claim_number'
                        },
                        {
                            data: 'encounter_info',
                            name: 'service_date'
                        },
                        {
                            data: 'amounts',
                            name: 'total_amount',
                            orderable: false
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [4, 'desc']
                    ],
                    pageLength: 25,
                    language: {
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                    }
                });
            });

            function deleteClaim(id) {
                if (confirm('Are you sure you want to delete this claim? This action cannot be undone.')) {
                    $.ajax({
                        url: '/facility/claims/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert(response.success);
                            $('#claimsTable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON.error || 'Error deleting claim');
                        }
                    });
                }
            }
        </script>
    @endpush
@endsection
