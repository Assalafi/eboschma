@extends('layouts.app')

@section('title', 'Service Details')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">{{ $service->name }}</h4>
                                <p class="text-muted mb-0">Service details and facilities</p>
                            </div>
                            <div>
                                <a href="{{ route('services.edit', $service->id) }}" class="btn btn-primary me-2">
                                    <i class="ti-pencil me-1"></i> Edit
                                </a>
                                <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-lg me-3" style="background: #e3f2fd;">
                                        <i class="ti-package" style="font-size: 2rem; color: #2196f3;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $service->name }}</h5>
                                        <p class="text-muted mb-0">
                                            {!! $service->type_badge !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="info-item">
                                    <label class="form-label text-muted small">Service Name</label>
                                    <div class="h6">{{ $service->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="info-item">
                                    <label class="form-label text-muted small">Service Type</label>
                                    <div class="h6">{!! $service->type_badge !!}</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="info-item">
                                    <label class="form-label text-muted small">Price</label>
                                    <div class="h5 text-success">{{ $service->price_with_currency }}</div>
                                </div>
                            </div>
                        </div>

                        @if ($service->description)
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">Description</label>
                                        <div class="h6">{{ $service->description }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="info-item">
                                    <label class="form-label text-muted small">Date Created</label>
                                    <div class="h6">{{ $service->created_at->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="info-item">
                                    <label class="form-label text-muted small">Last Updated</label>
                                    <div class="h6">{{ $service->updated_at->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>
                        </div>

                        @if ($service->type === 'Secondary' && $facilities->count() > 0)
                            <div class="row">
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h5 class="mb-3">
                                        <i class="ti-home me-2"></i>Facilities Providing This Service
                                        <span class="badge bg-info ms-2">{{ $facilities->count() }}</span>
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Facility Name</th>
                                                    <th>LGA</th>
                                                    <th>Ward</th>
                                                    <th>Type</th>
                                                    <th class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($facilities as $facility)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $facility->name }}</strong>
                                                        </td>
                                                        <td>{{ $facility->lga }}</td>
                                                        <td>{{ $facility->ward }}</td>
                                                        <td>
                                                            @if ($facility->type)
                                                                <span class="badge bg-info">{{ $facility->type }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('facilities.show', $facility->id) }}"
                                                                class="btn btn-sm btn-primary" title="View Facility">
                                                                <i class="ti-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @elseif($service->type === 'Secondary')
                            <div class="row">
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h5 class="mb-3">
                                        <i class="ti-home me-2"></i>Facilities Providing This Service
                                    </h5>
                                    <div class="alert alert-info">
                                        <i class="ti-info-alt me-2"></i>No facilities have been assigned to provide this
                                        service yet.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-primary">
                                <i class="ti-pencil me-2"></i>Edit Service
                            </a>
                            <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
                                <i class="ti-list me-2"></i>All Services
                            </a>
                            <a href="{{ route('services.create') }}" class="btn btn-outline-primary">
                                <i class="ti-plus me-2"></i>Add New Service
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Service Statistics</h6>
                        <div class="mb-3">
                            <i class="ti-package text-primary me-2"></i>
                            <strong>Type:</strong> {{ $service->type }}
                        </div>
                        @if ($service->type === 'Secondary')
                            <div class="mb-3">
                                <i class="ti-home text-info me-2"></i>
                                <strong>Facilities:</strong> {{ $facilities->count() }}
                            </div>
                        @endif
                        <div class="mb-0">
                            <i class="ti-calendar text-success me-2"></i>
                            <strong>Created:</strong> {{ $service->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-danger mb-3">Danger Zone</h6>
                        <p class="text-muted small mb-3">This action cannot be undone</p>
                        <form action="{{ route('services.destroy', $service->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this service? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="ti-trash"></i> Delete Service
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
