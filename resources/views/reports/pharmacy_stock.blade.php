@extends('layouts.app')

@section('title', 'Pharmacy Stock Report')

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
                            Pharmacy Stock Report
                        </h2>
                        <div class="text-muted mt-1">Overview of pharmacy activities and stock records</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Filter -->
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('reports.pharmacy_stock') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Filter by Facility</label>
                                <select name="facility_id" class="form-select">
                                    <option value="">All Facilities</option>
                                    @foreach($facilities as $facility)
                                        <option value="{{ $facility->id }}" {{ $facilityId == $facility->id ? 'selected' : '' }}>
                                            {{ $facility->name }}
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
                            <div class="col-md-2">
                                <div class="btn-list">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-filter me-1"></i>Filter
                                    </button>
                                    @if($facilityId || request('date_from') || request('date_to'))
                                        <a href="{{ route('reports.pharmacy_stock') }}" class="btn btn-ghost-secondary">
                                            <i class="ti ti-x"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @php
                    $stats = $facilityId ? $facilityStats : $globalStats;
                    $statsTitle = $facilityId ? 'Stats for ' . ($selectedFacility->name ?? 'Facility') : 'Global Pharmacy Stats';
                @endphp

                <h3 class="mb-3">{{ $statsTitle }}</h3>

                <!-- Summary Cards -->
                <div class="row row-deck row-cards mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Received</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($stats['total_received'] ?? 0) }}</div>
                                <div class="text-muted small">Total quantity received</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Remaining</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($stats['total_remaining'] ?? 0) }}</div>
                                <div class="text-muted small">Drugs currently in stock</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Dispensed</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($stats['total_dispensed'] ?? 0) }}</div>
                                <div class="text-muted small">Quantity dispensed to patients</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Value</div>
                                </div>
                                <div class="h3 mb-2">₦{{ number_format($stats['total_value'] ?? 0, 2) }}</div>
                                <div class="text-muted small">Value of remaining stock</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($facilityId && $stockRecords)
                <!-- Stock Records Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Stock Records for {{ $selectedFacility->name ?? 'Facility' }}</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap datatable">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">Drug Name</th>
                                            <th class="text-dark fw-semibold">Batch Number</th>
                                            <th class="text-dark fw-semibold">Received</th>
                                            <th class="text-dark fw-semibold">Remaining</th>
                                            <th class="text-dark fw-semibold">Dispensed</th>
                                            <th class="text-dark fw-semibold">Unit Cost (₦)</th>
                                            <th class="text-dark fw-semibold">Total Value (₦)</th>
                                            <th class="text-dark fw-semibold">Expiry Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($stockRecords as $record)
                                            <tr>
                                                <td>{{ $record->drug ? $record->drug->name : 'N/A' }}</td>
                                                <td>{{ $record->batch_number }}</td>
                                                <td>
                                                    <span class="text-primary fw-bold">{{ number_format($record->quantity_received) }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-success fw-bold">{{ number_format($record->quantity_remaining) }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-orange fw-bold">{{ number_format($record->quantity_received - $record->quantity_remaining) }}</span>
                                                </td>
                                                <td>{{ number_format($record->unit_cost, 2) }}</td>
                                                <td>{{ number_format($record->quantity_remaining * $record->unit_cost, 2) }}</td>
                                                <td>
                                                    @if($record->expiry_date)
                                                        {{ $record->expiry_date->format('Y-m-d') }}
                                                        @if($record->expiry_date->isPast())
                                                            <span class="badge bg-danger ms-2">Expired</span>
                                                        @elseif($record->expiry_date->diffInDays(now()) <= 30)
                                                            <span class="badge bg-warning ms-2">Expiring Soon</span>
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">No stock records found for this facility</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer d-flex align-items-center">
                                {{ $stockRecords->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                @elseif(!$facilityId)
                <div class="alert alert-info">
                    Please select a facility from the filter above to view detailed stock records.
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
