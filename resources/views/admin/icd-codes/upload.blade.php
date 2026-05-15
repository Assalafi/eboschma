@extends('layouts.app')

@section('title', 'Upload ICD Codes')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Upload ICD Codes</h4>
                                <p class="text-muted mb-0">Import ICD codes from Excel file</p>
                            </div>
                            <div>
                                <a href="{{ route('icd-codes.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

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
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('icd-codes.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="mb-3">📋 Instructions</h6>
                                            <ol class="mb-0">
                                                <li>Download the Excel template below</li>
                                                <li>Fill in your ICD code data</li>
                                                <li>Required columns: code, description, category</li>
                                                <li>Category must be valid ICD-10 chapter codes</li>
                                                <li>Upload the completed file</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="file" class="form-label">Excel File <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror"
                                        id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Supported formats: .xlsx, .xls, .csv (Max 10MB)</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-upload me-1"></i> Upload & Import
                                        </button>
                                        <a href="{{ route('icd-codes.template') }}" class="btn btn-outline-info">
                                            <i class="fe fe-download me-1"></i> Download Template
                                        </a>
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
                            <a href="{{ route('icd-codes.bulk.create') }}" class="btn btn-outline-success">
                                <i class="fe fe-list me-2"></i>Bulk Add Codes
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
                            <div class="col-6 mb-2">
                                <small class="text-muted">A00-B99</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">C00-D49</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">D50-D89</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">E00-E89</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">F01-F99</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">G00-G99</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">H00-H59</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">H60-H95</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">I00-I99</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">J00-J99</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">K00-K93</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">L00-L99</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">File Format</h6>
                        <div class="mb-3">
                            <strong>Required Columns:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li><code>code</code> - ICD code (e.g., A00.0)</li>
                                <li><code>description</code> - Full description</li>
                                <li><code>category</code> - ICD-10 chapter code</li>
                            </ul>
                        </div>
                        <div class="mb-0">
                            <strong>Valid Categories:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>A00-B99, C00-D49, D50-D89, E00-E89</li>
                                <li>F01-F99, G00-G99, H00-H59, H60-H95</li>
                                <li>I00-I99, J00-J99, K00-K93, L00-L99</li>
                                <li>M00-M99, N00-N99, O00-O9A, P00-P96</li>
                                <li>Q00-Q99, R00-R99, S00-T98, U00-U99</li>
                                <li>V01-Y99, Z00-Z99</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
