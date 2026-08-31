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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <i class="ti-check fs-2 me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof showToast === 'function') {
                            showToast(@json(session('success')), 'success');
                        }
                    });
                </script>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <i class="ti-alert-triangle fs-2 me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof showToast === 'function') {
                            showToast(@json(session('error')), 'danger');
                        }
                    });
                </script>
            @endif

            <!-- Statistics Cards -->
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="subheader">Total Claims</div>
                            </div>
                            <div class="h2 mb-2 fw-bold text-dark">{{ $stats['total_claims'] ?? 0 }}</div>
                            <div class="text-muted small">
                                Latest: {{ $claims->first()?->created_at ? \Carbon\Carbon::parse($claims->first()->created_at)->format('M j, Y') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="subheader">Pending Verification</div>
                            </div>
                            <div class="h2 mb-2 fw-bold text-dark">{{ $stats['verifier_pending'] ?? 0 }}</div>
                            <div class="text-muted small">Awaiting Verifier Review</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="subheader">ES Approval</div>
                            </div>
                            <div class="h2 mb-2 fw-bold text-dark">{{ $stats['es_pending'] ?? 0 }}</div>
                            <div class="text-muted small">Awaiting ES Final Approval</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="subheader">Total Value</div>
                            </div>
                            <div class="h2 mb-2 fw-bold text-dark text-truncate" style="font-size: 1.5rem;" title="₦{{ number_format($stats['total_amount'] ?? 0, 2) }}">
                                ₦{{ number_format($stats['total_amount'] ?? 0, 2) }}
                            </div>
                            <div class="text-muted" style="font-size: 0.9rem;">
                                Approved: <span class="fw-semibold text-success">₦{{ number_format($stats['approved_amount'] ?? 0, 2) }}</span>
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
                            <div class="col-md-2">
                                <label class="form-label">Search</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Program</label>
                                <select name="program_id" class="form-select">
                                    <option value="">All Programs</option>
                                    @foreach ($programs ?? [] as $program)
                                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                        @php
                            $user = auth()->user();
                            $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));
                            $hasBulkAccess = $isSuperAdmin || $user->can('claim.es-approve') || $user->can('claim.finance-approve');
                        @endphp
                        
                        @if ($hasBulkAccess)
                        <span id="bulkActionsTotal" class="badge bg-light text-dark me-2" style="display: none; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; vertical-align: middle;">
                            Total Selected: <strong>₦<span id="selectedTotalAmount">0.00</span></strong>
                        </span>
                        <div class="dropdown me-3" id="bulkActionsDropdown" style="display: none; vertical-align: middle;">
                            <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti-settings me-1"></i>Bulk Actions (<span id="selectedCount">0</span>)
                            </button>
                            <div class="dropdown-menu">
                                @if (auth()->user()->can('claim.verify'))
                                <a href="#" class="dropdown-item" onclick="processBulkAction('verifier')">
                                    <i class="ti-check text-warning me-2"></i>Verify Claims
                                </a>
                                @endif
                                @if (auth()->user()->can('claim.approve'))
                                <a href="#" class="dropdown-item" onclick="processBulkAction('approver')">
                                    <i class="ti-check text-success me-2"></i>Approve Claims
                                </a>
                                @endif
                                @if (auth()->user()->can('claim.es-approve'))
                                <a href="#" class="dropdown-item" onclick="processBulkAction('es')">
                                    <i class="ti-check text-primary me-2"></i>ES Approve
                                </a>
                                @endif
                                @if (auth()->user()->can('claim.finance-approve'))
                                <a href="#" class="dropdown-item" onclick="processBulkAction('finance')">
                                    <i class="ti-money text-success me-2"></i>Process Payment
                                </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item text-danger" onclick="processBulkReject()">
                                    <i class="ti-x text-danger me-2"></i>Reject Claims
                                </a>
                            </div>
                        </div>
                        @endif
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
                                        @if(isset($hasBulkAccess) && $hasBulkAccess)
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                        @endif
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
                                            @if(isset($hasBulkAccess) && $hasBulkAccess)
                                                @php
                                                    $canVerify = ($isSuperAdmin || $user->can('claim.verify')) && $claim->status === 'submitted' && in_array($claim->verifier_status ?? 'pending', ['pending', null, '']);
                                                    $canApprove = ($isSuperAdmin || $user->can('claim.approve')) && $claim->status === 'verified' && in_array($claim->approver_status ?? 'pending', ['pending', null, '']);
                                                    $canEsApprove = ($isSuperAdmin || $user->can('claim.es-approve')) && $claim->status === 'approved' && in_array($claim->es_status ?? 'pending', ['pending', null, '']);
                                                    $canFinanceApprove = ($isSuperAdmin || $user->can('claim.finance-approve')) && $claim->status === 'es_approved' && in_array($claim->finance_status ?? 'pending', ['pending', null, '']);
                                                    
                                                    $stage = '';
                                                    if ($canVerify) $stage = 'verifier';
                                                    elseif ($canApprove) $stage = 'approver';
                                                    elseif ($canEsApprove) $stage = 'es';
                                                    elseif ($canFinanceApprove) $stage = 'finance';
                                                @endphp
                                                
                                                @if ($canVerify || $canApprove || $canEsApprove || $canFinanceApprove)
                                                    <input type="checkbox" class="claim-checkbox" value="{{ $claim->id }}"
                                                        data-amount="{{ $claim->total_amount }}"
                                                        data-stage="{{ $stage }}"
                                                        onchange="updateSelectedCount()">
                                                @endif
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
                                            @php
                                                // A claim is "rejected" if any stage was rejected;
                                                // its overall status is rolled back to the previous level.
                                                $rejectedStage = null;
                                                if (($claim->verifier_status ?? '') === 'rejected') {
                                                    $rejectedStage = 'verifier';
                                                } elseif (($claim->approver_status ?? '') === 'rejected') {
                                                    $rejectedStage = 'approver';
                                                } elseif (($claim->es_status ?? '') === 'rejected') {
                                                    $rejectedStage = 'es';
                                                } elseif (($claim->finance_status ?? '') === 'rejected') {
                                                    $rejectedStage = 'finance';
                                                }

                                                $rejectedBackTo = match ($rejectedStage) {
                                                    'verifier' => 'Facility (Resubmit)',
                                                    'approver' => 'Verifier',
                                                    'es' => 'Approver',
                                                    'finance' => 'Executive Secretary',
                                                    default => null,
                                                };
                                            @endphp

                                            @if ($rejectedStage)
                                                <span class="badge bg-danger">
                                                    <i class="ti-x me-1"></i>Rejected
                                                </span>
                                                <div class="text-danger small mt-1 fw-semibold">
                                                    Back to {{ $rejectedBackTo }}
                                                </div>
                                            @elseif ($claim->status === 'submitted')
                                                <span class="badge bg-warning">Pending Verification</span>
                                            @elseif ($claim->status === 'verified')
                                                <span class="badge bg-primary">Pending Approval</span>
                                            @elseif ($claim->status === 'approved')
                                                <span class="badge bg-info">Pending ES</span>
                                            @elseif ($claim->status === 'es_approved')
                                                <span class="badge bg-primary">Pending Payment</span>
                                            @elseif ($claim->status === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif ($claim->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @elseif ($claim->status === 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('claims.facility-claim.show', [$claim->id, 'return_url' => request()->fullUrl()]) }}"
                                                    class="btn btn-sm btn-primary" title="View Claim">
                                                    <i class="ti-eye"></i>
                                                </a>
                                                @if (auth()->user()->can('claim.delete'))
                                                    <form action="{{ route('claims.facility-claim.destroy', $claim->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this claim?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Claim">
                                                            <i class="ti-trash"></i>
                                                        </button>
                                                    </form>
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

    <!-- Bulk Action Confirmation Modal -->
    <div class="modal modal-blur fade" id="bulkActionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkActionModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bulkActionModalBody">
                    <!-- Dynamic content will be placed here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBulkActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Reject Modal -->
    <div class="modal modal-blur fade" id="bulkRejectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Claims</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">You are about to reject <strong id="bulkRejectCount">0</strong> claim(s). They will be sent back to the previous approval level.</p>
                    <label class="form-label fw-semibold">Rejection Comments <span class="text-danger">*</span></label>
                    <textarea id="bulkRejectReason" class="form-control" rows="3" placeholder="Explain why these claims are being rejected..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkRejectBtn">
                        <i class="ti-alert-triangle me-1"></i>Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        <div id="actionToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="actionToastBody">
                    <!-- Toast message here -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('actionToast');
            const toastBody = document.getElementById('actionToastBody');
            
            toastEl.className = `toast align-items-center text-white border-0 bg-${type}`;
            toastBody.innerHTML = message;
            
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
        }

        // Toggle select all checkboxes
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.claim-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateSelectedCount();
        }

        // Update selected count and show/hide bulk action button
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.claim-checkbox:checked');
            const count = checkboxes.length;
            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');
            const selectedCount = document.getElementById('selectedCount');
            const bulkActionsTotal = document.getElementById('bulkActionsTotal');
            const selectedTotalAmount = document.getElementById('selectedTotalAmount');

            selectedCount.textContent = count;

            let totalAmount = 0;
            checkboxes.forEach(cb => {
                totalAmount += parseFloat(cb.dataset.amount || 0);
            });

            if (selectedTotalAmount) {
                selectedTotalAmount.textContent = totalAmount.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            if (count > 0) {
                if (bulkActionsDropdown) bulkActionsDropdown.style.display = 'inline-block';
                if (bulkActionsTotal) bulkActionsTotal.style.display = 'inline-block';
            } else {
                if (bulkActionsDropdown) bulkActionsDropdown.style.display = 'none';
                if (bulkActionsTotal) bulkActionsTotal.style.display = 'none';
            }

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.claim-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            if (allCheckboxes.length > 0 && selectAllCheckbox) {
                selectAllCheckbox.checked = count === allCheckboxes.length;
            }
        }

        // Process bulk action using Modal
        let pendingBulkActionData = null;

        // ── Bulk Reject ──
        let pendingBulkRejectData = null;

        function processBulkReject() {
            const checkboxes = document.querySelectorAll('.claim-checkbox:checked');
            if (checkboxes.length === 0) {
                showToast('Please select at least one claim to reject.', 'warning');
                return;
            }

            const claimStages = {};
            checkboxes.forEach(cb => {
                claimStages[cb.value] = cb.getAttribute('data-stage') || '';
            });

            pendingBulkRejectData = {
                claim_ids: Object.keys(claimStages),
                claim_stages: claimStages,
            };

            document.getElementById('bulkRejectCount').textContent = Object.keys(claimStages).length;
            document.getElementById('bulkRejectReason').value = '';

            const modal = new bootstrap.Modal(document.getElementById('bulkRejectModal'));
            modal.show();
        }
        
        function processBulkAction(actionType) {
            const checkboxes = document.querySelectorAll('.claim-checkbox:checked');
            const claimIds = [];
            let totalAmount = 0;
            
            checkboxes.forEach(cb => {
                claimIds.push(cb.value);
                totalAmount += parseFloat(cb.dataset.amount || 0);
            });

            if (claimIds.length === 0) {
                showToast('Please select at least one claim.', 'warning');
                return;
            }

            let confirmMsg = '';
            let endpoint = '{{ route("claims.facility-claims.batch-approve") }}';
            let bodyData = { claim_ids: claimIds, approval_type: actionType };
            let actionTitle = '';

            if (actionType === 'verifier') {
                actionTitle = 'Verify Claims';
                confirmMsg = `Are you sure you want to verify <strong>${claimIds.length}</strong> claims? This will move them to the approver stage.`;
            } else if (actionType === 'approver') {
                actionTitle = 'Approve Claims';
                confirmMsg = `Are you sure you want to approve <strong>${claimIds.length}</strong> verified claims?`;
            } else if (actionType === 'es') {
                actionTitle = 'ES Approve Claims';
                confirmMsg = `Are you sure you want to ES Approve <strong>${claimIds.length}</strong> claims?`;
            } else if (actionType === 'finance') {
                actionTitle = 'Process Bulk Payment';
                confirmMsg = `Process payment for <strong>${claimIds.length}</strong> claims?<br><br><strong>Total Amount: ₦${totalAmount.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>`;
            }

            // Set modal content
            document.getElementById('bulkActionModalTitle').textContent = actionTitle;
            document.getElementById('bulkActionModalBody').innerHTML = confirmMsg;
            
            // Store data for confirmation
            pendingBulkActionData = { endpoint, bodyData };
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
            modal.show();
        }

        // Execute bulk action on modal confirm
        document.getElementById('confirmBulkActionBtn').addEventListener('click', function() {
            if (!pendingBulkActionData) return;
            
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            
            fetch(pendingBulkActionData.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(pendingBulkActionData.bodyData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide modal properly
                        const modalEl = document.getElementById('bulkActionModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        showToast('<i class="ti-check"></i> ' + data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('<i class="ti-alert-circle"></i> Error: ' + data.message, 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('<i class="ti-alert-triangle"></i> An error occurred while processing bulk action', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });

        // Execute bulk reject on modal confirm
        document.getElementById('confirmBulkRejectBtn').addEventListener('click', function() {
            if (!pendingBulkRejectData) return;

            const reason = document.getElementById('bulkRejectReason').value.trim();
            if (!reason) {
                showToast('Rejection comments are required.', 'warning');
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Rejecting...';

            fetch('{{ route("claims.facility-claims.batch-approve") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        approval_type: 'reject',
                        claim_ids: pendingBulkRejectData.claim_ids,
                        claim_stages: pendingBulkRejectData.claim_stages,
                        rejection_reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modalEl = document.getElementById('bulkRejectModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        let msg = data.message;
                        if (data.errors && data.errors.length > 0) {
                            msg += ' — ' + data.errors.join(' | ');
                        }
                        showToast('<i class="ti-check"></i> ' + msg, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast('<i class="ti-alert-circle"></i> Error: ' + data.message, 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('<i class="ti-alert-triangle"></i> An error occurred while rejecting claims', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });
    </script>
@endsection
