@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">{{ $civilServant->fullname }}</h6>
                            <p class="text-muted card-sub-title">DP No: <strong>{{ $civilServant->dp_no }}</strong></p>
                        </div>
                        <div>
                            <a href="{{ route('civil-servants.edit', $civilServant) }}" class="btn btn-primary me-2">
                                <i class="fe fe-edit"></i> Edit
                            </a>
                            <a href="{{ route('civil-servants.index') }}" class="btn btn-light">
                                <i class="fe fe-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-semibold" width="40%">DP Number:</td>
                                                    <td>{{ $civilServant->dp_no }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Full Name:</td>
                                                    <td>{{ $civilServant->fullname }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Gender:</td>
                                                    <td>
                                                        <span class="badge badge-{{ $civilServant->gender == 'Male' ? 'info' : 'pink' }}">
                                                            {{ $civilServant->gender }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Date of Birth:</td>
                                                    <td>{{ $civilServant->dob->format('F d, Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Age:</td>
                                                    <td>{{ $civilServant->dob->age }} years</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-semibold" width="40%">NIN:</td>
                                                    <td>{{ $civilServant->nin ?: 'Not provided' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">BVN:</td>
                                                    <td>{{ $civilServant->bvn ?: 'Not provided' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">State:</td>
                                                    <td>{{ $civilServant->state ?: 'Not specified' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">LGA:</td>
                                                    <td>{{ $civilServant->lga ?: 'Not specified' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">MDA:</td>
                                                    <td>{{ $civilServant->mda }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Record Information</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-semibold" width="20%">Created Date:</td>
                                                    <td>{{ $civilServant->created_at->format('F d, Y \a\t h:i A') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Last Updated:</td>
                                                    <td>{{ $civilServant->updated_at->format('F d, Y \a\t h:i A') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Record Age:</td>
                                                    <td>{{ $civilServant->created_at->diffForHumans() }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('civil-servants.edit', $civilServant) }}" class="btn btn-primary">
                                    <i class="fe fe-edit me-1"></i> Edit Civil Servant
                                </a>
                                <button type="button" class="btn btn-danger" 
                                    data-toggle="modal" 
                                    data-target="#delete-civil-servant-{{ $civilServant->id }}">
                                    <i class="fe fe-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delete Modal -->
                    <div class="modal fade" id="delete-civil-servant-{{ $civilServant->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Confirmation</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete civil servant <strong>{{ $civilServant->dp_no }}</strong>: {{ $civilServant->fullname }}?</p>
                                    <p class="text-danger">This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <form action="{{ route('civil-servants.destroy', $civilServant->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
