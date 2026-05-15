@extends('layouts.app')

@section('title', 'Bulk Create Services')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Bulk Create Services</h4>
                        <p class="text-muted mb-0">Add multiple system services at once</p>
                    </div>
                    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
                        <i class="ti-arrow-left me-1"></i> Back to Services
                    </a>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form id="bulkCreateForm" action="{{ route('services.bulk.store') }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered" id="servicesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Service Name <span class="text-danger">*</span></th>
                                            <th>Type <span class="text-danger">*</span></th>
                                            <th>Price <span class="text-danger">*</span></th>
                                            <th>Description</th>
                                            <th width="80">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="servicesTableBody">
                                        <tr class="service-row" data-row="1">
                                            <td><span class="row-number">1</span></td>
                                            <td>
                                                <input type="text" class="form-control" name="services[0][name]" required
                                                    placeholder="e.g., Medical Consultation">
                                            </td>
                                            <td>
                                                <select class="form-select" name="services[0][type]" required>
                                                    <option value="">Select Type</option>
                                                    @foreach ($types as $key => $value)
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="number" step="0.01" min="0" class="form-control"
                                                        name="services[0][price]" required placeholder="0.00">
                                                </div>
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="services[0][description]" rows="1"
                                                    placeholder="Service description (optional)"></textarea>
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
                                    <button type="button" class="btn btn-outline-warning" onclick="clearTable()">
                                        <i class="ti-refresh me-1"></i> Clear All
                                    </button>
                                </div>
                                <div>
                                    <span class="text-muted me-3">
                                        <strong>Total Rows:</strong> <span id="rowCount">1</span>
                                    </span>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-save me-1"></i> Create All Services
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let rowCount = 1;

            function addRow() {
                rowCount++;
                const tbody = document.getElementById('servicesTableBody');
                const newRow = document.createElement('tr');
                newRow.className = 'service-row';
                newRow.setAttribute('data-row', rowCount);

                newRow.innerHTML = `
        <td><span class="row-number">${rowCount}</span></td>
        <td>
            <input type="text" class="form-control" name="services[${rowCount - 1}][name]" required placeholder="e.g., Medical Consultation">
        </td>
        <td>
            <select class="form-select" name="services[${rowCount - 1}][type]" required>
                <option value="">Select Type</option>
                @foreach ($types as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">₦</span>
                <input type="number" step="0.01" min="0" class="form-control" name="services[${rowCount - 1}][price]" required placeholder="0.00">
            </div>
        </td>
        <td>
            <textarea class="form-control" name="services[${rowCount - 1}][description]" rows="1" placeholder="Service description (optional)"></textarea>
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
                const rows = document.querySelectorAll('.service-row');

                if (rows.length > 1) {
                    row.remove();
                    updateRowNumbers();
                    updateRowCount();
                } else {
                    alert('You must have at least one row.');
                }
            }

            function updateRowNumbers() {
                const rows = document.querySelectorAll('.service-row');
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

            function clearTable() {
                if (confirm('Are you sure you want to clear all data?')) {
                    const tbody = document.getElementById('servicesTableBody');
                    tbody.innerHTML = `
            <tr class="service-row" data-row="1">
                <td><span class="row-number">1</span></td>
                <td>
                    <input type="text" class="form-control" name="services[0][name]" required placeholder="e.g., Medical Consultation">
                </td>
                <td>
                    <select class="form-select" name="services[0][type]" required>
                        <option value="">Select Type</option>
                        @foreach ($types as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <textarea class="form-control" name="services[0][description]" rows="1" placeholder="Service description (optional)"></textarea>
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
                const rows = document.querySelectorAll('.service-row');
                let hasValidData = false;

                rows.forEach(row => {
                    const name = row.querySelector('input[name$="[name]"]').value.trim();
                    const type = row.querySelector('select[name$="[type]"]').value;

                    if (name && type) {
                        hasValidData = true;
                    }
                });

                if (!hasValidData) {
                    e.preventDefault();
                    alert('Please fill in at least one complete service row with all required fields.');
                    return false;
                }
            });
        </script>
    @endpush
@endsection
