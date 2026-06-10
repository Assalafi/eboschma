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
                <div class="col-auto">
                    <a href="{{ route('facility.pharmacy.stock-requests') }}" class="btn btn-outline-secondary">
                        <i class="ti-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->has('requests'))
                <div class="alert alert-danger">{{ $errors->first('requests') }}</div>
            @endif

            @if(isset($walletCount) && $walletCount === 0)
                {{-- NO WALLET: show full page block --}}
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card border-danger">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <span class="avatar avatar-xl bg-danger-lt">
                                        <i class="ti-wallet text-danger" style="font-size:2rem;"></i>
                                    </span>
                                </div>
                                <h3 class="text-danger">Cannot Create Request</h3>
                                <p class="text-muted mb-4">Your facility does not have any wallets set up. Please contact the administrator to create a wallet before making a stock request.</p>
                                <a href="{{ route('facility.pharmacy.stock-requests') }}" class="btn btn-secondary">
                                    <i class="ti-arrow-left me-1"></i> Back to Requests
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="{{ route('facility.pharmacy.stock-requests.store') }}" id="stockRequestForm">
                                    @csrf

                                    {{-- Program Selection --}}
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Program <span class="text-danger">*</span></label>
                                                <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                                    <option value="">Select a program...</option>
                                                    @foreach ($programs as $program)
                                                        <option value="{{ $program->id }}"
                                                            {{ old('program_id') == $program->id ? 'selected' : '' }}
                                                            {{ stripos($program->name, 'formal') !== false && !old('program_id') ? 'selected' : '' }}>
                                                            {{ $program->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('program_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bulk Request Section --}}
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Drug Requests</h5>
                                        </div>

                                        <div id="drug-requests-container">
                                            {{-- Initial Drug Request --}}
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
                                                            <label class="form-label">Drug <span class="text-danger">*</span></label>
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
                                                            <label class="form-label">Priority <span class="text-danger">*</span></label>
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
                                                            <label class="form-label">Quantity Requested <span class="text-danger">*</span></label>
                                                            <input type="number" name="requests[0][quantity_requested]"
                                                                class="form-control quantity-input"
                                                                value="{{ old('requests.0.quantity_requested') }}" required min="1">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Estimated Cost (₦) <span class="text-danger">*</span></label>
                                                            <input type="number" name="requests[0][estimated_cost]"
                                                                class="form-control cost-input"
                                                                value="{{ old('requests.0.estimated_cost') }}" required min="0" step="0.01"
                                                                placeholder="Auto-calculated" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" id="add-drug-btn" class="btn btn-sm btn-primary" onclick="addDrugRequest()">
                                            <i class="ti-plus me-1"></i> Add Drug
                                        </button>

                                        {{-- Total & Wallet Summary --}}
                                        <div class="alert mt-3 mb-0" id="cost-summary-alert" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <div>
                                                    <strong>Total Estimated Cost:</strong>
                                                    <strong class="text-primary fs-5 ms-2">₦<span id="total-cost">0.00</span></strong>
                                                </div>
                                                <div id="wallet-info" class="d-none">
                                                    <span class="badge bg-success-lt text-success me-1"><i class="ti-wallet me-1"></i>Wallet Balance:</span>
                                                    <strong id="wallet-balance-display">—</strong>
                                                </div>
                                            </div>
                                            <div id="budget-warning" class="text-danger mt-1 d-none">
                                                <i class="ti-alert-circle me-1"></i>
                                                <span id="budget-warning-text"></span>
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
                                        <button type="submit" class="btn btn-primary" id="submit-btn">
                                            <i class="ti-send me-1"></i> Submit Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        {{-- Wallet Balance Card --}}
                        <div class="card mb-3 border-0 shadow-sm" id="wallet-card">
                            <div class="card-header" style="background:linear-gradient(135deg,#01542b,#0d9488);color:white;">
                                <h3 class="card-title text-white mb-0"><i class="ti-wallet me-2"></i>Program Wallet</h3>
                            </div>
                            <div class="card-body" id="wallet-card-body">
                                <div class="text-center text-muted py-3" id="wallet-no-program">
                                    <i class="ti-info-circle mb-2" style="font-size:2rem;opacity:0.4;"></i>
                                    <p class="mb-0 small">Select a program to see wallet balance</p>
                                </div>
                                <div id="wallet-loaded" class="d-none">
                                    <div class="mb-2">
                                        <div class="text-muted small mb-1">Wallet ID</div>
                                        <code id="wallet-id-display">—</code>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Available Balance</div>
                                        <div class="h2 mb-0" id="wallet-balance-big" style="color:#01542b;">—</div>
                                    </div>
                                    <div id="wallet-status-ok" class="alert alert-success py-2 mb-0 d-none">
                                        <i class="ti-check me-1"></i> Sufficient balance for this request
                                    </div>
                                    <div id="wallet-status-over" class="alert alert-danger py-2 mb-0 d-none">
                                        <i class="ti-alert-circle me-1"></i> Total exceeds wallet balance!
                                    </div>
                                </div>
                                <div id="wallet-error" class="d-none">
                                    <div class="alert alert-danger py-2 mb-0">
                                        <i class="ti-alert-circle me-1"></i>
                                        <span id="wallet-error-msg">No wallet found for this program.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Guidelines --}}
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Request Guidelines</h3></div>
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
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header"><h3 class="card-title">Current Facility</h3></div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar me-3" style="background-image: url(/images/facility-placeholder.png)"></div>
                                    <div>
                                        <div class="font-weight-medium">{{ Auth::user()->facility->name }}</div>
                                        <div class="text-muted">{{ Auth::user()->facility->type }}</div>
                                    </div>
                                </div>
                                <small class="text-muted">All requests will be submitted for this facility.</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Wallets from server — keyed by program_id
    const walletsByProgram = @json($walletsByProgram ?? []);

    let drugRequestCount = 1;
    let currentBalance = null;
    let hasWallet = false;

    // ─── Wallet Logic ─────────────────────────────────────────────────────────
    function onProgramChange() {
        const programId = document.getElementById('program_id').value;
        const noProgram  = document.getElementById('wallet-no-program');
        const loaded     = document.getElementById('wallet-loaded');
        const errorBox   = document.getElementById('wallet-error');

        if (!programId) {
            noProgram.classList.remove('d-none');
            loaded.classList.add('d-none');
            errorBox.classList.add('d-none');
            currentBalance = null;
            hasWallet = false;
            updateBudgetState();
            return;
        }

        const wallet = walletsByProgram[programId];

        if (wallet) {
            hasWallet = true;
            currentBalance = wallet.balance;

            document.getElementById('wallet-id-display').textContent   = wallet.wallet_number || '—';
            document.getElementById('wallet-balance-big').textContent  = '₦' + currentBalance.toLocaleString('en-NG', {minimumFractionDigits:2});
            document.getElementById('wallet-balance-display').textContent = '₦' + currentBalance.toLocaleString('en-NG', {minimumFractionDigits:2});

            noProgram.classList.add('d-none');
            loaded.classList.remove('d-none');
            errorBox.classList.add('d-none');
            document.getElementById('wallet-info').classList.remove('d-none');
        } else {
            hasWallet = false;
            currentBalance = null;

            noProgram.classList.add('d-none');
            loaded.classList.add('d-none');
            errorBox.classList.remove('d-none');
            document.getElementById('wallet-error-msg').textContent = 'No active wallet found for this program.';
            document.getElementById('wallet-info').classList.add('d-none');
        }

        updateBudgetState();
    }

    function getTotal() {
        let total = 0;
        document.querySelectorAll('.cost-input').forEach(i => {
            total += parseFloat(i.value) || 0;
        });
        return total;
    }

    function updateBudgetState() {
        const total = getTotal();
        document.getElementById('total-cost').textContent = total.toFixed(2);

        const submitBtn   = document.getElementById('submit-btn');
        const addBtn      = document.getElementById('add-drug-btn');
        const warnDiv     = document.getElementById('budget-warning');
        const warnText    = document.getElementById('budget-warning-text');
        const statusOk    = document.getElementById('wallet-status-ok');
        const statusOver  = document.getElementById('wallet-status-over');
        const summaryAlert = document.getElementById('cost-summary-alert');

        if (!hasWallet) {
            submitBtn.disabled = true;
            addBtn.disabled    = true;
            warnDiv.classList.remove('d-none');
            warnText.textContent = 'Please select a program with an active wallet before adding drugs.';
            summaryAlert.style.background = '#fef2f2';
            summaryAlert.style.borderColor = '#fca5a5';
            if (statusOk)   statusOk.classList.add('d-none');
            if (statusOver) statusOver.classList.add('d-none');
            return;
        }

        if (total > currentBalance) {
            submitBtn.disabled = true;
            addBtn.disabled    = true;
            warnDiv.classList.remove('d-none');
            warnText.textContent = `Total ₦${total.toLocaleString('en-NG',{minimumFractionDigits:2})} exceeds wallet balance ₦${currentBalance.toLocaleString('en-NG',{minimumFractionDigits:2})}.`;
            summaryAlert.style.background = '#fef2f2';
            summaryAlert.style.borderColor = '#fca5a5';
            if (statusOk)   statusOk.classList.add('d-none');
            if (statusOver) statusOver.classList.remove('d-none');
        } else {
            submitBtn.disabled = false;
            addBtn.disabled    = false;
            warnDiv.classList.add('d-none');
            summaryAlert.style.background = '#f0fdf4';
            summaryAlert.style.borderColor = '#bbf7d0';
            if (statusOk)   statusOk.classList.remove('d-none');
            if (statusOver) statusOver.classList.add('d-none');
        }
    }

    // ─── Drug Row Logic ────────────────────────────────────────────────────────
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
        updateBudgetState();
    }

    function removeDrugRequest(button) {
        button.closest('.drug-request-item').remove();
        updateRemoveButtons();
        updateBudgetState();
    }

    function updateRemoveButtons() {
        const items = document.querySelectorAll('.drug-request-item');
        items.forEach((item, index) => {
            const btn   = item.querySelector('button[onclick*="removeDrugRequest"]');
            const title = item.querySelector('h6');
            btn.style.display = items.length > 1 ? 'block' : 'none';
            title.textContent = `Drug #${index + 1}`;
        });
    }

    function updateEstimatedCost(requestItem) {
        const drugSelect   = requestItem.querySelector('.drug-select');
        const quantityInput = requestItem.querySelector('.quantity-input');
        const costInput    = requestItem.querySelector('.cost-input');
        const selected     = drugSelect.options[drugSelect.selectedIndex];
        const unitPrice    = parseFloat(selected?.dataset?.price) || 0;
        const quantity     = parseInt(quantityInput.value) || 0;

        costInput.value = (unitPrice > 0 && quantity > 0) ? (quantity * unitPrice).toFixed(2) : '';
        updateBudgetState();
    }

    function attachEventListeners() {
        document.querySelectorAll('.drug-request-item').forEach(item => {
            const drug = item.querySelector('.drug-select');
            const qty  = item.querySelector('.quantity-input');
            drug.onchange = () => updateEstimatedCost(item);
            qty.oninput   = () => updateEstimatedCost(item);
        });
    }

    // ─── Searchable Selects ────────────────────────────────────────────────────
    function initializeSearchableSelects() {
        document.querySelectorAll('.searchable-select').forEach(select => {
            if (select.style.display === 'none') return;

            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.placeholder = 'Type to search drugs...';
            input.required = select.required;

            const dropdown = document.createElement('div');
            dropdown.className = 'position-absolute w-100 bg-white border rounded-top-0 border-top-0 shadow';
            dropdown.style.cssText = 'max-height:200px;overflow-y:auto;z-index:1000;display:none;';

            const options = Array.from(select.options)
                .filter(o => o.value !== '')
                .map(o => ({ value: o.value, text: o.textContent.replace(/\s+/g,' ').trim(), price: o.dataset.price || 0 }));

            let selectedValue = select.value;

            function updateDisplay() {
                const found = options.find(o => o.value === selectedValue);
                input.value = found ? found.text : '';
            }

            function filterOptions(term) {
                dropdown.innerHTML = '';
                if (term.length < 3) {
                    dropdown.innerHTML = '<div class="p-2 text-muted">Type at least 3 characters...</div>';
                    return;
                }
                const filtered = options.filter(o => o.text.toLowerCase().includes(term.toLowerCase()));
                if (!filtered.length) {
                    dropdown.innerHTML = '<div class="p-2 text-muted">No drugs found</div>';
                } else {
                    filtered.forEach(o => {
                        const item = document.createElement('div');
                        item.className = 'p-2 cursor-pointer';
                        item.style.cursor = 'pointer';
                        item.textContent = o.text;
                        item.onmouseenter = () => item.style.background = '#f1f5f9';
                        item.onmouseleave = () => item.style.background = '';
                        item.onclick = () => {
                            selectedValue = o.value;
                            updateDisplay();
                            dropdown.style.display = 'none';
                            select.value = selectedValue;
                            select.dispatchEvent(new Event('change'));
                        };
                        dropdown.appendChild(item);
                    });
                }
            }

            input.addEventListener('focus', () => { filterOptions(input.value); dropdown.style.display = 'block'; });
            input.addEventListener('input', () => { filterOptions(input.value); dropdown.style.display = 'block'; });
            input.addEventListener('blur',  () => setTimeout(() => dropdown.style.display = 'none', 200));

            updateDisplay();
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(input);
            wrapper.appendChild(dropdown);
            select.style.display = 'none';
        });
    }

    // ─── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Wire program dropdown
        document.getElementById('program_id').addEventListener('change', onProgramChange);

        attachEventListeners();
        updateRemoveButtons();
        initializeSearchableSelects();

        // Trigger wallet load if a program was already pre-selected
        onProgramChange();
    });
</script>
@endpush
