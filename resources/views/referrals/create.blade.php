@extends('layouts.app')

@section('title', 'Create Referral')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Referral System
                    </div>
                    <h2 class="page-title">
                        Create New Referral
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('referrals.index') }}" class="btn">
                            <i class="ti ti-arrow-left me-2"></i>
                            Back to Referrals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('referrals.store') }}" id="referralForm">
                @csrf

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
                                    value="{{ old('referrer_name') }}" placeholder="Enter referrer's full name">
                                @error('referrer_name')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referrer Email</label>
                                <input type="email" name="referrer_email" class="form-control" required
                                    value="{{ old('referrer_email') }}" placeholder="referrer@example.com">
                                @error('referrer_email')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referrer Phone</label>
                                <input type="tel" name="referrer_phone" class="form-control" required
                                    value="{{ old('referrer_phone') }}" placeholder="+1234567890">
                                @error('referrer_phone')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referrer ID ( optional )</label>
                                <input type="text" name="referrer_id" class="form-control"
                                    value="{{ old('referrer_id') }}" placeholder="Existing referrer ID">
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
                                    value="{{ old('referred_name') }}" placeholder="Enter referred person's full name">
                                @error('referred_name')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referred Person Email</label>
                                <input type="email" name="referred_email" class="form-control" required
                                    value="{{ old('referred_email') }}" placeholder="referred@example.com">
                                @error('referred_email')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Referred Person Phone</label>
                                <input type="tel" name="referred_phone" class="form-control" required
                                    value="{{ old('referred_phone') }}" placeholder="+1234567890">
                                @error('referred_phone')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="referred_dob" class="form-control"
                                    value="{{ old('referred_dob') }}">
                                @error('referred_dob')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="referred_address" class="form-control" rows="3" placeholder="Enter complete address">{{ old('referred_address') }}</textarea>
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
                                        {{ old('service_type') == 'healthcare' ? 'selected' : '' }}>Healthcare</option>
                                    <option value="education" {{ old('service_type') == 'education' ? 'selected' : '' }}>
                                        Education</option>
                                    <option value="financial" {{ old('service_type') == 'financial' ? 'selected' : '' }}>
                                        Financial Services</option>
                                    <option value="insurance" {{ old('service_type') == 'insurance' ? 'selected' : '' }}>
                                        Insurance</option>
                                    <option value="legal" {{ old('service_type') == 'legal' ? 'selected' : '' }}>Legal
                                        Services</option>
                                    <option value="consulting"
                                        {{ old('service_type') == 'consulting' ? 'selected' : '' }}>Consulting</option>
                                    <option value="other" {{ old('service_type') == 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                @error('service_type')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority Level</label>
                                <select name="priority_level" class="form-select">
                                    <option value="normal" {{ old('priority_level') == 'normal' ? 'selected' : '' }}>
                                        Normal</option>
                                    <option value="high" {{ old('priority_level') == 'high' ? 'selected' : '' }}>High
                                    </option>
                                    <option value="urgent" {{ old('priority_level') == 'urgent' ? 'selected' : '' }}>
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
                                        min="0" value="{{ old('commission_amount') }}" placeholder="0.00">
                                </div>
                                @error('commission_amount')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Type</label>
                                <select name="commission_type" class="form-select">
                                    <option value="fixed" {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>Fixed
                                        Amount</option>
                                    <option value="percentage"
                                        {{ old('commission_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="tiered" {{ old('commission_type') == 'tiered' ? 'selected' : '' }}>
                                        Tiered</option>
                                </select>
                                @error('commission_type')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Referral Notes</label>
                                <textarea name="notes" class="form-control" rows="4"
                                    placeholder="Add any additional notes about this referral">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-file-upload me-2 text-warning"></i>
                            Supporting Documents
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Referrer Agreement</label>
                                <input type="file" name="referrer_agreement" class="form-control"
                                    accept=".pdf,.doc,.docx">
                                <div class="form-text">Upload referrer agreement ( PDF, DOC, DOCX - Max 5MB )</div>
                                @error('referrer_agreement')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referred Person ID</label>
                                <input type="file" name="referred_id_document" class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Upload ID document ( PDF, JPG, PNG - Max 5MB )</div>
                                @error('referred_id_document')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional Document 1</label>
                                <input type="file" name="additional_document_1" class="form-control"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">Any additional supporting document</div>
                                @error('additional_document_1')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional Document 2</label>
                                <input type="file" name="additional_document_2" class="form-control"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">Any additional supporting document</div>
                                @error('additional_document_2')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings and Notifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-settings me-2 text-secondary"></i>
                            Settings & Notifications
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" name="follow_up_date" class="form-control"
                                    value="{{ old('follow_up_date') }}">
                                @error('follow_up_date')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assigned To</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Select Staff Member</option>
                                    <option value="1" {{ old('assigned_to') == '1' ? 'selected' : '' }}>John Doe
                                    </option>
                                    <option value="2" {{ old('assigned_to') == '2' ? 'selected' : '' }}>Jane Smith
                                    </option>
                                    <option value="3" {{ old('assigned_to') == '3' ? 'selected' : '' }}>Mike Johnson
                                    </option>
                                </select>
                                @error('assigned_to')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-label">Notification Settings</div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_referrer"
                                        {{ old('notify_referrer', 'checked') }} id="notify_referrer">
                                    <label class="form-check-label" for="notify_referrer">
                                        Send notification email to referrer
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_referred"
                                        {{ old('notify_referred', 'checked') }} id="notify_referred">
                                    <label class="form-check-label" for="notify_referred">
                                        Send notification email to referred person
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_approve"
                                        {{ old('auto_approve') }} id="auto_approve">
                                    <label class="form-check-label" for="auto_approve">
                                        Auto-approve referral ( system rules apply )
                                    </label>
                                </div>
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
                                <a href="{{ route('referrals.index') }}" class="btn">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-2"></i>
                                    Create Referral
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
