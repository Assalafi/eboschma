@extends('layouts.app')

@section('title', 'Edit Staff Position')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Edit Staff Position</h4>
                                <p class="text-muted mb-0">Update facility staff position information</p>
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

                        <form action="{{ route('staff-positions.update', $position->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="name" class="form-label">Position Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $position->name) }}"
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
                                        rows="4" placeholder="Brief description of the position responsibilities">{{ old('description', $position->description) }}</textarea>
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
                                            <i class="fe fe-save me-1"></i> Update Position
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
                        <h6 class="mb-3">Position Info</h6>
                        <div class="mb-3">
                            <i class="fe fe-calendar text-primary me-2"></i>
                            <strong>Created:</strong> {{ $position->created_at->format('M d, Y') }}
                        </div>
                        <div class="mb-0">
                            <i class="fe fe-clock text-info me-2"></i>
                            <strong>Updated:</strong> {{ $position->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('staff-positions.create') }}" class="btn btn-outline-primary">
                                <i class="fe fe-plus me-2"></i>Add New Position
                            </a>
                            <a href="{{ route('staff-positions.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-list me-2"></i>All Positions
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-danger mb-3">Danger Zone</h6>
                        <p class="text-muted small mb-3">This action cannot be undone</p>
                        <form action="{{ route('staff-positions.destroy', $position->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this position? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fe fe-trash"></i> Delete Position
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
