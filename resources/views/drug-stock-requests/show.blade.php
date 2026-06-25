@extends('layouts.app')

@section('title', 'Stock Request #' . str_pad($request->id, 6, '0', STR_PAD_LEFT))

@push('styles')
<style>
    .removed-item { opacity: 0.35; text-decoration: line-through; pointer-events: none; }
    .quantity-input { width: 100px; }
    .edit-mode .table-hover tbody tr:hover { background-color: rgba(255, 193, 7, 0.08); }

    .edit-mode .quantity-input {
        border: 2px solid #f59e0b;
        border-radius: .375rem;
        font-weight: 600;
        text-align: center;
        transition: border-color .2s;
    }
    .edit-mode .quantity-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
    }
    .btn-remove-item {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; border: 1px solid #dee2e6;
        background: #fff; color: #6c757d;
        transition: all .15s;
    }
    .btn-remove-item:hover { background: #dc3545; color: #fff; border-color: #dc3545; }

    .items-table-wrap {
        max-height: 420px;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding-right: 1rem;
    }
    .items-table-wrap thead th {
        position: static !important;
    }

    .status-ribbon {
        padding: .5rem 1.25rem;
        border-radius: .5rem;
        font-weight: 600;
        font-size: .85rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
    .status-pending   { background: #fff3cd; color: #856404; }
    .status-approved  { background: #d1e7dd; color: #0f5132; }
    .status-rejected  { background: #f8d7da; color: #842029; }
    .status-dispensed  { background: #cff4fc; color: #055160; }

    .info-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; margin-bottom: .15rem; }
    .info-value { font-weight: 600; font-size: .95rem; }

    .tl-step { position: relative; padding-left: 2rem; padding-bottom: 1.25rem; }
    .tl-step:last-child { padding-bottom: 0; }
    .tl-step::before { content: ''; position: absolute; left: 7px; top: 24px; bottom: 0; width: 2px; background: #dee2e6; }
    .tl-step:last-child::before { display: none; }
    .tl-dot { position: absolute; left: 0; top: 4px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px currentColor; background: currentColor; }
    .tl-dot.tl-primary  { color: #0d6efd; }
    .tl-dot.tl-success  { color: #198754; }
    .tl-dot.tl-info     { color: #0dcaf0; }
    .tl-dot.tl-danger   { color: #dc3545; }

    .cost-highlight { font-size: 1.5rem; font-weight: 700; color: #01542B; }
</style>
@endpush

@php
    $statusClass = match($request->status) {
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        'dispensed' => 'status-dispensed',
        default => 'status-pending',
    };
    $statusIcon = match($request->status) {
        'approved' => 'ti-check',
        'rejected' => 'ti-x',
        'dispensed' => 'ti-package',
        default => 'ti-clock',
    };
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <ol class="breadcrumb breadcrumb-arrows mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('drug-stock-requests.index') }}">Stock Requests</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</li>
                    </ol>
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <h2 class="page-title mb-0">
                            Stock Request #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}
                        </h2>
                        <span class="status-ribbon {{ $statusClass }}">
                            <i class="{{ $statusIcon }}"></i> {{ ucfirst($request->status) }}
                        </span>
                    </div>
                    <div class="text-muted">
                        {{ $request->facility->name }} &middot;
                        {{ $request->requested_at->format('M j, Y g:i A') }}
                        @if(!$request->drug_id)
                            &middot; <span class="badge bg-primary-lt text-primary">Bulk ({{ $request->items->count() }} items)</span>
                        @endif
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-list">
                        <a href="{{ route('drug-stock-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="ti-arrow-left me-1"></i>Back
                        </a>
                        @if ($isBoschmaAdmin && $request->canBeApproved() && !$request->drug_id)
                            <button type="button" class="btn btn-warning" id="editModeBtn" onclick="toggleEditMode()">
                                <i class="ti-edit me-1"></i>Edit Quantities
                            </button>
                        @endif
                        @if ($request->canBeApproved() && $isBoschmaAdmin)
                            <button type="button" class="btn btn-success" onclick="approveRequest({{ $request->id }})">
                                <i class="ti-check me-1"></i>Approve
                            </button>
                        @endif
                        @if ($request->canBeRejected() && $isBoschmaAdmin)
                            <button type="button" class="btn btn-danger" onclick="rejectRequest({{ $request->id }})">
                                <i class="ti-x me-1"></i>Reject
                            </button>
                        @endif
                        @if ($request->canBeDispensed() && $isBoschmaAdmin)
                            <a href="{{ route('drug-stock-requests.dispense-form', $request->id) }}" class="btn btn-primary">
                                <i class="ti-package me-1"></i>Dispense
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            <!-- Summary Row -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="info-label">Program</div>
                            <div class="info-value">{{ $request->program->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="info-label">Priority</div>
                            <div>{!! $request->priority_badge !!}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="info-label">Requested By</div>
                            <div class="info-value">{{ $request->requestedBy->name }}</div>
                            <div class="text-muted small">{{ $request->requestedBy->staffPosition->name ?? '' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="info-label">Estimated Cost</div>
                            <div class="cost-highlight" id="requestTotalCost">{{ $request->formatted_estimated_cost }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">

                    <!-- Drug Information -->
                    <div class="card mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0">
                                @if($request->drug_id)
                                    Drug Details
                                @else
                                    Requested Items
                                    @if ($isBoschmaAdmin && $request->canBeApproved() && !$request->drug_id)
                                        <span class="ms-2" id="editActions" style="display:none">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="saveChanges()" id="saveChangesBtn">
                                                <i class="ti-save me-1"></i>Save
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelEdit()" id="cancelEditBtn">
                                                Cancel
                                            </button>
                                        </span>
                                    @endif
                                @endif
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($request->drug_id)
                                {{-- Single drug request --}}
                                <div class="p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex gap-3 align-items-start">
                                                <div class="avatar avatar-lg bg-primary-lt text-primary rounded">
                                                    <i class="ti-pill fs-2"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold fs-4">{{ $request->drug->name }}</div>
                                                    <div class="text-muted">{{ $request->drug->strength }} {{ $request->drug->unit }} &middot; {{ $request->drug->dosage_form }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="info-label">Qty Requested</div>
                                                    <div class="fw-bold fs-3">{{ $request->formatted_quantity }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="info-label">Unit Price</div>
                                                    <div class="fw-bold">₦{{ number_format($request->drug->unit_price, 2) }}</div>
                                                </div>
                                                <div class="col-4">
                                                    @php
                                                        $storeAvailable = \App\Models\DrugStoreStock::getAvailableQuantity($request->drug_id);
                                                    @endphp
                                                    <div class="info-label">Store Stock</div>
                                                    <div class="fw-bold {{ $storeAvailable == 0 ? 'text-danger' : ($storeAvailable < $request->quantity_requested ? 'text-warning' : 'text-success') }}">
                                                        {{ number_format($storeAvailable) }}
                                                        @if ($storeAvailable == 0)
                                                            <span class="badge bg-danger ms-1">Out</span>
                                                        @elseif ($storeAvailable < $request->quantity_requested)
                                                            <span class="badge bg-warning ms-1">Low</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Bulk request items table --}}
                                <div class="table-responsive items-table-wrap">
                                    <table class="table table-vcenter table-hover table-striped mb-0" id="bulkItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Drug</th>
                                                <th>Strength</th>
                                                <th class="text-end">Quantity</th>
                                                <th class="text-center">Store Stock</th>
                                                <th class="text-center">Facility Stock</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-end">Cost</th>
                                                @if ($isBoschmaAdmin && $request->canBeApproved() && !$request->drug_id)
                                                    <th class="text-center" style="width:50px">
                                                        <span class="edit-col" style="display:none"></span>
                                                    </th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($request->items as $item)
                                                @php
                                                    $itemAvailable = \App\Models\DrugStoreStock::getAvailableQuantity($item->drug_id);
                                                    $facilityStock = \App\Models\DrugStock::where('facility_id', $request->facility_id)
                                                        ->where('drug_id', $item->drug_id)
                                                        ->sum('quantity_remaining');
                                                @endphp
                                                <tr data-item-id="{{ $item->id }}" data-original-quantity="{{ $item->quantity_requested }}" data-unit-price="{{ $item->drug->unit_price }}">
                                                    <td>
                                                        <div class="fw-semibold">{{ $item->drug->name }}</div>
                                                        <div class="text-muted small">{{ $item->drug->dosage_form }}</div>
                                                    </td>
                                                    <td class="text-muted">{{ $item->drug->strength }} {{ $item->drug->unit }}</td>
                                                    <td class="text-end">
                                                        <span class="quantity-display fw-bold">{{ number_format($item->quantity_requested) }}</span>
                                                        @if ($isBoschmaAdmin && $request->canBeApproved() && !$request->drug_id)
                                                            <input type="number" class="form-control form-control-sm quantity-input" style="display:none"
                                                                   value="{{ $item->quantity_requested }}" min="1">
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($itemAvailable == 0)
                                                            <span class="badge bg-danger">Out of Stock</span>
                                                        @elseif ($itemAvailable < $item->quantity_requested)
                                                            <span class="text-warning fw-bold">{{ number_format($itemAvailable) }}</span>
                                                            <span class="badge bg-warning-lt text-warning ms-1">Low</span>
                                                        @else
                                                            <span class="text-success fw-bold">{{ number_format($itemAvailable) }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="text-info fw-bold">{{ number_format($facilityStock) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                        $itemIndex = $loop->index;
                                                        // Manually decode JSON since model cast isn't working locally
                                                        $outOfStockItems = [];
                                                        if ($request->out_of_stock_items) {
                                                            if (is_string($request->out_of_stock_items)) {
                                                                $outOfStockItems = json_decode($request->out_of_stock_items, true) ?: [];
                                                            } elseif (is_array($request->out_of_stock_items)) {
                                                                $outOfStockItems = $request->out_of_stock_items;
                                                            }
                                                        }
                                                        // Handle both string and integer indices
                                                        $isOutOfStock = in_array($itemIndex, $outOfStockItems) || in_array((string)$itemIndex, $outOfStockItems);
                                                        $isDispensed = $request->status === 'dispensed' && !$isOutOfStock;
                                                    @endphp
                                                        
                                                        @if ($request->status === 'dispensed')
                                                            @if ($isOutOfStock)
                                                                <span class="badge bg-warning text-dark">
                                                                    <i class="ti-alert-triangle me-1"></i>Skipped
                                                                </span>
                                                                <div class="small text-muted">Out of stock</div>
                                                            @else
                                                                <span class="badge bg-success">
                                                                    <i class="ti-check me-1"></i>Dispensed
                                                                </span>
                                                                <div class="small text-muted">Completed</div>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end text-nowrap item-cost">{{ $item->formatted_estimated_cost }}</td>
                                                    @if ($isBoschmaAdmin && $request->canBeApproved() && !$request->drug_id)
                                                        <td class="text-center">
                                                            <button type="button" class="btn-remove-item remove-item-btn" style="display:none"
                                                                    onclick="removeItem({{ $item->id }}, '{{ addslashes($item->drug->name) }}')"
                                                                    title="Remove item">
                                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6"/>
                                                                </svg>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold" style="background:#f8f9fa">
                                                <td colspan="2">Total ({{ $request->items->count() }} items)</td>
                                                <td class="text-end" id="totalQuantity">{{ number_format($request->items->sum('quantity_requested')) }}</td>
                                                <td class="text-end" id="totalCost">{{ $request->formatted_estimated_cost }}</td>
                                                @if ($isBoschmaAdmin && $request->canBeApproved() && !$request->drug_id)
                                                    <td></td>
                                                @endif
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                    @if (!$request->drug_id)
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Total Items:</strong> {{ $request->items->count() }}
                                @if ($request->status === 'dispensed' && $request->out_of_stock_items)
                                    @php
                                        // Manually decode JSON since model cast isn't working locally
                                        $outOfStockItems = [];
                                        if ($request->out_of_stock_items) {
                                            if (is_string($request->out_of_stock_items)) {
                                                $outOfStockItems = json_decode($request->out_of_stock_items, true) ?: [];
                                            } elseif (is_array($request->out_of_stock_items)) {
                                                $outOfStockItems = $request->out_of_stock_items;
                                            }
                                        }
                                        $dispensedCount = $request->items->count() - count($outOfStockItems);
                                        $skippedCount = count($outOfStockItems);
                                    @endphp
                                    <span class="ms-3">
                                        <span class="badge bg-success">{{ $dispensedCount }} Dispensed</span>
                                        <span class="badge bg-warning text-dark ms-1">{{ $skippedCount }} Skipped</span>
                                    </span>
                                @endif
                            </div>
                            <div class="text-end">
                                <strong>Total Cost:</strong> {{ $request->formatted_estimated_cost }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reason and Notes -->
                    @if($request->reason || $request->notes || $request->rejection_reason)
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title mb-0">Reason &amp; Notes</h3></div>
                        <div class="card-body">
                            @if($request->reason)
                            <div class="mb-3">
                                <div class="info-label">Reason for Request</div>
                                <div class="border rounded p-2 bg-light">{{ $request->reason }}</div>
                            </div>
                            @endif
                            @if ($request->notes)
                            <div class="mb-3">
                                <div class="info-label">Additional Notes</div>
                                <div class="border rounded p-2 bg-light">{{ $request->notes }}</div>
                            </div>
                            @endif
                            @if ($request->rejection_reason)
                            <div>
                                <div class="info-label text-danger">Rejection Reason</div>
                                <div class="border border-danger rounded p-2 bg-danger bg-opacity-10 text-danger">
                                    {{ $request->rejection_reason }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Stock Batches (if dispensed) -->
                    @if ($request->drugStocks->count() > 0)
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title mb-0">Dispensed Stock Batches</h3></div>
                            <div class="table-responsive">
                                <table class="table table-vcenter table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Batch #</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Unit Cost</th>
                                            <th class="text-end">Total</th>
                                            <th>Expiry</th>
                                            <th>Supplier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($request->drugStocks as $stock)
                                            <tr>
                                                <td class="fw-semibold">{{ $stock->batch_number }}</td>
                                                <td class="text-end">{{ $stock->formatted_quantity_received }}</td>
                                                <td class="text-end">{{ $stock->formatted_unit_cost }}</td>
                                                <td class="text-end fw-bold">{{ $stock->formatted_total_value }}</td>
                                                <td>
                                                    <span class="{{ $stock->expiry_status['status'] == 'expired' ? 'text-danger' : ($stock->expiry_status['status'] == 'near-expiry' ? 'text-warning' : 'text-success') }}">
                                                        {{ $stock->expiry_date->format('M j, Y') }}
                                                    </span>
                                                </td>
                                                <td>{{ $stock->supplier }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <!-- Facility Card -->
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title mb-0">Facility</h3></div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="avatar avatar-md bg-primary-lt text-primary rounded">
                                    <i class="ti-building"></i>
                                </span>
                                <div>
                                    <div class="fw-bold">{{ $request->facility->name }}</div>
                                    <div class="text-muted small">{{ $request->facility->type }}</div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="info-label">LGA</div>
                                    <div class="info-value small">{{ $request->facility->lga }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">Ward</div>
                                    <div class="info-value small">{{ $request->facility->ward }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title mb-0">Timeline</h3></div>
                        <div class="card-body">
                            <div class="tl-step">
                                <div class="tl-dot tl-primary"></div>
                                <div class="info-label">{{ $request->requested_at->format('M j, Y g:i A') }}</div>
                                <div class="fw-semibold">Request Submitted</div>
                                <div class="text-muted small">by {{ $request->requestedBy->name }}</div>
                            </div>
                            @if ($request->approved_at)
                            <div class="tl-step">
                                <div class="tl-dot tl-success"></div>
                                <div class="info-label">{{ $request->approved_at->format('M j, Y g:i A') }}</div>
                                <div class="fw-semibold">Approved</div>
                                <div class="text-muted small">by {{ $request->approvedBy->name ?? 'System' }}</div>
                            </div>
                            @endif
                            @if ($request->status === 'rejected')
                            <div class="tl-step">
                                <div class="tl-dot tl-danger"></div>
                                <div class="info-label">Rejected</div>
                                <div class="fw-semibold text-danger">Request Rejected</div>
                            </div>
                            @endif
                            @if ($request->dispensed_at)
                            <div class="tl-step">
                                <div class="tl-dot tl-info"></div>
                                <div class="info-label">{{ $request->dispensed_at->format('M j, Y g:i A') }}</div>
                                <div class="fw-semibold">Stock Dispensed</div>
                                <div class="text-muted small">by {{ $request->dispensedBy->name ?? 'System' }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    @if ($isBoschmaAdmin && ($request->canBeApproved() || $request->canBeRejected() || $request->canBeDispensed()))
                    <div class="card">
                        <div class="card-header"><h3 class="card-title mb-0">Quick Actions</h3></div>
                        <div class="card-body d-grid gap-2">
                            @if ($request->canBeApproved())
                                <button type="button" class="btn btn-success" onclick="approveRequest({{ $request->id }})">
                                    <i class="ti-check me-1"></i>Approve Request
                                </button>
                            @endif
                            @if ($request->canBeRejected())
                                <button type="button" class="btn btn-outline-danger" onclick="rejectRequest({{ $request->id }})">
                                    <i class="ti-x me-1"></i>Reject Request
                                </button>
                            @endif
                            @if ($request->canBeDispensed())
                                <a href="{{ route('drug-stock-requests.dispense-form', $request->id) }}" class="btn btn-primary">
                                    <i class="ti-package me-1"></i>Dispense Stock
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('drug-stock-requests.approve', $request->id) }}" id="approveForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="ti-check me-2"></i>Approve Stock Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to approve request <strong>#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</strong>.</p>
                        <div class="mb-3">
                            <label class="form-label">Approval Notes <span class="text-muted">(optional)</span></label>
                            <textarea name="approval_notes" class="form-control" rows="3"
                                placeholder="Add any notes for this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti-check me-1"></i>Approve
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('drug-stock-requests.reject', $request->id) }}" id="rejectForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="ti-x me-2"></i>Reject Stock Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to reject request <strong>#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</strong>.</p>
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required
                                placeholder="Please provide a reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti-x me-1"></i>Reject
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let editMode = false;
        let removedItems = [];

        function toggleEditMode() {
            editMode = !editMode;
            if (editMode) {
                $('#bulkItemsTable').addClass('edit-mode');
                $('.quantity-display').hide();
                $('.quantity-input').show().prop('disabled', false);
                $('.remove-item-btn, .edit-col').show();
                $('#editActions').show();
                $('#editModeBtn').hide();
                $('button[onclick*="approveRequest"], button[onclick*="rejectRequest"]').prop('disabled', true);
            } else {
                $('#bulkItemsTable').removeClass('edit-mode');
                $('.quantity-display').show();
                $('.quantity-input, .remove-item-btn, .edit-col').hide();
                $('#editActions').hide();
                $('#editModeBtn').show();
                $('button[onclick*="approveRequest"], button[onclick*="rejectRequest"]').prop('disabled', false);
            }
        }

        function cancelEdit() {
            $('#bulkItemsTable tbody tr').each(function() {
                const orig = $(this).data('original-quantity');
                $(this).find('.quantity-input').val(orig);
                $(this).find('.quantity-display').text(Number(orig).toLocaleString());
            });
            $('.removed-item').removeClass('removed-item').show();
            removedItems = [];
            updateTotals();
            toggleEditMode();
        }

        function removeItem(itemId, drugName) {
            if (confirm(`Remove "${drugName}" from this request?`)) {
                $(`tr[data-item-id="${itemId}"]`).addClass('removed-item').hide();
                removedItems.push(itemId);
                updateTotals();
            }
        }

        function updateTotals() {
            let totalQty = 0, totalCost = 0;
            $('#bulkItemsTable tbody tr:not(.removed-item)').each(function() {
                const qty = parseInt($(this).find('.quantity-input').val()) || parseInt($(this).find('.quantity-display').text().replace(/,/g, ''));
                const price = parseFloat($(this).data('unit-price'));
                totalQty += qty;
                totalCost += qty * price;
                $(this).find('.item-cost').text('₦' + (qty * price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            });
            const fmt = '₦' + totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            $('#totalQuantity').text(totalQty.toLocaleString());
            $('#totalCost').text(fmt);
            $('#requestTotalCost').text(fmt);
        }

        function saveChanges() {
            const updates = {};
            $('#bulkItemsTable tbody tr:not(.removed-item)').each(function() {
                const id = $(this).data('item-id');
                const newQty = parseInt($(this).find('.quantity-input').val());
                if (newQty !== $(this).data('original-quantity')) updates[id] = newQty;
            });

            if (Object.keys(updates).length === 0 && removedItems.length === 0) {
                alert('No changes to save.');
                return;
            }
            if (!confirm(`Save changes?\n${Object.keys(updates).length} updated, ${removedItems.length} removed.`)) return;

            const $btn = $('#saveChangesBtn');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

            $.ajax({
                url: '{{ route("drug-stock-requests.update-items", $request->id) }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', updates: updates, removed_items: removedItems },
                success: function(r) {
                    if (r.success) { window.location.href = r.redirect || location.href; }
                    else { alert('Error: ' + r.message); $btn.prop('disabled', false).html('<i class="ti-save me-1"></i>Save'); }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    $btn.prop('disabled', false).html('<i class="ti-save me-1"></i>Save');
                }
            });
        }

        $(document).on('input', '.quantity-input', updateTotals);

        function approveRequest(id) { new bootstrap.Modal(document.getElementById('approveModal')).show(); }
        function rejectRequest(id) { new bootstrap.Modal(document.getElementById('rejectModal')).show(); }

        // Loading on form submit
        $('#approveForm, #rejectForm').on('submit', function() {
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).prepend('<span class="spinner-border spinner-border-sm me-2"></span>');
        });
    </script>
@endpush
