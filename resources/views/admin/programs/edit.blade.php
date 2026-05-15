@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-8 col-md-12 mx-auto">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Edit Program</h6>
                            <p class="text-muted card-sub-title">Update program details</p>
                        </div>
                        <div>
                            <a href="{{ route('programs.index') }}" class="btn btn-outline-primary">
                                <i class="fe fe-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('programs.update', $program->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="name" class="col-md-3 col-form-label">Program Name <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $program->name) }}" 
                                       placeholder="Enter program name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="description" class="col-md-3 col-form-label">Description</label>
                            <div class="col-md-9">
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                       id="description" name="description" rows="2"
                                       placeholder="e.g. Basic Health Care Provision Fund">{{ old('description', $program->description) }}</textarea>
                                <small class="text-muted">Full name or description shown on ID cards</small>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="logo" class="col-md-3 col-form-label">Program Logo</label>
                            <div class="col-md-9">
                                @if($program->logo)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $program->logo) }}" alt="Current Logo" style="max-height: 60px; border: 1px solid #ddd; padding: 4px; border-radius: 4px;">
                                        <small class="text-muted d-block mt-1">Current logo. Upload a new file to replace it.</small>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/png,image/jpg,image/jpeg,image/svg+xml">
                                <small class="text-muted">PNG, JPG or SVG. Max 2MB. Used on ID cards.</small>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="format" class="col-md-3 col-form-label">Format <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control @error('format') is-invalid @enderror" 
                                       id="format" name="format" value="{{ old('format', $program->format) }}" 
                                       placeholder="Enter program format" required>
                                @error('format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Has Dependant <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="radio" name="has_dependant" id="has_dependant_yes" value="1" {{ old('has_dependant', $program->has_dependant) == 1 ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="has_dependant_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="has_dependant" id="has_dependant_no" value="0" {{ old('has_dependant', $program->has_dependant) == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_dependant_no">No</label>
                                </div>
                                @error('has_dependant')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', $program->status) == 1 ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="status_active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', $program->status) == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_inactive">Inactive</label>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Update Program
                                </button>
                                <a href="{{ route('programs.index') }}" class="btn btn-secondary">
                                    <i class="fe fe-x me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
