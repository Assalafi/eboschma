@extends('layouts.app')

@section('title', 'View Claim - ' . $claim->claim_number)

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="page-leftheader">
                <h4 class="page-title">Claim Details - Boschma Review</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('claims.index') }}"><i class="ti-home mr-1"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('claims.index') }}">Claims</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $claim->claim_number }}</li>
                </ol>
            </div>
            <div class="page-rightheader">
                <div class="btn-list">
                    @if (auth()->user()->can('review-claims') && $claim->status === 'submitted')
                        <button type="button" class="btn btn-warning" onclick="openReviewModal()">
                            <i class="ti-clipboard-check mr-1"></i> Review Claim
                        </button>
                    @endif
                    @if (auth()->user()->can('approve-claims') && $claim->status === 'ro_approved')
                        <button type="button" class="btn btn-primary" onclick="openApprovalModal()">
                            <i class="ti-shield-check mr-1"></i> E5 Approval
                        </button>
                    @endif
                    <a href="{{ route('claims.print', $claim->id) }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="ti-printer mr-1"></i>Print
                    </a>
                    <a href="{{ route('claims.index') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="ti-check mr-2"></i>Success!</strong>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Approval Status Banner -->
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar avatar-md bg-{{ $claim->status_color ?? 'primary' }}-lt">
                                        <i
                                            class="ti-{{ $claim->status_icon ?? 'file' }} fs-2 text-{{ $claim->status_color ?? 'primary' }}"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $claim->claim_number }}</h5>
                                    <div class="text-muted">{{ $claim->facility_name ?? 'Unknown Facility' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="mb-2">{!! $claim->status_badge !!}</div>
                            @if ($claim->ro_status)
                                <div class="small text-muted">RO Status: {{ $claim->ro_status }}</div>
                            @endif
                            @if ($claim->e5_status)
                                <div class="small text-muted">E5 Status: {{ $claim->e5_status }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs" id="claimTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="patient-tab" data-bs-toggle="tab"
                                        data-bs-target="#patient" type="button" role="tab" aria-controls="patient"
                                        aria-selected="true">
                                        <i class="ti-user me-2"></i>Patient Info
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="non-priced-tab" data-bs-toggle="tab"
                                        data-bs-target="#non-priced" type="button" role="tab"
                                        aria-controls="non-priced" aria-selected="false">
                                        <i class="ti-file-text me-2"></i>Non-Priced Activities
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="priced-tab" data-bs-toggle="tab" data-bs-target="#priced"
                                        type="button" role="tab" aria-controls="priced" aria-selected="false">
                                        <i class="ti-cash me-2"></i>Priced Activities
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="claimTabsContent">
                                <!-- Patient Info Tab -->
                                <div class="tab-pane fade show active" id="patient" role="tabpanel"
                                    aria-labelledby="patient-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="mb-3">Patient Information</h5>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Patient Name</label>
                                                <div class="fw-semibold">{{ $claim->patient_name }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">BOSCHMA ID</label>
                                                <div class="fw-semibold">{{ $claim->boschma_no }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">NIN</label>
                                                <div class="fw-semibold">{{ $claim->nin }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Phone Number</label>
                                                <div class="fw-semibold">{{ $claim->phone_number }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Gender</label>
                                                <div class="fw-semibold">{{ $claim->gender }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Date of Birth</label>
                                                <div class="fw-semibold">
                                                    {{ $claim->date_of_birth ? $claim->date_of_birth->format('Y-m-d') : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-3">Claim Information</h5>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Claim Number</label>
                                                <div class="fw-semibold">{{ $claim->claim_number }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Claim Type</label>
                                                <div class="fw-semibold">{{ ucfirst($claim->claim_type) }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Service Date</label>
                                                <div class="fw-semibold">
                                                    {{ $claim->service_date ? $claim->service_date->format('Y-m-d') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Facility</label>
                                                <div class="fw-semibold">{{ $claim->facility_name ?? 'N/A' }}</div>
                                            </div>
                                            @if ($claim->admission_date)
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small">Admission Date</label>
                                                    <div class="fw-semibold">{{ $claim->admission_date->format('Y-m-d') }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($claim->discharge_date)
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small">Discharge Date</label>
                                                    <div class="fw-semibold">{{ $claim->discharge_date->format('Y-m-d') }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Non-Priced Activities Tab -->
                                <div class="tab-pane fade" id="non-priced" role="tabpanel"
                                    aria-labelledby="non-priced-tab">
                                    <!-- Consultations -->
                                    @if ($claim->consultations && $claim->consultations->count() > 0)
                                        <div class="mb-4">
                                            <h5 class="mb-3">
                                                <i class="ti-user-check me-2"></i>Consultations
                                            </h5>
                                            <div class="table-responsive">
                                                <table class="table table-vcenter table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Doctor</th>
                                                            <th>Department</th>
                                                            <th>Diagnosis</th>
                                                            <th>Treatment</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->consultations as $consultation)
                                                            <tr>
                                                                <td>{{ $consultation->consultation_date ? $consultation->consultation_date->format('Y-m-d') : 'N/A' }}
                                                                </td>
                                                                <td>{{ $consultation->doctor_name ?? 'N/A' }}</td>
                                                                <td>{{ $consultation->department ?? 'N/A' }}</td>
                                                                <td>
                                                                    @if ($consultation->diagnoses && $consultation->diagnoses->count() > 0)
                                                                        @foreach ($consultation->diagnoses as $diagnosis)
                                                                            {{ $diagnosis->description }}<br>
                                                                        @endforeach
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td>{{ $consultation->treatment ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Laboratory Tests -->
                                    @if ($claim->laboratoryTests && $claim->laboratoryTests->count() > 0)
                                        <div class="mb-4">
                                            <h5 class="mb-3">
                                                <i class="ti-test-tube me-2"></i>Laboratory Tests
                                            </h5>
                                            <div class="table-responsive">
                                                <table class="table table-vcenter table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Test Name</th>
                                                            <th>Result</th>
                                                            <th>Normal Range</th>
                                                            <th>Test Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->laboratoryTests as $test)
                                                            <tr>
                                                                <td>{{ $test->test_name }}</td>
                                                                <td>{{ $test->result }}</td>
                                                                <td>{{ $test->normal_range ?? 'N/A' }}</td>
                                                                <td>{{ $test->test_date ? $test->test_date->format('Y-m-d') : 'N/A' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Activities -->
                                    @if ($claim->activities && $claim->activities->count() > 0)
                                        <div class="mb-4">
                                            <h5 class="mb-3">
                                                <i class="ti-activity me-2"></i>Clinical Activities
                                            </h5>
                                            <div class="table-responsive">
                                                <table class="table table-vcenter table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Activity</th>
                                                            <th>Description</th>
                                                            <th>Date</th>
                                                            <th>Notes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->activities as $activity)
                                                            <tr>
                                                                <td>{{ $activity->activity_name }}</td>
                                                                <td>{{ $activity->description }}</td>
                                                                <td>{{ $activity->activity_date ? $activity->activity_date->format('Y-m-d') : 'N/A' }}
                                                                </td>
                                                                <td>{{ $activity->notes ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if (!$claim->consultations && !$claim->laboratoryTests && !$claim->activities)
                                        <div class="alert alert-info">
                                            <i class="ti-info-circle me-2"></i>
                                            No non-priced activities recorded for this claim.
                                        </div>
                                    @endif
                                </div>

                                <!-- Priced Activities Tab -->
                                <div class="tab-pane fade" id="priced" role="tabpanel" aria-labelledby="priced-tab">
                                    <!-- Medications -->
                                    @if ($claim->medications && $claim->medications->count() > 0)
                                        <div class="mb-4">
                                            <h5 class="mb-3">
                                                <i class="ti-pills me-2"></i>Medications
                                            </h5>
                                            <div class="table-responsive">
                                                <table class="table table-vcenter table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Medication</th>
                                                            <th>Dosage</th>
                                                            <th>Quantity</th>
                                                            <th>Unit Price</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->medications as $medication)
                                                            <tr>
                                                                <td>{{ $medication->medication_name }}</td>
                                                                <td>{{ $medication->dosage }}</td>
                                                                <td>{{ $medication->quantity }}</td>
                                                                <td>₦{{ number_format($medication->unit_price, 2) }}</td>
                                                                <td>₦{{ number_format($medication->total_cost, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="4" class="text-end">Pharmacy Total:</th>
                                                            <th>₦{{ number_format($claim->pharmacy_amount, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Services -->
                                    @if ($claim->renderedServices && $claim->renderedServices->count() > 0)
                                        <div class="mb-4">
                                            <h5 class="mb-3">
                                                <i class="ti-device-desktop me-2"></i>Services
                                            </h5>
                                            <div class="table-responsive">
                                                <table class="table table-vcenter table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Service</th>
                                                            <th>Description</th>
                                                            <th>Quantity</th>
                                                            <th>Unit Price</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->renderedServices as $service)
                                                            <tr>
                                                                <td>{{ $service->service_name }}</td>
                                                                <td>{{ $service->description }}</td>
                                                                <td>{{ $service->quantity }}</td>
                                                                <td>₦{{ number_format($service->unit_price, 2) }}</td>
                                                                <td>₦{{ number_format($service->total_cost, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="4" class="text-end">Services Total:</th>
                                                            <th>₦{{ number_format($claim->services_amount, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Total Summary -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body">
                                                    <h3 class="text-center mb-0">
                                                        <strong>TOTAL CLAIM AMOUNT:
                                                            ₦{{ number_format($claim->total_amount, 2) }}</strong>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval History -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti-history me-2"></i>Approval History
                    </h5>
                </div>
                <div class="card-body">
                    @if ($claim->history && $claim->history->count() > 0)
                        <div class="timeline">
                            @foreach ($claim->history as $history)
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-{{ $history->action_color ?? 'primary' }}">
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">{{ $history->created_at->format('M j, Y H:i') }}</div>
                                        <div class="timeline-title">{{ $history->action }}</div>
                                        <div class="timeline-body text-muted">
                                            By: {{ $history->user->name ?? 'System' }}<br>
                                            @if ($history->notes)
                                                Notes: {{ $history->notes }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="ti-info-circle me-2"></i>
                            No approval history available.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="reviewForm" method="POST" action="{{ route('claims.review', $claim->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Review Claim - {{ $claim->claim_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Review Action</label>
                            <select name="action" class="form-select" required>
                                <option value="">Select Action</option>
                                <option value="approve">Approve</option>
                                <option value="reject">Reject</option>
                                <option value="request_info">Request Additional Information</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Review Notes</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Enter review notes..."></textarea>
                        </div>
                        <div class="mb-3" id="rejectionReason" style="display: none;">
                            <label class="form-label">Rejection Reason</label>
                            <select name="rejection_reason" class="form-select">
                                <option value="">Select Reason</option>
                                <option value="incomplete_documentation">Incomplete Documentation</option>
                                <option value="not_covered">Not Covered Under Policy</option>
                                <option value="duplicate_claim">Duplicate Claim</option>
                                <option value="exceeded_limit">Exceeded Coverage Limit</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- E5 Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="approvalForm" method="POST" action="{{ route('claims.e5-approve', $claim->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">E5 Approval - {{ $claim->claim_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti-info-circle me-2"></i>
                            This claim has been approved by the Regional Office. Your E5 approval will finalize it for
                            payment processing.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Approval Action</label>
                            <select name="action" class="form-select" required>
                                <option value="">Select Action</option>
                                <option value="approve">Approve for Payment</option>
                                <option value="reject">Reject Claim</option>
                                <option value="return_to_ro">Return to Regional Office</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Approval Notes</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Enter approval notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Fix tab colors */
        .nav-tabs .nav-link {
            color: #495057 !important;
        }

        .nav-tabs .nav-link:hover {
            background-color: #e9ecef;
            color: #495057 !important;
        }

        .nav-tabs .nav-link.active {
            color: #495057 !important;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        .nav-tabs .nav-link.active:hover {
            background-color: #fff;
            color: #495057 !important;
        }

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

        .timeline-point-success {
            background-color: #2fb344;
        }

        .timeline-point-warning {
            background-color: #f59e0b;
        }

        .timeline-point-danger {
            background-color: #d63939;
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
        function openReviewModal() {
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }

        function openApprovalModal() {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            modal.show();
        }

        // Handle review action change
        document.querySelector('select[name="action"]')?.addEventListener('change', function() {
            const rejectionReason = document.getElementById('rejectionReason');
            if (this.value === 'reject') {
                rejectionReason.style.display = 'block';
            } else {
                rejectionReason.style.display = 'none';
            }
        });

        // Handle form submissions
        document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Review submitted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error submitting review. Please try again.');
                });
        });

        document.getElementById('approvalForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Claim approved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error approving claim. Please try again.');
                });
        });
    </script>
@endpush
