@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-8 col-md-12 mx-auto">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Program Details</h6>
                            <p class="text-muted card-sub-title">Viewing program information</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('programs.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fe fe-list"></i> All Programs
                            </a>
                            <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-warning btn-sm">
                                <i class="fe fe-edit"></i> Edit
                            </a>
                            <form action="{{ route('programs.destroy', $program->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fe fe-trash"></i> Delete
                                </button>
                            </form>
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

                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Program Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="font-weight-bold text-muted small">Program ID</label>
                                    <div>{{ $program->id }}</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="font-weight-bold text-muted small">Program Name</label>
                                    <div><strong class="text-primary">{{ $program->name }}</strong></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted small">Format</label>
                                    <div>{{ $program->format }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted small">Has Dependant</label>
                                    <div>
                                        @if($program->has_dependant)
                                            <span class="badge bg-success">
                                                <i class="fe fe-check"></i> Yes
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fe fe-x"></i> No
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted small">Status</label>
                                    <div>
                                        @if($program->status)
                                            <span class="badge bg-success">
                                                <i class="fe fe-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fe fe-x-circle"></i> Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted small">Created Date</label>
                                    <div>{{ $program->created_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="font-weight-bold text-muted small">Last Updated</label>
                                    <div>{{ $program->updated_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
                            <i class="fe fe-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-primary">
                            <i class="fe fe-edit"></i> Edit Program
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
