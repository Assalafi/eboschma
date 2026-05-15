@extends('layouts.app')

@section('title', 'Beneficiary Categories')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Beneficiary Categories</h4>
                                    <p class="text-muted mb-0">Manage beneficiary categories</p>
                                </div>
                                <a href="{{ route('beneficiary-categories.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-1"></i> Add Category
                                </a>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Name</th>
                                            <th>Beneficiaries</th>
                                            <th>Created</th>
                                            <th style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($categories as $index => $category)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $category->name }}</strong></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ \App\Models\Beneficiary::where('category', $category->name)->count() }}
                                                    </span>
                                                </td>
                                                <td>{{ $category->created_at->format('d M Y') }}</td>
                                                <td>
                                                    <a href="{{ route('beneficiary-categories.edit', $category) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <form action="{{ route('beneficiary-categories.destroy', $category) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
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
                                                <td colspan="5" class="text-center text-muted py-4">No categories found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
