@extends('layouts.app')

@section('title', 'Claims Management & Verification Dashboard - Boschma Administration')

@section('content')
    @php
        $userStage = $userStage ?? 'admin';
        $stageConfig = match($userStage) {
            'verifier' => [
                'pending_label' => 'Pending Verification',
                'pending_sub' => 'Waiting to be Verified',
                'pending_filter' => 'verifier_pending',
                'approved_label' => 'Total Verified',
                'approved_sub' => 'Verified by Claim Verifier',
                'approved_filter' => 'verifier_approved',
                'rejected_label' => 'Rejected Claims',
                'rejected_sub' => 'Rejected by Claim Approver',
                'rejected_filter' => 'rejected_by_approver',
                'self_rejected_label' => 'Rejected to Facility',
                'self_rejected_filter' => 'rejected_by_verifier',
                'pending_val_label' => 'Pending Value',
                'pending_val_sub' => 'Waiting to be Verified',
                'approved_val_label' => 'Total Approved (Verifier)',
                'approved_val_sub' => 'Approved at Verifier Stage',
                'tbl_pending' => 'Pending Verification',
                'tbl_approved' => 'Total Verified',
                'tbl_rejected' => 'Rejected (by Approver)',
                'tbl_pending_amt' => 'Pending Amount',
                'tbl_approved_amt' => 'Verified Amount',
            ],
            'approver' => [
                'pending_label' => 'Pending Approval',
                'pending_sub' => 'Waiting to be Approved',
                'pending_filter' => 'approver_pending',
                'approved_label' => 'Total Approved',
                'approved_sub' => 'Approved by Claim Approver',
                'approved_filter' => 'approver_approved',
                'rejected_label' => 'Rejected Claims',
                'rejected_sub' => 'Rejected by Claim ES',
                'rejected_filter' => 'rejected_by_es',
                'self_rejected_label' => 'Rejected to Verifier',
                'self_rejected_filter' => 'rejected_by_approver',
                'pending_val_label' => 'Pending Value',
                'pending_val_sub' => 'Waiting to be Approved',
                'approved_val_label' => 'Total Approved (Approver)',
                'approved_val_sub' => 'Approved at Approver Stage',
                'tbl_pending' => 'Pending Approval',
                'tbl_approved' => 'Total Approved',
                'tbl_rejected' => 'Rejected (by ES)',
                'tbl_pending_amt' => 'Pending Amount',
                'tbl_approved_amt' => 'Approved Amount',
            ],
            'es' => [
                'pending_label' => 'Pending ES Approval',
                'pending_sub' => 'Waiting for ES Approval',
                'pending_filter' => 'es_pending',
                'approved_label' => 'Total ES Approved',
                'approved_sub' => 'Approved by Claim ES',
                'approved_filter' => 'es_stage_approved',
                'rejected_label' => 'Rejected Claims',
                'rejected_sub' => 'Rejected by Claim Finance',
                'rejected_filter' => 'rejected_by_finance',
                'self_rejected_label' => 'Rejected to Approver',
                'self_rejected_filter' => 'rejected_by_es',
                'pending_val_label' => 'Pending ES Value',
                'pending_val_sub' => 'Waiting for ES Approval',
                'approved_val_label' => 'Total Approved (ES)',
                'approved_val_sub' => 'Approved at ES Stage',
                'tbl_pending' => 'Pending ES Approval',
                'tbl_approved' => 'Total ES Approved',
                'tbl_rejected' => 'Rejected (by Finance)',
                'tbl_pending_amt' => 'Pending Amount',
                'tbl_approved_amt' => 'ES Approved Amount',
            ],
            'finance' => [
                'pending_label' => 'Pending Payment',
                'pending_sub' => 'Waiting for Payment',
                'pending_filter' => 'finance_pending',
                'approved_label' => 'Total Paid',
                'approved_sub' => 'Paid by Claim Finance',
                'approved_filter' => 'finance_paid',
                'rejected_label' => 'Rejected Claims',
                'rejected_sub' => 'Rejected at Finance Stage',
                'rejected_filter' => 'rejected_by_finance',
                'self_rejected_label' => 'Rejected to ES',
                'self_rejected_filter' => 'rejected_by_finance',
                'pending_val_label' => 'Pending Payment Value',
                'pending_val_sub' => 'Waiting to be Paid',
                'approved_val_label' => 'Total Paid (Finance)',
                'approved_val_sub' => 'Paid at Finance Stage',
                'tbl_pending' => 'Pending Payment',
                'tbl_approved' => 'Total Paid',
                'tbl_rejected' => 'Rejected Claims',
                'tbl_pending_amt' => 'Pending Amount',
                'tbl_approved_amt' => 'Paid Amount',
            ],
            default => null,
        };
    @endphp

    <!-- Page Header & Action Bar -->
    <div class="page-header d-print-none mb-4">
        <div class="container-fluid">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-primary-transparent me-3 rounded-3">
                            <i class="ti-receipt fs-1 text-primary"></i>
                        </div>
                        <div>
                            <h2 class="page-title mb-1 fw-bold text-dark">Claims Management Dashboard</h2>
                            <div class="text-muted small">
                                <span class="badge bg-success text-white me-1"><i class="ti-circle-check me-1"></i>System Active</span>
                                Review, verify, and track healthcare facility claims workflow
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-list justify-content-md-end">
                        @can('review-claims')
                            <a href="{{ route('claims.ro-review') }}" class="btn btn-warning shadow-sm">
                                <i class="ti-clipboard-check me-1"></i>RO Review Queue 
                                <span class="badge bg-dark text-white ms-1 fw-bold">{{ number_format($stats['ro_pending'] ?? 0) }}</span>
                            </a>
                        @endcan
                        @can('approve-claims')
                            <a href="{{ route('claims.e5-review') }}" class="btn btn-primary shadow-sm">
                                <i class="ti-shield-check me-1"></i>ES Approval Queue 
                                <span class="badge bg-white text-dark ms-1 fw-bold">{{ number_format($stats['e5_pending'] ?? 0) }}</span>
                            </a>
                        @endcan
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti-download me-1"></i>Export Reports
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="{{ route('claims.export', array_merge(request()->except(['page', 'status', 'format']), ['format' => 'excel'])) }}" class="dropdown-item">
                                    <i class="ti-file-spreadsheet me-2 text-success"></i>Export All Claims (Excel)
                                </a>
                                @if ($stageConfig)
                                    <a href="{{ route('claims.export', array_merge(request()->except(['page', 'status', 'format']), ['status' => $stageConfig['pending_filter'], 'format' => 'excel'])) }}" class="dropdown-item">
                                        <i class="ti-clock me-2 text-warning"></i>Export {{ $stageConfig['pending_label'] }} (Excel)
                                    </a>
                                    <a href="{{ route('claims.export', array_merge(request()->except(['page', 'status', 'format']), ['status' => $stageConfig['approved_filter'], 'format' => 'excel'])) }}" class="dropdown-item">
                                        <i class="ti-check me-2 text-success"></i>Export {{ $stageConfig['approved_label'] }} (Excel)
                                    </a>
                                    <a href="{{ route('claims.export', array_merge(request()->except(['page', 'status', 'format']), ['status' => $stageConfig['approved_filter'], 'format' => 'pdf'])) }}" class="dropdown-item">
                                        <i class="ti-file-text me-2 text-primary"></i>Export {{ $stageConfig['approved_label'] }} (PDF Slip)
                                    </a>
                                    <a href="{{ route('claims.export', array_merge(request()->except(['page', 'status', 'format']), ['status' => $stageConfig['rejected_filter'], 'format' => 'excel'])) }}" class="dropdown-item">
                                        <i class="ti-x me-2 text-danger"></i>Export {{ $stageConfig['rejected_sub'] }} (Excel)
                                    </a>
                                @else
                                    <a href="{{ route('claims.export', ['status' => 'verifier_pending', 'format' => 'excel']) }}" class="dropdown-item">
                                        <i class="ti-clock me-2 text-warning"></i>Export Pending Verification
                                    </a>
                                    <a href="{{ route('claims.export', ['status' => 'approver_pending', 'format' => 'excel']) }}" class="dropdown-item">
                                        <i class="ti-clock me-2 text-info"></i>Export Pending Approval
                                    </a>
                                    <a href="{{ route('claims.export', ['status' => 'es_pending', 'format' => 'excel']) }}" class="dropdown-item">
                                        <i class="ti-shield-check me-2 text-primary"></i>Export Pending ES Approval
                                    </a>
                                    <a href="{{ route('claims.export', ['status' => 'finance_pending', 'format' => 'excel']) }}" class="dropdown-item">
                                        <i class="ti-wallet me-2 text-info"></i>Export Pending Payment
                                    </a>
                                    <a href="{{ route('claims.export', ['status' => 'approved', 'format' => 'pdf']) }}" class="dropdown-item">
                                        <i class="ti-check me-2 text-success"></i>Export Approved Claims (PDF)
                                    </a>
                                    <a href="{{ route('claims.export', ['status' => 'paid', 'format' => 'excel']) }}" class="dropdown-item">
                                        <i class="ti-money me-2 text-success"></i>Export Paid Claims (Excel)
                                    </a>
                                    <a href="{{ route('claims.export', ['status' => 'rejected', 'format' => 'excel']) }}" class="dropdown-item">
                                        <i class="ti-x me-2 text-danger"></i>Export Rejected Claims
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('claims.export', array_merge(request()->except('page'), ['format' => 'excel'])) }}" class="dropdown-item">
                                    <i class="ti-filter me-2 text-primary"></i>Export Filtered Records
                                </a>
                                <a href="{{ route('claims.audit.report') }}" class="dropdown-item">
                                    <i class="ti-shield me-2 text-info"></i>Audit Trail Report
                                </a>
                            </div>
                        </div>
                        @can('claim.create')
                            <a href="{{ route('claims.create') }}" class="btn btn-success shadow-sm">
                                <i class="ti-plus me-1"></i>New Claim
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid">

            <!-- 1. Statistics & Workflow Metrics Cards -->
            @if ($stageConfig)
                <div class="row row-deck row-cards mb-4">
                    <!-- Card 1: Total Claims -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #206bc4 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Claims</span>
                                    <div class="avatar avatar-xs bg-primary-transparent rounded-2">
                                        <i class="ti-files text-primary"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-dark">{{ number_format($stats['total_claims'] ?? 0) }}</div>
                                <div class="text-muted small text-truncate" title="Total Value: ₦{{ number_format($stats['total_value'] ?? 0, 2) }}">
                                    ₦{{ number_format(($stats['total_value'] ?? 0) / 1000000, 2) }}M total value
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Pending at Stage -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #f59f00 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $stageConfig['pending_label'] }}</span>
                                    <div class="avatar avatar-xs bg-warning-transparent rounded-2">
                                        <i class="ti-clock text-warning"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-warning">{{ number_format($stats['stage_pending'] ?? 0) }}</div>
                                <div class="text-muted small">{{ $stageConfig['pending_sub'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Total Verified / Approved at Stage -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #2fb344 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $stageConfig['approved_label'] }}</span>
                                    <div class="avatar avatar-xs bg-success-transparent rounded-2">
                                        <i class="ti-check text-success"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-success">{{ number_format($stats['stage_approved'] ?? 0) }}</div>
                                <div class="text-muted small">{{ $stageConfig['approved_sub'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Rejected Claims (by Next Stage) -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #d63939 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $stageConfig['rejected_label'] }}</span>
                                    <div class="avatar avatar-xs bg-danger-lt rounded-2">
                                        <i class="ti-x text-danger"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-danger">{{ number_format($stats['stage_rejected'] ?? 0) }}</div>
                                <div class="text-muted small" title="Amount: ₦{{ number_format($stats['stage_rejected_value'] ?? 0, 2) }}">
                                    {{ $stageConfig['rejected_sub'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Total Amount Waiting at Stage -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #f59f00 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $stageConfig['pending_val_label'] }}</span>
                                    <div class="avatar avatar-xs bg-warning-transparent rounded-2">
                                        <i class="ti-wallet text-warning"></i>
                                    </div>
                                </div>
                                <div class="h3 mb-1 fw-bold text-warning">₦{{ number_format(($stats['stage_pending_value'] ?? 0) / 1000000, 2) }}M</div>
                                <div class="text-muted small text-truncate" title="Exact: ₦{{ number_format($stats['stage_pending_value'] ?? 0, 2) }}">
                                    ₦{{ number_format($stats['stage_pending_value'] ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6: Total Approved Value at Stage -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #016634 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $stageConfig['approved_val_label'] }}</span>
                                    <div class="avatar avatar-xs bg-success-transparent rounded-2">
                                        <i class="ti-wallet text-success"></i>
                                    </div>
                                </div>
                                <div class="h3 mb-1 fw-bold text-success">₦{{ number_format(($stats['stage_approved_value'] ?? 0) / 1000000, 2) }}M</div>
                                <div class="text-muted small text-truncate" title="Exact: ₦{{ number_format($stats['stage_approved_value'] ?? 0, 2) }}">
                                    ₦{{ number_format($stats['stage_approved_value'] ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row row-deck row-cards mb-4">
                    <!-- Card 1: Total Claims -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #206bc4 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Claims</span>
                                    <div class="avatar avatar-xs bg-primary-transparent rounded-2">
                                        <i class="ti-files text-primary"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-dark">{{ number_format($stats['total_claims'] ?? 0) }}</div>
                                <div class="text-muted small text-truncate" title="Total Value: ₦{{ number_format($stats['total_value'] ?? 0, 2) }}">
                                    ₦{{ number_format(($stats['total_value'] ?? 0) / 1000000, 2) }}M total value
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Pending Verification (Verifier) -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #f59f00 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pending Verification</span>
                                    <div class="avatar avatar-xs bg-warning-transparent rounded-2">
                                        <i class="ti-clock text-warning"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-warning">{{ number_format($stats['ro_pending'] ?? 0) }}</div>
                                <div class="text-muted small">Awaiting Verifier</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Pending Approval (Approver / ES) -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #4299e1 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pending Approval</span>
                                    <div class="avatar avatar-xs bg-info-transparent rounded-2">
                                        <i class="ti-shield-check text-info"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-info">{{ number_format($stats['e5_pending'] ?? 0) }}</div>
                                <div class="text-muted small">ES Pending: {{ number_format($stats['es_pending'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Approved Claims -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #2fb344 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Approved</span>
                                    <div class="avatar avatar-xs bg-success-transparent rounded-2">
                                        <i class="ti-check text-success"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-success">{{ number_format($stats['approved'] ?? 0) }}</div>
                                <div class="text-muted small">Ready for Payment</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Paid Claims -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #17a2b8 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Paid Claims</span>
                                    <div class="avatar avatar-xs bg-info-transparent rounded-2">
                                        <i class="ti-money text-info"></i>
                                    </div>
                                </div>
                                <div class="h2 mb-1 fw-bold text-info">{{ number_format($stats['paid'] ?? 0) }}</div>
                                <div class="text-muted small">Disbursed Settlements</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6: Approved Value -->
                    <div class="col-sm-6 col-lg-4 col-xl-2">
                        <div class="card border-0 shadow-sm card-metric" style="border-radius: 14px; border-left: 4px solid #016634 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Approved Value</span>
                                    <div class="avatar avatar-xs bg-success-transparent rounded-2">
                                        <i class="ti-wallet text-success"></i>
                                    </div>
                                </div>
                                <div class="h3 mb-1 fw-bold text-success">₦{{ number_format(($stats['approved_value'] ?? 0) / 1000000, 2) }}M</div>
                                <div class="text-muted small text-truncate" title="Exact: ₦{{ number_format($stats['approved_value'] ?? 0, 2) }}">
                                    ₦{{ number_format($stats['approved_value'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 2. Facility Claims Breakdown Overview -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header py-3 bg-white" style="border-radius: 14px 14px 0 0; border-bottom: 1px solid #edf2f7;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="ti-building me-2 text-primary"></i>Healthcare Facility Claims Summary
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Aggregated claims breakdown per healthcare provider</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('claims.analytics') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti-chart-bar me-1"></i>Analytics
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" onclick="refreshClaimsData()">
                                <i class="ti-refresh me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-vcenter card-table mb-0">
                            <thead class="bg-light">
                                @if ($stageConfig)
                                    <tr>
                                        <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Healthcare Facility</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Total Claims</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">{{ $stageConfig['tbl_pending'] }}</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">{{ $stageConfig['tbl_approved'] }}</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">{{ $stageConfig['tbl_rejected'] }}</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">Total Claimed Amount</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">{{ $stageConfig['tbl_pending_amt'] }}</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">{{ $stageConfig['tbl_approved_amt'] }}</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">Actions</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Healthcare Facility</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Total Claims</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Pending Verifier</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Pending Approver</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Pending ES</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Pending Payment</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Paid</th>
                                        <th class="fw-bold text-muted text-uppercase text-center" style="font-size: 0.75rem;">Rejected</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">Total Claimed Amount</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">Approved Amount</th>
                                        <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">Actions</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @forelse ($facilities ?? [] as $facility)
                                    @php
                                        if ($userStage === 'verifier') {
                                            $fPending = $facility->verifier_pending ?? 0;
                                            $fApproved = $facility->verifier_approved ?? 0;
                                            $fRejected = $facility->rejected_by_approver ?? 0;
                                            $fPendingAmt = $facility->verifier_pending_value ?? 0;
                                            $fApprovedAmt = $facility->verifier_approved_value ?? 0;
                                        } elseif ($userStage === 'approver') {
                                            $fPending = $facility->approver_pending ?? 0;
                                            $fApproved = $facility->approver_approved ?? 0;
                                            $fRejected = $facility->rejected_by_es ?? 0;
                                            $fPendingAmt = $facility->approver_pending_value ?? 0;
                                            $fApprovedAmt = $facility->approver_approved_value ?? 0;
                                        } elseif ($userStage === 'es') {
                                            $fPending = $facility->es_pending ?? 0;
                                            $fApproved = $facility->es_approved_count ?? 0;
                                            $fRejected = $facility->rejected_by_finance ?? 0;
                                            $fPendingAmt = $facility->es_pending_value ?? 0;
                                            $fApprovedAmt = $facility->es_approved_value ?? 0;
                                        } elseif ($userStage === 'finance') {
                                            $fPending = $facility->finance_pending ?? 0;
                                            $fApproved = $facility->finance_paid ?? 0;
                                            $fRejected = $facility->rejected_by_finance ?? 0;
                                            $fPendingAmt = $facility->finance_pending_value ?? 0;
                                            $fApprovedAmt = $facility->finance_paid_value ?? 0;
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary-transparent me-2 rounded-2">
                                                    <i class="ti-building fs-4 text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $facility->facility_name }}</div>
                                                    <div class="text-muted small">
                                                        {{ $facility->number_of_patients ?? 0 }} Enrollees • Latest: 
                                                        {{ $facility->latest_claim_date ? \Carbon\Carbon::parse($facility->latest_claim_date)->format('M j, Y') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white px-3 py-1 fs-6 fw-bold">
                                                {{ number_format($facility->total_claims ?? 0) }}
                                            </span>
                                        </td>
                                        @if ($stageConfig)
                                            <td class="text-center">
                                                @if ($fPending > 0)
                                                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold">
                                                        <i class="ti-clock me-1"></i>{{ number_format($fPending) }} Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-3 py-1">0 Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($fApproved > 0)
                                                    <span class="badge bg-success text-white px-3 py-1 fw-bold">
                                                        <i class="ti-check me-1"></i>{{ number_format($fApproved) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-3 py-1">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($fRejected > 0)
                                                    <span class="badge bg-danger text-white px-3 py-1 fw-bold">
                                                        <i class="ti-x me-1"></i>{{ number_format($fRejected) }} Rejected
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-3 py-1">0</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-dark">₦{{ number_format($facility->total_value ?? 0, 2) }}</div>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-warning">₦{{ number_format($fPendingAmt, 2) }}</div>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-success">₦{{ number_format($fApprovedAmt, 2) }}</div>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <span class="badge {{ ($facility->verifier_pending ?? 0) > 0 ? 'bg-warning text-dark fw-bold' : 'bg-secondary text-white' }} px-2 py-1">
                                                    {{ number_format($facility->verifier_pending ?? 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($facility->approver_pending ?? 0) > 0 ? 'bg-info text-white fw-bold' : 'bg-secondary text-white' }} px-2 py-1">
                                                    {{ number_format($facility->approver_pending ?? 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($facility->es_pending ?? 0) > 0 ? 'bg-primary text-white fw-bold' : 'bg-secondary text-white' }} px-2 py-1">
                                                    {{ number_format($facility->es_pending ?? 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($facility->finance_pending ?? 0) > 0 ? 'bg-warning text-dark fw-bold' : 'bg-secondary text-white' }} px-2 py-1">
                                                    {{ number_format($facility->finance_pending ?? 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($facility->finance_paid ?? 0) > 0 ? 'bg-success text-white fw-bold' : 'bg-secondary text-white' }} px-2 py-1">
                                                    {{ number_format($facility->finance_paid ?? 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($facility->rejected_claims ?? 0) > 0 ? 'bg-danger text-white fw-bold' : 'bg-secondary text-white' }} px-2 py-1">
                                                    {{ number_format($facility->rejected_claims ?? 0) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-dark">₦{{ number_format($facility->total_value ?? 0, 2) }}</div>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold text-success">₦{{ number_format($facility->approved_value ?? 0, 2) }}</div>
                                            </td>
                                        @endif
                                        <td class="text-end">
                                            <div class="btn-list justify-content-end flex-nowrap">
                                                <a href="{{ route('claims.facility.show', $facility->facility_id) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View Facility Claims">
                                                    <i class="ti-eye me-1"></i>View Claims
                                                </a>
                                                @can('review-claims')
                                                    <a href="{{ route('claims.ro-review') }}" 
                                                       class="btn btn-sm btn-warning"
                                                       title="Review Claims">
                                                        <i class="ti-clipboard-check me-1"></i>Review
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $stageConfig ? 9 : 11 }}" class="text-center py-5">
                                            <div class="empty">
                                                <div class="empty-icon text-muted mb-2">
                                                    <i class="ti-file-off fs-1"></i>
                                                </div>
                                                <p class="empty-title fw-bold">No facility claims available</p>
                                                <p class="empty-subtitle text-muted">Facility claims will appear here once submitted.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Recent Claim Submissions Table -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header py-3 bg-white" style="border-radius: 14px 14px 0 0; border-bottom: 1px solid #edf2f7;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="ti-list-check me-2 text-success"></i>Recent Claim Submissions
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Individual claims submitted across all providers</p>
                        </div>
                        <div>
                            <form method="GET" action="{{ route('claims.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                                <select name="program_id" class="form-select form-select-sm" style="min-width: 140px;" onchange="this.form.submit()">
                                    <option value="">All Programs</option>
                                    @foreach($programs ?? [] as $program)
                                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-icon">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Search claim #, enrollee, facility..." value="{{ request('search') }}">
                                </div>
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    @if ($stageConfig)
                                        <option value="{{ $stageConfig['pending_filter'] }}" {{ request('status') === $stageConfig['pending_filter'] ? 'selected' : '' }}>
                                            {{ $stageConfig['pending_label'] }}
                                        </option>
                                        <option value="{{ $stageConfig['approved_filter'] }}" {{ request('status') === $stageConfig['approved_filter'] ? 'selected' : '' }}>
                                            {{ $stageConfig['approved_label'] }}
                                        </option>
                                        <option value="{{ $stageConfig['rejected_filter'] }}" {{ request('status') === $stageConfig['rejected_filter'] ? 'selected' : '' }}>
                                            {{ $stageConfig['rejected_sub'] }}
                                        </option>
                                        @if ($stageConfig['self_rejected_filter'] !== $stageConfig['rejected_filter'])
                                            <option value="{{ $stageConfig['self_rejected_filter'] }}" {{ request('status') === $stageConfig['self_rejected_filter'] ? 'selected' : '' }}>
                                                {{ $stageConfig['self_rejected_label'] }}
                                            </option>
                                        @endif
                                    @else
                                        <option value="verifier_pending" {{ in_array(request('status'), ['verifier_pending', 'ro_pending']) ? 'selected' : '' }}>Pending Verification (Verifier)</option>
                                        <option value="approver_pending" {{ in_array(request('status'), ['approver_pending', 'e5_pending']) ? 'selected' : '' }}>Pending Approval (Approver)</option>
                                        <option value="es_pending" {{ request('status') === 'es_pending' ? 'selected' : '' }}>Pending ES Approval (ES)</option>
                                        <option value="finance_pending" {{ request('status') === 'finance_pending' ? 'selected' : '' }}>Pending Payment (Finance)</option>
                                        <option value="verifier_approved" {{ request('status') === 'verifier_approved' ? 'selected' : '' }}>Verified (All)</option>
                                        <option value="approver_approved" {{ request('status') === 'approver_approved' ? 'selected' : '' }}>Approved (All)</option>
                                        <option value="es_stage_approved" {{ request('status') === 'es_stage_approved' ? 'selected' : '' }}>ES Approved (All)</option>
                                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    @endif
                                </select>
                                @if(request()->hasAny(['search', 'status', 'program_id']))
                                    <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                                        <i class="ti-x"></i> Clear
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-vcenter card-table mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Claim #</th>
                                    <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Enrollee Details</th>
                                    <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Healthcare Provider</th>
                                    <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Service Date</th>
                                    <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Claim Amount</th>
                                    <th class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Workflow Status</th>
                                    <th class="fw-bold text-muted text-uppercase text-end" style="font-size: 0.75rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentClaims ?? [] as $claim)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $claim->authorization_code ?? 'CLM-' . $claim->id }}</div>
                                            <div class="text-muted small">Submitted {{ \Carbon\Carbon::parse($claim->created_at)->format('M j, Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary-transparent me-2 rounded-circle">
                                                    <i class="ti-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $claim->beneficiary_name }}</div>
                                                    <div class="text-muted small">ID: {{ $claim->boschma_id ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $claim->healthcare_provider }}</div>
                                            <div class="text-muted small">{{ $claim->claim_type ?? 'Medical Claim' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                {{ $claim->service_date ? \Carbon\Carbon::parse($claim->service_date)->format('M j, Y') : 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">₦{{ number_format($claim->claim_amount ?? 0, 2) }}</div>
                                        </td>
                                        <td>
                                            @if ($userStage === 'verifier')
                                                @if (($claim->approver_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Rejected by Approver</div>
                                                @elseif (($claim->verifier_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Back to Facility</div>
                                                @elseif (($claim->verifier_status ?? '') === 'approved')
                                                    <span class="badge bg-success text-white px-2 py-1 fw-bold"><i class="ti-check me-1"></i>Verified</span>
                                                @else
                                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold"><i class="ti-clock me-1"></i>Pending Verification</span>
                                                @endif
                                            @elseif ($userStage === 'approver')
                                                @if (($claim->es_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Rejected by ES</div>
                                                @elseif (($claim->approver_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Back to Verifier</div>
                                                @elseif (($claim->approver_status ?? '') === 'approved')
                                                    <span class="badge bg-success text-white px-2 py-1 fw-bold"><i class="ti-check me-1"></i>Approved</span>
                                                @else
                                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold"><i class="ti-clock me-1"></i>Pending Approval</span>
                                                @endif
                                            @elseif ($userStage === 'es')
                                                @if (($claim->finance_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Rejected by Finance</div>
                                                @elseif (($claim->es_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Back to Approver</div>
                                                @elseif (($claim->es_status ?? '') === 'approved')
                                                    <span class="badge bg-success text-white px-2 py-1 fw-bold"><i class="ti-check me-1"></i>ES Approved</span>
                                                @else
                                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold"><i class="ti-clock me-1"></i>Pending ES Approval</span>
                                                @endif
                                            @elseif ($userStage === 'finance')
                                                @if (($claim->finance_status ?? '') === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold"><i class="ti-x me-1"></i>Rejected</span>
                                                    <div class="text-danger small mt-1 fw-semibold">Back to ES</div>
                                                @elseif (($claim->finance_status ?? '') === 'paid' || $claim->status === 'paid')
                                                    <span class="badge bg-success text-white px-2 py-1 fw-bold"><i class="ti-money me-1"></i>Paid</span>
                                                @else
                                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold"><i class="ti-clock me-1"></i>Pending Payment</span>
                                                @endif
                                            @else
                                                @if (($claim->verifier_status ?? '') === 'rejected' || ($claim->approver_status ?? '') === 'rejected' || ($claim->es_status ?? '') === 'rejected' || ($claim->finance_status ?? '') === 'rejected' || $claim->status === 'rejected')
                                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold">
                                                        <i class="ti-x me-1"></i>Rejected
                                                    </span>
                                                @elseif ($claim->status === 'submitted')
                                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold">
                                                        <i class="ti-clock me-1"></i>Pending Verification
                                                    </span>
                                                @elseif ($claim->status === 'verified')
                                                    <span class="badge bg-info text-white px-2 py-1 fw-bold">
                                                        <i class="ti-shield-check me-1"></i>Verified (Pending Approver)
                                                    </span>
                                                @elseif ($claim->status === 'approved')
                                                    <span class="badge bg-primary text-white px-2 py-1 fw-bold">
                                                        <i class="ti-check me-1"></i>Approved (Pending ES)
                                                    </span>
                                                @elseif ($claim->status === 'es_approved')
                                                    <span class="badge bg-success text-white px-2 py-1 fw-bold">
                                                        <i class="ti-check-double me-1"></i>ES Approved (Pending Finance)
                                                    </span>
                                                @elseif ($claim->status === 'paid')
                                                    <span class="badge bg-success text-white px-2 py-1 fw-bold">
                                                        <i class="ti-money me-1"></i>Paid
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($claim->status) }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('claims.facility-claim.show', $claim->id) }}?return_url={{ urlencode(request()->fullUrl()) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Claim Details">
                                                <i class="ti-eye me-1"></i>Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty">
                                                <div class="empty-icon text-muted mb-2">
                                                    <i class="ti-search fs-1"></i>
                                                </div>
                                                <p class="empty-title fw-bold">No claims match your search</p>
                                                <p class="empty-subtitle text-muted">Try adjusting your search criteria or filters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if (isset($recentClaims) && method_exists($recentClaims, 'hasPages') && $recentClaims->hasPages())
                        <div class="card-footer d-flex align-items-center justify-content-between py-3">
                            <div class="text-muted small">
                                Showing {{ $recentClaims->firstItem() }} to {{ $recentClaims->lastItem() }} of {{ $recentClaims->total() }} claims
                            </div>
                            <div>
                                {{ $recentClaims->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. Quick Tools & Activity Timeline Grid -->
            <div class="row g-4">
                <!-- Quick Tools -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                        <div class="card-header bg-white py-3" style="border-radius: 14px 14px 0 0;">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="ti-bolt me-2 text-warning"></i>Workflow & Verification Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @can('review-claims')
                                    <div class="col-md-6">
                                        <a href="{{ route('claims.ro-review') }}" class="card card-link text-decoration-none border shadow-none hover-shadow p-3 h-100" style="border-radius: 10px;">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-warning-transparent me-3 rounded-2">
                                                    <i class="ti-clipboard-check fs-2 text-warning"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">RO Review Queue</div>
                                                    <div class="text-muted small">{{ number_format($stats['ro_pending'] ?? 0) }} pending verification</div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endcan

                                @can('approve-claims')
                                    <div class="col-md-6">
                                        <a href="{{ route('claims.e5-review') }}" class="card card-link text-decoration-none border shadow-none hover-shadow p-3 h-100" style="border-radius: 10px;">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary-transparent me-3 rounded-2">
                                                    <i class="ti-shield-check fs-2 text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">ES Approval Queue</div>
                                                    <div class="text-muted small">{{ number_format($stats['e5_pending'] ?? 0) }} pending approval</div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endcan

                                <div class="col-md-6">
                                    <a href="{{ route('claims.audit.report') }}" class="card card-link text-decoration-none border shadow-none hover-shadow p-3 h-100" style="border-radius: 10px;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-info-transparent me-3 rounded-2">
                                                <i class="ti-file-text fs-2 text-info"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">Audit & Compliance</div>
                                                <div class="text-muted small">View full claims trail</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <a href="{{ route('claims.bulk.upload') }}" class="card card-link text-decoration-none border shadow-none hover-shadow p-3 h-100" style="border-radius: 10px;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-success-transparent me-3 rounded-2">
                                                <i class="ti-upload fs-2 text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">Bulk Claims Upload</div>
                                                <div class="text-muted small">Import batch claims</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                        <div class="card-header bg-white py-3" style="border-radius: 14px 14px 0 0;">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="ti-activity me-2 text-primary"></i>Live Claims Activity Stream
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @forelse ($recentActivity ?? [] as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-point timeline-point-{{ $activity['type'] ?? 'primary' }}"></div>
                                        <div class="timeline-content shadow-none border-0 bg-light p-3 rounded-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-dark">{{ $activity['title'] }}</div>
                                                <div class="text-muted small">{{ $activity['time'] }}</div>
                                            </div>
                                            <div class="text-secondary small">{{ $activity['description'] }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="ti-history fs-2 mb-2 d-block"></i>
                                        No recent claims activity recorded.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Utility classes for soft light backgrounds used in dashboard metrics */
        .bg-primary-lt { background-color: rgba(1, 102, 52, 0.12) !important; color: #016634 !important; }
        .bg-warning-lt { background-color: rgba(245, 159, 0, 0.15) !important; color: #d97706 !important; }
        .bg-success-lt { background-color: rgba(47, 179, 68, 0.15) !important; color: #2fb344 !important; }
        .bg-info-lt { background-color: rgba(23, 162, 184, 0.15) !important; color: #17a2b8 !important; }
        .bg-blue-lt { background-color: rgba(32, 107, 196, 0.12) !important; color: #206bc4 !important; }
        .bg-secondary-lt { background-color: rgba(108, 117, 125, 0.12) !important; color: #6c757d !important; }
        .bg-danger-lt { background-color: rgba(214, 57, 57, 0.12) !important; color: #d63939 !important; }

        .card-metric {
            transition: all 0.25s ease-in-out;
        }
        .card-metric:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08) !important;
        }
        .hover-shadow {
            transition: all 0.2s ease;
        }
        .hover-shadow:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
            border-color: #016634 !important;
        }
        .timeline {
            position: relative;
            padding-left: 24px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 16px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-point {
            position: absolute;
            left: -24px;
            top: 6px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
        }
        .timeline-point-primary { background-color: #206bc4; }
        .timeline-point-warning { background-color: #f59f00; }
        .timeline-point-success { background-color: #2fb344; }
        .timeline-point-danger { background-color: #d63939; }
        .timeline-point-info { background-color: #17a2b8; }
    </style>
@endpush

@push('scripts')
    <script>
        function refreshClaimsData() {
            location.reload();
        }
    </script>
@endpush
