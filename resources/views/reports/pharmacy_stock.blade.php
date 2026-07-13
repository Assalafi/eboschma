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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-filter me-1"></i>Filter
                                    </button>
                                    @if($facilityId || $programId || request('date_from') || request('date_to'))
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
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title">Stock Records for {{ $selectedFacility->name ?? 'Facility' }}</h3>
                                
                                <!-- Search Bar -->
                                <form action="{{ route('reports.pharmacy_stock') }}" method="GET" class="d-flex mt-2 mt-md-0">
                                    @if($programId) <input type="hidden" name="program_id" value="{{ $programId }}"> @endif
                                    <input type="hidden" name="facility_id" value="{{ $facilityId }}">
                                    @if(request('date_from')) <input type="hidden" name="date_from" value="{{ request('date_from') }}"> @endif
                                    @if(request('date_to')) <input type="hidden" name="date_to" value="{{ request('date_to') }}"> @endif
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search drug or batch..." value="{{ request('search') }}">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="ti ti-search"></i>
                                        </button>
                                    </div>
                                </form>
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
                                            <tr data-stock-id="{{ $record->id }}">
                                                <td>
                                                    @if($record->drug)
                                                        {{ $record->drug->name }}
                                                        <br><small class="text-muted">{{ $record->drug->dosage_form }} | {{ $record->drug->strength }} | {{ $record->drug->unit }}</small>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $record->batch_number }}</td>
                                                <td class="editable-cell" data-field="quantity_received" data-value="{{ $record->quantity_received }}" title="Double-click to edit">
                                                    <span class="display-value text-primary fw-bold">{{ number_format($record->quantity_received) }}</span>
                                                    <input type="number" class="form-control form-control-sm edit-input d-none" value="{{ $record->quantity_received }}" min="0" style="width:90px;">
                                                </td>
                                                <td class="editable-cell" data-field="quantity_remaining" data-value="{{ $record->quantity_remaining }}" title="Double-click to edit">
                                                    <span class="display-value text-success fw-bold">{{ number_format($record->quantity_remaining) }}</span>
                                                    <input type="number" class="form-control form-control-sm edit-input d-none" value="{{ $record->quantity_remaining }}" min="0" style="width:90px;">
                                                </td>
                                                <td>
                                                    <span class="text-orange fw-bold dispensed-value">{{ number_format($record->quantity_received - $record->quantity_remaining) }}</span>
                                                </td>
                                                <td>{{ number_format($record->unit_cost, 2) }}</td>
                                                <td class="total-value-cell">{{ number_format($record->quantity_remaining * $record->unit_cost, 2) }}</td>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editableCells = document.querySelectorAll('.editable-cell');
    
    editableCells.forEach(function(cell) {
        const displayValue = cell.querySelector('.display-value');
        const editInput = cell.querySelector('.edit-input');
        
        // Double-click to edit
        cell.addEventListener('dblclick', function() {
            displayValue.classList.add('d-none');
            editInput.classList.remove('d-none');
            editInput.focus();
            editInput.select();
        });
        
        // Save on Enter key
        editInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveInlineEdit(cell, editInput);
            }
            if (e.key === 'Escape') {
                cancelEdit(cell, editInput, displayValue);
            }
        });
        
        // Save on blur (click elsewhere)
        editInput.addEventListener('blur', function() {
            saveInlineEdit(cell, editInput);
        });
    });
    
    function cancelEdit(cell, editInput, displayValue) {
        editInput.value = cell.dataset.value;
        editInput.classList.add('d-none');
        displayValue.classList.remove('d-none');
    }
    
    function saveInlineEdit(cell, editInput) {
        const displayValue = cell.querySelector('.display-value');
        const newValue = parseInt(editInput.value);
        const oldValue = parseInt(cell.dataset.value);
        
        // No change
        if (newValue === oldValue) {
            editInput.classList.add('d-none');
            displayValue.classList.remove('d-none');
            return;
        }
        
        const row = cell.closest('tr');
        const stockId = row.dataset.stockId;
        const field = cell.dataset.field;
        
        // Send AJAX request
        fetch("{{ url('/reports/pharmacy-stock') }}/" + stockId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field: field, value: newValue })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update displayed values
                cell.dataset.value = newValue;
                displayValue.textContent = Number(newValue).toLocaleString();
                editInput.classList.add('d-none');
                displayValue.classList.remove('d-none');
                
                // Update dispensed and total value in the same row
                const dispensedCell = row.querySelector('.dispensed-value');
                const totalValueCell = row.querySelector('.total-value-cell');
                
                if (dispensedCell) {
                    dispensedCell.textContent = Number(data.dispensed).toLocaleString();
                }
                if (totalValueCell) {
                    totalValueCell.textContent = Number(data.total_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                
                // Update the other editable cell's data-value in the same row
                const receivedCell = row.querySelector('[data-field="quantity_received"]');
                const remainingCell = row.querySelector('[data-field="quantity_remaining"]');
                if (receivedCell) {
                    receivedCell.dataset.value = data.quantity_received;
                    receivedCell.querySelector('.edit-input').value = data.quantity_received;
                }
                if (remainingCell) {
                    remainingCell.dataset.value = data.quantity_remaining;
                    remainingCell.querySelector('.edit-input').value = data.quantity_remaining;
                }
            } else {
                alert('Failed to update: ' + (data.message || 'Unknown error'));
                cancelEdit(cell, editInput, displayValue);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            cancelEdit(cell, editInput, displayValue);
        });
    }
});
</script>
<style>
    .editable-cell { cursor: pointer; }
    .editable-cell:hover { background-color: #f8f9fa; }
</style>
@endpush
