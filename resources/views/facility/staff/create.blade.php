@extends('layouts.facility')

@section('title', 'Add Staff Member')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Add
                                    Staff Member</h1>
                                <p class="text-muted mb-0">Create a new staff account for your facility</p>
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

                        <form action="{{ route('facility.staff.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

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
                                            id="name" name="name" value="{{ old('name') }}"
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
                                            id="email" name="email" value="{{ old('email') }}"
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
                                            id="phone" name="phone" value="{{ old('phone') }}"
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
                                                    {{ old('staff_position_id') == $position->id ? 'selected' : '' }}>
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
                                                            {{ in_array($role->id, old('role_id', [])) ? 'checked' : '' }}>
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

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-semibold">Password <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password" placeholder="Enter secure password"
                                                required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="ti-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label fw-semibold">Confirm Password
                                            <span class="text-danger">*</span></label>
                                        <input type="password"
                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                            id="password_confirmation" name="password_confirmation"
                                            placeholder="Confirm password" required>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Photo Upload -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-camera me-2 text-primary"></i>Staff Photo
                                </h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="passport" class="form-label fw-semibold">Passport Photograph</label>
                                        <input type="file" class="form-control @error('passport') is-invalid @enderror"
                                            id="passport" name="passport" accept="image/*">
                                        <div class="form-text">Optional: Upload a clear passport photograph (JPEG, PNG, max
                                            2MB)</div>
                                        @error('passport')
                                            <div class="invalid-feedback d-block">
                                                <i class="ti-alert-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-center align-items-center h-100">
                                            <div id="imagePreview" class="text-center">
                                                <i class="ti-camera text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted small mt-2">Photo preview will appear here</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Facility Information -->
                            <div class="mb-4">
                                <h5 class="card-title fw-bold mb-3" style="color: #01542B;">
                                    <i class="ti-home me-2 text-primary"></i>Facility Information
                                </h5>

                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="ti-info-alt me-2"></i>
                                    <div>
                                        <strong>This staff member will be assigned to your facility:</strong>
                                        <span
                                            class="fw-semibold">{{ Auth::guard('web')->user()->facility->name ?? 'Current Facility' }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="facility_id" value="{{ $facilityId }}">
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                <a href="{{ route('facility.staff.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Cancel
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-secondary me-2">
                                        <i class="ti-refresh me-1"></i> Reset Form
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-user-plus me-1"></i> Add Staff Member
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
                            <i class="ti-info-alt me-2 text-info"></i>Staff Account Information
                        </h6>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Staff will have access to facility portal</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Can manage patients and view reports</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti-check text-success me-2"></i>
                                <span class="small">Assigned to your facility only</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ti-shield text-warning me-2"></i>
                                <span class="small">Password should be secure and unique</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="card-title fw-bold mb-3" style="color: #01542B;">
                            <i class="ti-help-circle me-2 text-primary"></i>Quick Tips
                        </h6>
                        <div class="mb-3">
                            <p class="small text-muted mb-2">
                                <strong>Email Address:</strong> Use a professional email that the staff member can access.
                            </p>
                            <p class="small text-muted mb-2">
                                <strong>Photo:</strong> A clear passport photo helps with identification.
                            </p>
                            <p class="small text-muted mb-0">
                                <strong>Password:</strong> Share the password securely with the staff member after account
                                creation.
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

        // Image preview
        document.getElementById('passport').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="img-fluid rounded" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover; border: 3px solid #e9ecef;">
                        <p class="text-muted small mt-2">${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `
                    <i class="ti-camera text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted small mt-2">Photo preview will appear here</p>
                `;
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
