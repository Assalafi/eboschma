@extends('layouts.app')

@section('title', 'Dispense Stock Request')

@push('styles')
<style>
    .info-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; margin-bottom: .15rem; }
    .info-value { font-weight: 600; font-size: .95rem; }
    .cost-highlight { font-size: 1.25rem; font-weight: 700; color: #01542B; }

    #itemsCard {
        max-height: 420px;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    #itemsTable thead th {
        position: static !important;
    }

    .selected-row { background-color: rgba(13, 110, 253, 0.06) !important; }

    .bulk-item-quantity:focus,
    .bulk-item-cost:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .batch-configured { background-color: rgba(25, 135, 84, 0.06) !important; }
    .batch-configured td:first-child { border-left: 3px solid #198754; }

    .guideline-icon { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: .5rem; font-size: 1rem; }
</style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <ol class="breadcrumb breadcrumb-arrows mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('drug-stock-requests.index') }}">Stock Requests</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('drug-stock-requests.show', $stockRequest->id) }}">#{{ str_pad($stockRequest->id, 6, '0', STR_PAD_LEFT) }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dispense</li>
                    </ol>
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Dispense Stock Request
                    </h2>
                    <div class="text-muted mt-1">Create batch records and dispense approved stock</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('drug-stock-requests.show', $stockRequest->id) }}" class="btn btn-outline-secondary">
                        <i class="ti-arrow-left me-1"></i>Back to Request
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('drug-stock-requests.dispense', $stockRequest->id) }}" id="dispenseForm">
                @csrf

                <!-- Summary Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="info-label">Request</div>
                                <div class="info-value">#{{ str_pad($stockRequest->id, 6, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-muted small">
                                    @if ($stockRequest->drug_id && $stockRequest->drug)
                                        {{ $stockRequest->drug->name }}
                                    @else
                                        Bulk ({{ $stockRequest->items->count() }} items)
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="info-label">Program</div>
                                <div class="info-value">{{ $stockRequest->program->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="info-label">Total Requested</div>
                                <div class="info-value">{{ number_format($stockRequest->quantity_requested) }} units</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="info-label">Estimated Cost</div>
                                <div class="cost-highlight">{{ $stockRequest->formatted_estimated_cost }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-8">

                        <!-- Drug Items for Bulk Requests -->
                        @if (!$stockRequest->drug_id)
                            <!-- Toolbar outside the card, matching bulk-stock-request pattern -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <h3 class="mb-0">Drug Items to Dispense</h3>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllItems()">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllItems()">Deselect</button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="showBulkBatchForm()" id="bulkBatchBtn" style="display: none;">
                                        <i class="ti-package me-1"></i>Bulk Batches
                                    </button>
                                </div>
                            </div>
                            <!-- Card IS the scroll container, table is direct child -->
                            <div class="card mb-3" id="itemsCard" style="max-height:420px;overflow-y:auto;padding-left:2rem;padding-right:2rem">
                                    <table class="table table-vcenter table-hover table-striped mb-0" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width:40px">
                                                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAllItems()">
                                                </th>
                                                <th>Drug</th>
                                                <th>Strength</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-center">Store Stock</th>
                                                <th class="text-center">Out of Stock</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($stockRequest->items as $index => $item)
                                                @php
                                                    $itemAvailable = \App\Models\DrugStoreStock::getAvailableQuantity($item->drug_id);
                                                @endphp
                                                <tr data-item-index="{{ $index }}">
                                                    <td>
                                                        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $index }}" onchange="updateBulkButton()">
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold">{{ $item->drug->name }}</div>
                                                        <div class="text-muted small">{{ $item->drug->dosage_form }}</div>
                                                    </td>
                                                    <td class="text-muted">{{ $item->drug->strength }} {{ $item->drug->unit }}</td>
                                                    <td class="text-end fw-bold">{{ number_format($item->quantity_requested) }}</td>
                                                    <td class="text-center">
                                                        @if ($itemAvailable == 0)
                                                            <span class="badge bg-danger">Out</span>
                                                        @elseif ($itemAvailable < $item->quantity_requested)
                                                            <span class="text-warning fw-bold">{{ number_format($itemAvailable) }}</span>
                                                            <span class="badge bg-warning-lt text-warning ms-1">Low</span>
                                                        @else
                                                            <span class="text-success fw-bold">{{ number_format($itemAvailable) }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input out-of-stock-checkbox" 
                                                               id="out-of-stock-{{ $index }}"
                                                               data-item-index="{{ $index }}"
                                                               onchange="toggleOutOfStock({{ $index }})">
                                                        <label for="out-of-stock-{{ $index }}" class="form-check-label small text-muted">
                                                            Mark as unavailable
                                                        </label>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary batch-btn"
                                                            data-item-index="{{ $index }}"
                                                            onclick="showBatchForm({{ $index }}, '{{ addslashes($item->drug->name) }}', {{ $item->quantity_requested }}, '{{ $item->id }}', {{ $item->estimated_cost ? $item->estimated_cost / $item->quantity_requested : 0 }})"
                                                            {{ $itemAvailable == 0 ? 'disabled' : '' }}>
                                                            <i class="ti-plus me-1"></i>Add Batch
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                            </div>
                        @endif

                        <!-- Single Drug Dispensing Form -->
                        @if ($stockRequest->drug_id)
                            <div class="card mb-3">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h3 class="card-title mb-0">Batch Information for {{ $stockRequest->drug->name }}</h3>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBatch()">
                                        <i class="ti-plus me-1"></i>Add Batch
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="batches-container">
                                        <div class="batch-item mb-3 p-3 border rounded" data-batch-index="0">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Batch 1</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="removeBatch(0)" style="display:none;">
                                                    Remove
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                                                        <input type="text" name="batches[0][batch_number]" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="batches[0][expiry_date]" class="form-control" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                        <input type="number" name="batches[0][quantity_received]" class="form-control batch-quantity" required min="1" onchange="updateTotalQuantity()">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Unit Cost (₦) <span class="text-danger">*</span></label>
                                                        <input type="number" name="batches[0][unit_cost]" class="form-control" required min="0" step="0.01"
                                                            value="{{ $stockRequest->estimated_cost ? $stockRequest->estimated_cost / $stockRequest->quantity_requested : 0 }}">
                                                        <small class="text-muted">Requested: ₦{{ number_format($stockRequest->estimated_cost ? $stockRequest->estimated_cost / $stockRequest->quantity_requested : 0, 2) }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                                        <input type="text" name="batches[0][supplier]" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="batches[0][notes]" class="form-control" rows="2" placeholder="Batch notes..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Quantity Progress + Actions -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="info-label">Total Requested</div>
                                        <div class="fw-bold fs-4">{{ number_format($stockRequest->quantity_requested) }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-label">Total Dispensed</div>
                                        <div class="fw-bold fs-4 text-primary" id="total-dispensed">0</div>
                                    </div>
                                    <div class="col-md-4">
                                        @if ($stockRequest->quantity_requested != 0)
                                            <div class="info-label">Progress</div>
                                            <div class="progress mt-1" style="height: 10px;">
                                                <div id="quantity-progress" class="progress-bar bg-success" style="width: 0%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{ route('drug-stock-requests.show', $stockRequest->id) }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i>Cancel
                                </a>
                                <button type="button" class="btn btn-primary" id="dispenseBtn" onclick="submitDispenseForm()">
                                    <i class="ti-package me-1"></i>Dispense Stock
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title mb-0">Dispensing Guidelines</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="guideline-icon bg-primary-lt text-primary">
                                            <i class="ti-clipboard-list"></i>
                                        </span>
                                        <strong>Batch Requirements</strong>
                                    </div>
                                    <ul class="mb-0 small text-muted">
                                        <li>Unique batch number per batch</li>
                                        <li>Expiry date must be in the future</li>
                                        <li>Total qty must match requested amount</li>
                                        <li>Accurate unit cost for accounting</li>
                                    </ul>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="guideline-icon bg-warning-lt text-warning">
                                            <i class="ti-alert-triangle"></i>
                                        </span>
                                        <strong>Important</strong>
                                    </div>
                                    <ul class="mb-0 small text-muted">
                                        <li>Verify expiry dates before dispensing</li>
                                        <li>Verify supplier information</li>
                                        <li>Ensure proper storage conditions</li>
                                    </ul>
                                </div>
                                <hr>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="guideline-icon bg-info-lt text-info">
                                            <i class="ti-bulb"></i>
                                        </span>
                                        <strong>Tips</strong>
                                    </div>
                                    <ul class="mb-0 small text-muted">
                                        <li>Use <strong>Select All</strong> + <strong>Bulk Batches</strong> for fast dispensing</li>
                                        <li>FIFO (First In, First Out) principle</li>
                                        <li>Group similar expiry dates together</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Facility Info -->
                        <div class="card">
                            <div class="card-header"><h3 class="card-title mb-0">Facility</h3></div>
                            <div class="card-body">
                                <div class="fw-bold">{{ $stockRequest->facility->name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $stockRequest->facility->type ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Request Batch Modal -->
    <div class="modal fade" id="batchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="ti-package me-2"></i>Add Batches for <span id="modal-drug-name"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-item-index" value="">
                    <input type="hidden" id="modal-item-id" value="">
                    <input type="hidden" id="modal-requested-quantity" value="">
                    <input type="hidden" id="modal-requested-unit-cost" value="">

                    <div class="alert alert-info">
                        <strong>Requested Quantity:</strong> <span id="modal-requested-quantity-display"></span><br>
                        <strong>Requested Unit Cost:</strong> ₦<span id="modal-requested-unit-cost-display"></span>
                    </div>

                    <div id="modal-batches-container">
                        <!-- Initial batch form will be added here -->
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary" onclick="addModalBatch()">
                            <i class="ti-plus me-1"></i>Add Another Batch
                        </button>
                    </div>

                    <div class="alert alert-secondary mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Requested:</strong> <span id="modal-total-requested">0</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Dispensed:</strong> <span id="modal-total-dispensed">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveBatchesForItem()">Save Batches</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Batch Modal -->
    <div class="modal fade" id="bulkBatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height:85vh;display:flex;flex-direction:column">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="ti-package me-2"></i>Bulk Batches <span class="badge bg-dark ms-2" id="bulk-selected-count">0</span> items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y:auto;flex:1">
                    <!-- Selected Items Tags -->
                    <div class="mb-3">
                        <div id="bulk-selected-items" class="d-flex flex-wrap gap-1" style="max-height:60px;overflow-y:auto"></div>
                    </div>

                    <!-- Common Batch Information — compact 2-row grid -->
                    <div class="border rounded p-3 mb-3" style="background:#f8f9fa">
                        <div class="fw-bold small text-muted text-uppercase mb-2">Common Batch Info</div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Batch Number <span class="text-danger">*</span></label>
                                <input type="text" id="bulk_batch_number" class="form-control form-control-sm" required placeholder="e.g. BATCH001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" id="bulk_expiry_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Unit Cost (₦) <span class="text-danger">*</span></label>
                                <input type="number" id="bulk_unit_cost" class="form-control form-control-sm" required min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Supplier <span class="text-danger">*</span></label>
                                <input type="text" id="bulk_supplier" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Notes <span class="text-muted">(optional)</span></label>
                                <input type="text" id="bulk_notes" class="form-control form-control-sm" placeholder="Common notes...">
                            </div>
                        </div>
                    </div>

                    <!-- Individual Item Quantities — scrollable -->
                    <div class="fw-bold small text-muted text-uppercase mb-2">Item Quantities</div>
                    <div id="bulk-item-quantities" style="max-height:220px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.375rem;padding:.5rem">
                        <!-- Item quantities will be populated here -->
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Requested: <strong id="bulk-total-requested">0</strong> &middot;
                        Dispensed: <strong class="text-primary" id="bulk-total-dispensed">0</strong>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveBulkBatches()">
                            <i class="ti-check me-1"></i>Save Bulk Batches
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let batchCount = 1;
        let modalBatchCount = 0;
        const requestedQuantity = {{ $stockRequest->quantity_requested }};
        const isBulkRequest = {{ !$stockRequest->drug_id ? 'true' : 'false' }};
        const drugItems = @json($stockRequest->items ?? []);
        let itemBatches = {};

        // Bulk selection functions
        function toggleAllItems() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkButton();
        }

        function selectAllItems() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            
            selectAllCheckbox.checked = true;
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            updateBulkButton();
        }

        function deselectAllItems() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            
            selectAllCheckbox.checked = false;
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            updateBulkButton();
        }

        function updateBulkButton() {
            const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            const bulkBatchBtn = document.getElementById('bulkBatchBtn');
            
            if (selectedCheckboxes.length > 0) {
                if (selectedCheckboxes.length > 200) {
                    bulkBatchBtn.style.display = 'none';
                    alert('Too many items selected for bulk batch operation. Please select 200 items or fewer.');
                    return;
                }
                bulkBatchBtn.style.display = 'inline-flex';
                bulkBatchBtn.onclick = showBulkBatchForm;
            } else {
                bulkBatchBtn.style.display = 'none';
            }
        }

        function toggleOutOfStock(itemIndex) {
            const outOfStockCheckbox = document.getElementById(`out-of-stock-${itemIndex}`);
            const itemCheckbox = document.querySelector(`.item-checkbox[value="${itemIndex}"]`);
            const batchBtn = document.querySelector(`.batch-btn[data-item-index="${itemIndex}"]`);
            const row = document.querySelector(`tr[data-item-index="${itemIndex}"]`);
            
            if (outOfStockCheckbox.checked) {
                // Item is marked as out of stock
                itemCheckbox.checked = false;
                itemCheckbox.disabled = true;
                batchBtn.disabled = true;
                row.style.opacity = '0.6';
                row.style.backgroundColor = '#f8f9fa';
                
                // Remove any existing batch data for this item
                removeBatchesForItem(itemIndex);
            } else {
                // Item is available
                itemCheckbox.disabled = false;
                batchBtn.disabled = false;
                row.style.opacity = '1';
                row.style.backgroundColor = '';
            }
            
            updateBulkButton();
        }

        function removeBatchesForItem(itemIndex) {
            // Remove all batch forms for this item
            const batchForms = document.querySelectorAll(`.batch-form[data-item-index="${itemIndex}"]`);
            batchForms.forEach(form => form.remove());
        }

        function showBulkBatchForm() {
            const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one item');
                return;
            }

            if (selectedCheckboxes.length > 200) {
                alert('Please select maximum 200 items at a time.\n\nCurrently selected: ' + selectedCheckboxes.length + ' items.');
                return;
            }

            // Get selected items data
            const selectedItems = [];
            let totalRequested = 0;
            
            selectedCheckboxes.forEach(checkbox => {
                const itemIndex = parseInt(checkbox.value);
                const item = drugItems[itemIndex];
                selectedItems.push({
                    index: itemIndex,
                    id: item.id,
                    name: item.drug.name,
                    strength: item.drug.strength,
                    unit: item.drug.unit,
                    quantity_requested: item.quantity_requested,
                    estimated_unit_cost: item.estimated_cost ? item.estimated_cost / item.quantity_requested : 0
                });
                totalRequested += item.quantity_requested;
            });

            // Update modal with selected items info
            document.getElementById('bulk-selected-count').textContent = selectedItems.length;
            document.getElementById('bulk-total-requested').textContent = totalRequested;
            
            // Show selected items
            const selectedItemsContainer = document.getElementById('bulk-selected-items');
            selectedItemsContainer.innerHTML = selectedItems.map(item => 
                `<span class="badge bg-primary">${item.name} (${item.quantity_requested})</span>`
            ).join('');

            // Populate item quantities section
            const quantitiesContainer = document.getElementById('bulk-item-quantities');
            quantitiesContainer.innerHTML = selectedItems.map((item, index) => `
                <div class="row align-items-center mb-2" data-item-index="${item.index}">
                    <div class="col-md-6">
                        <label class="form-label mb-0">${item.name} (${item.strength} ${item.unit})</label>
                        <small class="text-muted">Requested: ${item.quantity_requested}</small>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control bulk-item-quantity" 
                               data-item-index="${item.index}" 
                               value="${item.quantity_requested}" 
                               min="1" max="999999999" 
                               onchange="updateBulkTotals()">
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control bulk-item-cost" 
                               data-item-index="${item.index}" 
                               value="${item.estimated_unit_cost.toFixed(2)}" 
                               min="0" step="0.01" 
                               onchange="updateBulkTotals()">
                        <small class="text-muted">Unit cost</small>
                    </div>
                </div>
            `).join('');

            // Set default unit cost to average of selected items
            const avgUnitCost = selectedItems.reduce((sum, item) => sum + item.estimated_unit_cost, 0) / selectedItems.length;
            document.getElementById('bulk_unit_cost').value = avgUnitCost.toFixed(2);

            updateBulkTotals();
            
            const modal = new bootstrap.Modal(document.getElementById('bulkBatchModal'));
            modal.show();
        }

        function updateBulkTotals() {
            const quantityInputs = document.querySelectorAll('.bulk-item-quantity');
            let totalDispensed = 0;
            
            quantityInputs.forEach(input => {
                totalDispensed += parseInt(input.value) || 0;
            });
            
            document.getElementById('bulk-total-dispensed').textContent = totalDispensed;
        }

        function saveBulkBatches() {
            const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            
            if (selectedCheckboxes.length === 0) {
                alert('No items selected');
                return;
            }

            // Validate common fields
            const batchNumber = document.getElementById('bulk_batch_number').value.trim();
            const expiryDate = document.getElementById('bulk_expiry_date').value;
            const unitCost = document.getElementById('bulk_unit_cost').value;
            const supplier = document.getElementById('bulk_supplier').value.trim();
            const notes = document.getElementById('bulk_notes').value.trim();

            if (!batchNumber || !expiryDate || !unitCost || !supplier) {
                alert('Please fill in all required common batch information');
                return;
            }

            // Get quantities and costs for each item
            const quantityInputs = document.querySelectorAll('.bulk-item-quantity');
            const costInputs = document.querySelectorAll('.bulk-item-cost');
            
            selectedCheckboxes.forEach((checkbox, index) => {
                const itemIndex = parseInt(checkbox.value);
                const item = drugItems[itemIndex];
                const quantity = parseInt(quantityInputs[index].value) || item.quantity_requested;
                const itemUnitCost = parseFloat(costInputs[index].value) || parseFloat(unitCost);
                
                // Create batch for this item
                const batch = {
                    batch_number: batchNumber + String(itemIndex + 1).padStart(3, '0'),
                    expiry_date: expiryDate,
                    quantity_received: quantity,
                    unit_cost: itemUnitCost,
                    supplier: supplier,
                    notes: notes,
                    item_id: item.id
                };
                
                // Store in itemBatches
                if (!itemBatches[itemIndex]) {
                    itemBatches[itemIndex] = [];
                }
                itemBatches[itemIndex] = [batch]; // Replace any existing batches
                
                // Update button
                const button = document.querySelector(`button[data-item-index="${itemIndex}"]`);
                if (button) {
                    button.innerHTML = `<i class="ti-check me-1"></i>1 Batch (${quantity} units)`;
                    button.className = 'btn btn-sm btn-success';
                }
            });

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkBatchModal'));
            if (modal) {
                modal.hide();
            }

            // Clear selection
            deselectAllItems();
            
            // Update overall totals
            updateTotalQuantity();
            
            alert(`Bulk batches added successfully for ${selectedCheckboxes.length} items!`);
        }

        function addBatch() {
            const container = document.getElementById('batches-container');
            const batchHtml = `
                <div class="batch-item mb-3 p-3 border rounded" data-batch-index="${batchCount}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Batch ${batchCount + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeBatch(${batchCount})">
                            <i class="ti-x"></i> Remove
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                                <input type="text" name="batches[${batchCount}][batch_number]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="batches[${batchCount}][expiry_date]" class="form-control" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="batches[${batchCount}][quantity_received]" class="form-control batch-quantity" required min="1" onchange="updateTotalQuantity()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit Cost (₦) <span class="text-danger">*</span></label>
                                <input type="number" name="batches[${batchCount}][unit_cost]" class="form-control" required min="0" step="0.01" value="{{ $stockRequest->estimated_cost && $stockRequest->quantity_requested ? round($stockRequest->estimated_cost / $stockRequest->quantity_requested, 2) : '' }}">
                                <small class="text-muted">Requested: ₦{{ $stockRequest->estimated_cost && $stockRequest->quantity_requested ? number_format($stockRequest->estimated_cost / $stockRequest->quantity_requested, 2) : '0.00' }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <input type="text" name="batches[${batchCount}][supplier]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="batches[${batchCount}][notes]" class="form-control" rows="2" placeholder="Batch notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', batchHtml);

            // Set the unit cost default value for single drug requests
            @if ($stockRequest->drug_id && $stockRequest->estimated_cost)
                const defaultUnitCost = {{ $stockRequest->estimated_cost / $stockRequest->quantity_requested }};
                const unitCostInput = document.querySelector(
                    `[data-batch-index="${batchCount}"] input[name="batches[${batchCount}][unit_cost]"]`);
                if (unitCostInput) {
                    unitCostInput.value = defaultUnitCost;
                }
                // Also set the requested unit cost display
                const costDisplay = document.getElementById(`requested-unit-cost-${batchCount}`);
                if (costDisplay) {
                    costDisplay.textContent = defaultUnitCost.toFixed(2);
                }
            @endif

            batchCount++;
        }

        function removeBatch(index) {
            const batchItem = document.querySelector(`[data-batch-index="${index}"]`);
            if (batchItem) {
                batchItem.remove();
                updateTotalQuantity();
            }
        }

        function updateTotalQuantity() {
            const quantities = document.querySelectorAll('.batch-quantity');
            let total = 0;
            quantities.forEach(input => {
                total += parseInt(input.value) || 0;
            });

            document.getElementById('total-dispensed').textContent = total;

            if (requestedQuantity > 0) {
                const percentage = Math.min((total / requestedQuantity) * 100, 100);
                document.getElementById('quantity-progress').style.width = percentage + '%';
            }
        }

        function showBatchForm(itemIndex, drugName, requestedQty, itemId, estimatedUnitCost) {
            document.getElementById('modal-drug-name').textContent = drugName;
            document.getElementById('modal-item-index').value = itemIndex;
            document.getElementById('modal-item-id').value = itemId;
            document.getElementById('modal-requested-quantity').value = requestedQty;
            document.getElementById('modal-requested-quantity-display').textContent = requestedQty;
            document.getElementById('modal-requested-unit-cost').value = estimatedUnitCost;
            document.getElementById('modal-requested-unit-cost-display').textContent = parseFloat(estimatedUnitCost)
                .toFixed(2);
            document.getElementById('modal-total-requested').textContent = requestedQty;

            // Clear and reset modal batches
            modalBatchCount = 0;
            document.getElementById('modal-batches-container').innerHTML = '';
            addModalBatch();

            // Load existing batches if any
            if (itemBatches[itemIndex]) {
                itemBatches[itemIndex].forEach((batch, index) => {
                    if (index > 0) addModalBatch();
                    // Fill in existing batch data
                    const batchElements = document.querySelectorAll(`[data-modal-batch-index="${index}"] input`);
                    // Implementation for loading existing batches would go here
                });
            }

            updateModalTotal();

            const modal = new bootstrap.Modal(document.getElementById('batchModal'));
            modal.show();
        }

        function addModalBatch() {
            const container = document.getElementById('modal-batches-container');
            const batchHtml = `
                <div class="batch-item mb-3 p-3 border rounded" data-modal-batch-index="${modalBatchCount}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Batch ${modalBatchCount + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeModalBatch(${modalBatchCount})" ${modalBatchCount === 0 ? 'style="display:none;"' : ''}>
                            <i class="ti-x"></i> Remove
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                                <input type="text" id="modal_batch_${modalBatchCount}_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" id="modal_batch_${modalBatchCount}_expiry" class="form-control" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" id="modal_batch_${modalBatchCount}_quantity" class="form-control modal-batch-quantity" required min="1" onchange="updateModalTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit Cost (₦) <span class="text-danger">*</span></label>
                                <input type="number" id="modal_batch_${modalBatchCount}_cost" class="form-control" required min="0" step="0.01" value="">
                                <small class="text-muted">Requested: ₦<span id="modal-requested-unit-cost-display-${modalBatchCount}"></span></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <input type="text" id="modal_batch_${modalBatchCount}_supplier" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea id="modal_batch_${modalBatchCount}_notes" class="form-control" rows="2" placeholder="Batch notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', batchHtml);

            // Set the requested unit cost display and input value
            const requestedCost = document.getElementById('modal-requested-unit-cost').value;
            if (requestedCost) {
                const costDisplay = document.getElementById(`modal-requested-unit-cost-display-${modalBatchCount}`);
                const costInput = document.getElementById(`modal_batch_${modalBatchCount}_cost`);
                if (costDisplay) {
                    costDisplay.textContent = parseFloat(requestedCost).toFixed(2);
                }
                if (costInput) {
                    costInput.value = parseFloat(requestedCost).toFixed(2);
                }
            }

            modalBatchCount++;
        }

        function removeModalBatch(index) {
            const batchItem = document.querySelector(`[data-modal-batch-index="${index}"]`);
            if (batchItem && modalBatchCount > 1) {
                batchItem.remove();
                modalBatchCount--;
                updateModalTotal();
            }
        }

        function updateModalTotal() {
            const quantities = document.querySelectorAll('.modal-batch-quantity');
            let total = 0;
            quantities.forEach(input => {
                total += parseInt(input.value) || 0;
            });

            document.getElementById('modal-total-dispensed').textContent = total;
        }

        function saveBatchesForItem() {
            console.log('=== SAVING BATCHES FOR ITEM ===');
            const itemIndex = parseInt(document.getElementById('modal-item-index').value);
            const itemId = document.getElementById('modal-item-id').value;
            const batches = [];

            console.log('Item Index:', itemIndex, 'Item ID:', itemId, 'Modal Batch Count:', modalBatchCount);

            for (let i = 0; i < modalBatchCount; i++) {
                const batchNumberEl = document.getElementById(`modal_batch_${i}_number`);
                const expiryDateEl = document.getElementById(`modal_batch_${i}_expiry`);
                const quantityEl = document.getElementById(`modal_batch_${i}_quantity`);
                const unitCostEl = document.getElementById(`modal_batch_${i}_cost`);
                const supplierEl = document.getElementById(`modal_batch_${i}_supplier`);
                const notesEl = document.getElementById(`modal_batch_${i}_notes`);

                if (!batchNumberEl || !expiryDateEl || !quantityEl || !unitCostEl || !supplierEl) {
                    console.error(`Missing batch ${i} elements`);
                    continue;
                }

                const batchNumber = batchNumberEl.value;
                const expiryDate = expiryDateEl.value;
                const quantity = quantityEl.value;
                const unitCost = unitCostEl.value;
                const supplier = supplierEl.value;
                const notes = notesEl ? notesEl.value : '';

                if (batchNumber && expiryDate && quantity && unitCost && supplier) {
                    batches.push({
                        batch_number: batchNumber,
                        expiry_date: expiryDate,
                        quantity_received: quantity,
                        unit_cost: unitCost,
                        supplier: supplier,
                        notes: notes,
                        item_id: itemId
                    });
                    console.log(`Added batch ${i}:`, batches[batches.length - 1]);
                } else {
                    console.warn(`Incomplete batch ${i} data`);
                }
            }

            if (batches.length === 0) {
                alert('Please add at least one complete batch');
                return;
            }

            itemBatches[itemIndex] = batches;
            console.log('Saved batches for item', itemIndex, ':', batches);
            console.log('Current itemBatches object:', itemBatches);

            // Update the button - use data attribute selector for reliability
            const button = document.querySelector(`button[data-item-index="${itemIndex}"]`);
            if (button) {
                const totalQuantity = batches.reduce((sum, batch) => sum + parseInt(batch.quantity_received), 0);
                button.innerHTML = `<i class="ti-check me-1"></i>${batches.length} Batches (${totalQuantity} units)`;
                button.className = 'btn btn-sm btn-success';
                console.log('Updated button for item', itemIndex);
            } else {
                console.error('Could not find button for item', itemIndex);
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('batchModal'));
            if (modal) {
                modal.hide();
            }
        }


        function submitDispenseForm() {
            console.log('=== AJAX FORM SUBMISSION STARTED ===');

            if (!validateForm()) {
                console.log('Validation failed, not submitting');
                return false;
            }

            // Create form data for AJAX submission
            const form = document.querySelector('form');
            const formData = new FormData(form);

            // Add batch data from itemBatches object
            let batchCounter = 0;
            Object.keys(itemBatches).sort((a, b) => parseInt(a) - parseInt(b)).forEach(itemIndex => {
                console.log(`Adding batches for item ${itemIndex} to form data`);
                itemBatches[itemIndex].forEach((batch, batchIndex) => {
                    console.log(`Adding batch ${batchIndex}:`, batch);
                    Object.keys(batch).forEach(key => {
                        formData.append(`batches[${batchCounter}][${key}]`, batch[key]);
                        console.log(
                            `Added to form data: batches[${batchCounter}][${key}] = ${batch[key]}`
                        );
                    });
                    batchCounter++;
                });
            });

            console.log('Total batches added to form data:', batchCounter);

            // Add out-of-stock items to form data
            const outOfStockCheckboxes = document.querySelectorAll('.out-of-stock-checkbox:checked');
            const outOfStockItems = [];
            outOfStockCheckboxes.forEach(checkbox => {
                const itemIndex = checkbox.dataset.itemIndex;
                outOfStockItems.push(itemIndex);
            });
            
            // Add out-of-stock items as JSON array
            formData.append('out_of_stock_items', JSON.stringify(outOfStockItems));
            console.log('Out of stock items added to form data:', outOfStockItems);

            // Show loading state
            const submitButton = document.getElementById('dispenseBtn');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            submitButton.disabled = true;

            // Submit via AJAX - explicitly use dispense route
            const formAction = "{{ route('drug-stock-requests.dispense', $stockRequest->id) }}";
            const formMethod = 'POST';

            console.log('Submitting to:', formAction, 'Method:', formMethod);

            fetch(formAction, {
                    method: formMethod,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        return response.text().then(html => {
                            console.error('Server returned HTML instead of JSON:', html.substring(0, 500));
                            throw new Error('Server error. This may be caused by too many items. Check the console for details.');
                        });
                    }
                    return response.json().then(data => {
                        if (!response.ok) {
                            if (data.errors) {
                                throw new Error('Validation: ' + Object.values(data.errors).flat().join(', '));
                            }
                            throw new Error(data.message || `HTTP ${response.status}`);
                        }
                        return data;
                    });
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || "{{ route('drug-stock-requests.show', $stockRequest->id) }}";
                    } else {
                        throw new Error(data.message || 'Dispensing failed');
                    }
                })
                .catch(error => {
                    console.error('Submission error:', error);
                    alert('Error: ' + error.message);
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                });

            return false;
        }

        function validateForm() {
            console.log('=== FORM VALIDATION STARTED ===');
            console.log('isBulkRequest:', isBulkRequest);
            console.log('itemBatches:', itemBatches);
            console.log('drugItems:', drugItems);

            if (isBulkRequest) {
                // Check if all non-out-of-stock items have batches
                const outOfStockCheckboxes = document.querySelectorAll('.out-of-stock-checkbox:checked');
                const outOfStockIndices = Array.from(outOfStockCheckboxes).map(cb => parseInt(cb.dataset.itemIndex));
                const requiredItemsCount = drugItems.length - outOfStockIndices.length;
                const itemBatchesCount = Object.keys(itemBatches).length;

                console.log('itemBatches count:', itemBatchesCount, 'required items count:', requiredItemsCount, 'out of stock items:', outOfStockIndices);

                if (itemBatchesCount !== requiredItemsCount) {
                    console.error('Missing batches - itemBatches keys:', Object.keys(itemBatches), 'required items count:', requiredItemsCount);
                    alert(
                        `Please add batches for all ${requiredItemsCount} available drug items. You have only added batches for ${itemBatchesCount} item(s).`
                    );
                    return false;
                }

                // Check if total quantities match for each item (skip out-of-stock items)
                let totalRequested = 0;
                let totalDispensed = 0;
                let allItemsValid = true;

                drugItems.forEach((item, index) => {
                    // Skip out-of-stock items completely
                    if (outOfStockIndices.includes(index)) {
                        console.log(`Item ${index} (${item.drug.name}) is out of stock - skipping all validation`);
                        return;
                    }

                    const requestedQty = parseInt(item.quantity_requested);
                    totalRequested += requestedQty;

                    if (itemBatches[index]) {
                        let itemDispensed = 0;
                        itemBatches[index].forEach(batch => {
                            itemDispensed += parseInt(batch.quantity_received);
                        });
                        totalDispensed += itemDispensed;

                        console.log(
                            `Item ${index} (${item.drug.name}): requested=${requestedQty}, dispensed=${itemDispensed}`
                        );

                        if (itemDispensed !== requestedQty) {
                            alert(
                                `Quantity mismatch for ${item.drug.name}: requested ${requestedQty}, dispensed ${itemDispensed}`
                            );
                            allItemsValid = false;
                        }
                    } else {
                        // Only show error if item is not out of stock
                        console.error(`No batches found for item ${index} (${item.drug.name})`);
                        alert(`No batches added for ${item.drug.name}. Please add batches or mark as out of stock.`);
                        allItemsValid = false;
                    }
                });

                if (!allItemsValid) {
                    return false;
                }

                console.log('Total requested:', totalRequested, 'Total dispensed:', totalDispensed);

            } else {
                // Single drug validation
                const totalDispensed = parseInt(document.getElementById('total-dispensed').textContent);
                console.log('Single drug validation - dispensed:', totalDispensed, 'requested:', requestedQuantity);
                if (totalDispensed !== requestedQuantity) {
                    alert(
                        `Total dispensed quantity (${totalDispensed}) must match requested quantity (${requestedQuantity})`
                    );
                    return false;
                }
            }

            console.log('=== FORM VALIDATION PASSED ===');
            return true;
        }

        // Initialize on page load
        // Set minimum date to today for expiry dates
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== PAGE LOADED ===');
            console.log('isBulkRequest:', isBulkRequest);

            // Set requested unit cost for initial batch forms
            @if (!$stockRequest->drug_id)
                @foreach ($stockRequest->items as $index => $item)
                    if (document.getElementById('requested-unit-cost-{{ $index }}')) {
                        const unitCost =
                            {{ $item->estimated_cost ? $item->estimated_cost / $item->quantity_requested : 0 }};
                        document.getElementById('requested-unit-cost-{{ $index }}').textContent = unitCost
                            .toFixed(2);
                    }
                @endforeach
            @endif
            console.log('drugItems:', drugItems);
            console.log('requestedQuantity:', requestedQuantity);

            // Test that JavaScript is working
            // alert('JavaScript loaded successfully! Bulk request: ' + isBulkRequest + ', Drug items: ' + drugItems
            //     .length);

            const dateInputs = document.querySelectorAll('input[type="date"]');
            const today = new Date().toISOString().split('T')[0];

            dateInputs.forEach(input => {
                input.setAttribute('min', today);
            });

            // Initialize quantity check
            updateTotalQuantity();
        });
    </script>
@endpush
