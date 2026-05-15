@extends('layouts.app')

@section('title', 'Create Beds')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Create Beds</h4>
                                    <p class="text-muted mb-0">Add multiple beds to a room</p>
                                </div>
                                <div>
                                    <a href="{{ route('beds.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Beds
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('beds.store') }}" id="bedForm">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label for="facility_id" class="form-label">Facility <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="facility_id" required>
                                            <option value="">Select Facility</option>
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ward_id" class="form-label">Ward <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="ward_id" required>
                                            <option value="">Select Ward</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="room_id" class="form-label">Room <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('room_id') is-invalid @enderror" id="room_id"
                                            name="room_id" required>
                                            <option value="">Select Room</option>
                                        </select>
                                        @error('room_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Select facility, ward, and room first, then add beds below. You can add multiple beds at
                                    once.
                                </div>

                                <div id="bedsContainer">
                                    <div class="bed-row row mb-3" data-index="0">
                                        <div class="col-md-6">
                                            <label class="form-label">Bed Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="beds[0][name]"
                                                placeholder="e.g., Bed 1, Bed A" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="beds[0][is_active]"
                                                    id="bed_active_0" value="1" checked>
                                                <label class="form-check-label" for="bed_active_0">Active</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-bed"
                                                style="display: none;">
                                                <i class="fe fe-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <button type="button" id="addBed" class="btn btn-outline-primary">
                                        <i class="fe fe-plus me-1"></i> Add Another Bed
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Save Beds
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let bedIndex = 1;

            // Load wards when facility changes
            $('#facility_id').change(function() {
                var facilityId = $(this).val();
                var wardSelect = $('#ward_id');
                var roomSelect = $('#room_id');

                roomSelect.empty().append('<option value="">Select Room</option>');

                if (facilityId) {
                    $.get('{{ route('wards.by-facility') }}', {
                            facility_id: facilityId
                        })
                        .done(function(data) {
                            wardSelect.empty().append('<option value="">Select Ward</option>');
                            $.each(data, function(key, value) {
                                wardSelect.append('<option value="' + value.id + '">' + value
                                    .name + '</option>');
                            });
                        });
                } else {
                    wardSelect.empty().append('<option value="">Select Ward</option>');
                }
            });

            // Load rooms when ward changes
            $('#ward_id').change(function() {
                var wardId = $(this).val();
                var roomSelect = $('#room_id');

                if (wardId) {
                    $.get('{{ route('rooms.by-ward') }}', {
                            ward_id: wardId
                        })
                        .done(function(data) {
                            roomSelect.empty().append('<option value="">Select Room</option>');
                            $.each(data, function(key, value) {
                                roomSelect.append('<option value="' + value.id + '">' + value
                                    .name + '</option>');
                            });
                        });
                } else {
                    roomSelect.empty().append('<option value="">Select Room</option>');
                }
            });

            $('#addBed').click(function() {
                const newRow = `
                    <div class="bed-row row mb-3" data-index="${bedIndex}">
                        <div class="col-md-6">
                            <label class="form-label">Bed Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="beds[${bedIndex}][name]" placeholder="e.g., Bed 1, Bed A" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="beds[${bedIndex}][is_active]" 
                                       id="bed_active_${bedIndex}" value="1" checked>
                                <label class="form-check-label" for="bed_active_${bedIndex}">Active</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-bed">
                                <i class="fe fe-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                $('#bedsContainer').append(newRow);
                bedIndex++;
                updateRemoveButtons();
            });

            $(document).on('click', '.remove-bed', function() {
                $(this).closest('.bed-row').remove();
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                const rows = $('.bed-row');
                if (rows.length > 1) {
                    $('.remove-bed').show();
                } else {
                    $('.remove-bed').hide();
                }
            }
        });
    </script>
@endpush
