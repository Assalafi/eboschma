@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h6 class="main-content-label mb-1">Upload Civil Servants Excel</h6>
                                <p class="text-muted card-sub-title">Import multiple civil servants from Excel file</p>
                            </div>
                            <div>
                                <a href="{{ route('civil-servants.download.template') }}" class="btn btn-info me-2">
                                    <i class="fe fe-download"></i> Download Template
                                </a>
                                <a href="{{ route('civil-servants.index') }}" class="btn btn-light">
                                    <i class="fe fe-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif


                        <!-- Upload Form -->
                        <form action="{{ route('civil-servants.upload.excel') }}" method="POST"
                            enctype="multipart/form-data" id="upload-form">
                            @csrf

                            <div class="form-group">
                                <label class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" name="excel_file"
                                        class="form-control @error('excel_file') is-invalid @enderror"
                                        accept=".xlsx,.xls,.csv" required id="excel-file">
                                    <div class="input-group-text">
                                        <i class="fe fe-file text-success"></i>
                                    </div>
                                </div>
                                @error('excel_file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Supported formats: .xlsx, .xls, .csv (Max: 10MB)</small>
                            </div>

                            <div class="form-group d-flex gap-2 justify-content-end">
                                <a href="{{ route('civil-servants.index') }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-success" id="upload-btn">
                                    <i class="fe fe-upload me-1"></i> Upload & Import
                                </button>
                            </div>
                        </form>

                        <!-- Progress Bar -->
                        <div class="progress mt-3" id="upload-progress" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BVN Update Upload Section -->
        <div class="row mt-4">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h6 class="main-content-label mb-1">Upload BVN for Existing Civil Servants</h6>
                                <p class="text-muted card-sub-title">Update BVN for existing civil servants using DP Number
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('civil-servants.download.bvn-template') }}" class="btn btn-info me-2">
                                    <i class="fe fe-download"></i> Download BVN Template
                                </a>
                            </div>
                        </div>

                        @if (session('bvn_success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('bvn_success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('bvn_error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('bvn_error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- BVN Upload Form -->
                        <form action="{{ route('civil-servants.upload.bvn') }}" method="POST"
                            enctype="multipart/form-data" id="bvn-upload-form">
                            @csrf

                            <div class="form-group">
                                <label class="form-label">Select BVN Excel File <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" name="bvn_file"
                                        class="form-control @error('bvn_file') is-invalid @enderror"
                                        accept=".xlsx,.xls,.csv" required id="bvn-file">
                                    <div class="input-group-text">
                                        <i class="fe fe-file text-info"></i>
                                    </div>
                                </div>
                                @error('bvn_file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">File should only contain: <strong>dp_no</strong> and
                                    <strong>bvn</strong> columns</small>
                            </div>

                            <div class="form-group d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-info" id="bvn-upload-btn">
                                    <i class="fe fe-upload me-1"></i> Upload & Update BVN
                                </button>
                            </div>
                        </form>

                        <!-- BVN Progress Bar -->
                        <div class="progress mt-3" id="bvn-upload-progress" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                role="progressbar" style="width: 0%"></div>
                        </div>

                        <!-- BVN Template Format -->
                        <div class="alert alert-info mt-3">
                            <strong><i class="fe fe-info me-1"></i> BVN Template Format:</strong>
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-info">
                                        <tr>
                                            <th>dp_no <span class="badge bg-danger">Required</span></th>
                                            <th>bvn <span class="badge bg-danger">Required</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>DP001</td>
                                            <td>22123456789</td>
                                        </tr>
                                        <tr>
                                            <td>12345</td>
                                            <td>22198765432</td>
                                        </tr>
                                        <tr>
                                            <td>'00001</td>
                                            <td>22100112233</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <ul class="mt-2 mb-0">
                                <li><strong>dp_no:</strong> Must match existing civil servant DP number</li>
                                <li><strong>bvn:</strong> Bank Verification Number (11 digits)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Excel Template Information -->
        <div class="row mt-4">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="main-content-label mb-0">Excel Template Format</h6>
                            <a href="{{ route('civil-servants.download.template') }}" class="btn btn-success btn-sm">
                                <i class="fe fe-download me-1"></i> Download Template
                            </a>
                        </div>

                        <div class="alert alert-info">
                            <strong><i class="fe fe-info me-1"></i> Quick Start:</strong>
                            Download the template above to get started quickly, or follow the format below.
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="fe fe-alert-triangle me-1"></i> Important:</strong>
                            Only <strong>dp_no</strong> is required. All other fields (fullname, NIN, gender, DOB, etc.) are
                            optional and can be left empty.
                        </div>

                        <div class="alert alert-info">
                            <strong><i class="fe fe-info me-1"></i> DP Number Formats:</strong>
                            DP numbers can be numeric (00001, 12345) or alphanumeric (DP001, CS123).
                            <br><strong>Tip:</strong> To preserve leading zeros in Excel (e.g., 00001), format the column as
                            <strong>Text</strong> before entering data, or prefix with an apostrophe (e.g., '00001).
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>dp_no <span class="badge bg-danger">*</span></th>
                                        <th>nin</th>
                                        <th>bvn</th>
                                        <th>fullname</th>
                                        <th>dob</th>
                                        <th>state</th>
                                        <th>lga</th>
                                        <th>gender</th>
                                        <th>mda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>DP001</strong></td>
                                        <td>12345678901</td>
                                        <td>22123456789</td>
                                        <td>John Doe</td>
                                        <td>1985-06-15</td>
                                        <td>Lagos</td>
                                        <td>Lagos Island</td>
                                        <td>Male</td>
                                        <td>Ministry of Finance</td>
                                    </tr>
                                    <tr>
                                        <td><strong>12345</strong></td>
                                        <td><em class="text-muted">(optional)</em></td>
                                        <td><em class="text-muted">(optional)</em></td>
                                        <td>Jane Smith</td>
                                        <td>1990-03-22</td>
                                        <td>Abuja</td>
                                        <td>Municipal</td>
                                        <td>Female</td>
                                        <td>Ministry of Health</td>
                                    </tr>
                                    <tr>
                                        <td><strong>'00001</strong> <small class="text-info">(with apostrophe)</small></td>
                                        <td colspan="8" class="text-center text-muted"><em>All other fields are
                                                optional - can be left empty</em></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Required Fields:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fe fe-check text-success me-1"></i> <strong>dp_no:</strong> Unique DP
                                        number <span class="badge bg-danger">Required</span></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Optional Fields:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>fullname:</strong> Full name of
                                        civil servant</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>nin:</strong> National Identity
                                        Number (max 11 digits)</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>bvn:</strong> Bank Verification
                                        Number (max 11 digits)</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>dob:</strong> Date of birth
                                        (YYYY-MM-DD format)</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>gender:</strong> Must be "Male"
                                        or "Female"</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>state:</strong> State of
                                        origin/residence</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>lga:</strong> Local Government
                                        Area</li>
                                    <li><i class="fe fe-info text-info me-1"></i> <strong>mda:</strong>
                                        Ministry/Department/Agency</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Civil Servants Upload Form
            $('#upload-form').on('submit', function() {
                // Show progress bar
                $('#upload-progress').show();
                $('#upload-btn').prop('disabled', true).html(
                    '<i class="fe fe-spinner fa-spin me-1"></i> Uploading...');

                // Simulate progress (since we can't get real upload progress easily)
                let progress = 0;
                const interval = setInterval(function() {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    $('#upload-progress .progress-bar').css('width', progress + '%');
                }, 200);

                // Clear interval after 10 seconds (should be done by then)
                setTimeout(function() {
                    clearInterval(interval);
                    $('#upload-progress .progress-bar').css('width', '100%');
                }, 10000);
            });

            // BVN Upload Form
            $('#bvn-upload-form').on('submit', function() {
                // Show progress bar
                $('#bvn-upload-progress').show();
                $('#bvn-upload-btn').prop('disabled', true).html(
                    '<i class="fe fe-spinner fa-spin me-1"></i> Uploading...');

                // Simulate progress
                let progress = 0;
                const interval = setInterval(function() {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    $('#bvn-upload-progress .progress-bar').css('width', progress + '%');
                }, 200);

                // Clear interval after 10 seconds
                setTimeout(function() {
                    clearInterval(interval);
                    $('#bvn-upload-progress .progress-bar').css('width', '100%');
                }, 10000);
            });

            // Civil Servants File validation
            $('#excel-file').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024; // Convert to MB

                    if (fileSize > 10) {
                        alert('File size must be less than 10MB');
                        this.value = '';
                        return;
                    }

                    const fileName = file.name.toLowerCase();
                    if (!fileName.endsWith('.xlsx') && !fileName.endsWith('.xls') && !fileName.endsWith(
                            '.csv')) {
                        alert('Please select a valid Excel file (.xlsx, .xls, or .csv)');
                        this.value = '';
                        return;
                    }
                }
            });

            // BVN File validation
            $('#bvn-file').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024; // Convert to MB

                    if (fileSize > 10) {
                        alert('File size must be less than 10MB');
                        this.value = '';
                        return;
                    }

                    const fileName = file.name.toLowerCase();
                    if (!fileName.endsWith('.xlsx') && !fileName.endsWith('.xls') && !fileName.endsWith(
                            '.csv')) {
                        alert('Please select a valid Excel file (.xlsx, .xls, or .csv)');
                        this.value = '';
                        return;
                    }
                }
            });
        });
    </script>
@endsection
