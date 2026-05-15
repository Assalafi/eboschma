@extends('layouts.app')

@section('title', 'Facility Claims - ' . $facility->name)

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <a href="{{ route('claims.index') }}" class="text-muted">Claims</a>
                    </div>
                    <h2 class="page-title">
                        {{ $facility->name }} Claims
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('claims.index') }}" class="btn">
                            <i class="ti-arrow-left me-1"></i> Back to Claims
                        </a>
                        @if (auth()->user()->can('create-claims'))
                            <a href="{{ route('facility.claims.create', $facility->id) }}" class="btn btn-primary">
                                <i class="ti-plus me-1"></i> New Claim
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Statistics Cards -->
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Claims</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['total_claims'] ?? 0 }}</div>
                            <div class="d-flex mb-2">
                                <div>Latest:
                                    {{ $claims->first()?->created_at ? \Carbon\Carbon::parse($claims->first()->created_at)->format('M j, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Pending Verification</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['verifier_pending'] ?? 0 }}</div>
                            <div class="d-flex mb-2">
                                <div>Awaiting Verifier Review</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">ES Approval</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['es_pending'] ?? 0 }}</div>
                            <div class="d-flex mb-2">
                                <div>Awaiting ES Final Approval</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Value</div>
                            </div>
                            <div class="h1 mb-3">₦{{ number_format($stats['total_amount'] ?? 0, 2) }}</div>
                            <div class="d-flex mb-2">
                                <div>Approved: ₦{{ number_format($stats['approved_amount'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <form method="GET" action="{{ route('claims.facility.show', $facility->id) }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Patient name, claim #, BOSCHMA #, enrollee #..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="es_approved" {{ request('status') == 'es_approved' ? 'selected' : '' }}>ES Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti-search me-1"></i> Search
                                    </button>
                                    <a href="{{ route('claims.facility.show', $facility->id) }}" class="btn btn-outline-secondary">
                                        <i class="ti-x"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Claims Table -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header">
                    <h3 class="card-title">Claims for {{ $facility->name }} ({{ $claims->total() }})</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-success me-2" id="bulkPaymentBtn" style="display: none;"
                            onclick="processBulkPayment()">
                            <i class="ti-money me-1"></i>Process Bulk Payment (<span id="selectedCount">0</span>)
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti-download me-1"></i>Export
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('claims.export') }}?facility_id={{ $facility->id }}"
                                    class="dropdown-item">
                                    <i class="ti-file me-2"></i>Export All Claims
                                </a>
                                <a href="{{ route('claims.export') }}?facility_id={{ $facility->id }}&status=submitted"
                                    class="dropdown-item">
                                    <i class="ti-clock me-2"></i>Export Pending
                                </a>
                                <a href="{{ route('claims.export') }}?facility_id={{ $facility->id }}&status=approved"
                                    class="dropdown-item">
                                    <i class="ti-check me-2"></i>Export Approved
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 fw-semibold" style="width: 40px;">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                    </th>
                                    <th class="border-0 fw-semibold">Claim #</th>
                                    <th class="border-0 fw-semibold">Beneficiary</th>
                                    <th class="border-0 fw-semibold">Service Date</th>
                                    <th class="border-0 fw-semibold">Amount</th>
                                    <th class="border-0 fw-semibold">Status</th>
                                    <th class="border-0 fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($claims as $claim)
                                    <tr>
                                        <td class="align-middle">
                                            @if (($claim->es_status ?? '') === 'approved' && ($claim->finance_status ?? 'pending') !== 'paid')
                                                <input type="checkbox" class="claim-checkbox" value="{{ $claim->id }}"
                                                    data-amount="{{ $claim->total_amount }}"
                                                    onchange="updateSelectedCount()">
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="fw-semibold">{{ $claim->claim_number ?? 'CLM-' . $claim->id }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($claim->created_at)->format('M j, Y') }}</div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-blue-lt me-2">
                                                    <i class="ti-user fs-4 text-blue"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $claim->patient_name }}</div>
                                                    <div class="text-muted small">{{ $claim->boschma_no ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($claim->service_date)->format('M j, Y') }}</div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="fw-semibold text-primary">
                                                ₦{{ number_format($claim->total_amount, 2) }}</div>
                                        </td>
                                        <td class="align-middle">
                                            @if ($claim->status === 'submitted')
                                                @if (empty($claim->ro_status))
                                                    <span class="badge bg-warning">Pending RO Review</span>
                                                @elseif ($claim->ro_status === 'approved' && empty($claim->e5_status))
                                                    <span class="badge bg-primary">Pending E5 Approval</span>
                                                @elseif ($claim->ro_status === 'rejected')
                                                    <span class="badge bg-danger">RO Rejected</span>
                                                @elseif ($claim->e5_status === 'rejected')
                                                    <span class="badge bg-danger">E5 Rejected</span>
                                                @else
                                                    <span class="badge bg-info">Submitted</span>
                                                @endif
                                            @elseif ($claim->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif ($claim->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @elseif ($claim->status === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif ($claim->status === 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @elseif ($claim->status === 'under_review')
                                                <span class="badge bg-info">Under Review</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('claims.facility-claim.show', $claim->id) }}"
                                                    class="btn btn-sm btn-primary" title="View Claim">
                                                    <i class="ti-eye"></i>
                                                </a>
                                                @if (auth()->user()->can('review-claims') && $claim->status === 'submitted' && empty($claim->ro_status))
                                                    <a href="#" class="btn btn-sm btn-warning" title="Review"
                                                        onclick="reviewClaim({{ $claim->id }})">
                                                        <i class="ti-clipboard-check"></i>
                                                    </a>
                                                @endif
                                                @if (auth()->user()->can('approve-claims') && $claim->ro_status === 'approved' && empty($claim->e5_status))
                                                    <a href="#" class="btn btn-sm btn-success" title="E5 Approve"
                                                        onclick="e5ApproveClaim({{ $claim->id }})">
                                                        <i class="ti-shield-check"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty">
                                                <div class="empty-img">
                                                    <i class="ti-file-off fs-1 text-muted"></i>
                                                </div>
                                                <p class="empty-title">No claims found</p>
                                                <p class="empty-subtitle text-muted">
                                                    No claims have been submitted for this facility yet.
                                                </p>
                                                @if (auth()->user()->can('create-claims'))
                                                    <a href="{{ route('facility.claims.create', $facility->id) }}"
                                                        class="btn btn-primary">
                                                        <i class="ti-plus me-1"></i> Create First Claim
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-0 gap-3 p-3 border-top">
                        <div>
                            <p class="text-muted mb-0">
                                Showing {{ $claims->firstItem() ?? 0 }} to
                                {{ $claims->lastItem() ?? 0 }}
                                of {{ $claims->total() }} results
                            </p>
                        </div>
                        <div class="overflow-auto w-100 w-md-auto">
                            @if ($claims->hasPages())
                                <nav aria-label="Claims pagination">
                                    <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                        {{-- Previous Page Link --}}
                                        @if ($claims->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link"
                                                    href="{{ $claims->previousPageUrl() }}"
                                                    rel="prev">Prev</a></li>
                                        @endif

                                        {{-- Pagination Elements with Smart Window --}}
                                        @php
                                            $currentPage = $claims->currentPage();
                                            $lastPage = $claims->lastPage();
                                            $onEachSide = 2;

                                            $start = max(1, $currentPage - $onEachSide);
                                            $end = min($lastPage, $currentPage + $onEachSide);

                                            if ($currentPage <= $onEachSide + 1) {
                                                $end = min($lastPage, $onEachSide * 2 + 2);
                                            }
                                            if ($currentPage >= $lastPage - $onEachSide) {
                                                $start = max(1, $lastPage - $onEachSide * 2 - 1);
                                            }
                                        @endphp

                                        {{-- First Page --}}
                                        @if ($start > 1)
                                            <li class="page-item"><a class="page-link"
                                                    href="{{ $claims->url(1) }}">1</a></li>
                                            @if ($start > 2)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                        @endif

                                        {{-- Page Number Links --}}
                                        @for ($page = $start; $page <= $end; $page++)
                                            @if ($page == $currentPage)
                                                <li class="page-item active"><span
                                                        class="page-link">{{ $page }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $claims->url($page) }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Last Page --}}
                                        @if ($end < $lastPage)
                                            @if ($end < $lastPage - 1)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item"><a class="page-link"
                                                    href="{{ $claims->url($lastPage) }}">{{ $lastPage }}</a>
                                            </li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if ($claims->hasMorePages())
                                            <li class="page-item"><a class="page-link"
                                                    href="{{ $claims->nextPageUrl() }}"
                                                    rel="next">Next</a></li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function reviewClaim(claimId) {
            window.location.href = `/claims/${claimId}#review`;
        }

        function e5ApproveClaim(claimId) {
            window.location.href = `/claims/${claimId}#e5-approve`;
        }

        // Toggle select all checkboxes
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.claim-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateSelectedCount();
        }

        // Update selected count and show/hide bulk payment button
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.claim-checkbox:checked');
            const count = checkboxes.length;
            const bulkPaymentBtn = document.getElementById('bulkPaymentBtn');
            const selectedCount = document.getElementById('selectedCount');

            selectedCount.textContent = count;

            if (count > 0) {
                bulkPaymentBtn.style.display = 'inline-block';
            } else {
                bulkPaymentBtn.style.display = 'none';
            }

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.claim-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            if (allCheckboxes.length > 0) {
                selectAllCheckbox.checked = count === allCheckboxes.length;
            }
        }

        // Process bulk payment
        function processBulkPayment() {
            const checkboxes = document.querySelectorAll('.claim-checkbox:checked');
            const claimIds = Array.from(checkboxes).map(cb => cb.value);

            if (claimIds.length === 0) {
                alert('Please select at least one claim to process payment');
                return;
            }

            // Calculate total amount
            let totalAmount = 0;
            checkboxes.forEach(cb => {
                totalAmount += parseFloat(cb.dataset.amount || 0);
            });

            // Confirm bulk payment
            if (!confirm(
                    `Process payment for ${claimIds.length} claims?\n\nTotal Amount: ₦${totalAmount.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
                )) {
                return;
            }

            // Show loading
            const btn = document.getElementById('bulkPaymentBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti-loader me-1"></i>Processing...';

            // Submit bulk payment request
            fetch('{{ route('claims.bulk-payment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        claim_ids: claimIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing bulk payment');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        }
    </script>
@endsection
