@extends('layouts.app')

@section('title', 'Claims Audit Report')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Claims Audit Report</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('claims.index') }}">Claims</a></li>
                                <li class="breadcrumb-item active">Audit Report</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Claims
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <form method="GET" action="{{ route('claims.audit.report') }}">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label for="date_range" class="form-label">Date Range</label>
                                    <select name="date_range" id="date_range" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 days
                                        </option>
                                        <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 days
                                        </option>
                                        <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 days
                                        </option>
                                        <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last year
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Period</label>
                                    <div class="text-muted">
                                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Export</label>
                                    <a href="{{ route('claims.audit.export', ['date_range' => $dateRange]) }}"
                                        class="btn btn-outline-primary w-100">
                                        <i class="fas fa-download me-1"></i>Export Audit Trail
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Claims</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($claims->count()) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Audits</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalAudits) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-history fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Notes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalNotes) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sticky-note fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Avg Audits/Claim
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $claims->count() > 0 ? number_format($totalAudits / $claims->count(), 1) : '0' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Breakdown Chart -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Action Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="actionBreakdownChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Top User Activity</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th class="text-center">Actions</th>
                                        <th class="text-end">Last Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($userActivity as $activity)
                                        <tr>
                                            <td>{{ $activity->user ? $activity->user->fullname : 'Unknown' }}</td>
                                            <td class="text-center">{{ number_format($activity->action_count) }}</td>
                                            <td class="text-end">
                                                {{ $activity->last_activity ? \Carbon\Carbon::parse($activity->last_activity)->format('M d, H:i') : 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claims with Audit Trail -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Claims with Audit Trail</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Authorization Code</th>
                                        <th>Beneficiary</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Audit Count</th>
                                        <th class="text-center">Notes</th>
                                        <th>Last Activity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($claims as $claim)
                                        <tr>
                                            <td>
                                                <a href="{{ route('claims.show', $claim->id) }}"
                                                    class="text-decoration-none">
                                                    {{ $claim->authorization_code }}
                                                </a>
                                            </td>
                                            <td>{{ $claim->beneficiary_name }}</td>
                                            <td>₦{{ number_format($claim->claim_amount, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $claim->status == 'approved' ? 'success' : ($claim->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($claim->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $claim->histories->count() }}</td>
                                            <td class="text-center">{{ $claim->notes->count() }}</td>
                                            <td>
                                                @if ($claim->histories->isNotEmpty())
                                                    {{ $claim->histories->last()->created_at->format('M d, H:i') }}
                                                @else
                                                    <span class="text-muted">No activity</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('claims.audit.trail', $claim->id) }}"
                                                        class="btn btn-outline-primary" title="View Audit Trail">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                    <a href="{{ route('claims.show', $claim->id) }}"
                                                        class="btn btn-outline-info" title="View Claim">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-gray-500 {
            color: #858796 !important;
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
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Action Breakdown Chart
            const actionCtx = document.getElementById('actionBreakdownChart').getContext('2d');
            new Chart(actionCtx, {
                type: 'doughnut',
                data: {
                    labels: @json(
                        $actionBreakdown->pluck('action')->map(function ($item) {
                            return ucfirst(str_replace('_', ' ', $item));
                        })),
                    datasets: [{
                        data: @json($actionBreakdown->pluck('count')),
                        backgroundColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b',
                            '#858796',
                            '#5a5c69',
                            '#2e59d9'
                        ],
                        hoverBackgroundColor: [
                            '#2e59d9',
                            '#17a673',
                            '#2c9faf',
                            '#f4b619',
                            '#e02d1b',
                            '#6c757d',
                            '#3a3b45',
                            '#2653d4'
                        ],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endpush
