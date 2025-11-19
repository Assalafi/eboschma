@extends('layouts.app')

@section('title', 'Enumerator Performance Report')

@section('content')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Reports</div>
                        <h2 class="page-title">Enumerator Performance</h2>
                        <div class="text-muted mt-1">Track enrollment statistics and facility coverage for each enumerator
                        </div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.index') }}" class="btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display: inline; margin-right: 0.25rem;">
                                    <line x1="19" y1="12" x2="5" y2="12"></line>
                                    <polyline points="12,19 5,12 12,5"></polyline>
                                </svg>
                                Back to Overview
                            </a>
                            <a href="{{ route('reports.enumerators.export') }}" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display: inline; margin-right: 0.25rem;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7,10 12,15 17,10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Export Data
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
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-primary text-white avatar">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">Total Enumerators</div>
                                        <div class="text-muted">{{ number_format($enumerators->count()) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-green text-white avatar">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                                <line x1="23" y1="11" x2="17" y2="11"></line>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">Total Enrollments</div>
                                        <div class="text-muted">
                                            {{ number_format($enumerators->sum('beneficiaries_count')) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-blue text-white avatar">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">Unique Facilities</div>
                                        <div class="text-muted">
                                            {{ number_format($enumerators->sum('unique_facilities_count')) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-orange text-white avatar">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="23,6 13.5,15.5 8.5,10.5 1,18"></polyline>
                                                <polyline points="17,6 23,6 23,12"></polyline>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">Average per Month</div>
                                        <div class="text-muted">
                                            {{ number_format($enumerators->avg('beneficiaries_count'), 1) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enumerators Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Performance Metrics</h3>
                                <div class="card-actions">
                                    <button class="btn btn-sm" onclick="location.reload()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="23,4 23,10 17,10"></polyline>
                                            <polyline points="1,20 1,14 7,14"></polyline>
                                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body border-bottom py-3">
                                <div class="d-flex">
                                    <div class="text-muted">
                                        Show
                                        <div class="mx-2 d-inline-block">
                                            <select class="form-select form-select-sm">
                                                <option value="20">20</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                        entries
                                    </div>
                                    <div class="ms-auto text-muted">
                                        Search:
                                        <div class="ms-2 d-inline-block">
                                            <input type="text" class="form-control form-control-sm"
                                                placeholder="Search enumerators..." id="searchInput">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap datatable">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">#</th>
                                            <th class="text-dark fw-semibold">Enumerator Name</th>
                                            <th class="text-dark fw-semibold">Total Enrollments</th>
                                            <th class="text-dark fw-semibold">Unique Facilities</th>
                                            <th class="text-dark fw-semibold">Performance</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($enumerators as $enumerator)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex py-1 align-items-center">

                                                        <div class="flex-fill">
                                                            <div class="font-weight-medium">
                                                                {{ $enumerator->fullname ?? 'N/A' }}</div>
                                                            <div class="text-muted">{{ $enumerator->email ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="text-green fw-bold">{{ $enumerator->beneficiaries_count }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-blue fw-bold">{{ $enumerator->unique_facilities_count }}</span>
                                                </td>
                                                <td>
                                                    @if ($enumerator->beneficiaries_count > 50)
                                                        <span class="badge bg-success text-white">Excellent</span>
                                                    @elseif ($enumerator->beneficiaries_count > 20)
                                                        <span class="badge bg-primary text-white">Good</span>
                                                    @elseif ($enumerator->beneficiaries_count > 0)
                                                        <span class="badge bg-warning text-dark">Average</span>
                                                    @else
                                                        <span class="badge bg-secondary text-white">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-list flex-nowrap">
                                                        <a href="{{ route('reports.enumerators.enrollments', $enumerator->id) }}"
                                                            class="btn" title="View Enrollments">
                                                            <svg width="16" height="16" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                                                </path>
                                                                <circle cx="12" cy="12" r="3"></circle>
                                                            </svg>
                                                        </a>
                                                        <a href="{{ route('reports.enumerators.enrollments.export', $enumerator->id) }}"
                                                            class="btn" title="Download Enrollments">
                                                            <svg width="16" height="16" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                <polyline points="7,10 12,15 17,10"></polyline>
                                                                <line x1="12" y1="15" x2="12"
                                                                    y2="3"></line>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No enumerators found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <div class="card-footer">
                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                                    <div>
                                        <p class="text-muted mb-0">
                                            Showing {{ $enumerators->firstItem() ?? 0 }} to
                                            {{ $enumerators->lastItem() ?? 0 }}
                                            of {{ $enumerators->total() }} entries
                                        </p>
                                    </div>
                                    <div class="overflow-auto w-100 w-md-auto">
                                        @if ($enumerators->hasPages())
                                            <nav aria-label="Enumerators pagination">
                                                <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                                    {{-- Previous Page Link --}}
                                                    @if ($enumerators->onFirstPage())
                                                        <li class="page-item disabled"><span class="page-link">Prev</span>
                                                        </li>
                                                    @else
                                                        <li class="page-item"><a class="page-link"
                                                                href="{{ $enumerators->previousPageUrl() }}"
                                                                rel="prev">Prev</a></li>
                                                    @endif

                                                    {{-- Pagination Elements with Smart Window --}}
                                                    @php
                                                        $currentPage = $enumerators->currentPage();
                                                        $lastPage = $enumerators->lastPage();
                                                        $onEachSide = 2; // Show 2 pages on each side of current page

                                                        // Calculate start and end of the sliding window
                                                        $start = max(1, $currentPage - $onEachSide);
                                                        $end = min($lastPage, $currentPage + $onEachSide);

                                                        // Adjust if we're near the beginning or end
                                                        if ($currentPage <= $onEachSide + 1) {
                                                            $end = min($lastPage, $onEachSide * 2 + 2);
                                                        }
                                                        if ($currentPage >= $lastPage - $onEachSide) {
                                                            $start = max(1, $lastPage - $onEachSide * 2 - 1);
                                                        }
                                                    @endphp

                                                    {{-- First Page --}}
                                                    @if ($start > 1)
                                                        <li class="page-item"><a class="page-link"
                                                                href="{{ $enumerators->url(1) }}">1</a></li>
                                                        @if ($start > 2)
                                                            <li class="page-item disabled"><span
                                                                    class="page-link">...</span></li>
                                                        @endif
                                                    @endif

                                                    {{-- Page Number Links --}}
                                                    @for ($page = $start; $page <= $end; $page++)
                                                        @if ($page == $currentPage)
                                                            <li class="page-item active"><span
                                                                    class="page-link">{{ $page }}</span></li>
                                                        @else
                                                            <li class="page-item"><a class="page-link"
                                                                    href="{{ $enumerators->url($page) }}">{{ $page }}</a>
                                                            </li>
                                                        @endif
                                                    @endfor

                                                    {{-- Last Page --}}
                                                    @if ($end < $lastPage)
                                                        @if ($end < $lastPage - 1)
                                                            <li class="page-item disabled"><span
                                                                    class="page-link">...</span></li>
                                                        @endif
                                                        <li class="page-item"><a class="page-link"
                                                                href="{{ $enumerators->url($lastPage) }}">{{ $lastPage }}</a>
                                                        </li>
                                                    @endif

                                                    {{-- Next Page Link --}}
                                                    @if ($enumerators->hasMorePages())
                                                        <li class="page-item"><a class="page-link"
                                                                href="{{ $enumerators->nextPageUrl() }}"
                                                                rel="next">Next</a></li>
                                                    @else
                                                        <li class="page-item disabled"><span class="page-link">Next</span>
                                                        </li>
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
    </div>

    @section('scripts')
        <script>
            // Search functionality
            document.getElementById('searchInput')?.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchValue) ? '' : 'none';
                });
            });
        </script>
    @endsection
@endsection
