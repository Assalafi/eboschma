@extends('layouts.facility')

@section('title', 'Edit Claim - ' . $claim->claim_number)

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Edit Claim</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('facility.claims.list') }}">Claims</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit {{ $claim->claim_number }}</li>
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

        <form action="{{ route('facility.claims.update', $claim->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Patient Information -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Patient Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Claim Number:</th>
                                    <td><strong>{{ $claim->claim_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $enrolleeDetails->fullname ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>BOSCHMA No:</th>
                                    <td>{{ $patient->enrollee_number }}</td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td>{{ $enrolleeDetails->gender ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $enrolleeDetails->phone_no ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Encounter Information -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Encounter Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Visit Date:</th>
                                    <td>{{ $encounter->visit_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Nature of Visit:</th>
                                    <td>{{ ucfirst($encounter->nature_of_visit) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td><span class="badge bg-success">{{ ucfirst($encounter->status) }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Claim Type -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Claim Type</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="claim_type">Select Claim Type <span class="text-danger">*</span></label>
                                <select name="claim_type" id="claim_type" class="form-control" required>
                                    <option value="outpatient"
                                        {{ old('claim_type', $claim->claim_type) == 'outpatient' ? 'selected' : '' }}>
                                        Outpatient</option>
                                    <option value="inpatient"
                                        {{ old('claim_type', $claim->claim_type) == 'inpatient' ? 'selected' : '' }}>
                                        Inpatient
                                    </option>
                                    <option value="emergency"
                                        {{ old('claim_type', $claim->claim_type) == 'emergency' ? 'selected' : '' }}>
                                        Emergency
                                    </option>
                                    <option value="referral"
                                        {{ old('claim_type', $claim->claim_type) == 'referral' ? 'selected' : '' }}>
                                        Referral
                                    </option>
                                </select>
                            </div>
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

                            <!-- Medications -->
                            @if (count($medications) > 0)
                                <h5 class="mb-3">💊 Medications (Pharmacy)</h5>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Drug Name</th>
                                                <th>Status</th>
                                                <th>Cost (₦)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($medications as $index => $med)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $med['drug']->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-success">Dispensed</span>
                                                    </td>
                                                    <td>
                                                        {{ $med['cost'] > 0 ? '₦' . number_format($med['cost'], 2) : '₦0.00' }}
                                                        <input type="hidden"
                                                            name="medications[{{ $index }}][amount]"
                                                            class="medication-amount" value="{{ $med['cost'] }}">
                                                        <input type="hidden"
                                                            name="medications[{{ $index }}][drug_name]"
                                                            value="{{ $med['drug']->name ?? 'N/A' }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th colspan="3" class="text-right">Pharmacy Total:</th>
                                                <th><span id="pharmacyTotal">₦{{ number_format($pharmacyTotal, 2) }}</span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">No medications for this claim.</div>
                            @endif

                            <!-- Services -->
                            @if (count($services) > 0)
                                <h5 class="mb-3">🔬 Laboratory & Services</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Service Name</th>
                                                <th>Type</th>
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
                                                    <td>
                                                        <span class="badge bg-success">Completed</span>
                                                    </td>
                                                    <td>
                                                        {{ $srv['price'] > 0 ? '₦' . number_format($srv['price'], 2) : '₦0.00' }}
                                                        <input type="hidden" name="services[{{ $index }}][amount]"
                                                            class="service-amount" value="{{ $srv['price'] }}">
                                                        <input type="hidden"
                                                            name="services[{{ $index }}][service_name]"
                                                            value="{{ $srv['service']->name ?? 'N/A' }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th colspan="4" class="text-right">Services Total:</th>
                                                <th><span id="servicesTotal">₦{{ number_format($servicesTotal, 2) }}</span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">No services for this claim.</div>
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
                    <a href="{{ route('facility.claims.show', $claim->id) }}" class="btn btn-secondary btn-lg">
                        <i class="ti-arrow-left mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="ti-save mr-1"></i> Update Claim
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
