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
                                Total Staff</p>
                            <h3 class="mb-0 font-weight-bold" style="color: #01542B;">
                                {{ $staff->total() }}</h3>
                            <small style="color: #01542B;">All Staff Members</small>
                        </div>
                        <div class="stats-icon bg-primary-light">
                            <i class="fe fe-users text-primary"></i>
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
                                Active Today</p>
                            <h3 class="mb-0 font-weight-bold text-success" style="color: #01542B;">
                                {{ $staff->count() }}</h3>
                            <small style="color: #01542B;">Current Page</small>
                        </div>
                        <div class="stats-icon bg-success-light">
                            <i class="fe fe-user-check text-success"></i>
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
                                With Roles</p>
                            <h3 class="mb-0 font-weight-bold text-info" style="color: #01542B;">
                                {{ $staff->filter(function($s) { return $s->roles->count() > 0; })->count() }}</h3>
                            <small style="color: #01542B;">Assigned Roles</small>
                        </div>
                        <div class="stats-icon bg-info-light">
                            <i class="fe fe-shield text-info"></i>
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
                            <h3 class="mb-0 font-weight-bold text-warning" style="color: #01542B;">
                                29</h3>
                            <small style="color: #01542B;">System Permissions</small>
                        </div>
                        <div class="stats-icon bg-warning-light">
                            <i class="fe fe-lock text-warning"></i>
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
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Staff Management</h6>
                            <p class="card-sub-title" style="color: #01542B;">Manage staff members and their roles</p>
                        </div>
                        <div class="d-flex align-items-center">
                            @can('staff.create')
                            <a href="{{ route('staff.create') }}" class="btn btn-primary" style="background-color: #01542B; border-color: #01542B;">
                                <i class="fe fe-plus-circle"></i> Add New Staff
                            </a>
                            @endcan
                        </div>
                    </div>

                    <form method="GET" action="{{ route('staff.index') }}" class="mb-4">
                        <div class="input-group" style="max-width: 420px;">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Search by name, email or phone..."
                                   value="{{ $search }}"
                                   autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" style="background-color: #01542B; border-color: #01542B;">
                                    <i class="fe fe-search"></i>
                                </button>
                                @if($search)
                                <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary" title="Clear search">
                                    <i class="fe fe-x"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @if($search)
                        <small class="text-muted mt-1 d-block">
                            Showing results for <strong>"{{ $search }}"</strong> &mdash; {{ $staff->total() }} found
                        </small>
                        @endif
                    </form>

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
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th width="150" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $member)
                                <tr>
                                    <td>{{ $loop->iteration + ($staff->currentPage() - 1) * $staff->perPage() }}</td>
                                    <td><strong style="color: #01542B;">{{ $member->fullname }}</strong></td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->phone }}</td>
                                    <td>
                                        @if($member->roles->count() > 0)
                                            @foreach($member->roles as $role)
                                                <span class="badge" style="background-color: #01542B; color: white;">
                                                    <i class="fe fe-shield"></i> {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">
                                                <i class="fe fe-alert-circle"></i> No Role
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <i class="fe fe-calendar"></i> {{ $member->created_at->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            @can('staff.view')
                                            <button type="button" class="btn btn-sm btn-info" 
                                                onclick="window.location='{{ route('staff.show', $member) }}'"
                                                title="View Details">
                                                <i class="fe fe-eye"></i>
                                            </button>
                                            @endcan
                                            
                                            @can('staff.edit')
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                style="background-color: #01542B; border-color: #01542B;"
                                                onclick="window.location='{{ route('staff.edit', $member) }}'"
                                                title="Edit">
                                                <i class="fe fe-edit"></i>
                                            </button>
                                            @endcan
                                            
                                            @can('staff.delete')
                                                @if(auth('staff')->id() !== $member->id)
                                                <form action="{{ route('staff.destroy', $member) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete {{ $member->fullname }}?');">
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fe fe-users" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mb-0">No staff members found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 gap-3">
                        <div>
                            <p class="text-muted mb-0">
                                Showing {{ $staff->firstItem() ?? 0 }} to {{ $staff->lastItem() ?? 0 }} 
                                of {{ $staff->total() }} results
                            </p>
                        </div>
                        <div class="overflow-auto w-100 w-md-auto">
                            @if ($staff->hasPages())
                                <nav aria-label="Staff pagination">
                                    <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                        {{-- Previous Page Link --}}
                                        @if ($staff->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $staff->previousPageUrl() }}" rel="prev">Prev</a></li>
                                        @endif

                                        {{-- Pagination Elements with Smart Window --}}
                                        @php
                                            $currentPage = $staff->currentPage();
                                            $lastPage = $staff->lastPage();
                                            $onEachSide = 2; // Show 2 pages on each side of current page
                                            
                                            // Calculate start and end of the sliding window
                                            $start = max(1, $currentPage - $onEachSide);
                                            $end = min($lastPage, $currentPage + $onEachSide);
                                            
                                            // Adjust if we're near the beginning or end
                                            if ($currentPage <= $onEachSide + 1) {
                                                $end = min($lastPage, ($onEachSide * 2) + 2);
                                            }
                                            if ($currentPage >= $lastPage - $onEachSide) {
                                                $start = max(1, $lastPage - ($onEachSide * 2) - 1);
                                            }
                                        @endphp

                                        {{-- First Page --}}
                                        @if ($start > 1)
                                            <li class="page-item"><a class="page-link" href="{{ $staff->url(1) }}">1</a></li>
                                            @if ($start > 2)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                        @endif

                                        {{-- Page Number Links --}}
                                        @for ($page = $start; $page <= $end; $page++)
                                            @if ($page == $currentPage)
                                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link" href="{{ $staff->url($page) }}">{{ $page }}</a></li>
                                            @endif
                                        @endfor

                                        {{-- Last Page --}}
                                        @if ($end < $lastPage)
                                            @if ($end < $lastPage - 1)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item"><a class="page-link" href="{{ $staff->url($lastPage) }}">{{ $lastPage }}</a></li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if ($staff->hasMorePages())
                                            <li class="page-item"><a class="page-link" href="{{ $staff->nextPageUrl() }}" rel="next">Next</a></li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
