@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card custom-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Role Details</h6>
                            <p class="card-sub-title" style="color: #01542B;">View role information and assigned permissions</p>
                        </div>
                        <div>
                            @can('role.edit')
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary btn-sm" style="background-color: #01542B; border-color: #01542B;">
                                <i class="fe fe-edit"></i> Edit Role
                            </a>
                            @endcan
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fe fe-list"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Role Name</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-lg" style="background-color: #01542B; color: white; font-size: 16px; padding: 8px 15px;">
                                        {{ $role->name }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Staff Members</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-info badge-lg" style="font-size: 16px; padding: 8px 15px;">
                                        {{ $role->users()->count() }} assigned
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="font-weight-bold mb-3">Assigned Permissions ({{ $role->permissions->count() }})</h6>
                            
                            @if($permissions->count() > 0)
                                @foreach($permissions as $module => $perms)
                                    <div class="card mb-3">
                                        <div class="card-header" style="background-color: #01542B; color: white;">
                                            <h6 class="mb-0 text-capitalize">
                                                <i class="fe fe-shield"></i> {{ str_replace('_', ' ', $module) }} Management
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($perms as $perm)
                                                    <div class="col-md-4 col-lg-3 mb-2">
                                                        <span class="badge badge-success" style="font-size: 13px; padding: 6px 10px;">
                                                            <i class="fe fe-check"></i> {{ ucfirst(str_replace('_', ' ', explode('.', $perm->name)[1])) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning">
                                    <i class="fe fe-alert-triangle"></i> No permissions assigned to this role
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
