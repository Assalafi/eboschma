@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="card custom-card">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1" style="color: #01542B;">
                                    <i class="fe fe-upload me-2"></i>Upload Beneficiaries
                                </h5>
                                <p class="text-muted mb-0 small">Import beneficiaries from Excel/CSV file</p>
                            </div>
                            <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fe fe-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('beneficiaries.upload.excel') }}" method="POST"
                            enctype="multipart/form-data" id="uploadForm">
                            @csrf

                            <div class="row">
                                <!-- Program Selection -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                                    <select class="form-select" name="program_id" id="program_id" required>
                                        <option value="">Select Program</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}"
                                                {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                {{ $program->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- State Selection -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                                    <select class="form-select" name="state" id="state" required>
                                        <option value="">Select State</option>
                                        @foreach ($states as $state)
                                            <option value="{{ $state }}"
                                                {{ old('state') == $state ? 'selected' : '' }}>
                                                {{ $state }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- LGA Selection -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">LGA <span class="text-danger">*</span></label>
                                    <select class="form-select" name="lga" id="lga" required disabled>
                                        <option value="">Select State First</option>
                                    </select>
                                </div>

                                <!-- Facility Selection -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Facility <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="facility_id" id="facility_id" required disabled>
                                        <option value="">Select LGA First</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- File Upload Section -->
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold">Excel/CSV File <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="excel_file" id="excel_file"
                                        accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text">
                                        Supported formats: .xlsx, .xls, .csv (Max size: 10MB)
                                    </div>
                                </div>
                            </div>

                            <!-- Template Download -->
                            <div class="alert alert-info py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fe fe-info fs-4 me-3"></i>
                                    <div>
                                        <strong>File Format Requirements:</strong>
                                        <p class="mb-1 mt-2">Your Excel/CSV file must have the following columns:</p>
                                        <code class="d-block bg-light p-2 rounded">
                                            boschma_number, name, dob, gender, phone, nin, marital_status, tribe, religion,
                                            category
                                        </code>
                                        <a href="{{ route('beneficiaries.download.template') }}"
                                            class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fe fe-download me-1"></i> Download Template
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2"
                                    onclick="window.history.back()">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fe fe-upload me-1"></i> Upload & Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Import Results (shown after import) -->
                @if (session('import_results'))
                    <div class="card custom-card mt-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fe fe-bar-chart-2 me-2"></i>Import Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="p-3 bg-success-light rounded">
                                        <h3 class="text-success mb-0">{{ session('import_results.imported') }}</h3>
                                        <small class="text-muted">Imported</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-warning-light rounded">
                                        <h3 class="text-warning mb-0">{{ session('import_results.skipped') }}</h3>
                                        <small class="text-muted">Skipped</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-info-light rounded">
                                        <h3 class="text-info mb-0">{{ session('import_results.total') }}</h3>
                                        <small class="text-muted">Total Rows</small>
                                    </div>
                                </div>
                            </div>

                            @if (!empty(session('import_results.errors')))
                                <div class="mt-3">
                                    <h6 class="text-danger"><i class="fe fe-alert-triangle me-1"></i>Errors:</h6>
                                    <ul class="list-unstyled mb-0" style="max-height: 200px; overflow-y: auto;">
                                        @foreach (session('import_results.errors') as $error)
                                            <li class="text-danger small">• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // LGA data by state
        const lgasByState = {
            'Borno': @json(\App\Models\Facility::getBornoLGAs()),
        };

        // State change handler - Load LGAs
        $('#state').on('change', function() {
            const state = $(this).val();
            const lgaSelect = $('#lga');
            const facilitySelect = $('#facility_id');

            // Reset dependent dropdowns
            lgaSelect.html('<option value="">Select LGA</option>').prop('disabled', true);
            facilitySelect.html('<option value="">Select LGA First</option>').prop('disabled', true);

            if (state && lgasByState[state]) {
                lgasByState[state].forEach(function(lga) {
                    lgaSelect.append(`<option value="${lga}">${lga}</option>`);
                });
                lgaSelect.prop('disabled', false);
            }
        });

        // LGA change handler - Load Facilities
        $('#lga').on('change', function() {
            const lga = $(this).val();
            const facilitySelect = $('#facility_id');

            facilitySelect.html('<option value="">Loading...</option>').prop('disabled', true);

            if (lga) {
                $.ajax({
                    url: '{{ route('api.facilities.by-lga') }}',
                    method: 'GET',
                    data: {
                        lga: lga
                    },
                    success: function(response) {
                        facilitySelect.html('<option value="">Select Facility</option>');
                        if (response.facilities && response.facilities.length > 0) {
                            response.facilities.forEach(function(facility) {
                                facilitySelect.append(
                                    `<option value="${facility.id}">${facility.name}</option>`
                                    );
                            });
                        } else {
                            facilitySelect.html('<option value="">No facilities found</option>');
                        }
                        facilitySelect.prop('disabled', false);
                    },
                    error: function() {
                        facilitySelect.html('<option value="">Error loading facilities</option>');
                        facilitySelect.prop('disabled', false);
                    }
                });
            }
        });

        // Form submission loading state
        $('#uploadForm').on('submit', function() {
            const btn = $('#submitBtn');
            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...');
        });
    </script>
@endpush
