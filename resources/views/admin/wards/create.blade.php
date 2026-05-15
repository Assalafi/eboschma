@extends('layouts.app')

@section('title', 'Create Wards')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Create Wards</h4>
                                    <p class="text-muted mb-0">Add multiple wards to a facility</p>
                                </div>
                                <div>
                                    <a href="{{ route('wards.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Wards
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('wards.store') }}" id="wardForm">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="facility_id" class="form-label">Facility <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('facility_id') is-invalid @enderror"
                                            id="facility_id" name="facility_id" required>
                                            <option value="">Select Facility</option>
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->id }}"
                                                    {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                                    {{ $facility->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('facility_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Select a facility first, then add wards below. You can add multiple wards at once.
                                </div>

                                <div id="wardsContainer">
                                    <div class="ward-row row mb-3" data-index="0">
                                        <div class="col-md-6">
                                            <label class="form-label">Ward Name <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('wards.0.name') is-invalid @enderror"
                                                name="wards[0][name]" placeholder="Enter ward name" required>
                                            @error('wards.0.name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="wards[0][is_active]"
                                                    id="ward_active_0" value="1" checked>
                                                <label class="form-check-label" for="ward_active_0">Active</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-ward"
                                                style="display: none;">
                                                <i class="fe fe-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <button type="button" id="addWard" class="btn btn-outline-primary">
                                        <i class="fe fe-plus me-1"></i> Add Another Ward
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Save Wards
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
            let wardIndex = 1;

            $('#addWard').click(function() {
                const newRow = `
                    <div class="ward-row row mb-3" data-index="${wardIndex}">
                        <div class="col-md-6">
                            <label class="form-label">Ward Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="wards[${wardIndex}][name]" 
                                   placeholder="Enter ward name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="wards[${wardIndex}][is_active]" 
                                       id="ward_active_${wardIndex}" value="1" checked>
                                <label class="form-check-label" for="ward_active_${wardIndex}">Active</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-ward">
                                <i class="fe fe-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                $('#wardsContainer').append(newRow);
                wardIndex++;
                updateRemoveButtons();
            });

            $(document).on('click', '.remove-ward', function() {
                $(this).closest('.ward-row').remove();
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                const rows = $('.ward-row');
                if (rows.length > 1) {
                    $('.remove-ward').show();
                } else {
                    $('.remove-ward').hide();
                }
            }
        });
    </script>
@endpush
