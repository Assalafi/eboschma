@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h6 class="main-content-label mb-1">Beneficiary Enrollment Form</h6>
                                <p class="text-muted card-sub-title">Register a new beneficiary and their dependents</p>
                            </div>
                            <div>
                                <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-primary">
                                    <i class="fe fe-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('beneficiaries.store') }}" method="POST" enctype="multipart/form-data"
                            class="form-horizontal" id="beneficiaryForm">
                            @csrf

                            <!-- Hidden fields for tracking -->
                            <input type="hidden" name="beneficiary_id" id="beneficiary_id"
                                value="{{ old('beneficiary_id') }}">
                            <input type="hidden" name="status" id="status" value="In Progress">

                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="beneficiary-tab" data-bs-toggle="tab"
                                        href="#beneficiary-section" role="tab">
                                        <i class="fe fe-user"></i> Beneficiary Information
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="spouse-tab" data-bs-toggle="tab" href="#spouse-section"
                                        role="tab">
                                        <i class="fe fe-users"></i> Spouse Information
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="children-tab" data-bs-toggle="tab" href="#children-section"
                                        role="tab">
                                        <i class="fe fe-users"></i> Children Information
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content mt-3">
                                <!-- Beneficiary Tab -->
                                <div class="tab-pane fade show active" id="beneficiary-section" role="tabpanel">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">Beneficiary Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- BOSCHMA ID Alert -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <div class="alert alert-info py-2 px-3"
                                                        style="background-color: #e8f5e9; border-left: 4px solid #006734;">
                                                        <i class="fe fe-info"></i> <strong>BOSCHMA ID:</strong>
                                                        Auto-Generated - Will be automatically assigned upon submission
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden Program ID from Verification -->
                                            <input type="hidden" name="program_id" id="program_id"
                                                value="{{ old('program_id') }}">

                                            <!-- Beneficiary Sub-Tabs -->
                                            <ul class="nav nav-pills mb-3" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="basic-info-tab" data-bs-toggle="pill"
                                                        href="#basic-info" role="tab">Basic Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="location-tab" data-bs-toggle="pill"
                                                        href="#location-info" role="tab">Location</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="demographic-tab" data-bs-toggle="pill"
                                                        href="#demographic-info" role="tab">Demographic</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="other-tab" data-bs-toggle="pill"
                                                        href="#other-info" role="tab">Other Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="photo-tab" data-bs-toggle="pill"
                                                        href="#photo-info" role="tab">Photo</a>
                                                </li>
                                            </ul>

                                            <!-- Sub-Tab Content -->
                                            <div class="tab-content">
                                                <!-- Basic Information Tab -->
                                                <div class="tab-pane fade show active" id="basic-info" role="tabpanel">

                                                    <div class="row">
                                                        <!-- Left Column -->
                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Facility: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="facility_id"
                                                                        required>
                                                                        <option value="">Select Facility</option>
                                                                        @foreach ($facilities as $facility)
                                                                            <option value="{{ $facility->id }}"
                                                                                {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                                                                {{ $facility->name }} -
                                                                                {{ $facility->lga }},
                                                                                {{ $facility->ward }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Full Name: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="fullname" value="{{ old('fullname') }}"
                                                                        required placeholder="Enter full name">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Gender: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <div class="form-check form-check-inline mt-2">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="gender" id="male" value="Male"
                                                                            {{ old('gender') == 'Male' ? 'checked' : '' }}
                                                                            required>
                                                                        <label class="form-check-label"
                                                                            for="male">Male</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="gender" id="female" value="Female"
                                                                            {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="female">Female</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Date of Birth: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <input type="date" class="form-control"
                                                                        name="date_of_birth"
                                                                        value="{{ old('date_of_birth') }}" required>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Place of Birth:</label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="place_of_birth"
                                                                        value="{{ old('place_of_birth') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Right Column -->
                                                        <div class="col-md-6">
                                                            <div class="row mb-3 alt-facility-section"
                                                                style="display: none;">
                                                                <label class="col-md-4 form-label">Alternative
                                                                    Facility:</label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="alt_facility_id"
                                                                        id="alt_facility_id">
                                                                        <option value="">Select Alternative Facility
                                                                        </option>
                                                                        @foreach ($facilities as $facility)
                                                                            <option value="{{ $facility->id }}"
                                                                                {{ old('alt_facility_id') == $facility->id ? 'selected' : '' }}>
                                                                                {{ $facility->name }} -
                                                                                {{ $facility->lga }},
                                                                                {{ $facility->ward }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Phone Number: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <input type="tel" class="form-control"
                                                                        name="phone_no" value="{{ old('phone_no') }}"
                                                                        required>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Email:</label>
                                                                <div class="col-md-8">
                                                                    <input type="email" class="form-control"
                                                                        name="email" value="{{ old('email') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Contact Address: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <textarea class="form-control" name="contact_address" rows="2" required>{{ old('contact_address') }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Basic Info Tab -->

                                                <!-- Location Information Tab -->
                                                <div class="tab-pane fade" id="location-info" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">LGA: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="lga" required>
                                                                        <option value="">Select LGA</option>
                                                                        <option value="Abadam"
                                                                            {{ old('lga') == 'Abadam' ? 'selected' : '' }}>
                                                                            Abadam</option>
                                                                        <option value="Askira/Uba"
                                                                            {{ old('lga') == 'Askira/Uba' ? 'selected' : '' }}>
                                                                            Askira/Uba
                                                                        </option>
                                                                        <option value="Bama"
                                                                            {{ old('lga') == 'Bama' ? 'selected' : '' }}>
                                                                            Bama</option>
                                                                        <option value="Bayo"
                                                                            {{ old('lga') == 'Bayo' ? 'selected' : '' }}>
                                                                            Bayo</option>
                                                                        <option value="Biu"
                                                                            {{ old('lga') == 'Biu' ? 'selected' : '' }}>
                                                                            Biu</option>
                                                                        <option value="Chibok"
                                                                            {{ old('lga') == 'Chibok' ? 'selected' : '' }}>
                                                                            Chibok</option>
                                                                        <option value="Damboa"
                                                                            {{ old('lga') == 'Damboa' ? 'selected' : '' }}>
                                                                            Damboa</option>
                                                                        <option value="Dikwa"
                                                                            {{ old('lga') == 'Dikwa' ? 'selected' : '' }}>
                                                                            Dikwa</option>
                                                                        <option value="Gubio"
                                                                            {{ old('lga') == 'Gubio' ? 'selected' : '' }}>
                                                                            Gubio</option>
                                                                        <option value="Guzamala"
                                                                            {{ old('lga') == 'Guzamala' ? 'selected' : '' }}>
                                                                            Guzamala
                                                                        </option>
                                                                        <option value="Gwoza"
                                                                            {{ old('lga') == 'Gwoza' ? 'selected' : '' }}>
                                                                            Gwoza</option>
                                                                        <option value="Hawul"
                                                                            {{ old('lga') == 'Hawul' ? 'selected' : '' }}>
                                                                            Hawul</option>
                                                                        <option value="Jere"
                                                                            {{ old('lga') == 'Jere' ? 'selected' : '' }}>
                                                                            Jere</option>
                                                                        <option value="Kaga"
                                                                            {{ old('lga') == 'Kaga' ? 'selected' : '' }}>
                                                                            Kaga</option>
                                                                        <option value="Kala/Balge"
                                                                            {{ old('lga') == 'Kala/Balge' ? 'selected' : '' }}>
                                                                            Kala/Balge
                                                                        </option>
                                                                        <option value="Konduga"
                                                                            {{ old('lga') == 'Konduga' ? 'selected' : '' }}>
                                                                            Konduga
                                                                        </option>
                                                                        <option value="Kukawa"
                                                                            {{ old('lga') == 'Kukawa' ? 'selected' : '' }}>
                                                                            Kukawa</option>
                                                                        <option value="Kwaya Kusar"
                                                                            {{ old('lga') == 'Kwaya Kusar' ? 'selected' : '' }}>
                                                                            Kwaya Kusar
                                                                        </option>
                                                                        <option value="Mafa"
                                                                            {{ old('lga') == 'Mafa' ? 'selected' : '' }}>
                                                                            Mafa</option>
                                                                        <option value="Magumeri"
                                                                            {{ old('lga') == 'Magumeri' ? 'selected' : '' }}>
                                                                            Magumeri
                                                                        </option>
                                                                        <option value="Maiduguri"
                                                                            {{ old('lga') == 'Maiduguri' ? 'selected' : '' }}>
                                                                            Maiduguri
                                                                        </option>
                                                                        <option value="Marte"
                                                                            {{ old('lga') == 'Marte' ? 'selected' : '' }}>
                                                                            Marte</option>
                                                                        <option value="Mobbar"
                                                                            {{ old('lga') == 'Mobbar' ? 'selected' : '' }}>
                                                                            Mobbar</option>
                                                                        <option value="Monguno"
                                                                            {{ old('lga') == 'Monguno' ? 'selected' : '' }}>
                                                                            Monguno
                                                                        </option>
                                                                        <option value="Ngala"
                                                                            {{ old('lga') == 'Ngala' ? 'selected' : '' }}>
                                                                            Ngala</option>
                                                                        <option value="Nganzai"
                                                                            {{ old('lga') == 'Nganzai' ? 'selected' : '' }}>
                                                                            Nganzai
                                                                        </option>
                                                                        <option value="Shani"
                                                                            {{ old('lga') == 'Shani' ? 'selected' : '' }}>
                                                                            Shani</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">State: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="state" required>
                                                                        <option value="">Select State</option>
                                                                        <option value="Abia"
                                                                            {{ old('state') == 'Abia' ? 'selected' : '' }}>
                                                                            Abia</option>
                                                                        <option value="Adamawa"
                                                                            {{ old('state') == 'Adamawa' ? 'selected' : '' }}>
                                                                            Adamawa
                                                                        </option>
                                                                        <option value="Akwa Ibom"
                                                                            {{ old('state') == 'Akwa Ibom' ? 'selected' : '' }}>
                                                                            Akwa Ibom
                                                                        </option>
                                                                        <option value="Anambra"
                                                                            {{ old('state') == 'Anambra' ? 'selected' : '' }}>
                                                                            Anambra
                                                                        </option>
                                                                        <option value="Bauchi"
                                                                            {{ old('state') == 'Bauchi' ? 'selected' : '' }}>
                                                                            Bauchi
                                                                        </option>
                                                                        <option value="Bayelsa"
                                                                            {{ old('state') == 'Bayelsa' ? 'selected' : '' }}>
                                                                            Bayelsa
                                                                        </option>
                                                                        <option value="Benue"
                                                                            {{ old('state') == 'Benue' ? 'selected' : '' }}>
                                                                            Benue</option>
                                                                        <option value="Borno"
                                                                            {{ old('state') == 'Borno' ? 'selected' : '' }}>
                                                                            Borno</option>
                                                                        <option value="Cross River"
                                                                            {{ old('state') == 'Cross River' ? 'selected' : '' }}>
                                                                            Cross
                                                                            River</option>
                                                                        <option value="Delta"
                                                                            {{ old('state') == 'Delta' ? 'selected' : '' }}>
                                                                            Delta</option>
                                                                        <option value="Ebonyi"
                                                                            {{ old('state') == 'Ebonyi' ? 'selected' : '' }}>
                                                                            Ebonyi
                                                                        </option>
                                                                        <option value="Edo"
                                                                            {{ old('state') == 'Edo' ? 'selected' : '' }}>
                                                                            Edo</option>
                                                                        <option value="Ekiti"
                                                                            {{ old('state') == 'Ekiti' ? 'selected' : '' }}>
                                                                            Ekiti</option>
                                                                        <option value="Enugu"
                                                                            {{ old('state') == 'Enugu' ? 'selected' : '' }}>
                                                                            Enugu</option>
                                                                        <option value="FCT"
                                                                            {{ old('state') == 'FCT' ? 'selected' : '' }}>
                                                                            FCT</option>
                                                                        <option value="Gombe"
                                                                            {{ old('state') == 'Gombe' ? 'selected' : '' }}>
                                                                            Gombe</option>
                                                                        <option value="Imo"
                                                                            {{ old('state') == 'Imo' ? 'selected' : '' }}>
                                                                            Imo</option>
                                                                        <option value="Jigawa"
                                                                            {{ old('state') == 'Jigawa' ? 'selected' : '' }}>
                                                                            Jigawa
                                                                        </option>
                                                                        <option value="Kaduna"
                                                                            {{ old('state') == 'Kaduna' ? 'selected' : '' }}>
                                                                            Kaduna
                                                                        </option>
                                                                        <option value="Kano"
                                                                            {{ old('state') == 'Kano' ? 'selected' : '' }}>
                                                                            Kano</option>
                                                                        <option value="Katsina"
                                                                            {{ old('state') == 'Katsina' ? 'selected' : '' }}>
                                                                            Katsina
                                                                        </option>
                                                                        <option value="Kebbi"
                                                                            {{ old('state') == 'Kebbi' ? 'selected' : '' }}>
                                                                            Kebbi</option>
                                                                        <option value="Kogi"
                                                                            {{ old('state') == 'Kogi' ? 'selected' : '' }}>
                                                                            Kogi</option>
                                                                        <option value="Kwara"
                                                                            {{ old('state') == 'Kwara' ? 'selected' : '' }}>
                                                                            Kwara</option>
                                                                        <option value="Lagos"
                                                                            {{ old('state') == 'Lagos' ? 'selected' : '' }}>
                                                                            Lagos</option>
                                                                        <option value="Nasarawa"
                                                                            {{ old('state') == 'Nasarawa' ? 'selected' : '' }}>
                                                                            Nasarawa
                                                                        </option>
                                                                        <option value="Niger"
                                                                            {{ old('state') == 'Niger' ? 'selected' : '' }}>
                                                                            Niger</option>
                                                                        <option value="Ogun"
                                                                            {{ old('state') == 'Ogun' ? 'selected' : '' }}>
                                                                            Ogun</option>
                                                                        <option value="Ondo"
                                                                            {{ old('state') == 'Ondo' ? 'selected' : '' }}>
                                                                            Ondo</option>
                                                                        <option value="Osun"
                                                                            {{ old('state') == 'Osun' ? 'selected' : '' }}>
                                                                            Osun</option>
                                                                        <option value="Oyo"
                                                                            {{ old('state') == 'Oyo' ? 'selected' : '' }}>
                                                                            Oyo</option>
                                                                        <option value="Plateau"
                                                                            {{ old('state') == 'Plateau' ? 'selected' : '' }}>
                                                                            Plateau
                                                                        </option>
                                                                        <option value="Rivers"
                                                                            {{ old('state') == 'Rivers' ? 'selected' : '' }}>
                                                                            Rivers
                                                                        </option>
                                                                        <option value="Sokoto"
                                                                            {{ old('state') == 'Sokoto' ? 'selected' : '' }}>
                                                                            Sokoto
                                                                        </option>
                                                                        <option value="Taraba"
                                                                            {{ old('state') == 'Taraba' ? 'selected' : '' }}>
                                                                            Taraba
                                                                        </option>
                                                                        <option value="Yobe"
                                                                            {{ old('state') == 'Yobe' ? 'selected' : '' }}>
                                                                            Yobe</option>
                                                                        <option value="Zamfara"
                                                                            {{ old('state') == 'Zamfara' ? 'selected' : '' }}>
                                                                            Zamfara
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Nationality: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="nationality"
                                                                        required>
                                                                        <option value="">Select Nationality</option>
                                                                        <option value="Nigerian"
                                                                            {{ old('nationality') == 'Nigerian' ? 'selected' : '' }}>
                                                                            Nigerian</option>
                                                                        <option value="Others"
                                                                            {{ old('nationality') == 'Others' ? 'selected' : '' }}>
                                                                            Others
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Location Tab -->

                                                <!-- Demographic Information Tab -->
                                                <div class="tab-pane fade" id="demographic-info" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Marital Status: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="marital_status"
                                                                        required>
                                                                        <option value="">Select status</option>
                                                                        <option value="Single"
                                                                            {{ old('marital_status') == 'Single' ? 'selected' : '' }}>
                                                                            Single</option>
                                                                        <option value="Married"
                                                                            {{ old('marital_status') == 'Married' ? 'selected' : '' }}>
                                                                            Married</option>
                                                                        <option value="Widow"
                                                                            {{ old('marital_status') == 'Widow' ? 'selected' : '' }}>
                                                                            Widow
                                                                        </option>
                                                                        <option value="Divorce"
                                                                            {{ old('marital_status') == 'Divorce' ? 'selected' : '' }}>
                                                                            Divorce</option>
                                                                        <option value="Others"
                                                                            {{ old('marital_status') == 'Others' ? 'selected' : '' }}>
                                                                            Others</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Ethnicity:</label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="ethnicity" required>
                                                                        <option value="">Select Ethnicity</option>
                                                                        @foreach (\App\Models\Beneficiary::getEthnicityOptions() as $value => $label)
                                                                            <option value="{{ $value }}"
                                                                                {{ old('ethnicity') == $value ? 'selected' : '' }}>
                                                                                {{ $label }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Religion: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="religion" required>
                                                                        <option value="">Select Religion</option>
                                                                        <option value="Christianity"
                                                                            {{ old('religion') == 'Christianity' ? 'selected' : '' }}>
                                                                            Christianity</option>
                                                                        <option value="Islam"
                                                                            {{ old('religion') == 'Islam' ? 'selected' : '' }}>
                                                                            Islam
                                                                        </option>
                                                                        <option value="Traditional"
                                                                            {{ old('religion') == 'Traditional' ? 'selected' : '' }}>
                                                                            Traditional</option>
                                                                        <option value="Others"
                                                                            {{ old('religion') == 'Others' ? 'selected' : '' }}>
                                                                            Others
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Category: <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="col-md-8">
                                                                    @foreach ($beneficiaryCategories as $cat)
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio"
                                                                                name="category"
                                                                                id="cat_{{ $loop->index }}"
                                                                                value="{{ $cat->name }}"
                                                                                {{ old('category') == $cat->name ? 'checked' : '' }}>
                                                                            <label class="form-check-label"
                                                                                for="cat_{{ $loop->index }}">{{ $cat->name }}</label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Demographic Tab -->

                                                <!-- Other Information Tab -->
                                                <div class="tab-pane fade" id="other-info" role="tabpanel">

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Occupation:</label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="occupation"
                                                                        value="{{ old('occupation') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">D.P No:</label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="dp_no" value="{{ old('dp_no') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">ID Type:</label>
                                                                <div class="col-md-8">
                                                                    <select class="form-select" name="id_type">
                                                                        <option value="">Select ID Type</option>
                                                                        <option value="Driver License"
                                                                            {{ old('id_type') == 'Driver License' ? 'selected' : '' }}>
                                                                            Driver License</option>
                                                                        <option value="NIMC"
                                                                            {{ old('id_type') == 'NIMC' ? 'selected' : '' }}>
                                                                            NIMC</option>
                                                                        <option value="Voters Card"
                                                                            {{ old('id_type') == 'Voters Card' ? 'selected' : '' }}>
                                                                            Voters
                                                                            Card</option>
                                                                        <option value="International Passport"
                                                                            {{ old('id_type') == 'International Passport' ? 'selected' : '' }}>
                                                                            International Passport</option>
                                                                        <option value="Others"
                                                                            {{ old('id_type') == 'Others' ? 'selected' : '' }}>
                                                                            Others
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">ID No:</label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="id_no" value="{{ old('id_no') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">NIN:</label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="nin" value="{{ old('nin') }}"
                                                                        maxlength="11" placeholder="Enter 11-digit NIN">
                                                                    <small class="text-muted">National Identification
                                                                        Number (11
                                                                        digits)</small>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Place of Work:</label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="place_of_work"
                                                                        value="{{ old('place_of_work') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Employment Date:</label>
                                                                <div class="col-md-8">
                                                                    <input type="date" class="form-control"
                                                                        name="date_of_employment"
                                                                        value="{{ old('date_of_employment') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-4 form-label">Retirement Date:</label>
                                                                <div class="col-md-8">
                                                                    <input type="date" class="form-control"
                                                                        name="date_of_retirement"
                                                                        value="{{ old('date_of_retirement') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Other Info Tab -->

                                                <!-- Photo Tab -->
                                                <div class="tab-pane fade" id="photo-info" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="card">
                                                                <div class="card-body text-center">
                                                                    <h6>Beneficiary Photo</h6>
                                                                    <div class="mb-3 mt-3">
                                                                        <input type="file" class="dropify-create"
                                                                            name="beneficiary_photo" data-height="200"
                                                                            data-allowed-file-extensions="jpg jpeg png"
                                                                            data-max-file-size="2M">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Photo Tab -->
                                            </div>
                                            <!-- End Sub-Tab Content -->

                                            <!-- Save Button for Beneficiary Info -->
                                            <div class="card-footer bg-light mt-3">
                                                <button type="button" class="btn btn-success" id="saveBeneficiaryInfo"
                                                    style="background-color: #006734; border-color: #006734;">
                                                    <i class="fe fe-save"></i> Save Beneficiary Information
                                                </button>
                                                <span class="ms-2 text-success" id="beneficiaryInfoSaved"
                                                    style="display: none;">
                                                    <i class="fe fe-check-circle"></i> Saved
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Beneficiary Tab -->

                                <!-- Spouse Tab -->
                                <div class="tab-pane fade" id="spouse-section" role="tabpanel">
                                    <div class="card dependant-section">
                                        <div class="card-header bg-light">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="hasSpouse"
                                                    name="has_spouse" value="1"
                                                    {{ old('has_spouse') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="hasSpouse">
                                                    <h5 class="mb-0">Dependents: Spouse</h5>
                                                </label>
                                            </div>
                                        </div>
                                        <div id="spouseSection" class="card shadow-none border mb-4"
                                            style="display: none;">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">BOSCHMA ID:</label>
                                                        <div class="col-md-9">
                                                            <div class="alert alert-info py-2 px-3 mb-0 small"
                                                                style="background-color: #e8f5e9; border-left: 3px solid #006734;">
                                                                <i class="fe fe-info"></i> <strong>Auto-Generated (Suffix:
                                                                    A)</strong>
                                                                <p class="mb-0 small">Will be Primary ID + 'A'</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3 spouse-facility-checkbox" style="display: none;">
                                                        <label class="col-md-3 form-label">Facility:</label>
                                                        <div class="col-md-9">
                                                            <div class="custom-control custom-checkbox mt-2">
                                                                <input type="checkbox"
                                                                    class="custom-control-input spouse-field"
                                                                    id="use_alt_facility_spouse"
                                                                    name="use_alt_facility_spouse" value="1">
                                                                <label class="custom-control-label"
                                                                    for="use_alt_facility_spouse">
                                                                    Use Alternative Facility
                                                                </label>
                                                            </div>
                                                            <small class="text-muted">Check to use alternative facility,
                                                                otherwise
                                                                main facility will be used</small>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">Spouse Name:</label>
                                                        <div class="col-md-9">
                                                            <input type="text" class="form-control spouse-field"
                                                                name="spouse_name" value="{{ old('spouse_name') }}">
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">Gender:</label>
                                                        <div class="col-md-9">
                                                            <div class="form-check form-check-inline mt-2">
                                                                <input class="form-check-input spouse-field"
                                                                    type="radio" name="spouse_gender" id="spouse_male"
                                                                    value="Male"
                                                                    {{ old('spouse_gender') == 'Male' ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="spouse_male">Male</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input spouse-field"
                                                                    type="radio" name="spouse_gender"
                                                                    id="spouse_female" value="Female"
                                                                    {{ old('spouse_gender') == 'Female' ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="spouse_female">Female</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">Date of Birth:</label>
                                                        <div class="col-md-9">
                                                            <input type="date" class="form-control spouse-field"
                                                                name="spouse_dob" value="{{ old('spouse_dob') }}">
                                                            <small class="text-muted">Format: dd-mm-yyyy</small>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">Phone No:</label>
                                                        <div class="col-md-9">
                                                            <input type="text" class="form-control spouse-field"
                                                                name="spouse_phone" value="{{ old('spouse_phone') }}">
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">Email:</label>
                                                        <div class="col-md-9">
                                                            <input type="email" class="form-control spouse-field"
                                                                name="spouse_email" value="{{ old('spouse_email') }}">
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <label class="col-md-3 form-label">NIN:</label>
                                                        <div class="col-md-9">
                                                            <div class="input-group">
                                                                <input type="text"
                                                                    class="form-control spouse-field nin-input"
                                                                    id="spouse_nin" name="spouse_nin"
                                                                    value="{{ old('spouse_nin') }}" maxlength="11"
                                                                    placeholder="Enter 11-digit NIN"
                                                                    data-person-type="spouse">
                                                                <div class="input-group-append">
                                                                    <button
                                                                        class="btn btn-outline-secondary verify-nin-btn"
                                                                        type="button" data-target="spouse_nin" disabled>
                                                                        <i class="fe fe-search"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">National Identification
                                                                Number</small>
                                                            <div id="spouse_nin_feedback" class="mt-1"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <h6>Spouse Photo</h6>
                                                            <div class="mb-3 mt-3">
                                                                <input type="file" class="dropify-create spouse-field"
                                                                    name="spouse_photo" data-height="200"
                                                                    data-allowed-file-extensions="jpg jpeg png"
                                                                    data-max-file-size="2M">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Save Button for Spouse Info -->
                                            <div class="card-footer bg-light">
                                                <button type="button" class="btn btn-success" id="saveSpouseInfo"
                                                    style="background-color: #006734; border-color: #006734;">
                                                    <i class="fe fe-save"></i> Save Spouse Information
                                                </button>
                                                <span class="ms-2 text-success" id="spouseInfoSaved"
                                                    style="display: none;">
                                                    <i class="fe fe-check-circle"></i> Saved
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Spouse Tab -->

                                <!-- Children Tab -->
                                <div class="tab-pane fade" id="children-section" role="tabpanel">
                                    <div class="card dependant-section">
                                        <div class="card-header bg-light">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="hasChildren"
                                                    name="has_children" value="1"
                                                    {{ old('has_children') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="hasChildren">
                                                    <h5 class="mb-0">Dependents: Children</h5>
                                                </label>
                                            </div>
                                        </div>
                                        <div id="childrenSection" class="card shadow-none border mb-4"
                                            style="display: none;">
                                            <div class="card-body">
                                                <p class="text-muted">You can add up to 4 children. For children below the
                                                    age of
                                                    18 years, attach a photocopy of National Population Commission Birth
                                                    Certificate.</p>

                                                <div id="childrenContainer">
                                                    <!-- Child records will be added dynamically here -->
                                                </div>

                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-outline-primary"
                                                        id="addChildBtn">
                                                        <i class="fe fe-plus-circle"></i> Add Child
                                                    </button>
                                                    <small class="text-muted ms-2">
                                                        <span id="childCount">0</span>/4 children added
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Save Button for Children Info -->
                                            <div class="card-footer bg-light">
                                                <button type="button" class="btn btn-success" id="saveChildrenInfo"
                                                    style="background-color: #006734; border-color: #006734;">
                                                    <i class="fe fe-save"></i> Save Children Information
                                                </button>
                                                <span class="ms-2 text-success" id="childrenInfoSaved"
                                                    style="display: none;">
                                                    <i class="fe fe-check-circle"></i> Saved
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Children Tab -->
                            </div>
                            <!-- End Tab Content -->

                            <!-- Final Submission (Outside Tabs) -->
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <i class="fe fe-info"></i> <strong>Final Submission:</strong> Clicking "Complete
                                    Enrollment" will finalize the registration and generate the BOSCHMA ID. Make sure all
                                    sections are saved before proceeding.
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg" id="finalSubmitBtn"
                                    style="background-color: #006734; border-color: #006734;">
                                    <i class="fe fe-check-circle"></i> Complete Enrollment & Generate BOSCHMA ID
                                </button>
                                <a href="{{ route('beneficiaries.index') }}" class="btn btn-light btn-lg">
                                    <i class="fe fe-x"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Programs data with has_dependant flag
        const programs = @json($programs);

        // Function to check if program allows dependants
        function checkProgramDependants(programId) {
            const program = programs.find(p => p.id == programId);

            if (program && !program.has_dependant) {
                // Program doesn't allow dependants - hide and disable sections
                $('#hasSpouse').prop('checked', false).prop('disabled', true).trigger('change');
                $('#hasChildren').prop('checked', false).prop('disabled', true).trigger('change');

                // Hide the checkbox containers and add warning
                $('.dependant-section').hide();

                // Hide alternative facility sections
                $('.alt-facility-section').hide();
                $('.spouse-facility-checkbox').hide();
                $('[class*="child-facility-checkbox-"]').hide();

                // Add warning message if not already present
                if ($('#dependant-warning').length === 0) {
                    const warningHtml = `
                    <div id="dependant-warning" class="alert alert-warning" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                        <i class="fe fe-alert-triangle"></i> <strong>Note:</strong> The selected program does not allow dependants (spouse and children).
                    </div>
                `;
                    $('.dependant-section').first().before(warningHtml);
                }
            } else {
                // Program allows dependants - enable sections
                $('#hasSpouse').prop('disabled', false);
                $('#hasChildren').prop('disabled', false);
                $('.dependant-section').show();
                $('#dependant-warning').remove();

                // Show alternative facility section
                $('.alt-facility-section').show();

                // Show facility checkboxes for dependants if alt facility is selected
                checkAltFacilitySelection();
            }
        }

        // Function to check if alternative facility is selected and show/hide checkboxes
        function checkAltFacilitySelection() {
            const altFacilityId = $('#alt_facility_id').val();

            if (altFacilityId) {
                // Alternative facility selected - show checkboxes
                $('.spouse-facility-checkbox').show();
                $('[class*="child-facility-checkbox-"]').show();
            } else {
                // No alternative facility - hide checkboxes
                $('.spouse-facility-checkbox').hide();
                $('[class*="child-facility-checkbox-"]').hide();
            }
        }

        // Listen to program changes
        $(document).on('change', '#program_id', function() {
            const programId = $(this).val();
            if (programId) {
                checkProgramDependants(programId);
            }
        });

        // Listen to alternative facility changes
        $(document).on('change', '#alt_facility_id', function() {
            checkAltFacilitySelection();
        });

        $(document).ready(function() {
            // Check if program is already selected (e.g., after form validation error)
            const initialProgramId = $('#program_id').val();
            if (initialProgramId) {
                checkProgramDependants(initialProgramId);
            }

            // Check if alternative facility is already selected
            const initialAltFacilityId = $('#alt_facility_id').val();
            if (initialAltFacilityId) {
                checkAltFacilitySelection();
            }

            // Trigger initial state of checkboxes
            $('#hasSpouse').trigger('change');
            $('#hasChildren').trigger('change');

            // Initialize dropify for file uploads
            function initializeDropify() {
                $('.dropify-create').dropify({
                    messages: {
                        'default': 'Drag and drop a photo here or click',
                        'replace': 'Drag and drop or click to replace',
                        'remove': 'Remove',
                        'error': 'Oops, something wrong happened.'
                    }
                });
            }

            initializeDropify();

            // Toggle spouse section
            $('#hasSpouse').change(function() {
                if ($(this).is(':checked')) {
                    $('#spouseSection').slideDown();
                    $('.spouse-field').prop('required', true);
                } else {
                    $('#spouseSection').slideUp();
                    $('.spouse-field').prop('required', false);
                }
            });

            // Toggle children section
            $('#hasChildren').change(function() {
                if ($(this).is(':checked')) {
                    $('#childrenSection').slideDown();
                } else {
                    $('#childrenSection').slideUp();
                    $('.child-field').prop('required', false);
                }
            });

            // Dynamic Child Form Handling
            let childCount = 0;
            const MAX_CHILDREN = 4;
            const childSuffixes = ['B', 'C', 'D', 'E'];

            // Add Child button click handler
            $('#addChildBtn').click(function(e) {
                e.preventDefault();
                if (childCount < MAX_CHILDREN) {
                    addChildForm(childCount);
                    childCount++;
                    updateChildCounter();

                    // Disable the button if max reached
                    if (childCount >= MAX_CHILDREN) {
                        $(this).prop('disabled', true);
                    }
                }
            });

            // Function to add a new child form
            function addChildForm(index) {
                var childHtml = '<div class="child-record mb-4" id="child-' + index + '">';
                childHtml += '<div class="d-flex justify-content-between align-items-center bg-light p-2">';
                childHtml += '<h6 class="mb-0">Child ' + (index + 1) + ' (ID Suffix: ' + childSuffixes[index] +
                    ')</h6>';
                childHtml +=
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-child" data-index="' +
                    index + '">';
                childHtml += '<i class="fe fe-trash"></i> Remove';
                childHtml += '</button>';
                childHtml += '</div>';
                childHtml += '<div class="row mt-3">';

                // Facility checkbox
                childHtml += '<div class="col-md-12">';
                childHtml += '<div class="row mb-3 child-facility-checkbox-' + index + '" style="display: none;">';
                childHtml += '<label class="col-md-2 form-label">Facility:</label>';
                childHtml += '<div class="col-md-10">';
                childHtml += '<div class="custom-control custom-checkbox mt-2">';
                childHtml +=
                    '<input type="checkbox" class="custom-control-input child-field" id="use_alt_facility_child_' +
                    index + '" name="use_alt_facility_child_' + index + '" value="1">';
                childHtml += '<label class="custom-control-label" for="use_alt_facility_child_' + index +
                    '">Use Alternative Facility</label>';
                childHtml += '</div>';
                childHtml +=
                    '<small class="text-muted">Check to use alternative facility, otherwise main facility will be used</small>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';

                // Left column - Form fields
                childHtml += '<div class="col-md-5">';
                childHtml += '<div class="row mb-3">';
                childHtml += '<label class="col-md-3 form-label">Child Full Name:</label>';
                childHtml += '<div class="col-md-9">';
                childHtml += '<input type="text" class="form-control child-field" name="child_name_[' + index +
                    ']" required>';
                childHtml += '</div>';
                childHtml += '</div>';

                childHtml += '<div class="row mb-3">';
                childHtml += '<label class="col-md-3 form-label">Gender:</label>';
                childHtml += '<div class="col-md-9">';
                childHtml += '<div class="form-check form-check-inline mt-2">';
                childHtml += '<input class="form-check-input child-field" type="radio" name="child_gender_[' +
                    index + ']" id="child' + index + '_male" value="Male">';
                childHtml += '<label class="form-check-label" for="child' + index + '_male">Male</label>';
                childHtml += '</div>';
                childHtml += '<div class="form-check form-check-inline">';
                childHtml += '<input class="form-check-input child-field" type="radio" name="child_gender_[' +
                    index + ']" id="child' + index + '_female" value="Female">';
                childHtml += '<label class="form-check-label" for="child' + index + '_female">Female</label>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';

                childHtml += '<div class="row mb-3">';
                childHtml += '<label class="col-md-3 form-label">Date of Birth:</label>';
                childHtml += '<div class="col-md-9">';
                childHtml += '<input type="date" class="form-control child-field" name="child_date_of_birth_[' +
                    index + ']">';
                childHtml += '</div>';
                childHtml += '</div>';

                childHtml += '<div class="row mb-3">';
                childHtml += '<label class="col-md-3 form-label">Birth Certificate No:</label>';
                childHtml += '<div class="col-md-9">';
                childHtml +=
                    '<input type="text" class="form-control child-field" name="child_birth_certificate_no_[' +
                    index + ']">';
                childHtml += '</div>';
                childHtml += '</div>';

                childHtml += '<div class="row mb-3">';
                childHtml += '<label class="col-md-3 form-label">NIN:</label>';
                childHtml += '<div class="col-md-9">';
                childHtml += '<div class="input-group">';
                childHtml += '<input type="text" class="form-control child-field nin-input" id="child_nin_' +
                    index + '" name="child_nin_[' + index +
                    ']" maxlength="11" placeholder="Enter 11-digit NIN" data-person-type="child" data-child-index="' +
                    index + '">';
                childHtml += '<div class="input-group-append">';
                childHtml +=
                    '<button class="btn btn-outline-secondary verify-nin-btn" type="button" data-target="child_nin_' +
                    index + '" disabled>';
                childHtml += '<i class="fe fe-search"></i>';
                childHtml += '</button>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '<small class="text-muted">National Identification Number</small>';
                childHtml += '<div id="child_nin_' + index + '_feedback" class="mt-1"></div>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';

                // Right column - Photos
                childHtml += '<div class="col-md-7">';
                childHtml += '<div class="row">';
                childHtml += '<div class="col-md-6">';
                childHtml += '<div class="card mb-3">';
                childHtml += '<div class="card-body text-center">';
                childHtml += '<h6>Child\'s Photo</h6>';
                childHtml += '<div class="mb-3 mt-3">';
                childHtml += '<input type="file" class="dropify-create child-field" name="child_photo_' + index +
                    '" data-height="180" data-allowed-file-extensions="jpg jpeg png" data-max-file-size="2M">';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '<div class="col-md-6">';
                childHtml += '<div class="card">';
                childHtml += '<div class="card-body text-center">';
                childHtml += '<h6>Birth Certificate</h6>';
                childHtml += '<div class="mb-3 mt-3">';
                childHtml +=
                    '<input type="file" class="dropify-create child-field" name="child_birth_certificate_file_' +
                    index +
                    '" data-height="180" data-allowed-file-extensions="jpg jpeg png pdf" data-max-file-size="2M">';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';
                childHtml += '</div>';

                childHtml += '</div>';
                childHtml += '</div>';

                $('#childrenContainer').append(childHtml);
                initializeDropify(); // Reinitialize dropify for the new file input

                // Check if facility checkbox should be shown for this child
                checkAltFacilitySelection();
            }

            // Handle removing a child form
            $(document).on('click', '.remove-child', function(e) {
                e.preventDefault();
                const index = $(this).data('index');
                $(`#child-${index}`).remove();
                childCount--;
                updateChildCounter();

                // Re-enable the add button
                $('#addChildBtn').prop('disabled', false);
            });

            // Update the child counter
            function updateChildCounter() {
                $('#childCount').text(childCount);
            }

            // Signature pad has been removed

            // ============================================
            // NIN VERIFICATION AND UNIQUENESS CHECK
            // ============================================
            let ninVerificationStatus = {};
            let usedNins = new Set(); // Track all NINs entered in the form

            // Handle NIN input changes
            $(document).on('input', '.nin-input', function() {
                const input = $(this);
                const value = input.val().replace(/\D/g, ''); // Only numbers
                input.val(value);

                const ninId = input.attr('id');
                const verifyBtn = $(`.verify-nin-btn[data-target="${ninId}"]`);
                const feedbackDiv = $(`#${ninId}_feedback`);

                // Clear previous status
                delete ninVerificationStatus[ninId];
                feedbackDiv.empty();

                // Enable/disable verify button
                if (value.length === 11) {
                    verifyBtn.prop('disabled', false);
                    verifyBtn.removeClass('btn-outline-secondary').addClass('btn-primary').css({
                        'background-color': '#006734',
                        'border-color': '#006734',
                        'color': 'white'
                    });
                } else {
                    verifyBtn.prop('disabled', true);
                    verifyBtn.removeClass('btn-primary').addClass('btn-outline-secondary').css({
                        'background-color': '',
                        'border-color': '',
                        'color': ''
                    });
                }
            });

            // Handle verify button clicks
            $(document).on('click', '.verify-nin-btn', function() {
                const btn = $(this);
                const ninId = btn.data('target');
                const ninInput = $(`#${ninId}`);
                const nin = ninInput.val();
                const feedbackDiv = $(`#${ninId}_feedback`);

                if (nin.length !== 11) return;

                // Check for duplicates within the form first
                const allNinInputs = $('.nin-input');
                let duplicateFound = false;
                allNinInputs.each(function() {
                    if ($(this).attr('id') !== ninId && $(this).val() === nin) {
                        duplicateFound = true;
                        return false; // break
                    }
                });

                if (duplicateFound) {
                    feedbackDiv.html(`
                    <div class="alert alert-danger py-2 px-3 small">
                        <i class="fe fe-x-circle"></i> <strong>Duplicate!</strong> This NIN is already used in this form.
                    </div>
                `);
                    ninVerificationStatus[ninId] = false;
                    checkAllNinsVerified();
                    return;
                }

                const originalHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);

                // Verify against database
                fetch(`/beneficiaries/verify-nin/${nin}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            feedbackDiv.html(`
                            <div class="alert alert-success py-2 px-3 small">
                                <i class="fe fe-check-circle"></i> <strong>NIN Available!</strong> Ready to use.
                            </div>
                        `);
                            ninVerificationStatus[ninId] = true;
                            usedNins.add(nin);
                        } else {
                            feedbackDiv.html(`
                            <div class="alert alert-danger py-2 px-3 small">
                                <i class="fe fe-x-circle"></i> <strong>NIN Exists!</strong> ${data.used_by} (${data.record_type})
                            </div>
                        `);
                            ninVerificationStatus[ninId] = false;
                        }
                        checkAllNinsVerified();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        feedbackDiv.html(`
                        <div class="alert alert-warning py-2 px-3 small">
                            <i class="fe fe-alert-triangle"></i> Error checking NIN. Please try again.
                        </div>
                    `);
                    })
                    .finally(() => {
                        btn.html(originalHtml).prop('disabled', false);
                    });
            });

            // Check if all filled NINs are verified
            function checkAllNinsVerified() {
                const allNinInputs = $('.nin-input');
                let allVerified = true;

                allNinInputs.each(function() {
                    const ninId = $(this).attr('id');
                    const value = $(this).val();

                    // If NIN is filled but not verified
                    if (value && value.length === 11 && !ninVerificationStatus[ninId]) {
                        allVerified = false;
                        return false; // break
                    }
                });

                return allVerified;
            }

            // Function to check if all NILs are verified
            function checkAllNINsVerified(section = 'all') {
                const unverifiedFields = [];

                if (section === 'beneficiary' || section === 'all') {
                    const beneficiaryNin = $('input[name="nin"]').val();
                    if (beneficiaryNin && beneficiaryNin.length === 11) {
                        if (!ninVerificationStatus['nin']) {
                            unverifiedFields.push('Beneficiary NIN');
                        }
                    }
                }

                // NOTE: Spouse and children NILs are NOT validated against civil_servants table
                // Only the main beneficiary must be a civil servant
                // Dependants (spouse/children) are not required to be civil servants
                // We only check for NIN uniqueness, not verification status

                return unverifiedFields;
            }

            // Form submission validation
            $('form').on('submit', function(e) {
                const unverifiedFields = checkAllNINsVerified('all');

                if (unverifiedFields.length > 0) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'NIN Verification Required',
                        html: `<p>The following NILs must be verified before submission:</p>
                               <ul style="text-align: left;">
                                   ${unverifiedFields.map(f => `<li>${f}</li>`).join('')}
                               </ul>`,
                        confirmButtonColor: '#006734',
                        confirmButtonText: 'OK'
                    });

                    return false;
                }
            });

            // Load verification data from sessionStorage
            const verificationData = sessionStorage.getItem('beneficiaryVerification');
            if (verificationData) {
                try {
                    const data = JSON.parse(verificationData);

                    // Show verification info banner
                    let bannerMessage = `NIN: ${data.nin} | DP Number: ${data.dp_no}`;
                    if (data.in_progress && data.beneficiary_id) {
                        bannerMessage +=
                            ' <span class="badge badge-warning">Continuing In Progress Enrollment</span>';
                    }

                    const banner = `
                    <div class="alert alert-info alert-dismissible fade show" role="alert" style="background-color: #e8f5e9; border-left: 4px solid #006734;">
                        <strong><i class="fe fe-check-circle"></i> Verification Completed!</strong>
                        <p class="mb-0">${bannerMessage}</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="sessionStorage.removeItem('beneficiaryVerification')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                    $('.card-body').first().prepend(banner);

                    // Pre-fill form with verification and civil servant data
                    // Fill program_id from verification
                    if (data.program_id) {
                        $('#program_id').val(data.program_id);

                        // Check if program allows dependants and hide sections if not
                        checkProgramDependants(data.program_id);
                    }

                    // If continuing in-progress enrollment, load existing data
                    if (data.in_progress && data.beneficiary_id) {
                        $('#beneficiary_id').val(data.beneficiary_id);

                        console.log('Loading existing enrollment data for beneficiary ID:', data.beneficiary_id);

                        // Load existing beneficiary data via AJAX
                        $.ajax({
                            url: `/beneficiaries/${data.beneficiary_id}/load-data`,
                            type: 'GET',
                            success: function(response) {
                                console.log('Load data response:', response);

                                if (response.success) {
                                    loadBeneficiaryData(response.beneficiary);

                                    if (response.spouse) {
                                        console.log('Loading spouse data');
                                        loadSpouseData(response.spouse);
                                    }

                                    if (response.children && response.children.length > 0) {
                                        console.log('Loading children data:', response.children.length,
                                            'children');
                                        loadChildrenData(response.children);
                                    }

                                    // Reload files after all data is loaded
                                    setTimeout(function() {
                                        loadAllFiles(response.beneficiary, response.spouse,
                                            response
                                            .children);
                                    }, 500);

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Data Loaded',
                                        text: 'Existing enrollment data loaded successfully!',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    console.error('Failed to load data:', response.message);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Loading Failed',
                                        text: 'Error loading existing data: ' + (response
                                            .message ||
                                            'Unknown error'),
                                        confirmButtonColor: '#006734'
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.error('AJAX Error loading beneficiary data:', xhr);
                                console.error('Response:', xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Loading Error',
                                    text: 'Error loading existing enrollment data. Please check the console for details.',
                                    confirmButtonColor: '#006734'
                                });
                            }
                        });
                    } else if (data.civil_servant) {
                        const cs = data.civil_servant;

                        // Fill beneficiary details
                        $('input[name="nin"]').val(data.nin).prop('readonly', true).css('background-color',
                            '#f0f0f0');
                        $('input[name="dp_no"]').val(data.dp_no).prop('readonly', true).css('background-color',
                            '#f0f0f0');

                        // Fill fullname directly
                        if (cs.fullname) {
                            $('input[name="fullname"]').val(cs.fullname);
                        }

                        // Set gender radio button
                        if (cs.gender) {
                            $(`input[name="gender"][value="${cs.gender}"]`).prop('checked', true);
                        }

                        if (cs.phone_no) $('input[name="phone_no"]').val(cs.phone_no);
                        if (cs.email) $('input[name="email"]').val(cs.email);
                        if (cs.lga) $('select[name="lga"]').val(cs.lga).trigger('change');
                        if (cs.state) $('select[name="state"]').val(cs.state).trigger('change');
                        if (cs.date_of_birth) $('input[name="date_of_birth"]').val(cs.date_of_birth);

                        // Highlight pre-filled fields
                        $('input[name="nin"], input[name="dp_no"]').closest('.form-group').find('label').append(
                            ' <span class="badge badge-success" style="background-color: #006734;">Verified</span>'
                        );
                    }

                    // Clear verification data after use (optional - keep it if you want to maintain throughout the session)
                    // sessionStorage.removeItem('beneficiaryVerification');

                } catch (e) {
                    console.error('Error parsing verification data:', e);
                }
            } else {
                // No verification data - redirect to verification page
                if (!{{ request()->has('verified') ? 'true' : 'false' }}) {
                    window.location.href = "{{ route('beneficiaries.verify') }}";
                }
            }

            // ============================================
            // HELPER FUNCTIONS TO LOAD EXISTING DATA
            // ============================================

            function loadAllFiles(beneficiary, spouse, children) {
                console.log('Loading all files...');

                // Load beneficiary photo
                if (beneficiary && beneficiary.photo) {
                    const photoUrl = `/storage/${beneficiary.photo}`;
                    const photoInput = $('input[name="beneficiary_photo"]');

                    if (photoInput.length) {
                        // Destroy and reinitialize with image
                        photoInput.dropify({
                            defaultFile: photoUrl
                        });
                        let drEvent = photoInput.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = photoUrl;
                        drEvent.destroy();
                        drEvent.init();

                        console.log('Beneficiary photo loaded:', photoUrl);
                    }
                }

                // Load beneficiary signature
                if (beneficiary && beneficiary.signature) {
                    const sigUrl = `/storage/${beneficiary.signature}`;
                    const sigInput = $('input[name="beneficiary_signature"]');

                    if (sigInput.length) {
                        sigInput.dropify({
                            defaultFile: sigUrl
                        });
                        let drEvent = sigInput.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = sigUrl;
                        drEvent.destroy();
                        drEvent.init();

                        console.log('Beneficiary signature loaded:', sigUrl);
                    }
                }

                // Load spouse photo
                if (spouse && spouse.photo) {
                    const spousePhotoUrl = `/storage/${spouse.photo}`;
                    const spousePhotoInput = $('input[name="spouse_photo"]');

                    if (spousePhotoInput.length) {
                        spousePhotoInput.dropify({
                            defaultFile: spousePhotoUrl
                        });
                        let drEvent = spousePhotoInput.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = spousePhotoUrl;
                        drEvent.destroy();
                        drEvent.init();

                        console.log('Spouse photo loaded:', spousePhotoUrl);
                    }
                }

                // Load children photos and birth certificates
                if (children && children.length > 0) {
                    children.forEach(function(child, index) {
                        // Child photo
                        if (child.photo) {
                            const childPhotoUrl = `/storage/${child.photo}`;
                            const childPhotoInput = $(`input[name="child_photo_${index}"]`);

                            if (childPhotoInput.length) {
                                childPhotoInput.dropify({
                                    defaultFile: childPhotoUrl
                                });
                                let drEvent = childPhotoInput.data('dropify');
                                drEvent.resetPreview();
                                drEvent.clearElement();
                                drEvent.settings.defaultFile = childPhotoUrl;
                                drEvent.destroy();
                                drEvent.init();

                                console.log(`Child ${index} photo loaded:`, childPhotoUrl);
                            }
                        }

                        // Child birth certificate
                        if (child.birth_certificate_file) {
                            const childBirthCertUrl = `/storage/${child.birth_certificate_file}`;
                            const childBirthCertInput = $(
                                `input[name="child_birth_certificate_file_${index}"]`);

                            if (childBirthCertInput.length) {
                                childBirthCertInput.dropify({
                                    defaultFile: childBirthCertUrl
                                });
                                let drEvent = childBirthCertInput.data('dropify');
                                drEvent.resetPreview();
                                drEvent.clearElement();
                                drEvent.settings.defaultFile = childBirthCertUrl;
                                drEvent.destroy();
                                drEvent.init();

                                console.log(`Child ${index} birth certificate loaded:`,
                                    childBirthCertUrl);
                            }
                        }
                    });
                }

                console.log('All files loaded successfully');
            }

            function loadBeneficiaryData(beneficiary) {
                console.log('Loading beneficiary data:', beneficiary);

                // Fill all beneficiary fields using #beneficiaryForm scope
                const form = $('#beneficiaryForm');

                form.find('select[name="facility_id"]').val(beneficiary.facility_id).trigger('change');
                form.find('select[name="alt_facility_id"]').val(beneficiary.alt_facility_id).trigger('change');
                form.find('#program_id').val(beneficiary.program_id);
                form.find('input[name="fullname"]').val(beneficiary.fullname);

                // Set gender radio button
                if (beneficiary.gender) {
                    form.find(`input[name="gender"][value="${beneficiary.gender}"]`).prop('checked', true);
                }

                form.find('input[name="date_of_birth"]').val(beneficiary.date_of_birth);
                form.find('input[name="place_of_birth"]').val(beneficiary.place_of_birth);
                form.find('select[name="lga"]').val(beneficiary.lga).trigger('change');
                form.find('select[name="state"]').val(beneficiary.state).trigger('change');
                form.find('select[name="nationality"]').val(beneficiary.nationality).trigger('change');
                form.find('select[name="marital_status"]').val(beneficiary.marital_status).trigger('change');
                form.find('select[name="ethnicity"]').val(beneficiary.ethnicity).trigger('change');
                form.find('select[name="religion"]').val(beneficiary.religion).trigger('change');
                form.find('textarea[name="contact_address"]').val(beneficiary.contact_address);
                form.find('input[name="phone_no"]').val(beneficiary.phone_no);
                form.find('input[name="email"]').val(beneficiary.email);
                form.find('input[name="occupation"]').val(beneficiary.occupation);
                form.find('input[name="dp_no"]').val(beneficiary.dp_no);
                form.find('select[name="id_type"]').val(beneficiary.id_type).trigger('change');
                form.find('input[name="id_no"]').val(beneficiary.id_no);
                form.find('input[name="nin"]').val(beneficiary.nin);
                form.find('input[name="place_of_work"]').val(beneficiary.place_of_work);
                form.find('input[name="date_of_employment"]').val(beneficiary.date_of_employment);
                form.find('input[name="date_of_retirement"]').val(beneficiary.date_of_retirement);

                // Category radio buttons
                if (beneficiary.category) {
                    form.find(`input[name="category"][value="${beneficiary.category}"]`).prop('checked', true);
                }

                // IMPORTANT: Mark beneficiary NIN as verified (it was verified when originally saved)
                if (beneficiary.nin && beneficiary.nin.length === 11) {
                    ninVerificationStatus['nin'] = true;

                    // Update UI to show verified
                    $('#nin').css('border-color', '#28a745');
                    $('#nin_feedback').html(
                        '<small class="text-success"><i class="fe fe-check-circle"></i> Previously verified</small>'
                    );

                    console.log('Beneficiary NIN marked as verified:', beneficiary.nin);
                }

                console.log('Beneficiary data loaded successfully');
            }

            function loadSpouseData(spouse) {
                console.log('Loading spouse data:', spouse);

                // Check the "has spouse" checkbox
                $('#hasSpouse').prop('checked', true).trigger('change');

                // Wait for spouse section to show
                setTimeout(function() {
                    // Fill spouse fields
                    $('input[name="spouse_name"]').val(spouse.name);

                    // Set gender radio button
                    if (spouse.gender) {
                        $(`input[name="spouse_gender"][value="${spouse.gender}"]`).prop('checked', true);
                    }

                    $('input[name="spouse_dob"]').val(spouse.dob);
                    $('input[name="spouse_phone"]').val(spouse.phone);
                    $('input[name="spouse_email"]').val(spouse.email);
                    $('input[name="spouse_nin"]').val(spouse.nin);

                    // IMPORTANT: Mark spouse NIN as verified (it was verified when originally saved)
                    if (spouse.nin && spouse.nin.length === 11) {
                        ninVerificationStatus['spouse_nin'] = true;

                        // Update UI to show verified
                        $('#spouse_nin').css('border-color', '#28a745');
                        $('#spouse_nin_feedback').html(
                            '<small class="text-success"><i class="fe fe-check-circle"></i> Previously verified</small>'
                        );

                        console.log('Spouse NIN marked as verified:', spouse.nin);
                    }

                    console.log('Spouse data loaded successfully');
                }, 300);
            }

            function loadChildrenData(children) {
                console.log('Loading children data, count:', children.length);

                // Check the "has children" checkbox
                $('#hasChildren').prop('checked', true).trigger('change');

                // Wait for children section to show
                setTimeout(function() {
                    // Add all child forms first
                    for (let i = 0; i < children.length; i++) {
                        console.log('Adding child form', i);
                        $('#addChildBtn').click();
                    }

                    // Then populate data for each child
                    children.forEach(function(child, index) {
                        setTimeout(function() {
                            console.log('Populating child', index, child);

                            $(`input[name="child_name_[${index}]"]`).val(child.name);

                            // Set child gender radio button
                            if (child.gender) {
                                $(`input[name="child_gender_[${index}]"][value="${child.gender}"]`)
                                    .prop('checked', true);
                            }

                            $(`input[name="child_date_of_birth_[${index}]"]`).val(child
                                .dob);
                            $(`input[name="child_birth_certificate_no_[${index}]"]`).val(
                                child
                                .birth_certificate_no);
                            $(`input[name="child_nin_[${index}]"]`).val(child.nin);

                            // IMPORTANT: Mark child NIN as verified (it was verified when originally saved)
                            if (child.nin && child.nin.length === 11) {
                                const childNinId = `child_nin_${index}`;
                                ninVerificationStatus[childNinId] = true;

                                // Update UI to show verified
                                $(`#${childNinId}`).css('border-color', '#28a745');
                                $(`#${childNinId}_feedback`).html(
                                    '<small class="text-success"><i class="fe fe-check-circle"></i> Previously verified</small>'
                                );

                                console.log(`Child ${index} NIN marked as verified:`, child
                                    .nin);
                            }

                            console.log(`Child ${index} data populated`);
                        }, 200 * (index + 1));
                    });

                    // Update child count
                    $('#childCount').text(children.length);
                }, 500);
            }

            // ============================================
            // SECTION-WISE SAVE FUNCTIONALITY
            // ============================================

            // Save Beneficiary Information
            $('#saveBeneficiaryInfo').click(function() {
                // NOTE: NIN verification is NOT required for section saves
                // Users can save progress even without NIN verification
                // NIN verification is only enforced during final submission

                const btn = $(this);
                const originalHtml = btn.html();

                btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop(
                    'disabled', true);

                const formData = new FormData($('#beneficiaryForm')[0]);
                formData.append('section', 'beneficiary_info');
                formData.append('save_section', '1');

                $.ajax({
                    url: '{{ route('beneficiaries.saveSection') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Store beneficiary ID for future saves
                            $('#beneficiary_id').val(response.beneficiary_id);
                            $('#beneficiaryInfoSaved').fadeIn();

                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: 'Beneficiary information saved successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error saving beneficiary information. Please try again.',
                            confirmButtonColor: '#006734'
                        });
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // Save Spouse Information
            $('#saveSpouseInfo').click(function() {
                const beneficiaryId = $('#beneficiary_id').val();
                if (!beneficiaryId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required',
                        text: 'Please save Beneficiary Information first!',
                        confirmButtonColor: '#006734'
                    });
                    return;
                }

                // NOTE: Spouse NILs don't need verification (they're not required to be civil servants)
                // BUT we still need to check for duplicate NILs within the form
                const spouseNin = $('input[name="spouse_nin"]').val();
                if (spouseNin && spouseNin.length === 11) {
                    // Check if spouse NIN is duplicate with beneficiary
                    const beneficiaryNin = $('input[name="nin"]').val();
                    if (spouseNin === beneficiaryNin) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Duplicate NIN',
                            text: 'Spouse NIN cannot be the same as Beneficiary NIN!',
                            confirmButtonColor: '#006734'
                        });
                        return false;
                    }

                    // Check if spouse NIN is duplicate with any child
                    let duplicateWithChild = false;
                    $('input[name^="child_nin_"]').each(function() {
                        const childNin = $(this).val();
                        if (childNin && childNin === spouseNin) {
                            duplicateWithChild = true;
                            return false; // break
                        }
                    });

                    if (duplicateWithChild) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Duplicate NIN',
                            text: 'Spouse NIN cannot be the same as any child NIN!',
                            confirmButtonColor: '#006734'
                        });
                        return false;
                    }
                }

                const btn = $(this);
                const originalHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop(
                    'disabled', true);

                const formData = new FormData($('#beneficiaryForm')[0]);
                formData.append('section', 'spouse_info');
                formData.append('save_section', '1');
                formData.append('beneficiary_id', beneficiaryId);

                $.ajax({
                    url: '{{ route('beneficiaries.saveSection') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#spouseInfoSaved').fadeIn();

                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: 'Spouse information saved successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error saving spouse information. Please try again.',
                            confirmButtonColor: '#006734'
                        });
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // Save Children Information
            $('#saveChildrenInfo').click(function() {
                const beneficiaryId = $('#beneficiary_id').val();
                if (!beneficiaryId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required',
                        text: 'Please save Beneficiary Information first!',
                        confirmButtonColor: '#006734'
                    });
                    return;
                }

                // NOTE: Children NILs don't need verification (they're not civil servants)
                // BUT we still need to check for duplicate NILs within the form
                const beneficiaryNin = $('input[name="nin"]').val();
                const spouseNin = $('input[name="spouse_nin"]').val();
                const childNins = [];
                let hasDuplicate = false;
                let duplicateMessage = '';

                $('input[name^="child_nin_"]').each(function(index) {
                    const childNin = $(this).val();
                    const childName = $(`input[name="child_name_[${index}]"]`).val();

                    if (childNin && childNin.length === 11 && childName && childName.trim() !==
                        '') {
                        // Check duplicate with beneficiary
                        if (childNin === beneficiaryNin) {
                            hasDuplicate = true;
                            duplicateMessage =
                                `Child ${index + 1} (${childName}) NIN cannot be the same as Beneficiary NIN!`;
                            return false; // break loop
                        }

                        // Check duplicate with spouse
                        if (childNin === spouseNin) {
                            hasDuplicate = true;
                            duplicateMessage =
                                `Child ${index + 1} (${childName}) NIN cannot be the same as Spouse NIN!`;
                            return false; // break loop
                        }

                        // Check duplicate with other children
                        if (childNins.includes(childNin)) {
                            hasDuplicate = true;
                            duplicateMessage =
                                `Child ${index + 1} (${childName}) NIN is already used by another child!`;
                            return false; // break loop
                        }

                        childNins.push(childNin);
                    }
                });

                // If duplicate found, show error and stop
                if (hasDuplicate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate NIN',
                        text: duplicateMessage,
                        confirmButtonColor: '#006734'
                    });
                    return false;
                }

                // NOTE: Photo and birth certificate validation removed for section saves
                // Section save only requires child name to be filled
                // Photos and birth certificates are validated during FINAL SUBMISSION only
                // This allows users to save progress and upload files later

                const btn = $(this);
                const originalHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop(
                    'disabled', true);

                const formData = new FormData($('#beneficiaryForm')[0]);
                formData.append('section', 'children_info');
                formData.append('save_section', '1');
                formData.append('beneficiary_id', beneficiaryId);

                $.ajax({
                    url: '{{ route('beneficiaries.saveSection') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#childrenInfoSaved').fadeIn();

                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: 'Children information saved successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error saving children information. Please try again.',
                            confirmButtonColor: '#006734'
                        });
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // Comprehensive validation function
            function validateAllRequiredFields() {
                const missingFields = [];
                const form = $('#beneficiaryForm');

                // Check beneficiary ID exists
                const beneficiaryId = $('#beneficiary_id').val();
                if (!beneficiaryId) {
                    missingFields.push('Please save Beneficiary Information first');
                }

                // CRITICAL: Check NIN Verification Status
                const unverifiedNins = checkAllNINsVerified('all');
                if (unverifiedNins.length > 0) {
                    unverifiedNins.forEach(nin => {
                        missingFields.push(`⚠️ ${nin} (NOT VERIFIED)`);
                    });
                }

                // Beneficiary Required Fields
                const requiredBeneficiaryFields = {
                    'facility_id': 'Facility',
                    'program_id': 'Program',
                    'fullname': 'Full Name',
                    'gender': 'Gender',
                    'date_of_birth': 'Date of Birth',
                    'lga': 'LGA',
                    'state': 'State',
                    'nationality': 'Nationality',
                    'marital_status': 'Marital Status',
                    'religion': 'Religion',
                    'contact_address': 'Contact Address',
                    'phone_no': 'Phone Number',
                    'nin': 'NIN',
                    'date_of_employment': 'Employment Date',
                    'category': 'Category'
                };

                // Check beneficiary fields
                for (const [fieldName, fieldLabel] of Object.entries(requiredBeneficiaryFields)) {
                    let value;

                    if (fieldName === 'gender' || fieldName === 'category') {
                        // Radio buttons
                        value = form.find(`input[name="${fieldName}"]:checked`).val();
                    } else {
                        // Text inputs and selects
                        value = form.find(`[name="${fieldName}"]`).val();
                    }

                    if (!value || value.trim() === '') {
                        missingFields.push(`Beneficiary: ${fieldLabel}`);
                    }
                }

                // Check spouse fields if spouse checkbox is checked
                const hasSpouse = $('#hasSpouse').is(':checked');
                if (hasSpouse) {
                    const requiredSpouseFields = {
                        'spouse_name': 'Spouse Name',
                        'spouse_gender': 'Spouse Gender',
                        'spouse_dob': 'Spouse Date of Birth',
                        'spouse_phone': 'Spouse Phone Number'
                        // NOTE: spouse_nin is optional - not all spouses are civil servants
                    };

                    for (const [fieldName, fieldLabel] of Object.entries(requiredSpouseFields)) {
                        let value;

                        if (fieldName === 'spouse_gender') {
                            value = $(`input[name="${fieldName}"]:checked`).val();
                        } else {
                            value = $(`[name="${fieldName}"]`).val();
                        }

                        if (!value || value.trim() === '') {
                            missingFields.push(fieldLabel);
                        }
                    }
                }

                // Check children fields if children checkbox is checked
                const hasChildren = $('#hasChildren').is(':checked');
                if (hasChildren) {
                    const childNames = $('input[name^="child_name_"]');

                    childNames.each(function(index) {
                        const childName = $(this).val();

                        if (childName && childName.trim() !== '') {
                            const childNum = index + 1;

                            // Check gender
                            const gender = $(`input[name="child_gender_[${index}]"]:checked`).val();
                            if (!gender) {
                                missingFields.push(`Child ${childNum}: Gender`);
                            }

                            // Check DOB
                            const dob = $(`input[name="child_date_of_birth_[${index}]"]`).val();
                            if (!dob || dob.trim() === '') {
                                missingFields.push(`Child ${childNum}: Date of Birth`);
                            }

                            // CRITICAL: Check for photo
                            const photoInput = $(`input[name="child_photo_${index}"]`);
                            const hasNewPhoto = photoInput[0] && photoInput[0].files && photoInput[0].files
                                .length > 0;

                            // Check if Dropify has existing file (for edit mode)
                            const dropifyPhoto = photoInput.data('dropify');
                            const hasExistingPhoto = dropifyPhoto && dropifyPhoto.settings && dropifyPhoto
                                .settings.defaultFile;

                            // Check if the wrapper has a preview (Dropify loaded state)
                            const hasPhotoPreview = photoInput.parent().find('.dropify-preview').length > 0;

                            if (!hasNewPhoto && !hasExistingPhoto && !hasPhotoPreview) {
                                missingFields.push(`Child ${childNum}: Photo`);
                            }

                            // CRITICAL: Check for birth certificate
                            const birthCertInput = $(`input[name="child_birth_certificate_file_${index}"]`);
                            const hasNewBirthCert = birthCertInput[0] && birthCertInput[0].files &&
                                birthCertInput[0].files.length > 0;

                            // Check if Dropify has existing file (for edit mode)
                            const dropifyBirthCert = birthCertInput.data('dropify');
                            const hasExistingBirthCert = dropifyBirthCert && dropifyBirthCert.settings &&
                                dropifyBirthCert.settings.defaultFile;

                            // Check if the wrapper has a preview (Dropify loaded state)
                            const hasBirthCertPreview = birthCertInput.parent().find('.dropify-preview')
                                .length > 0;

                            if (!hasNewBirthCert && !hasExistingBirthCert && !hasBirthCertPreview) {
                                missingFields.push(`Child ${childNum}: Birth Certificate`);
                            }
                        }
                    });

                    // Check if at least one child is entered
                    const hasAtLeastOneChild = Array.from(childNames).some(input =>
                        $(input).val() && $(input).val().trim() !== ''
                    );

                    if (!hasAtLeastOneChild) {
                        missingFields.push('At least one child is required when "Has Children" is checked');
                    }
                }

                return missingFields;
            }

            // Final Submit Handler
            $('#finalSubmitBtn').click(function(e) {
                e.preventDefault();

                // Validate all required fields
                const missingFields = validateAllRequiredFields();

                if (missingFields.length > 0) {
                    // Show missing fields with SweetAlert
                    let fieldsHtml = '<ul style="text-align: left; max-height: 400px; overflow-y: auto;">';
                    missingFields.forEach(field => {
                        fieldsHtml += `<li>${field}</li>`;
                    });
                    fieldsHtml += '</ul>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Required Fields',
                        html: `<p>Please fill in the following required fields:</p>${fieldsHtml}`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#006734',
                        width: '600px'
                    });

                    return false;
                }

                // Update status to active (finalized enrollment)
                $('#status').val('active');

                // Confirm final submission with SweetAlert
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm Final Submission',
                    text: 'This will finalize the enrollment and generate the BOSCHMA ID. Are you sure?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Complete Enrollment',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#006734',
                    cancelButtonColor: '#d33',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form
                        $('#beneficiaryForm').submit();
                    }
                });
            });
        });
    </script>
@endsection
