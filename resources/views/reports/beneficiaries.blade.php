@extends('layouts.app')

@section('title', 'Beneficiaries Report')

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
                            Beneficiaries Report
                        </h2>
                        <div class="text-muted mt-1">Detailed statistical report of all beneficiaries</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.index') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to Reports
                            </a>
                            <a href="{{ route('reports.beneficiaries.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display: inline; margin-right: 0.25rem;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7,10 12,15 17,10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Export{{ $selectedProgram ? ' ('.$selectedProgram->name.')' : '' }}{{ (request('lga') || request('gender') || request('date_from') || request('date_to')) ? ' (Filtered)' : '' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Filters -->
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('reports.beneficiaries') }}" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label">Filter by Program</label>
                                <select name="program_id" class="form-select">
                                    <option value="">All Programs</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Filter by LGA</label>
                                <select name="lga" class="form-select">
                                    <option value="">All LGAs</option>
                                    @foreach($lgas as $l)
                                        <option value="{{ $l }}" {{ request('lga') == $l ? 'selected' : '' }}>
                                            {{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">All Genders</option>
                                    <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <div class="btn-list">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-filter me-1"></i>Filter
                                    </button>
                                </div>
                            </div>
                            @if(request('program_id') || request('lga') || request('gender') || request('date_from') || request('date_to'))
                                <div class="col-12 mt-2">
                                    <a href="{{ route('reports.beneficiaries') }}" class="btn btn-sm btn-ghost-secondary">
                                        <i class="ti ti-x"></i> Clear Filters
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row row-deck row-cards mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Principals (Primary)</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($totalPrincipals) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Primary enrollees</div>
                                    <div class="ms-auto">
                                        <span class="text-blue small"><i class="ti ti-users"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Spouses</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($totalSpouses) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Registered spouses</div>
                                    <div class="ms-auto">
                                        <span class="text-green small"><i class="ti ti-user-plus"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Children</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($totalChildren) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Registered children</div>
                                    <div class="ms-auto">
                                        <span class="text-purple small"><i class="ti ti-mood-kid"></i></span>
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
                                <div class="h3 mb-2">{{ number_format($totalEnrollments) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Overall beneficiaries</div>
                                    <div class="ms-auto">
                                        <span class="text-primary small"><i class="ti ti-chart-pie"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Breakdown -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Enrollments by Program</h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="row row-cards">
                            @foreach($programStats as $stat)
                                <div class="col-sm-6 col-md-4 col-xl-3">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-primary text-white avatar">
                                                        <i class="ti ti-briefcase"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">
                                                        {{ $stat->program_name }}
                                                    </div>
                                                    <div class="text-muted">
                                                        {{ number_format($stat->total) }} Total
                                                    </div>
                                                    <div class="text-muted small">
                                                        P: {{ number_format($stat->beneficiaries) }} | 
                                                        S: {{ number_format($stat->spouses) }} | 
                                                        C: {{ number_format($stat->children) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($programStats->isEmpty())
                                <div class="col-12 text-center text-muted">No program data available</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Beneficiaries Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Beneficiaries Data</h3>
                            </div>
                            <div class="card-body border-bottom py-3">
                                <div class="d-flex">
                                    <div class="ms-auto text-muted">
                                        Search:
                                        <div class="ms-2 d-inline-block">
                                            <input type="text" class="form-control form-control-sm"
                                                placeholder="Search beneficiaries..." id="searchInput">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap datatable">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">Facility Name</th>
                                            <th class="text-dark fw-semibold">LGA / Ward</th>
                                            <th class="text-dark fw-semibold">Type</th>
                                            <th class="text-dark fw-semibold">Beneficiaries (Principals)</th>
                                            <th class="text-dark fw-semibold">Spouses</th>
                                            <th class="text-dark fw-semibold">Children</th>
                                            <th class="text-dark fw-semibold">Total Dependents</th>
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
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-muted"><i class="ti ti-map-pin fs-5"></i> {{ $facility->lga ?? 'N/A' }}</div>
                                                    <div class="text-muted small">{{ $facility->ward ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-blue-lt">{{ $facility->type ?? 'General' }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-primary fw-bold">{{ number_format($facility->beneficiaries_count) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-success fw-bold">{{ number_format($facility->spouses_count) }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-info fw-bold">{{ number_format($facility->children_count) }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ number_format($facility->spouses_count + $facility->children_count) }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-list flex-nowrap">
                                                        <a href="{{ route('reports.facilities.show', $facility->id) }}{{ $programId ? '?program_id='.$programId : '' }}{{ request('gender') ? '&gender='.request('gender') : '' }}{{ request('date_from') ? '&date_from='.request('date_from') : '' }}{{ request('date_to') ? '&date_to='.request('date_to') : '' }}"
                                                            class="btn btn-sm btn-outline-primary" title="View Beneficiaries in this Facility">
                                                            <i class="ti ti-eye"></i> View
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No facilities with beneficiaries found for the selected criteria</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <div class="card-footer d-flex align-items-center">
                                <p class="m-0 text-muted">Showing <span>{{ $facilities->firstItem() ?? 0 }}</span> to <span>{{ $facilities->lastItem() ?? 0 }}</span> of <span>{{ $facilities->total() }}</span> entries</p>
                                <div class="ms-auto">
                                    {{ $facilities->links('pagination::bootstrap-4') }}
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
