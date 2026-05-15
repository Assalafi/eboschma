@extends('layouts.app')

@section('title', 'Stock In - Drug Store')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Stock In - Add to Store
                    </h2>
                    <div class="text-muted mt-1">Register new drug batches into the central store</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('drug-store.index') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left me-1"></i> Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('drug-store.stock-in') }}" id="stockInForm">
                @csrf

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Program</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
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
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Stock Entries</h4>
                    <button type="button" class="btn btn-primary" onclick="addEntry()">
                        <i class="ti-plus me-1"></i> Add Another Drug
                    </button>
                </div>

                <div id="entries-container">
                    <!-- First entry -->
                    <div class="card mb-3 stock-entry" data-index="0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Entry #1</h3>
                            <button type="button" class="btn btn-sm btn-danger remove-entry-btn"
                                onclick="removeEntry(this)" style="display: none;">
                                <i class="ti-x me-1"></i> Remove
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Drug <span class="text-danger">*</span></label>
                                        <select name="entries[0][drug_id]" class="form-select searchable-drug-select"
                                            required>
                                            <option value="">Select a drug...</option>
                                            @foreach ($drugs as $drug)
                                                <option value="{{ $drug->id }}" data-price="{{ $drug->unit_price }}"
                                                    {{ $selectedDrugId == $drug->id ? 'selected' : '' }}>
                                                    {{ $drug->name }} ({{ $drug->strength }} {{ $drug->unit }}) -
                                                    ₦{{ number_format($drug->unit_price, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                                        <input type="text" name="entries[0][batch_number]" class="form-control"
                                            placeholder="e.g. BATCH-2026-001" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="entries[0][quantity]"
                                            class="form-control quantity-input" min="1" required
                                            placeholder="Number of units">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Unit Cost (₦) <span class="text-danger">*</span></label>
                                        <input type="number" name="entries[0][unit_cost]" class="form-control cost-input"
                                            min="0" step="0.01" required placeholder="Cost per unit">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                        <input type="date" name="entries[0][expiry_date]" class="form-control"
                                            value="{{ date('Y-m-d', strtotime('+2 years')) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                        <input type="text" name="entries[0][supplier]" class="form-control"
                                            placeholder="Supplier name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <input type="text" name="entries[0][notes]" class="form-control"
                                            placeholder="Optional notes">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex align-items-center text-muted">
                                        <span class="me-2">Subtotal:</span>
                                        <strong class="entry-subtotal text-primary">₦0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grand Total -->
                <div class="card mb-3 border-primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Grand Total</h4>
                            <small class="text-muted">Total value of all entries</small>
                        </div>
                        <h3 class="mb-0 text-primary" id="grandTotal">₦0.00</h3>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('drug-store.index') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="ti-check me-1"></i> Confirm Stock In
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let entryIndex = 0;

        @php
            $drugOptionsData = $drugs
                ->map(function ($drug) {
                    return [
                        'id' => $drug->id,
                        'name' => $drug->name,
                        'strength' => $drug->strength,
                        'unit' => $drug->unit,
                        'unit_price' => $drug->unit_price,
                        'text' => $drug->name . ' (' . $drug->strength . ' ' . $drug->unit . ') - NGN' . number_format($drug->unit_price, 2),
                    ];
                })
                ->values();
        @endphp
        const drugOptions = @json($drugOptionsData);

        function addEntry() {
            entryIndex++;
            const container = document.getElementById('entries-container');
            const html = `
                <div class="card mb-3 stock-entry" data-index="${entryIndex}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Entry #${entryIndex + 1}</h3>
                        <button type="button" class="btn btn-sm btn-danger remove-entry-btn" onclick="removeEntry(this)">
                            <i class="ti-x me-1"></i> Remove
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Drug <span class="text-danger">*</span></label>
                                    <select name="entries[${entryIndex}][drug_id]" class="form-select searchable-drug-select" required>
                                        <option value="">Select a drug...</option>
                                        ${drugOptions.map(d => '<option value="' + d.id + '" data-price="' + d.unit_price + '">' + d.text + '</option>').join('')}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                                    <input type="text" name="entries[${entryIndex}][batch_number]" class="form-control" placeholder="e.g. BATCH-2026-001" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="entries[${entryIndex}][quantity]" class="form-control quantity-input" min="1" required placeholder="Number of units">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Unit Cost (₦) <span class="text-danger">*</span></label>
                                    <input type="number" name="entries[${entryIndex}][unit_cost]" class="form-control cost-input" min="0" step="0.01" required placeholder="Cost per unit">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" name="entries[${entryIndex}][expiry_date]" class="form-control" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <input type="text" name="entries[${entryIndex}][supplier]" class="form-control" placeholder="Supplier name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="entries[${entryIndex}][notes]" class="form-control" placeholder="Optional notes">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex align-items-center text-muted">
                                    <span class="me-2">Subtotal:</span>
                                    <strong class="entry-subtotal text-primary">₦0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            updateRemoveButtons();
            initSearchableSelects();
        }

        function removeEntry(btn) {
            btn.closest('.stock-entry').remove();
            updateRemoveButtons();
            renumberEntries();
            updateGrandTotal();
        }

        function updateRemoveButtons() {
            const entries = document.querySelectorAll('.stock-entry');
            entries.forEach(e => {
                const btn = e.querySelector('.remove-entry-btn');
                btn.style.display = entries.length > 1 ? '' : 'none';
            });
        }

        function renumberEntries() {
            document.querySelectorAll('.stock-entry').forEach((entry, i) => {
                entry.querySelector('.card-title').textContent = `Entry #${i + 1}`;
            });
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.stock-entry').forEach(entry => {
                const qty = parseFloat(entry.querySelector('.quantity-input')?.value) || 0;
                const cost = parseFloat(entry.querySelector('.cost-input')?.value) || 0;
                const subtotal = qty * cost;
                entry.querySelector('.entry-subtotal').textContent = '₦' + subtotal.toLocaleString('en-NG', {
                    minimumFractionDigits: 2
                });
                total += subtotal;
            });
            document.getElementById('grandTotal').textContent = '₦' + total.toLocaleString('en-NG', {
                minimumFractionDigits: 2
            });
        }

        // Auto-fill unit cost when drug is selected
        document.addEventListener('change', function(e) {
            if (e.target.matches('.searchable-drug-select') || e.target.matches('select[name*="drug_id"]')) {
                const selected = e.target.options[e.target.selectedIndex];
                const price = selected?.dataset?.price;
                const entry = e.target.closest('.stock-entry');
                if (price && entry) {
                    entry.querySelector('.cost-input').value = parseFloat(price).toFixed(2);
                    updateGrandTotal();
                }
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.matches('.quantity-input') || e.target.matches('.cost-input')) {
                updateGrandTotal();
            }
        });

        // Initialize searchable selects
        function initSearchableSelects() {
            document.querySelectorAll('.searchable-drug-select').forEach(select => {
                if (select.style.display === 'none') return;

                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                wrapper.style.width = '100%';

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.placeholder = 'Type to search drugs...';

                const dropdown = document.createElement('div');
                dropdown.className = 'position-absolute w-100 bg-white border rounded-top-0 border-top-0 shadow';
                dropdown.style.maxHeight = '200px';
                dropdown.style.overflowY = 'auto';
                dropdown.style.zIndex = '1000';
                dropdown.style.display = 'none';

                const options = Array.from(select.options)
                    .filter(o => o.value !== '')
                    .map(o => ({
                        value: o.value,
                        text: o.textContent.replace(/\s+/g, ' ').trim(),
                        price: o.dataset.price
                    }));

                let selectedValue = select.value;

                function updateInput() {
                    const sel = options.find(o => o.value === selectedValue);
                    input.value = sel ? sel.text : '';
                }

                function filterOptions(term) {
                    dropdown.innerHTML = '';
                    if (term.length < 2) {
                        dropdown.innerHTML = '<div class="p-2 text-muted">Type at least 2 characters...</div>';
                        return;
                    }
                    const filtered = options.filter(o => o.text.toLowerCase().includes(term.toLowerCase()));
                    if (!filtered.length) {
                        dropdown.innerHTML = '<div class="p-2 text-muted">No drugs found</div>';
                    } else {
                        filtered.forEach(o => {
                            const item = document.createElement('div');
                            item.className = 'p-2';
                            item.style.cursor = 'pointer';
                            item.textContent = o.text;
                            item.onmouseenter = () => item.style.background = '#f0f0f0';
                            item.onmouseleave = () => item.style.background = '';
                            item.onclick = () => {
                                selectedValue = o.value;
                                updateInput();
                                dropdown.style.display = 'none';
                                select.value = selectedValue;
                                select.dispatchEvent(new Event('change'));
                            };
                            dropdown.appendChild(item);
                        });
                    }
                }

                input.addEventListener('focus', () => {
                    filterOptions(input.value);
                    dropdown.style.display = 'block';
                });
                input.addEventListener('input', () => {
                    filterOptions(input.value);
                    dropdown.style.display = 'block';
                });
                input.addEventListener('blur', () => setTimeout(() => dropdown.style.display = 'none', 200));

                updateInput();
                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(input);
                wrapper.appendChild(dropdown);
                select.style.display = 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initSearchableSelects();
            updateGrandTotal();
        });
    </script>
@endpush
