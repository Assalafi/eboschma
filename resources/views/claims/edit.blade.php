@extends('layouts.app')

@section('title', 'Edit Claim - ' . $claim->authorization_code)

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('claims.index') }}">Claims</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('claims.show', $claim->id) }}">Claim Details</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Claim</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <h2 class="page-title mb-0">
                                <i class="ti-edit me-2"></i>Edit Claim
                            </h2>
                            <p class="text-muted mb-0">Authorization Code: {{ $claim->authorization_code }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('claims.show', $claim->id) }}" class="btn btn-outline-secondary">
                            <i class="ti-arrow-left me-1"></i>Back to Claim
                        </a>
                        <a href="{{ route('claims.print', $claim->id) }}" class="btn btn-outline-danger" target="_blank">
                            <i class="ti-printer me-1"></i>Print
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Claim Status & Edit Restrictions -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    @switch($claim->status)
                                        @case('pending')
                                            <div class="avatar avatar-lg bg-warning-lt">
                                                <i class="ti-clock fs-2 text-warning"></i>
                                            </div>
                                        @break

                                        @case('approved')
                                            <div class="avatar avatar-lg bg-success-lt">
                                                <i class="ti-check fs-2 text-success"></i>
                                            </div>
                                        @break

                                        @case('rejected')
                                            <div class="avatar avatar-lg bg-danger-lt">
                                                <i class="ti-x fs-2 text-danger"></i>
                                            </div>
                                        @break

                                        @case('paid')
                                            <div class="avatar avatar-lg bg-info-lt">
                                                <i class="ti-cash fs-2 text-info"></i>
                                            </div>
                                        @break

                                        @default
                                            <div class="avatar avatar-lg bg-secondary-lt">
                                                <i class="ti-help fs-2 text-secondary"></i>
                                            </div>
                                    @endswitch
                                </div>
                                <div>
                                    <h3 class="mb-1">{{ ucfirst($claim->status) }}</h3>
                                    <p class="text-muted mb-0">
                                        Last updated: {{ $claim->updated_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>

                            @if ($claim->status !== 'pending')
                                <div class="alert alert-warning">
                                    <i class="ti-alert-triangle me-2"></i>
                                    <strong>Edit Restrictions:</strong> This claim has already been processed and can only
                                    be edited by administrators.
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="ti-info-alt me-2"></i>
                                    This claim is pending and can be edited. Changes will be tracked in the claim history.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h4 mb-1">₦{{ number_format($claim->claim_amount, 2) }}</div>
                            <div class="text-muted">Current Claim Amount</div>
                            <div class="mt-2">
                                <small class="text-muted">Created: {{ $claim->created_at->format('M j, Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claim Edit Form -->
            <form id="claimEditForm" method="POST" action="{{ route('claims.update', $claim->id) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Beneficiary Information -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-primary text-white" style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <h5 class="card-title mb-0">
                            <i class="ti-user me-2"></i>Beneficiary Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Beneficiary Name</label>
                                <input type="text" class="form-control" name="beneficiary_name"
                                    value="{{ old('beneficiary_name', $claim->beneficiary_name) }}"
                                    @if ($claim->status !== 'pending') readonly @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">BOSCHMA ID</label>
                                <input type="text" class="form-control" name="boschma_id"
                                    value="{{ old('boschma_id', $claim->boschma_id) }}"
                                    @if ($claim->status !== 'pending') readonly @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIN</label>
                                <input type="text" class="form-control" name="nin"
                                    value="{{ old('nin', $claim->nin) }}" maxlength="11"
                                    @if ($claim->status !== 'pending') readonly @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control" name="phone_number"
                                    value="{{ old('phone_number', $claim->phone_number) }}"
                                    @if ($claim->status !== 'pending') readonly @endif>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Claim Details -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-secondary text-white" style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <h5 class="card-title mb-0">
                            <i class="ti-file-text me-2"></i>Claim Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Authorization Code</label>
                                <input type="text" class="form-control" name="authorization_code"
                                    value="{{ old('authorization_code', $claim->authorization_code) }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Claim Type</label>
                                <select class="form-select" name="claim_type"
                                    @if ($claim->status !== 'pending') disabled @endif>
                                    <option value="medical"
                                        {{ old('claim_type', $claim->claim_type) == 'medical' ? 'selected' : '' }}>Medical
                                        Services</option>
                                    <option value="pharmacy"
                                        {{ old('claim_type', $claim->claim_type) == 'pharmacy' ? 'selected' : '' }}>
                                        Pharmacy/Medication</option>
                                    <option value="hospitalization"
                                        {{ old('claim_type', $claim->claim_type) == 'hospitalization' ? 'selected' : '' }}>
                                        Hospitalization</option>
                                    <option value="diagnostic"
                                        {{ old('claim_type', $claim->claim_type) == 'diagnostic' ? 'selected' : '' }}>
                                        Diagnostic Tests</option>
                                    <option value="emergency"
                                        {{ old('claim_type', $claim->claim_type) == 'emergency' ? 'selected' : '' }}>
                                        Emergency Services</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service Date</label>
                                <input type="date" class="form-control" name="service_date"
                                    value="{{ old('service_date', $claim->service_date->format('Y-m-d')) }}"
                                    @if ($claim->status !== 'pending') readonly @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Healthcare Provider</label>
                                <input type="text" class="form-control" name="healthcare_provider"
                                    value="{{ old('healthcare_provider', $claim->healthcare_provider) }}"
                                    @if ($claim->status !== 'pending') readonly @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Provider Type</label>
                                <select class="form-select" name="provider_type"
                                    @if ($claim->status !== 'pending') disabled @endif>
                                    <option value="hospital"
                                        {{ old('provider_type', $claim->provider_type) == 'hospital' ? 'selected' : '' }}>
                                        Hospital</option>
                                    <option value="clinic"
                                        {{ old('provider_type', $claim->provider_type) == 'clinic' ? 'selected' : '' }}>
                                        Clinic</option>
                                    <option value="pharmacy"
                                        {{ old('provider_type', $claim->provider_type) == 'pharmacy' ? 'selected' : '' }}>
                                        Pharmacy</option>
                                    <option value="laboratory"
                                        {{ old('provider_type', $claim->provider_type) == 'laboratory' ? 'selected' : '' }}>
                                        Laboratory</option>
                                    <option value="diagnostic_center"
                                        {{ old('provider_type', $claim->provider_type) == 'diagnostic_center' ? 'selected' : '' }}>
                                        Diagnostic Center</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Claim Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" class="form-control" name="claim_amount"
                                        value="{{ old('claim_amount', $claim->claim_amount) }}" min="0"
                                        step="0.01" @if ($claim->status !== 'pending') readonly @endif>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Diagnosis/Condition</label>
                                <textarea class="form-control" name="diagnosis" rows="3" @if ($claim->status !== 'pending') readonly @endif>{{ old('diagnosis', $claim->diagnosis) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Treatment Description</label>
                                <textarea class="form-control" name="treatment_description" rows="3"
                                    @if ($claim->status !== 'pending') readonly @endif>{{ old('treatment_description', $claim->treatment_description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Additional Notes</label>
                                <textarea class="form-control" name="additional_notes" rows="2"
                                    placeholder="Any additional information or special considerations"
                                    @if ($claim->status !== 'pending') readonly @endif>{{ old('additional_notes', $claim->additional_notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Management -->
                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('claims_manager'))
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-warning text-dark"
                            style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                            <h5 class="card-title mb-0">
                                <i class="ti-settings me-2"></i>Status Management
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Claim Status</label>
                                    <select class="form-select" name="status" id="claimStatus"
                                        onchange="updateStatusFields()">
                                        <option value="pending"
                                            {{ old('status', $claim->status) == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="approved"
                                            {{ old('status', $claim->status) == 'approved' ? 'selected' : '' }}>Approved
                                        </option>
                                        <option value="rejected"
                                            {{ old('status', $claim->status) == 'rejected' ? 'selected' : '' }}>Rejected
                                        </option>
                                        <option value="paid"
                                            {{ old('status', $claim->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="rejectionReasonDiv" style="display: none;">
                                    <label class="form-label fw-semibold">Rejection Reason</label>
                                    <textarea class="form-control" name="rejection_reason" rows="3"
                                        placeholder="Please provide a reason for rejection">{{ old('rejection_reason', $claim->rejection_reason) }}</textarea>
                                </div>
                                <div class="col-md-6" id="paymentDetailsDiv" style="display: none;">
                                    <label class="form-label fw-semibold">Payment Details</label>
                                    <input type="text" class="form-control" name="payment_reference"
                                        value="{{ old('payment_reference', $claim->payment_reference) }}"
                                        placeholder="Payment Reference">
                                    <input type="date" class="form-control mt-2" name="payment_date"
                                        value="{{ old('payment_date', $claim->payment_date ? $claim->payment_date->format('Y-m-d') : '') }}"
                                        placeholder="Payment Date">
                                </div>
                            </div>

                            <!-- Approval Workflow -->
                            <div class="mt-4">
                                <h6 class="fw-semibold mb-3">Approval Workflow</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">RO Status</label>
                                        <select class="form-select" name="ro_status">
                                            <option value="">Not Reviewed</option>
                                            <option value="approved"
                                                {{ old('ro_status', $claim->ro_status) == 'approved' ? 'selected' : '' }}>
                                                Approved</option>
                                            <option value="rejected"
                                                {{ old('ro_status', $claim->ro_status) == 'rejected' ? 'selected' : '' }}>
                                                Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">E5 Status</label>
                                        <select class="form-select" name="e5_status">
                                            <option value="">Not Reviewed</option>
                                            <option value="approved"
                                                {{ old('e5_status', $claim->e5_status) == 'approved' ? 'selected' : '' }}>
                                                Approved</option>
                                            <option value="rejected"
                                                {{ old('e5_status', $claim->e5_status) == 'rejected' ? 'selected' : '' }}>
                                                Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Supporting Documents -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-success text-white" style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <h5 class="card-title mb-0">
                            <i class="ti-file me-2"></i>Supporting Documents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Medical Report</label>
                                @if ($claim->medical_report)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $claim->medical_report) }}"
                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="ti-eye me-1"></i>View Current
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control" name="medical_report"
                                    accept=".pdf,.jpg,.jpeg,.png" @if ($claim->status !== 'pending') disabled @endif>
                                <div class="form-text">PDF, JPG, PNG (Max 5MB)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Prescription</label>
                                @if ($claim->prescription)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $claim->prescription) }}"
                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="ti-eye me-1"></i>View Current
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control" name="prescription"
                                    accept=".pdf,.jpg,.jpeg,.png" @if ($claim->status !== 'pending') disabled @endif>
                                <div class="form-text">PDF, JPG, PNG (Max 5MB)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Receipt/Invoice</label>
                                @if ($claim->receipt)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $claim->receipt) }}"
                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="ti-eye me-1"></i>View Current
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control" name="receipt" accept=".pdf,.jpg,.jpeg,.png"
                                    @if ($claim->status !== 'pending') disabled @endif>
                                <div class="form-text">PDF, JPG, PNG (Max 5MB)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Reason -->
                @if ($claim->status === 'pending')
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-info text-white"
                            style="padding: 1.5rem; border-radius: 12px 12px 0 0;">
                            <h5 class="card-title mb-0">
                                <i class="ti-message me-2"></i>Change Reason
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reason for Changes <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" name="change_reason" rows="3" required
                                    placeholder="Please explain why you are making changes to this claim..."></textarea>
                                <div class="form-text">This information will be recorded in the claim history for audit
                                    purposes.</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('claims.show', $claim->id) }}" class="btn btn-outline-secondary">
                                <i class="ti-arrow-left me-1"></i>Cancel
                            </a>
                            <div class="btn-list">
                                @if ($claim->status === 'pending')
                                    <button type="submit" class="btn btn-primary" onclick="return validateClaimEdit()">
                                        <i class="ti-save me-1"></i>Update Claim
                                    </button>
                                @elseif(auth()->user()->hasRole('admin') || auth()->user()->hasRole('claims_manager'))
                                    <button type="submit" class="btn btn-warning" onclick="return validateClaimEdit()">
                                        <i class="ti-save me-1"></i>Update Claim (Admin)
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function updateStatusFields() {
            const status = document.getElementById('claimStatus').value;
            const rejectionDiv = document.getElementById('rejectionReasonDiv');
            const paymentDiv = document.getElementById('paymentDetailsDiv');

            // Hide all optional fields first
            rejectionDiv.style.display = 'none';
            paymentDiv.style.display = 'none';

            // Show relevant fields based on status
            if (status === 'rejected') {
                rejectionDiv.style.display = 'block';
            } else if (status === 'paid') {
                paymentDiv.style.display = 'block';
            }
        }

        function validateClaimEdit() {
            const status = document.getElementById('claimStatus').value;
            const changeReason = document.querySelector('textarea[name="change_reason"]');

            // If claim is pending and changes are being made, require change reason
            @if ($claim->status === 'pending')
                if (!changeReason.value.trim()) {
                    alert('Please provide a reason for the changes to this claim.');
                    changeReason.focus();
                    return false;
                }
            @endif

            // If status is being changed to rejected, require rejection reason
            if (status === 'rejected') {
                const rejectionReason = document.querySelector('textarea[name="rejection_reason"]');
                if (!rejectionReason.value.trim()) {
                    alert('Please provide a rejection reason when changing status to rejected.');
                    rejectionReason.focus();
                    return false;
                }
            }

            // Confirm status changes
            @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('claims_manager'))
                if (status !== '{{ $claim->status }}') {
                    const confirmMessage =
                        `Are you sure you want to change the claim status from "{{ ucfirst($claim->status) }}" to "${status.charAt(0).toUpperCase() + status.slice(1)}"? This action will be logged.`;
                    if (!confirm(confirmMessage)) {
                        return false;
                    }
                }
            @endif

            return true;
        }

        // Initialize status fields on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStatusFields();
        });
    </script>
@endpush
