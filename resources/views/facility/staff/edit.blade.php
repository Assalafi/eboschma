@extends('layouts.facility')

@section('title', 'Edit Staff Member')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Edit
                                    Staff Member</h1>
                                <p class="text-muted mb-0">Update staff member information and account details</p>
                            </div>
                            <div>
                                <a href="{{ route('facility.staff.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Back to Staff
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-alert-circle me-2"></i>
                                    <div>
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-alert-circle me-2"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti-check-circle me-2"></i>
                                    <span>{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('facility.staff.updatef', $staff->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            <!-- Staff Photo Preview -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-camera me-2 text-primary"></i>Current Photo
                                </h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="text-center p-3" style="background: #f8f9fa; border-radius: 8px;">
                                            @if ($staff->passport)
                                                <img src="{{ asset('storage/' . $staff->passport) }}"
                                                    class="img-fluid rounded mb-2"
                                                    style="max-width: 150px; max-height: 150px; object-fit: cover; border: 3px solid #e9ecef;">
                                                <p class="text-muted small mb-0">Current photo</p>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center mb-2">
                                                    <i class="ti-user text-muted" style="font-size: 4rem;"></i>
                                                </div>
                                                <p class="text-muted small mb-0">No photo uploaded</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="passport" class="form-label fw-semibold">Update Photo</label>
                                        <input type="file" class="form-control @error('passport') is-invalid @enderror"
                                            id="passport" name="passport" accept="image/*">
                                        <div class="form-text">Leave empty to keep current photo (JPEG, PNG, max 2MB)</div>
                                        @error('passport')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-user me-2 text-primary"></i>Personal Information
                                </h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-semibold">Full Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $staff->name) }}"
                                            placeholder="Enter staff member's full name" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-semibold">Email Address <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $staff->email) }}"
                                            placeholder="staff@example.com" required>
                                        @error('email')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone', $staff->phone) }}"
                                            placeholder="+234 800 000 0000">
                                        @error('phone')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="staff_position_id" class="form-label fw-semibold">Position <span
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
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Roles <span
                                                class="text-danger">*</span></label>
                                        <div class="row">
                                            @foreach ($roles as $role)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input @error('role_id') is-invalid @enderror"
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
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">Select at least one role</div>
                                    </div>

                                </div>
                            </div>

                            <!-- Security Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-lock me-2 text-primary"></i>Security Information
                                </h5>

                                <div class="alert alert-info d-flex align-items-center mb-3">
                                    <i class="ti-info-alt me-2"></i>
                                    <div>
                                        <strong>Password Change:</strong> Leave password fields empty to keep current
                                        password.
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-semibold">New Password</label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password"
                                                placeholder="Enter new password (optional)">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="ti-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Leave empty to keep current password (min 6 characters)
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label fw-semibold">Confirm New
                                            Password</label>
                                        <input type="password"
                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                            id="password_confirmation" name="password_confirmation"
                                            placeholder="Confirm new password">
                                        @error('password_confirmation')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-info-alt me-2 text-primary"></i>Account Information
                                </h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Staff ID</label>
                                        <input type="text" class="form-control" value="{{ $staff->id }}" readonly>
                                        <div class="form-text">Unique staff identifier</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Date Joined</label>
                                        <input type="text" class="form-control"
                                            value="{{ $staff->created_at->format('M d, Y') }}" readonly>
                                        <div class="form-text">Account creation date</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Facility</label>
                                        <input type="text" class="form-control"
                                            value="{{ $staff->facility->name ?? 'Current Facility' }}" readonly>
                                        <div class="form-text">Assigned facility</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Last Updated</label>
                                        <input type="text" class="form-control"
                                            value="{{ $staff->updated_at->format('M d, Y H:i') }}" readonly>
                                        <div class="form-text">Last modification date</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">

                                <div>
                                    <a href="{{ route('facility.staff.index') }}" class="btn btn-outline-secondary me-2">
                                        <i class="ti-arrow-left me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-save me-1"></i> Update Staff
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-info-alt me-2 text-info"></i>Account Status
                        </h6>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check-circle text-success me-2"></i>
                                <span class="small fw-semibold">Account Active</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-home text-primary me-2"></i>
                                <span class="small">Facility: {{ $staff->facility->name ?? 'Current Facility' }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-briefcase text-info me-2"></i>
                                <span class="small">Position:
                                    {{ $staff->staffPosition ? $staff->staffPosition->name : 'Not Assigned' }}</span>
                            </div>
                            @if ($staff->id === Auth::guard('web')->id())
                                <div class="d-flex align-items-center">
                                    <i class="ti-user text-warning me-2"></i>
                                    <span class="small fw-semibold">This is your account</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-help-circle me-2 text-primary"></i>Update Guidelines
                        </h6>
                        <div class="mb-3">
                            <p class="small text-muted mb-2">
                                <strong>Email Changes:</strong> Staff will need to use new email for login.
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Password Reset:</strong> Communicate new password securely to staff member.
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Photo Update:</strong> New photo will replace existing one immediately.
                            </p>
                            <p class="small text-muted mb-0">
                                <strong>Account Deletion:</strong> Cannot be undone and removes all access.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('ti-eye');
                toggleIcon.classList.add('ti-eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('ti-eye-off');
                toggleIcon.classList.add('ti-eye');
            }
        });
    </script>

    <style>
        .form-control:focus,
        .form-select:focus {
            border-color: #01542B;
            box-shadow: 0 0 0 0.2rem rgba(1, 84, 43, 0.25);
        }

        .btn-primary {
            background-color: #01542B;
            border-color: #01542B;
        }

        .btn-primary:hover {
            background-color: #014121;
            border-color: #014121;
        }

        .card {
            border: none;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }
    </style>
@endsection
