@extends('layouts.facility')

@section('title', 'Facility Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="page-title mb-1">Welcome, {{ $user->name }}!</h4>
                                <p class="text-muted mb-0">
                                    @if ($facility)
                                        {{ $facility->name }} • {{ $facility->type ?? 'Healthcare Facility' }}
                                    @else
                                        No facility assigned
                                    @endif
                                </p>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Last login</small>
                                <strong>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'First time login' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="fe fe-file-text" style="font-size: 2.5rem;"></i>
                        </div>
                        <h3 class="card-title mb-1">{{ $stats['total_encounters'] }}</h3>
                        <p class="card-text text-muted">Total Encounters</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-warning mb-3">
                            <i class="fe fe-shuffle" style="font-size: 2.5rem;"></i>
                        </div>
                        <h3 class="card-title mb-1">{{ $stats['total_referrals'] }}</h3>
                        <p class="card-text text-muted">Total Referrals</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-success mb-3">
                            <i class="fe fe-users" style="font-size: 2.5rem;"></i>
                        </div>
                        <h3 class="card-title mb-1">{{ $stats['unique_patients'] }}</h3>
                        <p class="card-text text-muted">Patients</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-info mb-3">
                            <i class="fe fe-activity" style="font-size: 2.5rem;"></i>
                        </div>
                        <h3 class="card-title mb-1">{{ $stats['active_today'] }}</h3>
                        <p class="card-text text-muted">Active Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beneficiary Stats Row -->
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #01542B;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-primary">
                                <i class="fe fe-user-check" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-0">{{ $stats['total_beneficiaries'] }}</h3>
                                <p class="card-text text-muted mb-0">Enrolled Beneficiaries</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #17a2b8;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-info">
                                <i class="fe fe-heart" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-0">{{ $stats['total_spouses'] }}</h3>
                                <p class="card-text text-muted mb-0">Spouses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-success">
                                <i class="fe fe-smile" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-0">{{ $stats['total_children'] }}</h3>
                                <p class="card-text text-muted mb-0">Children</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activities -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-zap me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('facility.referrals.index') }}" class="btn btn-outline-primary">
                                <i class="fe fe-shuffle me-2"></i>View Referrals
                                <span class="badge bg-primary ms-auto">{{ $stats['total_referrals'] }}</span>
                            </a>
                            <a href="{{ route('facility.claims.list') }}" class="btn btn-outline-success">
                                <i class="fe fe-file-text me-2"></i>View Claims
                                <span class="badge bg-success ms-auto">{{ $stats['total_claims'] }}</span>
                            </a>
                            <a href="{{ route('facility.referrals.index') }}" class="btn btn-outline-warning">
                                <i class="fe fe-clock me-2"></i>Pending Referrals
                                <span class="badge bg-warning ms-auto">{{ $stats['pending_referrals'] }}</span>
                            </a>
                            <a href="{{ route('facility.claims.list') }}" class="btn btn-outline-info">
                                <i class="fe fe-alert-circle me-2"></i>Pending Claims
                                <span class="badge bg-info ms-auto">{{ $stats['pending_claims'] }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-clock me-2"></i>Recent Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (empty($stats['recent_activities']))
                            <div class="text-center py-5">
                                <i class="fe fe-inbox text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No Recent Activities</h5>
                                <p class="text-muted">Your activities will appear here once you start using the system.</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($stats['recent_activities'] as $activity)
                                    @if ($activity['url'])
                                        <a href="{{ $activity['url'] }}"
                                            class="list-group-item list-group-item-action border-0 py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3">
                                                    <span
                                                        class="avatar avatar-sm bg-{{ $activity['color'] }}-lt rounded-circle">
                                                        <i
                                                            class="fe {{ $activity['icon'] }} text-{{ $activity['color'] }}"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                                    <p class="text-muted mb-1 small">{{ $activity['description'] }}</p>
                                                    <small class="text-muted">
                                                        <i class="fe fe-clock me-1"></i>
                                                        {{ $activity['time']->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3">
                                                    <span
                                                        class="avatar avatar-sm bg-{{ $activity['color'] }}-lt rounded-circle">
                                                        <i
                                                            class="fe {{ $activity['icon'] }} text-{{ $activity['color'] }}"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                                    <p class="text-muted mb-1 small">{{ $activity['description'] }}</p>
                                                    <small class="text-muted">
                                                        <i class="fe fe-clock me-1"></i>
                                                        {{ $activity['time']->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Cards -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-info me-2"></i>System Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Portal Version</small>
                                <strong>v1.0.0</strong>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">User Role</small>
                                <strong>Facility Staff</strong>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Account Status</small>
                                <strong class="text-success">Active</strong>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Email Verified</small>
                                <strong class="text-success">{{ $user->email_verified_at ? 'Yes' : 'No' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-help-circle me-2"></i>Need Help?
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-primary text-white me-3">
                                <i class="fe fe-phone"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Support Hotline</h6>
                                <p class="text-muted mb-0">+234 800-000-0000</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-success text-white me-3">
                                <i class="fe fe-mail"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Email Support</h6>
                                <p class="text-muted mb-0">support@boschma.gov.ng</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-info text-white me-3">
                                <i class="fe fe-book"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">User Manual</h6>
                                <p class="text-muted mb-0">
                                    <a href="#" class="text-decoration-none">Download Guide</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: 8px;
            top: 20px;
            bottom: -20px;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item:last-child:before {
            display: none;
        }

        .timeline-point {
            position: absolute;
            left: 0;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e9ecef;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
@endsection
