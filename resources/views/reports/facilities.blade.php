@extends('layouts.app')

@section('title', 'Facility Performance Report')

@section('content')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            <a href="{{ route('reports.index') }}">Reports</a>
                        </div>
                        <h2 class="page-title">
                            Facility Performance
                        </h2>
                        <div class="text-muted mt-1">Enrollment statistics by healthcare facility</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.index') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to Reports
                            </a>
                            <a href="{{ route('reports.facilities.export') }}{{ $programId ? '?program_id='.$programId : '' }}{{ request('lga') ? ($programId ? '&' : '?').'lga='.request('lga') : '' }}{{ request('date_from') ? (($programId || request('lga')) ? '&' : '?').'date_from='.request('date_from') : '' }}{{ request('date_to') ? (($programId || request('lga') || request('date_from')) ? '&' : '?').'date_to='.request('date_to') : '' }}" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display: inline; margin-right: 0.25rem;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7,10 12,15 17,10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Export{{ $selectedProgram ? ' ('.$selectedProgram->name.')' : '' }}{{ (request('lga') || request('date_from') || request('date_to')) ? ' (Filtered)' : '' }}
                            </a>
                            <a href="{{ route('reports.facilities.export.all') }}{{ $programId ? '?program_id='.$programId : '' }}{{ request('lga') ? ($programId ? '&' : '?').'lga='.request('lga') : '' }}{{ request('date_from') ? (($programId || request('lga')) ? '&' : '?').'date_from='.request('date_from') : '' }}{{ request('date_to') ? (($programId || request('lga') || request('date_from')) ? '&' : '?').'date_to='.request('date_to') : '' }}"
                               class="btn btn-success"
                               onclick="return confirm('This will export all beneficiary records across all facilities. This may take a few minutes for large datasets. Continue?')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display: inline; margin-right: 0.25rem;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7,10 12,15 17,10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Export All (CSV){{ (request('lga') || request('date_from') || request('date_to') || $selectedProgram) ? ' – Filtered' : '' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Program Filter -->
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('reports.facilities') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Filter by Program</label>
                                <select name="program_id" class="form-select">
                                    <option value="">All Programs</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filter by LGA</label>
                                <select name="lga" class="form-select">
                                    <option value="">All LGAs</option>
                                    @foreach($lgas as $lga)
                                        <option value="{{ $lga }}" {{ request('lga') == $lga ? 'selected' : '' }}>
                                            {{ $lga }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <div class="btn-list">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-filter me-1"></i>Filter
                                    </button>
                                    @if($selectedProgram || request('lga') || request('date_from') || request('date_to'))
                                        <a href="{{ route('reports.facilities') }}" class="btn btn-ghost-secondary">
                                            <i class="ti ti-x"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        @if($selectedProgram || request('lga') || request('date_from') || request('date_to'))
                            <div class="mt-2">
                                @if($selectedProgram)
                                    <span class="badge bg-primary fs-6 me-2">
                                        <i class="ti ti-filter me-1"></i>{{ $selectedProgram->name }}
                                    </span>
                                @endif
                                @if(request('lga'))
                                    <span class="badge bg-secondary fs-6 me-2">
                                        LGA: {{ request('lga') }}
                                    </span>
                                @endif
                                @if(request('date_from'))
                                    <span class="badge bg-info fs-6 me-2">
                                        From: {{ request('date_from') }}
                                    </span>
                                @endif
                                @if(request('date_to'))
                                    <span class="badge bg-info fs-6 me-2">
                                        To: {{ request('date_to') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row row-deck row-cards mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Facilities</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($facilities->count()) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Healthcare centers</div>
                                    <div class="ms-auto">
                                        <span class="text-blue small">
                                            {{ number_format($facilities->count()) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Enrollments</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($facilities->sum('beneficiaries_count')) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">All facilities</div>
                                    <div class="ms-auto">
                                        <span class="text-blue small">
                                            {{ number_format($facilities->sum('beneficiaries_count')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Spouses</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($facilities->sum('spouses_count')) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Spouse enrollments</div>
                                    <div class="ms-auto">
                                        <span class="text-blue small">
                                            {{ number_format($facilities->sum('spouses_count')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Children</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($facilities->sum('children_count')) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Children enrollments</div>
                                    <div class="ms-auto">
                                        <span class="text-purple small">
                                            {{ number_format($facilities->sum('children_count')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Facility Performance Details</h3>
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
                                                placeholder="Search facilities..." id="searchInput">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap datatable">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">Facility Name</th>
                                            <th class="text-dark fw-semibold">Type</th>
                                            <th class="text-dark fw-semibold">LGA</th>
                                            <th class="text-dark fw-semibold">Beneficiaries</th>
                                            <th class="text-dark fw-semibold">Spouses</th>
                                            <th class="text-dark fw-semibold">Children</th>
                                            <th class="text-dark fw-semibold">Performance</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($facilities as $facility)
                                            <tr>
                                                <td>
                                                    <div class="d-flex py-1 align-items-center">
                                                        <span class="avatar me-2"
                                                            style="background-color: #{{ substr(md5($facility->name), 0, 6) }}; color: white;">
                                                            {{ strtoupper(substr($facility->name, 0, 2)) }}
                                                        </span>
                                                        <div class="flex-fill">
                                                            <div class="font-weight-medium">{{ $facility->name }}</div>
                                                            <div class="text-muted">{{ $facility->ward }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="">{{ $facility->type ?? 'General' }}</span>
                                                </td>
                                                <td>{{ $facility->lga ?? '--' }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="text-green fw-bold">{{ $facility->beneficiaries_count }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-blue fw-bold">{{ $facility->spouses_count ?? 0 }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-purple fw-bold">{{ $facility->children_count ?? 0 }}</span>
                                                </td>
                                                <td>
                                                    @if ($facility->total_enrollments > 100)
                                                        <span class="badge bg-success text-white">
                                                            {{ $facility->total_enrollments }} : High</span>
                                                    @elseif ($facility->total_enrollments > 50)
                                                        <span class="badge bg-primary text-white">
                                                            {{ $facility->total_enrollments }} : Medium</span>
                                                    @elseif ($facility->total_enrollments > 0)
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $facility->total_enrollments }} : Low</span>
                                                    @else
                                                        <span class="badge bg-secondary text-white">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-list flex-nowrap">
                                                        <a href="{{ route('reports.facilities.show', $facility->id) }}{{ $programId ? '?program_id='.$programId : '' }}"
                                                            class="btn" title="View Details">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                        <a href="{{ route('reports.facilities.export') }}?facility_id={{ $facility->id }}{{ $programId ? '&program_id='.$programId : '' }}{{ request('lga') ? '&lga='.request('lga') : '' }}{{ request('date_from') ? '&date_from='.request('date_from') : '' }}{{ request('date_to') ? '&date_to='.request('date_to') : '' }}"
                                                            class="btn" title="Export Data">
                                                            <i class="ti ti-download"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No facilities found</td>
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
                                            Showing {{ $facilities->firstItem() ?? 0 }} to {{ $facilities->lastItem() ?? 0 }} 
                                            of {{ $facilities->total() }} entries
                                        </p>
                                    </div>
                                    <div class="overflow-auto w-100 w-md-auto">
                                        @if ($facilities->hasPages())
                                            <nav aria-label="Facilities pagination">
                                                <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                                    {{-- Previous Page Link --}}
                                                    @if ($facilities->onFirstPage())
                                                        <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                                    @else
                                                        <li class="page-item"><a class="page-link" href="{{ $facilities->previousPageUrl() }}" rel="prev">Prev</a></li>
                                                    @endif

                                                    {{-- Pagination Elements with Smart Window --}}
                                                    @php
                                                        $currentPage = $facilities->currentPage();
                                                        $lastPage = $facilities->lastPage();
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
                                                        <li class="page-item"><a class="page-link" href="{{ $facilities->url(1) }}">1</a></li>
                                                        @if ($start > 2)
                                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                                        @endif
                                                    @endif

                                                    {{-- Page Number Links --}}
                                                    @for ($page = $start; $page <= $end; $page++)
                                                        @if ($page == $currentPage)
                                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                                        @else
                                                            <li class="page-item"><a class="page-link" href="{{ $facilities->url($page) }}">{{ $page }}</a></li>
                                                        @endif
                                                    @endfor

                                                    {{-- Last Page --}}
                                                    @if ($end < $lastPage)
                                                        @if ($end < $lastPage - 1)
                                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                                        @endif
                                                        <li class="page-item"><a class="page-link" href="{{ $facilities->url($lastPage) }}">{{ $lastPage }}</a></li>
                                                    @endif

                                                    {{-- Next Page Link --}}
                                                    @if ($facilities->hasMorePages())
                                                        <li class="page-item"><a class="page-link" href="{{ $facilities->nextPageUrl() }}" rel="next">Next</a></li>
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
