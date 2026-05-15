@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card custom-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h6 class="main-content-label mb-1" style="color: #01542B;">Staff Member Details</h6>
                                <p class="card-sub-title" style="color: #01542B;">View staff information and permissions</p>
                            </div>
                            <div>
                                @can('staff.edit')
                                    <a href="{{ route('staff.edit', $staff) }}" class="btn btn-primary btn-sm"
                                        style="background-color: #01542B; border-color: #01542B;">
                                        <i class="fe fe-edit"></i> Edit
                                    </a>
                                @endcan
                                <a href="{{ route('staff.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fe fe-list"></i> Back to List
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Full Name</label>
                                    <p class="form-control-plaintext">{{ $staff->fullname }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email Address</label>
                                    <p class="form-control-plaintext">{{ $staff->email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Phone Number</label>
                                    <p class="form-control-plaintext">{{ $staff->phone }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Member Since</label>
                                    <p class="form-control-plaintext">{{ $staff->created_at->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="font-weight-bold mb-3">Assigned Roles</h6>
                                @if ($staff->roles->count() > 0)
                                    @foreach ($staff->roles as $role)
                                        <span class="badge badge-lg mr-2 mb-2"
                                            style="background-color: #01542B; color: white; font-size: 14px; padding: 8px 12px;">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <p class="text-muted">No roles assigned</p>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="font-weight-bold mb-3">Permissions</h6>
                                @if ($staff->getAllPermissions()->count() > 0)
                                    @php
                                        $permissions = $staff->getAllPermissions()->groupBy(function ($permission) {
                                            return explode('.', $permission->name)[0];
                                        });
                                    @endphp

                                    @foreach ($permissions as $module => $perms)
                                        <div class="mb-3">
                                            <h6 class="text-capitalize" style="color: #01542B;">
                                                {{ str_replace('_', ' ', $module) }}</h6>
                                            <div class="pl-3">
                                                @foreach ($perms as $perm)
                                                    <span class="mr-2 mb-2">
                                                        <i class="fe fe-check"></i> {{ $perm->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">No permissions assigned</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
