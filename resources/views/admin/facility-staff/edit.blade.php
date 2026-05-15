@extends('layouts.app')

@section('title', 'Edit Facility Staff')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Edit Facility Staff</h4>
                                <p class="text-muted mb-0">Update staff member information</p>
                            </div>
                            <div>
                                <a href="{{ route('facility-staff.index') }}" class="btn btn-outline-secondary">
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

                        <form action="{{ route('facility-staff.update', $staff->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $staff->name) }}"
                                        placeholder="Enter staff member's full name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $staff->email) }}"
                                        placeholder="staff@example.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone', $staff->phone) }}"
                                        placeholder="+234 800 000 0000">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional: Contact number</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Leave blank to keep current password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional: Leave blank to keep current password</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="facility_id" class="form-label">Facility <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('facility_id') is-invalid @enderror" id="facility_id"
                                        name="facility_id" required>
                                        <option value="">Select Facility</option>
                                        @foreach ($facilities as $facility)
                                            <option value="{{ $facility->id }}"
                                                {{ old('facility_id', $staff->facility_id) == $facility->id ? 'selected' : '' }}>
                                                {{ $facility->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('facility_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="staff_position_id" class="form-label">Position <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('staff_position_id') is-invalid @enderror"
                                        id="staff_position_id" name="staff_position_id" required>
                                        <option value="">Select Position</option>
                                        @foreach ($staffPositions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ old('staff_position_id', $staff->staff_position_id) == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('staff_position_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Roles <span class="text-danger">*</span></label>
                                    <div class="row">
                                        @foreach ($roles as $role)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input @error('role_id') is-invalid @enderror"
                                                        type="checkbox" name="role_id[]" value="{{ $role->id }}"
                                                        id="role_{{ $role->id }}"
                                                        {{ in_array($role->id, old('role_id', $staff->roles->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                                        {{ $role->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Select at least one role</div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="passport" class="form-label">Passport Photo</label>
                                    <input type="file" class="form-control @error('passport') is-invalid @enderror"
                                        id="passport" name="passport" accept="image/*">
                                    @error('passport')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional: Upload new passport photo (JPG, PNG, GIF - Max
                                        2MB)</small>

                                    @if ($staff->passport)
                                        <div class="mt-2">
                                            <small class="text-muted">Current photo:</small><br>
                                            <img src="{{ asset('storage/' . $staff->passport) }}" alt="Current Passport"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;"
                                                class="img-thumbnail">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fe fe-save me-1"></i> Update Staff Account
                                        </button>
                                        <a href="{{ route('facility-staff.index') }}" class="btn btn-outline-secondary">
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
                        <h6 class="mb-3">Staff Information</h6>
                        <div class="mb-3">
                            <i class="fe fe-calendar text-primary me-2"></i>
                            <strong>Created:</strong> {{ $staff->created_at->format('M d, Y') }}
                        </div>
                        <div class="mb-3">
                            <i class="fe fe-check-circle text-success me-2"></i>
                            <strong>Email Verified:</strong> {{ $staff->email_verified_at ? 'Yes' : 'No' }}
                        </div>
                        <div class="mb-0">
                            <i class="fe fe-clock text-info me-2"></i>
                            <strong>Updated:</strong> {{ $staff->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('facility-staff.create') }}" class="btn btn-outline-primary">
                                <i class="fe fe-plus me-2"></i>Add New Staff
                            </a>
                            <a href="{{ route('facility-staff.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-list me-2"></i>All Staff
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-danger mb-3">Danger Zone</h6>
                        <p class="text-muted small mb-3">This action cannot be undone</p>
                        <form action="{{ route('facility-staff.destroy', $staff->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fe fe-trash"></i> Delete Staff
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
