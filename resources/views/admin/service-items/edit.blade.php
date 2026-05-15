@extends('layouts.app')

@section('title', 'Edit Service Item')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Edit Service Item</h4>
                                <p class="text-muted mb-0">Update service item information and pricing</p>
                            </div>
                            <div>
                                <a href="{{ route('service-items.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-arrow-left me-1"></i> Back to Service Items
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('service-items.update', $item->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="service_category_id" class="form-label fw-semibold">Service Category
                                            <span class="text-danger">*</span></label>
                                        <select class="form-select @error('service_category_id') is-invalid @enderror"
                                            id="service_category_id" name="service_category_id" required>
                                            <option value="">Select a category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('service_category_id', $item->serviceType && $item->serviceType->serviceCategory ? $item->serviceType->service_category_id : '') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('service_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="service_type_id" class="form-label fw-semibold">Service Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('service_type_id') is-invalid @enderror"
                                            id="service_type_id" name="service_type_id" required>
                                            <option value="">Select a service type</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ old('service_type_id', $item->serviceType ? $item->serviceType->id : '') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('service_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-semibold">Item Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $item->name) }}"
                                            placeholder="Enter service item name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Examples: CBC Test, Chest X-Ray, Doctor Consultation,
                                            Amoxicillin 500mg</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="type" class="form-label fw-semibold">Item Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type"
                                            name="type" required>
                                            <option value="">Select item type</option>
                                            @foreach ($serviceTypes as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('type', $item->type) == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="price" class="form-label fw-semibold">Price (₦) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                            id="price" name="price" value="{{ old('price', $item->price) }}"
                                            placeholder="0.00" step="0.01" min="0" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter the price in Nigerian Naira</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                            rows="3" placeholder="Enter item description (optional)">{{ old('description', $item->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Provide additional details about this service item</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('service-items.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Update Service Item
                                </button>
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
        $(document).ready(function() {
            // Initialize service types on page load
            var currentCategoryId =
                '{{ $item->serviceType && $item->serviceType->serviceCategory ? $item->serviceType->service_category_id : '' }}';
            if (currentCategoryId) {
                $('#service_category_id').val(currentCategoryId);
                // Trigger the change event to load service types
                $('#service_category_id').trigger('change');
            }

            // Load service types when category changes
            $('#service_category_id').change(function() {
                var categoryId = $(this).val();
                var serviceTypeSelect = $('#service_type_id');
                var currentTypeId = '{{ $item->serviceType ? $item->serviceType->id : '' }}';

                if (categoryId) {
                    $.get('{{ route('service-types.by-category') }}', {
                            category_id: categoryId
                        })
                        .done(function(data) {
                            serviceTypeSelect.empty().append(
                                '<option value="">Select a service type</option>');
                            $.each(data, function(key, value) {
                                var selected = value.id == currentTypeId ? 'selected' : '';
                                serviceTypeSelect.append('<option value="' + value.id + '" ' +
                                    selected + '>' + value.name + '</option>');
                            });
                        })
                        .fail(function() {
                            serviceTypeSelect.empty().append(
                                '<option value="">Error loading service types</option>');
                        });
                } else {
                    serviceTypeSelect.empty().append('<option value="">Select a service type</option>');
                }
            });
        });
    </script>
@endpush
