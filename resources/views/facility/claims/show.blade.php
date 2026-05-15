@extends('layouts.facility')

@section('title', 'View Claim - ' . $claim->claim_number)

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Claim Details</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('facility.claims.list') }}">Claims</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $claim->claim_number }}</li>
                </ol>
            </div>
            <div class="page-rightheader">
                <div class="btn-list">
                    @if ($claim->status === 'draft')
                        <a href="{{ route('facility.claims.edit', $claim->id) }}" class="btn btn-warning">
                            <i class="ti-pencil mr-1"></i> Edit Claim
                        </a>
                    @endif
                    <a href="{{ route('facility.claims.list') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
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
                        <h3 class="card-title mb-0">{{ $claim->claim_number }}</h3>
                        <div>{!! $claim->status_badge !!}</div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="claimTabs" role="tablist">
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
                                    📋 Non-Priced Activities
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="priced-tab" data-bs-toggle="tab" data-bs-target="#priced"
                                    type="button" role="tab" aria-controls="priced" aria-selected="false">
                                    💰 Priced Activities
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="claimTabsContent">
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
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="40%">Claim Number:</th>
                                                        <td><strong>{{ $claim->claim_number }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Name:</th>
                                                        <td>{{ $claim->patient->enrollee->fullname ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>BOSCHMA No:</th>
                                                        <td>{{ $claim->patient->enrollee_number }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Gender:</th>
                                                        <td>{{ $claim->patient->enrollee->gender ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Phone:</th>
                                                        <td>{{ $claim->patient->enrollee->phone_no ?? 'N/A' }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Claim Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="40%">Claim Type:</th>
                                                        <td><span
                                                                class="badge bg-primary">{{ ucfirst($claim->claim_type) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Visit Date:</th>
                                                        <td>{{ $claim->encounter ? $claim->encounter->visit_date->format('d M Y') : ($claim->service_date ? $claim->service_date->format('d M Y') : 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status:</th>
                                                        <td>{!! $claim->status_badge !!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Created:</th>
                                                        <td>{{ $claim->created_at->format('d M Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Amount:</th>
                                                        <td><strong
                                                                class="text-primary">{{ $claim->formatted_total_amount }}</strong>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: Non-Priced Activities -->
                            <div class="tab-pane fade" id="non-priced" role="tabpanel" aria-labelledby="non-priced-tab">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <!-- Consultations with Grouped Diagnoses -->
                                        @if ($claim->consultations->count() > 0)
                                            <h5 class="mb-3">👨‍⚕️ Clinical Consultations</h5>
                                            <div class="mb-4">
                                                @foreach ($claim->consultations as $index => $consultation)
                                                    <div class="card mb-3">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0">Consultation #{{ $index + 1 }}</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1"><strong>Consultation Notes:</strong>
                                                                        {{ $consultation->consultation_notes ?? 'N/A' }}
                                                                    </p>
                                                                    <p class="mb-0"><strong>Status:</strong>
                                                                        <span
                                                                            class="badge bg-success">{{ ucfirst($consultation->status ?? 'completed') }}</span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <!-- Diagnoses for this consultation -->
                                                                    @if (
                                                                        $consultation->consultation &&
                                                                            $consultation->consultation->diagnoses &&
                                                                            $consultation->consultation->diagnoses->count() > 0)
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
                                                                                    @foreach ($consultation->consultation->diagnoses as $diagIndex => $diagnosis)
                                                                                        <tr>
                                                                                            <td>{{ $diagIndex + 1 }}</td>
                                                                                            <td>{{ $diagnosis->icdCode->code ?? 'N/A' }}
                                                                                            </td>
                                                                                            <td>{{ $diagnosis->icdCode->description ?? 'N/A' }}
                                                                                            </td>
                                                                                            <td><span
                                                                                                    class="badge bg-info">{{ ucfirst($diagnosis->diagnosis_type) }}</span>
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

                                        <!-- Activities -->
                                        @if ($claim->activities->count() > 0)
                                            <h5 class="mb-3">⚡ Encounter Actions</h5>
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
                                                        @foreach ($claim->activities as $index => $activity)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td><span
                                                                        class="badge bg-info">{{ ucfirst($activity->activity_type) }}</span>
                                                                </td>
                                                                <td>{{ $activity->activity_description }}</td>
                                                                <td>{{ $activity->performed_at ? \Carbon\Carbon::parse($activity->performed_at)->format('d M Y H:i') : 'N/A' }}
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

                            <!-- TAB 3: Priced Activities -->
                            <div class="tab-pane fade" id="priced" role="tabpanel" aria-labelledby="priced-tab">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <!-- Medications -->
                                        @if ($claim->medications->count() > 0)
                                            <h5 class="mb-3">💊 Medications (Pharmacy)</h5>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Drug Name</th>
                                                            <th>Quantity</th>
                                                            <th>Unit Price (₦)</th>
                                                            <th>Total Price (₦)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->medications as $index => $med)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $med->drug_name }}</td>
                                                                <td>{{ $med->quantity }}</td>
                                                                <td>₦{{ number_format($med->unit_price, 2) }}</td>
                                                                <td>₦{{ number_format($med->total_price, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="4" class="text-right">Pharmacy Total:</th>
                                                            <th>₦{{ number_format($claim->pharmacy_amount, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">No medications in this claim.</div>
                                        @endif

                                        <!-- Services -->
                                        @if ($claim->services->count() > 0)
                                            <h5 class="mb-3">🔬 Laboratory & Services</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Service Name</th>
                                                            <th>Frequency</th>
                                                            <th>Unit Price (₦)</th>
                                                            <th>Total Price (₦)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($claim->services as $index => $srv)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $srv->service_name }}</td>
                                                                <td>{{ $srv->frequency }}</td>
                                                                <td>₦{{ number_format($srv->unit_price, 2) }}</td>
                                                                <td>₦{{ number_format($srv->total_price, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="bg-light">
                                                            <th colspan="4" class="text-right">Services Total:</th>
                                                            <th>₦{{ number_format($claim->services_amount, 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">No services in this claim.</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total Summary -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h3 class="text-center mb-0">
                                                    <strong>TOTAL CLAIM AMOUNT:
                                                        {{ $claim->formatted_total_amount }}</strong>
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
            /* Fix tab colors */
            .nav-tabs .nav-link {
                color: #495057 !important;
            }

            .nav-tabs .nav-link:hover {
                background-color: #e9ecef;
                color: #495057 !important;
            }

            .nav-tabs .nav-link.active {
                color: #495057 !important;
                background-color: #fff;
                border-color: #dee2e6 #dee2e6 #fff;
            }

            .nav-tabs .nav-link.active:hover {
                background-color: #fff;
                color: #495057 !important;
            }
        </style>
    @endpush
@endsection
