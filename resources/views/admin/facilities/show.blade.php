@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">{{ $facility->name }}</h6>
                            <p class="text-muted card-sub-title">Facility details and information</p>
                        </div>
                        <div>
                            <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-primary me-2">
                                <i class="fe fe-edit"></i> Edit
                            </a>
                            <a href="{{ route('facilities.index') }}" class="btn btn-secondary">
                                <i class="fe fe-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-lg brround me-3 bg-primary-transparent">
                                    <i class="fe fe-home text-primary fs-18"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $facility->name }}</h5>
                                    <p class="text-muted mb-0">
                                        @if ($facility->type)
                                            <span class="badge bg-info">{{ $facility->type }}</span>
                                        @else
                                            <span class="text-muted">Type not specified</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="info-item">
                                <label class="form-label text-muted small">Local Government Area</label>
                                <div class="h6">{{ $facility->lga }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="info-item">
                                <label class="form-label text-muted small">Ward</label>
                                <div class="h6">{{ $facility->ward }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="info-item">
                                <label class="form-label text-muted small">Date Created</label>
                                <div class="h6">{{ $facility->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="info-item">
                                <label class="form-label text-muted small">Last Updated</label>
                                <div class="h6">{{ $facility->updated_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <h6 class="main-content-label mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-primary">
                            <i class="fe fe-edit me-2"></i>Edit Facility
                        </a>
                        <a href="{{ route('facilities.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-list me-2"></i>All Facilities
                        </a>
                        <a href="{{ route('facilities.create') }}" class="btn btn-outline-primary">
                            <i class="fe fe-plus me-2"></i>Add New Facility
                        </a>
                    </div>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-body">
                    <h6 class="main-content-label mb-3">Location Summary</h6>
                    <div class="mb-3">
                        <i class="fe fe-map-pin text-primary me-2"></i>
                        <strong>State:</strong> Borno State
                    </div>
                    <div class="mb-3">
                        <i class="fe fe-map text-info me-2"></i>
                        <strong>LGA:</strong> {{ $facility->lga }}
                    </div>
                    <div class="mb-0">
                        <i class="fe fe-navigation text-success me-2"></i>
                        <strong>Ward:</strong> {{ $facility->ward }}
                    </div>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-body">
                    <h6 class="main-content-label mb-3">Danger Zone</h6>
                    <p class="text-muted small mb-3">This action cannot be undone</p>
                    <form action="{{ route('facilities.destroy', $facility) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this facility? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fe fe-trash"></i> Delete Facility
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
