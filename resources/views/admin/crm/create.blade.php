@extends('layouts.app')

@section('title', 'Create Ticket - Customer Care')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="ti-headphone-alt"></i> Create Enquiry
                        </h4>
                        <div style="float: right" class="card-action">
                            <a href="{{ route('crm.index') }}" class="btn btn-secondary">
                                <i class="ti-arrow-left"></i> Back to Tickets
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Error!</strong> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <strong>Success!</strong> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Please fix the following errors:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        <form action="{{ route('crm.store') }}" method="POST" enctype="multipart/form-data"
                            id="ticketForm">
                            @csrf
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const form = document.getElementById('ticketForm');
                                    form.addEventListener('submit', function(e) {
                                        console.log('Form submitting...');
                                        console.log('Form data:', new FormData(form));

                                        // Check if all required fields are filled
                                        const requiredFields = form.querySelectorAll('[required]');
                                        let missingFields = [];

                                        requiredFields.forEach(function(field) {
                                            if (!field.value.trim()) {
                                                missingFields.push(field.name || field.id);
                                            }
                                        });

                                        // Check complaint field manually (since it's not required in HTML but managed by CKEditor)
                                        const complaintValue = document.getElementById('complaint').value.trim();
                                        if (!complaintValue) {
                                            missingFields.push('complaint');
                                            alert('Complaint field is required. Please enter the complaint details.');
                                            e.preventDefault();
                                            return false;
                                        }

                                        if (missingFields.length > 0) {
                                            console.log('Missing required fields:', missingFields);
                                            alert('Please fill in all required fields: ' + missingFields.join(', '));
                                            e.preventDefault();
                                            return false;
                                        }

                                        console.log('Form validation passed, submitting...');
                                    });
                                });
                            </script>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <label for="boschma_no">Boschma number <span class="text-danger"
                                                    id="boschma_required">*</span></label>
                                            <div class="form-check">
                                                <input type="hidden" name="is_outsider" value="0">
                                                <input class="form-check-input" type="checkbox" id="is_outsider"
                                                    name="is_outsider" value="1">
                                                <label class="form-check-label" for="is_outsider">
                                                    <span style="font-weight: 600">Outsider (Non-Boschma)</span>
                                                </label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control @error('boschma_no') is-invalid @enderror"
                                            id="boschma_no" name="boschma_no" value="{{ old('boschma_no') }}"
                                            style="border-color: #28a745;" required>
                                        <small class="text-muted" id="boschma_help">Enter beneficiary's Boschma
                                            number</small>
                                        @error('boschma_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="facility_id">Facility <span class="text-danger"
                                                id="facility_required">*</span></label>
                                        <input type="text"
                                            class="form-control @error('facility_id') is-invalid @enderror"
                                            id="facility_id_display" name="facility_id_display" required
                                            list="facility_list" placeholder="Type to search facilities...">
                                        <datalist id="facility_list">
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->name }}" data-id="{{ $facility->id }}">
                                                    {{ $facility->name }}
                                                </option>
                                            @endforeach
                                        </datalist>
                                        <input type="hidden" id="facility_id" name="facility_id">
                                        <small class="text-muted" id="facility_help">Select beneficiary's facility</small>
                                        @error('facility_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="ticket_category_id">Complain Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control @error('ticket_category_id') is-invalid @enderror"
                                            id="ticket_category_id" name="ticket_category_id" required>
                                            <option value="">Select an option</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('ticket_category_id') == $category->id ? 'selected' : '' }}
                                                    style="background-color: {{ $category->color }}20;">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('ticket_category_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone') }}">
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">


                                    <!-- Photo Display Area -->
                                    <div id="photo_display" class="mt-3" style="display: none; margin-bottom: 5px;">
                                        <div class="row">

                                            <div class="col-md-5">
                                            </div>

                                            <div class="col-md-3">
                                                <img id="beneficiary_photo" src="" alt="Beneficiary Photo"
                                                    class="img-fluid rounded border" style="max-height: 100px;">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <strong id="photo_type_label" style="color: #000000;"></strong>
                                                        <div id="photo_gender" style="color: #000000; font-weight: bold;">
                                                        </div>
                                                        <div id="photo_age" style="color: #000000; font-weight: bold;">
                                                        </div>
                                                        <div id="photo_nin" style="color: #000000; font-weight: bold;">
                                                        </div>
                                                        <div id="photo_status" style="color: #000000; font-weight: bold;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="department">Department</label>
                                        {{-- Department should be a drop down of ES Office, Finance, ICT, Admin, Programmes, PRS, SQA --}}
                                        <select class="form-control @error('department') is-invalid @enderror"
                                            id="department" name="department" required>
                                            <option value="">Select an option</option>
                                            <option value="ES Office">ES Office</option>
                                            <option value="Finance">Finance</option>
                                            <option value="ICT">ICT</option>
                                            <option value="Admin">Admin</option>
                                            <option value="Programmes">Programmes</option>
                                            <option value="PRS">PRS</option>
                                            <option value="SQA">SQA</option>
                                        </select>
                                        @error('department')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="assigned_to">Assign To <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('assigned_to') is-invalid @enderror"
                                            id="assigned_to_display" name="assigned_to_display" required
                                            list="staff_list" placeholder="Type to search staff members...">
                                        <datalist id="staff_list">
                                            @foreach ($staff as $staffMember)
                                                <option value="{{ $staffMember->fullname }} ({{ $staffMember->email }})"
                                                    data-id="{{ $staffMember->id }}">
                                                    {{ $staffMember->fullname }} ({{ $staffMember->email }})
                                                </option>
                                            @endforeach
                                        </datalist>
                                        <input type="hidden" id="assigned_to" name="assigned_to">
                                        @error('assigned_to')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="sla_hours">SLA (in hours) <span class="text-danger">*</span></label>
                                        <select class="form-control @error('sla_hours') is-invalid @enderror"
                                            id="sla_hours" name="sla_hours" required>
                                            <option value="">Select expected response time</option>
                                            <option value="1" {{ old('sla_hours') == '1' ? 'selected' : '' }}>1 hour
                                            </option>
                                            <option value="2" {{ old('sla_hours') == '2' ? 'selected' : '' }}>2 hours
                                            </option>
                                            <option value="4" {{ old('sla_hours') == '4' ? 'selected' : '' }}>4 hours
                                            </option>
                                            <option value="8" {{ old('sla_hours') == '8' ? 'selected' : '' }}>8 hours
                                            </option>
                                            <option value="12" {{ old('sla_hours') == '12' ? 'selected' : '' }}>12
                                                hours</option>
                                            <option value="24" {{ old('sla_hours') == '24' ? 'selected' : '' }}>24
                                                hours</option>
                                            <option value="48" {{ old('sla_hours') == '48' ? 'selected' : '' }}>48
                                                hours</option>
                                            <option value="72" {{ old('sla_hours') == '72' ? 'selected' : '' }}>72
                                                hours</option>
                                            <option value="168" {{ old('sla_hours') == '168' ? 'selected' : '' }}>1
                                                week</option>
                                        </select>
                                        @error('sla_hours')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="priority">Priority <span class="text-danger">*</span></label>
                                        <select class="form-control @error('priority') is-invalid @enderror"
                                            id="priority" name="priority" required>
                                            <option value="">Select priority</option>
                                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low
                                            </option>
                                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>
                                                Medium</option>
                                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High
                                            </option>
                                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                                Urgent</option>
                                        </select>
                                        @error('priority')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="complaint">Complain <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('complaint') is-invalid @enderror" id="complaint" name="complaint">{{ old('complaint') }}</textarea>
                                        @error('complaint')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- CKEditor Script -->
                            <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
                            <style>
                                .ck-editor__editable {
                                    min-height: 300px !important;
                                }

                                .ck-content {
                                    min-height: 300px !important;
                                }

                                /* Make all form inputs and selects bold and pure black */
                                .form-control,
                                .form-select {
                                    font-weight: bold !important;
                                    color: #000000 !important;
                                }

                                .form-control::placeholder {
                                    font-weight: bold !important;
                                    color: #000000 !important;
                                }

                                .form-control:focus,
                                .form-select:focus {
                                    font-weight: bold !important;
                                    color: #000000 !important;
                                }

                                /* Make all form labels bold and pure black */
                                .form-group label,
                                .form-label {
                                    font-weight: bold !important;
                                    color: #000000 !important;
                                }

                                /* Make datalist suggestions bold and pure black */
                                #staff_list option,
                                #facility_list option {
                                    font-weight: bold;
                                    color: #000000;
                                }
                            </style>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    ClassicEditor
                                        .create(document.querySelector('#complaint'), {
                                            toolbar: {
                                                items: [
                                                    'heading', '|',
                                                    'bold', 'italic', 'underline', 'strikethrough', '|',
                                                    'bulletedList', 'numberedList', '|',
                                                    'outdent', 'indent', '|',
                                                    'link', 'imageUpload', 'insertTable', '|',
                                                    'blockQuote', '|',
                                                    'undo', 'redo'
                                                ]
                                            },
                                            image: {
                                                toolbar: [
                                                    'imageTextAlternative',
                                                    'imageStyle:full',
                                                    'imageStyle:side'
                                                ]
                                            },
                                            table: {
                                                contentToolbar: [
                                                    'tableColumn',
                                                    'tableRow',
                                                    'mergeTableCells'
                                                ]
                                            },
                                            placeholder: 'Enter detailed complaint description...'
                                        })
                                        .then(editor => {
                                            window.complaintEditor = editor;

                                            // Sync with form on change
                                            editor.model.document.on('change:data', () => {
                                                const textarea = document.querySelector('#complaint');
                                                textarea.value = editor.getData();
                                            });
                                        })
                                        .catch(error => {
                                            console.error('CKEditor initialization error:', error);
                                        });
                                });
                            </script>

                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="attachment" class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="ti-clip me-2"></i>
                                                <strong>Attachments (Optional)</strong>
                                                <span class="badge bg-info ms-2">Dynamic Upload</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="addFileInput()">
                                                <i class="ti-plus me-1"></i> Add File
                                            </button>
                                        </label>

                                        <div id="fileInputsContainer" class="mt-3">
                                            <!-- Initial file input -->
                                            <div class="file-input-item d-flex align-items-center mb-2">
                                                <input type="file"
                                                    class="form-control me-2 @error('attachments') is-invalid @enderror"
                                                    name="attachments[]" accept="*/*">
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="removeFileInput(this)" title="Remove file">
                                                    <i class="ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        @error('attachments')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <br>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="ti-check me-2"></i> Create
                                        </button>
                                        <div class="mt-2">
                                            <a href="{{ route('crm.index') }}" class="btn btn-secondary">
                                                <i class="ti-close me-1"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const boschmaNoInput = document.getElementById('boschma_no');
            const nameInput = document.getElementById('name');
            const phoneInput = document.getElementById('phone');
            const emailInput = document.getElementById('email'); // May not exist
            const facilityInput = document.getElementById('facility_id_display');
            const outsiderCheckbox = document.getElementById('is_outsider');

            // Handle outsider checkbox
            outsiderCheckbox.addEventListener('change', function() {
                const isOutsider = this.checked;

                // Toggle Boschma No field
                if (isOutsider) {
                    boschmaNoInput.removeAttribute('required');
                    boschmaNoInput.value = '';
                    boschmaNoInput.disabled = true;
                    document.getElementById('boschma_required').style.display = 'none';
                    document.getElementById('boschma_help').textContent = 'Not required for outsiders';
                } else {
                    boschmaNoInput.setAttribute('required', 'required');
                    boschmaNoInput.disabled = false;
                    document.getElementById('boschma_required').style.display = 'inline';
                    document.getElementById('boschma_help').textContent =
                        'Enter beneficiary\'s Boschma number';
                }

                // Toggle Facility field - keep enabled but optional for outsiders
                if (isOutsider) {
                    facilityInput.removeAttribute('required');
                    document.getElementById('facility_required').style.display = 'none';
                    document.getElementById('facility_help').textContent =
                        'Optional for outsiders - may help with routing';
                } else {
                    facilityInput.setAttribute('required', 'required');
                    document.getElementById('facility_required').style.display = 'inline';
                    document.getElementById('facility_help').textContent = 'Select beneficiary\'s facility';
                }
            });

            // Initialize checkbox state on page load
            const isInitiallyOutsider = outsiderCheckbox.checked;
            if (isInitiallyOutsider) {
                boschmaNoInput.removeAttribute('required');
                boschmaNoInput.disabled = true;
                document.getElementById('boschma_required').style.display = 'none';
                document.getElementById('boschma_help').textContent = 'Not required for outsiders';
                facilityInput.removeAttribute('required');
                document.getElementById('facility_required').style.display = 'none';
                document.getElementById('facility_help').textContent =
                    'Optional for outsiders - may help with routing';
            }

            // Boschma No validation and auto-population
            boschmaNoInput.addEventListener('blur', function() {
                const boschmaNo = this.value.trim();
                console.log('Boschma No entered:', boschmaNo);

                if (boschmaNo) {
                    // Show loading indicator
                    this.classList.add('loading');

                    const encodedBoschmaNo = encodeURIComponent(boschmaNo);
                    const url = `/crm/validate-boschma-no/${encodedBoschmaNo}`;
                    console.log('Fetching from URL:', url);

                    // Fetch beneficiary info via AJAX
                    fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            console.log('Response ok:', response.ok);
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Received data:', data);
                            if (data.found) {
                                // Auto-populate fields with beneficiary info (handle null/empty values)
                                if (data.name && data.name.trim() && nameInput) {
                                    nameInput.value = data.name;
                                    nameInput.setAttribute('readonly', true);
                                    nameInput.style.backgroundColor = '#f8f9fa';
                                } else if (nameInput) {
                                    nameInput.removeAttribute('readonly');
                                    nameInput.style.backgroundColor = '';
                                }

                                if (data.phone && data.phone.trim() && phoneInput) {
                                    phoneInput.value = data.phone;
                                }

                                if (data.email && data.email.trim() && emailInput) {
                                    emailInput.value = data.email;
                                }

                                if (data.facility_id && facilityInput) {
                                    // Find the facility option by ID and set the display value to the name
                                    const facilityOption = $(
                                        `#facility_list option[data-id="${data.facility_id}"]`);
                                    if (facilityOption.length > 0) {
                                        facilityInput.value = facilityOption
                                            .val(); // Set display to facility name
                                        $('#facility_id').val(data
                                            .facility_id); // Set hidden field to facility ID
                                    }
                                }

                                // Display beneficiary photo and additional info if available
                                if (data.photo || data.gender || data.nin || data.date_of_birth || data
                                    .status) {
                                    const photoDisplay = document.getElementById('photo_display');
                                    const photoImg = document.getElementById('beneficiary_photo');
                                    const photoTypeLabel = document.getElementById('photo_type_label');
                                    const photoGender = document.getElementById('photo_gender');
                                    const photoNin = document.getElementById('photo_nin');
                                    const photoAge = document.getElementById('photo_age');
                                    const photoStatus = document.getElementById('photo_status');

                                    // Set photo if available
                                    if (data.photo) {
                                        photoImg.src = data.photo;
                                        photoImg.alt = data.type === 'Beneficiary' ? 'Principal' : data
                                            .type;
                                        photoImg.style.display = 'block';
                                    } else {
                                        photoImg.style.display = 'none';
                                    }

                                    // Set type label
                                    photoTypeLabel.textContent = data.type === 'Beneficiary' ?
                                        'Principal' : data.type;

                                    // Set gender
                                    photoGender.textContent = data.gender ? `Gender: ${data.gender}` :
                                        '';

                                    // Set NIN
                                    photoNin.textContent = data.nin ? `NIN: ${data.nin}` : '';

                                    // Calculate and set age from date of birth
                                    if (data.date_of_birth) {
                                        const birthDate = new Date(data.date_of_birth);
                                        const today = new Date();
                                        let age = today.getFullYear() - birthDate.getFullYear();
                                        const monthDiff = today.getMonth() - birthDate.getMonth();
                                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() <
                                                birthDate.getDate())) {
                                            age--;
                                        }
                                        photoAge.textContent = `Age: ${age} years`;
                                    } else {
                                        photoAge.textContent = '';
                                    }

                                    // Set status
                                    photoStatus.textContent = data.status ?
                                        `Status: ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}` :
                                        '';

                                    photoDisplay.style.display = 'block';
                                } else {
                                    // Hide photo display if no data
                                    document.getElementById('photo_display').style.display = 'none';
                                }

                                // Show success message with type
                                const displayName = data.name && data.name.trim() ? data.name :
                                    'Record found';
                                showNotification(`✅ ${data.type}: ${displayName}`, 'success');
                            } else {
                                // Show non-beneficiary message
                                showNotification(
                                    'ℹ️ Non-beneficiary: Please enter customer details manually',
                                    'info');

                                // Make name field editable
                                if (nameInput) {
                                    nameInput.removeAttribute('readonly');
                                    nameInput.style.backgroundColor = '';
                                }

                                // Hide photo display
                                document.getElementById('photo_display').style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error validating Boschma No:', error);
                            console.error('Error details:', error.message);
                            let errorMessage = '❌ Error validating Boschma No';

                            if (error.message.includes('HTTP 401')) {
                                errorMessage = '❌ Authentication required. Please refresh the page.';
                            } else if (error.message.includes('HTTP 403')) {
                                errorMessage = '❌ Permission denied. You need CRM view permission.';
                            } else if (error.message.includes('HTTP 404')) {
                                errorMessage = '❌ Validation endpoint not found.';
                            } else if (error.message.includes('HTTP 419')) {
                                errorMessage = '❌ CSRF token mismatch. Please refresh the page.';
                            } else if (error.message.includes('HTTP 500')) {
                                errorMessage = '❌ Server error. Please try again later.';
                            } else if (error.message.includes('JSON')) {
                                errorMessage = '❌ Invalid response from server. Please check console.';
                            } else {
                                errorMessage = `❌ ${error.message}`;
                            }

                            showNotification(errorMessage, 'error');

                            // Hide photo display on error
                            document.getElementById('photo_display').style.display = 'none';
                        })
                        .finally(() => {
                            this.classList.remove('loading');
                        });
                } else {
                    // Clear auto-populated fields if Boschma No is cleared
                    if (nameInput) {
                        nameInput.removeAttribute('readonly');
                        nameInput.style.backgroundColor = '';
                    }

                    // Hide photo display when Boschma No is cleared
                    document.getElementById('photo_display').style.display = 'none';
                }
            });

            // Notification function
            function showNotification(message, type) {
                // Remove existing notifications
                const existing = document.querySelector('.boschma-notification');
                if (existing) {
                    existing.remove();
                }

                // Create notification element
                const notification = document.createElement('div');
                notification.className = `alert alert-${type} boschma-notification`;
                notification.style.position = 'absolute';
                notification.style.top = '-40px';
                notification.style.left = '0';
                notification.style.right = '0';
                notification.style.zIndex = '1000';
                notification.innerHTML = message;

                // Position relative to the form group
                boschmaNoInput.parentElement.style.position = 'relative';
                boschmaNoInput.parentElement.appendChild(notification);

                // Auto-remove after 3 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 3000);
            }
        });

        // Initialize searchable datalists
        $(document).ready(function() {
            // Facility search functionality
            $('#facility_id_display').on('input', function() {
                const inputValue = $(this).val();
                const hiddenInput = $('#facility_id');

                // Find matching option by name
                const matchingOption = $('#facility_list option').filter(function() {
                    return $(this).val() === inputValue;
                });

                if (matchingOption.length > 0) {
                    hiddenInput.val(matchingOption.attr('data-id'));
                } else {
                    hiddenInput.val('');
                }
            });

            // Staff search functionality  
            $('#assigned_to_display').on('input', function() {
                const inputValue = $(this).val();
                const hiddenInput = $('#assigned_to');

                // Find matching option by exact value match
                const matchingOption = $('#staff_list option').filter(function() {
                    const optionValue = $(this).val();
                    return optionValue === inputValue;
                });

                if (matchingOption.length > 0) {
                    hiddenInput.val(matchingOption.attr('data-id'));
                } else {
                    hiddenInput.val('');
                }
            });

            // Set initial values if editing (from old input)
            @if (old('facility_id'))
                const facilityId = '{{ old('facility_id') }}';
                const facilityOption = $(`#facility_list option[data-id="${facilityId}"]`);
                if (facilityOption.length > 0) {
                    $('#facility_id_display').val(facilityOption.val());
                    $('#facility_id').val(facilityId);
                }
            @endif

            @if (old('assigned_to'))
                const staffId = '{{ old('assigned_to') }}';
                const staffOption = $(`#staff_list option[data-id="${staffId}"]`);
                if (staffOption.length > 0) {
                    const staffValue = staffOption.val();
                    $('#assigned_to_display').val(staffValue);
                    $('#assigned_to').val(staffId);
                }
            @endif
        });

        // Dynamic File Input Functions
        let fileInputCount = 1;

        function addFileInput() {
            fileInputCount++;
            const container = document.getElementById('fileInputsContainer');

            const fileInputItem = document.createElement('div');
            fileInputItem.className = 'file-input-item d-flex align-items-center mb-2';
            fileInputItem.innerHTML = `
                <input type="file" class="form-control me-2" name="attachments[]" multiple accept="*/*">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFileInput(this)" title="Remove file">
                    <i class="ti-trash"></i>
                </button>
            `;

            // Add animation
            fileInputItem.style.opacity = '0';
            fileInputItem.style.transform = 'translateY(-10px)';
            container.appendChild(fileInputItem);

            // Animate in
            setTimeout(() => {
                fileInputItem.style.transition = 'all 0.3s ease';
                fileInputItem.style.opacity = '1';
                fileInputItem.style.transform = 'translateY(0)';
            }, 10);

            // Focus the new input
            fileInputItem.querySelector('input[type="file"]').focus();
        }

        function removeFileInput(button) {
            const fileInputItem = button.parentElement;

            // Animate out
            fileInputItem.style.transition = 'all 0.3s ease';
            fileInputItem.style.opacity = '0';
            fileInputItem.style.transform = 'translateX(20px)';

            // Remove after animation
            setTimeout(() => {
                fileInputItem.remove();

                // Ensure at least one file input remains
                const container = document.getElementById('fileInputsContainer');
                if (container.children.length === 0) {
                    addFileInput();
                }
            }, 300);
        }
    </script>

    <style>
        /* Dynamic File Input Styling */
        .file-input-item {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .file-input-item .btn-outline-danger {
            transition: all 0.2s ease;
        }

        .file-input-item .btn-outline-danger:hover {
            transform: scale(1.1);
            background-color: #dc3545;
            color: white;
        }

        /* Enhanced file input styling */
        .file-input-item input[type="file"] {
            transition: all 0.2s ease;
        }

        .file-input-item input[type="file"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Add file button animation */
        .btn-outline-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        /* Custom file input button styling */
        input[type="file"]::-webkit-file-upload-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: #0056b3;
        }
    </style>
@endsection
