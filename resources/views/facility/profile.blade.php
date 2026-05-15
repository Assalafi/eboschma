@extends('layouts.facility')

@section('title', 'My Profile')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">My Profile</h1>
                        <p class="text-muted mb-0">Manage your personal information and account settings</p>
                    </div>
                    <div>
                        <a href="{{ route('facility.dashboard') }}" class="btn btn-outline-secondary me-2">
                            <i class="ti-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <button class="btn btn-primary" disabled>
                            <i class="ti-printer me-1"></i> Print Profile
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Profile Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-body text-center p-4">
                                <div class="position-relative d-inline-block mb-3">
                                    <img src="{{ url('assets/img/faces/9.jpg') }}" class="avatar avatar-xl brround"
                                        alt="Profile" style="width: 120px; height: 120px; border: 4px solid #f8f9fa;">
                                    <div class="position-absolute bottom-0 end-0">
                                        <span class="badge bg-success"
                                            style="width: 20px; height: 20px; border-radius: 50%; display: inline-block;"></span>
                                    </div>
                                </div>
                                <h5 class="mb-2 fw-bold" style="color: #01542B;">{{ Auth::guard('web')->user()->name }}</h5>
                                <p class="text-muted mb-3">{{ Auth::guard('web')->user()->email }}</p>

                                @if (Auth::guard('web')->user()->facility)
                                    <div class="badge bg-primary mb-3 px-3 py-2" style="font-size: 0.875rem;">
                                        <i class="ti-map-pin me-1"></i> {{ Auth::guard('web')->user()->facility->name }}
                                    </div>
                                @endif

                                <div class="d-grid gap-2 mt-4">
                                    <button class="btn btn-primary" disabled style="border-radius: 8px;">
                                        <i class="ti-camera me-2"></i> Change Photo
                                        <span class="badge bg-light text-dark ms-auto">Coming Soon</span>
                                    </button>
                                    <button class="btn btn-outline-primary" disabled style="border-radius: 8px;">
                                        <i class="ti-lock me-2"></i> Change Password
                                        <span class="badge bg-light text-dark ms-auto">Coming Soon</span>
                                    </button>
                                </div>

                                <!-- Quick Stats -->
                                <div class="mt-4 pt-4 border-top">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="fw-bold text-primary">
                                                {{ Auth::guard('web')->user()->created_at->format('Y') }}</div>
                                            <div class="small text-muted">Member Since</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-success">Active</div>
                                            <div class="small text-muted">Status</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-info">
                                                {{ Auth::guard('web')->user()->facility?->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">Facility</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom" style="padding: 1.25rem;">
                                <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                    <i class="ti-user me-2 text-primary"></i>Personal Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-dark">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0">
                                                    <i class="ti-user text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0"
                                                    style="border-left: none;"
                                                    value="{{ Auth::guard('web')->user()->name }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-dark">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0">
                                                    <i class="ti-email text-primary"></i>
                                                </span>
                                                <input type="email" class="form-control border-start-0"
                                                    style="border-left: none;"
                                                    value="{{ Auth::guard('web')->user()->email }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-dark">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0">
                                                    <i class="ti-mobile text-primary"></i>
                                                </span>
                                                <input type="tel" class="form-control border-start-0"
                                                    style="border-left: none;"
                                                    value="{{ Auth::guard('web')->user()->phone ?? 'Not provided' }}"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-dark">Facility</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0">
                                                    <i class="ti-map-pin text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0"
                                                    style="border-left: none;"
                                                    value="{{ Auth::guard('web')->user()->facility->name ?? 'No facility assigned' }}"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-dark">Staff Position</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0">
                                                    <i class="ti-briefcase text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0"
                                                    style="border-left: none;"
                                                    value="{{ Auth::guard('web')->user()->staffPosition->name ?? 'Not assigned' }}"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-dark">Date Joined</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0">
                                                    <i class="ti-calendar text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0"
                                                    style="border-left: none;"
                                                    value="{{ Auth::guard('web')->user()->created_at->format('M d, Y') }}"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info border-0"
                                        style="background-color: #e8f5e9; border-radius: 8px;">
                                        <div class="d-flex align-items-center">
                                            <i class="ti-info-alt me-3 text-primary" style="font-size: 1.25rem;"></i>
                                            <div>
                                                <strong>Profile Management</strong>
                                                <p class="mb-0 text-muted">Profile editing will be available in a future
                                                    update. Contact your administrator for any changes.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div class="text-muted small">
                                            <i class="ti-lock me-1"></i> Your information is secure and encrypted
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary me-2" disabled>
                                                <i class="ti-download me-1"></i> Export Data
                                            </button>
                                            <button type="button" class="btn btn-primary" disabled>
                                                <i class="ti-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Cards -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-shield me-2 text-success"></i>Security Settings
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Two-Factor Authentication</span>
                                    <span class="badge bg-warning">Disabled</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Last Login</span>
                                    <span class="text-muted">{{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Password Status</span>
                                    <span class="badge bg-success">Strong</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-bell me-2 text-primary"></i>Notification Preferences
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Email Notifications</span>
                                    <span class="badge bg-success">Enabled</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">System Alerts</span>
                                    <span class="badge bg-success">Enabled</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Marketing Emails</span>
                                    <span class="badge bg-secondary">Disabled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
