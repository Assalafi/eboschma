@extends('layouts.facility')

@section('title', 'Settings')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Settings</h1>
                        <p class="text-muted mb-0">Configure your account and application preferences</p>
                    </div>
                    <div>
                        <a href="{{ route('facility.dashboard') }}" class="btn btn-outline-secondary me-2">
                            <i class="ti-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <button class="btn btn-primary" disabled>
                            <i class="ti-save me-1"></i> Save All Changes
                        </button>
                    </div>
                </div>

                <!-- Settings Navigation Tabs -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-tabs-bottom border-0" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active px-4 py-3" id="account-tab" data-bs-toggle="tab"
                                    data-bs-target="#account" type="button" role="tab"
                                    style="border: none; background: transparent; color: #01542B; font-weight: 600;">
                                    <i class="ti-user me-2"></i>Account Settings
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-4 py-3" id="system-tab" data-bs-toggle="tab"
                                    data-bs-target="#system" type="button" role="tab"
                                    style="border: none; background: transparent; color: #6c757d; font-weight: 600;">
                                    <i class="ti-settings me-2"></i>System Settings
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-4 py-3" id="support-tab" data-bs-toggle="tab"
                                    data-bs-target="#support" type="button" role="tab"
                                    style="border: none; background: transparent; color: #6c757d; font-weight: 600;">
                                    <i class="ti-help-alt me-2"></i>Support & Help
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="settingsTabContent">
                    <!-- Account Settings Tab -->
                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-header bg-white border-bottom" style="padding: 1.5rem;">
                                        <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                            <i class="ti-user me-2 text-primary"></i>Account Security
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="accordion" id="securityAccordion">
                                            <!-- Password Section -->
                                            <div class="accordion-item border mb-3"
                                                style="border-radius: 8px; overflow: hidden;">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#passwordCollapse"
                                                        style="background: #f8f9fa; font-weight: 600;">
                                                        <i class="ti-lock me-3 text-primary"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span>Password & Authentication</span>
                                                                <span class="badge bg-success">Strong</span>
                                                            </div>
                                                            <small class="text-muted">Last changed 30 days ago</small>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="passwordCollapse" class="accordion-collapse collapse"
                                                    data-bs-parent="#securityAccordion">
                                                    <div class="accordion-body p-4">
                                                        <p class="text-muted mb-3">Update your password to keep your account
                                                            secure.</p>
                                                        <button class="btn btn-primary" disabled>
                                                            <i class="ti-lock me-2"></i>Change Password
                                                            <span class="badge bg-light text-dark ms-2">Coming Soon</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 2FA Section -->
                                            <div class="accordion-item border mb-3"
                                                style="border-radius: 8px; overflow: hidden;">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#twoFactorCollapse"
                                                        style="background: #f8f9fa; font-weight: 600;">
                                                        <i class="ti-shield me-3 text-warning"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span>Two-Factor Authentication</span>
                                                                <span class="badge bg-warning">Disabled</span>
                                                            </div>
                                                            <small class="text-muted">Add an extra layer of security</small>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="twoFactorCollapse" class="accordion-collapse collapse"
                                                    data-bs-parent="#securityAccordion">
                                                    <div class="accordion-body p-4">
                                                        <p class="text-muted mb-3">Enable 2FA to protect your account with
                                                            an additional security layer.</p>
                                                        <button class="btn btn-success" disabled>
                                                            <i class="ti-shield me-2"></i>Enable 2FA
                                                            <span class="badge bg-light text-dark ms-2">Coming Soon</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Email Notifications Section -->
                                            <div class="accordion-item border"
                                                style="border-radius: 8px; overflow: hidden;">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#notificationsCollapse"
                                                        style="background: #f8f9fa; font-weight: 600;">
                                                        <i class="ti-email me-3 text-info"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span>Email Notifications</span>
                                                                <span class="badge bg-success">Enabled</span>
                                                            </div>
                                                            <small class="text-muted">Manage email preferences</small>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="notificationsCollapse" class="accordion-collapse collapse"
                                                    data-bs-parent="#securityAccordion">
                                                    <div class="accordion-body p-4">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-check form-switch mb-3">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="systemAlerts" checked disabled>
                                                                    <label class="form-check-label" for="systemAlerts">
                                                                        System Alerts
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-switch mb-3">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="securityAlerts" checked disabled>
                                                                    <label class="form-check-label" for="securityAlerts">
                                                                        Security Notifications
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-check form-switch mb-3">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="marketingEmails" disabled>
                                                                    <label class="form-check-label" for="marketingEmails">
                                                                        Marketing Emails
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-switch mb-3">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="newsletter" disabled>
                                                                    <label class="form-check-label" for="newsletter">
                                                                        Newsletter
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button class="btn btn-info" disabled>
                                                            <i class="ti-save me-2"></i>Save Preferences
                                                            <span class="badge bg-light text-dark ms-2">Coming Soon</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions Sidebar -->
                            <div class="col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-4">
                                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                            <i class="ti-flash me-2 text-warning"></i>Quick Actions
                                        </h6>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-primary" disabled>
                                                <i class="ti-key me-2"></i>Reset API Key
                                            </button>
                                            <button class="btn btn-outline-warning" disabled>
                                                <i class="ti-download me-2"></i>Download Data
                                            </button>
                                            <button class="btn btn-outline-danger" disabled>
                                                <i class="ti-trash me-2"></i>Delete Account
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3" style="border-radius: 12px;">
                                    <div class="card-body p-4">
                                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                            <i class="ti-info-alt me-2 text-info"></i>Account Status
                                        </h6>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Account Type</span>
                                                <span class="badge bg-primary">Facility Staff</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Status</span>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Member Since</span>
                                                <span
                                                    class="text-muted">{{ Auth::guard('web')->user()->created_at->format('M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings Tab -->
                    <div class="tab-pane fade" id="system" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-header bg-white border-bottom" style="padding: 1.5rem;">
                                        <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                            <i class="ti-settings me-2 text-primary"></i>System Preferences
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <label class="form-label fw-semibold text-dark mb-3">
                                                    <i class="ti-world me-2 text-primary"></i>Language & Region
                                                </label>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Language</label>
                                                    <select class="form-select" disabled>
                                                        <option selected>English</option>
                                                        <option>Hausa</option>
                                                        <option>Yoruba</option>
                                                        <option>Igbo</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Time Zone</label>
                                                    <select class="form-select" disabled>
                                                        <option selected>West Africa Time (WAT)</option>
                                                        <option>Greenwich Mean Time (GMT)</option>
                                                        <option>Central European Time (CET)</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label text-muted">Date Format</label>
                                                    <select class="form-select" disabled>
                                                        <option selected>MM/DD/YYYY</option>
                                                        <option>DD/MM/YYYY</option>
                                                        <option>YYYY-MM-DD</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-4">
                                                <label class="form-label fw-semibold text-dark mb-3">
                                                    <i class="ti-palette me-2 text-primary"></i>Appearance
                                                </label>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Theme</label>
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" class="btn-check" name="theme"
                                                            id="light" checked disabled>
                                                        <label class="btn btn-outline-primary"
                                                            for="light">Light</label>
                                                        <input type="radio" class="btn-check" name="theme"
                                                            id="dark" disabled>
                                                        <label class="btn btn-outline-primary" for="dark">Dark</label>
                                                        <input type="radio" class="btn-check" name="theme"
                                                            id="auto" disabled>
                                                        <label class="btn btn-outline-primary" for="auto">Auto</label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Font Size</label>
                                                    <select class="form-select" disabled>
                                                        <option>Small</option>
                                                        <option selected>Medium</option>
                                                        <option>Large</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label text-muted">Dashboard Layout</label>
                                                    <select class="form-select" disabled>
                                                        <option selected>Default</option>
                                                        <option>Compact</option>
                                                        <option>Detailed</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-info border-0"
                                            style="background-color: #e8f5e9; border-radius: 8px;">
                                            <div class="d-flex align-items-center">
                                                <i class="ti-info-alt me-3 text-primary" style="font-size: 1.25rem;"></i>
                                                <div>
                                                    <strong>System Preferences</strong>
                                                    <p class="mb-0 text-muted">These settings will be available in a future
                                                        update. Contact your administrator for any changes.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" disabled>
                                            <i class="ti-save me-2"></i>Save Preferences
                                            <span class="badge bg-light text-dark ms-2">Coming Soon</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-4">
                                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                            <i class="ti-download me-2 text-primary"></i>Data Management
                                        </h6>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-success" disabled>
                                                <i class="ti-file-text me-2"></i>Export Profile Data
                                            </button>
                                            <button class="btn btn-outline-info" disabled>
                                                <i class="ti-archive me-2"></i>Download Archive
                                            </button>
                                            <button class="btn btn-outline-warning" disabled>
                                                <i class="ti-refresh me-2"></i>Sync Settings
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3" style="border-radius: 12px;">
                                    <div class="card-body p-4">
                                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                            <i class="ti-info-alt me-2 text-info"></i>System Information
                                        </h6>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Portal Version</span>
                                                <span class="text-muted">v1.0.0</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Last Updated</span>
                                                <span class="text-muted">{{ now()->format('M d, Y') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Browser</span>
                                                <span class="text-muted">Modern</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">IP Address</span>
                                                <span class="text-muted">{{ request()->ip() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Tab -->
                    <div class="tab-pane fade" id="support" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-header bg-white border-bottom" style="padding: 1.5rem;">
                                        <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                            <i class="ti-help-alt me-2 text-primary"></i>Get Help & Support
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 mb-4">
                                                <div class="text-center p-3"
                                                    style="background: #f8f9fa; border-radius: 12px;">
                                                    <div class="avatar avatar-lg bg-primary text-white mb-3"
                                                        style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                        <i class="ti-headphone-alt" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-2">Support Hotline</h6>
                                                    <p class="text-muted mb-2">+234 800-000-0000</p>
                                                    <small class="text-success">Available 24/7</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-4">
                                                <div class="text-center p-3"
                                                    style="background: #f8f9fa; border-radius: 12px;">
                                                    <div class="avatar avatar-lg bg-success text-white mb-3"
                                                        style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                        <i class="ti-email" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-2">Email Support</h6>
                                                    <p class="text-muted mb-2">support@boschma.gov.ng</p>
                                                    <small class="text-info">Response within 24hrs</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-4">
                                                <div class="text-center p-3"
                                                    style="background: #f8f9fa; border-radius: 12px;">
                                                    <div class="avatar avatar-lg bg-info text-white mb-3"
                                                        style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                        <i class="ti-book" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-2">User Manual</h6>
                                                    <button class="btn btn-sm btn-outline-info" disabled>
                                                        <i class="ti-download me-1"></i>Download PDF
                                                    </button>
                                                    <small class="text-muted d-block mt-1">Latest version</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- FAQ Section -->
                                        <div class="mt-4">
                                            <h6 class="fw-bold mb-3" style="color: #01542B;">Frequently Asked Questions
                                            </h6>
                                            <div class="accordion" id="faqAccordion">
                                                <div class="accordion-item border mb-2"
                                                    style="border-radius: 8px; overflow: hidden;">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#faq1"
                                                            style="background: #f8f9fa; font-size: 0.9rem;">
                                                            How do I reset my password?
                                                        </button>
                                                    </h2>
                                                    <div id="faq1" class="accordion-collapse collapse"
                                                        data-bs-parent="#faqAccordion">
                                                        <div class="accordion-body p-3">
                                                            <small class="text-muted">Click on "Change Password" in the
                                                                Account Settings tab. You'll receive an email with
                                                                instructions to reset your password.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-item border mb-2"
                                                    style="border-radius: 8px; overflow: hidden;">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#faq2"
                                                            style="background: #f8f9fa; font-size: 0.9rem;">
                                                            How do I update my facility information?
                                                        </button>
                                                    </h2>
                                                    <div id="faq2" class="accordion-collapse collapse"
                                                        data-bs-parent="#faqAccordion">
                                                        <div class="accordion-body p-3">
                                                            <small class="text-muted">Contact your system administrator to
                                                                update facility information. This feature will be available
                                                                in a future update.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-item border"
                                                    style="border-radius: 8px; overflow: hidden;">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#faq3"
                                                            style="background: #f8f9fa; font-size: 0.9rem;">
                                                            What browsers are supported?
                                                        </button>
                                                    </h2>
                                                    <div id="faq3" class="accordion-collapse collapse"
                                                        data-bs-parent="#faqAccordion">
                                                        <div class="accordion-body p-3">
                                                            <small class="text-muted">We support the latest versions of
                                                                Chrome, Firefox, Safari, and Edge. For the best experience,
                                                                please keep your browser updated.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-4">
                                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                            <i class="ti-headphone-alt me-2 text-primary"></i>Contact Support
                                        </h6>
                                        <form>
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Subject</label>
                                                <select class="form-select" disabled>
                                                    <option selected>General Inquiry</option>
                                                    <option>Technical Issue</option>
                                                    <option>Account Problem</option>
                                                    <option>Feature Request</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Message</label>
                                                <textarea class="form-control" rows="4" placeholder="Describe your issue..." disabled></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" disabled>
                                                <i class="ti-send me-2"></i>Send Message
                                                <span class="badge bg-light text-dark ms-2">Coming Soon</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3" style="border-radius: 12px;">
                                    <div class="card-body p-4">
                                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                            <i class="ti-time me-2 text-info"></i>Support Hours
                                        </h6>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Monday - Friday</span>
                                                <span class="text-muted">8:00 AM - 6:00 PM</span>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Saturday</span>
                                                <span class="text-muted">9:00 AM - 4:00 PM</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Sunday</span>
                                                <span class="text-muted">Emergency Only</span>
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
    </div>
@endsection
