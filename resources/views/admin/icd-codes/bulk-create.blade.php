@extends('layouts.app')

@section('title', 'Bulk Add ICD Codes')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Bulk Add ICD Codes</h4>
                                <p class="text-muted mb-0">Add multiple ICD codes at once</p>
                            </div>
                            <div>
                                <a href="{{ route('icd-codes.index') }}" class="btn btn-outline-secondary">
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

                        <form action="{{ route('icd-codes.bulk.store') }}" method="POST" id="bulkForm">
                            @csrf

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">ICD Codes</label>
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
                                <table class="table table-bordered" id="codesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%">ICD Code *</th>
                                            <th style="width: 45%">Description *</th>
                                            <th style="width: 30%">Category *</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="codesTableBody">
                                        <!-- Initial row -->
                                        <tr class="code-row">
                                            <td>
                                                <input type="text" class="form-control font-monospace"
                                                    name="codes[0][code]" placeholder="e.g., A00.0" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="codes[0][description]"
                                                    placeholder="e.g., Cholera due to Vibrio cholerae 01" required>
                                            </td>
                                            <td>
                                                <select class="form-select" name="codes[0][category]" required>
                                                    <option value="">Select Category</option>
                                                    @foreach ($categories as $key => $category)
                                                        <option value="{{ $category }}">{{ $key }}</option>
                                                    @endforeach
                                                </select>
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
                                            <i class="fe fe-save me-1"></i> Create All Codes
                                        </button>
                                        <a href="{{ route('icd-codes.index') }}" class="btn btn-outline-secondary">
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
                            <a href="{{ route('icd-codes.create') }}" class="btn btn-outline-primary">
                                <i class="fe fe-plus me-2"></i>Add Single Code
                            </a>
                            <a href="{{ route('icd-codes.upload') }}" class="btn btn-outline-success">
                                <i class="fe fe-upload me-2"></i>Upload Excel File
                            </a>
                            <a href="{{ route('icd-codes.template') }}" class="btn btn-outline-info">
                                <i class="fe fe-download me-2"></i>Download Template
                            </a>
                            <a href="{{ route('icd-codes.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-list me-2"></i>All Codes
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">ICD-10 Categories</h6>
                        <div class="row">
                            @foreach ($categories as $key => $category)
                                <div class="col-12 mb-2">
                                    <small class="text-muted"><strong>{{ $key }}:</strong>
                                        {{ Str::limit($category, 40) }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Instructions</h6>
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Fill in ICD code details for each row</li>
                            <li class="mb-2">Code and description are required</li>
                            <li class="mb-2">Category must be selected from dropdown</li>
                            <li class="mb-2">Click "Add Row" for more codes</li>
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
                <tr class="code-row">
                    <td>
                        <input type="text" 
                               class="form-control font-monospace" 
                               name="codes[${rowCount}][code]" 
                               placeholder="e.g., A00.0"
                               required>
                    </td>
                    <td>
                        <input type="text" 
                               class="form-control" 
                               name="codes[${rowCount}][description]" 
                               placeholder="e.g., Cholera due to Vibrio cholerae 01"
                               required>
                    </td>
                    <td>
                        <select class="form-select" name="codes[${rowCount}][category]" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $key => $category)
                                <option value="{{ $category }}">{{ $key }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="fe fe-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                $('#codesTableBody').append(newRow);
                rowCount++;
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('#codesTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('You must have at least one row.');
                }
            });

            // Clear all rows
            $('#clearAll').on('click', function() {
                if (confirm('Are you sure you want to clear all rows?')) {
                    $('#codesTableBody').empty();
                    // Add one empty row back
                    rowCount = 0;
                    $('#addRow').click();
                }
            });

            // Form validation
            $('#bulkForm').on('submit', function(e) {
                let valid = true;
                let hasData = false;

                $('.code-row').each(function() {
                    const code = $(this).find('input[name*="[code]"]').val().trim();
                    const description = $(this).find('input[name*="[description]"]').val().trim();
                    const category = $(this).find('select[name*="[category]"]').val();

                    if (code || description || category) {
                        hasData = true;
                        if (!code || !description || !category) {
                            valid = false;
                            $(this).find('input, select').addClass('is-invalid');
                        } else {
                            $(this).find('input, select').removeClass('is-invalid');
                        }
                    }
                });

                if (!hasData) {
                    e.preventDefault();
                    alert('Please add at least one ICD code.');
                    return false;
                }

                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields for each ICD code.');
                    return false;
                }
            });
        });
    </script>
@endpush
