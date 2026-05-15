@extends('layouts.app')

@section('title', 'Edit Service Category')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Edit Service Category</h4>
                                <p class="text-muted mb-0">Update service category information</p>
                            </div>
                            <div>
                                <a href="{{ route('service-categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back to Categories
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('service-categories.update', $category->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-semibold">Category Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $category->name) }}"
                                            placeholder="Enter category name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Examples: Laboratory Services, Radiology, Pharmacy,
                                            Consultation</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('service-categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Update Category
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
