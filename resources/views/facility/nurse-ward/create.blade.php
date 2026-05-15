@extends('facility.layouts.app')

@section('title', 'Assign Nurses to Ward')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Assign Nurses to Ward</h4>
                                    <p class="text-muted mb-0">Assign staff members to a ward</p>
                                </div>
                                <div>
                                    <a href="{{ route('facility.nurse-ward.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Assignments
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('facility.nurse-ward.store') }}" id="nurseWardForm">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="ward_id" class="form-label">Ward <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ward_id') is-invalid @enderror" id="ward_id"
                                            name="ward_id" required>
                                            <option value="">Select Ward</option>
                                            @foreach ($wards as $ward)
                                                <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('ward_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Select a ward first, then add nurses below. You can assign multiple nurses at once.
                                </div>

                                <div id="assignmentsContainer">
                                    <div class="assignment-row row mb-3" data-index="0">
                                        <div class="col-md-5">
                                            <label class="form-label">Nurse/Staff <span class="text-danger">*</span></label>
                                            <select class="form-control nurse-select" name="assignments[0][user_id]"
                                                required>
                                                <option value="">Select Staff</option>
                                                @foreach ($nurses as $nurse)
                                                    <option value="{{ $nurse->id }}">{{ $nurse->name }}
                                                        ({{ $nurse->email }})</option>
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
                                                <label class="form-check-label" for="assignment_active_0">Active</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-assignment"
                                                style="display: none;">
                                                <i class="fe fe-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <button type="button" id="addAssignment" class="btn btn-outline-primary">
                                        <i class="fe fe-plus me-1"></i> Add Another Staff
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Save Assignments
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let assignmentIndex = 1;
            const nursesData = @json($nurses);

            function getNurseOptions() {
                let options = '<option value="">Select Staff</option>';
                nursesData.forEach(function(nurse) {
                    options += `<option value="${nurse.id}">${nurse.name} (${nurse.email})</option>`;
                });
                return options;
            }

            $('#addAssignment').click(function() {
                const newRow = `
                    <div class="assignment-row row mb-3" data-index="${assignmentIndex}">
                        <div class="col-md-5">
                            <label class="form-label">Nurse/Staff <span class="text-danger">*</span></label>
                            <select class="form-control nurse-select" name="assignments[${assignmentIndex}][user_id]" required>
                                ${getNurseOptions()}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Assigned Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="assignments[${assignmentIndex}][assigned_date]" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="assignments[${assignmentIndex}][is_active]" 
                                       id="assignment_active_${assignmentIndex}" value="1" checked>
                                <label class="form-check-label" for="assignment_active_${assignmentIndex}">Active</label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-assignment">
                                <i class="fe fe-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                $('#assignmentsContainer').append(newRow);
                assignmentIndex++;
                updateRemoveButtons();
            });

            $(document).on('click', '.remove-assignment', function() {
                $(this).closest('.assignment-row').remove();
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                const rows = $('.assignment-row');
                if (rows.length > 1) {
                    $('.remove-assignment').show();
                } else {
                    $('.remove-assignment').hide();
                }
            }
        });
    </script>
@endpush
