@extends('layouts.app')

@section('title', 'Referral Details - REF-' . $referral->id)

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Referral Details</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti-home mr-1"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('referrals.index') }}">Referrals</a></li>
                    <li class="breadcrumb-item active" aria-current="page">REF-{{ $referral->id }}</li>
                </ol>
            </div>
            <div class="page-rightheader">
                <div class="btn-list">
                    @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'))
                        @if($referral->approval_status === 'pending')
                            <form action="{{ route('referrals.approve', $referral->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this referral?');">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="ti-check mr-1"></i> Approve
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="ti-close mr-1"></i> Reject
                            </button>
                        @endif
                    @endif
                    
                    <a href="{{ route('referrals.pdf', $referral->id) }}" class="btn btn-primary" target="_blank">
                        <i class="ti-download mr-1"></i> Download PDF
                    </a>
                    
                    <button type="button" class="btn btn-info" onclick="window.print()">
                        <i class="ti-printer mr-1"></i> Print
                    </button>
                    
                    <a href="{{ route('referrals.index') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('referrals.reject', $referral->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Referral</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="ti-check mr-2"></i>Success!</strong>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">REF-{{ $referral->id }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            @if ($isOutgoing)
                                <span class="badge bg-white text-primary">📤 Outgoing</span>
                            @else
                                <span class="badge bg-white text-primary">📥 Incoming</span>
                            @endif
                            {!! $referral->status_badge !!}
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="referralTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="patient-tab" data-bs-toggle="tab"
                                    data-bs-target="#patient" type="button" role="tab" aria-controls="patient"
                                    aria-selected="true">
                                    👤 Patient Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="non-priced-tab" data-bs-toggle="tab"
                                    data-bs-target="#non-priced" type="button" role="tab" aria-controls="non-priced"
                                    aria-selected="false">
                                    📋 Clinical Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="priced-tab" data-bs-toggle="tab" data-bs-target="#priced"
                                    type="button" role="tab" aria-controls="priced" aria-selected="false">
                                    💰 Claimed
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="referralTabsContent">
                            <!-- TAB 1: Patient Info -->
                            <div class="tab-pane fade show active" id="patient" role="tabpanel"
                                aria-labelledby="patient-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">Patient Information</h5>
                                            </div>
                                            <div class="card-body">
                                                @if ($referral->encounter && $referral->encounter->patient)
                                                    @php
                                                        $patient = $referral->encounter->patient;
                                                        $enrolleeDetails = $patient->enrolleeDetails;
                                                    @endphp
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th width="40%">Name:</th>
                                                            <td>{{ $enrolleeDetails->fullname ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Enrollee Number:</th>
                                                            <td>{{ $patient->enrollee_number }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>File Number:</th>
                                                            <td>{{ $patient->file_number ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Gender:</th>
                                                            <td>{{ ucfirst($enrolleeDetails->gender ?? 'N/A') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Phone:</th>
                                                            <td>{{ $enrolleeDetails->phone_no ?? ($enrolleeDetails->phone ?? 'N/A') }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                @else
                                                    <p class="text-muted">Patient information not available.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Referral Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="40%">Referral Type:</th>
                                                        <td><span
                                                                class="badge bg-primary text-white">{{ $referral->getReferralTypeLabel() }}</span>
                                                        </td>
                                                    </tr>
                                                    @if ($referral->serviceItem)
                                                        <tr>
                                                            <th>Service:</th>
                                                            <td>
                                                                <strong>{{ $referral->serviceItem->name }}</strong>
                                                                <br>
                                                                <small
                                                                    class="text-muted">{{ $referral->serviceItem->type ?? 'Service' }}</small>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th>From Facility:</th>
                                                        <td>{{ $referral->fromFacility->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>To Facility:</th>
                                                        <td>{{ $referral->toFacility->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status:</th>
                                                        <td>
                                                            {!! $referral->status_badge !!}
                                                            @if($referral->approval_status === 'approved')
                                                                <span class="badge bg-success ms-1">Approved</span>
                                                            @elseif($referral->approval_status === 'rejected')
                                                                <span class="badge bg-danger ms-1">Rejected</span>
                                                            @else
                                                                <span class="badge bg-warning ms-1">Pending Approval</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Created:</th>
                                                        <td>{{ $referral->created_at->format('d M Y H:i') }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Authorization Information -->
                                    @if ($referral->authorization)
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header bg-warning text-dark">
                                                    <h5 class="mb-0">🔐 Authorization Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th width="25%">Authorization Code:</th>
                                                            <td><strong
                                                                    class="text-primary">{{ $referral->authorization->authorization_code }}</strong>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Generated By:</th>
                                                            <td>{{ $referral->authorization->approver->name ?? 'System' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Status:</th>
                                                            <td><span class="badge bg-success">Auto-generated</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Expires At:</th>
                                                            <td>
                                                                @if ($referral->authorization->expires_at)
                                                                    <span
                                                                        class="{{ $referral->authorization->isExpired() ? 'text-danger' : 'text-success' }}">
                                                                        {{ $referral->authorization->expires_at->format('d M Y H:i') }}
                                                                        @if ($referral->authorization->isExpired())
                                                                            <span
                                                                                class="badge bg-danger ms-2">EXPIRED</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-success ms-2">VALID</span>
                                                                        @endif
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">No expiration</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Status:</th>
                                                            <td>
                                                                @if ($referral->authorization->isValid())
                                                                    <span class="badge bg-success">✓ Valid</span>
                                                                @else
                                                                    <span class="badge bg-danger">✗ Expired</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Referral Reason -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">📝 Reason for Referral</h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0">{{ $referral->reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: Clinical Details -->
                            <div class="tab-pane fade" id="non-priced" role="tabpanel" aria-labelledby="non-priced-tab">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <!-- Vital Signs -->
                                        @if (count($vitalSigns) > 0)
                                            <h5 class="mb-3">❤️ Vital Signs</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Date & Time</th>
                                                            <th>Temperature (°C)</th>
                                                            <th>Blood Pressure</th>
                                                            <th>Pulse (bpm)</th>
                                                            <th>Respiration (bpm)</th>
                                                            <th>SpO2 (%)</th>
                                                            <th>Weight (kg)</th>
                                                            <th>Height (cm)</th>
                                                            <th>BMI</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($vitalSigns as $index => $vital)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($vital->created_at)->format('d M Y H:i') }}
                                                                </td>
                                                                <td>{{ $vital->temperature ?? 'N/A' }}</td>
                                                                <td>{{ $vital->blood_pressure_systolic ?? 'N/A' }}/{{ $vital->blood_pressure_diastolic ?? 'N/A' }}
                                                                </td>
                                                                <td>{{ $vital->pulse_rate ?? 'N/A' }}</td>
                                                                <td>{{ $vital->respiration_rate ?? 'N/A' }}</td>
                                                                <td>{{ $vital->spo2 ?? 'N/A' }}</td>
                                                                <td>{{ $vital->weight ?? 'N/A' }}</td>
                                                                <td>{{ $vital->height ?? 'N/A' }}</td>
                                                                <td>{{ $vital->bmi ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">No vital signs recorded for this encounter.
                                            </div>
                                        @endif

                                        <!-- Consultations with Grouped Diagnoses -->
                                        @if ($consultations->count() > 0)
                                            <h5 class="mb-3 mt-4">👨‍⚕️ Clinical Consultations</h5>
                                            <div class="mb-4">
                                                @foreach ($consultations as $index => $consultation)
                                                    <div class="card mb-3">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0">Consultation #{{ $index + 1 }}</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1"><strong>Presenting
                                                                            Complaints:</strong>
                                                                        {{ $consultation->presenting_complaints ?? 'N/A' }}
                                                                    </p>
                                                                    <p class="mb-1"><strong>Clinical Notes:</strong>
                                                                        {{ $consultation->clinical_note ?? 'N/A' }}
                                                                    </p>
                                                                    <p class="mb-0">
                                                                        <strong>Status:</strong> <span
                                                                            class="badge bg-success">{{ ucfirst($consultation->status) }}</span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <!-- Diagnoses for this consultation -->
                                                                    @if ($consultation->diagnoses && $consultation->diagnoses->count() > 0)
                                                                        <h6 class="mb-2">🩺 Diagnoses:</h6>
                                                                        <div class="table-responsive">
                                                                            <table class="table table-sm table-bordered">
                                                                                <thead class="bg-light">
                                                                                    <tr>
                                                                                        <th>#</th>
                                                                                        <th>ICD Code</th>
                                                                                        <th>Description</th>
                                                                                        <th>Type</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach ($consultation->diagnoses as $diagIndex => $diagnosis)
                                                                                        <tr>
                                                                                            <td>{{ $diagIndex + 1 }}</td>
                                                                                            <td>{{ $diagnosis->icdCode->code ?? 'N/A' }}
                                                                                            </td>
                                                                                            <td>{{ $diagnosis->icdCode->description ?? 'N/A' }}
                                                                                            </td>
                                                                                            <td><span
                                                                                                    class="badge bg-info">{{ ucfirst($diagnosis->diagnosis_type ?? 'primary') }}</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @else
                                                                        <p class="mb-0 text-muted">No diagnoses recorded
                                                                            for this consultation.</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-warning">No consultations recorded for this encounter.
                                            </div>
                                        @endif

                                        <!-- Encounter Actions -->
                                        @if ($actions->count() > 0)
                                            <h5 class="mb-3 mt-4">⚡ Encounter Actions</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Action Type</th>
                                                            <th>Description</th>
                                                            <th>Time</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($actions as $index => $action)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td><span
                                                                        class="badge bg-info">{{ ucfirst($action->action_type) }}</span>
                                                                </td>
                                                                <td>{{ $action->description }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($action->action_time)->format('d M Y H:i') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: Claimed -->
                            <div class="tab-pane fade" id="priced" role="tabpanel" aria-labelledby="priced-tab">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <!-- Medications/Pharmacy -->
                                        @if (count($medications) > 0)
                                            <h5 class="mb-3">💊 Medications (Pharmacy)</h5>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Drug Name</th>
                                                            <th>Dosage</th>
                                                            <th>Quantity</th>
                                                            <th>Duration (days)</th>
                                                            <th>Status</th>
                                                            <th>Cost (₦)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($medications as $index => $med)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $med['drug']->name ?? 'N/A' }}</td>
                                                                <td>{{ $med['item']->dosage ?? 'N/A' }}</td>
                                                                <td>{{ $med['item']->quantity ?? 0 }}</td>
                                                                <td>{{ $med['item']->duration ?? 0 }}</td>
                                                                <td>
                                                                    <span
                                                                        class="badge bg-{{ $med['dispensing_status'] === 'dispensed' ? 'success' : 'warning' }}">
                                                                        {{ ucfirst($med['dispensing_status']) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $med['cost'] > 0 ? '₦' . number_format($med['cost'], 2) : '₦0.00' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="6" class="text-right">Pharmacy Total:</th>
                                                            <th>₦{{ number_format($pharmacyTotal, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">No medications dispensed for this encounter.
                                            </div>
                                        @endif

                                        <!-- Laboratory Tests -->
                                        @if (count($laboratoryTests) > 0)
                                            <h5 class="mb-3">🔬 Laboratory Tests</h5>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Test Name</th>
                                                            <th>Description</th>
                                                            <th>Status</th>
                                                            <th>Price (₦)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($laboratoryTests as $index => $lab)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $lab['service']->name ?? 'N/A' }}</td>
                                                                <td>{{ $lab['service']->description ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge bg-{{ $lab['status'] === 'completed' ? 'success' : 'warning' }}">
                                                                        {{ ucfirst($lab['status']) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $lab['price'] > 0 ? '₦' . number_format($lab['price'], 2) : '₦0.00' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="4" class="text-right">Laboratory Total:</th>
                                                            <th>₦{{ number_format($labTotal, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @endif

                                        <!-- Other Services -->
                                        @if (count($services) > 0)
                                            <h5 class="mb-3">🏥 Other Services</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Service Name</th>
                                                            <th>Type</th>
                                                            <th>Description</th>
                                                            <th>Status</th>
                                                            <th>Price (₦)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($services as $index => $srv)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $srv['service']->name ?? 'N/A' }}</td>
                                                                <td><span
                                                                        class="badge bg-primary">{{ ucfirst($srv['service']->type ?? 'N/A') }}</span>
                                                                </td>
                                                                <td>{{ $srv['service']->description ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge bg-{{ $srv['status'] === 'completed' ? 'success' : 'warning' }}">
                                                                        {{ ucfirst($srv['status']) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $srv['price'] > 0 ? '₦' . number_format($srv['price'], 2) : '₦0.00' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="5" class="text-right">Services Total:</th>
                                                            <th>₦{{ number_format($servicesTotal, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @endif

                                        @if (count($medications) === 0 && count($laboratoryTests) === 0 && count($services) === 0)
                                            <div class="alert alert-info">No claimed items for this encounter.</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total Summary -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h3 class="text-center mb-0">
                                                    <strong>TOTAL AMOUNT: ₦{{ number_format($totalAmount, 2) }}</strong>
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
    </div>

    @push('styles')
        <style>
            .nav-tabs .nav-link {
                color: #495057 !important;
            }

            .nav-tabs .nav-link:hover {
                background-color: #e9ecef;
                color: #495057 !important;
            }

            .nav-tabs .nav-link.active {
                background-color: #007bff;
                color: #ffffff !important;
                border-color: #007bff #007bff #fff;
            }
        </style>
    @endpush
@endsection
