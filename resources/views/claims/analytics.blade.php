@extends('layouts.app')

@section('title', 'Claims Analytics & Reports')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle"><a href="{{ route('claims.index') }}" class="text-muted">Claims</a></div>
                    <h2 class="page-title">Claims Analytics & Reports</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary">
                            <i class="ti-arrow-left me-1"></i> Back to Claims
                        </a>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="ti-printer me-1"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <form method="GET" action="{{ route('claims.analytics') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="paid" {{ $statusFilter == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="submitted" {{ $statusFilter == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="verified" {{ $statusFilter == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="approved" {{ $statusFilter == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="es_approved" {{ $statusFilter == 'es_approved' ? 'selected' : '' }}>ES Approved</option>
                                    <option value="rejected" {{ $statusFilter == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Program</label>
                                <select name="program_id" class="form-select">
                                    <option value="">All Programs</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ (isset($programId) && $programId == $program->id) ? 'selected' : '' }}>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('claims.analytics') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x text-dark"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #4e73df !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-primary">Total Claims</div>
                            </div>
                            <div class="h1 mb-1">{{ number_format($overallStats['total_claims']) }}</div>
                            <div class="text-muted small">₦{{ number_format($overallStats['total_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #f6c23e !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-warning">Pending / Submitted</div>
                            </div>
                            <div class="h1 mb-1">{{ number_format($overallStats['pending']) }}</div>
                            <div class="text-muted small">Verified: {{ number_format($overallStats['verified']) }} | Approved: {{ number_format($overallStats['approved']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #1cc88a !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-success">Paid Claims</div>
                            </div>
                            <div class="h1 mb-1">{{ number_format($overallStats['paid']) }}</div>
                            <div class="text-muted small">₦{{ number_format($overallStats['paid_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #e74a3b !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader text-danger">Rejected</div>
                            </div>
                            <div class="h1 mb-1">{{ number_format($overallStats['rejected']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claims Per Facility Breakdown -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti-building-hospital me-2"></i>
                        Claims Per Facility
                        @if($statusFilter)
                            <span class="badge bg-blue-lt ms-2">{{ ucfirst(str_replace('_', ' ', $statusFilter)) }}</span>
                        @endif
                    </h3>
                    <div class="card-actions">
                        <span class="text-muted">{{ $facilityBreakdown->count() }} facilities</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="facilityTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 fw-semibold" style="width: 40px;">#</th>
                                    <th class="border-0 fw-semibold">Facility</th>
                                    <th class="border-0 fw-semibold text-center">Claims</th>
                                    <th class="border-0 fw-semibold text-end">Admin Charges</th>
                                    <th class="border-0 fw-semibold text-end">Pharmacy</th>
                                    <th class="border-0 fw-semibold text-end">Laboratory</th>
                                    <th class="border-0 fw-semibold text-end">Services</th>
                                    <th class="border-0 fw-semibold text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($facilityBreakdown as $index => $row)
                                    <tr>
                                        <td class="align-middle text-muted">{{ $index + 1 }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <a href="{{ route('claims.facility.show', $row->facility_id) }}" class="fw-semibold text-reset">
                                                        {{ $row->facility_name }}
                                                    </a>
                                                </div>
                                                <div class="d-print-none">
                                                    <a href="{{ route('claims.analytics.facility-report') }}?facility_id={{ $row->facility_id }}&program_id={{ $programId ?? '' }}&date_from={{ $dateFrom }}&date_to={{ $dateTo }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti-printer me-1"></i> Report
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge bg-blue-lt">{{ number_format($row->claim_count) }}</span>
                                        </td>
                                        <td class="align-middle text-end">₦{{ number_format($row->admin_charges, 2) }}</td>
                                        <td class="align-middle text-end">₦{{ number_format($row->pharmacy, 2) }}</td>
                                        <td class="align-middle text-end">₦{{ number_format($row->laboratory, 2) }}</td>
                                        <td class="align-middle text-end">₦{{ number_format($row->services, 2) }}</td>
                                        <td class="align-middle text-end fw-bold text-primary">₦{{ number_format($row->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No claims found for the selected criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($facilityBreakdown->count() > 0)
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td></td>
                                        <td>Grand Total</td>
                                        <td class="text-center">{{ number_format($grandTotals['claim_count']) }}</td>
                                        <td class="text-end">₦{{ number_format($grandTotals['admin_charges'], 2) }}</td>
                                        <td class="text-end">₦{{ number_format($grandTotals['pharmacy'], 2) }}</td>
                                        <td class="text-end">₦{{ number_format($grandTotals['laboratory'], 2) }}</td>
                                        <td class="text-end">₦{{ number_format($grandTotals['services'], 2) }}</td>
                                        <td class="text-end text-primary">₦{{ number_format($grandTotals['total_amount'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Charts & Type Breakdown -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti-chart-pie me-2"></i>Claims by Type</h3>
                        </div>
                        <div class="card-body">
                            @if($claimsByType->count() > 0)
                                <canvas id="claimsByTypeChart" height="250"></canvas>
                            @else
                                <div class="text-center text-muted py-4">No data available</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti-chart-bar me-2"></i>Amount Breakdown by Type</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 fw-semibold">Type</th>
                                            <th class="border-0 fw-semibold text-center">Claims</th>
                                            <th class="border-0 fw-semibold text-end">Total Amount</th>
                                            <th class="border-0 fw-semibold text-end">Avg per Claim</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($claimsByType as $type)
                                            <tr>
                                                <td><span class="badge bg-azure-lt">{{ ucfirst($type->claim_type) }}</span></td>
                                                <td class="text-center">{{ number_format($type->claim_count) }}</td>
                                                <td class="text-end fw-semibold">₦{{ number_format($type->total_amount, 2) }}</td>
                                                <td class="text-end text-muted">₦{{ number_format($type->claim_count > 0 ? $type->total_amount / $type->claim_count : 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trends -->
            @if($monthlyTrends->count() > 0)
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti-trending-up me-2"></i>Monthly Trends</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 fw-semibold">Month</th>
                                    <th class="border-0 fw-semibold text-center">Claims</th>
                                    <th class="border-0 fw-semibold text-end">Admin Charges</th>
                                    <th class="border-0 fw-semibold text-end">Pharmacy</th>
                                    <th class="border-0 fw-semibold text-end">Laboratory</th>
                                    <th class="border-0 fw-semibold text-end">Services</th>
                                    <th class="border-0 fw-semibold text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyTrends as $trend)
                                    <tr>
                                        <td class="fw-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->format('F Y') }}</td>
                                        <td class="text-center"><span class="badge bg-blue-lt">{{ number_format($trend->claim_count) }}</span></td>
                                        <td class="text-end">₦{{ number_format($trend->admin_charges, 2) }}</td>
                                        <td class="text-end">₦{{ number_format($trend->pharmacy, 2) }}</td>
                                        <td class="text-end">₦{{ number_format($trend->laboratory, 2) }}</td>
                                        <td class="text-end">₦{{ number_format($trend->services, 2) }}</td>
                                        <td class="text-end fw-bold text-primary">₦{{ number_format($trend->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($claimsByType->count() > 0)
            const ctx = document.getElementById('claimsByTypeChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: @json($claimsByType->pluck('claim_type')->map(fn($t) => ucfirst($t))),
                    datasets: [{
                        data: @json($claimsByType->pluck('total_amount')),
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ₦' + Number(context.raw).toLocaleString('en-NG', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
            @endif
        });
    </script>
@endpush
