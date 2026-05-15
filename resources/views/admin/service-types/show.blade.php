@extends('layouts.app')

@section('title', 'View Service Type')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Type Header Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-2">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('service-categories.index') }}">Categories</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('service-categories.show', $type->serviceCategory->id) }}">{{ $type->serviceCategory->name }}</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{ $type->name }}</li>
                                    </ol>
                                </nav>
                                <h4 class="page-title mb-1">{{ $type->name }}</h4>
                                <p class="text-muted mb-0">
                                    <span class="badge bg-secondary-lt">{{ $type->serviceCategory->name }}</span>
                                    Service Type Details
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('service-types.edit', $type->id) }}" class="btn btn-primary">
                                    <i class="fe fe-edit me-1"></i> Edit Type
                                </a>
                                <a href="{{ route('service-categories.show', $type->serviceCategory->id) }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back to Category
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
                                <h2 class="mb-1 text-primary">{{ $type->service_items_count }}</h2>
                                <p class="text-muted mb-0">Service Items</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h2 class="mb-1 text-success">₦{{ number_format($serviceItems->sum('price'), 2) }}</h2>
                                <p class="text-muted mb-0">Total Value</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h2 class="mb-1 text-info">{{ $type->created_at->format('M d, Y') }}</h2>
                                <p class="text-muted mb-0">Created Date</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Items List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Service Items in this Type</h5>
                        <a href="{{ route('service-items.create') }}" class="btn btn-sm btn-primary">
                            <i class="fe fe-plus me-1"></i> Add Service Item
                        </a>
                    </div>
                    <div class="card-body">
                        @if($serviceItems->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Description</th>
                                            <th class="text-center">Item Type</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($serviceItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-semibold">{{ $item->name }}</td>
                                                <td>
                                                    <span class="text-muted">{{ Str::limit($item->description, 50) ?: '-' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($item->type === 'Primary')
                                                        <span class="badge bg-success-lt">Primary</span>
                                                    @elseif($item->type === 'Secondary')
                                                        <span class="badge bg-warning-lt">Secondary</span>
                                                    @else
                                                        <span class="badge bg-secondary-lt">{{ $item->type }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">₦{{ number_format($item->price, 2) }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('service-items.edit', $item->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <form action="{{ route('service-items.destroy', $item->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service item?')" title="Delete">
                                                            <i class="fe fe-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="4" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold">₦{{ number_format($serviceItems->sum('price'), 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fe fe-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No service items found in this type.</p>
                                <a href="{{ route('service-items.create') }}" class="btn btn-primary mt-3">
                                    <i class="fe fe-plus me-1"></i> Add First Service Item
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
