@extends('layouts.app')

@section('title', 'Referrals Management')

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">Referrals Management</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Referrals</li>
                        </ol>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Total Referrals</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['total'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>All referrals (in/out)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Outgoing</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['outgoing'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>📤 Referred to other facilities</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Incoming</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['incoming'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>📥 Referred to this facility</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Pending</div>
                                </div>
                                <div class="h1 mb-3">{{ $stats['pending'] ?? 0 }}</div>
                                <div class="d-flex mb-2">
                                    <div>Awaiting action</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrals Table -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">All Referrals</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="referralsTable" class="table table-bordered table-hover text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Auth code</th>
                                                <th>Patient</th>
                                                <th>Facility</th>
                                                <th>Service</th>
                                                <th>Status</th>
                                                <th>Date</th>
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
        </div>
    </div>

    @push('scripts')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#referralsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('referrals.index') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'referral_info',
                            name: 'id'
                        },
                        {
                            data: 'patient_info',
                            name: 'encounter.patient.firstname',
                            orderable: false
                        },
                        {
                            data: 'facility_info',
                            name: 'from_facility_id',
                            orderable: false
                        },
                        {
                            data: 'reason',
                            name: 'reason'
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'date',
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
                        [5, 'desc']
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
