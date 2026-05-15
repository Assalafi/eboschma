@extends('layouts.app')

@section('title', 'Create Staff Position')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">New Staff Position</h4>
                                <p class="text-muted mb-0">Add a new position for facility staff</p>
                            </div>
                            <div>
                                <a href="{{ route('staff-positions.index') }}" class="btn btn-outline-secondary">
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

                        <form action="{{ route('staff-positions.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="name" class="form-label">Position Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}"
                                        placeholder="e.g., Nurse, Doctor, Pharmacist" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter the staff position title</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="4" placeholder="Brief description of the position responsibilities">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional: Describe the role and responsibilities</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Create Position
                                        </button>
                                        <a href="{{ route('staff-positions.index') }}" class="btn btn-outline-secondary">
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
                            <a href="{{ route('staff-positions.bulk.create') }}" class="btn btn-outline-success">
                                <i class="fe fe-layers me-2"></i>Bulk Create Positions
                            </a>
                            <a href="{{ route('staff-positions.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-list me-2"></i>All Positions
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Tips</h6>
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Use clear, professional position names</li>
                            <li class="mb-2">Description helps staff understand the role</li>
                            <li class="mb-0">Use bulk create for multiple positions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
