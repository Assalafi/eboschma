@extends('layouts.facility')

@section('title', 'Bulk Add Drugs')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Bulk
                                    Add Drugs</h1>
                                <p class="text-muted mb-0">Add multiple drugs to your facility's pharmacy inventory at once
                                </p>
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

                        <form action="{{ route('facility.pharmacy.bulk-store') }}" method="POST" id="bulkForm">
                            @csrf

                            <!-- Quick Actions -->
                            <div class="card bg-light mb-4" style="border-radius: 8px;">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0 fw-bold" style="color: #01542B;">
                                                <i class="ti-settings me-2"></i>Quick Actions
                                            </h6>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                                onclick="addDrugRow()">
                                                <i class="ti-plus me-1"></i>Add Drug
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary me-2"
                                                onclick="clearAllRows()">
                                                <i class="ti-x me-1"></i>Clear All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="fillExample()">
                                                <i class="ti-file me-1"></i>Fill Example
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Drug Entries Container -->
                            <div id="drugEntries">
                                <!-- Initial drug row -->
                                <div class="drug-row mb-3 p-3 border rounded" data-row="1">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Drug Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="drugs[0][name]"
                                                placeholder="Enter drug name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Dosage Form <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="drugs[0][dosage_form]" required>
                                                <option value="">Select Form</option>
                                                @foreach ($dosageForms as $form)
                                                    <option value="{{ $form }}">{{ $form }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Strength <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="drugs[0][strength]"
                                                placeholder="e.g., 500mg" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Unit <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="drugs[0][unit]"
                                                placeholder="e.g., tablets" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Quantity <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="drugs[0][quantity]"
                                                placeholder="0" min="0" value="0" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Unit Price (₦) <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">₦</span>
                                                <input type="number" class="form-control" name="drugs[0][unit_price]"
                                                    placeholder="0.00" step="0.01" min="0" value="0"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" name="drugs[0][description]" rows="1" placeholder="Optional description"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeDrugRow(this)">
                                                <i class="ti-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                <div>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                        <i class="ti-refresh me-1"></i> Reset Form
                                    </button>
                                </div>
                                <div>
                                    <a href="{{ route('facility.pharmacy.import') }}" class="btn btn-outline-info me-2">
                                        <i class="ti-upload me-1"></i>Import Excel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="ti-package me-1"></i>Add All Drugs
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
                            <i class="ti-info-alt me-2 text-info"></i>Bulk Creation Tips
                        </h6>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Add multiple drugs at once</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Use consistent naming</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Set accurate quantities</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ti-shield text-warning me-2"></i>
                                <span class="small">Review before submitting</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-file me-2 text-primary"></i>Alternative Methods
                        </h6>
                        <div class="mb-3">
                            <p class="small text-muted mb-3">
                                For large quantities, consider using Excel import for faster data entry.
                            </p>
                            <a href="{{ route('facility.pharmacy.import') }}" class="btn btn-info btn-sm w-100 mb-2">
                                <i class="ti-upload me-2"></i>Import Excel File
                            </a>
                            <a href="{{ route('facility.pharmacy.download-template') }}"
                                class="btn btn-outline-primary btn-sm w-100">
                                <i class="ti-download me-2"></i>Download Template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-help-circle me-2 text-primary"></i>Keyboard Shortcuts
                        </h6>
                        <div class="mb-3">
                            <p class="small text-muted mb-2">
                                <strong>Ctrl + Enter:</strong> Submit form
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Tab:</strong> Move to next field
                            </p>
                            <p class="small text-muted mb-0">
                                <strong>Shift + Tab:</strong> Move to previous field
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let drugRowCount = 1;

        function addDrugRow() {
            drugRowCount++;
            const rowHtml = `
                <div class="drug-row mb-3 p-3 border rounded" data-row="${drugRowCount}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Drug Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="drugs[${drugRowCount - 1}][name]" 
                                   placeholder="Enter drug name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Dosage Form <span class="text-danger">*</span></label>
                            <select class="form-select" name="drugs[${drugRowCount - 1}][dosage_form]" required>
                                <option value="">Select Form</option>
                                @foreach ($dosageForms as $form)
                                    <option value="{{ $form }}">{{ $form }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Strength <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="drugs[${drugRowCount - 1}][strength]" 
                                   placeholder="e.g., 500mg" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="drugs[${drugRowCount - 1}][unit]" 
                                   placeholder="e.g., tablets" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="drugs[${drugRowCount - 1}][quantity]" 
                                   placeholder="0" min="0" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Unit Price (₦) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" class="form-control" name="drugs[${drugRowCount - 1}][unit_price]" 
                                       placeholder="0.00" step="0.01" min="0" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="drugs[${drugRowCount - 1}][description]" rows="1"
                                      placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDrugRow(this)">
                                <i class="ti-trash me-1"></i>Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('drugEntries').insertAdjacentHTML('beforeend', rowHtml);
        }

        function removeDrugRow(button) {
            const rows = document.querySelectorAll('.drug-row');
            if (rows.length > 1) {
                button.closest('.drug-row').remove();
                updateRowIndices();
            } else {
                alert('You must have at least one drug row.');
            }
        }

        function clearAllRows() {
            document.querySelectorAll('.drug-row').forEach(row => {
                row.querySelectorAll('input, textarea').forEach(field => {
                    if (field.type === 'number') {
                        field.value = '0';
                    } else {
                        field.value = '';
                    }
                });
                row.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                });
            });
        }

        function resetForm() {
            document.getElementById('bulkForm').reset();
            const rows = document.querySelectorAll('.drug-row');
            rows.forEach((row, index) => {
                if (index > 0) {
                    row.remove();
                }
            });
            drugRowCount = 1;
        }

        function fillExample() {
            const examples = [{
                    name: 'Paracetamol',
                    dosage_form: 'Tablet',
                    strength: '500mg',
                    unit: 'tablets',
                    quantity: 100,
                    unit_price: 50.00,
                    description: 'Pain reliever and fever reducer'
                },
                {
                    name: 'Amoxicillin',
                    dosage_form: 'Capsule',
                    strength: '250mg',
                    unit: 'capsules',
                    quantity: 50,
                    unit_price: 120.00,
                    description: 'Antibiotic for bacterial infections'
                },
                {
                    name: 'Ibuprofen',
                    dosage_form: 'Tablet',
                    strength: '400mg',
                    unit: 'tablets',
                    quantity: 75,
                    unit_price: 80.00,
                    description: 'Anti-inflammatory pain medication'
                }
            ];

            const rows = document.querySelectorAll('.drug-row');
            rows.forEach((row, index) => {
                if (index < examples.length) {
                    const example = examples[index];
                    row.querySelector('input[name$="[name]"]').value = example.name;
                    row.querySelector('select[name$="[dosage_form]"]').value = example.dosage_form;
                    row.querySelector('input[name$="[strength]"]').value = example.strength;
                    row.querySelector('input[name$="[unit]"]').value = example.unit;
                    row.querySelector('input[name$="[quantity]"]').value = example.quantity;
                    row.querySelector('input[name$="[unit_price]"]').value = example.unit_price;
                    row.querySelector('textarea[name$="[description]"]').value = example.description;
                }
            });

            // Add more rows if needed
            for (let i = rows.length; i < examples.length; i++) {
                addDrugRow();
                setTimeout(() => {
                    const newRow = document.querySelectorAll('.drug-row')[i];
                    const example = examples[i];
                    newRow.querySelector('input[name$="[name]"]').value = example.name;
                    newRow.querySelector('select[name$="[dosage_form]"]').value = example.dosage_form;
                    newRow.querySelector('input[name$="[strength]"]').value = example.strength;
                    newRow.querySelector('input[name$="[unit]"]').value = example.unit;
                    newRow.querySelector('input[name$="[quantity]"]').value = example.quantity;
                    newRow.querySelector('input[name$="[unit_price]"]').value = example.unit_price;
                    newRow.querySelector('textarea[name$="[description]"]').value = example.description;
                }, 100);
            }
        }

        function updateRowIndices() {
            const rows = document.querySelectorAll('.drug-row');
            rows.forEach((row, index) => {
                const inputs = row.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    const name = input.name;
                    if (name) {
                        const newName = name.replace(/\[\d+\]/, `[${index}]`);
                        input.name = newName;
                    }
                });
            });
        }

        // Form submission validation
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            const drugRows = document.querySelectorAll('.drug-row');
            let hasValidData = false;

            drugRows.forEach(row => {
                const name = row.querySelector('input[name$="[name]"]').value.trim();
                const dosageForm = row.querySelector('select[name$="[dosage_form]"]').value;
                const strength = row.querySelector('input[name$="[strength]"]').value.trim();
                const unit = row.querySelector('input[name$="[unit]"]').value.trim();
                const quantity = row.querySelector('input[name$="[quantity]"]').value;
                const unitPrice = row.querySelector('input[name$="[unit_price]"]').value;

                if (name && dosageForm && strength && unit && quantity && unitPrice) {
                    hasValidData = true;
                }
            });

            if (!hasValidData) {
                e.preventDefault();
                alert('Please fill in at least one complete drug entry.');
                return;
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('bulkForm').submit();
            }
        });
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

        .drug-row {
            background-color: #fafafa;
            transition: all 0.3s ease;
        }

        .drug-row:hover {
            background-color: #f0f0f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection
