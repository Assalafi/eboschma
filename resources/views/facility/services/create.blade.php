@extends('layouts.facility')

@section('title', 'Add Services')

@section('content')
    <div class="main-container container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title mb-1">Add Services to Facility</h4>
                                    <p class="text-muted mb-0">Select services to offer at your facility</p>
                                </div>
                                <div>
                                    <a href="{{ route('facility.services.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Back to Services
                                    </a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('facility.services.store') }}" id="servicesForm">
                                @csrf

                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Select the services you want to offer. Already assigned services are disabled.
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <input type="text" id="searchServices" class="form-control"
                                            placeholder="Search services...">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" id="selectAll" class="btn btn-outline-primary btn-sm">Select
                                            All Visible</button>
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
                                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}"
                                                    aria-expanded="false" aria-controls="collapse{{ $category->id }}">
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
                                                                        @php
                                                                            $isAssigned = in_array(
                                                                                $item->id,
                                                                                $assignedServiceIds,
                                                                            );
                                                                        @endphp
                                                                        <div class="col-md-6 col-lg-4 mb-2 service-item"
                                                                            data-name="{{ strtolower($item->name) }}">
                                                                            <div class="form-check">
                                                                                <input
                                                                                    class="form-check-input service-checkbox"
                                                                                    type="checkbox" name="service_items[]"
                                                                                    value="{{ $item->id }}"
                                                                                    id="service_{{ $item->id }}"
                                                                                    {{ $isAssigned ? 'disabled checked' : '' }}>
                                                                                <label
                                                                                    class="form-check-label {{ $isAssigned ? 'text-muted' : '' }}"
                                                                                    for="service_{{ $item->id }}">
                                                                                    {{ $item->name }}
                                                                                    @if ($item->price)
                                                                                        <small
                                                                                            class="text-success">(₦{{ number_format($item->price, 2) }})</small>
                                                                                    @endif
                                                                                    @if ($isAssigned)
                                                                                        <span
                                                                                            class="badge bg-secondary">Already
                                                                                            Added</span>
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
                                        <i class="fe fe-save me-1"></i> Add Selected Services
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
            function updateSelectedCount() {
                const count = $('.service-checkbox:checked:not(:disabled)').length;
                $('#selectedCount').text(count + ' services selected');
                $('#submitBtn').prop('disabled', count === 0);
            }

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

            updateSelectedCount();
        });
    </script>
@endpush
