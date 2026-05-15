@extends('layouts.facility')

@section('title', 'Add Drug')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Add New
                                    Drug</h1>
                                <p class="text-muted mb-0">Add a new drug to your facility's pharmacy inventory</p>
                            </div>
                            <div>
                                <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Back to Pharmacy
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-alert-circle me-2"></i>
                                    <div>
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-alert-circle me-2"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('facility.pharmacy.store') }}" method="POST">
                            @csrf

                            <!-- Basic Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-package me-2 text-primary"></i>Basic Information
                                </h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-semibold">Drug Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}"
                                            placeholder="Enter drug name" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="dosage_form" class="form-label fw-semibold">Dosage Form <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('dosage_form') is-invalid @enderror"
                                            id="dosage_form" name="dosage_form" required>
                                            <option value="">Select Dosage Form</option>
                                            @foreach ($dosageForms as $form)
                                                <option value="{{ $form }}"
                                                    {{ old('dosage_form') == $form ? 'selected' : '' }}>
                                                    {{ $form }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('dosage_form')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="strength" class="form-label fw-semibold">Strength <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('strength') is-invalid @enderror"
                                            id="strength" name="strength" value="{{ old('strength') }}"
                                            placeholder="e.g., 500mg, 10ml" required>
                                        @error('strength')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="unit" class="form-label fw-semibold">Unit <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('unit') is-invalid @enderror"
                                            id="unit" name="unit" value="{{ old('unit') }}"
                                            placeholder="e.g., tablets, bottles" required>
                                        @error('unit')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="quantity" class="form-label fw-semibold">Initial Quantity <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                            id="quantity" name="quantity" value="{{ old('quantity', 0) }}" placeholder="0"
                                            min="0" required>
                                        @error('quantity')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3" placeholder="Enter drug description, usage instructions, etc.">{{ old('description') }}</textarea>
                                    <div class="form-text">Optional: Include usage instructions, side effects, etc.</div>
                                    @error('description')
                                        <div class="invalid-feedback d-block">
                                            <i class="ti-alert-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Pricing Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-money me-2 text-primary"></i>Pricing Information
                                </h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="unit_price" class="form-label fw-semibold">Unit Price (₦) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₦</span>
                                            <input type="number"
                                                class="form-control @error('unit_price') is-invalid @enderror"
                                                id="unit_price" name="unit_price" value="{{ old('unit_price') }}"
                                                placeholder="0.00" step="0.01" min="0" required>
                                        </div>
                                        <div class="form-text">Price per single unit (tablet, bottle, etc.)</div>
                                        @error('unit_price')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Total Value</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₦</span>
                                            <input type="text" class="form-control" id="total_value" value="0.00"
                                                readonly>
                                        </div>
                                        <div class="form-text">Calculated automatically</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Facility Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-home me-2 text-primary"></i>Facility Information
                                </h5>

                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="ti-info-alt me-2"></i>
                                    <div>
                                        <strong>This drug will be assigned to your facility:</strong>
                                        <span
                                            class="fw-semibold">{{ Auth::guard('web')->user()->facility->name ?? 'Current Facility' }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="facility_id" value="{{ $facilityId }}">
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Cancel
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-secondary me-2">
                                        <i class="ti-refresh me-1"></i> Reset Form
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-plus me-1"></i> Add Drug
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-info-alt me-2 text-info"></i>Drug Information Guidelines
                        </h6>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Use generic drug names when possible</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Include complete strength information</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Set accurate initial quantities</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ti-shield text-warning me-2"></i>
                                <span class="small">Prices are per individual unit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-help-circle me-2 text-primary"></i>Dosage Form Examples
                        </h6>
                        <div class="mb-3">
                            <p class="small text-muted mb-2">
                                <strong>Tablet:</strong> Paracetamol 500mg tablets
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Capsule:</strong> Amoxicillin 250mg capsules
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Syrup:</strong> Cough syrup 100ml bottles
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Injection:</strong> Insulin 10ml vials
                            </p>
                            <p class="small text-muted mb-0">
                                <strong>Ointment:</strong> Antibiotic cream 20g tubes
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calculate total value automatically
        function calculateTotal() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
            const totalValue = quantity * unitPrice;
            document.getElementById('total_value').value = totalValue.toFixed(2);
        }

        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('unit_price').addEventListener('input', calculateTotal);
    </script>

    <style>
        .form-control:focus,
        .form-select:focus {
            border-color: #01542B;
            box-shadow: 0 0 0 0.2rem rgba(1, 84, 43, 0.25);
        }

        .btn-primary {
            background-color: #01542B;
            border-color: #01542B;
        }

        .btn-primary:hover {
            background-color: #014121;
            border-color: #014121;
        }

        .card {
            border: none;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }
    </style>
@endsection
