@extends('layouts.app')

@section('title', 'Referral Settings')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Referral System
                    </div>
                    <h2 class="page-title">
                        Referral Settings & Configuration
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
            <form method="POST" action="{{ route('referrals.settings.update') }}" id="settingsForm">
                @csrf

                <!-- Commission Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-cash me-2 text-success"></i>
                            Commission Settings
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Default Commission Amount ($)</label>
                                <input type="number" name="default_commission" class="form-control" step="0.01"
                                    min="0"
                                    value="{{ old('default_commission', $settings['default_commission'] ?? 50.0) }}">
                                <div class="form-text">Default commission for new referrals</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Type</label>
                                <select name="commission_type" class="form-select">
                                    <option value="fixed"
                                        {{ old('commission_type', $settings['commission_type']) == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount</option>
                                    <option value="percentage"
                                        {{ old('commission_type', $settings['commission_type']) == 'percentage' ? 'selected' : '' }}>
                                        Percentage of Service Value</option>
                                    <option value="tiered"
                                        {{ old('commission_type', $settings['commission_type']) == 'tiered' ? 'selected' : '' }}>
                                        Tiered Based on Volume</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Percentage (%)</label>
                                <input type="number" name="commission_percentage" class="form-control" step="0.1"
                                    min="0" max="100"
                                    value="{{ old('commission_percentage', $settings['commission_percentage'] ?? 10.0) }}">
                                <div class="form-text">Used when percentage type is selected</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Processing Days</label>
                                <input type="number" name="payment_processing_days" class="form-control" min="1"
                                    max="365"
                                    value="{{ old('payment_processing_days', $settings['payment_processing_days'] ?? 7) }}">
                                <div class="form-text">Days before commission is paid after referral completion</div>
                            </div>
                        </div>

                        <!-- Service-Specific Commissions -->
                        <div class="mt-4">
                            <h5 class="mb-3">Service-Specific Commission Rates</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Service Type</th>
                                            <th>Commission Amount ($)</th>
                                            <th>Percentage (%)</th>
                                            <th>Active</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Healthcare</td>
                                            <td>
                                                <input type="number" name="commission_healthcare"
                                                    class="form-control form-control-sm" step="0.01" min="0"
                                                    value="{{ old('commission_healthcare', $settings['commission_healthcare'] ?? 100.0) }}">
                                            </td>
                                            <td>
                                                <input type="number" name="percentage_healthcare"
                                                    class="form-control form-control-sm" step="0.1" min="0"
                                                    max="100"
                                                    value="{{ old('percentage_healthcare', $settings['percentage_healthcare'] ?? 15.0) }}">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="active_healthcare"
                                                        {{ old('active_healthcare', $settings['active_healthcare'] ?? true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Education</td>
                                            <td>
                                                <input type="number" name="commission_education"
                                                    class="form-control form-control-sm" step="0.01" min="0"
                                                    value="{{ old('commission_education', $settings['commission_education'] ?? 75.0) }}">
                                            </td>
                                            <td>
                                                <input type="number" name="percentage_education"
                                                    class="form-control form-control-sm" step="0.1" min="0"
                                                    max="100"
                                                    value="{{ old('percentage_education', $settings['percentage_education'] ?? 10.0) }}">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="active_education"
                                                        {{ old('active_education', $settings['active_education'] ?? true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Financial Services</td>
                                            <td>
                                                <input type="number" name="commission_financial"
                                                    class="form-control form-control-sm" step="0.01" min="0"
                                                    value="{{ old('commission_financial', $settings['commission_financial'] ?? 150.0) }}">
                                            </td>
                                            <td>
                                                <input type="number" name="percentage_financial"
                                                    class="form-control form-control-sm" step="0.1" min="0"
                                                    max="100"
                                                    value="{{ old('percentage_financial', $settings['percentage_financial'] ?? 20.0) }}">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="active_financial"
                                                        {{ old('active_financial', $settings['active_financial'] ?? true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>
                                                <input type="number" name="commission_insurance"
                                                    class="form-control form-control-sm" step="0.01" min="0"
                                                    value="{{ old('commission_insurance', $settings['commission_insurance'] ?? 125.0) }}">
                                            </td>
                                            <td>
                                                <input type="number" name="percentage_insurance"
                                                    class="form-control form-control-sm" step="0.1" min="0"
                                                    max="100"
                                                    value="{{ old('percentage_insurance', $settings['percentage_insurance'] ?? 18.0) }}">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="active_insurance"
                                                        {{ old('active_insurance', $settings['active_insurance'] ?? true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Legal Services</td>
                                            <td>
                                                <input type="number" name="commission_legal"
                                                    class="form-control form-control-sm" step="0.01" min="0"
                                                    value="{{ old('commission_legal', $settings['commission_legal'] ?? 200.0) }}">
                                            </td>
                                            <td>
                                                <input type="number" name="percentage_legal"
                                                    class="form-control form-control-sm" step="0.1" min="0"
                                                    max="100"
                                                    value="{{ old('percentage_legal', $settings['percentage_legal'] ?? 25.0) }}">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="active_legal"
                                                        {{ old('active_legal', $settings['active_legal'] ?? true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-check me-2 text-info"></i>
                            Approval Settings
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_approve_enabled"
                                        {{ old('auto_approve_enabled', $settings['auto_approve_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Enable Auto-Approval
                                    </label>
                                </div>
                                <div class="form-text">Automatically approve referrals that meet criteria</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Auto-Approval Criteria</label>
                                <select name="auto_approve_criteria" class="form-select">
                                    <option value="verified_referrer"
                                        {{ old('auto_approve_criteria', $settings['auto_approve_criteria']) == 'verified_referrer' ? 'selected' : '' }}>
                                        Verified Referrers Only
                                    </option>
                                    <option value="low_value"
                                        {{ old('auto_approve_criteria', $settings['auto_approve_criteria']) == 'low_value' ? 'selected' : '' }}>
                                        Low Value Referrals (< $100) </option>
                                    <option value="all"
                                        {{ old('auto_approve_criteria', $settings['auto_approve_criteria']) == 'all' ? 'selected' : '' }}>
                                        All Referrals
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Minimum Referrer Score</label>
                                <input type="number" name="min_referrer_score" class="form-control" min="0"
                                    max="100"
                                    value="{{ old('min_referrer_score', $settings['min_referrer_score'] ?? 70) }}">
                                <div class="form-text">Minimum score for auto-approval</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Approval Time Limit ( hours )</label>
                                <input type="number" name="approval_time_limit" class="form-control" min="1"
                                    max="720"
                                    value="{{ old('approval_time_limit', $settings['approval_time_limit'] ?? 48) }}">
                                <div class="form-text">Maximum hours to approve a referral</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-bell me-2 text-warning"></i>
                            Notification Settings
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="mb-3">Email Notifications</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_referrer_on_create"
                                        {{ old('notify_referrer_on_create', $settings['notify_referrer_on_create'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify referrer on referral creation
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_referrer_on_approval"
                                        {{ old('notify_referrer_on_approval', $settings['notify_referrer_on_approval'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify referrer on approval
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_referrer_on_commission"
                                        {{ old('notify_referrer_on_commission', $settings['notify_referrer_on_commission'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify referrer on commission payment
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_referred_on_create"
                                        {{ old('notify_referred_on_create', $settings['notify_referred_on_create'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify referred person on referral creation
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Admin Notifications</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_admin_on_create"
                                        {{ old('notify_admin_on_create', $settings['notify_admin_on_create'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify admin on new referral
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_admin_on_pending"
                                        {{ old('notify_admin_on_pending', $settings['notify_admin_on_pending'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify admin on pending referrals
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_admin_on_overdue"
                                        {{ old('notify_admin_on_overdue', $settings['notify_admin_on_overdue'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Notify admin on overdue approvals
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="daily_summary_enabled"
                                        {{ old('daily_summary_enabled', $settings['daily_summary_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Enable daily summary reports
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-settings me-2 text-secondary"></i>
                            General Settings
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Referral ID Prefix</label>
                                <input type="text" name="referral_id_prefix" class="form-control"
                                    value="{{ old('referral_id_prefix', $settings['referral_id_prefix'] ?? 'REF') }}"
                                    maxlength="10">
                                <div class="form-text">Prefix for generated referral IDs</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Follow-up Days</label>
                                <input type="number" name="default_followup_days" class="form-control" min="1"
                                    max="365"
                                    value="{{ old('default_followup_days', $settings['default_followup_days'] ?? 7) }}">
                                <div class="form-text">Default days to set for follow-up</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maximum Referrals Per Day</label>
                                <input type="number" name="max_referrals_per_day" class="form-control" min="1"
                                    max="1000"
                                    value="{{ old('max_referrals_per_day', $settings['max_referrals_per_day'] ?? 50) }}">
                                <div class="form-text">Limit referrals per referrer per day</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referral Expiry Days</label>
                                <input type="number" name="referral_expiry_days" class="form-control" min="1"
                                    max="365"
                                    value="{{ old('referral_expiry_days', $settings['referral_expiry_days'] ?? 30) }}">
                                <div class="form-text">Days before referral expires if not acted upon</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="require_referrer_agreement"
                                        {{ old('require_referrer_agreement', $settings['require_referrer_agreement'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Require referrer agreement document
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="require_id_document"
                                        {{ old('require_id_document', $settings['require_id_document'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Require ID document for referred person
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Templates -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-mail me-2 text-primary"></i>
                            Email Templates
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Referrer Welcome Email Subject</label>
                                <input type="text" name="email_referrer_welcome_subject" class="form-control"
                                    value="{{ old('email_referrer_welcome_subject', $settings['email_referrer_welcome_subject'] ?? 'Welcome to Our Referral Program') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referral Created Email Subject</label>
                                <input type="text" name="email_referral_created_subject" class="form-control"
                                    value="{{ old('email_referral_created_subject', $settings['email_referral_created_subject'] ?? 'Your Referral Has Been Received') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referral Approved Email Subject</label>
                                <input type="text" name="email_referral_approved_subject" class="form-control"
                                    value="{{ old('email_referral_approved_subject', $settings['email_referral_approved_subject'] ?? 'Your Referral Has Been Approved') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Paid Email Subject</label>
                                <input type="text" name="email_commission_paid_subject" class="form-control"
                                    value="{{ old('email_commission_paid_subject', $settings['email_commission_paid_subject'] ?? 'Commission Payment Processed') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetToDefaults()">
                                    <i class="ti ti-refresh me-2"></i>
                                    Reset to Defaults
                                </button>
                            </div>
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-2"></i>
                                    Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function resetToDefaults() {
            if (confirm(
                    'Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                const form = document.getElementById('settingsForm');
                const resetInput = document.createElement('input');
                resetInput.type = 'hidden';
                resetInput.name = 'reset_defaults';
                resetInput.value = '1';
                form.appendChild(resetInput);
                form.submit();
            }
        }

        // Toggle commission fields based on commission type
        document.querySelector('select[name="commission_type"]')?.addEventListener('change', function(e) {
            const percentageFields = document.querySelectorAll('input[name*="percentage"]');
            const fixedFields = document.querySelectorAll('input[name*="commission_"]:not([name*="percentage"])');

            if (e.target.value === 'percentage') {
                percentageFields.forEach(field => field.disabled = false);
                fixedFields.forEach(field => field.disabled = true);
            } else if (e.target.value === 'fixed') {
                percentageFields.forEach(field => field.disabled = true);
                fixedFields.forEach(field => field.disabled = false);
            } else {
                percentageFields.forEach(field => field.disabled = false);
                fixedFields.forEach(field => field.disabled = false);
            }
        });
    </script>
@endsection
