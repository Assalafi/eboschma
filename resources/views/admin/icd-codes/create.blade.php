@extends('layouts.app')

@section('title', 'Add ICD Code')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Add ICD Code</h4>
                                <p class="text-muted mb-0">Create a new ICD code</p>
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

                        <form action="{{ route('icd-codes.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="code" class="form-label">ICD Code <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control font-monospace @error('code') is-invalid @enderror"
                                        id="code" name="code" value="{{ old('code') }}" placeholder="e.g., A00.0"
                                        required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: Letter(s) + numbers + optional decimal (e.g., I10,
                                        A00.0)</small>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="description" class="form-label">Description <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" value="{{ old('description') }}"
                                        placeholder="e.g., Cholera due to Vibrio cholerae 01" required>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Full description of the disease or condition</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="category" class="form-label">Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category"
                                        name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $key => $category)
                                            <option value="{{ $category }}"
                                                {{ old('category') == $category ? 'selected' : '' }}>
                                                {{ $key }} - {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">ICD-10 chapter category</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Create ICD Code
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
                            <a href="{{ route('icd-codes.bulk.create') }}" class="btn btn-outline-primary">
                                <i class="fe fe-list me-2"></i>Bulk Add Codes
                            </a>
                            <a href="{{ route('icd-codes.upload') }}" class="btn btn-outline-success">
                                <i class="fe fe-upload me-2"></i>Upload Excel File
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
                                        {{ Str::limit($category, 50) }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Code Information</h6>
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">ICD code must be unique</li>
                            <li class="mb-2">Format: Letter(s) + numbers (e.g., I10)</li>
                            <li class="mb-2">Can include decimal point (e.g., A00.0)</li>
                            <li class="mb-2">Description should be clear and concise</li>
                            <li class="mb-0">Category determines the ICD chapter</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
