@extends('layouts.app')

@section('title', 'Add Laboratory Test')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Add Laboratory Test</h4>
                                <p class="text-muted mb-0">Create a new laboratory test</p>
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

                        <form action="{{ route('laboratory-tests.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Test Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}"
                                        placeholder="e.g., Complete Blood Count" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sample_type" class="form-label">Sample Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('sample_type') is-invalid @enderror" id="sample_type"
                                        name="sample_type" required>
                                        <option value="">Select Sample Type</option>
                                        @foreach ($sampleTypes as $key => $type)
                                            <option value="{{ $key }}"
                                                {{ old('sample_type') == $key ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sample_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price (₦) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                        id="price" name="price" value="{{ old('price') }}" placeholder="0.00"
                                        step="0.01" min="0" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter price in Naira</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="4" placeholder="Enter test description (optional)">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional: Detailed description of the test</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Create Test
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
                            <a href="{{ route('laboratory-tests.bulk.create') }}" class="btn btn-outline-primary">
                                <i class="fe fe-list me-2"></i>Bulk Add Tests
                            </a>
                            <a href="{{ route('laboratory-tests.upload') }}" class="btn btn-outline-success">
                                <i class="fe fe-upload me-2"></i>Upload Excel File
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
                        <h6 class="mb-3">Test Information</h6>
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Test name is required and must be unique</li>
                            <li class="mb-2">Sample type determines the specimen needed</li>
                            <li class="mb-2">Price is required and must be numeric</li>
                            <li class="mb-0">Description helps patients understand the test</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
