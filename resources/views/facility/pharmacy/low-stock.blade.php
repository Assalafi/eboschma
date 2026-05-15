@extends('layouts.facility')

@section('title', 'Low Stock Alerts')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Low Stock
                            Alerts</h1>
                        <p class="text-muted mb-0">Monitor drugs that need restocking in your facility</p>
                    </div>
                    <div>
                        <a href="{{ route('facility.pharmacy.stock') }}" class="btn btn-info me-2">
                            <i class="ti-package me-1"></i> Update Stock
                        </a>
                        <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-outline-secondary">
                            <i class="ti-arrow-left me-1"></i> Back to Pharmacy
                        </a>
                    </div>
                </div>

                <!-- Search and Filter Controls -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('facility.pharmacy.low-stock') }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                placeholder="Search drugs by name, description, form, or strength..."
                                value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if (request('search') || request('dosage_form'))
                                <a href="{{ route('facility.pharmacy.low-stock') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            @endif
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('facility.pharmacy.low-stock') }}">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <select name="dosage_form" class="form-select" onchange="this.form.submit()">
                                <option value="">All Dosage Forms</option>
                                @foreach ($dosageForms as $form)
                                    <option value="{{ $form }}"
                                        {{ request('dosage_form') == $form ? 'selected' : '' }}>
                                        {{ $form }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Alert Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-warning text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-alert-triangle" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-warning">{{ $lowStockDrugs->total() }}</h3>
                                        <p class="text-muted mb-0 small">Low Stock (≤10 units)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-danger text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-close-circle" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-danger">{{ $outOfStockDrugs->total() }}</h3>
                                        <p class="text-muted mb-0 small">Out of Stock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Drugs -->
                @if ($outOfStockDrugs->count() > 0)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-danger text-white"
                            style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">
                                        <i class="ti-close-circle me-2"></i>Out of Stock Drugs
                                        ({{ $outOfStockDrugs->total() }})
                                    </h5>
                                    <p class="mb-0 small opacity-75">These drugs need immediate restocking</p>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-light btn-sm me-2" onclick="selectAllOutStock()">
                                        <i class="ti-check me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkRestock('out')">
                                        <i class="ti-plus me-1"></i>Bulk Restock
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="5%" class="border-0 fw-semibold">
                                                <input type="checkbox" class="form-check-input"
                                                    id="selectAllOutStockCheckbox">
                                            </th>
                                            <th class="border-0 fw-semibold">Drug Name</th>
                                            <th class="border-0 fw-semibold">Form & Strength</th>
                                            <th class="border-0 fw-semibold">Current Stock</th>
                                            <th class="border-0 fw-semibold">Unit Price</th>
                                            <th class="border-0 fw-semibold">Last Updated</th>
                                            <th class="border-0 fw-semibold text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outOfStockDrugs as $drug)
                                            <tr>
                                                <td class="align-middle">
                                                    <input type="checkbox" class="form-check-input out-stock-checkbox"
                                                        value="{{ $drug->id }}" data-name="{{ $drug->name }}">
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-semibold">{{ $drug->name }}</div>
                                                    @if ($drug->description)
                                                        <div class="text-muted small">
                                                            {{ Str::limit($drug->description, 50) }}</div>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-light text-dark">{{ $drug->dosage_form }}</span>
                                                    <div class="text-muted small">{{ $drug->strength }}
                                                        {{ $drug->unit }}
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-danger">0 units</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="fw-semibold">₦{{ number_format($drug->unit_price, 2) }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="text-muted small">{{ $drug->updated_at->diffForHumans() }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('facility.pharmacy.edit', $drug->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="ti-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="quickRestock('{{ $drug->id }}', '{{ $drug->name }}')"
                                                            title="Quick Restock">
                                                            <i class="ti-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination for Out of Stock -->
                            @if ($outOfStockDrugs->hasPages())
                                <div class="d-flex justify-content-center p-3 border-top">
                                    {{ $outOfStockDrugs->links('pagination.bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Low Stock Drugs -->
                @if ($lowStockDrugs->count() > 0)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-warning text-white"
                            style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">
                                        <i class="ti-alert-triangle me-2"></i>Low Stock Drugs
                                        ({{ $lowStockDrugs->total() }})
                                    </h5>
                                    <p class="mb-0 small opacity-75">These drugs have 10 or fewer units remaining</p>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-light btn-sm me-2"
                                        onclick="selectAllLowStock()">
                                        <i class="ti-check me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkRestock('low')">
                                        <i class="ti-plus me-1"></i>Bulk Restock
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="5%" class="border-0 fw-semibold">
                                                <input type="checkbox" class="form-check-input"
                                                    id="selectAllLowStockCheckbox">
                                            </th>
                                            <th class="border-0 fw-semibold">Drug Name</th>
                                            <th class="border-0 fw-semibold">Form & Strength</th>
                                            <th class="border-0 fw-semibold">Current Stock</th>
                                            <th class="border-0 fw-semibold">Unit Price</th>
                                            <th class="border-0 fw-semibold">Total Value</th>
                                            <th class="border-0 fw-semibold text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lowStockDrugs as $drug)
                                            <tr>
                                                <td class="align-middle">
                                                    <input type="checkbox" class="form-check-input low-stock-checkbox"
                                                        value="{{ $drug->id }}" data-name="{{ $drug->name }}">
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-semibold">{{ $drug->name }}</div>
                                                    @if ($drug->description)
                                                        <div class="text-muted small">
                                                            {{ Str::limit($drug->description, 50) }}</div>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-light text-dark">{{ $drug->dosage_form }}</span>
                                                    <div class="text-muted small">{{ $drug->strength }}
                                                        {{ $drug->unit }}</div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-warning">{{ $drug->quantity }} units</span>
                                                    <div class="progress mt-1" style="height: 4px;">
                                                        <div class="progress-bar bg-warning"
                                                            style="width: {{ min(100, $drug->quantity * 10) }}%"></div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="fw-semibold">₦{{ number_format($drug->unit_price, 2) }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="fw-bold text-warning">₦{{ number_format($drug->quantity * $drug->unit_price, 2) }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('facility.pharmacy.edit', $drug->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="ti-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="quickRestock('{{ $drug->id }}', '{{ $drug->name }}')"
                                                            title="Quick Restock">
                                                            <i class="ti-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination for Low Stock -->
                            @if ($lowStockDrugs->hasPages())
                                <div class="d-flex justify-content-center p-3 border-top">
                                    {{ $lowStockDrugs->links('pagination.bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- No Alerts -->
                @if ($lowStockDrugs->count() == 0 && $outOfStockDrugs->count() == 0)
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body text-center py-5">
                            <div class="avatar avatar-lg bg-success text-white mb-3"
                                style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto;">
                                <i class="ti-check-circle" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-success mb-2">All Stock Levels Good!</h5>
                            <p class="text-muted mb-4">No drugs are currently low on stock or out of stock.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-primary">
                                    <i class="ti-package me-2"></i>View All Drugs
                                </a>
                                <a href="{{ route('facility.pharmacy.create') }}" class="btn btn-outline-primary">
                                    <i class="ti-plus me-2"></i>Add New Drug
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Restock Modal -->
    <div class="modal fade" id="quickRestockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Restock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="quickRestockForm" method="POST" action="{{ route('facility.pharmacy.stock.update') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Drug</label>
                            <input type="text" class="form-control" id="restockDrugName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Add Quantity</label>
                            <input type="number" class="form-control" id="restockQuantity" min="1"
                                value="50" required>
                        </div>
                        <input type="hidden" name="selected_drugs[]" id="restockDrugId">
                        <div id="stockUpdateInputs">
                            <!-- Stock update inputs will be added dynamically -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti-plus me-1"></i>Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Restock Modal -->
    <div class="modal fade" id="bulkRestockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Restock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkRestockForm" method="POST" action="{{ route('facility.pharmacy.stock.update') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Selected Drugs</label>
                            <div id="selectedDrugsList" class="border rounded p-2 bg-light"
                                style="max-height: 150px; overflow-y: auto;">
                                <!-- Selected drugs will be listed here -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Add Quantity</label>
                            <input type="number" class="form-control" id="bulkRestockQuantity" min="1"
                                value="50" required>
                            <small class="text-muted">This quantity will be added to all selected drugs</small>
                        </div>
                        <div id="bulkDrugInputs">
                            <!-- Hidden inputs for selected drugs will be added here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti-plus me-1"></i>Restock All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function quickRestock(drugId, drugName) {
            document.getElementById('restockDrugId').value = drugId;
            document.getElementById('restockDrugName').value = drugName;
            document.getElementById('restockQuantity').value = '50';

            // Create dynamic stock update inputs
            const stockUpdateInputs = document.getElementById('stockUpdateInputs');
            stockUpdateInputs.innerHTML = `
                <input type="hidden" name="stock_updates[${drugId}][drug_id]" value="${drugId}">
                <input type="hidden" name="stock_updates[${drugId}][quantity_change]" id="restockQuantityInput" value="50">
                <input type="hidden" name="stock_updates[${drugId}][operation]" value="add">
            `;

            const modal = new bootstrap.Modal(document.getElementById('quickRestockModal'));
            modal.show();
        }

        function selectAllOutStock() {
            document.querySelectorAll('.out-stock-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllOutStockCheckbox').checked = true;
        }

        function selectAllLowStock() {
            document.querySelectorAll('.low-stock-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllLowStockCheckbox').checked = true;
        }

        function bulkRestock(type) {
            const checkboxes = type === 'out' ?
                document.querySelectorAll('.out-stock-checkbox:checked') :
                document.querySelectorAll('.low-stock-checkbox:checked');

            if (checkboxes.length === 0) {
                alert('Please select at least one drug to restock.');
                return;
            }

            const selectedDrugs = [];
            const drugInputs = [];
            const selectedDrugsIds = [];

            checkboxes.forEach((checkbox, index) => {
                const drugId = checkbox.value;
                const drugName = checkbox.dataset.name;
                selectedDrugs.push(drugName);
                selectedDrugsIds.push(drugId);

                drugInputs.push(`<input type="hidden" name="stock_updates[${drugId}][drug_id]" value="${drugId}">
                    <input type="hidden" name="stock_updates[${drugId}][quantity_change]" value="50">
                    <input type="hidden" name="stock_updates[${drugId}][operation]" value="add">`);
            });

            document.getElementById('selectedDrugsList').innerHTML = selectedDrugs.map(name =>
                `<span class="badge bg-secondary me-1 mb-1">${name}</span>`
            ).join('');

            // Add selected drugs IDs and stock update inputs
            const selectedDrugsInputs = selectedDrugsIds.map(id =>
                `<input type="hidden" name="selected_drugs[]" value="${id}">`
            ).join('');

            document.getElementById('bulkDrugInputs').innerHTML = selectedDrugsInputs + drugInputs.join('');

            const modal = new bootstrap.Modal(document.getElementById('bulkRestockModal'));
            modal.show();
        }

        // Update hidden quantity input when visible input changes
        document.getElementById('restockQuantity')?.addEventListener('input', function() {
            const hiddenInput = document.getElementById('restockQuantityInput');
            if (hiddenInput) {
                hiddenInput.value = this.value;
            }
        });

        // Update bulk restock quantity when changed
        document.getElementById('bulkRestockQuantity')?.addEventListener('input', function() {
            const quantity = this.value;
            const inputs = document.querySelectorAll('#bulkDrugInputs input[name*="quantity_change"]');
            inputs.forEach(input => {
                input.value = quantity;
            });
        });

        // Select all checkbox functionality
        document.getElementById('selectAllOutStockCheckbox')?.addEventListener('change', selectAllOutStock);
        document.getElementById('selectAllLowStockCheckbox')?.addEventListener('change', selectAllLowStock);
    </script>

    <style>
        .table th {
            border-bottom: 2px solid #e9ecef !important;
        }

        .table td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f8f9fa !important;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .progress {
            background-color: #e9ecef;
        }

        .card-header {
            border-bottom: none;
        }
    </style>
@endsection
