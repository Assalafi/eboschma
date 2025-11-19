@extends('layouts.app')

@section('title', 'Facility Enrollments')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Reports</div>
                    <h2 class="page-title">{{ $facility->name }} - Enrollments</h2>
                    <div class="text-muted mt-1">View all beneficiary enrollments at {{ $facility->name }}</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('reports.facilities') }}" class="btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 0.25rem;">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12,19 5,12 12,5"></polyline>
                            </svg>
                            Back to Facilities
                        </a>
                        <a href="{{ route('reports.facilities.export') }}?facility_id={{ $facility->id }}" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 0.25rem;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7,10 12,15 17,10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Download Enrollments
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
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fw-semibold text-primary">Total Persons</div>
                                    <div class="h3 mb-0">{{ number_format($enrollments->count()) }}</div>
                                    <div class="text-muted small d-flex align-items-center mt-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                            <polyline points="23,6 13.5,15.5 8.5,10.5 1,18"></polyline>
                                            <polyline points="17,6 23,6 23,12"></polyline>
                                        </svg>
                                        <span>All categories</span>
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
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fw-semibold text-success">Principals</div>
                                    <div class="h3 mb-0">{{ number_format($enrollments->where('category', 'Principal')->count()) }}</div>
                                    <div class="text-muted small d-flex align-items-center mt-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12,6 12,12 16,14"></polyline>
                                        </svg>
                                        <span>Main beneficiaries</span>
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
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 11V6a3 3 0 1 1 6 0v5"></path>
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fw-semibold text-info">Spouses</div>
                                    <div class="h3 mb-0">{{ number_format($enrollments->where('category', 'Spouse')->count()) }}</div>
                                    <div class="text-muted small d-flex align-items-center mt-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                            <path d="M9 11V6a3 3 0 1 1 6 0v5"></path>
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        </svg>
                                        <span>Dependants</span>
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
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fw-semibold text-warning">Children</div>
                                    <div class="h3 mb-0">{{ number_format($enrollments->where('category', 'Child')->count()) }}</div>
                                    <div class="text-muted small d-flex align-items-center mt-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span>Minors</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0 bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h3 class="card-title mb-1">All Enrollments by Category</h3>
                                    <div class="text-muted">Principals, spouses, and children enrolled at this facility</div>
                                </div>
                                <div class="ms-auto">
                                    <button class="btn btn-sm" onclick="location.reload()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23,4 23,10 17,10"></polyline>
                                            <polyline points="1,20 1,14 7,14"></polyline>
                                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-vcenter">
                                <thead>
                                    <tr>
                                        <th>BOSCHMA ID</th>
                                        <th>Full Name</th>
                                        <th>Category</th>
                                        <th>Gender</th>
                                        <th>Phone</th>
                                        <th>Enumerator</th>
                                        <th>Status</th>
                                        <th>Enrollment Date</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($paginatedEnrollments as $enrollment)
                                    <tr>
                                        <td><span class="text-muted">{{ $enrollment->boschma_no ?? 'N/A' }}</span></td>
                                        <td>{{ $enrollment->fullname ?? 'N/A' }}</td>
                                        <td>
                                            @if ($enrollment->category == 'Principal')
                                                <span class="badge bg-primary text-white">Principal</span>
                                            @elseif ($enrollment->category == 'Spouse')
                                                <span class="badge bg-success text-white">Spouse</span>
                                            @elseif ($enrollment->category == 'Child')
                                                <span class="badge bg-info text-white">Child</span>
                                            @endif
                                        </td>
                                        <td>{{ $enrollment->gender ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->phone_no ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->creator->fullname ?? 'N/A' }}</td>
                                        <td>
                                            @if ($enrollment->status == 'active')
                                                <span class="badge bg-success text-white">Active</span>
                                            @elseif ($enrollment->status == 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-secondary text-white">{{ ucfirst($enrollment->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $enrollment->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('beneficiaries.show', $enrollment->beneficiary_id ?? $enrollment->id) }}" class="btn" title="View Details">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No enrollments found for this facility</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                                <div>
                                    <p class="text-muted mb-0">
                                        Showing {{ $paginatedEnrollments->firstItem() ?? 0 }} to {{ $paginatedEnrollments->lastItem() ?? 0 }} 
                                        of {{ $paginatedEnrollments->total() }} entries
                                    </p>
                                </div>
                                <div class="overflow-auto w-100 w-md-auto">
                                    @if ($paginatedEnrollments->hasPages())
                                        <nav aria-label="Facility enrollments pagination">
                                            <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                                {{-- Previous Page Link --}}
                                                @if ($paginatedEnrollments->onFirstPage())
                                                    <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                                @else
                                                    <li class="page-item"><a class="page-link" href="{{ $paginatedEnrollments->previousPageUrl() }}" rel="prev">Prev</a></li>
                                                @endif

                                                {{-- Pagination Elements with Smart Window --}}
                                                @php
                                                    $currentPage = $paginatedEnrollments->currentPage();
                                                    $lastPage = $paginatedEnrollments->lastPage();
                                                    $onEachSide = 2; // Show 2 pages on each side of current page
                                                    
                                                    // Calculate start and end of the sliding window
                                                    $start = max(1, $currentPage - $onEachSide);
                                                    $end = min($lastPage, $currentPage + $onEachSide);
                                                    
                                                    // Adjust if we're near the beginning or end
                                                    if ($currentPage <= $onEachSide + 1) {
                                                        $end = min($lastPage, ($onEachSide * 2) + 2);
                                                    }
                                                    if ($currentPage >= $lastPage - $onEachSide) {
                                                        $start = max(1, $lastPage - ($onEachSide * 2) - 1);
                                                    }
                                                @endphp

                                                {{-- First Page --}}
                                                @if ($start > 1)
                                                    <li class="page-item"><a class="page-link" href="{{ $paginatedEnrollments->url(1) }}">1</a></li>
                                                    @if ($start > 2)
                                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                                    @endif
                                                @endif

                                                {{-- Page Number Links --}}
                                                @for ($page = $start; $page <= $end; $page++)
                                                    @if ($page == $currentPage)
                                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                                    @else
                                                        <li class="page-item"><a class="page-link" href="{{ $paginatedEnrollments->url($page) }}">{{ $page }}</a></li>
                                                    @endif
                                                @endfor

                                                {{-- Last Page --}}
                                                @if ($end < $lastPage)
                                                    @if ($end < $lastPage - 1)
                                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                                    @endif
                                                    <li class="page-item"><a class="page-link" href="{{ $paginatedEnrollments->url($lastPage) }}">{{ $lastPage }}</a></li>
                                                @endif

                                                {{-- Next Page Link --}}
                                                @if ($paginatedEnrollments->hasMorePages())
                                                    <li class="page-item"><a class="page-link" href="{{ $paginatedEnrollments->nextPageUrl() }}" rel="next">Next</a></li>
                                                @else
                                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                                @endif
                                            </ul>
                                        </nav>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
