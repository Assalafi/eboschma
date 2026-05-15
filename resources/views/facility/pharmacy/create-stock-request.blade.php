@extends('layouts.facility')

@section('title', 'Create Stock Request')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Create Stock Request
                    </h2>
                    <div class="text-muted mt-1">Submit a new drug stock request for approval</div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('facility.pharmacy.stock-requests.store') }}">
                                @csrf

                                <!-- Program Selection -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Program <span class="text-danger">*</span></label>
                                            <select name="program_id" class="form-select" required>
                                                <option value="">Select a program...</option>
                                                @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}
                                                        {{ stripos($program->name, 'formal') !== false ? 'selected' : '' }}>
                                                        {{ $program->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('program_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Bulk Request Section -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Drug Requests</h5>
                                    </div>

                                    <div id="drug-requests-container">
                                        <!-- Initial Drug Request -->
                                        <div class="drug-request-item border rounded p-3 mb-3" data-request-index="0">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Drug #1</h6>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="removeDrugRequest(this)" style="display: none;">
                                                    <i class="ti-x"></i> Remove
                                                </button>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Drug <span
                                                                class="text-danger">*</span></label>
                                                        <select name="requests[0][drug_id]"
                                                            class="form-select drug-select searchable-select" required>
                                                            <option value="">Select a drug...</option>
                                                            @foreach ($drugs as $drug)
                                                                <option value="{{ $drug->id }}"
                                                                    data-price="{{ $drug->unit_price }}"
                                                                    {{ old('requests.0.drug_id') == $drug->id ? 'selected' : '' }}>
                                                                    {{ $drug->name }} ({{ $drug->strength }}
                                                                    {{ $drug->unit }}) -
                                                                    ₦{{ number_format($drug->unit_price, 2) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Priority <span
                                                                class="text-danger">*</span></label>
                                                        <select name="requests[0][priority]" class="form-select" required>
                                                            <option value="">Select priority...</option>
                                                            @foreach ($priorities as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('requests.0.priority') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Quantity Requested <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" name="requests[0][quantity_requested]"
                                                            class="form-control quantity-input"
                                                            value="{{ old('requests.0.quantity_requested') }}" required
                                                            min="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Estimated Cost (₦) <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" name="requests[0][estimated_cost]"
                                                            class="form-control cost-input"
                                                            value="{{ old('requests.0.estimated_cost') }}" required
                                                            min="0" step="0.01" placeholder="Auto-calculated"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-primary" onclick="addDrugRequest()">
                                        <i class="ti-plus me-1"></i> Add Drug
                                    </button>

                                    <!-- Total Cost Summary -->
                                    <div class="alert alert-info">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>Total Estimated Cost:</strong>
                                            <strong class="text-primary fs-5">₦<span id="total-cost">0.00</span></strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Reason for Request <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="4" required
                                        placeholder="Please explain why this stock is needed...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Any additional information...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('facility.pharmacy.stock-requests') }}" class="btn btn-secondary">
                                        <i class="ti-arrow-left me-1"></i> Back to Requests
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-send me-1"></i> Submit Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Request Guidelines</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5 class="text-primary">📋 Process Overview</h5>
                                <ol class="mb-0">
                                    <li>Submit stock request</li>
                                    <li>Boschma admin reviews</li>
                                    <li>Request approved/rejected</li>
                                    <li>If approved, stock is dispensed</li>
                                </ol>
                            </div>

                            <div class="mb-3">
                                <h5 class="text-warning">⚠️ Important Notes</h5>
                                <ul class="mb-0">
                                    <li>Requests require Boschma admin approval</li>
                                    <li>Provide accurate quantity estimates</li>
                                    <li>Include detailed justification</li>
                                    <li>Urgent requests will be prioritized</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h5 class="text-info">💡 Tips</h5>
                                <ul class="mb-0">
                                    <li>Check current stock levels first</li>
                                    <li>Request minimum required quantity</li>
                                    <li>Consider expiry dates when ordering</li>
                                    <li>Plan ahead to avoid emergencies</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Facility</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3" style="background-image: url(/images/facility-placeholder.png)">
                                </div>
                                <div>
                                    <div class="font-weight-medium">{{ Auth::user()->facility->name }}</div>
                                    <div class="text-muted">{{ Auth::user()->facility->type }}</div>
                                </div>
                            </div>
                            <div class="text-muted">
                                <small>All requests will be submitted for this facility.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let drugRequestCount = 1;

        function addDrugRequest() {
            const container = document.getElementById('drug-requests-container');
            const requestHtml = `
                <div class="drug-request-item border rounded p-3 mb-3" data-request-index="${drugRequestCount}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Drug #${drugRequestCount + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeDrugRequest(this)">
                            <i class="ti-x"></i> Remove
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Drug <span class="text-danger">*</span></label>
                                <select name="requests[${drugRequestCount}][drug_id]" class="form-select drug-select searchable-select" required>
                                    <option value="">Select a drug...</option>
                                    @foreach ($drugs as $drug)
                                        <option value="{{ $drug->id }}" data-price="{{ $drug->unit_price }}">
                                            {{ $drug->name }} ({{ $drug->strength }} {{ $drug->unit }}) - ₦{{ number_format($drug->unit_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="requests[${drugRequestCount}][priority]" class="form-select" required>
                                    <option value="">Select priority...</option>
                                    @foreach ($priorities as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantity Requested <span class="text-danger">*</span></label>
                                <input type="number" name="requests[${drugRequestCount}][quantity_requested]" class="form-control quantity-input" required min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estimated Cost (₦) <span class="text-danger">*</span></label>
                                <input type="number" name="requests[${drugRequestCount}][estimated_cost]" class="form-control cost-input" required min="0" step="0.01" placeholder="Auto-calculated" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', requestHtml);
            drugRequestCount++;
            updateRemoveButtons();
            attachEventListeners();
            initializeSearchableSelects();
        }

        function removeDrugRequest(button) {
            const requestItem = button.closest('.drug-request-item');
            requestItem.remove();
            updateRemoveButtons();
            updateTotalCost();
        }

        function updateRemoveButtons() {
            const requestItems = document.querySelectorAll('.drug-request-item');
            requestItems.forEach((item, index) => {
                const removeButton = item.querySelector('button[onclick*="removeDrugRequest"]');
                const title = item.querySelector('h6');

                if (requestItems.length > 1) {
                    removeButton.style.display = 'block';
                    title.textContent = `Drug #${index + 1}`;
                } else {
                    removeButton.style.display = 'none';
                    title.textContent = 'Drug #1';
                }
            });
        }

        function updateEstimatedCost(requestItem) {
            const drugSelect = requestItem.querySelector('.drug-select');
            const quantityInput = requestItem.querySelector('.quantity-input');
            const costInput = requestItem.querySelector('.cost-input');

            const selectedOption = drugSelect.options[drugSelect.selectedIndex];
            const unitPrice = parseFloat(selectedOption.dataset.price) || 0;
            const quantity = parseInt(quantityInput.value) || 0;

            if (unitPrice > 0 && quantity > 0) {
                const estimatedCost = quantity * unitPrice;
                costInput.value = estimatedCost.toFixed(2);
            } else {
                costInput.value = '';
            }

            updateTotalCost();
        }

        function updateTotalCost() {
            const costInputs = document.querySelectorAll('.cost-input');
            let total = 0;

            costInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });

            document.getElementById('total-cost').textContent = total.toFixed(2);
        }

        function attachEventListeners() {
            const requestItems = document.querySelectorAll('.drug-request-item');

            requestItems.forEach(item => {
                const drugSelect = item.querySelector('.drug-select');
                const quantityInput = item.querySelector('.quantity-input');

                // Remove existing listeners
                drugSelect.removeEventListener('change', () => updateEstimatedCost(item));
                quantityInput.removeEventListener('input', () => updateEstimatedCost(item));

                // Add new listeners
                drugSelect.addEventListener('change', () => updateEstimatedCost(item));
                quantityInput.addEventListener('input', () => updateEstimatedCost(item));
            });
        }

        function initializeSearchableSelects() {
            const searchableSelects = document.querySelectorAll('.searchable-select');

            searchableSelects.forEach(select => {
                // Skip if already processed (check if it's hidden)
                if (select.style.display === 'none') {
                    return;
                }
                // Convert select to searchable input
                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                wrapper.style.width = '100%';

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.placeholder = 'Type to search drugs...';
                input.required = select.required;

                const dropdown = document.createElement('div');
                dropdown.className = 'position-absolute w-100 bg-white border rounded-top-0 border-top-0 shadow';
                dropdown.style.maxHeight = '200px';
                dropdown.style.overflowY = 'auto';
                dropdown.style.zIndex = '1000';
                dropdown.style.display = 'none';

                // Store original select data (exclude placeholder option)
                const options = Array.from(select.options)
                    .filter(option => option.value !== '') // Exclude placeholder
                    .map(option => ({
                        value: option.value,
                        text: option.textContent.replace(/\s+/g, ' ')
                            .trim(), // Remove extra spaces and line breaks
                        price: option.dataset.price || 0
                    }));

                let selectedValue = select.value;

                // Update input when option is selected
                function updateInputDisplay() {
                    const selected = options.find(opt => opt.value === selectedValue);
                    if (selected) {
                        input.value = selected.text;
                        input.placeholder = 'Type to search drugs...'; // Reset placeholder
                    } else {
                        input.value = '';
                        input.placeholder = 'Type to search drugs...';
                    }
                }

                // Filter options based on input
                function filterOptions(searchTerm) {
                    dropdown.innerHTML = '';

                    // Only show results after 3 characters
                    if (searchTerm.length < 3) {
                        dropdown.innerHTML =
                            '<div class="p-2 text-muted">Type at least 3 characters to search...</div>';
                        return;
                    }

                    const filtered = options.filter(option =>
                        option.text.toLowerCase().includes(searchTerm.toLowerCase())
                    );

                    if (filtered.length === 0) {
                        dropdown.innerHTML = '<div class="p-2 text-muted">No drugs found</div>';
                    } else {
                        filtered.forEach(option => {
                            const item = document.createElement('div');
                            item.className = 'p-2 hover-bg-light cursor-pointer';
                            item.style.cursor = 'pointer';
                            item.textContent = option.text;
                            item.onclick = () => {
                                selectedValue = option.value;
                                updateInputDisplay();
                                dropdown.style.display = 'none';

                                // Trigger change event for cost calculation
                                select.value = selectedValue;
                                select.dispatchEvent(new Event('change'));
                            };
                            dropdown.appendChild(item);
                        });
                    }
                }

                // Event listeners
                input.addEventListener('focus', () => {
                    filterOptions(input.value);
                    dropdown.style.display = 'block';
                });

                input.addEventListener('input', () => {
                    filterOptions(input.value);
                    dropdown.style.display = 'block';
                });

                input.addEventListener('blur', (e) => {
                    // Delay hiding to allow clicking on options
                    setTimeout(() => {
                        dropdown.style.display = 'none';
                    }, 200);
                });

                // Initialize
                updateInputDisplay();

                // Replace select with searchable input
                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(input);
                wrapper.appendChild(dropdown);
                select.style.display = 'none';

                // Store reference to original select for form submission
                wrapper.dataset.originalSelect = select.name;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            attachEventListeners();
            updateRemoveButtons();
            initializeSearchableSelects();
        });
    </script>
@endpush
