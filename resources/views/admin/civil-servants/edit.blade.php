@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Edit Civil Servant: {{ $civilServant->fullname }}</h6>
                            <p class="text-muted card-sub-title">Update the civil servant details below</p>
                        </div>
                        <div>
                            <a href="{{ route('civil-servants.index') }}" class="btn btn-light">
                                <i class="fe fe-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    
                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif
                            <form action="{{ route('civil-servants.update', $civilServant) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">DP Number <span class="text-danger">*</span></label>
                                            <input type="text" name="dp_no" class="form-control @error('dp_no') is-invalid @enderror" 
                                                   value="{{ old('dp_no', $civilServant->dp_no) }}" placeholder="Enter DP number" required>
                                            @error('dp_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror" 
                                                   value="{{ old('fullname', $civilServant->fullname) }}" placeholder="Enter full name" required>
                                            @error('fullname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">NIN (Optional)</label>
                                            <input type="text" name="nin" class="form-control @error('nin') is-invalid @enderror" 
                                                   value="{{ old('nin', $civilServant->nin) }}" placeholder="Enter NIN" maxlength="11">
                                            @error('nin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">BVN (Optional)</label>
                                            <input type="text" name="bvn" class="form-control @error('bvn') is-invalid @enderror" 
                                                   value="{{ old('bvn', $civilServant->bvn) }}" placeholder="Enter BVN" maxlength="11">
                                            @error('bvn')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                                                   value="{{ old('dob', $civilServant->dob->format('Y-m-d')) }}" required>
                                            @error('dob')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male" {{ old('gender', $civilServant->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender', $civilServant->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                            @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">State (Optional)</label>
                                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" 
                                                   value="{{ old('state', $civilServant->state) }}" placeholder="Enter state">
                                            @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">LGA (Optional)</label>
                                            <input type="text" name="lga" class="form-control @error('lga') is-invalid @enderror" 
                                                   value="{{ old('lga', $civilServant->lga) }}" placeholder="Enter LGA">
                                            @error('lga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Ministry/Department/Agency (MDA) <span class="text-danger">*</span></label>
                                            <input type="text" name="mda" class="form-control @error('mda') is-invalid @enderror" 
                                                   value="{{ old('mda', $civilServant->mda) }}" placeholder="Enter MDA" required>
                                            @error('mda')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group d-flex gap-2 justify-content-end">
                                    <a href="{{ route('civil-servants.index') }}" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Update Civil Servant
                                    </button>
                                </div>
                            </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
