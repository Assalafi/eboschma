@extends('layouts.app')

@section('title', 'Create New Claim')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('claims.index') }}" class="btn btn-sm btn-ghost-secondary me-2">
                            <i class="ti ti-arrow-left"></i>
                        </a>
                        <div>
                            <h2 class="page-title mb-0">Create New Claim</h2>
                            <div class="text-muted small">Submit claim for beneficiary reimbursement</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form id="claimForm" method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Beneficiary Selection Card -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="ti ti-user me-2"></i>Select Beneficiary</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold required">Search Patient
                                    (Beneficiary/Spouse/Child)</label>
                                <select class="form-select" name="patient_id" id="beneficiary_select" required
                                    style="width: 100%;">
                                    <option value="">Type to search by name, BOSCHMA ID, or NIN...</option>
                                </select>
                                <small class="text-muted">Search for any enrollee (beneficiary, spouse, or child) by name,
                                    BOSCHMA ID, or NIN (minimum 2 characters)</small>
                            </div>
                        </div>

                        <!-- Selected Patient Info Display -->
                        <div id="beneficiaryInfoDisplay" class="mt-3" style="display: none;">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <strong>Selected Patient Information</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Patient Type:</small>
                                            <div class="fw-semibold">
                                                <span class="badge" id="beneficiary_type_badge">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Full Name:</small>
                                            <div class="fw-semibold" id="beneficiary_fullname">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">BOSCHMA ID:</small>
                                            <div class="fw-semibold" id="beneficiary_boschma_no">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">File Number:</small>
                                            <div class="fw-semibold" id="beneficiary_file_no">-</div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-3">
                                            <small class="text-muted">NIN:</small>
                                            <div class="fw-semibold" id="beneficiary_nin">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Gender:</small>
                                            <div class="fw-semibold" id="beneficiary_gender">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Date of Birth:</small>
                                            <div class="fw-semibold" id="beneficiary_dob">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Phone:</small>
                                            <div class="fw-semibold" id="beneficiary_phone">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facility Selection Card -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0"><i class="ti ti-building me-2"></i>Healthcare Facility</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold required">Select Facility</label>
                                <select class="form-select" name="facility_id" id="facility_select" required>
                                    <option value="">Choose healthcare facility...</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}" data-type="{{ $facility->type }}"
                                            data-lga="{{ $facility->lga }}" data-ward="{{ $facility->ward }}">
                                            {{ $facility->name }} ({{ $facility->type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Selected Facility Info -->
                        <div id="facilityInfoDisplay" class="mt-3" style="display: none;">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Facility Type:</small>
                                            <div class="fw-semibold" id="facility_type">-</div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">LGA:</small>
                                            <div class="fw-semibold" id="facility_lga">-</div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Ward:</small>
                                            <div class="fw-semibold" id="facility_ward">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Claim Details Card -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="ti ti-file-text me-2"></i>Claim Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold required">Authorization Code</label>
                                <input type="text" class="form-control" name="authorization_code" required
                                    placeholder="Enter authorization code">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold required">Service Date</label>
                                <input type="date" class="form-control" name="service_date" required
                                    max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold required">Diagnosis</label>
                                <textarea class="form-control" name="diagnosis" rows="2" required
                                    placeholder="Enter diagnosis or medical condition"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medications Section -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Medications</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="addMedication()">
                            <i class="ti ti-plus me-1"></i>Add Medication
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="medicationsTable">
                                <thead>
                                    <tr>
                                        <th>Drug Name</th>
                                        <th>Dosage/Strength</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Days</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="medicationsBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No medications added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Laboratory Tests Section -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Laboratory Tests</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="addLaboratory()">
                            <i class="ti ti-plus me-1"></i>Add Test
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="laboratoryTable">
                                <thead>
                                    <tr>
                                        <th>Test Name</th>
                                        <th>Sample Type</th>
                                        <th>Price</th>
                                        <th>Frequency</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="laboratoryBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No laboratory tests added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Rendered Services Section -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Rendered Services</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="addService()">
                            <i class="ti ti-plus me-1"></i>Add Service
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Frequency</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="servicesBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No services added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Grand Total Card -->
                <div class="card mb-3 shadow-sm border-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Grand Total</h3>
                            </div>
                            <div class="col-auto">
                                <h2 class="mb-0 text-primary" id="grandTotal">₦0.00</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supporting Documents -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title mb-0"><i class="ti ti-file-upload me-2"></i>Supporting Documents</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Operation Sheet(s)</label>
                                <input type="file" class="form-control" name="operation_sheets[]" multiple
                                    accept="image/*">
                                <small class="text-muted">Upload operation sheet images (JPG, PNG, GIF)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Prescription Sheet(s)</label>
                                <input type="file" class="form-control" name="prescription_sheets[]" multiple
                                    accept="image/*">
                                <small class="text-muted">Upload prescription sheet images (JPG, PNG, GIF)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Other Documents</label>
                                <input type="file" class="form-control" name="other_documents[]" multiple
                                    accept="image/*,.pdf">
                                <small class="text-muted">Upload receipts, invoices, etc.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ti ti-check me-1"></i>Submit Claim
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Add Medication Modal -->
    <div class="modal modal-blur fade" id="medicationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Select Drug</label>
                        <select class="form-select" id="drug_select_create" style="width: 100%;">
                            <option value="">Search and select drug...</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dosage Form</label>
                            <input type="text" class="form-control" id="med_dosage" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Strength</label>
                            <input type="text" class="form-control" id="med_strength" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Unit Price</label>
                            <input type="number" class="form-control" id="med_cost" step="0.01" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Quantity</label>
                            <input type="number" class="form-control" id="med_quantity" min="1" value="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Days</label>
                        <input type="number" class="form-control" id="med_days" min="1" value="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" id="med_total" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveMedication()">Add</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Laboratory Modal -->
    <div class="modal modal-blur fade" id="laboratoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Laboratory Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Select Test</label>
                        <select class="form-select" id="lab_select_create" style="width: 100%;">
                            <option value="">Search and select test...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sample Type</label>
                        <input type="text" class="form-control" id="lab_sample" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Price</label>
                            <input type="number" class="form-control" id="lab_price" step="0.01" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Frequency</label>
                            <input type="number" class="form-control" id="lab_frequency" min="1"
                                value="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" id="lab_total" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveLaboratory()">Add</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal modal-blur fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Rendered Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Select Service</label>
                        <select class="form-select" id="service_select_create" style="width: 100%;">
                            <option value="">Search and select service...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Service Type</label>
                        <input type="text" class="form-control" id="service_type_input" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Price</label>
                            <input type="number" class="form-control" id="service_price" step="0.01" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Frequency</label>
                            <input type="number" class="form-control" id="service_frequency" min="1"
                                value="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" id="service_total" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveService()">Add</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let medications = [];
        let laboratories = [];
        let services = [];
        let currentDrug = null;
        let currentTest = null;
        let currentService = null;

        $(document).ready(function() {
            // Initialize Select2 for Patient Search (All enrollee types)
            $('#beneficiary_select').select2({
                ajax: {
                    url: '/api/patients/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(p => ({
                                id: p.id,
                                text: `${p.fullname} (${p.boschma_no}) - ${p.enrollee_type}`,
                                data: p
                            }))
                        };
                    },
                    cache: true
                },
                placeholder: 'Type to search patient...',
                minimumInputLength: 2
            });

            // Show patient info on selection
            $('#beneficiary_select').on('select2:select', function(e) {
                const data = e.params.data.data;

                console.log('👤 Patient selected via Select2:');
                console.log('  - Selected data:', data);
                console.log('  - Patient ID:', data.id);
                console.log('  - Patient Name:', data.fullname);
                console.log('  - Enrollee Type:', data.enrollee_type);

                // Explicitly set the hidden input value
                $('#beneficiary_select').val(data.id).trigger('change');

                console.log('✅ Set beneficiary_select value to:', data.id);
                console.log('🔍 Current beneficiary_select value:', $('#beneficiary_select').val());

                // Set patient type badge with color
                const typeColors = {
                    'Beneficiary': 'bg-primary',
                    'Spouse': 'bg-success',
                    'Child': 'bg-info'
                };
                const badgeClass = typeColors[data.enrollee_type] || 'bg-secondary';
                $('#beneficiary_type_badge').text(data.enrollee_type).removeClass().addClass('badge ' +
                    badgeClass);

                // Set other fields
                $('#beneficiary_fullname').text(data.fullname);
                $('#beneficiary_boschma_no').text(data.boschma_no);
                $('#beneficiary_file_no').text(data.file_number || '-');
                $('#beneficiary_nin').text(data.nin || '-');
                $('#beneficiary_gender').text(data.gender);
                $('#beneficiary_dob').text(data.date_of_birth);
                $('#beneficiary_phone').text(data.phone_no || '-');
                $('#beneficiaryInfoDisplay').show();

                console.log('✅ Patient info display updated and shown');
            });

            // Show facility info on selection
            $('#facility_select').on('change', function() {
                const selected = $(this).find(':selected');
                if (selected.val()) {
                    $('#facility_type').text(selected.data('type'));
                    $('#facility_lga').text(selected.data('lga'));
                    $('#facility_ward').text(selected.data('ward'));
                    $('#facilityInfoDisplay').show();
                } else {
                    $('#facilityInfoDisplay').hide();
                }
            });

            // Initialize Select2 for Drugs
            $('#drug_select_create').select2({
                dropdownParent: $('#medicationModal'),
                ajax: {
                    url: '{{ route('claims.master.drugs') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                placeholder: 'Search drug...',
                minimumInputLength: 2
            });

            $('#drug_select_create').on('select2:select', function(e) {
                currentDrug = e.params.data;
                $('#med_dosage').val(currentDrug.dosage_form);
                $('#med_strength').val(currentDrug.strength);
                $('#med_cost').val(currentDrug.unit_price);
                calculateMedTotal();
            });

            // Initialize Select2 for Laboratory Tests
            $('#lab_select_create').select2({
                dropdownParent: $('#laboratoryModal'),
                ajax: {
                    url: '{{ route('claims.master.laboratory-tests') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                placeholder: 'Search test...',
                minimumInputLength: 2
            });

            $('#lab_select_create').on('select2:select', function(e) {
                currentTest = e.params.data;
                $('#lab_sample').val(currentTest.sample_type);
                $('#lab_price').val(currentTest.price);
                calculateLabTotal();
            });

            // Initialize Select2 for Services
            $('#service_select_create').select2({
                dropdownParent: $('#serviceModal'),
                ajax: {
                    url: '{{ route('claims.master.services') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                placeholder: 'Search service...',
                minimumInputLength: 2
            });

            $('#service_select_create').on('select2:select', function(e) {
                currentService = e.params.data;
                $('#service_type_input').val(currentService.type);
                $('#service_price').val(currentService.price);
                calculateServiceTotal();
            });

            // Auto-calculate totals
            $('#med_quantity, #med_days').on('input', calculateMedTotal);
            $('#lab_frequency').on('input', calculateLabTotal);
            $('#service_frequency').on('input', calculateServiceTotal);

            // Form submission
            $('#claimForm').on('submit', function(e) {
                e.preventDefault();

                console.log('🚀 Claim form submission started');

                const beneficiaryVal = $('#beneficiary_select').val();
                const facilityVal = $('#facility_select').val();

                console.log('📋 Form validation check:');
                console.log('  - Beneficiary value:', beneficiaryVal);
                console.log('  - Facility value:', facilityVal);
                console.log('  - Beneficiary select element:', $('#beneficiary_select')[0]);
                console.log('  - Facility select element:', $('#facility_select')[0]);

                if (!beneficiaryVal) {
                    console.error('❌ Validation failed: No beneficiary selected');
                    alert('Please select a beneficiary');
                    return;
                }

                if (!facilityVal) {
                    console.error('❌ Validation failed: No facility selected');
                    alert('Please select a facility');
                    return;
                }

                console.log('✅ Form validation passed');

                const formData = new FormData(this);
                formData.append('medications', JSON.stringify(medications));
                formData.append('laboratories', JSON.stringify(laboratories));
                formData.append('services', JSON.stringify(services));
                formData.append('claim_amount', calculateTotal());

                console.log('📦 FormData prepared:');
                console.log('  - patient_id:', formData.get('patient_id'));
                console.log('  - facility_id:', formData.get('facility_id'));
                console.log('  - authorization_code:', formData.get('authorization_code'));
                console.log('  - service_date:', formData.get('service_date'));
                console.log('  - diagnosis:', formData.get('diagnosis'));
                console.log('  - claim_amount:', formData.get('claim_amount'));
                console.log('  - medications:', formData.get('medications'));
                console.log('  - laboratories:', formData.get('laboratories'));
                console.log('  - services:', formData.get('services'));

                console.log('🌐 Sending AJAX request to:', $(this).attr('action'));

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('✅ AJAX success:', response);
                        alert('Claim created successfully!');
                        window.location.href = '{{ route('claims.index') }}';
                    },
                    error: function(xhr) {
                        console.error('❌ AJAX error:');
                        console.error('  - Status:', xhr.status);
                        console.error('  - Status Text:', xhr.statusText);
                        console.error('  - Response Text:', xhr.responseText);
                        console.error('  - Response JSON:', xhr.responseJSON);

                        const errorMessage = xhr.responseJSON?.message ||
                            xhr.responseJSON?.error ||
                            'Failed to create claim';
                        alert('Error: ' + errorMessage);
                    }
                });
            });
        });

        function calculateMedTotal() {
            const cost = parseFloat($('#med_cost').val()) || 0;
            const qty = parseInt($('#med_quantity').val()) || 0;
            const days = parseInt($('#med_days').val()) || 0;
            const total = cost * qty * days;
            $('#med_total').val('₦' + total.toFixed(2));
        }

        function calculateLabTotal() {
            const price = parseFloat($('#lab_price').val()) || 0;
            const freq = parseInt($('#lab_frequency').val()) || 0;
            const total = price * freq;
            $('#lab_total').val('₦' + total.toFixed(2));
        }

        function calculateServiceTotal() {
            const price = parseFloat($('#service_price').val()) || 0;
            const freq = parseInt($('#service_frequency').val()) || 0;
            const total = price * freq;
            $('#service_total').val('₦' + total.toFixed(2));
        }

        function saveMedication() {
            if (!currentDrug) {
                alert('Please select a drug');
                return;
            }

            const cost = parseFloat($('#med_cost').val());
            const qty = parseInt($('#med_quantity').val());
            const days = parseInt($('#med_days').val());
            const total = cost * qty * days;

            medications.push({
                drug_id: currentDrug.id,
                name: currentDrug.name,
                dosage: $('#med_dosage').val(),
                strength: $('#med_strength').val(),
                cost: cost,
                quantity: qty,
                days: days,
                total: total
            });

            renderMedications();
            $('#medicationModal').modal('hide');
            resetMedicationForm();
        }

        function saveLaboratory() {
            if (!currentTest) {
                alert('Please select a test');
                return;
            }

            const price = parseFloat($('#lab_price').val());
            const freq = parseInt($('#lab_frequency').val());
            const total = price * freq;

            laboratories.push({
                test_id: currentTest.id,
                name: currentTest.name,
                sample_type: $('#lab_sample').val(),
                price: price,
                frequency: freq,
                total: total
            });

            renderLaboratories();
            $('#laboratoryModal').modal('hide');
            resetLabForm();
        }

        function saveService() {
            if (!currentService) {
                alert('Please select a service');
                return;
            }

            const price = parseFloat($('#service_price').val());
            const freq = parseInt($('#service_frequency').val());
            const total = price * freq;

            services.push({
                service_id: currentService.id,
                name: currentService.name,
                type: $('#service_type_input').val(),
                price: price,
                frequency: freq,
                total: total
            });

            renderServices();
            $('#serviceModal').modal('hide');
            resetServiceForm();
        }

        function renderMedications() {
            const tbody = $('#medicationsBody');
            if (medications.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center text-muted">No medications added</td></tr>');
            } else {
                tbody.html(medications.map((m, i) => `
            <tr>
                <td>${m.name}</td>
                <td>${m.dosage} ${m.strength}</td>
                <td>₦${m.cost.toFixed(2)}</td>
                <td>${m.quantity}</td>
                <td>${m.days}</td>
                <td class="fw-semibold">₦${m.total.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeMedication(${i})"><i class="ti ti-trash"></i></button></td>
            </tr>
        `).join(''));
            }
            calculateGrandTotal();
        }

        function renderLaboratories() {
            const tbody = $('#laboratoryBody');
            if (laboratories.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center text-muted">No laboratory tests added</td></tr>');
            } else {
                tbody.html(laboratories.map((l, i) => `
            <tr>
                <td>${l.name}</td>
                <td>${l.sample_type}</td>
                <td>₦${l.price.toFixed(2)}</td>
                <td>${l.frequency}</td>
                <td class="fw-semibold">₦${l.total.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeLaboratory(${i})"><i class="ti ti-trash"></i></button></td>
            </tr>
        `).join(''));
            }
            calculateGrandTotal();
        }

        function renderServices() {
            const tbody = $('#servicesBody');
            if (services.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center text-muted">No services added</td></tr>');
            } else {
                tbody.html(services.map((s, i) => `
            <tr>
                <td>${s.name}</td>
                <td>${s.type}</td>
                <td>₦${s.price.toFixed(2)}</td>
                <td>${s.frequency}</td>
                <td class="fw-semibold">₦${s.total.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeService(${i})"><i class="ti ti-trash"></i></button></td>
            </tr>
        `).join(''));
            }
            calculateGrandTotal();
        }

        function removeMedication(index) {
            medications.splice(index, 1);
            renderMedications();
        }

        function removeLaboratory(index) {
            laboratories.splice(index, 1);
            renderLaboratories();
        }

        function removeService(index) {
            services.splice(index, 1);
            renderServices();
        }

        function calculateGrandTotal() {
            const total = calculateTotal();
            $('#grandTotal').text('₦' + total.toFixed(2));
        }

        function calculateTotal() {
            let total = 0;
            medications.forEach(m => total += parseFloat(m.total));
            laboratories.forEach(l => total += parseFloat(l.total));
            services.forEach(s => total += parseFloat(s.total));
            return total;
        }

        function resetMedicationForm() {
            currentDrug = null;
            $('#drug_select_create').val(null).trigger('change');
            $('#med_dosage, #med_strength, #med_cost, #med_total').val('');
            $('#med_quantity, #med_days').val('1');
        }

        function resetLabForm() {
            currentTest = null;
            $('#lab_select_create').val(null).trigger('change');
            $('#lab_sample, #lab_price, #lab_total').val('');
            $('#lab_frequency').val('1');
        }

        function resetServiceForm() {
            currentService = null;
            $('#service_select_create').val(null).trigger('change');
            $('#service_type_input, #service_price, #service_total').val('');
            $('#service_frequency').val('1');
        }

        function addMedication() {
            $('#medicationModal').modal('show');
        }

        function addLaboratory() {
            $('#laboratoryModal').modal('show');
        }

        function addService() {
            $('#serviceModal').modal('show');
        }
    </script>
@endpush
