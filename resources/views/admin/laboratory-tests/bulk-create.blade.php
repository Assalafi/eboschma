@extends('layouts.app')

@section('title', 'Bulk Add Laboratory Tests')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Bulk Add Laboratory Tests</h4>
                                <p class="text-muted mb-0">Add multiple laboratory tests at once</p>
                            </div>
                            <div>
                                <a href="{{ route('laboratory-tests.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('laboratory-tests.bulk.store') }}" method="POST" id="bulkForm">
                            @csrf

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Laboratory Tests</label>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-success" id="addRow">
                                            <i class="fe fe-plus me-1"></i> Add Row
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAll">
                                            <i class="fe fe-x me-1"></i> Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="testsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%">Test Name *</th>
                                            <th style="width: 30%">Sample Type *</th>
                                            <th style="width: 15%">Price (₦) *</th>
                                            <th style="width: 25%">Description</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="testsTableBody">
                                        <!-- Initial row -->
                                        <tr class="test-row">
                                            <td>
                                                <input type="text" class="form-control" name="tests[0][name]"
                                                    placeholder="e.g., Complete Blood Count" required>
                                            </td>
                                            <td>
                                                <select class="form-select" name="tests[0][sample_type]" required>
                                                    <option value="">Select Sample Type</option>
                                                    @foreach ($sampleTypes as $key => $type)
                                                        <option value="{{ $key }}">{{ $type }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="tests[0][price]"
                                                    placeholder="0.00" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="tests[0][description]" rows="2" placeholder="Optional description"></textarea>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-row">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Create All Tests
                                        </button>
                                        <a href="{{ route('laboratory-tests.index') }}" class="btn btn-outline-secondary">
                                            <i class="fe fe-x me-1"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('laboratory-tests.create') }}" class="btn btn-outline-primary">
                                <i class="fe fe-plus me-2"></i>Add Single Test
                            </a>
                            <a href="{{ route('laboratory-tests.upload') }}" class="btn btn-outline-success">
                                <i class="fe fe-upload me-2"></i>Upload Excel File
                            </a>
                            <a href="{{ route('laboratory-tests.template') }}" class="btn btn-outline-info">
                                <i class="fe fe-download me-2"></i>Download Template
                            </a>
                            <a href="{{ route('laboratory-tests.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-list me-2"></i>All Tests
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Sample Types</h6>
                        <div class="row">
                            @foreach ($sampleTypes as $key => $type)
                                <div class="col-6 mb-2">
                                    <small class="text-muted">{{ $type }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Instructions</h6>
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Fill in test details for each row</li>
                            <li class="mb-2">Test name and sample type are required</li>
                            <li class="mb-2">Price must be a valid number</li>
                            <li class="mb-2">Description is optional</li>
                            <li class="mb-2">Click "Add Row" for more tests</li>
                            <li class="mb-0">Empty rows will be ignored</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let rowCount = 1;

            // Add new row
            $('#addRow').on('click', function() {
                const newRow = `
                <tr class="test-row">
                    <td>
                        <input type="text" 
                               class="form-control" 
                               name="tests[${rowCount}][name]" 
                               placeholder="e.g., Complete Blood Count"
                               required>
                    </td>
                    <td>
                        <select class="form-select" name="tests[${rowCount}][sample_type]" required>
                            <option value="">Select Sample Type</option>
                            @foreach ($sampleTypes as $key => $type)
                                <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" 
                               class="form-control" 
                               name="tests[${rowCount}][price]" 
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               required>
                    </td>
                    <td>
                        <textarea class="form-control" 
                                  name="tests[${rowCount}][description]" 
                                  rows="2"
                                  placeholder="Optional description"></textarea>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="fe fe-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                $('#testsTableBody').append(newRow);
                rowCount++;
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('#testsTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('You must have at least one row.');
                }
            });

            // Clear all rows
            $('#clearAll').on('click', function() {
                if (confirm('Are you sure you want to clear all rows?')) {
                    $('#testsTableBody').empty();
                    // Add one empty row back
                    rowCount = 0;
                    $('#addRow').click();
                }
            });

            // Form validation
            $('#bulkForm').on('submit', function(e) {
                let valid = true;
                let hasData = false;

                $('.test-row').each(function() {
                    const name = $(this).find('input[name*="[name]"]').val().trim();
                    const sampleType = $(this).find('select[name*="[sample_type]"]').val();
                    const price = $(this).find('input[name*="[price]"]').val();

                    if (name || sampleType || price) {
                        hasData = true;
                        if (!name || !sampleType || !price) {
                            valid = false;
                            $(this).find('input, select').addClass('is-invalid');
                        } else {
                            $(this).find('input, select').removeClass('is-invalid');
                        }
                    }
                });

                if (!hasData) {
                    e.preventDefault();
                    alert('Please add at least one test.');
                    return false;
                }

                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields for each test.');
                    return false;
                }
            });
        });
    </script>
@endpush
