@extends('layouts.facility')

@section('title', 'Staff Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">Staff
                            Management</h1>
                        <p class="text-muted mb-0">Manage staff members for your facility</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                            <i class="ti-printer me-1"></i> Print List
                        </button>
                        <a href="{{ route('facility.staff.create') }}" class="btn btn-primary">
                            <i class="ti-user-plus me-1"></i> Add Staff
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-primary text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-users" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_staff'] }}</h3>
                                        <p class="text-muted mb-0 small">Total Staff</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-success text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-camera" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-success">{{ $stats['with_photo'] }}</h3>
                                        <p class="text-muted mb-0 small">With Photos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-warning text-white me-3"
                                        style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="ti-time" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['new_this_month'] }}</h3>
                                        <p class="text-muted mb-0 small">New This Month</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <form method="GET" action="{{ route('facility.staff.index') }}">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold text-dark">Search Staff</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="ti-search text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" name="search"
                                            value="{{ $search }}" placeholder="Search by name, email...">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold text-dark">Position</label>
                                    <select class="form-select" name="position">
                                        <option value="">All Positions</option>
                                        @foreach ($staffPositions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ $position == $position ? 'selected' : '' }}>{{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti-search me-2"></i>Search
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('facility.staff.index') }}" class="btn btn-outline-secondary w-100">
                                        <i class="ti-refresh me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Staff Table -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom" style="padding: 1.5rem;">
                        <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                            <i class="ti-users me-2 text-primary"></i>Staff Members
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($staff->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 fw-semibold text-dark">Staff Member</th>
                                            <th class="border-0 fw-semibold text-dark">Role</th>
                                            <th class="border-0 fw-semibold text-dark">Position</th>
                                            <th class="border-0 fw-semibold text-dark">Email</th>
                                            <th class="border-0 fw-semibold text-dark">Phone</th>
                                            <th class="border-0 fw-semibold text-dark">Joined</th>
                                            <th class="border-0 fw-semibold text-center text-dark">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($staff as $member)
                                            <tr>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        @if ($member->passport)
                                                            <img src="{{ asset('storage/' . $member->passport) }}"
                                                                class="avatar avatar-sm rounded-circle me-3"
                                                                style="width: 40px; height: 40px; object-fit: cover;">
                                                        @else
                                                            <div class="avatar avatar-sm rounded-circle bg-light me-3 d-flex align-items-center justify-content-center"
                                                                style="width: 40px; height: 40px;">
                                                                <i class="ti-user text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $member->name }}</div>
                                                            @if ($member->id === Auth::guard('web')->id())
                                                                <span class="badge bg-info"
                                                                    style="font-size: 0.625rem;">You</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    @php
                                                        $roles = DB::table('model_has_roles')
                                                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                                            ->where('model_has_roles.model_id', $member->id)
                                                            ->where('model_has_roles.model_type', 'App\Models\User')
                                                            ->pluck('roles.name');
                                                    @endphp
                                                    @if ($roles->count() > 0)
                                                        @foreach ($roles as $role)
                                                            <span
                                                                class="badge bg-light text-dark fw-semibold me-1">{{ $role }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="badge bg-light text-dark fw-semibold">Not
                                                            Assigned</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-light text-dark fw-semibold">
                                                        {{ $member->staffPosition ? $member->staffPosition->name : 'Not Assigned' }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-muted">{{ $member->email }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-muted">{{ $member->phone ?: 'N/A' }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="text-muted">{{ $member->created_at->format('M d, Y') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('facility.staff.edit', $member->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="ti-pencil"></i>
                                                        </a>
                                                        @if ($member->id !== Auth::guard('web')->id())
                                                            <form
                                                                action="{{ route('facility.staff.destroy', $member->id) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to remove this staff member?')"
                                                                    title="Delete">
                                                                    <i class="ti-trash"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button class="btn btn-sm btn-outline-secondary" disabled
                                                                title="Cannot delete your own account">
                                                                <i class="ti-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        Showing {{ $staff->firstItem() }} to {{ $staff->lastItem() }} of
                                        {{ $staff->total() }} entries
                                    </div>
                                    {{ $staff->links('pagination.bootstrap-5') }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar avatar-lg bg-light text-muted mb-3"
                                    style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto;">
                                    <i class="ti-users" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="text-muted mb-2">No Staff Members Found</h5>
                                <p class="text-muted">No staff members match your search criteria.</p>
                                <a href="{{ route('facility.staff.create') }}" class="btn btn-primary">
                                    <i class="ti-user-plus me-2"></i>Add First Staff Member
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table th {
            border-bottom: 2px solid #e9ecef !important;
        }

        .table td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f8f9fa !important;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .avatar {
            object-fit: cover;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        @media print {

            .btn,
            .btn-group {
                display: none !important;
            }

            .table {
                font-size: 12px;
            }
        }
    </style>
@endsection
