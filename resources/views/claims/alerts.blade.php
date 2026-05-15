@extends('layouts.app')

@section('title', 'Claims Alerts Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Claims Alerts Dashboard</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('claims.index') }}">Claims</a></li>
                                <li class="breadcrumb-item active">Alerts</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('claims.notifications') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-bell me-1"></i>Notifications
                        </a>
                        <a href="{{ route('claims.index') }}" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-arrow-left me-1"></i>Back to Claims
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Claims</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($pendingClaims) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">RO Pending</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($roPending) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">E5 Pending</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($e5Pending) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue Claims</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($overdueClaims->count()) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Cards -->
        <div class="row">
            <!-- Recent Pending Claims -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-clock me-2"></i>Recent Pending Claims
                        </h6>
                        <span class="badge bg-warning">{{ $recentPending->count() }}</span>
                    </div>
                    <div class="card-body">
                        @forelse($recentPending as $claim)
                            <div class="alert-item border-start border-4 border-warning ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('claims.show', $claim->id) }}" class="text-decoration-none">
                                                {{ $claim->authorization_code }}
                                            </a>
                                        </h6>
                                        <p class="text-muted mb-1">{{ $claim->beneficiary_name }}</p>
                                        <small class="text-muted">
                                            Amount: ₦{{ number_format($claim->claim_amount, 2) }}
                                            • Created {{ $claim->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ route('claims.show', $claim->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p>No pending claims</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Overdue Claims -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>Overdue Claims (7+ days)
                        </h6>
                        <span class="badge bg-danger">{{ $overdueClaims->count() }}</span>
                    </div>
                    <div class="card-body">
                        @forelse($overdueClaims as $claim)
                            <div class="alert-item border-start border-4 border-danger ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('claims.show', $claim->id) }}" class="text-decoration-none">
                                                {{ $claim->authorization_code }}
                                            </a>
                                        </h6>
                                        <p class="text-muted mb-1">{{ $claim->beneficiary_name }}</p>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $claim->created_at->diffForHumans(now()) }} old
                                            • ₦{{ number_format($claim->claim_amount, 2) }}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ route('claims.show', $claim->id) }}"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p>No overdue claims</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- High Value Claims -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-dollar-sign me-2"></i>High Value Claims (>₦100,000)
                        </h6>
                        <span class="badge bg-info">{{ $highValueClaims->count() }}</span>
                    </div>
                    <div class="card-body">
                        @forelse($highValueClaims as $claim)
                            <div class="alert-item border-start border-4 border-info ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('claims.show', $claim->id) }}"
                                                class="text-decoration-none">
                                                {{ $claim->authorization_code }}
                                            </a>
                                        </h6>
                                        <p class="text-muted mb-1">{{ $claim->beneficiary_name }}</p>
                                        <small class="text-info">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            ₦{{ number_format($claim->claim_amount, 2) }}
                                            • {{ $claim->created_at->format('M d, Y') }}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ route('claims.show', $claim->id) }}"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p>No high value claims</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('claims.index', ['status' => 'pending']) }}"
                                    class="btn btn-outline-warning w-100">
                                    <i class="fas fa-clock me-2"></i>View All Pending
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('claims.index', ['ro_status' => 'pending']) }}"
                                    class="btn btn-outline-info w-100">
                                    <i class="fas fa-user me-2"></i>RO Review Needed
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('claims.index', ['e5_status' => 'pending']) }}"
                                    class="btn btn-outline-primary w-100">
                                    <i class="fas fa-shield-alt me-2"></i>E5 Review Needed
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('claims.analytics') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-chart-bar me-2"></i>View Analytics
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('claims.bulk.upload') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-upload me-2"></i>Bulk Upload
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('claims.audit.report') }}" class="btn btn-outline-dark w-100">
                                    <i class="fas fa-history me-2"></i>Audit Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-heartbeat me-2"></i>System Health Overview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div
                                    class="health-indicator {{ $pendingClaims > 50 ? 'critical' : ($pendingClaims > 20 ? 'warning' : 'good') }}">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h5>{{ $pendingClaims }} Pending</h5>
                                    <small class="text-muted">
                                        @if ($pendingClaims > 50)
                                            Critical: High volume
                                        @elseif($pendingClaims > 20)
                                            Warning: Monitor closely
                                        @else
                                            Good: Normal volume
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div
                                    class="health-indicator {{ $overdueClaims->count() > 10 ? 'critical' : ($overdueClaims->count() > 5 ? 'warning' : 'good') }}">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <h5>{{ $overdueClaims->count() }} Overdue</h5>
                                    <small class="text-muted">
                                        @if ($overdueClaims->count() > 10)
                                            Critical: Immediate action needed
                                        @elseif($overdueClaims->count() > 5)
                                            Warning: Review required
                                        @else
                                            Good: Under control
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div
                                    class="health-indicator {{ $roPending > 30 ? 'critical' : ($roPending > 15 ? 'warning' : 'good') }}">
                                    <i class="fas fa-user fa-2x mb-2"></i>
                                    <h5>{{ $roPending }} RO Pending</h5>
                                    <small class="text-muted">
                                        @if ($roPending > 30)
                                            Critical: RO bottleneck
                                        @elseif($roPending > 15)
                                            Warning: RO attention needed
                                        @else
                                            Good: Normal flow
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div
                                    class="health-indicator {{ $e5Pending > 20 ? 'critical' : ($e5Pending > 10 ? 'warning' : 'good') }}">
                                    <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                    <h5>{{ $e5Pending }} E5 Pending</h5>
                                    <small class="text-muted">
                                        @if ($e5Pending > 20)
                                            Critical: E5 bottleneck
                                        @elseif($e5Pending > 10)
                                            Warning: E5 attention needed
                                        @else
                                            Good: Normal flow
                                        @endif
                                    </small>
                                </div>
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
        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-danger {
            border-left: 4px solid #e74a3b !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-xs {
            font-size: 0.7rem;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .health-indicator {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .health-indicator.good {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .health-indicator.warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        .health-indicator.critical {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-item {
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .alert-item:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
    </style>
@endpush
