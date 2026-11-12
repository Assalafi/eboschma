@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Facilities Management</h6>
                            <p class="text-muted card-sub-title">List of all registered facilities in Borno State ({{ $facilities->total() }} total)</p>
                        </div>
                        <div>
                            <a href="{{ route('facilities.create') }}" class="btn btn-primary me-2">
                                <i class="fe fe-plus-circle"></i> New Facility
                            </a>
                            <a href="{{ route('facilities.upload.form') }}" class="btn btn-success">
                                <i class="fe fe-upload"></i> Upload Excel
                            </a>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('facility_activity_blocks'))
                        <div class="alert alert-warning border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fe fe-lock fs-18 me-2 mt-1"></i>
                                <div class="w-100">
                                    <div class="fw-semibold mb-1">Some facilities were protected from deletion</div>
                                    <div class="small mb-3">The following facilities have linked activity. Review the complete activity breakdown before trying again.</div>
                                    @foreach (session('facility_activity_blocks') as $blockedFacility)
                                        <div class="bg-white rounded-2 p-2 mb-2">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <a href="{{ route('facilities.show', $blockedFacility['id']) }}" class="fw-semibold text-dark text-decoration-none">
                                                    {{ $blockedFacility['name'] }}
                                                </a>
                                                <span class="badge bg-danger-transparent text-danger">{{ number_format($blockedFacility['total']) }} linked records</span>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                @foreach ($blockedFacility['activities'] as $activity)
                                                    <span class="me-3">{{ $activity['label'] }}: <strong>{{ number_format($activity['count']) }}</strong></span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <form method="GET" action="{{ route('facilities.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search facilities..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">LGA</label>
                                <select name="lga" class="form-select">
                                    <option value="">All LGAs</option>
                                    @foreach ($lgas as $lga)
                                        <option value="{{ $lga }}" {{ request('lga') == $lga ? 'selected' : '' }}>
                                            {{ $lga }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('facilities.index') }}" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if ($facilities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>LGA</th>
                                            <th>Ward</th>
                                            <th>Type</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($facilities as $facility)
                                            <tr>
                                                <td>{{ $facilities->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md brround me-3 bg-primary-transparent">
                                                        <i class="fe fe-home text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $facility->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $facility->lga }}</td>
                                            <td>{{ $facility->ward }}</td>
                                            <td>
                                                @if ($facility->type)
                                                    <span class="badge bg-info">{{ $facility->type }}</span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                            <td>{{ $facility->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('facilities.show', $facility) }}"
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    <a href="{{ route('facilities.edit', $facility) }}"
                                                        class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing {{ $facilities->firstItem() ?? 0 }} to {{ $facilities->lastItem() ?? 0 }} 
                                    of {{ $facilities->total() }} results
                                </p>
                            </div>
                            <div>
                                {{ $facilities->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-home fe-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No facilities found</h5>
                            <p class="text-muted">
                                @if (request()->hasAny(['search', 'lga', 'type']))
                                    No facilities match your search criteria.
                                    <a href="{{ route('facilities.index') }}">Clear filters</a>
                                @else
                                    <a href="{{ route('facilities.create') }}">Add your first facility</a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
