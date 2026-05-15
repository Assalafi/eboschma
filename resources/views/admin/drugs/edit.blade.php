@extends('layouts.app')

@section('title', 'Edit Drug')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Edit Drug</h4>
                        <p class="text-muted mb-0">Update drug information and details</p>
                    </div>
                    <a href="{{ route('drugs.index') }}" class="btn btn-outline-secondary">
                        <i class="ti-arrow-left me-1"></i> Back to Drugs
                    </a>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('drugs.update', $drug->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary">
                                        <i class="ti-info-alt me-2"></i>Basic Information
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Drug Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $drug->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dosage_form" class="form-label">Dosage Form <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('dosage_form') is-invalid @enderror"
                                            id="dosage_form" name="dosage_form" required>
                                            <option value="">Select Dosage Form</option>
                                            @foreach ($dosageForms as $form)
                                                <option value="{{ $form }}"
                                                    {{ old('dosage_form', $drug->dosage_form) == $form ? 'selected' : '' }}>
                                                    {{ $form }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('dosage_form')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="strength" class="form-label">Strength <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('strength') is-invalid @enderror"
                                            id="strength" name="strength" value="{{ old('strength', $drug->strength) }}"
                                            placeholder="e.g., 500mg, 10ml" required>
                                        @error('strength')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="unit" class="form-label">Unit <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('unit') is-invalid @enderror" id="unit"
                                            name="unit" required>
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit }}"
                                                    {{ old('unit', $drug->unit) == $unit ? 'selected' : '' }}>
                                                    {{ $unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="unit_price" class="form-label">Unit Price ($) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('unit_price') is-invalid @enderror"
                                            id="unit_price" name="unit_price"
                                            value="{{ old('unit_price', $drug->unit_price) }}" step="0.01"
                                            min="0" placeholder="0.00" required>
                                        @error('unit_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                            rows="3">{{ old('description', $drug->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('drugs.index') }}" class="btn btn-outline-secondary">
                                            <i class="ti-arrow-left me-1"></i> Cancel
                                        </a>
                                        <div>
                                            <button type="reset" class="btn btn-outline-warning me-2"
                                                onclick="if(confirm('Reset all changes?')) this.form.reset();">
                                                <i class="ti-refresh me-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-save me-1"></i> Update Drug
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-suggest strength based on dosage form
            $('#dosage_form').on('change', function() {
                const dosageForm = $(this).val();
                const strengthField = $('#strength');

                // Common strength suggestions based on dosage form
                const strengthSuggestions = {
                    'Tablet': ['500mg', '250mg', '100mg', '50mg', '10mg'],
                    'Capsule': ['500mg', '250mg', '100mg', '50mg'],
                    'Liquid': ['100ml', '250ml', '500ml', '1000ml'],
                    'Syrup': ['100ml', '250ml', '500ml'],
                    'Injection': ['10ml', '5ml', '2ml', '1ml'],
                    'Cream': ['10g', '20g', '30g', '50g']
                };

                if (strengthSuggestions[dosageForm]) {
                    strengthField.attr('placeholder', 'e.g., ' + strengthSuggestions[dosageForm].join(
                    ', '));
                }
            });

            // Auto-suggest unit based on dosage form
            $('#dosage_form').on('change', function() {
                const dosageForm = $(this).val();
                const unitField = $('#unit');

                const unitSuggestions = {
                    'Tablet': 'Tablet',
                    'Capsule': 'Capsule',
                    'Liquid': 'Bottle',
                    'Syrup': 'Bottle',
                    'Injection': 'Vial',
                    'Cream': 'Tube'
                };

                if (unitSuggestions[dosageForm]) {
                    unitField.val(unitSuggestions[dosageForm]);
                }
            });
        });
    </script>
@endpush
