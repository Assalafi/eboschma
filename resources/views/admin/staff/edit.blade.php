@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card custom-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Edit Staff Member</h6>
                            <p class="card-sub-title" style="color: #01542B;">Update staff account and role assignment</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Validation Error!</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <form action="{{ route('staff.update', $staff) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="fullname">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('fullname') is-invalid @enderror"
                                    id="fullname" name="fullname" value="{{ old('fullname', $staff->fullname) }}" required>
                                @error('fullname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $staff->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone', $staff->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Roles <span class="text-danger">*</span></label>
                                <small class="text-muted d-block mb-2">Select one or more roles for this staff
                                    member</small>
                                <div class="border rounded p-3 bg-light">
                                    @foreach ($roles as $role)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="roles[]"
                                                value="{{ $role->name }}" id="role_{{ $role->name }}"
                                                @error('roles') is-invalid @enderror
                                                {{ in_array($role->name, old('roles', $staff->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->name }}">
                                                <strong>{{ $role->name }}</strong>
                                                @if ($role->description)
                                                    <small class="text-muted d-block">{{ $role->description }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('roles')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="facilities">Assigned Facilities</label>
                                <small class="text-muted d-block mb-2">Select one or more facilities (optional)</small>
                                <select class="form-control select2 @error('facilities') is-invalid @enderror" 
                                    id="facilities" name="facilities[]" multiple>
                                    @php
                                        $selectedFacilities = old('facilities', $staff->facilities->pluck('id')->toArray());
                                    @endphp
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}" {{ in_array($facility->id, $selectedFacilities) ? 'selected' : '' }}>
                                            {{ $facility->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('facilities')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" minlength="8">
                                <small class="form-text text-muted">Leave blank to keep current password. Minimum 8
                                    characters if changing.</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" minlength="8">
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary"
                                    style="background-color: #01542B; border-color: #01542B;">
                                    <i class="fe fe-save"></i> Update Staff Member
                                </button>
                                <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                                    <i class="fe fe-x"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            var checkedRoles = document.querySelectorAll('input[name="roles[]"]:checked');
            if (checkedRoles.length === 0) {
                e.preventDefault();
                alert('Please select at least one role for this staff member.');
                return false;
            }
        });
    </script>
@endsection
