@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-8 col-md-12 mx-auto">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Upload Facilities</h6>
                            <p class="text-muted card-sub-title">Import multiple facilities using Excel file</p>
                        </div>
                        <div>
                            <a href="{{ route('facilities.index') }}" class="btn btn-secondary">
                                <i class="fe fe-arrow-left"></i> Back to List
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

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Download Template Section -->
                    <div class="alert alert-info">
                        <h6 class="mb-2"><i class="fe fe-info-circle me-2"></i>Before Uploading</h6>
                        <p class="mb-2">Download the template file to ensure your data is in the correct format.</p>
                        <a href="{{ route('facilities.download.template') }}" class="btn btn-info btn-sm">
                            <i class="fe fe-download"></i> Download Template
                        </a>
                    </div>

                    <!-- Upload Requirements -->
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Upload Requirements</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">File Format</h6>
                                    <ul class="mb-3">
                                        <li>Excel file (.xlsx, .xls)</li>
                                        <li>CSV file (.csv)</li>
                                        <li>Maximum file size: 10MB</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Required Columns</h6>
                                    <ul class="mb-3">
                                        <li><strong>Name</strong> - Facility name</li>
                                        <li><strong>LGA</strong> - Must match Borno LGAs exactly</li>
                                        <li><strong>Ward</strong> - Ward name</li>
                                        <li><strong>Type (Optional)</strong> - Facility type</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Valid LGAs Section -->
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Valid Borno State LGAs</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @php
                                    $lgas = [
                                        'Abadam', 'Askira/Uba', 'Bama', 'Bayo', 'Biu', 'Chibok', 'Damboa',
                                        'Dikwa', 'Gubio', 'Guzamala', 'Gwoza', 'Hawul', 'Jere', 'Kaga',
                                        'Kala/Balge', 'Konduga', 'Kukawa', 'Kwaya Kusar', 'Mafa', 'Magumeri',
                                        'Maiduguri', 'Marte', 'Mobbar', 'Monguno', 'Ngala', 'Nganzai', 'Shani'
                                    ];
                                    $chunks = array_chunk($lgas, 9);
                                @endphp
                                @foreach ($chunks as $chunk)
                                    <div class="col-md-4">
                                        @foreach ($chunk as $lga)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $lga }}</span>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Valid Facility Types Section -->
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Valid Facility Types (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @php
                                    $types = [
                                        'Primary Health Care Center', 'General Hospital', 'Specialist Hospital',
                                        'Teaching Hospital', 'Clinic', 'Maternity Home', 'Pharmacy',
                                        'Laboratory', 'Diagnostic Center', 'Rehabilitation Center',
                                        'Mental Health Facility', 'Other'
                                    ];
                                    $typeChunks = array_chunk($types, 4);
                                @endphp
                                @foreach ($typeChunks as $chunk)
                                    <div class="col-md-6">
                                        @foreach ($chunk as $type)
                                            <span class="badge bg-info me-1 mb-1">{{ $type }}</span>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('facilities.upload.excel') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="mb-4">
                            <label for="excel_file" class="form-label">
                                Select Excel File <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('excel_file') is-invalid @enderror" 
                                   id="excel_file" name="excel_file" 
                                   accept=".xlsx,.xls,.csv" required>
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Supported formats: .xlsx, .xls, .csv (Maximum size: 10MB)
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="upload-btn">
                                <i class="fe fe-upload"></i> Upload Facilities
                            </button>
                            <a href="{{ route('facilities.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// File validation
document.getElementById('excel_file').addEventListener('change', function() {
    const file = this.files[0];
    const uploadBtn = document.getElementById('upload-btn');
    
    if (file) {
        // Check file size (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            this.value = '';
            return;
        }
        
        // Check file type
        const validTypes = ['.xlsx', '.xls', '.csv'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(fileExtension)) {
            alert('Please select a valid Excel or CSV file (.xlsx, .xls, .csv)');
            this.value = '';
            return;
        }
        
        uploadBtn.disabled = false;
    } else {
        uploadBtn.disabled = true;
    }
});

// Form submission loading state
document.querySelector('form').addEventListener('submit', function() {
    const uploadBtn = document.getElementById('upload-btn');
    uploadBtn.innerHTML = '<i class="fe fe-loader"></i> Uploading...';
    uploadBtn.disabled = true;
});
</script>
@endsection
