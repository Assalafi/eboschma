@extends('layouts.app')

@section('title', 'Bulk Upload Claims')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Bulk Upload Claims</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('claims.index') }}">Claims</a></li>
                                <li class="breadcrumb-item active">Bulk Upload</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Claims
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Upload Instructions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Upload Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Required Fields:</h6>
                                <ul class="mb-3">
                                    <li><strong>beneficiary_name:</strong> Full name of the beneficiary</li>
                                    <li><strong>boschma_id:</strong> BOSCHMA ID number</li>
                                    <li><strong>claim_type:</strong> medical, pharmacy, hospitalization, diagnostic,
                                        emergency</li>
                                    <li><strong>healthcare_provider:</strong> Name of the healthcare provider</li>
                                    <li><strong>provider_type:</strong> hospital, clinic, pharmacy, laboratory,
                                        diagnostic_center</li>
                                    <li><strong>service_date:</strong> Date of service (YYYY-MM-DD format)</li>
                                    <li><strong>claim_amount:</strong> Amount claimed (numeric, e.g., 15000.00)</li>
                                </ul>

                                <h6>Optional Fields:</h6>
                                <ul class="mb-3">
                                    <li><strong>nin:</strong> National Identification Number (11 digits)</li>
                                    <li><strong>phone_number:</strong> Contact phone number</li>
                                    <li><strong>diagnosis:</strong> Medical diagnosis</li>
                                    <li><strong>treatment_description:</strong> Description of treatment provided</li>
                                    <li><strong>additional_notes:</strong> Any additional notes</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('claims.template') }}" class="btn btn-success">
                                        <i class="fas fa-download me-2"></i>
                                        Download Template
                                    </a>
                                    <small class="text-muted">
                                        Download the Excel template with sample data and field descriptions.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-upload text-primary me-2"></i>
                            Upload Claims File
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="bulkUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="file" class="form-label">
                                            Select Excel File <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" id="file" name="file"
                                            accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">
                                            Supported formats: .xlsx, .xls, .csv (Max size: 10MB)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">File Preview</label>
                                        <div id="filePreview" class="border rounded p-3 bg-light text-center">
                                            <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                                            <p class="mb-0 text-muted">No file selected</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                                            <i class="fas fa-upload me-2"></i>
                                            Upload Claims
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Upload Results -->
                <div id="uploadResults" class="mt-4" style="display: none;">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                Upload Results
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="resultsContent">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Processing Upload</h5>
                    <p class="text-muted">Please wait while we process your claims file...</p>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .file-drop-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-drop-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .file-drop-area.dragover {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }

        .success-message {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }

        .warning-message {
            background-color: #fff3cd;
            border: 1px solid #ffecb5;
            color: #664d03;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // File input change handler
            $('#file').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    updateFilePreview(file);
                }
            });

            // Form submission
            $('#bulkUploadForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const file = formData.get('file');

                if (!file) {
                    showAlert('Please select a file to upload.', 'error');
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showAlert('File size must be less than 10MB.', 'error');
                    return;
                }

                // Validate file type
                const allowedTypes = ['application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'
                ];
                if (!allowedTypes.includes(file.type)) {
                    showAlert('Please upload a valid Excel or CSV file.', 'error');
                    return;
                }

                // Show loading modal
                const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                loadingModal.show();

                // Submit the form
                $.ajax({
                    url: '{{ route('claims.bulk.upload.process') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loadingModal.hide();
                        displayResults(response);
                    },
                    error: function(xhr) {
                        loadingModal.hide();
                        const error = xhr.responseJSON ? xhr.responseJSON.message :
                            'An error occurred while uploading the file.';
                        showAlert(error, 'error');
                    }
                });
            });
        });

        function updateFilePreview(file) {
            const preview = $('#filePreview');
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';

            let icon = 'fa-file-excel text-success';
            if (fileName.endsWith('.csv')) {
                icon = 'fa-file-csv text-info';
            }

            preview.html(`
        <i class="fas ${icon} fa-3x mb-2"></i>
        <p class="mb-0 fw-bold">${fileName}</p>
        <small class="text-muted">${fileSize}</small>
    `);
        }

        function displayResults(response) {
            const resultsDiv = $('#uploadResults');
            const resultsContent = $('#resultsContent');

            let html = '';

            if (response.success) {
                const results = response.results;

                html += `
            <div class="alert alert-success" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-check-circle me-2"></i>
                    Upload Completed Successfully
                </h6>
                <p class="mb-0">${response.message}</p>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h3 class="text-success mb-0">${results.success}</h3>
                            <small class="text-muted">Claims Imported</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h3 class="text-danger mb-0">${results.failed}</h3>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h3 class="text-primary mb-0">${results.success + results.failed}</h3>
                            <small class="text-muted">Total Processed</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

                if (results.errors && results.errors.length > 0) {
                    html += `
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Errors Found
                    </h6>
                    <div class="error-list" style="max-height: 200px; overflow-y: auto;">
                        ${results.errors.map(error => `<div class="small">${error}</div>`).join('')}
                    </div>
                </div>
            `;
                }

                html += `
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i>View All Claims
                </a>
                <button type="button" class="btn btn-primary" onclick="resetForm()">
                    <i class="fas fa-plus me-2"></i>Upload More Claims
                </button>
            </div>
        `;

            } else {
                html += `
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-times-circle me-2"></i>
                    Upload Failed
                </h6>
                <p class="mb-0">${response.message}</p>
            </div>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-primary" onclick="resetForm()">
                    <i class="fas fa-redo me-2"></i>Try Again
                </button>
            </div>
        `;
            }

            resultsContent.html(html);
            resultsDiv.show();

            // Scroll to results
            resultsDiv[0].scrollIntoView({
                behavior: 'smooth'
            });
        }

        function resetForm() {
            $('#bulkUploadForm')[0].reset();
            $('#filePreview').html(`
        <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
        <p class="mb-0 text-muted">No file selected</p>
    `);
            $('#uploadResults').hide();
        }

        function showAlert(message, type) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

            // Insert at the top of the card body
            $('.card-body').first().prepend(alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    </script>
@endpush
