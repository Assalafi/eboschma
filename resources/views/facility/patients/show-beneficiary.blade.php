@extends('layouts.facility')

@section('title', 'Beneficiary Details')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Beneficiary
                            Details</h1>
                        <p class="text-muted mb-0">View enrolled beneficiary information</p>
                    </div>
                    <div>
                        <a href="{{ route('facility.patients.index', ['tab' => 'beneficiaries']) }}"
                            class="btn btn-outline-secondary me-2">
                            <i class="ti-arrow-left me-1"></i> Back to Beneficiaries
                        </a>
                        <button class="btn btn-primary me-2" onclick="window.print()">
                            <i class="ti-printer me-1"></i> Print Record
                        </button>
                        <button class="btn btn-success" disabled>
                            <i class="ti-download me-1"></i> Export PDF
                        </button>
                    </div>
                </div>

                @php
                    $name =
                        $patient->enrollee_type === 'beneficiary'
                            ? $patient->fullname
                            : ($patient->enrollee_type === 'child'
                                ? $patient->name
                                : $patient->name);
                    $photo =
                        $patient->enrollee_type === 'beneficiary'
                            ? $patient->photo
                            : ($patient->enrollee_type === 'child'
                                ? $patient->photo
                                : $patient->photo);
                    $gender =
                        $patient->enrollee_type === 'beneficiary'
                            ? $patient->gender
                            : ($patient->enrollee_type === 'child'
                                ? $patient->gender
                                : $patient->gender);
                    $dob =
                        $patient->enrollee_type === 'beneficiary'
                            ? $patient->date_of_birth
                            : ($patient->enrollee_type === 'child'
                                ? $patient->dob
                                : $patient->dob);
                @endphp

                <div class="row">
                    <!-- Patient Profile Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-body text-center p-4">
                                <div class="position-relative d-inline-block mb-3">
                                    @if ($photo)
                                        <img src="{{ asset('storage/' . $photo) }}" class="avatar avatar-xl brround"
                                            alt="Patient Photo"
                                            style="width: 120px; height: 120px; border: 4px solid #f8f9fa; object-fit: cover;">
                                    @else
                                        <div class="avatar avatar-xl bg-light text-white brround d-flex align-items-center justify-content-center"
                                            style="width: 120px; height: 120px; border: 4px solid #f8f9fa;">
                                            <i class="ti-user text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    <div class="position-absolute bottom-0 end-0">
                                        @if ($patient->enrollee_type === 'beneficiary')
                                            <span class="badge bg-primary" style="font-size: 0.75rem;">Primary</span>
                                        @else
                                            <span class="badge bg-success" style="font-size: 0.75rem;">Dependant</span>
                                        @endif
                                    </div>
                                </div>
                                <h5 class="mb-2 fw-bold" style="color: #01542B;">{{ $name }}</h5>
                                <p class="text-muted mb-3">{{ $patient->enrollee_number }}</p>

                                <div class="badge bg-primary mb-3 px-3 py-2" style="font-size: 0.875rem;">
                                    @if ($patient->enrollee_type === 'beneficiary')
                                        <i class="ti-user me-1"></i> Beneficiary
                                    @elseif($patient->enrollee_type === 'child')
                                        <i class="ti-face-smile me-1"></i> Child
                                    @else
                                        <i class="ti-heart me-1"></i> Spouse
                                    @endif
                                </div>

                                <!-- Quick Info -->
                                <div class="mt-4 pt-4 border-top">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="fw-bold text-primary">{{ $gender ?? 'N/A' }}</div>
                                            <div class="small text-muted">Gender</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-success">
                                                @if ($dob)
                                                    {{ \Carbon\Carbon::parse($dob)->age }} yrs
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                            <div class="small text-muted">Age</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-info">{{ $patient->facility_name ?? 'N/A' }}</div>
                                            <div class="small text-muted">Facility</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Card -->
                        <div class="card border-0 shadow-sm mt-3" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-info-alt me-2 text-info"></i>Registration Status
                                </h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Status</span>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Program</span>
                                        <span class="text-muted">{{ $patient->program_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Registered</span>
                                        <span class="text-muted">{{ $patient->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="col-lg-8 mb-4">
                        <!-- Personal Information -->
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom" style="padding: 1.25rem;">
                                <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                    <i class="ti-user me-2 text-primary"></i>Personal Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Full Name</label>
                                        <p class="fw-semibold mb-0">{{ $name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">BOSCHMA ID</label>
                                        <p class="fw-semibold mb-0 text-primary">{{ $patient->enrollee_number }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Gender</label>
                                        <p class="mb-0">{{ $gender ?? 'Not specified' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Date of Birth</label>
                                        <p class="mb-0">
                                            @if ($dob)
                                                {{ \Carbon\Carbon::parse($dob)->format('M d, Y') }}
                                                ({{ \Carbon\Carbon::parse($dob)->age }} years)
                                            @else
                                                Not specified
                                            @endif
                                        </p>
                                    </div>
                                    @if ($patient->enrollee_type === 'beneficiary')
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Place of Birth</label>
                                            <p class="mb-0">{{ $patient->place_of_birth ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Marital Status</label>
                                            <p class="mb-0">{{ $patient->marital_status ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Nationality</label>
                                            <p class="mb-0">{{ $patient->nationality ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Religion</label>
                                            <p class="mb-0">{{ $patient->religion ?? 'Not specified' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom" style="padding: 1.25rem;">
                                <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                    <i class="ti-phone me-2 text-primary"></i>Contact Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    @if ($patient->enrollee_type === 'beneficiary')
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Phone Number</label>
                                            <p class="mb-0">{{ $patient->phone_no ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Email Address</label>
                                            <p class="mb-0">{{ $patient->email ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label text-muted small">Contact Address</label>
                                            <p class="mb-0">{{ $patient->contact_address ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Place of Work</label>
                                            <p class="mb-0">{{ $patient->place_of_work ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Occupation</label>
                                            <p class="mb-0">{{ $patient->occupation ?? 'Not specified' }}</p>
                                        </div>
                                    @endif
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">LGA</label>
                                        <p class="mb-0">{{ $patient->lga ?? 'Not specified' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">State</label>
                                        <p class="mb-0">{{ $patient->state ?? 'Not specified' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Identification -->
                        @if ($patient->enrollee_type === 'beneficiary')
                            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                                <div class="card-header bg-white border-bottom" style="padding: 1.25rem;">
                                    <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                        <i class="ti-id-badge me-2 text-primary"></i>Identification
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">NIN</label>
                                            <p class="mb-0">{{ $patient->nin ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">ID Type</label>
                                            <p class="mb-0">{{ $patient->id_type ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">ID Number</label>
                                            <p class="mb-0">{{ $patient->id_no ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">DP Number</label>
                                            <p class="mb-0">{{ $patient->dp_no ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Date of Employment</label>
                                            <p class="mb-0">
                                                @if ($patient->date_of_employment)
                                                    {{ \Carbon\Carbon::parse($patient->date_of_employment)->format('M d, Y') }}
                                                @else
                                                    Not specified
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Date of Retirement</label>
                                            <p class="mb-0">
                                                @if ($patient->date_of_retirement)
                                                    {{ \Carbon\Carbon::parse($patient->date_of_retirement)->format('M d, Y') }}
                                                @else
                                                    Not specified
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Child Specific Information -->
                        @if ($patient->enrollee_type === 'child')
                            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                                <div class="card-header bg-white border-bottom" style="padding: 1.25rem;">
                                    <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                        <i class="ti-file me-2 text-primary"></i>Birth Certificate
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Birth Certificate Number</label>
                                            <p class="mb-0">{{ $patient->birth_certificate_no ?? 'Not specified' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Birth Certificate File</label>
                                            @if ($patient->birth_certificate_file)
                                                <a href="{{ asset('storage/' . $patient->birth_certificate_file) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti-eye me-1"></i> View Document
                                                </a>
                                            @else
                                                <p class="mb-0 text-muted">No document uploaded</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Family Members (for beneficiaries) -->
                @if ($patient->enrollee_type === 'beneficiary' && !empty($familyMembers))
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-white border-bottom" style="padding: 1.25rem;">
                                    <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                                        <i class="ti-family me-2 text-primary"></i>Family Members
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        @foreach ($familyMembers as $member)
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-center p-3"
                                                    style="background: #f8f9fa; border-radius: 8px;">
                                                    @if ($member['photo'])
                                                        <img src="{{ asset('storage/' . $member['photo']) }}"
                                                            class="avatar avatar-md rounded-circle me-3"
                                                            style="width: 48px; height: 48px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar avatar-md rounded-circle bg-light me-3 d-flex align-items-center justify-content-center"
                                                            style="width: 48px; height: 48px;">
                                                            <i class="ti-user text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold">{{ $member['name'] }}</div>
                                                        <div class="text-muted small">{{ $member['boschma_no'] }}</div>
                                                        <div class="text-muted small">
                                                            {{ $member['gender'] }} •
                                                            @if ($member['dob'])
                                                                {{ \Carbon\Carbon::parse($member['dob'])->age }} years
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>
                                                        @if ($member['type'] === 'spouse')
                                                            <span class="badge bg-info">Spouse</span>
                                                        @else
                                                            <span class="badge bg-success">Child</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .avatar {
            object-fit: cover;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .card {
            border: none;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
        }

        @media print {

            .btn,
            .btn-group {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .page-title {
                font-size: 20px !important;
            }
        }
    </style>
@endsection
