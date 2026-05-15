@extends('layouts.app')

@section('title', 'Edit Bed')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Edit Bed</h4>
                                    <p class="text-muted mb-0">Update bed details</p>
                                </div>
                                <div>
                                    <a href="{{ route('beds.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Beds
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('beds.update', $bed->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Bed Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $bed->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="room_id" class="form-label">Room <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('room_id') is-invalid @enderror" id="room_id"
                                            name="room_id" required>
                                            <option value="">Select Room</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}"
                                                    {{ old('room_id', $bed->room_id) == $room->id ? 'selected' : '' }}>
                                                    {{ $room->name }} ({{ $room->ward->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('room_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_occupied"
                                                id="is_occupied" value="1"
                                                {{ old('is_occupied', $bed->is_occupied) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_occupied">Occupied</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                                value="1" {{ old('is_active', $bed->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> Update Bed
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
