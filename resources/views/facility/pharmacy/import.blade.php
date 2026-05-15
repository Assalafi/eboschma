@extends('layouts.facility')

@section('title', 'Import Drugs from Excel')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Import
                                    Drugs from Excel</h1>
                                <p class="text-muted mb-0">Upload an Excel file to bulk import drugs into your pharmacy
                                    inventory</p>
                            </div>
                            <div>
                                <a href="{{ route('facility.pharmacy.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Back to Pharmacy
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-check-circle me-2"></i>
                                    <span>{{ session('success') }}</span>
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

                        <!-- Download Template Section -->
                        <div class="card bg-light mb-4" style="border-radius: 8px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-2 fw-bold" style="color: #01542B;">
                                            <i class="ti-download me-2"></i>Download Excel Template
                                        </h6>
                                        <p class="mb-0 text-muted small">Start with our pre-formatted template to ensure
                                            correct data structure</p>
                                    </div>
                                    <a href="{{ route('facility.pharmacy.download-template') }}" class="btn btn-primary">
                                        <i class="ti-download me-2"></i>Download Template
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <form action="{{ route('facility.pharmacy.import.store') }}" method="POST"
                            enctype="multipart/form-data" id="importForm">
                            @csrf

                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                        <i class="ti-upload me-2 text-primary"></i>Upload Excel File
                                    </h5>

                                    <div class="mb-4">
                                        <label for="excel_file" class="form-label fw-semibold">Select Excel File <span
                                                class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                            id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">Supported formats: .xlsx, .xls, .csv (Max file size: 10MB)
                                        </div>
                                        @error('excel_file')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- File Preview -->
                                    <div id="filePreview" class="d-none">
                                        <div class="alert alert-info d-flex align-items-center">
                                            <i class="ti-file me-2"></i>
                                            <div>
                                                <strong>Selected file:</strong> <span id="fileName"></span>
                                                <br>
                                                <strong>File size:</strong> <span id="fileSize"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                <div>
                                    <a href="{{ route('facility.pharmacy.bulk-create') }}" class="btn btn-outline-primary">
                                        <i class="ti-plus me-1"></i>Try Bulk Form
                                    </a>
                                </div>
                                <div>
                                    <button type="reset" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                        <i class="ti-refresh me-1"></i> Clear
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="ti-upload me-1"></i> Import Drugs
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
                            <i class="ti-info-alt me-2 text-info"></i>File Format Requirements
                        </h6>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Use .xlsx, .xls, or .csv format</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Maximum file size: 10MB</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">First row must contain headers</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ti-shield text-warning me-2"></i>
                                <span class="small">Required fields: Name, Dosage Form, Strength, Unit, Quantity, Unit
                                    Price</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-file me-2 text-primary"></i>Required Excel Columns
                        </h6>
                        <div class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Column</th>
                                            <th>Required</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>Name</code></td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>Description</code></td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>Dosage Form</code></td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>Strength</code></td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>Unit</code></td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>Quantity</code></td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>Unit Price</code></td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-help-circle me-2 text-primary"></i>Import Process
                        </h6>
                        <div class="mb-3">
                            <ol class="small text-muted mb-0">
                                <li class="mb-2">Download the Excel template</li>
                                <li class="mb-2">Fill in your drug data</li>
                                <li class="mb-2">Save the file as .xlsx or .csv</li>
                                <li class="mb-2">Upload the file using the form</li>
                                <li class="mb-0">Review import results</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-alert-triangle me-2 text-warning"></i>Important Notes
                        </h6>
                        <div class="mb-3">
                            <ul class="small text-muted mb-0">
                                <li class="mb-2">Duplicate drugs will be skipped</li>
                                <li class="mb-2">Invalid data will cause import to fail</li>
                                <li class="mb-2">All drugs will be assigned to your facility</li>
                                <li class="mb-2">Check data carefully before importing</li>
                                <li class="mb-0">Large files may take time to process</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File preview functionality
        document.getElementById('excel_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                preview.classList.remove('d-none');

                // Validate file size
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    e.target.value = '';
                    preview.classList.add('d-none');
                    alert('File size must be less than 10MB.');
                }
            } else {
                preview.classList.add('d-none');
            }
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function resetForm() {
            document.getElementById('importForm').reset();
            document.getElementById('filePreview').classList.add('d-none');
        }

        // Form submission validation
        document.getElementById('importForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('excel_file');
            const file = fileInput.files[0];

            if (!file) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti-loader me-2"></i>Importing...';

            // Re-enable button after 30 seconds (timeout)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti-upload me-1"></i> Import Drugs';
            }, 30000);
        });

        // Drag and drop functionality
        const dropZone = document.getElementById('importForm');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('bg-light');
        }

        function unhighlight(e) {
            dropZone.classList.remove('bg-light');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                document.getElementById('excel_file').files = files;
                const event = new Event('change', {
                    bubbles: true
                });
                document.getElementById('excel_file').dispatchEvent(event);
            }
        }
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

        .drag-over {
            background-color: #f8f9fa;
            border: 2px dashed #01542B;
        }

        code {
            background-color: #f1f3f4;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 0.875em;
        }
    </style>
@endsection
