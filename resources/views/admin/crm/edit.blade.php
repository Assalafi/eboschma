@extends('layouts.app')

@section('title', 'Edit Ticket - Customer Care')

@section('content')
    @if (!$ticket->canBeEditedBy(Auth::user()))
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h4><i class="ti-lock"></i> Access Denied</h4>
                        <p>Only the ticket creator can edit this ticket.</p>
                        <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-info">
                            <i class="ti-eye"></i> Back to Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="ti-headphone-alt"></i> Edit Ticket {{ $ticket->ticket_id }}
                            </h4>
                            <div class="card-action">
                                <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-info">
                                    <i class="ti-eye"></i> View
                                </a>
                                <a href="{{ route('crm.index') }}" class="btn btn-secondary">
                                    <i class="ti-arrow-left"></i> Back to Tickets
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($ticket->status === 'completed')
                                <div class="alert alert-warning">
                                    <i class="ti-info-alt"></i> This ticket is completed and cannot be edited.
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-info">
                                            <i class="ti-eye"></i> View Ticket
                                        </a>
                                        <a href="{{ route('crm.index') }}" class="btn btn-secondary">
                                            <i class="ti-arrow-left"></i> Back to Tickets
                                        </a>
                                    </div>
                                </div>
                            @elseif($ticket->status === 'in_progress')
                                <div class="alert alert-info">
                                    <i class="ti-info-alt"></i> This ticket is in progress. You can update the assigned
                                    staff member, SLA, and priority.
                                </div>
                                <form action="{{ route('crm.update', $ticket->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="assigned_to">Assign To <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('assigned_to') is-invalid @enderror"
                                                    id="assigned_to_display" name="assigned_to_display" required
                                                    list="staff_list" placeholder="Type to search staff members..."
                                                    value="{{ old('assigned_to_display', $ticket->assignedUser->fullname . ' (' . $ticket->assignedUser->email . ')' ?? '') }}">
                                                <datalist id="staff_list">
                                                    @foreach ($staff as $staffMember)
                                                        <option
                                                            value="{{ $staffMember->fullname }} ({{ $staffMember->email }})"
                                                            data-id="{{ $staffMember->id }}">
                                                            {{ $staffMember->fullname }} ({{ $staffMember->email }})
                                                        </option>
                                                    @endforeach
                                                </datalist>
                                                <input type="hidden" id="assigned_to" name="assigned_to"
                                                    value="{{ old('assigned_to', $ticket->assigned_to) }}">
                                                @error('assigned_to')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="sla_hours">SLA (in hours) <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control @error('sla_hours') is-invalid @enderror"
                                                    id="sla_hours" name="sla_hours" required>
                                                    <option value="">Select expected response time</option>
                                                    <option value="1"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '1' ? 'selected' : '' }}>
                                                        1
                                                        hour
                                                    </option>
                                                    <option value="2"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '2' ? 'selected' : '' }}>
                                                        2
                                                        hours
                                                    </option>
                                                    <option value="4"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '4' ? 'selected' : '' }}>
                                                        4
                                                        hours
                                                    </option>
                                                    <option value="8"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '8' ? 'selected' : '' }}>
                                                        8
                                                        hours
                                                    </option>
                                                    <option value="12"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '12' ? 'selected' : '' }}>
                                                        12
                                                        hours</option>
                                                    <option value="24"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '24' ? 'selected' : '' }}>
                                                        24
                                                        hours</option>
                                                    <option value="48"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '48' ? 'selected' : '' }}>
                                                        48
                                                        hours</option>
                                                    <option value="72"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '72' ? 'selected' : '' }}>
                                                        72
                                                        hours</option>
                                                    <option value="168"
                                                        {{ old('sla_hours', $ticket->sla_hours) == '168' ? 'selected' : '' }}>
                                                        1
                                                        week</option>
                                                </select>
                                                @error('sla_hours')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="department">Department</label>
                                                <input type="text"
                                                    class="form-control @error('department') is-invalid @enderror"
                                                    id="department" name="department"
                                                    value="{{ old('department', $ticket->department) }}">
                                                @error('department')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="priority">Priority <span class="text-danger">*</span></label>
                                                <select class="form-control @error('priority') is-invalid @enderror"
                                                    id="priority" name="priority" required>
                                                    <option value="">Select priority</option>
                                                    <option value="low"
                                                        {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>
                                                        Low</option>
                                                    <option value="medium"
                                                        {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>
                                                        Medium</option>
                                                    <option value="high"
                                                        {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>
                                                        High</option>
                                                    <option value="urgent"
                                                        {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>
                                                        Urgent</option>
                                                </select>
                                                @error('priority')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="status">Status <span class="text-danger">*</span></label>
                                                <select class="form-control @error('status') is-invalid @enderror"
                                                    id="status" name="status" required>
                                                    <option value="in_progress"
                                                        {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>
                                                        In Progress</option>
                                                    <option value="completed"
                                                        {{ old('status', $ticket->status) == 'completed' ? 'selected' : '' }}>
                                                        Completed</option>
                                                </select>
                                                @error('status')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                <small class="text-muted">Status can only be changed to In Progress or
                                                    Completed</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti-check"></i> Update Ticket
                                                </button>
                                                <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-secondary">
                                                    <i class="ti-close"></i> Cancel
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <form action="{{ route('crm.update', $ticket->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <label for="boschma_no">Boschma number <span class="text-danger"
                                                            id="boschma_required">*</span></label>
                                                    <div class="form-check">
                                                        <input type="hidden" name="is_outsider" value="0">
                                                        <input class="form-check-input" type="checkbox" id="is_outsider"
                                                            name="is_outsider" value="1"
                                                            {{ old('is_outsider', $ticket->beneficiary_type === 'outsider') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_outsider">
                                                            <span style="font-weight: 600">Outsider (Non-Boschma)</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text"
                                                    class="form-control @error('boschma_no') is-invalid @enderror"
                                                    id="boschma_no" name="boschma_no"
                                                    value="{{ old('boschma_no', $ticket->boschma_no) }}"
                                                    style="border-color: #28a745;"
                                                    {{ old('is_outsider', $ticket->beneficiary_type === 'outsider') ? 'disabled' : 'required' }}>
                                                <small class="text-muted"
                                                    id="boschma_help">{{ old('is_outsider', $ticket->beneficiary_type === 'outsider') ? 'Not required for outsiders' : 'Enter beneficiary\'s Boschma number' }}</small>
                                                @error('boschma_no')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Name <span class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    id="name" name="name"
                                                    value="{{ old('name', $ticket->name) }}" required>
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="facility_id">Facility <span class="text-danger"
                                                        id="facility_required">*</span></label>
                                                <select class="form-control @error('facility_id') is-invalid @enderror"
                                                    id="facility_id" name="facility_id"
                                                    {{ old('is_outsider', $ticket->beneficiary_type === 'outsider') ? '' : 'required' }}>
                                                    <option value="">Select an option</option>
                                                    @foreach ($facilities as $facility)
                                                        <option value="{{ $facility->id }}"
                                                            {{ old('facility_id', $ticket->facility_id) == $facility->id ? 'selected' : '' }}>
                                                            {{ $facility->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted"
                                                    id="facility_help">{{ old('is_outsider', $ticket->beneficiary_type === 'outsider') ? 'Optional for outsiders - may help with routing' : 'Select beneficiary\'s facility' }}</small>
                                                @error('facility_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ticket_category_id">Complain Type <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    class="form-control @error('ticket_category_id') is-invalid @enderror"
                                                    id="ticket_category_id" name="ticket_category_id" required>
                                                    <option value="">Select an option</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ old('ticket_category_id', $ticket->ticket_category_id) == $category->id ? 'selected' : '' }}
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
                                                <label for="ticket_id">Ticket id <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('ticket_id') is-invalid @enderror"
                                                    id="ticket_id" name="ticket_id" value="{{ $ticket->ticket_id }}"
                                                    readonly style="background-color: #f8f9fa;">
                                                @error('ticket_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    id="phone" name="phone"
                                                    value="{{ old('phone', $ticket->phone) }}">
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="complaint">Complain <span class="text-danger">*</span></label>
                                                <textarea class="form-control @error('complaint') is-invalid @enderror tinymce-editor" id="complaint"
                                                    name="complaint" required>{{ old('complaint', $ticket->complaint) }}</textarea>
                                                @error('complaint')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="description">Additional Information</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                    rows="3">{{ old('description', $ticket->description) }}</textarea>
                                                @error('description')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="department">Department</label>
                                            {{-- Department should be a drop down of ES Office, Finance, ICT, Admin, Programmes, PRS, SQA --}}
                                            <select class="form-control @error('department') is-invalid @enderror"
                                                id="department" name="department" required>
                                                <option value="">Select an option</option>
                                                <option value="ES Office"
                                                    {{ old('department', $ticket->department) == 'ES Office' ? 'selected' : '' }}>
                                                    ES Office</option>
                                                <option value="Finance"
                                                    {{ old('department', $ticket->department) == 'Finance' ? 'selected' : '' }}>
                                                    Finance</option>
                                                <option value="ICT"
                                                    {{ old('department', $ticket->department) == 'ICT' ? 'selected' : '' }}>
                                                    ICT</option>
                                                <option value="Admin"
                                                    {{ old('department', $ticket->department) == 'Admin' ? 'selected' : '' }}>
                                                    Admin</option>
                                                <option value="Programmes"
                                                    {{ old('department', $ticket->department) == 'Programmes' ? 'selected' : '' }}>
                                                    Programmes</option>
                                                <option value="PRS"
                                                    {{ old('department', $ticket->department) == 'PRS' ? 'selected' : '' }}>
                                                    PRS</option>
                                                <option value="SQA"
                                                    {{ old('department', $ticket->department) == 'SQA' ? 'selected' : '' }}>
                                                    SQA</option>
                                            </select>
                                            @error('department')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="assigned_to">Assign To <span class="text-danger">*</span></label>
                                            <select class="form-control @error('assigned_to') is-invalid @enderror"
                                                id="assigned_to" name="assigned_to" required>
                                                <option value="">Select an option</option>
                                                @foreach ($staff as $staffMember)
                                                    <option value="{{ $staffMember->id }}"
                                                        {{ old('assigned_to', $ticket->assigned_to) == $staffMember->id ? 'selected' : '' }}>
                                                        {{ $staffMember->fullname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('assigned_to')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="sla_hours">SLA (in hours) <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control @error('sla_hours') is-invalid @enderror"
                                                id="sla_hours" name="sla_hours" required>
                                                <option value="">Select expected response time</option>
                                                <option value="1"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '1' ? 'selected' : '' }}>
                                                    1
                                                    hour
                                                </option>
                                                <option value="2"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '2' ? 'selected' : '' }}>
                                                    2
                                                    hours
                                                </option>
                                                <option value="4"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '4' ? 'selected' : '' }}>
                                                    4
                                                    hours
                                                </option>
                                                <option value="8"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '8' ? 'selected' : '' }}>
                                                    8
                                                    hours
                                                </option>
                                                <option value="12"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '12' ? 'selected' : '' }}>
                                                    12
                                                    hours</option>
                                                <option value="24"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '24' ? 'selected' : '' }}>
                                                    24
                                                    hours</option>
                                                <option value="48"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '48' ? 'selected' : '' }}>
                                                    48
                                                    hours</option>
                                                <option value="72"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '72' ? 'selected' : '' }}>
                                                    72
                                                    hours</option>
                                                <option value="168"
                                                    {{ old('sla_hours', $ticket->sla_hours) == '168' ? 'selected' : '' }}>
                                                    1
                                                    week</option>
                                            </select>
                                            @error('sla_hours')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="priority">Priority <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control @error('priority') is-invalid @enderror"
                                                        id="priority" name="priority" required>
                                                        <option value="">Select priority</option>
                                                        <option value="low"
                                                            {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>
                                                            Low</option>
                                                        <option value="medium"
                                                            {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>
                                                            Medium</option>
                                                        <option value="high"
                                                            {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>
                                                            High</option>
                                                        <option value="urgent"
                                                            {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>
                                                            Urgent</option>
                                                    </select>
                                                    @error('priority')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status">Status <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control @error('status') is-invalid @enderror"
                                                        id="status" name="status" required>
                                                        <option value="in_progress"
                                                            {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>
                                                            In Progress</option>
                                                        <option value="completed"
                                                            {{ old('status', $ticket->status) == 'completed' ? 'selected' : '' }}>
                                                            Completed</option>
                                                    </select>
                                                    @error('status')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">Status can only be changed to In Progress
                                                        or Completed</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-check"></i> Update Ticket
                                    </button>
                                    <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-secondary">
                                        <i class="ti-close"></i> Cancel
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
    @endif
    @endif

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
            font-weight: normal !important;
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
            // Initialize CKEditor for complaint field
            if (document.getElementById('complaint')) {
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
            }

            const boschmaNoInput = document.getElementById('boschma_no');
            const facilitySelect = document.getElementById('facility_id');
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
                    facilitySelect.removeAttribute('required');
                    document.getElementById('facility_required').style.display = 'none';
                    document.getElementById('facility_help').textContent =
                        'Optional for outsiders - may help with routing';
                } else {
                    facilitySelect.setAttribute('required', 'required');
                    document.getElementById('facility_required').style.display = 'inline';
                    document.getElementById('facility_help').textContent = 'Select beneficiary\'s facility';
                }
            });

            // Initialize checkbox state on page load
            const isInitiallyOutsider = outsiderCheckbox.checked;
            if (isInitiallyOutsider) {
                document.getElementById('boschma_required').style.display = 'none';
                document.getElementById('facility_required').style.display = 'none';
            }

            // Initialize searchable datalist for staff
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
        });
    </script>
@endsection
