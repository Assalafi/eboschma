@extends('layouts.app')

@section('title', 'Edit Doctor Ward Assignment')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Edit Doctor Ward Assignment</h4>
                                    <p class="text-muted mb-0">Update doctor ward assignment details</p>
                                </div>
                                <div>
                                    <a href="{{ route('doctor-ward.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Assignments
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('doctor-ward.update', $assignment->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="facility_id" class="form-label">Facility <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="facility_id" required>
                                            <option value="">Select Facility</option>
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->id }}" 
                                                    {{ $assignment->ward->facility_id == $facility->id ? 'selected' : '' }}>
                                                    {{ $facility->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ward_id" class="form-label">Ward <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ward_id') is-invalid @enderror" 
                                            id="ward_id" name="ward_id" required>
                                            <option value="">Select Ward</option>
                                            @foreach ($wards as $ward)
                                                <option value="{{ $ward->id }}" 
                                                    {{ $assignment->ward_id == $ward->id ? 'selected' : '' }}>
                                                    {{ $ward->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('ward_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="user_id" class="form-label">Doctor <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" name="user_id" required>
                                            <option value="">Select Doctor</option>
                                            @foreach ($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" 
                                                    {{ $assignment->user_id == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->name }} ({{ $doctor->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="assigned_date" class="form-label">Assigned Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('assigned_date') is-invalid @enderror" 
                                            id="assigned_date" name="assigned_date"
                                            value="{{ $assignment->assigned_date ? $assignment->assigned_date->format('Y-m-d') : '' }}" required>
                                        @error('assigned_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                name="is_active" id="is_active" value="1"
                                                {{ $assignment->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active Assignment
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Uncheck this to deactivate the assignment without deleting it.
                                        </small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Update Assignment
                                        </button>
                                        <a href="{{ route('doctor-ward.index') }}" class="btn btn-outline-secondary ms-2">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Load wards when facility changes
        $('#facility_id').on('change', function() {
            var facilityId = $(this).val();
            var wardSelect = $('#ward_id');
            var currentWardId = '{{ $assignment->ward_id }}';
            
            wardSelect.html('<option value="">Loading wards...</option>');
            
            if (facilityId) {
                $.get('/api/facilities/' + facilityId + '/wards', function(data) {
                    wardSelect.html('<option value="">Select Ward</option>');
                    $.each(data, function(index, ward) {
                        var selected = ward.id == currentWardId ? 'selected' : '';
                        wardSelect.append('<option value="' + ward.id + '" ' + selected + '>' + ward.name + '</option>');
                    });
                }).fail(function() {
                    wardSelect.html('<option value="">Error loading wards</option>');
                });
            } else {
                wardSelect.html('<option value="">Select Ward</option>');
            }
        });

        // Load doctors when facility changes
        $('#facility_id').on('change', function() {
            var facilityId = $(this).val();
            var currentDoctorId = '{{ $assignment->user_id }}';
            
            if (facilityId) {
                $.get('{{ route("doctor-ward.doctors-by-facility") }}', {facility_id: facilityId}, function(data) {
                    var doctorSelect = $('#user_id');
                    doctorSelect.html('<option value="">Select Doctor</option>');
                    $.each(data, function(index, doctor) {
                        var selected = doctor.id == currentDoctorId ? 'selected' : '';
                        doctorSelect.append('<option value="' + doctor.id + '" ' + selected + '>' + doctor.name + ' (' + doctor.email + ')</option>');
                    });
                });
            }
        });
    </script>
    @endpush
@endsection
