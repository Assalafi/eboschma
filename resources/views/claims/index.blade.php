@extends('layouts.app')

@section('title', 'Claims Management - Boschma Administration')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title mb-0">Claims Management</h2>
                    <div class="text-muted mt-1">Review and approve facility claims</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti-download me-1"></i>Export
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('claims.export') }}" class="dropdown-item">
                                    <i class="ti-file me-2"></i>Export All Claims
                                </a>
                                <a href="#" class="dropdown-item" onclick="exportPendingClaims()">
                                    <i class="ti-clock me-2"></i>Export Pending RO Review
                                </a>
                                <a href="#" class="dropdown-item" onclick="exportApprovedClaims()">
                                    <i class="ti-check me-2"></i>Export E5 Approved
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('claims.bulk.upload') }}" class="btn btn-outline-primary">
                            <i class="ti-upload me-1"></i>Bulk Upload
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Claims Statistics Cards -->
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-md bg-warning-lt">
                                        <i class="ti-clock fs-2 text-warning"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-uppercase text-muted mb-1"
                                        style="font-size: 0.75rem; font-weight: 600;">RO Review</div>
                                    <div class="h2 mb-0 fw-bold">{{ $stats['ro_pending'] ?? 0 }}</div>
                                    <div class="text-muted small">Awaiting Regional Office</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-md bg-primary-lt">
                                        <i class="ti-shield-check fs-2 text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-uppercase text-muted mb-1"
                                        style="font-size: 0.75rem; font-weight: 600;">E5 Approval</div>
                                    <div class="h2 mb-0 fw-bold">{{ $stats['e5_pending'] ?? 0 }}</div>
                                    <div class="text-muted small">Awaiting E5 Review</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-md bg-success-lt">
                                        <i class="ti-check fs-2 text-success"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-uppercase text-muted mb-1"
                                        style="font-size: 0.75rem; font-weight: 600;">Approved</div>
                                    <div class="h2 mb-0 fw-bold">{{ $stats['approved'] ?? 0 }}</div>
                                    <div class="text-muted small">Ready for Payment</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm hover-lift" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-md bg-info-lt">
                                        <i class="ti-cash fs-2 text-info"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-uppercase text-muted mb-1"
                                        style="font-size: 0.75rem; font-weight: 600;">Paid Claims</div>
                                    <div class="h2 mb-0 fw-bold">{{ $stats['paid'] ?? 0 }}</div>
                                    <div class="text-muted small">Completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facility Claims Overview -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-primary text-white" style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="ti-building me-2"></i>Facility Claims Overview
                            </h5>
                            <p class="mb-0 small opacity-75">Claims submitted by facilities awaiting approval</p>
                        </div>
                        <div class="btn-list">
                            <button class="btn btn-light btn-sm" onclick="refreshClaimsData()">
                                <i class="ti-refresh me-1"></i>Refresh
                            </button>
                            <a href="{{ route('claims.analytics') }}" class="btn btn-outline-light btn-sm">
                                <i class="ti-chart-bar me-1"></i>Analytics
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 fw-semibold">Facility</th>
                                    <th class="border-0 fw-semibold">Total Claims</th>
                                    <th class="border-0 fw-semibold">RO Review</th>
                                    <th class="border-0 fw-semibold">E5 Approval</th>
                                    <th class="border-0 fw-semibold">Total Amount</th>
                                    <th class="border-0 fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($facilities ?? [] as $facility)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-blue-lt me-2">
                                                    <i class="ti-building fs-4 text-blue"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $facility->facility_name }}</div>
                                                    <div class="text-muted small">{{ $facility->total_claims ?? 0 }} claims
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="badge bg-primary me-2">{{ $facility->total_claims ?? 0 }}</div>
                                                <div class="small">
                                                    <div>Latest:
                                                        {{ $facility->latest_claim_date ? \Carbon\Carbon::parse($facility->latest_claim_date)->format('M j, Y') : 'N/A' }}
                                                    </div>
                                                    <div class="text-muted">
                                                        {{ $facility->latest_claim_date ? \Carbon\Carbon::parse($facility->latest_claim_date)->diffForHumans() : 'No claims' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                @if (($facility->ro_pending ?? 0) > 0)
                                                    <div class="badge bg-warning me-2">{{ $facility->ro_pending ?? 0 }}
                                                    </div>
                                                    <div class="small text-warning">Pending RO</div>
                                                @else
                                                    <div class="badge bg-success me-2">{{ $facility->ro_approved ?? 0 }}
                                                    </div>
                                                    <div class="small text-success">RO Approved</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                @if (($facility->e5_pending ?? 0) > 0)
                                                    <div class="badge bg-warning me-2">{{ $facility->e5_pending ?? 0 }}
                                                    </div>
                                                    <div class="small text-warning">Pending E5</div>
                                                @else
                                                    <div class="badge bg-success me-2">{{ $facility->e5_approved ?? 0 }}
                                                    </div>
                                                    <div class="small text-success">E5 Approved</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="fw-semibold text-primary">
                                                ₦{{ number_format($facility->total_value ?? 0, 2) }}</div>
                                            <div class="text-muted small">
                                                ₦{{ number_format($facility->approved_value ?? 0, 2) }} approved
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-list flex-nowrap">
                                                <a href="#" class="btn btn-sm btn-primary"
                                                    title="View Facility Claims"
                                                    onclick="viewFacilityClaims({{ $facility->facility_id }})">
                                                    <i class="ti-eye"></i>
                                                </a>
                                                @if (auth()->user()->can('review-claims'))
                                                    <a href="#" class="btn btn-sm btn-success"
                                                        title="Review Claims"
                                                        onclick="reviewFacilityClaims({{ $facility->facility_id }})">
                                                        <i class="ti-clipboard-check"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="empty">
                                                <div class="empty-img">
                                                    <i class="ti-file-off fs-1 text-muted"></i>
                                                </div>
                                                <p class="empty-title">No facilities found</p>
                                                <p class="empty-subtitle text-muted">
                                                    No facilities with claims data available.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Claims -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti-file-text me-2"></i>Recent Claims
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 fw-semibold">Claim #</th>
                                            <th class="border-0 fw-semibold">Beneficiary</th>
                                            <th class="border-0 fw-semibold">Facility</th>
                                            <th class="border-0 fw-semibold">Service Date</th>
                                            <th class="border-0 fw-semibold">Amount</th>
                                            <th class="border-0 fw-semibold">Status</th>
                                            <th class="border-0 fw-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentClaims ?? [] as $claim)
                                            <tr>
                                                <td class="align-middle">
                                                    <div class="fw-semibold">
                                                        {{ $claim->authorization_code ?? 'CLM-' . $claim->id }}</div>
                                                    <div class="text-muted small">
                                                        {{ \Carbon\Carbon::parse($claim->created_at)->format('M j, Y') }}
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-blue-lt me-2">
                                                            <i class="ti-user fs-4 text-blue"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $claim->beneficiary_name }}</div>
                                                            <div class="text-muted small">
                                                                {{ $claim->boschma_id ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-semibold">{{ $claim->healthcare_provider }}</div>
                                                    <div class="text-muted small">{{ $claim->claim_type ?? 'Outpatient' }}
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-semibold">
                                                        {{ \Carbon\Carbon::parse($claim->service_date)->format('M j, Y') }}
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-semibold text-primary">
                                                        ₦{{ number_format($claim->claim_amount, 2) }}</div>
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
                                                        <span
                                                            class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <div class="btn-list flex-nowrap">
                                                        <a href="{{ route('claims.facility-claim.show', $claim->id) }}"
                                                            class="btn btn-sm btn-primary" title="View Claim">
                                                            <i class="ti-eye"></i>
                                                        </a>
                                                        @if (auth()->user()->can('review-claims') && $claim->status === 'submitted' && empty($claim->ro_status))
                                                            <a href="#" class="btn btn-sm btn-warning"
                                                                title="Review"
                                                                onclick="reviewClaim({{ $claim->id }})">
                                                                <i class="ti-clipboard-check"></i>
                                                            </a>
                                                        @endif
                                                        @if (auth()->user()->can('approve-claims') && $claim->ro_status === 'approved' && empty($claim->e5_status))
                                                            <a href="#" class="btn btn-sm btn-success"
                                                                title="E5 Approve"
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
                                                            No claims have been submitted yet.
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if (isset($recentClaims) && $recentClaims->hasPages())
                                <div class="card-footer">
                                    {{ $recentClaims->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Pending Reviews -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if (auth()->user()->can('review-claims'))
                                    <a href="{{ route('claims.ro-review') }}" class="btn btn-outline-warning">
                                        <i class="ti-clipboard-check me-2"></i>RO Review Queue
                                        @if (($stats['ro_pending'] ?? 0) > 0)
                                            <span class="badge bg-warning">{{ $stats['ro_pending'] }}</span>
                                        @endif
                                    </a>
                                @endif
                                @if (auth()->user()->can('approve-claims'))
                                    <a href="{{ route('claims.e5-review') }}" class="btn btn-outline-primary">
                                        <i class="ti-shield-check me-2"></i>E5 Approval Queue
                                        @if (($stats['e5_pending'] ?? 0) > 0)
                                            <span class="badge bg-primary">{{ $stats['e5_pending'] }}</span>
                                        @endif
                                    </a>
                                @endif
                                <a href="{{ route('claims.audit.report') }}" class="btn btn-outline-secondary">
                                    <i class="ti-file-text me-2"></i>Audit Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti-bell me-2"></i>Recent Activity
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @forelse ($recentActivity ?? [] as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-point timeline-point-{{ $activity['type'] ?? 'primary' }}">
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-time">{{ $activity['time'] }}</div>
                                            <div class="timeline-title">{{ $activity['title'] }}</div>
                                            <div class="timeline-body text-muted">{{ $activity['description'] }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-3">
                                        <div class="text-muted">No recent activity</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Claims Modal -->
    <div class="modal fade" id="reviewClaimsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Review Facility Claims</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="reviewClaimsContent">
                        <!-- Claims will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="approveSelectedClaims()">
                        <i class="ti-check me-1"></i>Approve Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-point {
            position: absolute;
            left: -30px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .timeline-point-primary {
            background-color: #206bc4;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            border-left: 3px solid #206bc4;
        }

        .timeline-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .timeline-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .timeline-body {
            font-size: 0.875rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function refreshClaimsData() {
            location.reload();
        }

        function exportPendingClaims() {
            window.location.href = '{{ route('claims.export') }}?status=ro_pending';
        }

        function exportApprovedClaims() {
            window.location.href = '{{ route('claims.export') }}?status=e5_approved';
        }

        function viewFacilityClaims(facilityId) {
            // Redirect to facility claims page
            window.location.href = `/claims/facility/${facilityId}`;
        }

        function reviewFacilityClaims(facilityId) {
            const modal = new bootstrap.Modal(document.getElementById('reviewClaimsModal'));
            const content = document.getElementById('reviewClaimsContent');

            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading facility claims for review...</div>
                </div>
            `;

            modal.show();

            // Load facility claims for review
            fetch(`/api/facilities/${facilityId}/claims/review`)
                .then(response => response.json())
                .then(data => {
                    let claimsHtml = '';
                    data.claims.forEach(claim => {
                        claimsHtml += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input claim-checkbox" type="checkbox" 
                                                       value="${claim.id}" id="claim_${claim.id}">
                                                <label class="form-check-label" for="claim_${claim.id}">
                                                    <strong>${claim.claim_number}</strong>
                                                    <br><small class="text-muted">${claim.patient_name}</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="fw-semibold">₦${parseFloat(claim.total_amount).toLocaleString()}</div>
                                            <div class="small text-muted">${claim.claim_type}</div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-warning">RO Review</span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Submitted: ${new Date(claim.created_at).toLocaleDateString()}</small>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewClaimDetails(${claim.id})">
                                                <i class="ti-eye"></i> View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    content.innerHTML = `
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>${data.facility_name} - Claims for Review</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllClaims">
                                    <label class="form-check-label" for="selectAllClaims">
                                        Select All
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="claims-list">
                            ${claimsHtml}
                        </div>
                    `;

                    // Add select all functionality
                    document.getElementById('selectAllClaims').addEventListener('change', function() {
                        document.querySelectorAll('.claim-checkbox').forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="ti-alert-triangle me-2"></i>
                            Error loading facility claims. Please try again.
                        </div>
                    `;
                });
        }

        function viewClaimDetails(claimId) {
            window.open(`/claims/${claimId}`, '_blank');
        }

        function approveSelectedClaims() {
            const selectedClaims = Array.from(document.querySelectorAll('.claim-checkbox:checked'))
                .map(checkbox => checkbox.value);

            if (selectedClaims.length === 0) {
                alert('Please select at least one claim to approve.');
                return;
            }

            if (confirm(`Are you sure you want to approve ${selectedClaims.length} claim(s)?`)) {
                fetch('/claims/bulk-approve', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            claim_ids: selectedClaims
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`${data.approved_count} claim(s) approved successfully.`);
                            location.reload();
                        } else {
                            alert('Error approving claims. Please try again.');
                        }
                    })
                    .catch(error => {
                        alert('Error approving claims. Please try again.');
                    });
            }
        }

        function reviewClaim(claimId) {
            window.location.href = `/claims/${claimId}#review`;
        }

        function e5ApproveClaim(claimId) {
            window.location.href = `/claims/${claimId}#e5-approve`;
        }
    </script>
@endpush
