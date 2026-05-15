@extends('layouts.app')

@section('title', 'Edit Referral')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Referral System
                    </div>
                    <h2 class="page-title">
                        Edit Referral
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('referrals.show', $referral->id) }}" class="btn">
                            <i class="ti ti-arrow-left me-2"></i>
                            Back to Details
                        </a>
                        <a href="{{ route('referrals.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-list me-2"></i>
                            All Referrals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('referrals.update', $referral->id) }}" id="referralForm">
                @csrf
                @method('PUT')

                <!-- Referrer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user me-2 text-primary"></i>
                            Referrer Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Referrer Name</label>
                                <input type="text" name="referrer_name" class="form-control" required
                                    value="{{ old('referrer_name', $referral->referrer_name) }}"
                                    placeholder="Enter referrer's full name">
                                @error('referrer_name')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referrer Email</label>
                                <input type="email" name="referrer_email" class="form-control" required
                                    value="{{ old('referrer_email', $referral->referrer_email) }}"
                                    placeholder="referrer@example.com">
                                @error('referrer_email')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referrer Phone</label>
                                <input type="tel" name="referrer_phone" class="form-control" required
                                    value="{{ old('referrer_phone', $referral->referrer_phone) }}"
                                    placeholder="+1234567890">
                                @error('referrer_phone')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referrer ID ( optional )</label>
                                <input type="text" name="referrer_id" class="form-control"
                                    value="{{ old('referrer_id', $referral->referrer_id) }}"
                                    placeholder="Existing referrer ID">
                                @error('referrer_id')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referred Person Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user-plus me-2 text-success"></i>
                            Referred Person Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Referred Person Name</label>
                                <input type="text" name="referred_name" class="form-control" required
                                    value="{{ old('referred_name', $referral->referred_name) }}"
                                    placeholder="Enter referred person's full name">
                                @error('referred_name')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referred Person Email</label>
                                <input type="email" name="referred_email" class="form-control" required
                                    value="{{ old('referred_email', $referral->referred_email) }}"
                                    placeholder="referred@example.com">
                                @error('referred_email')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referred Person Phone</label>
                                <input type="tel" name="referred_phone" class="form-control" required
                                    value="{{ old('referred_phone', $referral->referred_phone) }}"
                                    placeholder="+1234567890">
                                @error('referred_phone')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="referred_dob" class="form-control"
                                    value="{{ old('referred_dob', $referral->referred_dob) }}">
                                @error('referred_dob')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="referred_address" class="form-control" rows="3" placeholder="Enter complete address">{{ old('referred_address', $referral->referred_address) }}</textarea>
                                @error('referred_address')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-file-text me-2 text-info"></i>
                            Referral Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Service Type</label>
                                <select name="service_type" class="form-select" required>
                                    <option value="">Select Service Type</option>
                                    <option value="healthcare"
                                        {{ old('service_type', $referral->service_type) == 'healthcare' ? 'selected' : '' }}>
                                        Healthcare</option>
                                    <option value="education"
                                        {{ old('service_type', $referral->service_type) == 'education' ? 'selected' : '' }}>
                                        Education</option>
                                    <option value="financial"
                                        {{ old('service_type', $referral->service_type) == 'financial' ? 'selected' : '' }}>
                                        Financial Services</option>
                                    <option value="insurance"
                                        {{ old('service_type', $referral->service_type) == 'insurance' ? 'selected' : '' }}>
                                        Insurance</option>
                                    <option value="legal"
                                        {{ old('service_type', $referral->service_type) == 'legal' ? 'selected' : '' }}>
                                        Legal Services</option>
                                    <option value="consulting"
                                        {{ old('service_type', $referral->service_type) == 'consulting' ? 'selected' : '' }}>
                                        Consulting</option>
                                    <option value="other"
                                        {{ old('service_type', $referral->service_type) == 'other' ? 'selected' : '' }}>
                                        Other</option>
                                </select>
                                @error('service_type')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority Level</label>
                                <select name="priority_level" class="form-select">
                                    <option value="normal"
                                        {{ old('priority_level', $referral->priority_level) == 'normal' ? 'selected' : '' }}>
                                        Normal</option>
                                    <option value="high"
                                        {{ old('priority_level', $referral->priority_level) == 'high' ? 'selected' : '' }}>
                                        High</option>
                                    <option value="urgent"
                                        {{ old('priority_level', $referral->priority_level) == 'urgent' ? 'selected' : '' }}>
                                        Urgent</option>
                                </select>
                                @error('priority_level')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expected Commission Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="commission_amount" class="form-control" step="0.01"
                                        min="0"
                                        value="{{ old('commission_amount', $referral->commission_amount) }}"
                                        placeholder="0.00">
                                </div>
                                @error('commission_amount')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Type</label>
                                <select name="commission_type" class="form-select">
                                    <option value="fixed"
                                        {{ old('commission_type', $referral->commission_type) == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount</option>
                                    <option value="percentage"
                                        {{ old('commission_type', $referral->commission_type) == 'percentage' ? 'selected' : '' }}>
                                        Percentage</option>
                                    <option value="tiered"
                                        {{ old('commission_type', $referral->commission_type) == 'tiered' ? 'selected' : '' }}>
                                        Tiered</option>
                                </select>
                                @error('commission_type')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Referral Notes</label>
                                <textarea name="notes" class="form-control" rows="4"
                                    placeholder="Add any additional notes about this referral">{{ old('notes', $referral->notes) }}</textarea>
                                @error('notes')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Assignment -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-settings me-2 text-secondary"></i>
                            Status & Assignment
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Current Status</label>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'completed' => 'bg-info',
                                    ];
                                    $currentStatusColor =
                                        $statusColors[$referral->status ?? 'pending'] ?? 'bg-secondary';
                                @endphp
                                <div class="input-group">
                                    <select name="status" class="form-select">
                                        <option value="pending"
                                            {{ old('status', $referral->status) == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="approved"
                                            {{ old('status', $referral->status) == 'approved' ? 'selected' : '' }}>Approved
                                        </option>
                                        <option value="rejected"
                                            {{ old('status', $referral->status) == 'rejected' ? 'selected' : '' }}>Rejected
                                        </option>
                                        <option value="completed"
                                            {{ old('status', $referral->status) == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                    </select>
                                    <span class="input-group-text">
                                        <span
                                            class="badge {{ $currentStatusColor }}">{{ ucfirst($referral->status ?? 'pending') }}</span>
                                    </span>
                                </div>
                                @error('status')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Status</label>
                                <select name="commission_status" class="form-select">
                                    <option value="pending"
                                        {{ old('commission_status', $referral->commission_status) == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="paid"
                                        {{ old('commission_status', $referral->commission_status) == 'paid' ? 'selected' : '' }}>
                                        Paid</option>
                                    <option value="waived"
                                        {{ old('commission_status', $referral->commission_status) == 'waived' ? 'selected' : '' }}>
                                        Waived</option>
                                </select>
                                @error('commission_status')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" name="follow_up_date" class="form-control"
                                    value="{{ old('follow_up_date', $referral->follow_up_date) }}">
                                @error('follow_up_date')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assigned To</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Select Staff Member</option>
                                    <option value="1"
                                        {{ old('assigned_to', $referral->assigned_to) == '1' ? 'selected' : '' }}>John Doe
                                    </option>
                                    <option value="2"
                                        {{ old('assigned_to', $referral->assigned_to) == '2' ? 'selected' : '' }}>Jane
                                        Smith</option>
                                    <option value="3"
                                        {{ old('assigned_to', $referral->assigned_to) == '3' ? 'selected' : '' }}>Mike
                                        Johnson</option>
                                </select>
                                @error('assigned_to')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Documents -->
                @if (
                    $referral->referrer_agreement ||
                        $referral->referred_id_document ||
                        $referral->additional_document_1 ||
                        $referral->additional_document_2)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-file me-2 text-warning"></i>
                                Existing Documents
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if ($referral->referrer_agreement)
                                    <div class="col-md-6 mb-3">
                                        <div class="form-label">Referrer Agreement</div>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ $referral->referrer_agreement }}"
                                                class="btn btn-outline-secondary btn-sm me-2" target="_blank">
                                                <i class="ti ti-file-text me-1"></i>View
                                            </a>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="remove_referrer_agreement" value="1">
                                                <span class="form-check-label text-danger">Remove</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                @if ($referral->referred_id_document)
                                    <div class="col-md-6 mb-3">
                                        <div class="form-label">ID Document</div>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ $referral->referred_id_document }}"
                                                class="btn btn-outline-secondary btn-sm me-2" target="_blank">
                                                <i class="ti ti-id me-1"></i>View
                                            </a>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="remove_referred_id_document" value="1">
                                                <span class="form-check-label text-danger">Remove</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                @if ($referral->additional_document_1)
                                    <div class="col-md-6 mb-3">
                                        <div class="form-label">Additional Document 1</div>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ $referral->additional_document_1 }}"
                                                class="btn btn-outline-secondary btn-sm me-2" target="_blank">
                                                <i class="ti ti-file me-1"></i>View
                                            </a>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="remove_additional_document_1" value="1">
                                                <span class="form-check-label text-danger">Remove</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                @if ($referral->additional_document_2)
                                    <div class="col-md-6 mb-3">
                                        <div class="form-label">Additional Document 2</div>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ $referral->additional_document_2 }}"
                                                class="btn btn-outline-secondary btn-sm me-2" target="_blank">
                                                <i class="ti ti-file me-1"></i>View
                                            </a>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="remove_additional_document_2" value="1">
                                                <span class="form-check-label text-danger">Remove</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- New Documents Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-file-upload me-2 text-warning"></i>
                            Upload New Documents
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Referrer Agreement</label>
                                <input type="file" name="new_referrer_agreement" class="form-control"
                                    accept=".pdf,.doc,.docx">
                                <div class="form-text">Upload new referrer agreement ( PDF, DOC, DOCX - Max 5MB )</div>
                                @error('new_referrer_agreement')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Referred Person ID</label>
                                <input type="file" name="new_referred_id_document" class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Upload new ID document ( PDF, JPG, PNG - Max 5MB )</div>
                                @error('new_referred_id_document')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Additional Document 1</label>
                                <input type="file" name="new_additional_document_1" class="form-control"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">Any additional supporting document</div>
                                @error('new_additional_document_1')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Additional Document 2</label>
                                <input type="file" name="new_additional_document_2" class="form-control"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">Any additional supporting document</div>
                                @error('new_additional_document_2')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="saveAsDraft()">
                                    <i class="ti ti-device-floppy me-2"></i>
                                    Save as Draft
                                </button>
                            </div>
                            <div class="btn-list">
                                <a href="{{ route('referrals.show', $referral->id) }}" class="btn">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-2"></i>
                                    Update Referral
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function saveAsDraft() {
            const form = document.getElementById('referralForm');
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'save_as_draft';
            draftInput.value = '1';
            form.appendChild(draftInput);
            form.submit();
        }

        // Auto-calculate commission based on service type if needed
        document.querySelector('select[name="service_type"]')?.addEventListener('change', function(e) {
            const commissionInput = document.querySelector('input[name="commission_amount"]');
            const defaultCommissions = {
                'healthcare': 100.00,
                'education': 75.00,
                'financial': 150.00,
                'insurance': 125.00,
                'legal': 200.00,
                'consulting': 175.00,
                'other': 50.00
            };

            if (!commissionInput.value && defaultCommissions[e.target.value]) {
                commissionInput.value = defaultCommissions[e.target.value];
            }
        });
    </script>
@endsection
