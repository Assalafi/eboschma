@extends('facility.layouts.app')

@section('title', 'Edit Nurse Assignment')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Edit Nurse Assignment</h4>
                                    <p class="text-muted mb-0">Update ward assignment</p>
                                </div>
                                <div>
                                    <a href="{{ route('facility.nurse-ward.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Assignments
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('facility.nurse-ward.update', $assignment->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="user_id" class="form-label">Nurse/Staff <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id"
                                            name="user_id" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($nurses as $nurse)
                                                <option value="{{ $nurse->id }}"
                                                    {{ old('user_id', $assignment->user_id) == $nurse->id ? 'selected' : '' }}>
                                                    {{ $nurse->name }} ({{ $nurse->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ward_id" class="form-label">Ward <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ward_id') is-invalid @enderror" id="ward_id"
                                            name="ward_id" required>
                                            <option value="">Select Ward</option>
                                            @foreach ($wards as $ward)
                                                <option value="{{ $ward->id }}"
                                                    {{ old('ward_id', $assignment->ward_id) == $ward->id ? 'selected' : '' }}>
                                                    {{ $ward->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('ward_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="assigned_date" class="form-label">Assigned Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('assigned_date') is-invalid @enderror"
                                            id="assigned_date" name="assigned_date"
                                            value="{{ old('assigned_date', $assignment->assigned_date ? $assignment->assigned_date->format('Y-m-d') : '') }}"
                                            required>
                                        @error('assigned_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                                value="1"
                                                {{ old('is_active', $assignment->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Update Assignment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
