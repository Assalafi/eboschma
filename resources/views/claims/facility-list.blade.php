@extends('layouts.app')

@section('title', 'Facility Claims - ' . $facility)

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary">
                            <i class="ti-arrow-left me-1"></i>Back to Claims
                        </a>
                        <a href="{{ route('claims.create') }}" class="btn btn-primary">
                            <i class="ti-plus me-1"></i>New Claim
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti-download me-1"></i>Export
                            </button>
                            <div class="dropdown-menu">
                                <a href="#" class="dropdown-item" onclick="exportClaims('excel')">
                                    <i class="ti-file-excel me-2"></i>Export to Excel
                                </a>
                                <a href="#" class="dropdown-item" onclick="exportClaims('pdf')">
                                    <i class="ti-file-text me-2"></i>Export to PDF
                                </a>
                                <a href="#" class="dropdown-item" onclick="exportClaims('csv')">
                                    <i class="ti-file me-2"></i>Export to CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Facility Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-lg bg-primary-lt">
                                        <i class="ti-file-text fs-2 text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="h3 mb-0">{{ $claims->total() }}</div>
                                    <div class="text-muted">Total Claims</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-lg bg-success-lt">
                                        <i class="ti-check fs-2 text-success"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="h3 mb-0">{{ $claims->where('status', 'approved')->count() }}</div>
                                    <div class="text-muted">Approved</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-lg bg-info-lt">
                                        <i class="ti-cash fs-2 text-info"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="h3 mb-0">₦{{ number_format($claims->sum('claim_amount'), 0) }}</div>
                                    <div class="text-muted">Total Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-lg bg-warning-lt">
                                        <i class="ti-clock fs-2 text-warning"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="h3 mb-0">{{ $claims->where('status', 'pending')->count() }}</div>
                                    <div class="text-muted">Pending</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-primary text-white" style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ti-filter me-2"></i>Advanced Filters
                        </h5>
                        <button type="button" class="btn btn-sm btn-light" onclick="toggleFilters()">
                            <i class="ti-adjustments-horizontal me-1"></i>Toggle Filters
                        </button>
                    </div>
                </div>
                <div class="card-body" id="filtersSection">
                    <form id="filterForm" method="GET" action="{{ request()->url() }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                        Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Claim Type</label>
                                <select class="form-select" name="claim_type">
                                    <option value="">All Types</option>
                                    <option value="medical" {{ request('claim_type') == 'medical' ? 'selected' : '' }}>
                                        Medical Services</option>
                                    <option value="pharmacy" {{ request('claim_type') == 'pharmacy' ? 'selected' : '' }}>
                                        Pharmacy/Medication</option>
                                    <option value="hospitalization"
                                        {{ request('claim_type') == 'hospitalization' ? 'selected' : '' }}>Hospitalization
                                    </option>
                                    <option value="diagnostic"
                                        {{ request('claim_type') == 'diagnostic' ? 'selected' : '' }}>Diagnostic Tests
                                    </option>
                                    <option value="emergency"
                                        {{ request('claim_type') == 'emergency' ? 'selected' : '' }}>Emergency Services
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date Range</label>
                                <select class="form-select" name="date_range">
                                    <option value="">All Time</option>
                                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today
                                    </option>
                                    <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This
                                        Week</option>
                                    <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This
                                        Month</option>
                                    <option value="quarter" {{ request('date_range') == 'quarter' ? 'selected' : '' }}>
                                        This Quarter</option>
                                    <option value="year" {{ request('date_range') == 'year' ? 'selected' : '' }}>This
                                        Year</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Amount Range</label>
                                <select class="form-select" name="amount_range">
                                    <option value="">All Amounts</option>
                                    <option value="0-10000" {{ request('amount_range') == '0-10000' ? 'selected' : '' }}>
                                        ₦0 - ₦10,000</option>
                                    <option value="10000-50000"
                                        {{ request('amount_range') == '10000-50000' ? 'selected' : '' }}>₦10,000 - ₦50,000
                                    </option>
                                    <option value="50000-100000"
                                        {{ request('amount_range') == '50000-100000' ? 'selected' : '' }}>₦50,000 -
                                        ₦100,000</option>
                                    <option value="100000+" {{ request('amount_range') == '100000+' ? 'selected' : '' }}>
                                        ₦100,000+</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Beneficiary Name</label>
                                <input type="text" class="form-control" name="beneficiary_name"
                                    value="{{ request('beneficiary_name') }}" placeholder="Search by beneficiary name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Authorization Code</label>
                                <input type="text" class="form-control" name="authorization_code"
                                    value="{{ request('authorization_code') }}"
                                    placeholder="Search by authorization code">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Healthcare Provider</label>
                                <input type="text" class="form-control" name="healthcare_provider"
                                    value="{{ request('healthcare_provider') }}" placeholder="Search by provider">
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-search me-1"></i>Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                        <i class="ti-refresh me-1"></i>Clear Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="saveFilterPreset()">
                                        <i class="ti-save me-1"></i>Save Preset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Claims Table -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-secondary text-white" style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ti-list me-2"></i>Claims List
                            <span class="badge bg-light text-dark ms-2">{{ $claims->count() }} claims</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" id="quickSearch"
                                    placeholder="Quick search..." onkeyup="quickSearch()">
                                <button class="btn btn-outline-light" type="button">
                                    <i class="ti-search"></i>
                                </button>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="ti-columns me-1"></i>Columns
                                </button>
                                <div class="dropdown-menu">
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colDate" checked
                                                onchange="toggleColumn('date')">
                                            <label class="form-check-label" for="colDate">Date</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colBeneficiary" checked
                                                onchange="toggleColumn('beneficiary')">
                                            <label class="form-check-label" for="colBeneficiary">Beneficiary</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colBoschmaId" checked
                                                onchange="toggleColumn('boschma_id')">
                                            <label class="form-check-label" for="colBoschmaId">BOSCHMA ID</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colAuthCode" checked
                                                onchange="toggleColumn('auth_code')">
                                            <label class="form-check-label" for="colAuthCode">Auth Code</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colVerifierStatus"
                                                checked onchange="toggleColumn('verifier_status')">
                                            <label class="form-check-label" for="colVerifierStatus">Verifier
                                                Status</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colApproverStatus"
                                                checked onchange="toggleColumn('approver_status')">
                                            <label class="form-check-label" for="colApproverStatus">Approver
                                                Status</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colEsStatus" checked
                                                onchange="toggleColumn('es_status')">
                                            <label class="form-check-label" for="colEsStatus">ES Status</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colFinanceStatus" checked
                                                onchange="toggleColumn('finance_status')">
                                            <label class="form-check-label" for="colFinanceStatus">Finance Status</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colDiagnosis" checked
                                                onchange="toggleColumn('diagnosis')">
                                            <label class="form-check-label" for="colDiagnosis">Diagnosis</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colAmount" checked
                                                onchange="toggleColumn('amount')">
                                            <label class="form-check-label" for="colAmount">Amount</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colStatus" checked
                                                onchange="toggleColumn('status')">
                                            <label class="form-check-label" for="colStatus">Status</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="colActions" checked
                                                onchange="toggleColumn('actions')">
                                            <label class="form-check-label" for="colActions">Actions</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="claimsTable">
                            <thead>
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" id="selectAllClaims" onclick="toggleSelectAll()" title="Select all">
                                    </th>
                                    <th class="col-date" style="cursor: pointer;" onclick="sortTable('date')">
                                        Date <i class="ti-arrow-down-up text-muted"></i>
                                    </th>
                                    <th class="col-beneficiary" style="cursor: pointer;"
                                        onclick="sortTable('beneficiary')">
                                        Beneficiary <i class="ti-arrow-down-up text-muted"></i>
                                    </th>
                                    <th class="col-boschma_id">BOSCHMA ID</th>
                                    <th class="col-auth_code">Authorization Code</th>
                                    <th class="col-verifier_status">Verifier</th>
                                    <th class="col-approver_status">Approver</th>
                                    <th class="col-es_status">ES</th>
                                    <th class="col-finance_status">Finance</th>
                                    <th class="col-diagnosis">Diagnosis</th>
                                    <th class="col-amount" style="cursor: pointer;" onclick="sortTable('amount')">
                                        Amount <i class="ti-arrow-down-up text-muted"></i>
                                    </th>
                                    <th class="col-status">Status</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr
                                        data-searchable="{{ strtolower($claim->beneficiary_name . ' ' . $claim->authorization_code . ' ' . $claim->diagnosis) }}"
                                        data-claim-id="{{ $claim->id }}"
                                        data-claim-amount="{{ $claim->claim_amount ?? 0 }}"
                                        data-approver-status="{{ $claim->approver_status ?? 'pending' }}"
                                        data-es-status="{{ $claim->es_status ?? 'pending' }}"
                                        data-finance-status="{{ $claim->finance_status ?? 'pending' }}">
                                        <td>
                                            <input type="checkbox" class="claim-checkbox" value="{{ $claim->id }}" data-amount="{{ $claim->claim_amount ?? 0 }}" onclick="updateBatchBar()">
                                        </td>
                                        <td class="col-date">
                                            <div class="text-muted small">{{ $claim->created_at->format('M j, Y') }}</div>
                                            <div class="text-muted small">{{ $claim->created_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="col-beneficiary">
                                            <div class="fw-semibold">{{ $claim->beneficiary_name }}</div>
                                            <div class="text-muted small">{{ $claim->nin }}</div>
                                        </td>
                                        <td class="col-boschma_id">
                                            <span class="badge bg-light text-dark">{{ $claim->boschma_id }}</span>
                                        </td>
                                        <td class="col-auth_code">
                                            <code>{{ $claim->authorization_code }}</code>
                                        </td>
                                        <td class="col-verifier_status">
                                            @if (($claim->verifier_status ?? 'pending') === 'approved')
                                                <span class="badge bg-success-lt">✓</span>
                                            @elseif(($claim->verifier_status ?? 'pending') === 'rejected')
                                                <span class="badge bg-danger-lt">✗</span>
                                            @else
                                                <span class="badge bg-secondary-lt">⏳</span>
                                            @endif
                                        </td>
                                        <td class="col-approver_status">
                                            @if (($claim->approver_status ?? 'pending') === 'approved')
                                                <span class="badge bg-success-lt">✓</span>
                                            @elseif(($claim->approver_status ?? 'pending') === 'rejected')
                                                <span class="badge bg-danger-lt">✗</span>
                                            @else
                                                <span class="badge bg-secondary-lt">⏳</span>
                                            @endif
                                        </td>
                                        <td class="col-es_status">
                                            @if (($claim->es_status ?? 'pending') === 'approved')
                                                <span class="badge bg-success-lt">✓</span>
                                            @elseif(($claim->es_status ?? 'pending') === 'rejected')
                                                <span class="badge bg-danger-lt">✗</span>
                                            @else
                                                <span class="badge bg-secondary-lt">⏳</span>
                                            @endif
                                        </td>
                                        <td class="col-finance_status">
                                            @if (($claim->finance_status ?? 'pending') === 'paid')
                                                <span class="badge bg-success-lt">✓</span>
                                            @elseif(($claim->finance_status ?? 'pending') === 'rejected')
                                                <span class="badge bg-danger-lt">✗</span>
                                            @else
                                                <span class="badge bg-secondary-lt">⏳</span>
                                            @endif
                                        </td>
                                        <td class="col-diagnosis">
                                            <div class="text-truncate" style="max-width: 200px;"
                                                title="{{ $claim->diagnosis }}">
                                                {{ Str::limit($claim->diagnosis, 50) }}
                                            </div>
                                        </td>
                                        <td class="col-amount">
                                            <div class="fw-semibold">₦{{ number_format($claim->claim_amount, 2) }}</div>
                                        </td>
                                        <td class="col-status">
                                            @switch($claim->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @break

                                                @case('approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @break

                                                @case('rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @break

                                                @case('paid')
                                                    <span class="badge bg-info">Paid</span>
                                                @break

                                                @default
                                                    <span class="badge bg-secondary">{{ $claim->status }}</span>
                                            @endswitch
                                        </td>
                                        <td class="col-actions">
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('claims.show', $claim->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="ti-eye"></i>
                                                </a>
                                                @if ($claim->status === 'pending')
                                                    <a href="{{ route('claims.edit', $claim->id) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="Edit Claim">
                                                        <i class="ti-edit"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('claims.print', $claim->id) }}"
                                                    class="btn btn-sm btn-outline-danger" target="_blank" title="Print">
                                                    <i class="ti-printer"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                        <i class="ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a href="#" class="dropdown-item"
                                                            onclick="viewNotes({{ $claim->id }})">
                                                            <i class="ti-message me-2"></i>View Notes
                                                        </a>
                                                        <a href="#" class="dropdown-item"
                                                            onclick="viewHistory({{ $claim->id }})">
                                                            <i class="ti-history me-2"></i>View History
                                                        </a>
                                                        @if ($claim->status === 'pending')
                                                            <a href="#" class="dropdown-item"
                                                                onclick="approveClaim({{ $claim->id }})">
                                                                <i class="ti-check me-2"></i>Approve Claim
                                                            </a>
                                                            <a href="#" class="dropdown-item text-danger"
                                                                onclick="rejectClaim({{ $claim->id }})">
                                                                <i class="ti-x me-2"></i>Reject Claim
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center py-8">
                                                <div class="text-muted">
                                                    <i class="ti-file-off fs-1 mb-2"></i>
                                                    <div>No claims found</div>
                                                    <div class="small">Try adjusting your filters or create a new claim</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($claims->hasPages())
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        Showing {{ $claims->firstItem() }} to {{ $claims->lastItem() }} of
                                        {{ $claims->total() }} claims
                                    </div>
                                    {{ $claims->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Batch Approval Sticky Bar --}}
            <div id="batchBar" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:1050;background:#fff;border-top:2px solid #006634;box-shadow:0 -4px 12px rgba(0,0,0,.15);padding:12px 24px;">
                <div class="container-xl">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <strong id="batchCount">0</strong> claims selected &nbsp;|&nbsp;
                            Running Total: <strong id="batchTotal" style="color:#006634">₦0</strong>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <select id="batchAction" class="form-select form-select-sm" style="width:200px">
                                <option value="">Select batch action...</option>
                                <option value="es">ES Approve Selected</option>
                                <option value="finance">Finance / Mark as Paid</option>
                            </select>
                            <button class="btn btn-sm btn-success" onclick="executeBatchApproval()">
                                <i class="ti-checks me-1"></i>Execute Batch
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endsection

    @push('styles')
        <style>
            .table th {
                border-top: none;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.75rem;
                letter-spacing: 0.5px;
                color: #6c757d;
            }

            .table td {
                vertical-align: middle;
            }

            .avatar {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            }

            .avatar-lg {
                width: 4rem;
                height: 4rem;
                font-size: 1.5rem;
            }

            .text-truncate {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .btn-list {
                display: flex;
                gap: 0.25rem;
            }

            .flex-nowrap {
                flex-wrap: nowrap;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            let currentSort = {
                column: 'date',
                direction: 'desc'
            };

            function toggleFilters() {
                const filtersSection = document.getElementById('filtersSection');
                filtersSection.style.display = filtersSection.style.display === 'none' ? 'block' : 'none';
            }

            function clearFilters() {
                document.getElementById('filterForm').reset();
                window.location.href = '{{ request()->url() }}';
            }

            function quickSearch() {
                const searchTerm = document.getElementById('quickSearch').value.toLowerCase();
                const rows = document.querySelectorAll('#claimsTable tbody tr');

                rows.forEach(row => {
                    const searchable = row.getAttribute('data-searchable');
                    if (searchable && searchable.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            function sortTable(column) {
                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                // Update URL with sort parameters
                const url = new URL(window.location);
                url.searchParams.set('sort', column);
                url.searchParams.set('direction', currentSort.direction);
                window.location.href = url.toString();
            }

            function toggleColumn(columnName) {
                const columns = document.querySelectorAll(`.col-${columnName}`);
                columns.forEach(col => {
                    col.style.display = col.style.display === 'none' ? '' : 'none';
                });
            }

            function exportClaims(format) {
                const url = new URL(window.location);
                url.searchParams.set('export', format);
                window.open(url.toString(), '_blank');
            }

            function saveFilterPreset() {
                const formData = new FormData(document.getElementById('filterForm'));
                const params = new URLSearchParams(formData);

                localStorage.setItem('claimsFilterPreset', params.toString());
                alert('Filter preset saved successfully!');
            }

            function loadFilterPreset() {
                const preset = localStorage.getItem('claimsFilterPreset');
                if (preset) {
                    const url = new URL(window.location);
                    url.search = preset;
                    window.location.href = url.toString();
                }
            }

            function approveClaim(claimId) {
                if (confirm('Are you sure you want to approve this claim?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/claims/${claimId}/approve`;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function rejectClaim(claimId) {
                const reason = prompt('Please enter the rejection reason:');
                if (reason) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/claims/${claimId}/reject`;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const rejectionReason = document.createElement('input');
                    rejectionReason.type = 'hidden';
                    rejectionReason.name = 'rejection_reason';
                    rejectionReason.value = reason;
                    form.appendChild(rejectionReason);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function viewNotes(claimId) {
                window.open(`/claims/${claimId}/notes`, '_blank');
            }

            function viewHistory(claimId) {
                window.open(`/claims/${claimId}/history`, '_blank');
            }

            // Load saved filter preset on page load
            document.addEventListener('DOMContentLoaded', function() {
                // Check if there's a saved preset and no current filters
                if (!window.location.search && localStorage.getItem('claimsFilterPreset')) {
                    if (confirm('Would you like to load your saved filter preset?')) {
                        loadFilterPreset();
                    }
                }
            });

            // ── Batch Approval Functions ──
            function toggleSelectAll() {
                var checked = document.getElementById('selectAllClaims').checked;
                document.querySelectorAll('.claim-checkbox').forEach(function(cb) {
                    cb.checked = checked;
                });
                updateBatchBar();
            }

            function updateBatchBar() {
                var checkboxes = document.querySelectorAll('.claim-checkbox:checked');
                var count = checkboxes.length;
                var total = 0;
                checkboxes.forEach(function(cb) {
                    total += parseFloat(cb.getAttribute('data-amount') || 0);
                });
                document.getElementById('batchCount').textContent = count;
                document.getElementById('batchTotal').textContent = '\u20a6' + total.toLocaleString('en-NG', {minimumFractionDigits: 2});
                document.getElementById('batchBar').style.display = count > 0 ? 'block' : 'none';
            }

            function clearSelection() {
                document.querySelectorAll('.claim-checkbox').forEach(function(cb) { cb.checked = false; });
                document.getElementById('selectAllClaims').checked = false;
                updateBatchBar();
            }

            function executeBatchApproval() {
                var action = document.getElementById('batchAction').value;
                if (!action) { alert('Please select a batch action first.'); return; }

                var ids = [];
                document.querySelectorAll('.claim-checkbox:checked').forEach(function(cb) {
                    ids.push(parseInt(cb.value));
                });
                if (ids.length === 0) { alert('No claims selected.'); return; }

                var label = action === 'es' ? 'ES approve' : 'mark as paid';
                if (!confirm('Are you sure you want to ' + label + ' ' + ids.length + ' claim(s)?')) return;

                var btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '<i class="ti-loader me-1 spin"></i>Processing...';

                fetch('{{ route("claims.facility-claims.batch-approve") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        approval_type: action,
                        claim_ids: ids,
                        notes: 'Batch ' + (action === 'es' ? 'ES approval' : 'finance payment')
                    })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        var msg = data.message;
                        if (data.errors && data.errors.length > 0) {
                            msg += '\n\nErrors:\n' + data.errors.join('\n');
                        }
                        alert(msg);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti-checks me-1"></i>Execute Batch';
                    }
                })
                .catch(function(e) {
                    alert('Request failed: ' + e.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti-checks me-1"></i>Execute Batch';
                });
            }
        </script>
    @endpush
