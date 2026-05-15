@extends('layouts.app')

@section('title', 'Create Rooms')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Create Rooms</h4>
                                    <p class="text-muted mb-0">Add multiple rooms to a ward</p>
                                </div>
                                <div>
                                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Rooms
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('rooms.store') }}" id="roomForm">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="facility_id" class="form-label">Facility <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="facility_id" required>
                                            <option value="">Select Facility</option>
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ward_id" class="form-label">Ward <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ward_id') is-invalid @enderror" id="ward_id"
                                            name="ward_id" required>
                                            <option value="">Select Ward</option>
                                        </select>
                                        @error('ward_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Select a facility and ward first, then add rooms below. You can add multiple rooms at
                                    once.
                                </div>

                                <div id="roomsContainer">
                                    <div class="room-row row mb-3" data-index="0">
                                        <div class="col-md-6">
                                            <label class="form-label">Room Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="rooms[0][name]"
                                                placeholder="Enter room name" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="rooms[0][is_active]"
                                                    id="room_active_0" value="1" checked>
                                                <label class="form-check-label" for="room_active_0">Active</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-room"
                                                style="display: none;">
                                                <i class="fe fe-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <button type="button" id="addRoom" class="btn btn-outline-primary">
                                        <i class="fe fe-plus me-1"></i> Add Another Room
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Save Rooms
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
            let roomIndex = 1;

            // Load wards when facility changes
            $('#facility_id').change(function() {
                var facilityId = $(this).val();
                var wardSelect = $('#ward_id');

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

            $('#addRoom').click(function() {
                const newRow = `
                    <div class="room-row row mb-3" data-index="${roomIndex}">
                        <div class="col-md-6">
                            <label class="form-label">Room Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="rooms[${roomIndex}][name]" placeholder="Enter room name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="rooms[${roomIndex}][is_active]" 
                                       id="room_active_${roomIndex}" value="1" checked>
                                <label class="form-check-label" for="room_active_${roomIndex}">Active</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-room">
                                <i class="fe fe-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                $('#roomsContainer').append(newRow);
                roomIndex++;
                updateRemoveButtons();
            });

            $(document).on('click', '.remove-room', function() {
                $(this).closest('.room-row').remove();
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                const rows = $('.room-row');
                if (rows.length > 1) {
                    $('.remove-room').show();
                } else {
                    $('.remove-room').hide();
                }
            }
        });
    </script>
@endpush
