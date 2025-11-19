@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Programs Management</h6>
                            <p class="text-muted card-sub-title">List of all programs in the system ({{ $programs->total() }} total)</p>
                        </div>
                        <div>
                            <a href="{{ route('programs.create') }}" class="btn btn-primary">
                                <i class="fe fe-plus me-1"></i> Add New Program
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Search and Filter -->
                    <form method="GET" action="{{ route('programs.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search programs..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="has_dependant">
                                    <option value="">All Dependant Types</option>
                                    <option value="1" {{ request('has_dependant') == '1' ? 'selected' : '' }}>With Dependants</option>
                                    <option value="0" {{ request('has_dependant') == '0' ? 'selected' : '' }}>Without Dependants</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fe fe-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Programs Table -->
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Format</th>
                                    <th>Has Dependant</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $program)
                                <tr>
                                    <td>{{ $program->id }}</td>
                                    <td><strong>{{ $program->name }}</strong></td>
                                    <td>{{ $program->format }}</td>
                                    <td>
                                        @if($program->has_dependant)
                                            <span class="badge bg-success">
                                                <i class="fe fe-check"></i> Yes
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fe fe-x"></i> No
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->status)
                                            <span class="badge bg-success">
                                                <i class="fe fe-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fe fe-x-circle"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $program->created_at->format('M d, Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('programs.show', $program->id) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <form action="{{ route('programs.destroy', $program->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fe fe-inbox" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="mt-2 text-muted">No programs found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($programs->hasPages())
                    <div class="mt-4">
                        {{ $programs->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
