@extends('layouts.facility')

@section('title', 'Encounter Details')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Encounter Details</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('facility.encounters.index') }}">Encounters</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Encounter</li>
                </ol>
            </div>
            <div class="page-rightheader ml-auto">
                @if ($encounter->status === 'Completed')
                    <a href="{{ route('facility.claims.create', $encounter->id) }}" class="btn btn-success">
                        <i class="ti-receipt mr-1"></i> Create Claim
                    </a>
                @endif
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="encounterTabs" role="tablist">
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

                        <div class="tab-content mt-3" id="encounterTabsContent">
                            <!-- TAB 1: Patient Info -->
                            <div class="tab-pane fade show active" id="patient" role="tabpanel"
                                aria-labelledby="patient-tab">
                                <div class="row">
                                    <!-- Patient Information -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white">
                                                <h3 class="card-title mb-0">Patient Information</h3>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="40%">Enrollee No:</th>
                                                        <td><strong>{{ $patient->enrollee_number }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Name:</th>
                                                        <td>{{ $enrolleeDetails->fullname ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Gender:</th>
                                                        <td>{{ $enrolleeDetails->gender ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Phone:</th>
                                                        <td>{{ $enrolleeDetails->phone_no ?? ($enrolleeDetails->phone ?? 'N/A') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Visit Date:</th>
                                                        <td>{{ $encounter->visit_date->format('d M Y') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Visit Nature:</th>
                                                        <td><span
                                                                class="badge bg-info">{{ ucfirst($encounter->nature_of_visit) }}</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Encounter Status -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <h3 class="card-title mb-0">Encounter Status</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Status:</strong></label>
                                                    <div>
                                                        @php
                                                            $statusColors = [
                                                                'Registered' => 'info',
                                                                'In Progress' => 'warning',
                                                                'Completed' => 'success',
                                                                'Cancelled' => 'danger',
                                                            ];
                                                            $color = $statusColors[$encounter->status] ?? 'secondary';
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $color }} fs-5">{{ $encounter->status }}</span>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info mt-3">
                                                    <strong>ℹ️ Note:</strong> This is a view-only page showing encounter
                                                    details.
                                                    @if ($encounter->status === 'Completed')
                                                        You can create a claim from this encounter using the button above.
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: Non-Priced Activities -->
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
                                                            <h6 class="mb-0">Consultation
                                                                #{{ $index + 1 }}</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1"><strong>Presenting
                                                                            Complaints:</strong>
                                                                        {{ $consultation->presenting_complaints ?? 'N/A' }}
                                                                    </p>
                                                                    <p class="mb-1"><strong>Clinical
                                                                            Notes:</strong>
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
                                                                        <h6 class="mb-2">🩺 Diagnoses:
                                                                        </h6>
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
                                                                                            <td>{{ $diagIndex + 1 }}
                                                                                            </td>
                                                                                            <td>{{ $diagnosis->icdCode->code ?? 'N/A' }}
                                                                                            </td>
                                                                                            <td>{{ $diagnosis->icdCode->description ?? 'N/A' }}
                                                                                            </td>
                                                                                            <td><span
                                                                                                    class="badge bg-primary">{{ ucfirst($diagnosis->diagnosis_type ?? 'primary') }}</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @else
                                                                        <p class="mb-0 text-muted">No
                                                                            diagnoses
                                                                            recorded for this
                                                                            consultation.</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-warning">No consultations recorded for
                                                this
                                                encounter.</div>
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

                            <!-- TAB 3: Priced Activities -->
                            <div class="tab-pane fade" id="priced" role="tabpanel" aria-labelledby="priced-tab">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h3 class="card-title">💰 Priced Activities</h3>
                                        </div>
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
                                                                <th colspan="6" class="text-right">Pharmacy Total:
                                                                </th>
                                                                <th>₦{{ number_format($pharmacyTotal, 2) }}</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info">No medications dispensed for this
                                                    encounter.
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
                                                                <th colspan="4" class="text-right">Laboratory Total:
                                                                </th>
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
                                                                <th colspan="5" class="text-right">Services Total:
                                                                </th>
                                                                <th>₦{{ number_format($servicesTotal, 2) }}</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @endif

                                            @if (count($medications) === 0 && count($laboratoryTests) === 0 && count($services) === 0)
                                                <div class="alert alert-info">No priced activities for this encounter.
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>

                                <!-- Total Summary Inside Tab 3 -->
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

                        <!-- Action Buttons Outside Tabs -->
                        <div class="row mt-3 mb-3">
                            <div class="col-12 text-right">
                                <a href="{{ route('facility.encounters.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="ti-arrow-left mr-1"></i> Back to Encounters
                                </a>
                                @if ($encounter->status === 'Completed')
                                    <a href="{{ route('facility.claims.create', $encounter->id) }}"
                                        class="btn btn-success btn-lg">
                                        <i class="ti-receipt mr-1"></i> Create Claim
                                    </a>
                                @endif
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
