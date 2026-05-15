@extends('layouts.facility')

@section('title', 'Update Stock Levels')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Update
                                    Stock Levels</h1>
                                <p class="text-muted mb-0">Bulk update drug quantities for your facility's pharmacy</p>
                            </div>
                            <div>
                                <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Back to Pharmacy
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-check-circle me-2"></i>
                                    <span>{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-alert-circle me-2"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-danger">{{ $outOfStock }}</h3>
                        <p class="mb-0 text-muted">Out of Stock</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Controls -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="{{ route('facility.pharmacy.stock') }}" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search drugs..."
                        value="{{ request('search') }}">
                    <button type="submit" class="btn">
                        <i class="fas fa-search"></i>
                    </button>
                    @if (request('search') || request('stock_filter') || request('dosage_form'))
                        <a href="{{ route('facility.pharmacy.stock') }}" class="btn">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
            <div class="col-md-3">
                <form method="GET" action="{{ route('facility.pharmacy.stock') }}">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if (request('dosage_form'))
                        <input type="hidden" name="dosage_form" value="{{ request('dosage_form') }}">
                    @endif
                    <select name="stock_filter" class="form-select" onchange="this.form.submit()">
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" {{ request('stock_filter') == 'in_stock' ? 'selected' : '' }}>
                            In Stock (> 10)
                        </option>
                        <option value="low_stock" {{ request('stock_filter') == 'low_stock' ? 'selected' : '' }}>
                            Low Stock (1-10)
                        </option>
                        <option value="out_of_stock" {{ request('stock_filter') == 'out_of_stock' ? 'selected' : '' }}>
                            Out of Stock (0)
                        </option>
                    </select>
                </form>
            </div>
            <div class="col-md-3">
                <form method="GET" action="{{ route('facility.pharmacy.stock') }}">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if (request('stock_filter'))
                        <input type="hidden" name="stock_filter" value="{{ request('stock_filter') }}">
                    @endif
                    <select name="dosage_form" class="form-select" onchange="this.form.submit()">
                        <option value="">All Forms</option>
                        @foreach ($dosageForms as $form)
                            <option value="{{ $form }}" {{ request('dosage_form') == $form ? 'selected' : '' }}>
                                {{ $form }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="selectAll()">
                            <i class="fas fa-check-square me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="deselectAll()">
                            <i class="fas fa-square me-1"></i>Deselect All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success me-2"
                            onclick="setOperationForAll('add')">
                            <i class="fas fa-plus me-1"></i>Set All to Add
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning me-2"
                            onclick="setOperationForAll('subtract')">
                            <i class="fas fa-minus me-1"></i>Set All to Subtract
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info me-2" onclick="setOperationForAll('set')">
                            <i class="fas fa-equals me-1"></i>Set All to Set
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetForm()">
                            <i class="fas fa-undo me-1"></i>Reset Form
                        </button>
                    </div>
                    <div class="text-muted">
                        Showing {{ $drugs->firstItem() }} to {{ $drugs->lastItem() }} of {{ $drugs->total() }} drugs
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('facility.pharmacy.stock.update') }}" id="stockForm">
            @csrf

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                            </th>
                            <th width="25%">Drug Name</th>
                            <th width="15%">Form & Strength</th>
                            <th width="10%">Current Stock</th>
                            <th width="15%">Operation</th>
                            <th width="15%">Quantity</th>
                            <th width="15%">New Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drugs as $drug)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input drug-checkbox" name="selected_drugs[]"
                                        value="{{ $drug->id }}">
                                </td>
                                <td>
                                    <strong>{{ $drug->name }}</strong>
                                    @if ($drug->description)
                                        <div class="text-muted small">
                                            {{ Str::limit($drug->description, 30) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $drug->dosage_form }}</span>
                                    <div class="text-muted small">{{ $drug->strength }} {{ $drug->unit }}
                                    </div>
                                </td>
                                <td>
                                    @if ($drug->quantity == 0)
                                        <span class="badge bg-danger current-stock"
                                            data-original-stock="{{ $drug->quantity }}">{{ $drug->quantity }}</span>
                                    @elseif($drug->quantity <= 10)
                                        <span class="badge bg-warning current-stock"
                                            data-original-stock="{{ $drug->quantity }}">{{ $drug->quantity }}</span>
                                    @else
                                        <span class="badge bg-success current-stock"
                                            data-original-stock="{{ $drug->quantity }}">{{ $drug->quantity }}</span>
                                    @endif
                                </td>
                                <td>
                                    <select class="form-select form-select-sm operation-select"
                                        name="stock_updates[{{ $drug->id }}][operation]"
                                        onchange="calculateNewStock('{{ $drug->id }}')">
                                        <option value="add">Add (+)</option>
                                        <option value="subtract">Subtract (-)</option>
                                        <option value="set">Set to (=)</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm quantity-input"
                                        name="stock_updates[{{ $drug->id }}][quantity_change]" value="0"
                                        min="0" onchange="calculateNewStock('{{ $drug->id }}')"
                                        oninput="calculateNewStock('{{ $drug->id }}')">
                                    <input type="hidden" name="stock_updates[{{ $drug->id }}][drug_id]"
                                        value="{{ $drug->id }}">
                                </td>
                                <td>
                                    <span class="fw-bold new-stock"
                                        id="new-stock-{{ $drug->id }}">{{ $drug->quantity }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x mb-2"></i>
                                    <p>No drugs found matching your criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($drugs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $drugs->links('pagination.bootstrap-5') }}
                </div>
            @endif

            <!-- Submit Button -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Update Stock Levels
                    </button>
                </div>
            </div>
        </form>
    </div>
    </div>
    </div>
    </div>

    <script>
        function calculateNewStock(drugId) {
            const operation = document.querySelector(`select[name="stock_updates[${drugId}][operation]"]`).value;
            const quantity = parseInt(document.querySelector(`input[name="stock_updates[${drugId}][quantity_change]"]`)
                .value) || 0;

            // Get original stock from the data attribute (this is the actual current stock from database)
            const currentStockElement = document.querySelector(`#new-stock-${drugId}`).closest('tr').querySelector(
                '.current-stock');

            if (!currentStockElement) {
                console.error('Could not find current stock element for drug:', drugId);
                return;
            }

            const originalStock = parseInt(currentStockElement.dataset.originalStock) || 0;

            let newStock = originalStock;

            switch (operation) {
                case 'add':
                    newStock = originalStock + quantity;
                    break;
                case 'subtract':
                    newStock = Math.max(0, originalStock - quantity);
                    break;
                case 'set':
                    newStock = quantity;
                    break;
            }

            const newStockElement = document.getElementById(`new-stock-${drugId}`);
            newStockElement.textContent = newStock;

            // Update badge color
            const parentRow = newStockElement.closest('tr');
            const currentBadge = parentRow.querySelector('.badge');

            if (newStock === 0) {
                newStockElement.className = 'fw-bold text-danger';
            } else if (newStock <= 10) {
                newStockElement.className = 'fw-bold text-warning';
            } else {
                newStockElement.className = 'fw-bold text-success';
            }
        }

        function selectAll() {
            document.querySelectorAll('.drug-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllCheckbox').checked = true;
        }

        function deselectAll() {
            document.querySelectorAll('.drug-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAllCheckbox').checked;
            document.querySelectorAll('.drug-checkbox').forEach(checkbox => {
                checkbox.checked = selectAll;
            });
        }

        function setOperationForAll(operation) {
            document.querySelectorAll('.operation-select').forEach(select => {
                select.value = operation;
                // Trigger change to recalculate
                const match = select.name.match(/stock_updates\[([^\]]+)\]/);
                if (!match) {
                    return;
                }
                const drugId = match[1];
                calculateNewStock(drugId);
            });
        }

        function resetForm() {
            document.getElementById('stockForm').reset();
            document.querySelectorAll('.new-stock').forEach((element) => {
                const drugId = element.id.replace('new-stock-', '');
                // Get original stock from the data attribute
                const currentStockElement = element.closest('tr').querySelector('.current-stock');
                const originalStock = parseInt(currentStockElement.dataset.originalStock) || 0;
                element.textContent = originalStock;
                // Reset styling
                element.className = 'fw-bold';
                // Reset badge styling based on original stock
                if (originalStock === 0) {
                    currentStockElement.className = 'badge bg-danger current-stock';
                } else if (originalStock <= 10) {
                    currentStockElement.className = 'badge bg-warning current-stock';
                } else {
                    currentStockElement.className = 'badge bg-success current-stock';
                }
            });
            deselectAll();
        }

        // Form submission validation
        document.getElementById('stockForm').addEventListener('submit', function(e) {
            const selectedDrugs = document.querySelectorAll('.drug-checkbox:checked');
            if (selectedDrugs.length === 0) {
                e.preventDefault();
                alert('Please select at least one drug to update.');
                return false;
            }

            let hasChanges = false;
            selectedDrugs.forEach(checkbox => {
                const drugId = checkbox.value;
                const quantity = parseInt(document.querySelector(
                    `input[name="stock_updates[${drugId}][quantity_change]"]`).value) || 0;
                const operation = document.querySelector(
                    `select[name="stock_updates[${drugId}][operation]"]`).value;

                if (quantity > 0 || operation === 'set') {
                    hasChanges = true;
                }
            });

            if (!hasChanges) {
                e.preventDefault();
                alert(
                    'Please enter quantities greater than 0 for selected drugs, or use the Set operation with quantity 0.'
                );
                return false;
            }
        });

        // Select all checkbox functionality
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            selectAll();
        });
    </script>

    <style>
        .form-control:focus,
        .form-select:focus {
            border-color: #01542B;
            box-shadow: 0 0 0 0.2rem rgba(1, 84, 43, 0.25);
        }

        .btn-primary {
            background-color: #01542B;
            border-color: #01542B;
        }

        .btn-primary:hover {
            background-color: #014121;
            border-color: #014121;
        }

        .card {
            border: none;
        }

        .table th {
            border-bottom: 2px solid #e9ecef !important;
        }

        .table td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f8f9fa !important;
        }

        .form-check-input:checked {
            background-color: #01542B;
            border-color: #01542B;
        }
    </style>
@endsection
