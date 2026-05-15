@extends('layouts.app')

@section('title', 'Reports Overview')

@section('content')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Reports</div>
                        <h2 class="page-title">Dashboard Overview</h2>
                        <div class="text-muted mt-1">Monitor enrollment performance and system metrics</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="#" class="btn" onclick="location.reload()">
                                <i class="fas fa-sync"></i>
                                Refresh
                            </a>
                            <a href="{{ route('reports.export') }}" class="btn btn-primary">
                                <i class="fas fa-download"></i>
                                Export Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Key Metrics -->
                <div class="row row-deck row-cards mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm border-0 shadow-sm hover-lift">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-md bg-primary bg-gradient text-white rounded-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fw-semibold text-primary">Total Beneficiaries</div>
                                        <div class="h3 mb-0">{{ number_format($stats['total_beneficiaries']) }}</div>
                                        <div class="text-muted small d-flex align-items-center mt-1">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" class="me-1">
                                                <polyline points="23,6 13.5,15.5 8.5,10.5 1,18"></polyline>
                                                <polyline points="17,6 23,6 23,12"></polyline>
                                            </svg>
                                            <span>All time enrollments</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm border-0 shadow-sm hover-lift">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-md bg-success bg-gradient text-white rounded-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                                <line x1="23" y1="11" x2="17" y2="11"></line>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fw-semibold text-success">Active Enumerators</div>
                                        <div class="h3 mb-0">{{ number_format($stats['total_enumerators']) }}</div>
                                        <div class="text-muted small d-flex align-items-center mt-1">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" class="me-1">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12,6 12,12 16,14"></polyline>
                                            </svg>
                                            <span>Field staff members</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm border-0 shadow-sm hover-lift">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-md bg-info bg-gradient text-white rounded-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fw-semibold text-info">Health Facilities</div>
                                        <div class="h3 mb-0">{{ number_format($stats['total_facilities']) }}</div>
                                        <div class="text-muted small d-flex align-items-center mt-1">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" class="me-1">
                                                <path
                                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                                </path>
                                            </svg>
                                            <span>Coverage locations</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm border-0 shadow-sm hover-lift">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-md bg-warning bg-gradient text-white rounded-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12,6 12,12 16,14"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fw-semibold text-warning">Pending Enrollments</div>
                                        <div class="h3 mb-0">{{ number_format($stats['pending_enrollments']) }}</div>
                                        <div class="text-muted small d-flex align-items-center mt-1">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" class="me-1">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12">
                                                </line>
                                                <line x1="12" y1="16" x2="12.01" y2="16">
                                                </line>
                                            </svg>
                                            <span>Awaiting approval</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Reports -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-4">
                            <div>
                                <h3 class="page-title mb-1">Available Reports</h3>
                                <div class="text-muted">Click on any report to view detailed analytics and insights</div>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-info text-white">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" class="me-1">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2">
                                        </rect>
                                        <line x1="9" y1="9" x2="15" y2="9"></line>
                                        <line x1="9" y1="15" x2="15" y2="15"></line>
                                    </svg>
                                    4 Active Reports
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm hover-lift h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-lg bg-primary bg-gradient text-white rounded-3 me-3">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="card-title mb-1">Enumerator Performance</h4>
                                        <div class="badge bg-primary bg-opacity-10 text-primary mb-2">Staff Analytics</div>
                                    </div>
                                </div>
                                <p class="text-muted mb-4">Track enrollment statistics, facility coverage, and performance
                                    metrics for each field enumerator</p>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-primary">{{ $stats['total_enumerators'] }}</div>
                                            <div class="text-muted small">Active Staff</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-primary">{{ $stats['total_beneficiaries'] }}</div>
                                            <div class="text-muted small">Total Enrollments</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="{{ route('reports.enumerators') }}" class="btn btn-primary w-100">
                                    View Performance Report
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" class="ms-1">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12,5 19,12 12,19"></polyline>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm hover-lift h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-lg bg-success bg-gradient text-white rounded-3 me-3">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="card-title mb-1">Facility Performance</h4>
                                        <div class="badge bg-success bg-opacity-10 text-light mb-2">Location Analytics
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted mb-4">Monitor enrollment activity, coverage rates, and performance
                                    metrics across all healthcare facilities</p>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-success">{{ $stats['total_facilities'] }}</div>
                                            <div class="text-muted small">Total Facilities</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-success">100%</div>
                                            <div class="text-muted small">Coverage Rate</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="{{ route('reports.facilities') }}" class="btn btn-success w-100">
                                    View Facility Report
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" class="ms-1">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12,5 19,12 12,19"></polyline>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm hover-lift h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-lg bg-info bg-gradient text-white rounded-3 me-3">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="23,6 13.5,15.5 8.5,10.5 1,18"></polyline>
                                            <polyline points="17,6 23,6 23,12"></polyline>
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="card-title mb-1">Enrollment Trends</h4>
                                        <div class="badge bg-info text-light mb-2">Growth Analytics</div>
                                    </div>
                                </div>
                                <p class="text-muted mb-4">Analyze monthly enrollment patterns, growth metrics, and
                                    trending data across all beneficiaries</p>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-info">{{ $stats['active_enrollments'] }}</div>
                                            <div class="text-muted small">Active</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-info">{{ $stats['pending_enrollments'] }}</div>
                                            <div class="text-muted small">Pending</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="{{ route('reports.enrollments') }}" class="btn btn-info w-100">
                                    View Trends Report
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" class="ms-1">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12,5 19,12 12,19"></polyline>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm hover-lift h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-lg bg-danger bg-gradient text-white rounded-3 me-3">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="card-title mb-1">Customer Care Report</h4>
                                        <div class="badge bg-light bg-opacity-10 text-danger mb-2">Support Analytics</div>
                                    </div>
                                </div>
                                <p class="text-muted mb-4">Analyze customer support tickets, resolution times, categories,
                                    and department performance metrics</p>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-danger">{{ App\Models\Ticket::count() }}</div>
                                            <div class="text-muted small">Total Tickets</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 mb-1 text-danger">
                                                {{ App\Models\Ticket::status('completed')->count() }}</div>
                                            <div class="text-muted small">Resolved</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="{{ route('reports.crm') }}" class="btn btn-danger w-100">
                                    View CRM Report
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" class="ms-1">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12,5 19,12 12,19"></polyline>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
