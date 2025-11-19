@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card custom-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="main-content-label mb-1" style="color: #01542B;">Edit Role</h6>
                        <p class="card-sub-title" style="color: #01542B;">Update role name and permissions</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Validation Error!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Role Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $role->name) }}" 
                                   {{ $role->name === 'Super Admin' ? 'readonly' : '' }}
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Assign Permissions</h6>
                        <p class="text-muted mb-4">Select the permissions for this role</p>

                        @foreach($permissions as $module => $perms)
                            <div class="card mb-3">
                                <div class="card-header" style="background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-capitalize" style="color: #01542B;">
                                            <i class="fe fe-shield"></i> {{ str_replace('_', ' ', $module) }} Management
                                        </h6>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary"
                                                style="color: #01542B; border-color: #01542B;"
                                                onmouseover="this.style.backgroundColor='#01542B'; this.style.color='white';"
                                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='#01542B';"
                                                onclick="toggleModule('{{ $module }}')">
                                            <i class="fe fe-check-square"></i> Toggle All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($perms as $permission)
                                            <div class="col-md-4 col-lg-3">
                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="checkbox" 
                                                           class="custom-control-input module-{{ $module }}" 
                                                           id="perm-{{ $permission->id }}" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}"
                                                           {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="perm-{{ $permission->id }}">
                                                        {{ ucfirst(str_replace('_', ' ', explode('.', $permission->name)[1])) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary" style="background-color: #01542B; border-color: #01542B;">
                                <i class="fe fe-save"></i> Update Role
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
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
function toggleModule(module) {
    const checkboxes = document.querySelectorAll('.module-' + module);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}
</script>
@endsection
