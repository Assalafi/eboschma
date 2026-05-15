@extends('layouts.facility')

@section('title', 'Create Claim from Encounter')

@section('content')
    <div class="page-header">
        <div class="page-leftheader">
            <h4 class="page-title">Create Claim</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                            class="ti-home mr-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('facility.encounters.index') }}">Encounters</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Claim</li>
            </ol>
        </div>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="ti-alert-circle mr-2"></i>Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="ti-alert-circle mr-2"></i>Error!</strong>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="ti-check mr-2"></i>Success!</strong>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('facility.claims.store') }}" method="POST">
        @csrf
        <input type="hidden" name="encounter_id" value="{{ $encounter->id }}">

        <!-- Tabs Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="claimTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="patient-tab" data-bs-toggle="tab"
                                    data-bs-target="#patient" type="button" role="tab" aria-controls="patient"
                                    aria-selected="true">
                                    <i class="ti-user mr-1"></i> 👤 Patient Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="non-priced-tab" data-bs-toggle="tab"
                                    data-bs-target="#non-priced" type="button" role="tab" aria-controls="non-priced"
                                    aria-selected="false">
                                    <i class="ti-clipboard mr-1"></i> 📋 Non-Priced Activities
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="priced-tab" data-bs-toggle="tab" data-bs-target="#priced"
                                    type="button" role="tab" aria-controls="priced" aria-selected="false">
                                    <i class="ti-money mr-1"></i> 💰 Priced Activities
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="claimTabsContent">
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

                                    <!-- Claim Settings -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <h3 class="card-title mb-0">Claim Settings</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label class="form-label">Claim Type <span
                                                            class="text-danger">*</span></label>
                                                    <select name="claim_type" class="form-control form-control-lg"
                                                        required>
                                                        <option value="outpatient" selected>Outpatient</option>
                                                        <option value="inpatient">Inpatient</option>
                                                        <option value="emergency">Emergency</option>
                                                        <option value="referral">Referral</option>
                                                    </select>
                                                </div>

                                                <div class="alert alert-info mt-3">
                                                    <strong>ℹ️ Note:</strong> Review all tabs before submitting the claim.
                                                    Ensure all priced activities are accurate.
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
                                                            <th>Taken By</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($vitalSigns as $index => $vital)
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
                                                                            <th>Taken By</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($vitalSigns as $index => $vital)
                                                                            <tr>
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>{{ \Carbon\Carbon::parse($vital->created_at)->format('d M Y H:i') }}
                                                                                </td>
                                                                                <td>{{ $vital->temperature ?? 'N/A' }}</td>
                                                                                <td>{{ $vital->blood_pressure_systolic }}/{{ $vital->blood_pressure_diastolic }}
                                                                                </td>
                                                                                <td>{{ $vital->pulse_rate ?? 'N/A' }}</td>
                                                                                <td>{{ $vital->respiration_rate ?? 'N/A' }}
                                                                                </td>
                                                                                <td>{{ $vital->spo2 ?? 'N/A' }}</td>
                                                                                <td>{{ $vital->weight ?? 'N/A' }}</td>
                                                                                <td>{{ $vital->height ?? 'N/A' }}</td>
                                                                                <td>{{ $vital->bmi ?? 'N/A' }}</td>
                                                                                <td>{{ $vital->takenBy->name ?? 'N/A' }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif


                                                        <!-- Consultations with Grouped Diagnoses -->
                                                        @if ($consultations->count() > 0)
                                                            <h5 class="mb-3">👨‍⚕️ Clinical Consultations</h5>
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
                                                                                            <table
                                                                                                class="table table-sm table-bordered">
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
                            </div>

                            <!-- PRICED ACTIVITIES SECTION -->
                            <div class="row">
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
                                                        <tbody id="medicationsTable">
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
                                                                        <input type="hidden"
                                                                            name="medications[{{ $index }}][amount]"
                                                                            value="{{ $med['cost'] }}">
                                                                        <input type="hidden"
                                                                            name="medications[{{ $index }}][drug_id]"
                                                                            value="{{ $med['drug']->id ?? '' }}">
                                                                        <input type="hidden"
                                                                            name="medications[{{ $index }}][drug_name]"
                                                                            value="{{ $med['drug']->name ?? 'N/A' }}">
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light">
                                                                <th colspan="6" class="text-right">Pharmacy Total:</th>
                                                                <th><span
                                                                        id="pharmacyTotal">₦{{ number_format($pharmacyTotal, 2) }}</span>
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info">No medications dispensed for this encounter.
                                                </div>
                                            @endif

                                            <!-- Services (Lab, Imaging, etc) -->
                                            @if (count($services) > 0)
                                                <h5 class="mb-3">🔬 Laboratory & Services</h5>
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
                                                        <tbody id="servicesTable">
                                                            @foreach ($services as $index => $srv)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $srv['service']->name ?? 'N/A' }}</td>
                                                                    <td><span
                                                                            class="badge bg-primary">{{ ucfirst($srv['service']->type ?? 'N/A') }}</span>
                                                                    </td>
                                                                    <td>{{ $srv['service']->description ?? 'N/A' }}</td>
                                                                    <td>
                                                                        <span
                                                                            class="badge bg-{{ in_array($srv['status'], ['completed', 'approved', 'delivered']) ? 'success' : 'warning' }}">
                                                                            {{ ucfirst($srv['status']) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        {{ $srv['price'] > 0 ? '₦' . number_format($srv['price'], 2) : '₦0.00' }}
                                                                        <input type="hidden"
                                                                            name="services[{{ $index }}][amount]"
                                                                            class="service-amount"
                                                                            value="{{ $srv['price'] }}">
                                                                        <input type="hidden"
                                                                            name="services[{{ $index }}][service_id]"
                                                                            value="{{ $srv['service']->id ?? '' }}">
                                                                        <input type="hidden"
                                                                            name="services[{{ $index }}][service_name]"
                                                                            value="{{ $srv['service']->name ?? 'N/A' }}">
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light">
                                                                <th colspan="5" class="text-right">Services Total:</th>
                                                                <th><span
                                                                        id="servicesTotal">₦{{ number_format($servicesTotal, 2) }}</span>
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info">No services ordered for this encounter.</div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Summary -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h3 class="text-center mb-0">
                                                <strong>TOTAL CLAIM AMOUNT: <span
                                                        id="grandTotal">₦{{ number_format($pharmacyTotal + $servicesTotal, 2) }}</span></strong>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mb-4">
                                <div class="col-12 text-right">
                                    <a href="{{ route('facility.encounters.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="ti-arrow-left mr-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="ti-save mr-1"></i> Create Claim
                                    </button>
                                </div>
                            </div>
    </form>

    @push('scripts')
        <script>
            // Calculate totals
            function calculateTotals() {
                let pharmacyTotal = 0;
                let servicesTotal = 0;

                // Calculate pharmacy total
                $('.medication-amount').each(function() {
                    pharmacyTotal += parseFloat($(this).val()) || 0;
                });

                // Calculate services total
                $('.service-amount').each(function() {
                    servicesTotal += parseFloat($(this).val()) || 0;
                });

                // Update display
                $('#pharmacyTotal').text('₦' + pharmacyTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                $('#servicesTotal').text('₦' + servicesTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                $('#grandTotal').text('₦' + (pharmacyTotal + servicesTotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
            }

            // Remove row
            function removeRow(btn) {
                if (confirm('Are you sure you want to remove this item?')) {
                    $(btn).closest('tr').remove();
                    calculateTotals();
                }
            }

            // Recalculate on amount change
            $(document).on('input', '.medication-amount, .service-amount', function() {
                calculateTotals();
            });

            // Initial calculation
            $(document).ready(function() {
                calculateTotals();
            });
        </script>
    @endpush
@endsection
