@extends('layouts.facility')

@section('title', 'Create Referral')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="page-leftheader">
                <h4 class="page-title">Create Referral</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}"><i
                                class="ti-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('facility.encounters.index') }}">Encounters</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Referral</li>
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

        <form action="{{ route('facility.referrals.store') }}" method="POST">
            @csrf
            <input type="hidden" name="encounter_id" value="{{ $encounter->id }}">

            <div class="row">
                <!-- Patient Information Card -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0">👤 Patient Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
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
                                    <td>{{ $enrolleeDetails->phone_no ?? ($enrolleeDetails->phone ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>Visit Date:</th>
                                    <td>{{ $encounter->visit_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Encounter Status:</th>
                                    <td><span class="badge bg-info">{{ $encounter->status }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Diagnoses if available -->
                    @if ($encounter->consultations->count() > 0)
                        <div class="card shadow-sm mt-3">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">🩺 Diagnoses</h5>
                            </div>
                            <div class="card-body">
                                @foreach ($encounter->consultations as $consultation)
                                    @if ($consultation->diagnoses && $consultation->diagnoses->count() > 0)
                                        <ul class="mb-0">
                                            @foreach ($consultation->diagnoses as $diagnosis)
                                                <li>
                                                    <strong>{{ $diagnosis->icdCode->code ?? 'N/A' }}</strong> -
                                                    {{ $diagnosis->icdCode->description ?? $diagnosis->diagnosis }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Referral Form -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title mb-0">🔄 Referral Details</h3>
                        </div>
                        <div class="card-body">
                            <!-- Refer To Facility -->
                            <div class="form-group mb-3">
                                <label class="form-label">Refer To Facility <span class="text-danger">*</span></label>
                                <select name="to_facility_id" class="form-control form-select" required>
                                    <option value="">-- Select Facility --</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}"
                                            {{ old('to_facility_id') == $facility->id ? 'selected' : '' }}>
                                            {{ $facility->name }}
                                            @if ($facility->address)
                                                - {{ $facility->address }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_facility_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Referral Type -->
                            <div class="form-group mb-3">
                                <label class="form-label">Referral Type <span class="text-danger">*</span></label>
                                <select name="referral_type" id="referralType" class="form-control form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="service" {{ old('referral_type') == 'service' ? 'selected' : '' }}>
                                        Service Referral</option>
                                    <option value="patient" {{ old('referral_type') == 'patient' ? 'selected' : '' }}>
                                        Patient Referral</option>
                                </select>
                                <small class="form-text text-muted">
                                    <strong>Service:</strong> Referring for specific service/test<br>
                                    <strong>Patient:</strong> Referring entire patient for comprehensive care
                                </small>
                                @error('referral_type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Service Item (conditional) -->
                            <div class="form-group mb-3" id="serviceItemGroup" style="display: none;">
                                <label class="form-label">Service Required <span class="text-danger">*</span></label>
                                <select name="service_item_id" id="serviceItemId" class="form-control form-select">
                                    <option value="">-- Select Service --</option>
                                    @foreach ($serviceItems as $service)
                                        <option value="{{ $service->id }}"
                                            {{ old('service_item_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                            @if ($service->category)
                                                ({{ $service->category }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_item_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Reason for Referral -->
                            <div class="form-group mb-3">
                                <label class="form-label">Reason for Referral <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="6" required
                                    placeholder="Please provide detailed reason for this referral...">{{ old('reason') }}</textarea>
                                <small class="form-text text-muted">
                                    Include relevant clinical findings, test results, and reason for referral.
                                </small>
                                @error('reason')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <strong>ℹ️ Note:</strong> The receiving facility will be notified of this referral and can
                                accept or reject it.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-3 mb-3">
                <div class="col-12 text-right">
                    <a href="{{ route('facility.encounters.index') }}" class="btn btn-secondary btn-lg">
                        <i class="ti-arrow-left mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="ti-check mr-1"></i> Create Referral
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Show/hide service item field based on referral type
                $('#referralType').on('change', function() {
                    const type = $(this).val();
                    if (type === 'service') {
                        $('#serviceItemGroup').slideDown();
                        $('#serviceItemId').prop('required', true);
                    } else {
                        $('#serviceItemGroup').slideUp();
                        $('#serviceItemId').prop('required', false);
                        $('#serviceItemId').val('');
                    }
                });

                // Trigger on page load if there's an old value
                if ($('#referralType').val() === 'service') {
                    $('#serviceItemGroup').show();
                    $('#serviceItemId').prop('required', true);
                }
            });
        </script>
    @endpush
@endsection
