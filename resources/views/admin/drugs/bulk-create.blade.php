@extends('layouts.app')

@section('title', 'Bulk Create Drugs')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Bulk Create Drugs</h4>
                        <p class="text-muted mb-0">Add multiple drugs at once using table format</p>
                    </div>
                    <a href="{{ route('drugs.index') }}" class="btn btn-outline-secondary">
                        <i class="ti-arrow-left me-1"></i> Back to Drugs
                    </a>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form id="bulkCreateForm" action="{{ route('drugs.bulk.store') }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered" id="drugsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Name <span class="text-danger">*</span></th>
                                            <th>Description</th>
                                            <th>Dosage Form <span class="text-danger">*</span></th>
                                            <th>Strength <span class="text-danger">*</span></th>
                                            <th>Unit <span class="text-danger">*</span></th>
                                            <th>Unit Price <span class="text-danger">*</span></th>
                                            <th width="80">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="drugsTableBody">
                                        <tr class="drug-row" data-row="1">
                                            <td><span class="row-number">1</span></td>
                                            <td>
                                                <input type="text" class="form-control" name="drugs[0][name]" required
                                                    placeholder="e.g., Paracetamol">
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="drugs[0][description]" rows="1"
                                                    placeholder="e.g., Pain relief and fever reducer"></textarea>
                                            </td>
                                            <td>
                                                <select class="form-select" name="drugs[0][dosage_form]" required>
                                                    <option value="">Select Form</option>
                                                    <option value="Tablet">Tablet</option>
                                                    <option value="Capsule">Capsule</option>
                                                    <option value="Liquid">Liquid</option>
                                                    <option value="Injection">Injection</option>
                                                    <option value="Cream">Cream</option>
                                                    <option value="Ointment">Ointment</option>
                                                    <option value="Drops">Drops</option>
                                                    <option value="Spray">Spray</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="drugs[0][strength]"
                                                    required placeholder="e.g., 500mg">
                                            </td>
                                            <td>
                                                <select class="form-select" name="drugs[0][unit]" required>
                                                    <option value="">Select Unit</option>
                                                    <option value="Tablet">Tablet</option>
                                                    <option value="Capsule">Capsule</option>
                                                    <option value="Bottle">Bottle</option>
                                                    <option value="Tube">Tube</option>
                                                    <option value="Vial">Vial</option>
                                                    <option value="ml">ml</option>
                                                    <option value="g">g</option>
                                                    <option value="mg">mg</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="drugs[0][unit_price]"
                                                    step="0.01" min="0" required placeholder="0.00">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-row"
                                                    onclick="removeRow(this)">
                                                    <i class="ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <button type="button" class="btn btn-outline-primary" onclick="addRow()">
                                        <i class="ti-plus me-1"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="fillSampleData()">
                                        <i class="ti-file me-1"></i> Fill Sample Data
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="clearTable()">
                                        <i class="ti-refresh me-1"></i> Clear All
                                    </button>
                                </div>
                                <div>
                                    <span class="text-muted me-3">
                                        <strong>Total Rows:</strong> <span id="rowCount">1</span>
                                    </span>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-save me-1"></i> Create All Drugs
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="ti-help me-2"></i>Instructions
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Required Fields:</h6>
                                <ul class="small">
                                    <li><strong>Name:</strong> Drug name (e.g., Paracetamol)</li>
                                    <li><strong>Dosage Form:</strong> Physical form (Tablet, Capsule, etc.)</li>
                                    <li><strong>Strength:</strong> Potency (e.g., 500mg, 250mg)</li>
                                    <li><strong>Unit:</strong> Packaging unit (Tablet, Bottle, etc.)</li>
                                    <li><strong>Unit Price:</strong> Price per unit (e.g., 25.50)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Quick Tips:</h6>
                                <ul class="small">
                                    <li>Click "Add Row" to add more drug entries</li>
                                    <li>Use "Fill Sample Data" for quick testing</li>
                                    <li>Click the trash icon to remove individual rows</li>
                                    <li>Fields marked with <span class="text-danger">*</span> are required</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let rowCount = 1;

        function addRow() {
            rowCount++;
            const tbody = document.getElementById('drugsTableBody');
            const newRow = document.createElement('tr');
            newRow.className = 'drug-row';
            newRow.setAttribute('data-row', rowCount);

            newRow.innerHTML = `
        <td><span class="row-number">${rowCount}</span></td>
        <td>
            <input type="text" class="form-control" name="drugs[${rowCount - 1}][name]" required placeholder="e.g., Paracetamol">
        </td>
        <td>
            <textarea class="form-control" name="drugs[${rowCount - 1}][description]" rows="1" placeholder="e.g., Pain relief and fever reducer"></textarea>
        </td>
        <td>
            <select class="form-select" name="drugs[${rowCount - 1}][dosage_form]" required>
                <option value="">Select Form</option>
                <option value="Tablet">Tablet</option>
                <option value="Capsule">Capsule</option>
                <option value="Liquid">Liquid</option>
                <option value="Injection">Injection</option>
                <option value="Cream">Cream</option>
                <option value="Ointment">Ointment</option>
                <option value="Drops">Drops</option>
                <option value="Spray">Spray</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="drugs[${rowCount - 1}][strength]" required placeholder="e.g., 500mg">
        </td>
        <td>
            <select class="form-select" name="drugs[${rowCount - 1}][unit]" required>
                <option value="">Select Unit</option>
                <option value="Tablet">Tablet</option>
                <option value="Capsule">Capsule</option>
                <option value="Bottle">Bottle</option>
                <option value="Tube">Tube</option>
                <option value="Vial">Vial</option>
                <option value="ml">ml</option>
                <option value="g">g</option>
                <option value="mg">mg</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control" name="drugs[${rowCount - 1}][unit_price]" step="0.01" min="0" required placeholder="₦0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)">
                <i class="ti-trash"></i>
            </button>
        </td>
    `;

            tbody.appendChild(newRow);
            updateRowCount();
        }

        function removeRow(button) {
            const row = button.closest('tr');
            const rows = document.querySelectorAll('.drug-row');

            if (rows.length > 1) {
                row.remove();
                updateRowNumbers();
                updateRowCount();
            } else {
                alert('You must have at least one row.');
            }
        }

        function updateRowNumbers() {
            const rows = document.querySelectorAll('.drug-row');
            rows.forEach((row, index) => {
                row.setAttribute('data-row', index + 1);
                row.querySelector('.row-number').textContent = index + 1;

                // Update input names to maintain sequential order
                const inputs = row.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        const newName = name.replace(/\[\d+\]/, `[${index}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });

            rowCount = rows.length;
        }

        function updateRowCount() {
            document.getElementById('rowCount').textContent = rowCount;
        }

        function fillSampleData() {
            const sampleData = [{
                    name: 'Paracetamol',
                    description: 'Pain relief and fever reducer',
                    dosage_form: 'Tablet',
                    strength: '500mg',
                    unit: 'Tablet',
                    unit_price: '2500.00'
                },
                {
                    name: 'Amoxicillin',
                    description: 'Broad spectrum antibiotic',
                    dosage_form: 'Capsule',
                    strength: '250mg',
                    unit: 'Capsule',
                    unit_price: '4500.00'
                },
                {
                    name: 'Ibuprofen',
                    description: 'Anti-inflammatory and analgesic',
                    dosage_form: 'Tablet',
                    strength: '400mg',
                    unit: 'Tablet',
                    unit_price: '3200.00'
                }
            ];

            // Clear existing rows except first one
            const tbody = document.getElementById('drugsTableBody');
            tbody.innerHTML = '';

            // Add sample data rows
            sampleData.forEach((data, index) => {
                if (index === 0) {
                    // Update first row
                    const firstRow = document.createElement('tr');
                    firstRow.className = 'drug-row';
                    firstRow.setAttribute('data-row', 1);
                    firstRow.innerHTML = createRowHTML(data, 0);
                    tbody.appendChild(firstRow);
                } else {
                    // Add new rows
                    addRow();
                    const lastRow = tbody.lastElementChild;
                    fillRowWithData(lastRow, data);
                }
            });

            updateRowCount();
        }

        function fillRowWithData(row, data) {
            row.querySelector('input[name$="[name]"]').value = data.name;
            row.querySelector('textarea[name$="[description]"]').value = data.description;
            row.querySelector('select[name$="[dosage_form]"]').value = data.dosage_form;
            row.querySelector('input[name$="[strength]"]').value = data.strength;
            row.querySelector('select[name$="[unit]"]').value = data.unit;
            row.querySelector('input[name$="[unit_price]"]').value = data.unit_price;
        }

        function createRowHTML(data, index) {
            return `
        <td><span class="row-number">1</span></td>
        <td>
            <input type="text" class="form-control" name="drugs[${index}][name]" value="${data.name}" required placeholder="e.g., Paracetamol">
        </td>
        <td>
            <textarea class="form-control" name="drugs[${index}][description]" rows="1" placeholder="e.g., Pain relief and fever reducer">${data.description}</textarea>
        </td>
        <td>
            <select class="form-select" name="drugs[${index}][dosage_form]" required>
                <option value="">Select Form</option>
                <option value="Tablet" ${data.dosage_form === 'Tablet' ? 'selected' : ''}>Tablet</option>
                <option value="Capsule" ${data.dosage_form === 'Capsule' ? 'selected' : ''}>Capsule</option>
                <option value="Liquid" ${data.dosage_form === 'Liquid' ? 'selected' : ''}>Liquid</option>
                <option value="Injection" ${data.dosage_form === 'Injection' ? 'selected' : ''}>Injection</option>
                <option value="Cream" ${data.dosage_form === 'Cream' ? 'selected' : ''}>Cream</option>
                <option value="Ointment" ${data.dosage_form === 'Ointment' ? 'selected' : ''}>Ointment</option>
                <option value="Drops" ${data.dosage_form === 'Drops' ? 'selected' : ''}>Drops</option>
                <option value="Spray" ${data.dosage_form === 'Spray' ? 'selected' : ''}>Spray</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="drugs[${index}][strength]" value="${data.strength}" required placeholder="e.g., 500mg">
        </td>
        <td>
            <select class="form-select" name="drugs[${index}][unit]" required>
                <option value="">Select Unit</option>
                <option value="Tablet" ${data.unit === 'Tablet' ? 'selected' : ''}>Tablet</option>
                <option value="Capsule" ${data.unit === 'Capsule' ? 'selected' : ''}>Capsule</option>
                <option value="Bottle" ${data.unit === 'Bottle' ? 'selected' : ''}>Bottle</option>
                <option value="Tube" ${data.unit === 'Tube' ? 'selected' : ''}>Tube</option>
                <option value="Vial" ${data.unit === 'Vial' ? 'selected' : ''}>Vial</option>
                <option value="ml" ${data.unit === 'ml' ? 'selected' : ''}>ml</option>
                <option value="g" ${data.unit === 'g' ? 'selected' : ''}>g</option>
                <option value="mg" ${data.unit === 'mg' ? 'selected' : ''}>mg</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control" name="drugs[${index}][unit_price]" step="0.01" min="0" value="${data.unit_price}" required placeholder="₦0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)">
                <i class="ti-trash"></i>
            </button>
        </td>
    `;
        }

        function clearTable() {
            if (confirm('Are you sure you want to clear all data?')) {
                const tbody = document.getElementById('drugsTableBody');
                tbody.innerHTML = `
            <tr class="drug-row" data-row="1">
                <td><span class="row-number">1</span></td>
                <td>
                    <input type="text" class="form-control" name="drugs[0][name]" required placeholder="e.g., Paracetamol">
                </td>
                <td>
                    <textarea class="form-control" name="drugs[0][description]" rows="1" placeholder="e.g., Pain relief and fever reducer"></textarea>
                </td>
                <td>
                    <select class="form-select" name="drugs[0][dosage_form]" required>
                        <option value="">Select Form</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Liquid">Liquid</option>
                        <option value="Injection">Injection</option>
                        <option value="Cream">Cream</option>
                        <option value="Ointment">Ointment</option>
                        <option value="Drops">Drops</option>
                        <option value="Spray">Spray</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" name="drugs[0][strength]" required placeholder="e.g., 500mg">
                </td>
                <td>
                    <select class="form-select" name="drugs[0][unit]" required>
                        <option value="">Select Unit</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Bottle">Bottle</option>
                        <option value="Tube">Tube</option>
                        <option value="Vial">Vial</option>
                        <option value="ml">ml</option>
                        <option value="g">g</option>
                        <option value="mg">mg</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="drugs[0][unit_price]" step="0.01" min="0" required placeholder="₦0.00">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)">
                        <i class="ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;
                rowCount = 1;
                updateRowCount();
            }
        }

        // Form submission validation
        document.getElementById('bulkCreateForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.drug-row');
            let hasValidData = false;

            rows.forEach(row => {
                const name = row.querySelector('input[name$="[name]"]').value.trim();
                const dosageForm = row.querySelector('select[name$="[dosage_form]"]').value;
                const strength = row.querySelector('input[name$="[strength]"]').value.trim();
                const unit = row.querySelector('select[name$="[unit]"]').value;
                const unitPrice = row.querySelector('input[name$="[unit_price]"]').value;

                if (name && dosageForm && strength && unit && unitPrice) {
                    hasValidData = true;
                }
            });

            if (!hasValidData) {
                e.preventDefault();
                alert('Please fill in at least one complete drug row with all required fields.');
                return false;
            }
        });

        // Initialize row count on page load
        updateRowCount();
    </script>
@endpush
