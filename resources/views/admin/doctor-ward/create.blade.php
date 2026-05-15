@extends('layouts.app')

@section('title', 'Assign Doctors to Ward')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Assign Doctors to Ward</h4>
                                    <p class="text-muted mb-0">Assign multiple doctors to a ward</p>
                                </div>
                                <div>
                                    <a href="{{ route('doctor-ward.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Assignments
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('doctor-ward.store') }}" id="doctorWardForm">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="facility_id" class="form-label">Facility <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="facility_id" required>
                                            <option value="">Select Facility</option>
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ward_id" class="form-label">Ward <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ward_id') is-invalid @enderror" id="ward_id"
                                            name="ward_id" required>
                                            <option value="">Select Ward</option>
                                        </select>
                                        @error('ward_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Select a facility and ward first, then assign doctors below. You can assign multiple
                                    doctors at once.
                                </div>

                                <div id="assignmentsContainer">
                                    <div class="assignment-row row mb-3" data-index="0">
                                        <div class="col-md-5">
                                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                            <select class="form-control doctor-select" name="assignments[0][user_id]"
                                                required>
                                                <option value="">Select Doctor</option>
                                                @foreach ($doctors as $doctor)
                                                    <option value="{{ $doctor->id }}">{{ $doctor->name }}
                                                        ({{ $doctor->email }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Assigned Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="assignments[0][assigned_date]"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="assignments[0][is_active]" id="assignment_active_0" value="1"
                                                    checked>
                                                <label class="form-check-label" for="assignment_active_0">
                                                    Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm w-100 remove-assignment"
                                                onclick="removeAssignment(0)" style="display: none;">
                                                <i class="fe fe-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-outline-primary" onclick="addAssignment()">
                                            <i class="fe fe-plus me-1"></i> Add Another Doctor
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Save Assignments
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
        let assignmentIndex = 1;

        // Load wards when facility changes
        $('#facility_id').on('change', function() {
            var facilityId = $(this).val();
            var wardSelect = $('#ward_id');
            
            wardSelect.html('<option value="">Loading wards...</option>');
            
            if (facilityId) {
                $.get('/api/facilities/' + facilityId + '/wards', function(data) {
                    wardSelect.html('<option value="">Select Ward</option>');
                    $.each(data, function(index, ward) {
                        wardSelect.append('<option value="' + ward.id + '">' + ward.name + '</option>');
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
            
            if (facilityId) {
                $.get('{{ route("doctor-ward.doctors-by-facility") }}', {facility_id: facilityId}, function(data) {
                    // Update all doctor selects
                    $('.doctor-select').each(function() {
                        var currentValue = $(this).val();
                        $(this).html('<option value="">Select Doctor</option>');
                        $.each(data, function(index, doctor) {
                            $(this).append('<option value="' + doctor.id + '">' + doctor.name + ' (' + doctor.email + ')</option>');
                        }.bind(this));
                        $(this).val(currentValue);
                    });
                });
            }
        });

        function addAssignment() {
            var container = $('#assignmentsContainer');
            var newAssignment = $('.assignment-row:first').clone();
            
            // Update index and clear values
            newAssignment.attr('data-index', assignmentIndex);
            newAssignment.find('select, input').each(function() {
                var name = $(this).attr('name').replace(/\[\d+\]/, '[' + assignmentIndex + ']');
                $(this).attr('name', name);
                
                if ($(this).attr('id')) {
                    var id = $(this).attr('id').replace(/_\d+$/, '_' + assignmentIndex);
                    $(this).attr('id', id);
                }
                
                // Clear values except date and active checkbox
                if ($(this).attr('type') === 'date') {
                    $(this).val('{{ date('Y-m-d') }}');
                } else if ($(this).attr('type') === 'checkbox') {
                    $(this).prop('checked', true);
                } else {
                    $(this).val('');
                }
            });
            
            // Show remove button
            newAssignment.find('.remove-assignment').show();
            
            container.append(newAssignment);
            assignmentIndex++;
        }

        function removeAssignment(index) {
            $('.assignment-row[data-index="' + index + '"]').remove();
        }

        // Show remove button for first assignment if there are multiple
        $(document).ready(function() {
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            var assignments = $('.assignment-row');
            if (assignments.length > 1) {
                assignments.find('.remove-assignment').show();
            } else {
                assignments.find('.remove-assignment').hide();
            }
        }
    </script>
    @endpush
@endsection
