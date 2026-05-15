@extends('layouts.app')

@section('title', 'View Service Category')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Category Header Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="page-title mb-1">{{ $category->name }}</h4>
                                <p class="text-muted mb-0">Service Category Details</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('service-categories.edit', $category->id) }}" class="btn btn-primary">
                                    <i class="fe fe-edit me-1"></i> Edit Category
                                </a>
                                <a href="{{ route('service-categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h2 class="mb-1 text-primary">{{ $category->service_types_count }}</h2>
                                <p class="text-muted mb-0">Service Types</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h2 class="mb-1 text-success">{{ $category->service_items_count }}</h2>
                                <p class="text-muted mb-0">Total Service Items</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h2 class="mb-1 text-info">{{ $category->created_at->format('M d, Y') }}</h2>
                                <p class="text-muted mb-0">Created Date</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Types List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Service Types in this Category</h5>
                        <a href="{{ route('service-types.create') }}" class="btn btn-sm btn-primary">
                            <i class="fe fe-plus me-1"></i> Add Service Type
                        </a>
                    </div>
                    <div class="card-body">
                        @if($serviceTypes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Service Type Name</th>
                                            <th class="text-center">Service Items</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($serviceTypes as $index => $type)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('service-types.show', $type->id) }}" class="text-primary fw-semibold">
                                                        {{ $type->name }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary-lt">{{ $type->service_items_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('service-types.show', $type->id) }}" class="btn btn-sm btn-info me-1" title="View">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    <a href="{{ route('service-types.edit', $type->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <form action="{{ route('service-types.destroy', $type->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service type?')" title="Delete">
                                                            <i class="fe fe-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fe fe-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No service types found in this category.</p>
                                <a href="{{ route('service-types.create') }}" class="btn btn-primary mt-3">
                                    <i class="fe fe-plus me-1"></i> Add First Service Type
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
