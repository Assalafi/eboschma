@extends('layouts.app')

@section('title', 'Bulk Create Staff Positions')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Bulk Create Staff Positions</h4>
                                <p class="text-muted mb-0">Add multiple facility staff positions at once</p>
                            </div>
                            <div>
                                <a href="{{ route('staff-positions.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back
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

                        <form action="{{ route('staff-positions.bulk.store') }}" method="POST" id="bulkPositionForm">
                            @csrf

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Staff Positions</label>
                                    <button type="button" class="btn btn-sm btn-primary" id="addPositionBtn">
                                        <i class="fe fe-plus me-1"></i> Add Position
                                    </button>
                                </div>
                            </div>

                            <div id="positionsContainer">
                                <!-- Position rows will be added here -->
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fe fe-save me-1"></i> Create All Positions
                                        </button>
                                        <a href="{{ route('staff-positions.index') }}" class="btn btn-outline-secondary">
                                            <i class="fe fe-x me-1"></i> Cancel
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
@endsection

@push('scripts')
    <script>
        let positionCount = 0;

        function addPositionRow() {
            positionCount++;
            const html = `
            <div class="card mb-3 position-row" id="position-${positionCount}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Position #${positionCount}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePosition(${positionCount})">
                            <i class="fe fe-trash"></i> Remove
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="positions[${positionCount}][name]" 
                                   placeholder="e.g., Nurse, Doctor, Pharmacist"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="positions[${positionCount}][description]" 
                                   placeholder="Brief description (optional)">
                        </div>
                    </div>
                </div>
            </div>
        `;

            $('#positionsContainer').append(html);
        }

        function removePosition(id) {
            $(`#position-${id}`).remove();

            // If no positions left, add one
            if ($('.position-row').length === 0) {
                addPositionRow();
            }
        }

        $(document).ready(function() {
            // Add initial position row
            addPositionRow();

            // Add position button
            $('#addPositionBtn').click(function() {
                addPositionRow();
            });

            // Form validation
            $('#bulkPositionForm').submit(function(e) {
                const positions = $('.position-row').length;
                if (positions === 0) {
                    e.preventDefault();
                    alert('Please add at least one position.');
                    return false;
                }
            });
        });
    </script>
@endpush
