@extends('layouts.app')

@section('title', 'Facility Change Audit')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle"><a href="{{ route('reports.index') }}" class="text-muted">Reports</a></div>
                    <h2 class="page-title">Facility Change Audit</h2>
                    <div class="text-muted mt-1">Beneficiaries whose facility has been changed</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('reports.facility-changes.export', request()->query()) }}" class="btn btn-primary">
                            <i class="ti ti-download me-1"></i> Export CSV
                        </a>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- Stats --}}
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Total Facility Changes</div>
                            <div class="h1 mb-0">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Beneficiaries Affected</div>
                            <div class="h1 mb-0">{{ number_format($stats['beneficiaries']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Last 30 Days</div>
                            <div class="h1 mb-0">{{ number_format($stats['last_30']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.facility-changes') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="BOSCHMA No, beneficiary ID or name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">From Facility</label>
                                <select name="old_facility_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}"
                                            {{ request('old_facility_id') == $facility->id ? 'selected' : '' }}>
                                            {{ $facility->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">To Facility</label>
                                <select name="new_facility_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}"
                                            {{ request('new_facility_id') == $facility->id ? 'selected' : '' }}>
                                            {{ $facility->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Source</label>
                                <select name="changed_via" class="form-select">
                                    <option value="">All</option>
                                    @foreach ($viaOptions as $via)
                                        <option value="{{ $via }}"
                                            {{ request('changed_via') == $via ? 'selected' : '' }}>{{ $via }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i></button>
                            </div>
                        </div>
                        @if (request()->hasAny(['search', 'old_facility_id', 'new_facility_id', 'changed_via', 'date_from', 'date_to']))
                            <div class="mt-2">
                                <a href="{{ route('reports.facility-changes') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>Clear filters
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Change Log
                        <span class="badge bg-blue-lt ms-2">{{ number_format($changes->total()) }}</span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-vcenter mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Beneficiary</th>
                                    <th class="border-0">Old Facility</th>
                                    <th class="border-0">New Facility</th>
                                    <th class="border-0">Changed By</th>
                                    <th class="border-0">Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($changes as $change)
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $change->created_at ? $change->created_at->format('d M Y H:i') : 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $change->beneficiary->fullname ?? 'N/A' }}</div>
                                            <div class="text-muted small">
                                                {{ $change->boschma_no ?? 'N/A' }}
                                                <span class="text-muted">· #{{ $change->beneficiary_id }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-lt">{{ $change->oldFacility->name ?? ($change->old_facility_id ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-lt">{{ $change->newFacility->name ?? ($change->new_facility_id ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            {{ $change->changedBy->fullname ?? ($change->changedBy->name ?? ($change->changed_by ?? 'System')) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-lt">{{ $change->changed_via ?? 'unknown' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="empty">
                                                <p class="empty-title">No facility changes found</p>
                                                <p class="empty-subtitle text-muted">Try adjusting the filters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($changes->hasPages())
                    <div class="card-footer">
                        {{ $changes->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
