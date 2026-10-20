@extends('layouts.app')

@section('title', 'Referral ' . ($referral->authorization->authorization_code ?? 'REF-' . $referral->id))

@section('content')
    @php
        $patient = $referral->encounter->patient ?? null;
        $enrolleeDetails = $patient?->enrolleeDetails;
        $patientName = $enrolleeDetails->fullname ?? $enrolleeDetails->name ?? 'N/A';
        $patientDob = $enrolleeDetails->date_of_birth ?? $enrolleeDetails->dob ?? null;
        $patientNin = $enrolleeDetails->nin ?? null;
        $patientPhone = $enrolleeDetails->phone_no ?? $enrolleeDetails->phone ?? null;
        $referenceCode = $referral->authorization->authorization_code ?? ('REF-' . $referral->id);

        $canApproveReferral = (auth('staff')->user()?->can('referral.approve')) || (auth()->user()?->can('referral.approve'));
        $canRejectReferral = (auth('staff')->user()?->can('referral.reject')) || (auth()->user()?->can('referral.reject'));
    @endphp

    <div class="container-fluid ref-wrap">

        {{-- HERO --}}
        <div class="ref-hero">
            <div class="ref-hero-main">
                <div class="ref-hero-icon"><i class="fas fa-share-square"></i></div>
                <div>
                    <div class="ref-hero-eyebrow">
                        <a href="{{ route('dashboard') }}">Dashboard</a><span>/</span>
                        <a href="{{ route('referrals.index') }}">Referrals</a><span>/</span>
                        <span>{{ $referenceCode }}</span>
                    </div>
                    <h1 class="ref-hero-title">{{ $referenceCode }}</h1>
                    <div class="ref-hero-badges">
                        <span class="ref-chip ref-chip-light">
                            <i class="fas {{ $isOutgoing ? 'fa-arrow-up-right-from-square' : 'fa-arrow-down-left' }}"></i>
                            {{ $isOutgoing ? 'Outgoing' : 'Incoming' }}
                        </span>
                        <span class="ref-chip">{{ $referral->getReferralTypeLabel() }}</span>
                        {!! $referral->status_badge !!}
                        @if ($referral->approval_status === 'approved')
                            <span class="ref-chip ref-chip-success"><i class="fas fa-check"></i> Approved</span>
                        @elseif ($referral->approval_status === 'rejected')
                            <span class="ref-chip ref-chip-danger"><i class="fas fa-times"></i> Rejected</span>
                        @else
                            <span class="ref-chip ref-chip-warning"><i class="fas fa-hourglass-half"></i> Pending Approval</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="ref-hero-actions">
                <a href="{{ route('referrals.pdf', $referral->id) }}" target="_blank" class="btn btn-light">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <button type="button" class="btn btn-outline-light" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="{{ route('referrals.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        {{-- SUMMARY STRIP --}}
        <div class="ref-summary">
            <div class="ref-stat">
                <div class="ref-stat-label">Patient</div>
                <div class="ref-stat-value">
                    {{ $patientName }}
                    <small>{{ $patient->enrollee_number ?? 'N/A' }}@if ($patient?->file_number) &middot; {{ $patient->file_number }} @endif</small>
                </div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat-label">Referral Route</div>
                <div class="ref-stat-value">
                    {{ $referral->fromFacility->name ?? 'N/A' }}
                    <small><i class="fas fa-arrow-right me-1"></i>{{ $referral->toFacility->name ?? 'N/A' }}</small>
                </div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat-label">Service</div>
                <div class="ref-stat-value">
                    {{ $referral->serviceItem->name ?? 'General Referral' }}
                    <small>{{ $referral->serviceItem->type ?? ($referral->getReferralTypeLabel()) }}</small>
                </div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat-label">Created</div>
                <div class="ref-stat-value">
                    {{ $referral->created_at ? $referral->created_at->format('d M Y') : 'N/A' }}
                    <small>{{ $referral->created_at ? $referral->created_at->format('g:i A') : '' }}</small>
                </div>
            </div>
        </div>

        {{-- REJECTION NOTICE --}}
        @if ($referral->approval_status === 'rejected')
            <div class="ref-alert ref-alert-danger">
                <div class="fw-bold mb-1"><i class="fas fa-circle-exclamation me-1"></i> This referral was rejected</div>
                @if ($referral->rejected_by_name)
                    <div>By <strong>{{ $referral->rejected_by_name }}</strong>
                        @if ($referral->rejected_at) &middot; {{ $referral->rejected_at->format('d M Y g:i A') }} @endif
                    </div>
                @endif
                @if ($referral->rejection_reason)
                    <div class="mt-2">Reason: {{ $referral->rejection_reason }}</div>
                @endif
            </div>
        @endif

        {{-- TABS --}}
        <div class="ref-tabs-card">
            <ul class="nav ref-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-patient" type="button" role="tab">
                        <i class="fas fa-user"></i> Patient Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-clinical" type="button" role="tab">
                        <i class="fas fa-stethoscope"></i> Clinical Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-claimed" type="button" role="tab">
                        <i class="fas fa-receipt"></i> Claimed
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- ============ PATIENT INFO ============ --}}
                <div class="tab-pane fade show active ref-pane" id="tab-patient" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="ref-card mb-0">
                                <div class="ref-card-header"><i class="fas fa-user-circle"></i> Patient Information</div>
                                <div class="ref-card-body">
                                    @if ($patient)
                                        <table class="ref-info">
                                            <tr><th>Full Name</th><td>{{ $patientName }}</td></tr>
                                            <tr><th>Enrollee Number</th><td>{{ $patient->enrollee_number ?? 'N/A' }}</td></tr>
                                            <tr><th>File Number</th><td>{{ $patient->file_number ?? 'N/A' }}</td></tr>
                                            <tr><th>Gender</th><td>{{ $enrolleeDetails ? ucfirst($enrolleeDetails->gender ?? 'N/A') : 'N/A' }}</td></tr>
                                            <tr><th>Date of Birth</th><td>{{ $patientDob ? \Carbon\Carbon::parse($patientDob)->format('d M Y') : 'N/A' }}</td></tr>
                                            <tr><th>NIN</th><td>{{ $patientNin ?: 'N/A' }}</td></tr>
                                            <tr><th>Phone</th><td>{{ $patientPhone ?: 'N/A' }}</td></tr>
                                        </table>
                                    @else
                                        <div class="ref-empty">Patient information not available.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="ref-card mb-0">
                                <div class="ref-card-header"><i class="fas fa-route"></i> Referral Information</div>
                                <div class="ref-card-body">
                                    <table class="ref-info">
                                        <tr><th>Referral Type</th><td>{{ $referral->getReferralTypeLabel() }}</td></tr>
                                        @if ($referral->serviceItem)
                                            <tr><th>Service</th><td>{{ $referral->serviceItem->name }} <span class="text-muted">({{ $referral->serviceItem->type ?? 'Service' }})</span></td></tr>
                                        @endif
                                        <tr><th>From Facility</th><td>{{ $referral->fromFacility->name ?? 'N/A' }}</td></tr>
                                        <tr><th>To Facility</th><td>{{ $referral->toFacility->name ?? 'N/A' }}</td></tr>
                                        <tr><th>Created</th><td>{{ $referral->created_at ? $referral->created_at->format('d M Y g:i A') : 'N/A' }}</td></tr>
                                        @if ($referral->approval_status === 'approved' && $referral->approved_by_name)
                                            <tr><th>Approved By</th><td>{{ $referral->approved_by_name }}
                                                @if ($referral->approved_at)<span class="text-muted"> &middot; {{ $referral->approved_at->format('d M Y g:i A') }}</span>@endif
                                            </td></tr>
                                        @endif
                                        @if ($referral->approval_status === 'rejected' && $referral->rejected_by_name)
                                            <tr><th>Rejected By</th><td class="text-danger">{{ $referral->rejected_by_name }}
                                                @if ($referral->rejected_at)<span class="text-muted"> &middot; {{ $referral->rejected_at->format('d M Y g:i A') }}</span>@endif
                                            </td></tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($referral->authorization)
                            <div class="col-12">
                                <div class="ref-card mb-0">
                                    <div class="ref-card-header"><i class="fas fa-shield-halved"></i> Authorization</div>
                                    <div class="ref-card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="ref-stat-label">Authorization Code</div>
                                                <div class="ref-stat-value">{{ $referral->authorization->authorization_code }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="ref-stat-label">Generated By</div>
                                                <div class="ref-stat-value">{{ $referral->authorization->approver->name ?? 'System' }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="ref-stat-label">Expires At</div>
                                                <div class="ref-stat-value">
                                                    {{ $referral->authorization->expires_at ? $referral->authorization->expires_at->format('d M Y g:i A') : 'No expiration' }}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="ref-stat-label">Validity</div>
                                                <div class="ref-stat-value">
                                                    @if ($referral->authorization->isValid())
                                                        <span class="ref-badge ref-badge-success"><i class="fas fa-check me-1"></i>Valid</span>
                                                    @else
                                                        <span class="ref-badge ref-badge-danger"><i class="fas fa-times me-1"></i>Expired</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <div class="ref-card mb-0">
                                <div class="ref-card-header"><i class="fas fa-comment-medical"></i> Reason for Referral</div>
                                <div class="ref-card-body">
                                    <p class="mb-0" style="white-space: pre-line;">{{ $referral->reason ?: 'No reason provided.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ CLINICAL DETAILS ============ --}}
                <div class="tab-pane fade ref-pane" id="tab-clinical" role="tabpanel">
                    {{-- Vitals --}}
                    <div class="ref-section-title"><i class="fas fa-heart-pulse"></i> Vital Signs</div>
                    @if (count($vitalSigns) > 0)
                        <div class="table-responsive mb-4">
                            <table class="ref-table">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Date &amp; Time</th><th>Temp (°C)</th><th>BP</th>
                                        <th>Pulse</th><th>Resp</th><th>SpO2</th><th>Weight</th><th>Height</th><th>BMI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vitalSigns as $index => $vital)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($vital->created_at)->format('d M Y g:i A') }}</td>
                                            <td>{{ $vital->temperature ?? '—' }}</td>
                                            <td>{{ $vital->blood_pressure_systolic ?? '—' }}/{{ $vital->blood_pressure_diastolic ?? '—' }}</td>
                                            <td>{{ $vital->pulse_rate ?? '—' }}</td>
                                            <td>{{ $vital->respiration_rate ?? '—' }}</td>
                                            <td>{{ $vital->spo2 ?? '—' }}</td>
                                            <td>{{ $vital->weight ?? '—' }}</td>
                                            <td>{{ $vital->height ?? '—' }}</td>
                                            <td>{{ $vital->bmi ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="ref-empty mb-4">No vital signs recorded for this encounter.</div>
                    @endif

                    {{-- Consultations --}}
                    <div class="ref-section-title"><i class="fas fa-user-doctor"></i> Clinical Consultations</div>
                    @if ($consultations->count() > 0)
                        @foreach ($consultations as $index => $consultation)
                            <div class="ref-card">
                                <div class="ref-card-header">
                                    <i class="fas fa-notes-medical"></i> Consultation #{{ $index + 1 }}
                                    <span class="ref-badge {{ strtolower($consultation->status ?? '') === 'completed' ? 'ref-badge-success' : 'ref-badge-warning' }} ms-auto">
                                        {{ ucfirst($consultation->status ?? 'N/A') }}
                                    </span>
                                </div>
                                <div class="ref-card-body">
                                    <div class="row g-4">
                                        <div class="col-lg-6">
                                            <table class="ref-info">
                                                <tr><th>Presenting Complaints</th><td>{{ $consultation->presenting_complaints ?: 'N/A' }}</td></tr>
                                                @if (!empty($consultation->history_of_present_illness))
                                                    <tr><th>History of Present Illness</th><td>{{ $consultation->history_of_present_illness }}</td></tr>
                                                @endif
                                                @if (!empty($consultation->physical_examination))
                                                    <tr><th>Physical Examination</th><td>{{ $consultation->physical_examination }}</td></tr>
                                                @endif
                                                <tr><th>Clinical Notes</th><td>{{ $consultation->clinical_note ?: 'N/A' }}</td></tr>
                                                @if (!empty($consultation->investigation_note))
                                                    <tr><th>Investigation Note</th><td>{{ $consultation->investigation_note }}</td></tr>
                                                @endif
                                            </table>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="ref-stat-label mb-2">Diagnoses</div>
                                            @if ($consultation->diagnoses && $consultation->diagnoses->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="ref-table">
                                                        <thead>
                                                            <tr><th>#</th><th>ICD Code</th><th>Description</th><th>Type</th></tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($consultation->diagnoses as $diagIndex => $diagnosis)
                                                                <tr>
                                                                    <td class="text-muted">{{ $diagIndex + 1 }}</td>
                                                                    <td><code>{{ $diagnosis->icdCode->code ?? 'N/A' }}</code></td>
                                                                    <td>{{ $diagnosis->icdCode->description ?? 'N/A' }}</td>
                                                                    <td>
                                                                        <span class="ref-badge {{ strtolower($diagnosis->diagnosis_type ?? '') === 'confirmed' ? 'ref-badge-success' : 'ref-badge-info' }}">
                                                                            {{ ucfirst($diagnosis->diagnosis_type ?? 'primary') }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="ref-empty">No diagnoses recorded for this consultation.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="ref-empty mb-4">No consultations recorded for this encounter.</div>
                    @endif

                    @if ($referral->approval_status === 'pending' && ($canApproveReferral || $canRejectReferral))
                        <div class="ref-approval-actions">
                            @if ($canRejectReferral)
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectReferralModal">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            @endif
                            @if ($canApproveReferral)
                                <form id="approveReferralForm" action="{{ route('referrals.approve', $referral->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                </form>
                            @endif
                        </div>
                    @endif

                    {{-- Encounter Actions --}}
                    @if ($actions->count() > 0)
                        <div class="ref-section-title"><i class="fas fa-bolt"></i> Encounter Actions</div>
                        <div class="table-responsive">
                            <table class="ref-table">
                                <thead>
                                    <tr><th>#</th><th>Action Type</th><th>Description</th><th>Time</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($actions as $index => $action)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td><span class="ref-badge ref-badge-info">{{ ucfirst($action->action_type) }}</span></td>
                                            <td>{{ $action->description }}</td>
                                            <td>{{ \Carbon\Carbon::parse($action->action_time)->format('d M Y g:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- ============ CLAIMED ============ --}}
                <div class="tab-pane fade ref-pane" id="tab-claimed" role="tabpanel">
                    {{-- Medications --}}
                    @if (count($medications) > 0)
                        <div class="ref-section-title"><i class="fas fa-pills"></i> Medications (Pharmacy)</div>
                        <div class="table-responsive mb-4">
                            <table class="ref-table">
                                <thead>
                                    <tr><th>#</th><th>Drug Name</th><th>Dosage</th><th class="text-center">Qty</th><th class="text-center">Days</th><th>Status</th><th class="text-end">Cost</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($medications as $index => $med)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $med['drug']->name ?? 'N/A' }}</td>
                                            <td>{{ $med['item']->dosage ?? '—' }}</td>
                                            <td class="text-center">{{ $med['item']->quantity ?? 0 }}</td>
                                            <td class="text-center">{{ $med['item']->duration ?? 0 }}</td>
                                            <td>
                                                <span class="ref-badge {{ $med['dispensing_status'] === 'dispensed' ? 'ref-badge-success' : 'ref-badge-warning' }}">
                                                    {{ ucfirst($med['dispensing_status']) }}
                                                </span>
                                            </td>
                                            <td class="text-end">₦{{ number_format($med['cost'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr><th colspan="6" class="text-end">Pharmacy Total</th><th class="text-end">₦{{ number_format($pharmacyTotal, 2) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    {{-- Laboratory --}}
                    @if (count($laboratoryTests) > 0)
                        <div class="ref-section-title"><i class="fas fa-flask"></i> Laboratory Tests</div>
                        <div class="table-responsive mb-4">
                            <table class="ref-table">
                                <thead>
                                    <tr><th>#</th><th>Test Name</th><th>Description</th><th>Status</th><th class="text-end">Price</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($laboratoryTests as $index => $lab)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $lab['service']->name ?? 'N/A' }}</td>
                                            <td>{{ $lab['service']->description ?? '—' }}</td>
                                            <td>
                                                <span class="ref-badge {{ $lab['status'] === 'completed' ? 'ref-badge-success' : 'ref-badge-warning' }}">
                                                    {{ ucfirst($lab['status']) }}
                                                </span>
                                            </td>
                                            <td class="text-end">₦{{ number_format($lab['price'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr><th colspan="4" class="text-end">Laboratory Total</th><th class="text-end">₦{{ number_format($labTotal, 2) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    {{-- Services --}}
                    @if (count($services) > 0)
                        <div class="ref-section-title"><i class="fas fa-hospital"></i> Other Services</div>
                        <div class="table-responsive mb-4">
                            <table class="ref-table">
                                <thead>
                                    <tr><th>#</th><th>Service Name</th><th>Type</th><th>Description</th><th>Status</th><th class="text-end">Price</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($services as $index => $srv)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $srv['service']->name ?? 'N/A' }}</td>
                                            <td><span class="ref-badge ref-badge-info">{{ ucfirst($srv['service']->type ?? 'N/A') }}</span></td>
                                            <td>{{ $srv['service']->description ?? '—' }}</td>
                                            <td>
                                                <span class="ref-badge {{ $srv['status'] === 'completed' ? 'ref-badge-success' : 'ref-badge-warning' }}">
                                                    {{ ucfirst($srv['status']) }}
                                                </span>
                                            </td>
                                            <td class="text-end">₦{{ number_format($srv['price'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr><th colspan="5" class="text-end">Services Total</th><th class="text-end">₦{{ number_format($servicesTotal, 2) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    @if (count($medications) === 0 && count($laboratoryTests) === 0 && count($services) === 0)
                        <div class="ref-empty">No claimed items for this encounter.</div>
                    @endif

                    {{-- Grand total --}}
                    <div class="ref-total mt-4">
                        <div>
                            <div style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;opacity:.8">Total Claim Amount</div>
                            <div style="font-size:13px;opacity:.85">Pharmacy ₦{{ number_format($pharmacyTotal, 2) }} &middot; Lab ₦{{ number_format($labTotal, 2) }} &middot; Services ₦{{ number_format($servicesTotal, 2) }}</div>
                        </div>
                        <div style="font-size:26px;font-weight:800;">₦{{ number_format($totalAmount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Referral Modal -->
    <div class="modal fade" id="rejectReferralModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('referrals.reject', $referral->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Referral</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="rejection_reason" class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"
                            placeholder="Explain why this referral is being rejected..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --ref-primary: #016634;
            --ref-primary-dark: #014525;
            --ref-primary-light: #02803f;
            --ref-success: #10b981;
            --ref-warning: #f59e0b;
            --ref-info: #3b82f6;
            --ref-danger: #ef4444;
            --ref-border: #e2e8f0;
            --ref-muted: #64748b;
            --ref-ink: #0f172a;
        }

        .ref-wrap { padding-top: .25rem; }

        /* Hero */
        .ref-hero {
            background: linear-gradient(135deg, var(--ref-primary) 0%, var(--ref-primary-dark) 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 10px 30px rgba(1, 102, 52, .18);
            margin-bottom: 18px;
        }
        .ref-hero-main { display: flex; align-items: center; gap: 16px; }
        .ref-hero-icon {
            width: 56px; height: 56px; border-radius: 14px;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .ref-hero-eyebrow { font-size: 12px; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.72); margin-bottom: 4px; }
        .ref-hero-eyebrow a { color: rgba(255,255,255,.9); text-decoration: none; }
        .ref-hero-eyebrow span { margin: 0 6px; opacity: .6; }
        .ref-hero-title { font-size: 22px; font-weight: 700; margin: 0 0 10px; }
        .ref-hero-badges { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .ref-chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600;
            background: rgba(255,255,255,.16); color: #fff;
        }
        .ref-chip-light { background: #fff; color: var(--ref-primary); }
        .ref-chip-success { background: var(--ref-success); }
        .ref-chip-danger { background: var(--ref-danger); }
        .ref-chip-warning { background: var(--ref-warning); color: #3b2500; }
        .ref-hero-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .ref-hero-actions .btn { border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .ref-hero-actions .btn-light { color: var(--ref-primary); }
        .ref-hero-actions .btn-outline-light { border-color: rgba(255,255,255,.5); color: #fff; }
        .ref-hero-actions .btn-outline-light:hover { background: rgba(255,255,255,.15); }

        /* Summary */
        .ref-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .ref-stat { background: #fff; border: 1px solid var(--ref-border); border-radius: 14px; padding: 16px 18px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
        .ref-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--ref-muted); font-weight: 700; margin-bottom: 6px; }
        .ref-stat-value { font-size: 15px; font-weight: 600; color: var(--ref-ink); line-height: 1.35; }
        .ref-stat-value small { display: block; font-size: 12px; font-weight: 500; color: var(--ref-muted); margin-top: 2px; }

        /* Alerts */
        .ref-alert { border-radius: 12px; padding: 14px 18px; font-size: 13.5px; margin-bottom: 18px; }
        .ref-alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Tabs */
        .ref-tabs-card { background: #fff; border: 1px solid var(--ref-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.04); overflow: hidden; }
        .ref-tabs { padding: 0 16px; border-bottom: 1px solid var(--ref-border); gap: 4px; flex-wrap: nowrap; overflow-x: auto; }
        .ref-tabs .nav-link {
            border: none; border-bottom: 2px solid transparent;
            color: var(--ref-muted); font-weight: 600; font-size: 13.5px;
            padding: 14px 16px; display: flex; align-items: center; gap: 8px; white-space: nowrap;
        }
        .ref-tabs .nav-link:hover { color: var(--ref-primary); }
        .ref-tabs .nav-link.active { color: var(--ref-primary); border-bottom-color: var(--ref-primary); background: transparent; }
        .ref-pane { padding: 22px; }

        /* Cards */
        .ref-card { border: 1px solid var(--ref-border); border-radius: 14px; overflow: hidden; margin-bottom: 18px; }
        .ref-card-header {
            background: #f8fafc; border-bottom: 1px solid var(--ref-border);
            padding: 12px 16px; font-weight: 700; font-size: 13.5px; color: var(--ref-ink);
            display: flex; align-items: center; gap: 8px;
        }
        .ref-card-header i { color: var(--ref-primary); }
        .ref-card-body { padding: 16px; }

        /* Info list */
        .ref-info { width: 100%; border-collapse: collapse; }
        .ref-info th, .ref-info td { padding: 9px 0; border-bottom: 1px dashed #eef2f6; font-size: 13.5px; vertical-align: top; }
        .ref-info tr:last-child th, .ref-info tr:last-child td { border-bottom: none; }
        .ref-info th { width: 42%; color: var(--ref-muted); font-weight: 600; padding-right: 12px; }
        .ref-info td { color: var(--ref-ink); }

        /* Tables */
        .ref-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ref-table thead th {
            background: #f8fafc; color: var(--ref-muted);
            text-transform: uppercase; letter-spacing: .04em; font-size: 11px; font-weight: 700;
            padding: 10px 12px; border-bottom: 1px solid var(--ref-border); text-align: left; white-space: nowrap;
        }
        .ref-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .ref-table tbody tr:hover td { background: #f8fafc; }
        .ref-table tfoot th, .ref-table tfoot td { padding: 10px 12px; background: #f8fafc; font-weight: 700; }
        .ref-table .text-end { text-align: right; }
        .ref-table .text-center { text-align: center; }
        .ref-table code { background: #f1f5f9; color: #0f172a; padding: 1px 6px; border-radius: 4px; font-size: 12px; }

        /* Badges */
        .ref-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .ref-badge-success { background: #dcfce7; color: #166534; }
        .ref-badge-warning { background: #fef3c7; color: #92400e; }
        .ref-badge-info { background: #dbeafe; color: #1e40af; }
        .ref-badge-danger { background: #fee2e2; color: #991b1b; }

        /* Section titles */
        .ref-section-title { font-size: 14px; font-weight: 700; color: var(--ref-ink); margin: 4px 0 12px; display: flex; align-items: center; gap: 8px; }
        .ref-section-title i { color: var(--ref-primary); }
        .ref-section-title:not(:first-child) { margin-top: 26px; }

        /* Approval actions */
        .ref-approval-actions {
            display: flex; justify-content: flex-end; align-items: center; gap: 10px;
            margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--ref-border);
        }
        .ref-approval-actions .btn {
            border-radius: 10px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .ref-approval-actions .btn-success {
            background: var(--ref-primary); border-color: var(--ref-primary);
        }
        .ref-approval-actions .btn-success:hover,
        .ref-approval-actions .btn-success:focus {
            background: var(--ref-primary-dark); border-color: var(--ref-primary-dark);
        }
        .ref-approval-actions .btn-outline-danger {
            color: var(--ref-danger); border-color: rgba(239,68,68,.45);
        }
        .ref-approval-actions .btn-outline-danger:hover {
            background: var(--ref-danger); border-color: var(--ref-danger); color: #fff;
        }

        /* Empty states */
        .ref-empty { text-align: center; padding: 26px; color: var(--ref-muted); background: #f8fafc; border: 1px dashed var(--ref-border); border-radius: 12px; font-size: 13px; }

        /* Grand total */
        .ref-total {
            background: linear-gradient(135deg, var(--ref-primary) 0%, var(--ref-primary-dark) 100%);
            color: #fff; border-radius: 14px; padding: 20px 24px;
            display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;
            box-shadow: 0 8px 24px rgba(1,102,52,.18);
        }

        @media (max-width: 640px) {
            .ref-hero { padding: 18px; }
            .ref-pane { padding: 14px; }
            .ref-total { text-align: left; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var approveForm = document.getElementById('approveReferralForm');
            if (approveForm) {
                approveForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var form = this;
                    swal({
                        title: 'Approve Referral?',
                        text: 'Are you sure you want to approve this referral?',
                        type: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#016634',
                        confirmButtonText: 'Yes, approve',
                        cancelButtonText: 'Cancel',
                        closeOnConfirm: true
                    }, function (confirmed) {
                        if (confirmed) form.submit();
                    });
                });
            }
        })();
    </script>
@endpush
