@extends('layouts.app')

@section('title', 'Referral Analytics')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Referral System
                    </div>
                    <h2 class="page-title">
                        Referral Analytics & Reports
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('referrals.index') }}" class="btn">
                            <i class="ti ti-arrow-left me-2"></i>
                            Back to Referrals
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-download me-2"></i>
                                Export Report
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('referrals.analytics.export') }}?format=excel">
                                    <i class="ti ti-file-spreadsheet me-2"></i>Export to Excel
                                </a>
                                <a class="dropdown-item" href="{{ route('referrals.analytics.export') }}?format=pdf">
                                    <i class="ti ti-file-text me-2"></i>Export to PDF
                                </a>
                                <a class="dropdown-item" href="#" onclick="window.print()">
                                    <i class="ti ti-printer me-2"></i>Print Report
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
            <!-- Date Range Filter -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('referrals.analytics') }}"
                                class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <select name="date_range" class="form-select">
                                        <option value="7" {{ request('date_range') == '7' ? 'selected' : '' }}>Last 7
                                            days</option>
                                        <option value="30" {{ request('date_range') == '30' ? 'selected' : '' }}>Last 30
                                            days</option>
                                        <option value="90" {{ request('date_range') == '90' ? 'selected' : '' }}>Last 3
                                            months</option>
                                        <option value="365" {{ request('date_range') == '365' ? 'selected' : '' }}>Last
                                            year</option>
                                        <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>
                                            Custom range</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control"
                                        value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-refresh me-2"></i>
                                        Update Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-muted">Total Referrals</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown">
                                            {{ request('date_range', '30') }} days
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ number_format($analytics['total_referrals'] ?? 0) }}</div>
                            <div class="d-flex align-items-center">
                                <div class="me-auto">
                                    <span class="text-green">
                                        <i class="ti ti-arrow-up"></i>
                                        {{ $analytics['referrals_growth'] ?? '0%' }}
                                    </span>
                                    <span class="text-muted ms-2">vs previous period</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-muted">Conversion Rate</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown">
                                            Success rate
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ $analytics['conversion_rate'] ?? '0%' }}</div>
                            <div class="d-flex align-items-center">
                                <div class="me-auto">
                                    <span class="text-blue">
                                        <i class="ti ti-percentage"></i>
                                        {{ $analytics['conversion_trend'] ?? 'Stable' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-muted">Total Commission</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown">
                                            Paid + Pending
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">${{ number_format($analytics['total_commission'] ?? 0, 2) }}</div>
                            <div class="d-flex align-items-center">
                                <div class="me-auto">
                                    <span class="text-green">
                                        <i class="ti ti-cash"></i>
                                        ${{ number_format($analytics['paid_commission'] ?? 0, 2) }} paid
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-muted">Active Referrers</div>
                                <div class="ms-auto lh-1">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown">
                                            This period
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-3">{{ number_format($analytics['active_referrers'] ?? 0) }}</div>
                            <div class="d-flex align-items-center">
                                <div class="me-auto">
                                    <span class="text-blue">
                                        <i class="ti ti-users"></i>
                                        {{ $analytics['avg_referrals_per_referrer'] ?? 0 }} avg per referrer
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Referral Trends</h3>
                            <div class="card-actions">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active"
                                        onclick="updateChart('daily')">Daily</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="updateChart('weekly')">Weekly</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="updateChart('monthly')">Monthly</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="referralTrendsChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Service Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="serviceDistributionChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Breakdown & Top Referrers -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Status Breakdown</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-center">Count</th>
                                            <th class="text-center">Percentage</th>
                                            <th class="w-50">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($analytics['status_breakdown'] ?? [] as $status => $data)
                                            <tr>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-warning',
                                                            'approved' => 'bg-success',
                                                            'rejected' => 'bg-danger',
                                                            'completed' => 'bg-info',
                                                        ];
                                                        $statusColor = $statusColors[$status] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $statusColor }}">{{ ucfirst($status) }}</span>
                                                </td>
                                                <td class="text-center">{{ $data['count'] ?? 0 }}</td>
                                                <td class="text-center">{{ $data['percentage'] ?? 0 }}%</td>
                                                <td>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar {{ $statusColor }}"
                                                            style="width: {{ $data['percentage'] ?? 0 }}%"></div>
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
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Referrers</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Referrer</th>
                                            <th class="text-center">Referrals</th>
                                            <th class="text-center">Commission</th>
                                            <th class="text-center">Success Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($analytics['top_referrers'] ?? [] as $referrer)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                                style="width: 28px; height: 28px; font-size: 12px;">
                                                                {{ strtoupper(substr($referrer['name'] ?? 'UN', 0, 1)) }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $referrer['name'] ?? 'Unknown' }}
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ $referrer['email'] ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $referrer['referral_count'] ?? 0 }}</td>
                                                <td class="text-center">
                                                    ${{ number_format($referrer['commission'] ?? 0, 2) }}</td>
                                                <td class="text-center">{{ $referrer['success_rate'] ?? 0 }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detailed Referral Analytics</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="ti ti-printer me-1"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Service Type</th>
                                            <th class="text-center">Total Referrals</th>
                                            <th class="text-center">Approved</th>
                                            <th class="text-center">Rejected</th>
                                            <th class="text-center">Completed</th>
                                            <th class="text-center">Success Rate</th>
                                            <th class="text-center">Avg Commission</th>
                                            <th class="text-center">Total Commission</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($analytics['service_analytics'] ?? [] as $service => $data)
                                            <tr>
                                                <td>
                                                    <span
                                                        class="badge bg-blue-lt text-blue">{{ ucfirst($service) }}</span>
                                                </td>
                                                <td class="text-center">{{ $data['total'] ?? 0 }}</td>
                                                <td class="text-center">{{ $data['approved'] ?? 0 }}</td>
                                                <td class="text-center">{{ $data['rejected'] ?? 0 }}</td>
                                                <td class="text-center">{{ $data['completed'] ?? 0 }}</td>
                                                <td class="text-center">{{ $data['success_rate'] ?? 0 }}%</td>
                                                <td class="text-center">
                                                    ${{ number_format($data['avg_commission'] ?? 0, 2) }}</td>
                                                <td class="text-center">
                                                    <span class="fw-semibold text-success">
                                                        ${{ number_format($data['total_commission'] ?? 0, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active fw-bold">
                                            <td>Total</td>
                                            <td class="text-center">{{ $analytics['totals']['total_referrals'] ?? 0 }}
                                            </td>
                                            <td class="text-center">{{ $analytics['totals']['total_approved'] ?? 0 }}</td>
                                            <td class="text-center">{{ $analytics['totals']['total_rejected'] ?? 0 }}</td>
                                            <td class="text-center">{{ $analytics['totals']['total_completed'] ?? 0 }}
                                            </td>
                                            <td class="text-center">
                                                {{ $analytics['totals']['overall_success_rate'] ?? 0 }}%</td>
                                            <td class="text-center">
                                                ${{ number_format($analytics['totals']['overall_avg_commission'] ?? 0, 2) }}
                                            </td>
                                            <td class="text-center text-success">
                                                ${{ number_format($analytics['totals']['grand_total_commission'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Referral Trends Chart
        const trendsCtx = document.getElementById('referralTrendsChart').getContext('2d');
        const referralTrendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: @json($analytics['chart_labels'] ?? []),
                datasets: [{
                    label: 'Referrals',
                    data: @json($analytics['referral_trends'] ?? []),
                    borderColor: '#01542B',
                    backgroundColor: 'rgba(1, 84, 43, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Approved',
                    data: @json($analytics['approval_trends'] ?? []),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Service Distribution Chart
        const serviceCtx = document.getElementById('serviceDistributionChart').getContext('2d');
        const serviceDistributionChart = new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($analytics['service_breakdown'] ?? [])),
                datasets: [{
                    data: @json(array_values($analytics['service_breakdown'] ?? [])),
                    backgroundColor: [
                        '#01542B',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8',
                        '#6f42c1'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        function updateChart(period) {
            // Implementation for updating chart based on period
            console.log('Updating chart for period:', period);
        }
    </script>
@endsection
