@extends('layouts.facility')

@section('title', 'Quick Bulk Stock Request')

@push('styles')
    <style>
        .drug-row.selected td { background-color: rgba(1, 84, 43, 0.07); }
        .drug-row:hover td { background-color: #f1f5f9; cursor: pointer; }
        .drug-row.selected:hover td { background-color: rgba(1, 84, 43, 0.12); }

        .toolbar { background: #fff; border: 1px solid #e9ecef; border-radius: .5rem; padding: .75rem 1rem; }

        #drugsCard {
            max-height: 364px;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-right: 1rem;
        }
        #drugsTable thead th {
            position: static !important;
        }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 1030;
            background: #fff; border-top: 2px solid #01542B;
            box-shadow: 0 -4px 12px rgba(0,0,0,.08);
            transform: translateY(100%);
            transition: transform .25s ease;
        }
        .bottom-bar.visible { transform: translateY(0); }

        .bottom-bar .form-control,
        .bottom-bar .form-select { height: 38px; font-size: .875rem; }

        .page-body-padded { padding-bottom: 100px; }

        .btn-notes { border: 1px dashed #6c757d; color: #6c757d; font-size: .8rem; }
        .btn-notes:hover { border-color: #01542B; color: #01542B; background: rgba(1,84,43,.05); }
        .btn-notes.has-notes { border-color: #01542B; color: #01542B; border-style: solid; }
    </style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Quick Bulk Stock Request
                    </h2>
                    <div class="text-muted mt-1">Select drugs, set quantity &amp; priority, and submit in one go</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('facility.pharmacy.stock-requests') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left me-1"></i>Back to Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body page-body-padded">
        <div class="container-xl">
            @if(isset($walletCount) && $walletCount === 0)
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti-alert-circle me-2 icon text-danger"></i>
                        </div>
                        <div>
                            <h4 class="alert-title">Cannot Create Request</h4>
                            <div class="text-secondary">Your facility does not have any wallets set up. Please contact the administrator to create a wallet before making a stock request.</div>
                        </div>
                    </div>
                </div>
            @else
            <form method="POST" action="{{ route('facility.pharmacy.stock-requests.bulk.store') }}" id="bulkRequestForm">
                @csrf

                <!-- Top toolbar: Program + Search + Actions -->
                <div class="toolbar d-flex flex-wrap align-items-end gap-3 mb-3">
                    <div style="min-width:200px">
                        <label class="form-label mb-1 small text-muted">Program <span class="text-danger">*</span></label>
                        <select name="program_id" class="form-select form-select-sm" required>
                            <option value="">Select program...</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                        @error('program_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="wallet-balance-indicator" class="small mt-1 d-none">
                            <strong>Wallet Balance:</strong> <span id="wallet-balance-amount" class="text-success">...</span>
                        </div>
                    </div>
                    <div class="flex-grow-1" style="min-width:180px">
                        <label class="form-label mb-1 small text-muted">Search drugs</label>
                        <input type="text" class="form-control form-control-sm" id="drugSearch" placeholder="Type to filter...">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">Deselect All</button>
                    </div>
                    <div class="ms-auto text-muted small pt-2">
                        <span id="selectionInfo">0 selected</span>
                    </div>
                </div>

                <!-- Drug Table -->
                <div class="card mb-3" id="drugsCard" style="max-height:364px;overflow-y:auto;padding-left:2rem;padding-right:2rem">
                        <table id="drugsTable" class="table table-vcenter table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                    </th>
                                    <th>Drug Name</th>
                                    <th>Strength</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($drugs as $drug)
                                    <tr class="drug-row" data-drug-id="{{ $drug->id }}" data-drug-name="{{ $drug->name }}" data-drug-price="{{ $drug->unit_price }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input drug-checkbox" value="{{ $drug->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $drug->name }}</span>
                                            @if($drug->description)
                                                <div class="text-muted small">{{ Str::limit($drug->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-muted">{{ $drug->strength }} {{ $drug->unit }}</td>
                                        <td class="text-end text-nowrap">₦{{ number_format($drug->unit_price, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $drug->quantity <= 10 ? 'bg-danger' : ($drug->quantity <= 50 ? 'bg-warning' : 'bg-success') }}">
                                                {{ $drug->quantity ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $drug->status == 'active' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                                {{ ucfirst($drug->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No drugs found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>

                <!-- Pagination -->
                @if ($drugs->hasPages())
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">
                            Showing <strong>{{ $drugs->firstItem() }}</strong>&ndash;<strong>{{ $drugs->lastItem() }}</strong> of <strong>{{ $drugs->total() }}</strong>
                        </span>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                @if ($drugs->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $drugs->previousPageUrl() }}">&laquo;</a></li>
                                @endif

                                @php
                                    $cur = $drugs->currentPage();
                                    $last = $drugs->lastPage();
                                    $from = max(1, $cur - 2);
                                    $to   = min($last, $cur + 2);
                                    if ($from <= 3) $to = min(5, $last);
                                    if ($to >= $last - 2) $from = max(1, $last - 4);
                                @endphp

                                @if ($from > 1)
                                    <li class="page-item"><a class="page-link" href="{{ $drugs->url(1) }}">1</a></li>
                                    @if ($from > 2)
                                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                    @endif
                                @endif

                                @for ($p = $from; $p <= $to; $p++)
                                    @if ($p == $cur)
                                        <li class="page-item active"><span class="page-link">{{ $p }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $drugs->url($p) }}">{{ $p }}</a></li>
                                    @endif
                                @endfor

                                @if ($to < $last)
                                    @if ($to < $last - 1)
                                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                    @endif
                                    <li class="page-item"><a class="page-link" href="{{ $drugs->url($last) }}">{{ $last }}</a></li>
                                @endif

                                @if ($drugs->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $drugs->nextPageUrl() }}">&raquo;</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif

                <input type="hidden" name="selected_drugs" id="selectedDrugs" value="">
                <textarea name="reason" id="reasonInput" class="d-none" required>Out of Stock</textarea>
                <textarea name="notes" id="notesInput" class="d-none"></textarea>
            </form>
            @endif
        </div>
    </div>

    <!-- Sticky bottom action bar -->
    <div class="bottom-bar" id="bottomBar">
        <div class="container-xl py-2">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary fs-6" id="selectedCount">0</span>
                    <span class="fw-semibold">drugs</span>
                </div>
                <div class="vr d-none d-md-block"></div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted text-nowrap small mb-0">Program:</label>
                    <select name="program_id_bar" id="programBarSelect" class="form-select" style="width:160px">
                        <option value="">Select...</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted text-nowrap small mb-0">Qty:</label>
                    <input type="number" name="bulk_quantity" id="bulkQuantity" class="form-control" min="1" placeholder="0" style="width:110px" form="bulkRequestForm">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted text-nowrap small mb-0">Priority:</label>
                    <select name="bulk_priority" id="bulkPriority" class="form-select" style="width:130px" form="bulkRequestForm">
                        <option value="">Select...</option>
                        @foreach ($priorities as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="vr d-none d-md-block"></div>
                <span class="fw-bold text-nowrap" style="color:#01542B">
                    ₦<span id="totalEstimatedCost">0.00</span>
                </span>
                <button type="button" class="btn btn-sm btn-notes" id="btnNotes" title="Add reason & notes">
                    <i class="ti-pencil me-1"></i>Notes
                </button>
                <div class="ms-auto">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled form="bulkRequestForm">
                        <i class="ti-send me-1"></i>Submit Request
                    </button>
                </div>
            </div>
            <div id="budget-warning" class="text-danger small mt-2 d-none text-end">
                <i class="ti-alert-circle me-1"></i><span id="budget-warning-text"></span>
            </div>
        </div>
    </div>

    <!-- Reason & Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reason &amp; Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Request <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modalReason" rows="3">Out of Stock</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="modalNotes" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnSaveNotes">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const walletsByProgram = @json($walletsByProgram ?? []);
        let selectedDrugs = [];
        let currentBalance = null;
        let hasWallet = false;
        let totalCost = 0;

        $(document).ready(function() {
            // Restore selections
            const saved = localStorage.getItem('selectedDrugs');
            if (saved) {
                try { selectedDrugs = JSON.parse(saved); restoreSelections(); }
                catch(e) { localStorage.removeItem('selectedDrugs'); }
            }

            // Search filter
            $('#drugSearch').on('input', function() {
                const q = $(this).val().toLowerCase();
                $('.drug-row').each(function() {
                    const name = $(this).data('drug-name').toLowerCase();
                    $(this).toggle(name.indexOf(q) !== -1);
                });
            });

            // Select all checkbox in table header
            $('#selectAllCheckbox').on('change', function() {
                const visible = $('.drug-row:visible .drug-checkbox');
                visible.prop('checked', $(this).is(':checked'));
                updateSelectedDrugs();
            });

            // Toolbar buttons
            $('#btnSelectAll').on('click', function() {
                $('.drug-row:visible .drug-checkbox').prop('checked', true);
                syncSelectAll();
                updateSelectedDrugs();
            });
            $('#btnDeselectAll').on('click', function() {
                $('.drug-checkbox').prop('checked', false);
                $('#selectAllCheckbox').prop('checked', false).prop('indeterminate', false);
                updateSelectedDrugs();
            });

            // Individual checkbox
            $(document).on('change', '.drug-checkbox', function() {
                updateSelectedDrugs();
                syncSelectAll();
            });

            // Row click toggles checkbox
            $(document).on('click', '.drug-row', function(e) {
                if ($(e.target).is('input')) return;
                const cb = $(this).find('.drug-checkbox');
                cb.prop('checked', !cb.prop('checked'));
                updateSelectedDrugs();
                syncSelectAll();
            });

            // Bottom bar inputs
            $('#bulkQuantity, #bulkPriority').on('input change', function() {
                calculateTotalCost();
                validateForm();
            });

            // Sync program selects
            $('#programBarSelect').on('change', function() {
                $('select[name="program_id"]').val($(this).val());
                updateWalletDisplay();
                validateForm();
            });
            $('select[name="program_id"]').on('change', function() {
                $('#programBarSelect').val($(this).val());
                updateWalletDisplay();
                validateForm();
            });

            // Notes modal
            $('#btnNotes').on('click', function() {
                $('#modalReason').val($('#reasonInput').val());
                $('#modalNotes').val($('#notesInput').val());
                new bootstrap.Modal('#notesModal').show();
            });
            $('#btnSaveNotes').on('click', function() {
                const reason = $('#modalReason').val().trim();
                const notes = $('#modalNotes').val().trim();
                $('#reasonInput').val(reason || 'Out of Stock');
                $('#notesInput').val(notes);
                if (notes || reason !== 'Out of Stock') {
                    $('#btnNotes').addClass('has-notes').html('<i class="ti-check me-1"></i>Notes');
                } else {
                    $('#btnNotes').removeClass('has-notes').html('<i class="ti-pencil me-1"></i>Notes');
                }
                bootstrap.Modal.getInstance('#notesModal').hide();
                validateForm();
            });

            // Persist across pages
            $(window).on('beforeunload', function() {
                localStorage.setItem('selectedDrugs', JSON.stringify(selectedDrugs));
            });
            $('#bulkRequestForm').on('submit', function(e) {
                localStorage.removeItem('selectedDrugs');
                
                // Show loading state
                const $btn = $('#submitBtn');
                $btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting...');
                
                // Re-enable after 10 seconds as fallback
                setTimeout(function() {
                    $btn.prop('disabled', false)
                        .html('<i class="ti-send me-1"></i>Submit Request');
                }, 10000);
            });

            updateSelectedDrugs();
            updateWalletDisplay();
            validateForm();
        });

        function updateWalletDisplay() {
            const programId = $('select[name="program_id"]').val();
            const indicator = $('#wallet-balance-indicator');
            const amountSpan = $('#wallet-balance-amount');

            if (!programId) {
                indicator.addClass('d-none');
                hasWallet = false;
                currentBalance = null;
                return;
            }

            const wallet = walletsByProgram[programId];
            if (wallet) {
                hasWallet = true;
                currentBalance = wallet.balance;
                amountSpan.text('₦' + currentBalance.toLocaleString('en-US', {minimumFractionDigits: 2}))
                    .removeClass('text-danger').addClass('text-success');
                indicator.removeClass('d-none');
            } else {
                hasWallet = false;
                currentBalance = null;
                amountSpan.text('No active wallet')
                    .removeClass('text-success').addClass('text-danger');
                indicator.removeClass('d-none');
            }
        }

        function syncSelectAll() {
            const total = $('.drug-row:visible .drug-checkbox').length;
            const checked = $('.drug-row:visible .drug-checkbox:checked').length;
            $('#selectAllCheckbox')
                .prop('checked', total > 0 && checked === total)
                .prop('indeterminate', checked > 0 && checked < total);
        }

        function restoreSelections() {
            selectedDrugs.forEach(function(d) {
                $(`.drug-checkbox[value="${d.id}"]`).prop('checked', true);
            });
            syncSelectAll();
        }

        function updateSelectedDrugs() {
            selectedDrugs = [];
            $('.drug-checkbox:checked').each(function() {
                const row = $(this).closest('.drug-row');
                selectedDrugs.push({
                    id: $(this).val(),
                    name: row.data('drug-name'),
                    price: parseFloat(row.data('drug-price'))
                });
            });

            const count = selectedDrugs.length;
            $('#selectedCount').text(count);
            $('#selectionInfo').text(count + ' selected');
            $('#selectedDrugs').val(JSON.stringify(selectedDrugs.map(d => d.id)));

            // Toggle bottom bar
            if (count > 0) {
                $('#bottomBar').addClass('visible');
            } else {
                $('#bottomBar').removeClass('visible');
            }

            // Row styling
            $('.drug-row').removeClass('selected');
            $('.drug-checkbox:checked').closest('.drug-row').addClass('selected');

            calculateTotalCost();
            validateForm();
        }

        function calculateTotalCost() {
            const qty = parseInt($('#bulkQuantity').val()) || 0;
            totalCost = selectedDrugs.reduce(function(s, d) { return s + d.price * qty; }, 0);
            $('#totalEstimatedCost').text(totalCost.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
        }

        function validateForm() {
            const qty = parseInt($('#bulkQuantity').val()) || 0;
            const programId = $('select[name="program_id"]').val();
            
            let ok = selectedDrugs.length > 0 &&
                       qty > 0 &&
                       $('#bulkPriority').val() &&
                       programId &&
                       $('#reasonInput').val().trim().length > 0;
            
            const warnDiv = $('#budget-warning');
            const warnText = $('#budget-warning-text');
            const costEl = $('#totalEstimatedCost').parent();

            if (programId && !hasWallet) {
                ok = false;
                warnDiv.removeClass('d-none');
                warnText.text('Selected program has no active wallet.');
                costEl.css('color', '#dc3545'); // red
            } else if (hasWallet && totalCost > currentBalance) {
                ok = false;
                warnDiv.removeClass('d-none');
                warnText.text(`Total ₦${totalCost.toLocaleString('en-US',{minimumFractionDigits:2})} exceeds wallet balance ₦${currentBalance.toLocaleString('en-US',{minimumFractionDigits:2})}.`);
                costEl.css('color', '#dc3545'); // red
            } else {
                warnDiv.addClass('d-none');
                costEl.css('color', '#01542B'); // green
            }

            $('#submitBtn').prop('disabled', !ok);
        }
    </script>
@endpush
