@extends('layouts.app')

@section('title', 'Claims Audit Report - Super Admin')

@section('content')
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <h3 class="fw-bold text-dark mb-1">
                    <i class="fas fa-clipboard-check text-success me-2"></i>Claims Verification Audit Report
                </h3>
                <p class="text-muted mb-0 small">
                    Track reviewer activity metrics, apply custom date range filters, and generate audit reports.
                </p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Claims
                </a>
                <a href="{{ route('claims.audit.export', request()->query()) }}" class="btn btn-success btn-sm me-2">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('claims.audit.export-pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light rounded">
                <form method="GET" action="{{ route('claims.audit.report') }}" id="auditFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="date_range" class="form-label fw-bold text-secondary small mb-1">Date Range</label>
                            <select name="date_range" id="date_range" class="form-select form-select-sm" onchange="toggleCustomDates(this.value)">
                                <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 Days</option>
                                <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last 365 Days (1 Year)</option>
                                <option value="all" {{ $dateRange == 'all' ? 'selected' : '' }}>All Time</option>
                                <option value="custom" {{ ($dateFrom && $dateTo) || $dateRange == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                            </select>
                        </div>

                        <div class="col-md-2 custom-date-field" style="{{ (($dateFrom && $dateTo) || $dateRange == 'custom') ? '' : 'display: none;' }}">
                            <label for="date_from" class="form-label fw-bold text-secondary small mb-1">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>

                        <div class="col-md-2 custom-date-field" style="{{ (($dateFrom && $dateTo) || $dateRange == 'custom') ? '' : 'display: none;' }}">
                            <label for="date_to" class="form-label fw-bold text-secondary small mb-1">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>

                        <div class="col-md-3">
                            <label for="program_id" class="form-label fw-bold text-secondary small mb-1">Program</label>
                            <select name="program_id" id="program_id" class="form-select form-select-sm">
                                <option value="">-- All Programs --</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}" {{ (isset($programId) && (string)$programId === (string)$prog->id) ? 'selected' : '' }}>
                                        {{ $prog->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="reviewer_id" class="form-label fw-bold text-secondary small mb-1">Reviewer / Staff</label>
                            <select name="reviewer_id" id="reviewer_id" class="form-select form-select-sm">
                                <option value="">-- All Reviewers & Staff --</option>
                                @foreach($allStaff as $staff)
                                    <option value="{{ $staff->id }}" {{ $reviewerId == (string)$staff->id ? 'selected' : '' }}>
                                        {{ $staff->fullname }} ({{ $staff->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-filter me-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('claims.audit.report') }}" class="btn btn-outline-dark btn-sm" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small">Active Reviewers</span>
                                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($activeReviewersCount) }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                <i class="fas fa-users-cog fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-primary">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small">Total Claims Audited</span>
                                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalClaimsProcessed) }}</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="fas fa-file-invoice fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-info">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small">Total Processed Value</span>
                                <h3 class="fw-bold text-success mb-0 mt-1">₦{{ number_format($totalValueProcessed, 2) }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                                <i class="fas fa-coins fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Reviewer Performance Summary -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-user-check text-primary me-2"></i>Reviewer Performance Summary
                    </h5>
                    <div class="text-muted small ms-4">Individual activity breakdown and processing metrics by reviewer</div>
                </div>
                <span class="badge bg-secondary">Period: {{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-3">Reviewer / Staff Member</th>
                                <th class="text-center">Verified</th>
                                <th class="text-center">RO Approved</th>
                                <th class="text-center">ES Approved</th>
                                <th class="text-center">Paid Claims</th>
                                <th class="text-center">Rejected</th>
                                <th class="text-center">Total Actions</th>
                                <th class="text-end">Total Processed Value (₦)</th>
                                <th class="text-center pe-3">Last Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewerStats as $stat)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $stat['name'] }}</div>
                                        <div class="text-muted small">{{ $stat['email'] }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success text-white fw-bold px-2 py-1" style="font-size: 0.85rem;">
                                            {{ number_format($stat['verified_count']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1" style="font-size: 0.85rem;">
                                            {{ number_format($stat['approved_count']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-white fw-bold px-2 py-1" style="font-size: 0.85rem; color: #ffffff !important;">
                                            {{ number_format($stat['es_approved_count']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.85rem; color: #182433 !important;">
                                            {{ number_format($stat['paid_count']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger text-white fw-bold px-2 py-1" style="font-size: 0.85rem;">
                                            {{ number_format($stat['rejected_count']) }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold fs-6 text-dark">
                                        {{ number_format($stat['total_actions']) }}
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        ₦{{ number_format($stat['total_value'], 2) }}
                                    </td>
                                    <td class="text-center pe-3 text-muted small">
                                        {{ $stat['last_activity'] ? \Carbon\Carbon::parse($stat['last_activity'])->format('d M, Y H:i A') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                                        No reviewer activity records found for the selected filter parameters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. Detailed Itemized Audit Trail -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-list-alt text-success me-2"></i>Itemized Claim Audit Trail
                    </h5>
                    <div class="text-muted small ms-4">Chronological log of claim verifications, approvals, and reviewer actions</div>
                </div>
                <span class="badge bg-light text-dark border">Showing {{ $auditClaims->firstItem() ?? 0 }} - {{ $auditClaims->lastItem() ?? 0 }} of {{ $auditClaims->total() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-3">Date & Time</th>
                                <th>Claim Number</th>
                                <th>Patient / Enrollee</th>
                                <th>Action / Status</th>
                                <th>Processed By (Reviewer)</th>
                                <th class="text-end">Amount</th>
                                <th>Notes / Reason</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditClaims as $claim)
                                @php
                                    $reviewerName = 'N/A';
                                    if ($claim->verifier_id && isset($nameMap[$claim->verifier_id])) {
                                        $reviewerName = $nameMap[$claim->verifier_id];
                                    } elseif ($claim->approver_id && isset($nameMap[$claim->approver_id])) {
                                        $reviewerName = $nameMap[$claim->approver_id];
                                    } elseif ($claim->es_id && isset($nameMap[$claim->es_id])) {
                                        $reviewerName = $nameMap[$claim->es_id];
                                    } elseif ($claim->finance_id && isset($nameMap[$claim->finance_id])) {
                                        $reviewerName = $nameMap[$claim->finance_id];
                                    }

                                    $notes = $claim->rejection_reason ?: ($claim->verifier_notes ?: ($claim->approver_notes ?: ($claim->es_notes ?: $claim->finance_notes)));

                                    $statusBadge = match($claim->status) {
                                        'verified' => 'bg-success text-white',
                                        'ro_approved', 'approved' => 'bg-primary text-white',
                                        'es_approved' => 'bg-info text-white',
                                        'paid' => 'bg-warning text-dark',
                                        'rejected' => 'bg-danger text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3 text-nowrap small text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($claim->updated_at)->format('d M, Y H:i:s') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('claims.facility-claim.show', $claim->id) }}" class="fw-bold text-primary text-decoration-none">
                                            {{ $claim->claim_number ?: ('CLM-'.$claim->id) }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $claim->patient_name ?: 'N/A' }}</div>
                                        <div class="text-muted small">{{ $claim->boschma_no ?: $claim->enrollee_number }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadge }} text-uppercase px-2.5 py-1 fw-bold" style="{{ $claim->status === 'paid' ? 'color: #182433 !important;' : '' }}">
                                            {{ strtoupper($claim->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="fas fa-user-check me-1 text-secondary"></i> {{ $reviewerName }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        ₦{{ number_format($claim->total_amount ?? 0, 2) }}
                                    </td>
                                    <td class="small text-muted" style="max-width: 250px;">
                                        {{ $notes ?: '-' }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('claims.facility-claim.show', $claim->id) }}" class="btn btn-sm btn-outline-primary" title="View Claim Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No itemized audit logs found for the selected filter parameters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($auditClaims->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $auditClaims->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleCustomDates(value) {
            const customFields = document.querySelectorAll('.custom-date-field');
            if (value === 'custom') {
                customFields.forEach(el => el.style.display = 'block');
            } else {
                customFields.forEach(el => el.style.display = 'none');
                document.getElementById('auditFilterForm').submit();
            }
        }
    </script>
@endpush
