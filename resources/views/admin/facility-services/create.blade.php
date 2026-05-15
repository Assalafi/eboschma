@extends('layouts.app')

@section('title', 'Assign Services to Facility')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Assign Services to Facility</h4>
                                    <p class="text-muted mb-0">Select a facility and choose services to assign</p>
                                </div>
                                <div>
                                    <a href="{{ route('facility-services.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('facility-services.store') }}" id="servicesForm">
                                @csrf

                                <div class="alert alert-primary">
                                    <i class="fe fe-info me-2"></i>
                                    Select one or more facilities to assign services to.
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Select Facilities <span class="text-danger">*</span></label>
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                                id="selectAllFacilities">Select All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="deselectAllFacilities">Deselect All</button>
                                        </div>
                                    </div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach ($facilities as $facility)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input facility-checkbox" type="checkbox"
                                                            name="facility_ids[]" value="{{ $facility->id }}"
                                                            id="facility_{{ $facility->id }}">
                                                        <label class="form-check-label" for="facility_{{ $facility->id }}">
                                                            {{ $facility->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <small class="text-muted"><span id="selectedFacilitiesCount">0</span> facilities
                                        selected</small>
                                </div>

                                <div id="servicesSection" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fe fe-info me-2"></i>
                                        Select services to assign. Already assigned services are disabled.
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <input type="text" id="searchServices" class="form-control"
                                                placeholder="Search services...">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" id="selectAll"
                                                class="btn btn-outline-primary btn-sm">Select All Visible</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" id="deselectAll"
                                                class="btn btn-outline-secondary btn-sm">Deselect All</button>
                                        </div>
                                    </div>

                                    <div class="accordion" id="categoriesAccordion">
                                        @foreach ($categories as $category)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading{{ $category->id }}">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse{{ $category->id }}" aria-expanded="false"
                                                        aria-controls="collapse{{ $category->id }}">
                                                        <strong>{{ $category->name }}</strong>
                                                        <span class="badge bg-primary ms-2">
                                                            {{ $category->serviceTypes->sum(fn($t) => $t->serviceItems->count()) }}
                                                            services
                                                        </span>
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ $category->id }}" class="accordion-collapse collapse"
                                                    aria-labelledby="heading{{ $category->id }}"
                                                    data-bs-parent="#categoriesAccordion">
                                                    <div class="accordion-body">
                                                        @foreach ($category->serviceTypes as $type)
                                                            @if ($type->serviceItems->count() > 0)
                                                                <div class="mb-3">
                                                                    <h6 class="text-muted border-bottom pb-2">
                                                                        {{ $type->name }}</h6>
                                                                    <div class="row">
                                                                        @foreach ($type->serviceItems as $item)
                                                                            <div class="col-md-6 col-lg-4 mb-2 service-item"
                                                                                data-name="{{ strtolower($item->name) }}"
                                                                                data-id="{{ $item->id }}">
                                                                                <div class="form-check">
                                                                                    <input
                                                                                        class="form-check-input service-checkbox"
                                                                                        type="checkbox"
                                                                                        name="service_items[]"
                                                                                        value="{{ $item->id }}"
                                                                                        id="service_{{ $item->id }}">
                                                                                    <label class="form-check-label"
                                                                                        for="service_{{ $item->id }}">
                                                                                        {{ $item->name }}
                                                                                        @if ($item->price)
                                                                                            <small
                                                                                                class="text-success">(₦{{ number_format($item->price, 2) }})</small>
                                                                                        @endif
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span id="selectedCount" class="text-muted">0 services selected</span>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                            <i class="fe fe-save me-1"></i> Assign Selected Services
                                        </button>
                                    </div>
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
            var assignedServicesByFacility = {};

            function updateSelectedCount() {
                const count = $('.service-checkbox:checked:not(:disabled)').length;
                $('#selectedCount').text(count + ' services selected');
                $('#submitBtn').prop('disabled', count === 0);
            }

            function updateFacilityCount() {
                const count = $('.facility-checkbox:checked').length;
                $('#selectedFacilitiesCount').text(count);

                if (count > 0) {
                    loadAssignedServicesForFacilities();
                    $('#servicesSection').show();
                } else {
                    $('#servicesSection').hide();
                }
            }

            function loadAssignedServicesForFacilities() {
                const selectedFacilities = $('.facility-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedFacilities.length === 0) return;

                // Load assigned services for all selected facilities
                const promises = selectedFacilities.map(function(facilityId) {
                    return $.get('{{ route('facility-services.assigned') }}', {
                        facility_id: facilityId
                    });
                });

                Promise.all(promises).then(function(results) {
                    assignedServicesByFacility = {};
                    selectedFacilities.forEach(function(facilityId, index) {
                        assignedServicesByFacility[facilityId] = results[index];
                    });
                    updateAssignedStatus();
                });
            }

            function updateAssignedStatus() {
                // Get all assigned service IDs across all selected facilities
                const allAssignedServices = new Set();
                Object.values(assignedServicesByFacility).forEach(function(services) {
                    services.forEach(function(serviceId) {
                        allAssignedServices.add(serviceId);
                    });
                });

                $('.service-item').each(function() {
                    const itemId = $(this).data('id');
                    const checkbox = $(this).find('.service-checkbox');
                    const label = $(this).find('.form-check-label');

                    // Check if this service is already assigned to ANY of the selected facilities
                    if (allAssignedServices.has(itemId)) {
                        checkbox.prop('disabled', true).prop('checked', true);
                        label.addClass('text-muted');
                        if (!label.find('.badge').length) {
                            label.append(' <span class="badge bg-secondary">Already Assigned</span>');
                        }
                    } else {
                        checkbox.prop('disabled', false).prop('checked', false);
                        label.removeClass('text-muted');
                        label.find('.badge').remove();
                    }
                });
                updateSelectedCount();
            }

            $('.facility-checkbox').change(function() {
                updateFacilityCount();
            });

            $('#selectAllFacilities').click(function() {
                $('.facility-checkbox').prop('checked', true);
                updateFacilityCount();
            });

            $('#deselectAllFacilities').click(function() {
                $('.facility-checkbox').prop('checked', false);
                updateFacilityCount();
            });

            $('.service-checkbox').change(function() {
                updateSelectedCount();
            });

            $('#searchServices').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.service-item').each(function() {
                    const name = $(this).data('name');
                    $(this).toggle(name.includes(searchTerm));
                });
            });

            $('#selectAll').click(function() {
                $('.service-item:visible .service-checkbox:not(:disabled)').prop('checked', true);
                updateSelectedCount();
            });

            $('#deselectAll').click(function() {
                $('.service-checkbox:not(:disabled)').prop('checked', false);
                updateSelectedCount();
            });

            updateFacilityCount();
        });
    </script>
@endpush
