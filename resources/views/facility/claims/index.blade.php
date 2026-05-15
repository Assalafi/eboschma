@extends('layouts.facility')

@section('title', 'Patient History & Activities')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Patient History & Activities</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Patient History</li>
                </ol>
            </div>
            <div class="page-rightheader ml-auto">
                <a href="{{ route('facility.claims.billable') }}" class="btn btn-success">
                    <i class="ti-money mr-1"></i> Billable Items
                </a>
                <a href="{{ route('facility.claims.list') }}" class="btn btn-primary">
                    <i class="ti-file-list mr-1"></i> View All Claims
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Encounters</h3>
                        <div class="card-options">
                            <span class="badge bg-info">Patient visit history & activities</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="encountersTable" class="table table-bordered table-hover text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Patient Info</th>
                                        <th>Visit Info</th>
                                        <th>Nature of Visit</th>
                                        <th>Status</th>
                                        <th>Consultation</th>
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
                $('#encountersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('facility.encounters.index') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'patient_info',
                            name: 'patient_id',
                            orderable: false
                        },
                        {
                            data: 'visit_info',
                            name: 'visit_date'
                        },
                        {
                            data: 'nature_of_visit',
                            name: 'nature_of_visit'
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'consultation_status',
                            name: 'consultation_status',
                            orderable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    pageLength: 25,
                    language: {
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                    }
                });
            });
        </script>
    @endpush
@endsection
