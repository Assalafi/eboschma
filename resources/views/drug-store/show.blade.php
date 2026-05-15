@extends('layouts.app')

@section('title', $drug->name . ' - Store Batches')

@push('styles')
    <style>
        .nav-tabs .nav-link.active {
            color: #206bc4 !important;
            background-color: #f8fafc !important;
            border-color: #dee2e6 #dee2e6 #f8fafc !important;
        }

        .nav-tabs .nav-link.active:hover {
            color: #206bc4 !important;
            background-color: #f8fafc !important;
            border-color: #dee2e6 #dee2e6 #f8fafc !important;
        }

        .nav-tabs .nav-link:not(.active):hover {
            color: #206bc4 !important;
            background-color: #f8fafc !important;
        }
    </style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Drug Store</div>
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>{{ $drug->name }}
                    </h2>
                    <div class="text-muted mt-1">{{ $drug->strength }} {{ $drug->unit }} &middot; {{ $drug->dosage_form }}
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-list">
                        <a href="{{ route('drug-store.index') }}" class="btn btn-secondary">
                            <i class="ti-arrow-left me-1"></i> Back to Store
                        </a>
                        <a href="{{ route('drug-store.stock-in-form') }}?drug_id={{ $drug->id }}"
                            class="btn btn-primary">
                            <i class="ti-plus me-1"></i> Stock In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Enhanced Summary Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Available Stock</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">Last 7 days</a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3 {{ $available < 0 ? 'text-danger' : '' }}">
                                {{ number_format($available) }}
                                @if ($available < 0)
                                    <small class="badge bg-danger ms-2">Deficit</small>
                                @endif
                            </div>
                            <div class="d-flex mb-2">
                                <div>{{ $available < 0 ? 'Units owed to store' : 'Units available' }}</div>
                            </div>
                            <div class="progress progress-sm">
                                @if ($totalReceived > 0 && $available >= 0)
                                    <div class="progress-bar bg-primary"
                                        style="width: {{ ($available / $totalReceived) * 100 }}%" role="progressbar"
                                        aria-valuenow="{{ $available }}" aria-valuemin="0"
                                        aria-valuemax="{{ $totalReceived }}">
                                        <span class="visually-hidden">{{ round(($available / $totalReceived) * 100) }}%
                                            Available</span>
                                    </div>
                                @elseif ($available < 0)
                                    <div class="progress-bar bg-danger" style="width: 100%" role="progressbar">
                                        <span class="visually-hidden">Deficit</span>
                                    </div>
                                @else
                                    <div class="progress-bar bg-primary" style="width: 0%" role="progressbar">
                                        <span class="visually-hidden">0% Available</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Received</div>
                                <div class="ms-auto">
                                    <i class="ti-arrow-down-circle text-success"></i>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ number_format($totalReceived) }}</div>
                            <div class="d-flex mb-2">
                                <div>Total units stocked</div>
                            </div>
                            @if (count($batches) > 0)
                                <div class="text-muted">
                                    {{ count($batches) }} batch{{ count($batches) > 1 ? 'es' : '' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Dispensed</div>
                                <div class="ms-auto">
                                    <i class="ti-arrow-up-circle text-danger"></i>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ number_format($totalDispensed) }}</div>
                            <div class="d-flex mb-2">
                                <div>Units sent to facilities</div>
                            </div>
                            @if ($dispensingHistory->count() > 0)
                                <div class="text-muted">
                                    {{ $dispensingHistory->count() }} dispensing
                                    record{{ $dispensingHistory->count() > 1 ? 's' : '' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Value</div>
                                <div class="ms-auto">
                                    <i class="ti-coin text-warning"></i>
                                </div>
                            </div>
                            <div class="h1 mb-3">
                                ₦{{ number_format($batches->sum(function ($batch) {return $batch->quantity_remaining * $batch->unit_cost;}),2) }}
                            </div>
                            <div class="d-flex mb-2">
                                <div>Current stock value</div>
                            </div>
                            @if ($available > 0)
                                <div class="text-muted">
                                    ₦{{ number_format($batches->sum(function ($batch) {return $batch->quantity_remaining * $batch->unit_cost;}) / $available,2) }}
                                    avg/unit
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for History -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                                <li class="nav-item">
                                    <a href="#batches" class="nav-link active" data-bs-toggle="tab" style="color: #206bc4;">
                                        <i class="ti-package me-2"></i>Current Batches ({{ count($batches) }})
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#stocking-history" class="nav-link" data-bs-toggle="tab"
                                        style="color: #495057;">
                                        <i class="ti-arrow-down-circle me-2"></i>Stocking History
                                        ({{ count($stockingHistory) }})
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#dispensing-history" class="nav-link" data-bs-toggle="tab"
                                        style="color: #495057;">
                                        <i class="ti-arrow-up-circle me-2"></i>Dispensing History
                                        ({{ $dispensingHistory->count() }})
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Current Batches Tab -->
                                <div class="tab-pane active show" id="batches">
                                    @if (count($batches) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-vcenter table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Batch Number</th>
                                                        <th>Program</th>
                                                        <th>Received</th>
                                                        <th>Remaining</th>
                                                        <th>Dispensed</th>
                                                        <th>Unit Cost</th>
                                                        <th>Total Value</th>
                                                        <th>Supplier</th>
                                                        <th>Expiry Date</th>
                                                        <th>Status</th>
                                                        <th>Stocked</th>
                                                        <th class="w-1">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($batches as $batch)
                                                        <tr>
                                                            <td class="fw-bold">{{ $batch->batch_number }}</td>
                                                            <td>{{ $batch->program->name ?? 'N/A' }}</td>
                                                            <td>{{ number_format($batch->quantity_received) }}</td>
                                                            <td>
                                                                @if ($batch->quantity_remaining == 0)
                                                                    <span class="text-muted">0</span>
                                                                @elseif($batch->quantity_remaining <= 10)
                                                                    <span
                                                                        class="text-warning fw-bold">{{ number_format($batch->quantity_remaining) }}</span>
                                                                @else
                                                                    <span
                                                                        class="text-success fw-bold">{{ number_format($batch->quantity_remaining) }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ number_format($batch->quantity_dispensed) }}</td>
                                                            <td>{{ $batch->formatted_unit_cost }}</td>
                                                            <td>{{ $batch->formatted_total_value }}</td>
                                                            <td>{{ $batch->supplier ?? '-' }}</td>
                                                            <td>
                                                                @php $expiry = $batch->expiry_status; @endphp
                                                                <span
                                                                    class="{{ $expiry['status'] == 'expired' ? 'text-danger' : ($expiry['status'] == 'near-expiry' ? 'text-warning' : 'text-success') }}">
                                                                    {{ $batch->expiry_date->format('M j, Y') }}
                                                                </span>
                                                                <div class="small">{!! $expiry['badge'] !!}</div>
                                                            </td>
                                                            <td>{!! $batch->status_badge !!}</td>
                                                            <td>
                                                                <div>{{ $batch->stocked_at->format('M j, Y') }}</div>
                                                                <div class="text-muted small">
                                                                    {{ $batch->stocked_at->format('g:i A') }}</div>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="{{ route('drug-store.edit', $batch->id) }}"
                                                                        class="btn btn-sm btn-info" title="Edit Batch">
                                                                        Edit
                                                                    </a>
                                                                    @if ($batch->quantity_dispensed == 0)
                                                                        <form method="POST"
                                                                            action="{{ route('drug-store.destroy', $batch->id) }}"
                                                                            style="display: inline;"
                                                                            onsubmit="return confirm('Are you sure you want to delete this stock batch?')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-danger"
                                                                                title="Delete Batch">
                                                                                <i class="ti-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    @else
                                                                        <button class="btn btn-sm btn-outline-secondary"
                                                                            disabled
                                                                            title="Cannot delete - partially dispensed">
                                                                            <i class="ti-trash"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="text-muted mb-3">
                                                <i class="ti-package" style="font-size: 3rem;"></i>
                                            </div>
                                            <h4>No stock batches found</h4>
                                            <p class="text-muted">This drug has no stock entries in the store yet.</p>
                                            <a href="{{ route('drug-store.stock-in-form') }}?drug_id={{ $drug->id }}"
                                                class="btn btn-primary">
                                                <i class="ti-plus me-1"></i> Add Stock
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Stocking History Tab -->
                                <div class="tab-pane" id="stocking-history">
                                    @if (count($stockingHistory) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-vcenter table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Batch Number</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Cost</th>
                                                        <th>Total Value</th>
                                                        <th>Supplier</th>
                                                        <th>Status</th>
                                                        <th>Stocked By</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($stockingHistory as $stock)
                                                        <tr>
                                                            <td>
                                                                <div>{{ $stock['stocked_at']->format('M j, Y') }}</div>
                                                                <div class="text-muted small">
                                                                    {{ $stock['stocked_at']->format('g:i A') }}</div>
                                                            </td>
                                                            <td class="fw-bold">{{ $stock['batch_number'] }}</td>
                                                            <td>
                                                                <div>{{ number_format($stock['quantity_received']) }}</div>
                                                                <div class="text-muted small">
                                                                    Remaining:
                                                                    {{ number_format($stock['quantity_remaining']) }}
                                                                </div>
                                                            </td>
                                                            <td>₦{{ number_format($stock['unit_cost'], 2) }}</td>
                                                            <td>₦{{ number_format($stock['total_value'], 2) }}</td>
                                                            <td>{{ $stock['supplier'] ?? '-' }}</td>
                                                            <td>
                                                                @if ($stock['status'] == 'active')
                                                                    <span class="badge bg-success">Active</span>
                                                                @elseif($stock['status'] == 'depleted')
                                                                    <span class="badge bg-warning">Depleted</span>
                                                                @else
                                                                    <span class="badge bg-danger">Expired</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $stock['stocked_by'] }}</td>
                                                            <td class="text-muted">{{ $stock['notes'] ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="text-muted mb-3">
                                                <i class="ti-history" style="font-size: 3rem;"></i>
                                            </div>
                                            <h4>No stocking history</h4>
                                            <p class="text-muted">This drug hasn't been stocked in the store yet.</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Dispensing History Tab -->
                                <div class="tab-pane" id="dispensing-history">
                                    @if ($dispensingHistory->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-vcenter table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Facility</th>
                                                        <th>Request ID</th>
                                                        <th>Batch Number</th>
                                                        <th>Quantity</th>
                                                        <th>Expiry Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($dispensingHistory as $dispense)
                                                        <tr>
                                                            <td>
                                                                <div>{{ $dispense->created_at->format('M j, Y') }}</div>
                                                                <div class="text-muted small">
                                                                    {{ $dispense->created_at->format('g:i A') }}</div>
                                                            </td>
                                                            <td class="fw-bold">{{ $dispense->facility_name }}</td>
                                                            <td>
                                                                <a href="{{ route('drug-stock-requests.show', $dispense->request_id) }}"
                                                                    class="text-decoration-none">
                                                                    #{{ $dispense->request_id }}
                                                                </a>
                                                            </td>
                                                            <td>{{ $dispense->batch_number }}</td>
                                                            <td class="fw-bold">
                                                                {{ number_format($dispense->quantity_received) }}</td>
                                                            <td>
                                                                <span
                                                                    class="{{ \Carbon\Carbon::parse($dispense->expiry_date)->isPast() ? 'text-danger' : 'text-success' }}">
                                                                    {{ \Carbon\Carbon::parse($dispense->expiry_date)->format('M j, Y') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($dispense->request_status == 'dispensed')
                                                                    <span class="badge bg-success">Dispensed</span>
                                                                @else
                                                                    <span
                                                                        class="badge bg-warning">{{ ucfirst($dispense->request_status) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="text-muted mb-3">
                                                <i class="ti-arrow-up-circle" style="font-size: 3rem;"></i>
                                            </div>
                                            <h4>No dispensing history</h4>
                                            <p class="text-muted">This drug hasn't been dispensed to any facility yet.</p>
                                        </div>
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
