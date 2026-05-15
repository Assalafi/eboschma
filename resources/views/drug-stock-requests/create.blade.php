@extends('layouts.app')

@section('title', 'Create Drug Stock Request')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <ol class="breadcrumb breadcrumb-arrows mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('drug-stock-requests.index') }}">Stock Requests</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Create Stock Request
                    </h2>
                    <div class="text-muted mt-1">Submit a new drug stock request for approval</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('drug-stock-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="ti-arrow-left me-1"></i>Back
                    </a>
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
                            <form method="POST" action="{{ route('drug-stock-requests.store') }}">
                                @csrf

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

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Drug <span class="text-danger">*</span></label>
                                            <select name="drug_id" class="form-select searchable-select" required>
                                                <option value="">Select a drug...</option>
                                                @foreach ($drugs as $drug)
                                                    <option value="{{ $drug->id }}"
                                                        {{ old('drug_id') == $drug->id ? 'selected' : '' }}>
                                                        {{ $drug->name }} ({{ $drug->strength }} {{ $drug->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('drug_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                                            <select name="priority" class="form-select" required>
                                                <option value="">Select priority...</option>
                                                @foreach ($priorities as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('priority') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('priority')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity Requested <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="quantity_requested" class="form-control"
                                                value="{{ old('quantity_requested') }}" required min="1">
                                            @error('quantity_requested')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estimated Cost (₦) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="estimated_cost" class="form-control"
                                                value="{{ old('estimated_cost') }}" required min="0" step="0.01">
                                            @error('estimated_cost')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
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
                                    <a href="{{ route('drug-stock-requests.index') }}" class="btn btn-secondary">
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
        // Auto-calculate estimated cost based on quantity and unit price
        document.addEventListener('DOMContentLoaded', function() {
            const drugSelect = document.querySelector('select[name="drug_id"]');
            const quantityInput = document.querySelector('input[name="quantity_requested"]');
            const costInput = document.querySelector('input[name="estimated_cost"]');

            // Store drug prices in JavaScript
            const drugPrices = @json(
                $drugs->mapWithKeys(function ($drug) {
                    return [$drug->id => $drug->unit_price];
                }));

            function updateEstimatedCost() {
                const drugId = drugSelect.value;
                const quantity = parseInt(quantityInput.value) || 0;

                if (drugId && drugPrices[drugId]) {
                    const unitPrice = drugPrices[drugId];
                    const estimatedCost = quantity * unitPrice;
                    costInput.value = estimatedCost.toFixed(2);
                }
            }

            drugSelect.addEventListener('change', updateEstimatedCost);
            quantityInput.addEventListener('input', updateEstimatedCost);

            // Initialize searchable drug select
            initializeSearchableSelects();
        });

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
            });
        }
    </script>
@endpush
