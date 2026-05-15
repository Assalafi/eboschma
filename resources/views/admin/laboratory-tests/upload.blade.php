@extends('layouts.app')

@section('title', 'Upload Laboratory Tests')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Upload Laboratory Tests</h4>
                                <p class="text-muted mb-0">Import laboratory tests from Excel file</p>
                            </div>
                            <div>
                                <a href="{{ route('laboratory-tests.index') }}" class="btn btn-outline-secondary">
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

                        <form action="{{ route('laboratory-tests.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="mb-3">📋 Instructions</h6>
                                            <ol class="mb-0">
                                                <li>Download the Excel template below</li>
                                                <li>Fill in your laboratory test data</li>
                                                <li>Required columns: name, sample_type, price</li>
                                                <li>Optional columns: description</li>
                                                <li>Sample types must be: Blood, Urine, Saliva, Stool, Sputum, Swab, Tissue,
                                                    CSF, Synovial Fluid, Pleural Fluid, Peritoneal Fluid, or Other</li>
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
                                        <a href="{{ route('laboratory-tests.template') }}" class="btn btn-outline-info">
                                            <i class="fe fe-download me-1"></i> Download Template
                                        </a>
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
                            <a href="{{ route('laboratory-tests.bulk.create') }}" class="btn btn-outline-success">
                                <i class="fe fe-list me-2"></i>Bulk Add Tests
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
                            <div class="col-6 mb-2">
                                <small class="text-muted">Blood</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Urine</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Saliva</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Stool</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Sputum</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Swab</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Tissue</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">CSF</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Synovial Fluid</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Pleural Fluid</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Peritoneal Fluid</small>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Other</small>
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
                                <li><code>name</code> - Test name</li>
                                <li><code>sample_type</code> - Sample type</li>
                                <li><code>price</code> - Price in Naira</li>
                            </ul>
                        </div>
                        <div class="mb-0">
                            <strong>Optional Columns:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li><code>description</code> - Test description</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
