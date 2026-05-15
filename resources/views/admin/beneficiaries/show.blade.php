@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h6 class="main-content-label mb-1">Beneficiary Details</h6>
                                <p class="text-muted card-sub-title">Viewing beneficiary information and dependents</p>
                            </div>
                            <div class="d-flex align-items-center flex-wrap">
                                <a href="{{ route('beneficiaries.index') }}"
                                    class="btn btn-outline-primary btn-sm mr-1 mb-1">
                                    <i class="fe fe-list"></i> All Beneficiaries
                                </a>
                                <a href="{{ route('beneficiaries.pdf', $beneficiary->id) }}"
                                    class="btn btn-danger btn-sm mr-1 mb-1">
                                    <i class="fe fe-file-text"></i> PDF
                                </a>
                                <a href="{{ route('beneficiaries.id-card', $beneficiary->id) }}"
                                    class="btn btn-primary btn-sm mr-1 mb-1">
                                    <i class="fe fe-credit-card"></i> View ID Card
                                </a>
                                <a href="{{ route('beneficiaries.edit', $beneficiary->id) }}"
                                    class="btn btn-primary btn-sm mr-1 mb-1">
                                    <i class="fe fe-edit"></i> Edit
                                </a>

                                <!-- Status Action Buttons -->
                                @if ($beneficiary->status !== 'active')
                                    <button type="button" class="btn btn-success btn-sm mr-1 mb-1"
                                        onclick="changeStatus('active')">
                                        <i class="fe fe-check-circle"></i> Activate
                                    </button>
                                @endif
                                @if ($beneficiary->status !== 'pending')
                                    <button type="button" class="btn btn-warning btn-sm mr-1 mb-1"
                                        onclick="changeStatus('pending')">
                                        <i class="fe fe-clock"></i> Pending
                                    </button>
                                @endif
                                @if ($beneficiary->status !== 'inactive')
                                    <button type="button" class="btn btn-secondary btn-sm mr-1 mb-1"
                                        onclick="changeStatus('inactive')">
                                        <i class="fe fe-slash"></i> Deactivate
                                    </button>
                                @endif
                                @if ($beneficiary->status !== 'rejected')
                                    <button type="button" class="btn btn-outline-secondary btn-sm mr-1 mb-1"
                                        onclick="changeStatus('rejected')">
                                        <i class="fe fe-x-circle"></i> Reject
                                    </button>
                                @endif

                                <!-- Convert Program Button -->
                                @if ($beneficiary->boschma_no)
                                    <button type="button" class="btn btn-info btn-sm mr-1 mb-1" data-bs-toggle="modal"
                                        data-bs-target="#convertProgramModal">
                                        <i class="fe fe-refresh-cw"></i> Convert Program
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Convert Program Modal -->
                        <div class="modal fade" id="convertProgramModal" tabindex="-1" role="dialog"
                            aria-labelledby="convertProgramModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="convertProgramModalLabel">
                                            <i class="fe fe-refresh-cw"></i> Convert to Different Program
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('beneficiaries.convert-program', $beneficiary->id) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="current_program_id"
                                            value="{{ $beneficiary->program_id }}">
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <i class="fe fe-alert-triangle"></i>
                                                <strong>Warning:</strong> Converting the program will change the BOSCHMA ID
                                                for this beneficiary and all dependants (spouse and children).
                                            </div>

                                            <div class="mb-3">
                                                <label class="font-weight-bold">Current Details:</label>
                                                <ul class="list-unstyled mb-0 mt-2">
                                                    <li><strong>Program:</strong> {{ $beneficiary->program->name ?? 'N/A' }}
                                                    </li>
                                                    <li><strong>BOSCHMA ID:</strong>
                                                        <code>{{ $beneficiary->boschma_no }}</code>
                                                    </li>
                                                    @if ($beneficiary->spouse)
                                                        <li><strong>Spouse ID:</strong>
                                                            <code>{{ $beneficiary->spouse->boschma_no }}</code>
                                                        </li>
                                                    @endif
                                                    @if ($beneficiary->children->count() > 0)
                                                        <li><strong>Children IDs:</strong>
                                                            @foreach ($beneficiary->children as $child)
                                                                <code>{{ $child->boschma_no }}</code>{{ !$loop->last ? ', ' : '' }}
                                                            @endforeach
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>

                                            <div class="form-group">
                                                <label for="new_program_id" class="font-weight-bold">Select New Program:
                                                    <span class="text-danger">*</span></label>
                                                <select name="new_program_id" id="new_program_id" class="form-control"
                                                    required>
                                                    <option value="">-- Select Program --</option>
                                                    @foreach ($programs ?? [] as $program)
                                                        @if ($program->id != $beneficiary->program_id)
                                                            <option value="{{ $program->id }}">
                                                                {{ $program->name }} ({{ $program->format }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group mt-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="generate_new_boschma_no"
                                                        name="generate_new_boschma_no" value="1">
                                                    <label class="form-check-label" for="generate_new_boschma_no">
                                                        <strong>Generate New Boschma No</strong>
                                                        <br><small class="text-muted">Check this to generate a new BOSCHMA number with a new sequence number (continuing from the last available). If unchecked, the current sequence number ({{ $beneficiary->sequence_number }}) will be kept.</small>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="newIdPreview" class="alert alert-info" style="display: none;">
                                                <i class="fe fe-info"></i>
                                                <strong>New BOSCHMA ID will be:</strong> <span id="previewId"></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-info">
                                                <i class="fe fe-refresh-cw"></i> Convert Program
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Status Change Form (hidden) -->
                        <form id="status-change-form" method="POST"
                            action="{{ route('beneficiaries.update-status', $beneficiary->id) }}" style="display: none;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" id="status-input">
                        </form>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="beneficiary-tab" data-bs-toggle="tab"
                                    href="#beneficiary-section" role="tab">
                                    <i class="fe fe-user"></i> Beneficiary Information
                                </a>
                            </li>
                            @if ($beneficiary->spouse)
                                <li class="nav-item">
                                    <a class="nav-link" id="spouse-tab" data-bs-toggle="tab" href="#spouse-section"
                                        role="tab">
                                        <i class="fe fe-user-plus"></i> Spouse Information
                                    </a>
                                </li>
                            @endif
                            @if ($beneficiary->children && $beneficiary->children->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link" id="children-tab" data-bs-toggle="tab" href="#children-section"
                                        role="tab">
                                        <i class="fe fe-users"></i> Children Information
                                        ({{ $beneficiary->children->count() }})
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" id="contributions-tab" data-bs-toggle="tab"
                                    href="#contributions-section" role="tab">
                                    <i class="fe fe-dollar-sign"></i> Contributions @if ($beneficiary->contributions->count() > 0)
                                        ({{ $beneficiary->contributions->count() }})
                                    @endif
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content mt-3">
                            <!-- Beneficiary Tab -->
                            <div class="tab-pane fade show active" id="beneficiary-section" role="tabpanel"
                                aria-labelledby="beneficiary-tab">
                                <div class="card shadow-none border">
                                    <div class="card-header" style="background-color: #006734; color: white;">
                                        <h4 class="card-title mb-0">
                                            <i class="fe fe-user mr-2"></i> Primary Beneficiary Information
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 text-center">
                                                @if ($beneficiary->photo)
                                                    <img src="{{ url('storage/' . $beneficiary->photo) }}"
                                                        class="img-fluid rounded border" style="max-height: 200px;"
                                                        alt="{{ $beneficiary->first_name }} Photo">
                                                @else
                                                    <img src="{{ asset('assets/img/avatar-placeholder.png') }}"
                                                        class="img-fluid rounded border" style="max-height: 200px;"
                                                        alt="No Photo">
                                                @endif
                                                <div class="mt-2">
                                                    <h4 class="mb-1">{{ $beneficiary->boschma_no }}</h4>
                                                    @if ($beneficiary->status == 'active')
                                                        <span>
                                                            <i class="fe fe-check-circle"></i> Active
                                                        </span>
                                                    @elseif ($beneficiary->status == 'pending')
                                                        <span>
                                                            <i class="fe fe-clock"></i> Pending
                                                        </span>
                                                    @elseif ($beneficiary->status == 'rejected')
                                                        <span>
                                                            <i class="fe fe-x-circle"></i> Rejected
                                                        </span>
                                                    @else
                                                        <span>
                                                            <i class="fe fe-slash"></i> Inactive
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-9">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Full Name</label>
                                                        <div>{{ $beneficiary->fullname }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Gender</label>
                                                        <div>{{ $beneficiary->gender }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Date of
                                                            Birth</label>
                                                        <div>
                                                            {{ $beneficiary->date_of_birth ? date('d-m-Y', strtotime($beneficiary->date_of_birth)) : 'N/A' }}
                                                            @if ($beneficiary->date_of_birth)
                                                                <span
                                                                    class="text-muted">({{ \Carbon\Carbon::parse($beneficiary->date_of_birth)->age }}
                                                                    years)</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Registered
                                                            Facility</label>
                                                        <div>
                                                            @if ($beneficiary->facility)
                                                                <div class="fw-semibold">
                                                                    {{ $beneficiary->facility->name }}</div>
                                                                <small
                                                                    class="text-muted">{{ $beneficiary->facility->lga }},
                                                                    {{ $beneficiary->facility->ward }}</small>
                                                                @if ($beneficiary->facility->type)
                                                                    <div><span
                                                                            class="badge bg-info mt-1">{{ $beneficiary->facility->type }}</span>
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <span class="text-danger">No facility assigned</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Phone
                                                            Number</label>
                                                        <div>{{ $beneficiary->phone_no ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Email</label>
                                                        <div>{{ $beneficiary->email ?? 'N/A' }}</div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Registration
                                                            Status</label>
                                                        <div>
                                                            @if ($beneficiary->status == 'active')
                                                                <span>
                                                                    <i class="fe fe-check-circle"></i> Active
                                                                </span>
                                                            @elseif ($beneficiary->status == 'pending')
                                                                <span>
                                                                    <i class="fe fe-clock"></i> Pending
                                                                </span>
                                                            @elseif ($beneficiary->status == 'rejected')
                                                                <span>
                                                                    <i class="fe fe-x-circle"></i> Rejected
                                                                </span>
                                                            @else
                                                                <span>
                                                                    <i class="fe fe-slash"></i> Inactive
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Address</label>
                                                        <div>{{ $beneficiary->contact_address ?? 'N/A' }}</div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">ID Type</label>
                                                        <div>{{ $beneficiary->id_type ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">ID Number</label>
                                                        <div>{{ $beneficiary->id_no ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">NIN</label>
                                                        <div>{{ $beneficiary->nin ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Category</label>
                                                        <div>{{ $beneficiary->category ?? 'N/A' }}</div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Occupation</label>
                                                        <div>{{ $beneficiary->occupation ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">D.P No</label>
                                                        <div>{{ $beneficiary->dp_no ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Employer</label>
                                                        <div>{{ $beneficiary->place_of_work ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold text-muted small">Registration
                                                            Date</label>
                                                        <div>{{ date('d-m-Y', strtotime($beneficiary->created_at)) }}</div>
                                                    </div>
                                                </div>

                                                @if ($beneficiary->additional_info)
                                                    <div class="row mt-2">
                                                        <div class="col-12">
                                                            <label class="font-weight-bold text-muted small">Additional
                                                                Information</label>
                                                            <div>{{ $beneficiary->additional_info }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Beneficiary Tab -->

                            <!-- Spouse Tab -->
                            @if ($beneficiary->spouse)
                                <div class="tab-pane fade" id="spouse-section" role="tabpanel"
                                    aria-labelledby="spouse-tab">
                                    <div class="card shadow-none border">
                                        <div class="card-header" style="background-color: #006734; color: white;">
                                            <h4 class="card-title mb-0">
                                                <i class="fe fe-user-plus mr-2"></i> Spouse Information
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 text-center">
                                                    @if ($beneficiary->spouse->photo)
                                                        <img src="{{ url('storage/' . $beneficiary->spouse->photo) }}"
                                                            class="img-fluid rounded border" style="max-height: 200px;"
                                                            alt="{{ $beneficiary->spouse->first_name }} Photo">
                                                    @else
                                                        <img src="{{ asset('assets/img/avatar-placeholder.png') }}"
                                                            class="img-fluid rounded border" style="max-height: 200px;"
                                                            alt="No Photo">
                                                    @endif
                                                    <div class="mt-2">
                                                        <h4 class="mb-1">{{ $beneficiary->spouse->boschma_no }}</h4>
                                                        <span class="text-muted">Spouse</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-9">
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="font-weight-bold text-muted small">Full
                                                                Name</label>
                                                            <div>{{ $beneficiary->spouse->name ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="font-weight-bold text-muted small">Gender</label>
                                                            <div>{{ $beneficiary->spouse->gender }}</div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="font-weight-bold text-muted small">Date of
                                                                Birth</label>
                                                            <div>
                                                                {{ $beneficiary->spouse->dob ? date('d-m-Y', strtotime($beneficiary->spouse->dob)) : 'N/A' }}
                                                                @if ($beneficiary->spouse->dob)
                                                                    <span
                                                                        class="text-muted">({{ \Carbon\Carbon::parse($beneficiary->spouse->dob)->age }}
                                                                        years)</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4 mb-3">
                                                            <label class="font-weight-bold text-muted small">Phone
                                                                Number</label>
                                                            <div>{{ $beneficiary->spouse->phone ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="font-weight-bold text-muted small">Email</label>
                                                            <div>{{ $beneficiary->spouse->email ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="font-weight-bold text-muted small">NIN</label>
                                                            <div>{{ $beneficiary->spouse->nin ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Spouse Tab -->
                            @endif

                            <!-- Children Tab -->
                            @if ($beneficiary->children && $beneficiary->children->count() > 0)
                                <div class="tab-pane fade" id="children-section" role="tabpanel"
                                    aria-labelledby="children-tab">
                                    <div class="card shadow-none border">
                                        <div class="card-header" style="background-color: #006734; color: white;">
                                            <h4 class="card-title mb-0">
                                                <i class="fe fe-users mr-2"></i> Children Information
                                                ({{ $beneficiary->children->count() }})
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($beneficiary->children as $child)
                                                    <div class="col-md-6 mb-4">
                                                        <div class="card shadow-none border">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-4 text-center">
                                                                        @if ($child->photo)
                                                                            <img src="{{ url('storage/' . $child->photo) }}"
                                                                                class="img-fluid rounded border"
                                                                                style="max-height: 150px;"
                                                                                alt="{{ $child->first_name }} Photo">
                                                                        @else
                                                                            <img src="{{ asset('assets/img/child-placeholder.png') }}"
                                                                                class="img-fluid rounded border"
                                                                                style="max-height: 150px;" alt="No Photo">
                                                                        @endif
                                                                        <div class="mt-2">
                                                                            <h5 class="mb-0">{{ $child->boschma_no }}
                                                                            </h5>
                                                                            <span class="text-muted small">Child</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <div class="mb-2">
                                                                            <label
                                                                                class="font-weight-bold text-muted small mb-0">Full
                                                                                Name</label>
                                                                            <div>{{ $child->name ?? 'N/A' }}</div>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <label
                                                                                class="font-weight-bold text-muted small mb-0">Gender</label>
                                                                            <div>{{ $child->gender }}</div>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <label
                                                                                class="font-weight-bold text-muted small mb-0">Date
                                                                                of Birth</label>
                                                                            <div>
                                                                                {{ $child->dob ? date('d-m-Y', strtotime($child->dob)) : 'N/A' }}
                                                                                @if ($child->dob)
                                                                                    <span
                                                                                        class="text-muted">({{ \Carbon\Carbon::parse($child->dob)->age }}
                                                                                        years)</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <label
                                                                                class="font-weight-bold text-muted small mb-0">Birth
                                                                                Certificate No.</label>
                                                                            <div>
                                                                                {{ $child->birth_certificate_no ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <label
                                                                                class="font-weight-bold text-muted small mb-0">NIN</label>
                                                                            <div>{{ $child->nin ?? 'N/A' }}</div>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <label
                                                                                class="font-weight-bold text-muted small mb-0">Birth
                                                                                Certificate</label>
                                                                            <div>
                                                                                @if ($child->birth_certificate_file)
                                                                                    <a href="{{ url('storage/' . $child->birth_certificate_file) }}"
                                                                                        target="_blank"
                                                                                        class="btn btn-sm btn-outline-primary">
                                                                                        <i class="fe fe-download"></i> View
                                                                                        Document
                                                                                    </a>
                                                                                @else
                                                                                    <span class="text-muted">No document
                                                                                        uploaded</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Children Tab -->
                            @endif
                        </div>
                        <!-- End Tab Content -->

                        <!-- Signature Section -->
                        @if ($beneficiary->signature)
                            <div class="card shadow-none border mt-4">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="fe fe-edit-3 mr-2"></i> Declaration and Authorization
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="font-weight-bold text-muted">Beneficiary Signature</label>
                                            <div class="border p-3 bg-light mb-3">
                                                <img src="{{ url('storage/' . $beneficiary->signature) }}"
                                                    class="img-fluid" alt="Signature">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="font-weight-bold text-muted">Signed On</label>
                                            <div class="border p-3 bg-light">
                                                {{ $beneficiary->signature_date ? date('d-m-Y', strtotime($beneficiary->signature_date)) : date('d-m-Y', strtotime($beneficiary->created_at)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Staff Tracking Information -->
                        <div class="card shadow-none border mt-4">
                            <div class="card-header" style="background-color: #f8f9fa;">
                                <h4 class="card-title mb-0" style="color: #01542B;">
                                    <i class="fe fe-activity mr-2"></i> Record Tracking
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="font-weight-bold text-muted d-block">
                                                <i class="fe fe-user"></i> Created By
                                            </label>
                                            @if ($beneficiary->creator)
                                                <div class="d-flex align-items-center mt-2">
                                                    <div class="avatar avatar-sm mr-2"
                                                        style="background-color: #01542B; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            class="font-weight-bold">{{ strtoupper(substr($beneficiary->creator->fullname, 0, 2)) }}</span>
                                                    </div>
                                                    <div>
                                                        <strong
                                                            style="color: #01542B;">{{ $beneficiary->creator->fullname }}</strong>
                                                        <small
                                                            class="d-block text-muted">{{ $beneficiary->creator->email }}</small>
                                                        <small class="d-block text-muted">
                                                            <i class="fe fe-calendar"></i>
                                                            {{ $beneficiary->created_at->format('M d, Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Not recorded</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="font-weight-bold text-muted d-block">
                                                <i class="fe fe-check-circle"></i> Submitted By
                                            </label>
                                            @if ($beneficiary->submitter)
                                                <div class="d-flex align-items-center mt-2">
                                                    <div class="avatar avatar-sm mr-2"
                                                        style="background-color: #28a745; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            class="font-weight-bold">{{ strtoupper(substr($beneficiary->submitter->fullname, 0, 2)) }}</span>
                                                    </div>
                                                    <div>
                                                        <strong
                                                            class="text-success">{{ $beneficiary->submitter->fullname }}</strong>
                                                        <small
                                                            class="d-block text-muted">{{ $beneficiary->submitter->email }}</small>
                                                        <small class="d-block text-muted">Finalized enrollment</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Not yet submitted</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="font-weight-bold text-muted d-block">
                                                <i class="fe fe-edit"></i> Last Updated By
                                            </label>
                                            @if ($beneficiary->updater)
                                                <div class="d-flex align-items-center mt-2">
                                                    <div class="avatar avatar-sm mr-2"
                                                        style="background-color: #17a2b8; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            class="font-weight-bold">{{ strtoupper(substr($beneficiary->updater->fullname, 0, 2)) }}</span>
                                                    </div>
                                                    <div>
                                                        <strong
                                                            class="text-info">{{ $beneficiary->updater->fullname }}</strong>
                                                        <small
                                                            class="d-block text-muted">{{ $beneficiary->updater->email }}</small>
                                                        <small class="d-block text-muted">
                                                            <i class="fe fe-clock"></i>
                                                            {{ $beneficiary->updated_at->format('M d, Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Not recorded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Tab -->
                        <div class="tab-pane fade" id="contributions-section" role="tabpanel"
                            aria-labelledby="contributions-tab">
                            <div class="card shadow-none border">
                                <div class="card-header bg-light border-bottom">
                                    <h4 class="card-title mb-0" style="color: #01542B; font-weight: 600;">
                                        <i class="fe fe-dollar-sign mr-2"></i> Contribution History
                                    </h4>
                                </div>
                                <div class="card-body">
                                    @if ($beneficiary->contributions->count() > 0)
                                        <div class="alert alert-info">
                                            <strong>Total Contributions:</strong>
                                            {{ $beneficiary->contributions->count() }} records |
                                            <strong>Total Contributed:</strong>
                                            ₦{{ number_format($beneficiary->contributions->sum('contributed'), 2) }}
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="50">#</th>
                                                        <th>Period</th>
                                                        <th>Salary Amount</th>
                                                        <th>Contributed (3.5%)</th>
                                                        <th>Status</th>
                                                        <th>Date Added</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($beneficiary->contributions as $contribution)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>
                                                                <strong>{{ $contribution->period }}</strong>
                                                            </td>
                                                            <td>₦{{ number_format($contribution->amount, 2) }}</td>
                                                            <td>
                                                                <span class="text-success">
                                                                    <i class="fe fe-trending-up"></i>
                                                                    <strong>₦{{ number_format($contribution->contributed, 2) }}</strong>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($contribution->status)
                                                                    <span class="text-success">
                                                                        <i class="fe fe-check-circle"></i>
                                                                        <strong>Active</strong>
                                                                    </span>
                                                                @else
                                                                    <span class="text-danger">
                                                                        <i class="fe fe-x-circle"></i>
                                                                        <strong>Inactive</strong>
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $contribution->created_at->format('M d, Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-light">
                                                    <tr>
                                                        <th colspan="3" class="text-right">Total:</th>
                                                        <th>
                                                            <span class="text-success">
                                                                <strong>₦{{ number_format($beneficiary->contributions->sum('contributed'), 2) }}</strong>
                                                            </span>
                                                        </th>
                                                        <th colspan="2"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fe fe-inbox"
                                                style="font-size: 3rem; opacity: 0.3; color: #01542B;"></i>
                                            <p class="text-muted mt-3">No contribution records found for this beneficiary.
                                            </p>
                                            <small class="text-muted">Contributions are linked via DP No:
                                                <strong>{{ $beneficiary->dp_no }}</strong></small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('beneficiaries.index') }}" class="btn btn-light btn-lg">
                                <i class="fe fe-arrow-left"></i> Back to List
                            </a>
                            <a href="{{ route('beneficiaries.edit', $beneficiary->id) }}" class="btn btn-primary btn-lg">
                                <i class="fe fe-edit"></i> Edit Beneficiary
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Manual tab switching for main tabs
            $('#beneficiary-tab, #spouse-tab, #children-tab, #contributions-tab').on('click', function(e) {
                e.preventDefault();

                // Remove active class from all MAIN tabs
                $('#beneficiary-tab, #spouse-tab, #children-tab, #contributions-tab').removeClass('active');
                $('#beneficiary-section, #spouse-section, #children-section, #contributions-section')
                    .removeClass('show active');

                // Add active class to clicked tab
                $(this).addClass('active');

                // Show the corresponding tab content
                const target = $(this).attr('href');
                $(target).addClass('show active');
            });
        });

        function changeStatus(status) {
            const statusLabels = {
                'active': 'Active',
                'pending': 'Pending',
                'inactive': 'Inactive',
                'rejected': 'Rejected'
            };

            if (confirm(`Are you sure you want to mark this beneficiary as ${statusLabels[status]}?`)) {
                document.getElementById('status-input').value = status;
                document.getElementById('status-change-form').submit();
            }
        }
    </script>

    <style>
        /* Fix tab hover visibility */
        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
            color: #01542B !important;
            border-color: #dee2e6 #dee2e6 #f8f9fa;
        }

        .nav-tabs .nav-link.active {
            color: #01542B !important;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        .nav-tabs .nav-link {
            color: #495057;
            transition: all 0.2s ease-in-out;
        }

        .nav-tabs .nav-link:focus {
            color: #01542B !important;
        }
    </style>

    <script>
        // Program conversion preview
        document.addEventListener('DOMContentLoaded', function() {
            const programSelect = document.getElementById('new_program_id');
            const previewDiv = document.getElementById('newIdPreview');
            const previewSpan = document.getElementById('previewId');
            const skipReservedCheckbox = document.getElementById('skip_reserved');
            const sequenceNumber = '{{ str_pad($beneficiary->sequence_number ?? 0, 6, '0', STR_PAD_LEFT) }}';

            // Store program formats
            const programFormats = {
                @foreach ($programs ?? [] as $program)
                    '{{ $program->id }}': '{{ $program->format }}',
                @endforeach
            };

            function updatePreview() {
                const selectedProgramId = programSelect ? programSelect.value : '';
                const skipReserved = skipReservedCheckbox ? skipReservedCheckbox.checked : false;

                if (selectedProgramId && programFormats[selectedProgramId]) {
                    if (skipReserved) {
                        // Show message that new sequence will be assigned
                        previewSpan.innerHTML = programFormats[selectedProgramId] +
                            '<strong>[Next Available]</strong>';
                    } else {
                        const newBoschmaNo = programFormats[selectedProgramId] + sequenceNumber;
                        previewSpan.textContent = newBoschmaNo;
                    }
                    previewDiv.style.display = 'block';
                } else {
                    previewDiv.style.display = 'none';
                }
            }

            if (programSelect) {
                programSelect.addEventListener('change', updatePreview);
            }

            if (skipReservedCheckbox) {
                skipReservedCheckbox.addEventListener('change', updatePreview);
            }
        });
    </script>
@endsection
