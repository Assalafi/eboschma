@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                Total Roles</p>
                            <h3 class="mb-0 font-weight-bold" style="color: #01542B;">
                                {{ $roles->total() }}</h3>
                            <small style="color: #01542B;">All System Roles</small>
                        </div>
                        <div class="stats-icon bg-primary-light">
                            <i class="fe fe-shield text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                Permissions</p>
                            <h3 class="mb-0 font-weight-bold text-success" style="color: #01542B;">
                                29</h3>
                            <small style="color: #01542B;">Total Permissions</small>
                        </div>
                        <div class="stats-icon bg-success-light">
                            <i class="fe fe-lock text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                Active Roles</p>
                            <h3 class="mb-0 font-weight-bold text-info" style="color: #01542B;">
                                {{ $roles->count() }}</h3>
                            <small style="color: #01542B;">In Use</small>
                        </div>
                        <div class="stats-icon bg-info-light">
                            <i class="fe fe-check-circle text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                Staff Assigned</p>
                            <h3 class="mb-0 font-weight-bold text-warning" style="color: #01542B;">
                                {{ \App\Models\Staff::has('roles')->count() }}</h3>
                            <small style="color: #01542B;">With Roles</small>
                        </div>
                        <div class="stats-icon bg-warning-light">
                            <i class="fe fe-users text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Role Management</h6>
                            <p class="card-sub-title" style="color: #01542B;">Manage roles and assign permissions</p>
                        </div>
                        <div class="d-flex align-items-center">
                            @can('role.create')
                            <a href="{{ route('roles.create') }}" class="btn btn-primary" style="background-color: #01542B; border-color: #01542B;">
                                <i class="fe fe-plus-circle"></i> Create New Role
                            </a>
                            @endcan
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fe fe-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fe fe-alert-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mg-b-0 text-md-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Role Name</th>
                                    <th class="text-center">Permissions</th>
                                    <th class="text-center">Staff Count</th>
                                    <th width="150" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>{{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}</td>
                                    <td>
                                        <strong style="color: #01542B;">
                                            <i class="fe fe-shield"></i> {{ $role->name }}
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-success">
                                            <i class="fe fe-lock"></i> <strong>{{ $role->permissions_count }} permissions</strong>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($role->users()->count() > 0)
                                            <span class="text-info">
                                                <i class="fe fe-users"></i> <strong>{{ $role->users()->count() }} staff</strong>
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                <i class="fe fe-user-x"></i> No staff
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            @can('role.view')
                                            <button type="button" class="btn btn-sm btn-info" 
                                                onclick="window.location='{{ route('roles.show', $role) }}'"
                                                title="View Details">
                                                <i class="fe fe-eye"></i>
                                            </button>
                                            @endcan
                                            
                                            @can('role.edit')
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                style="background-color: #01542B; border-color: #01542B;"
                                                onclick="window.location='{{ route('roles.edit', $role) }}'"
                                                title="Edit">
                                                <i class="fe fe-edit"></i>
                                            </button>
                                            @endcan
                                            
                                            @can('role.delete')
                                                @if($role->name !== 'Super Admin')
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete role {{ $role->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fe fe-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fe fe-shield" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mb-0">No roles found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination and Results Info -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }}
                                    of {{ $roles->total() ?? 0 }} results
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                {{ $roles->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
