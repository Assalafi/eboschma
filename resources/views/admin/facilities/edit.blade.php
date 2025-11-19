@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Edit Facility</h6>
                            <p class="text-muted card-sub-title">Update facility information</p>
                        </div>
                        <div>
                            <a href="{{ route('facilities.show', $facility) }}" class="btn btn-info me-2">
                                <i class="fe fe-eye"></i> View Details
                            </a>
                            <a href="{{ route('facilities.index') }}" class="btn btn-secondary">
                                <i class="fe fe-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('facilities.update', $facility) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Facility Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $facility->name) }}" 
                                       placeholder="Enter facility name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Facility Type</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                    <option value="">Select facility type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" {{ old('type', $facility->type) == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lga" class="form-label">Local Government Area <span class="text-danger">*</span></label>
                                <select class="form-select @error('lga') is-invalid @enderror" id="lga" name="lga" required>
                                    <option value="">Select LGA</option>
                                    @foreach ($lgas as $lga)
                                        <option value="{{ $lga }}" {{ old('lga', $facility->lga) == $lga ? 'selected' : '' }}>
                                            {{ $lga }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="ward" class="form-label">Ward <span class="text-danger">*</span></label>
                                <select class="form-select @error('ward') is-invalid @enderror" id="ward" name="ward" required>
                                    <option value="">Select ward (choose LGA first)</option>
                                </select>
                                @error('ward')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save"></i> Update Facility
                                    </button>
                                    <a href="{{ route('facilities.show', $facility) }}" class="btn btn-secondary">
                                        <i class="fe fe-x"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-capitalize facility name
    document.getElementById('name').addEventListener('input', function() {
        this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
    });
    
    // Wards data from backend
    const wardsData = @json($wards);
    
    // Populate wards based on selected LGA
    document.getElementById('lga').addEventListener('change', function() {
        const selectedLga = this.value;
        const wardSelect = document.getElementById('ward');
        
        // Clear existing options
        wardSelect.innerHTML = '<option value="">Select ward</option>';
        
        // Populate wards for selected LGA
        if (selectedLga && wardsData[selectedLga]) {
            wardsData[selectedLga].forEach(function(ward) {
                const option = document.createElement('option');
                option.value = ward;
                option.textContent = ward;
                wardSelect.appendChild(option);
            });
        }
    });
    
    // Initialize wards for existing facility on page load
    document.addEventListener('DOMContentLoaded', function() {
        const currentLga = "{{ old('lga', $facility->lga) }}";
        const currentWard = "{{ old('ward', $facility->ward) }}";
        const wardSelect = document.getElementById('ward');
        
        if (currentLga && wardsData[currentLga]) {
            wardSelect.innerHTML = '<option value="">Select ward</option>';
            wardsData[currentLga].forEach(function(ward) {
                const option = document.createElement('option');
                option.value = ward;
                option.textContent = ward;
                if (ward === currentWard) {
                    option.selected = true;
                }
                wardSelect.appendChild(option);
            });
        }
    });
</script>
@endsection
