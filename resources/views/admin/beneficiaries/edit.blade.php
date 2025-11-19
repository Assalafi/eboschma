@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h6 class="main-content-label mb-1">Edit Beneficiary Enrollment</h6>
                                <p class="text-muted card-sub-title">Update beneficiary information and dependents</p>
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

                        <form action="{{ route('beneficiaries.update', $beneficiary->id) }}" method="POST"
                            enctype="multipart/form-data" class="form-horizontal">
                            @csrf
                            @method('PUT')

                            <!-- Main Beneficiary Details -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Beneficiary Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">BOSCHMA ID:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control"
                                                        value="{{ $beneficiary->boschma_no }}" readonly disabled>
                                                    <small class="text-muted">This ID cannot be changed</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Program: <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="program_id" required>
                                                        <option value="">Select Program</option>
                                                        @foreach ($programs as $program)
                                                            <option value="{{ $program->id }}"
                                                                {{ $beneficiary->program_id == $program->id ? 'selected' : '' }}>
                                                                {{ $program->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Select the program for this beneficiary</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Facility: <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="facility_id" required>
                                                        <option value="">Select Facility</option>
                                                        @foreach ($facilities as $facility)
                                                            <option value="{{ $facility->id }}"
                                                                {{ $beneficiary->facility_id == $facility->id ? 'selected' : '' }}>
                                                                {{ $facility->name }} - {{ $facility->lga }},
                                                                {{ $facility->ward }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Select the healthcare facility where this
                                                        beneficiary is registered</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Full Name: <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" name="fullname"
                                                        value="{{ old('fullname', $beneficiary->fullname) }}" required
                                                        placeholder="Enter full name">
                                                    <small class="text-muted">Enter the complete full name of the
                                                        beneficiary</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Gender:</label>
                                                <div class="col-md-9">
                                                    <div class="form-check form-check-inline mt-2">
                                                        <input class="form-check-input" type="radio" name="gender"
                                                            id="male" value="Male"
                                                            {{ $beneficiary->gender == 'Male' ? 'checked' : '' }} required>
                                                        <label class="form-check-label" for="male">Male</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="gender"
                                                            id="female" value="Female"
                                                            {{ $beneficiary->gender == 'Female' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="female">Female</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Date of Birth:</label>
                                                <div class="col-md-9">
                                                    <input type="date" class="form-control" name="date_of_birth"
                                                        value="{{ old('date_of_birth', $beneficiary->date_of_birth ? date('Y-m-d', strtotime($beneficiary->date_of_birth)) : '') }}"
                                                        required>
                                                    <small class="text-muted">Format: dd-mm-yyyy</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Place of Birth:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" name="place_of_birth"
                                                        value="{{ old('place_of_birth', $beneficiary->place_of_birth) }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">LGA:</label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="lga" required>
                                                        <option value="">Select LGA</option>
                                                        <option value="Abadam"
                                                            {{ old('lga', $beneficiary->lga) == 'Abadam' ? 'selected' : '' }}>
                                                            Abadam</option>
                                                        <option value="Askira/Uba"
                                                            {{ old('lga', $beneficiary->lga) == 'Askira/Uba' ? 'selected' : '' }}>
                                                            Askira/Uba
                                                        </option>
                                                        <option value="Bama"
                                                            {{ old('lga', $beneficiary->lga) == 'Bama' ? 'selected' : '' }}>
                                                            Bama</option>
                                                        <option value="Bayo"
                                                            {{ old('lga', $beneficiary->lga) == 'Bayo' ? 'selected' : '' }}>
                                                            Bayo</option>
                                                        <option value="Biu"
                                                            {{ old('lga', $beneficiary->lga) == 'Biu' ? 'selected' : '' }}>
                                                            Biu</option>
                                                        <option value="Chibok"
                                                            {{ old('lga', $beneficiary->lga) == 'Chibok' ? 'selected' : '' }}>
                                                            Chibok</option>
                                                        <option value="Damboa"
                                                            {{ old('lga', $beneficiary->lga) == 'Damboa' ? 'selected' : '' }}>
                                                            Damboa</option>
                                                        <option value="Dikwa"
                                                            {{ old('lga', $beneficiary->lga) == 'Dikwa' ? 'selected' : '' }}>
                                                            Dikwa</option>
                                                        <option value="Gubio"
                                                            {{ old('lga', $beneficiary->lga) == 'Gubio' ? 'selected' : '' }}>
                                                            Gubio</option>
                                                        <option value="Guzamala"
                                                            {{ old('lga', $beneficiary->lga) == 'Guzamala' ? 'selected' : '' }}>
                                                            Guzamala
                                                        </option>
                                                        <option value="Gwoza"
                                                            {{ old('lga', $beneficiary->lga) == 'Gwoza' ? 'selected' : '' }}>
                                                            Gwoza</option>
                                                        <option value="Hawul"
                                                            {{ old('lga', $beneficiary->lga) == 'Hawul' ? 'selected' : '' }}>
                                                            Hawul</option>
                                                        <option value="Jere"
                                                            {{ old('lga', $beneficiary->lga) == 'Jere' ? 'selected' : '' }}>
                                                            Jere</option>
                                                        <option value="Kaga"
                                                            {{ old('lga', $beneficiary->lga) == 'Kaga' ? 'selected' : '' }}>
                                                            Kaga</option>
                                                        <option value="Kala/Balge"
                                                            {{ old('lga', $beneficiary->lga) == 'Kala/Balge' ? 'selected' : '' }}>
                                                            Kala/Balge
                                                        </option>
                                                        <option value="Konduga"
                                                            {{ old('lga', $beneficiary->lga) == 'Konduga' ? 'selected' : '' }}>
                                                            Konduga
                                                        </option>
                                                        <option value="Kukawa"
                                                            {{ old('lga', $beneficiary->lga) == 'Kukawa' ? 'selected' : '' }}>
                                                            Kukawa</option>
                                                        <option value="Kwaya Kusar"
                                                            {{ old('lga', $beneficiary->lga) == 'Kwaya Kusar' ? 'selected' : '' }}>
                                                            Kwaya Kusar
                                                        </option>
                                                        <option value="Mafa"
                                                            {{ old('lga', $beneficiary->lga) == 'Mafa' ? 'selected' : '' }}>
                                                            Mafa</option>
                                                        <option value="Magumeri"
                                                            {{ old('lga', $beneficiary->lga) == 'Magumeri' ? 'selected' : '' }}>
                                                            Magumeri
                                                        </option>
                                                        <option value="Maiduguri"
                                                            {{ old('lga', $beneficiary->lga) == 'Maiduguri' ? 'selected' : '' }}>
                                                            Maiduguri
                                                        </option>
                                                        <option value="Marte"
                                                            {{ old('lga', $beneficiary->lga) == 'Marte' ? 'selected' : '' }}>
                                                            Marte</option>
                                                        <option value="Mobbar"
                                                            {{ old('lga', $beneficiary->lga) == 'Mobbar' ? 'selected' : '' }}>
                                                            Mobbar</option>
                                                        <option value="Monguno"
                                                            {{ old('lga', $beneficiary->lga) == 'Monguno' ? 'selected' : '' }}>
                                                            Monguno
                                                        </option>
                                                        <option value="Ngala"
                                                            {{ old('lga', $beneficiary->lga) == 'Ngala' ? 'selected' : '' }}>
                                                            Ngala</option>
                                                        <option value="Nganzai"
                                                            {{ old('lga', $beneficiary->lga) == 'Nganzai' ? 'selected' : '' }}>
                                                            Nganzai
                                                        </option>
                                                        <option value="Shani"
                                                            {{ old('lga', $beneficiary->lga) == 'Shani' ? 'selected' : '' }}>
                                                            Shani</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">State:</label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="state" required>
                                                        <option value="">Select State</option>
                                                        <option value="Abia"
                                                            {{ old('state', $beneficiary->state) == 'Abia' ? 'selected' : '' }}>
                                                            Abia</option>
                                                        <option value="Adamawa"
                                                            {{ old('state', $beneficiary->state) == 'Adamawa' ? 'selected' : '' }}>
                                                            Adamawa
                                                        </option>
                                                        <option value="Akwa Ibom"
                                                            {{ old('state', $beneficiary->state) == 'Akwa Ibom' ? 'selected' : '' }}>
                                                            Akwa Ibom
                                                        </option>
                                                        <option value="Anambra"
                                                            {{ old('state', $beneficiary->state) == 'Anambra' ? 'selected' : '' }}>
                                                            Anambra
                                                        </option>
                                                        <option value="Bauchi"
                                                            {{ old('state', $beneficiary->state) == 'Bauchi' ? 'selected' : '' }}>
                                                            Bauchi
                                                        </option>
                                                        <option value="Bayelsa"
                                                            {{ old('state', $beneficiary->state) == 'Bayelsa' ? 'selected' : '' }}>
                                                            Bayelsa
                                                        </option>
                                                        <option value="Benue"
                                                            {{ old('state', $beneficiary->state) == 'Benue' ? 'selected' : '' }}>
                                                            Benue</option>
                                                        <option value="Borno"
                                                            {{ old('state', $beneficiary->state) == 'Borno' ? 'selected' : '' }}>
                                                            Borno</option>
                                                        <option value="Cross River"
                                                            {{ old('state', $beneficiary->state) == 'Cross River' ? 'selected' : '' }}>
                                                            Cross
                                                            River</option>
                                                        <option value="Delta"
                                                            {{ old('state', $beneficiary->state) == 'Delta' ? 'selected' : '' }}>
                                                            Delta</option>
                                                        <option value="Ebonyi"
                                                            {{ old('state', $beneficiary->state) == 'Ebonyi' ? 'selected' : '' }}>
                                                            Ebonyi
                                                        </option>
                                                        <option value="Edo"
                                                            {{ old('state', $beneficiary->state) == 'Edo' ? 'selected' : '' }}>
                                                            Edo</option>
                                                        <option value="Ekiti"
                                                            {{ old('state', $beneficiary->state) == 'Ekiti' ? 'selected' : '' }}>
                                                            Ekiti</option>
                                                        <option value="Enugu"
                                                            {{ old('state', $beneficiary->state) == 'Enugu' ? 'selected' : '' }}>
                                                            Enugu</option>
                                                        <option value="FCT"
                                                            {{ old('state', $beneficiary->state) == 'FCT' ? 'selected' : '' }}>
                                                            FCT</option>
                                                        <option value="Gombe"
                                                            {{ old('state', $beneficiary->state) == 'Gombe' ? 'selected' : '' }}>
                                                            Gombe</option>
                                                        <option value="Imo"
                                                            {{ old('state', $beneficiary->state) == 'Imo' ? 'selected' : '' }}>
                                                            Imo</option>
                                                        <option value="Jigawa"
                                                            {{ old('state', $beneficiary->state) == 'Jigawa' ? 'selected' : '' }}>
                                                            Jigawa
                                                        </option>
                                                        <option value="Kaduna"
                                                            {{ old('state', $beneficiary->state) == 'Kaduna' ? 'selected' : '' }}>
                                                            Kaduna
                                                        </option>
                                                        <option value="Kano"
                                                            {{ old('state', $beneficiary->state) == 'Kano' ? 'selected' : '' }}>
                                                            Kano</option>
                                                        <option value="Katsina"
                                                            {{ old('state', $beneficiary->state) == 'Katsina' ? 'selected' : '' }}>
                                                            Katsina
                                                        </option>
                                                        <option value="Kebbi"
                                                            {{ old('state', $beneficiary->state) == 'Kebbi' ? 'selected' : '' }}>
                                                            Kebbi</option>
                                                        <option value="Kogi"
                                                            {{ old('state', $beneficiary->state) == 'Kogi' ? 'selected' : '' }}>
                                                            Kogi</option>
                                                        <option value="Kwara"
                                                            {{ old('state', $beneficiary->state) == 'Kwara' ? 'selected' : '' }}>
                                                            Kwara</option>
                                                        <option value="Lagos"
                                                            {{ old('state', $beneficiary->state) == 'Lagos' ? 'selected' : '' }}>
                                                            Lagos</option>
                                                        <option value="Nasarawa"
                                                            {{ old('state', $beneficiary->state) == 'Nasarawa' ? 'selected' : '' }}>
                                                            Nasarawa
                                                        </option>
                                                        <option value="Niger"
                                                            {{ old('state', $beneficiary->state) == 'Niger' ? 'selected' : '' }}>
                                                            Niger</option>
                                                        <option value="Ogun"
                                                            {{ old('state', $beneficiary->state) == 'Ogun' ? 'selected' : '' }}>
                                                            Ogun</option>
                                                        <option value="Ondo"
                                                            {{ old('state', $beneficiary->state) == 'Ondo' ? 'selected' : '' }}>
                                                            Ondo</option>
                                                        <option value="Osun"
                                                            {{ old('state', $beneficiary->state) == 'Osun' ? 'selected' : '' }}>
                                                            Osun</option>
                                                        <option value="Oyo"
                                                            {{ old('state', $beneficiary->state) == 'Oyo' ? 'selected' : '' }}>
                                                            Oyo</option>
                                                        <option value="Plateau"
                                                            {{ old('state', $beneficiary->state) == 'Plateau' ? 'selected' : '' }}>
                                                            Plateau
                                                        </option>
                                                        <option value="Rivers"
                                                            {{ old('state', $beneficiary->state) == 'Rivers' ? 'selected' : '' }}>
                                                            Rivers
                                                        </option>
                                                        <option value="Sokoto"
                                                            {{ old('state', $beneficiary->state) == 'Sokoto' ? 'selected' : '' }}>
                                                            Sokoto
                                                        </option>
                                                        <option value="Taraba"
                                                            {{ old('state', $beneficiary->state) == 'Taraba' ? 'selected' : '' }}>
                                                            Taraba
                                                        </option>
                                                        <option value="Yobe"
                                                            {{ old('state', $beneficiary->state) == 'Yobe' ? 'selected' : '' }}>
                                                            Yobe</option>
                                                        <option value="Zamfara"
                                                            {{ old('state', $beneficiary->state) == 'Zamfara' ? 'selected' : '' }}>
                                                            Zamfara
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Nationality:</label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="nationality" required>
                                                        <option value="">Select Nationality</option>
                                                        <option value="Nigerian"
                                                            {{ old('nationality', $beneficiary->nationality) == 'Nigerian' ? 'selected' : '' }}>
                                                            Nigerian</option>
                                                        <option value="Others"
                                                            {{ old('nationality', $beneficiary->nationality) == 'Others' ? 'selected' : '' }}>
                                                            Others
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Marital Status:</label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="marital_status">
                                                        <option value="">Select status</option>
                                                        <option value="Single"
                                                            {{ old('marital_status', $beneficiary->marital_status) == 'Single' ? 'selected' : '' }}>
                                                            Single</option>
                                                        <option value="Married"
                                                            {{ old('marital_status', $beneficiary->marital_status) == 'Married' ? 'selected' : '' }}>
                                                            Married</option>
                                                        <option value="Widow"
                                                            {{ old('marital_status', $beneficiary->marital_status) == 'Widow' ? 'selected' : '' }}>
                                                            Widow
                                                        </option>
                                                        <option value="Divorce"
                                                            {{ old('marital_status', $beneficiary->marital_status) == 'Divorce' ? 'selected' : '' }}>
                                                            Divorce</option>
                                                        <option value="Others"
                                                            {{ old('marital_status', $beneficiary->marital_status) == 'Others' ? 'selected' : '' }}>
                                                            Others</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Ethnicity:</label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="ethnicity" required>
                                                        <option value="">Select Ethnicity</option>
                                                        <option value="Hausa"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Hausa' ? 'selected' : '' }}>
                                                            Hausa
                                                        </option>
                                                        <option value="Yoruba"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Yoruba' ? 'selected' : '' }}>
                                                            Yoruba
                                                        </option>
                                                        <option value="Igbo"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Igbo' ? 'selected' : '' }}>
                                                            Igbo
                                                        </option>
                                                        <option value="Fulani"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Fulani' ? 'selected' : '' }}>
                                                            Fulani
                                                        </option>
                                                        <option value="Kanuri"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Kanuri' ? 'selected' : '' }}>
                                                            Kanuri
                                                        </option>
                                                        <option value="Ibibio"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Ibibio' ? 'selected' : '' }}>
                                                            Ibibio
                                                        </option>
                                                        <option value="Tiv"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Tiv' ? 'selected' : '' }}>
                                                            Tiv</option>
                                                        <option value="Ijaw"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Ijaw' ? 'selected' : '' }}>
                                                            Ijaw
                                                        </option>
                                                        <option value="Edo"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Edo' ? 'selected' : '' }}>
                                                            Edo</option>
                                                        <option value="Nupe"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Nupe' ? 'selected' : '' }}>
                                                            Nupe
                                                        </option>
                                                        <option value="Gbagyi"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Gbagyi' ? 'selected' : '' }}>
                                                            Gbagyi
                                                        </option>
                                                        <option value="Jukun"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Jukun' ? 'selected' : '' }}>
                                                            Jukun
                                                        </option>
                                                        <option value="Urhobo"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Urhobo' ? 'selected' : '' }}>
                                                            Urhobo
                                                        </option>
                                                        <option value="Igala"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Igala' ? 'selected' : '' }}>
                                                            Igala
                                                        </option>
                                                        <option value="Idoma"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Idoma' ? 'selected' : '' }}>
                                                            Idoma
                                                        </option>
                                                        <option value="Others"
                                                            {{ old('ethnicity', $beneficiary->ethnicity) == 'Others' ? 'selected' : '' }}>
                                                            Others
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Religion:</label>
                                                <div class="col-md-9">
                                                    <select class="form-select" name="religion" required>
                                                        <option value="">Select Religion</option>
                                                        <option value="Christianity"
                                                            {{ old('religion', $beneficiary->religion) == 'Christianity' ? 'selected' : '' }}>
                                                            Christianity</option>
                                                        <option value="Islam"
                                                            {{ old('religion', $beneficiary->religion) == 'Islam' ? 'selected' : '' }}>
                                                            Islam
                                                        </option>
                                                        <option value="Traditional"
                                                            {{ old('religion', $beneficiary->religion) == 'Traditional' ? 'selected' : '' }}>
                                                            Traditional</option>
                                                        <option value="Others"
                                                            {{ old('religion', $beneficiary->religion) == 'Others' ? 'selected' : '' }}>
                                                            Others
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h6>Beneficiary Photo</h6>
                                                    <div class="mb-3 mt-3">
                                                        <input type="file" class="dropify-create"
                                                            name="beneficiary_photo" data-height="200"
                                                            data-allowed-file-extensions="jpg jpeg png"
                                                            data-max-file-size="2M"
                                                            data-default-file="{{ $beneficiary->photo ? url('storage/' . $beneficiary->photo) : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Contact Address:</label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" name="contact_address" rows="3">{{ old('contact_address', $beneficiary->contact_address) }}</textarea>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Phone No:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" name="phone_no"
                                                        value="{{ old('phone_no', $beneficiary->phone_no) }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Email:</label>
                                                <div class="col-md-9">
                                                    <input type="email" class="form-control" name="email"
                                                        value="{{ old('email', $beneficiary->email) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="bg-light p-2">Other Information</h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">Occupation:</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="occupation"
                                                        value="{{ old('occupation', $beneficiary->occupation) }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">D.P No:</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="dp_no"
                                                        value="{{ old('dp_no', $beneficiary->dp_no) }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">ID Type: <span class="text-danger">*</span></label>
                                                <div class="col-md-8">
                                                    <select class="form-select" name="id_type" required>
                                                        <option value="">Select ID Type</option>
                                                        <option value="Driver License"
                                                            {{ $beneficiary->id_type == 'Driver License' ? 'selected' : '' }}>
                                                            Driver License</option>
                                                        <option value="NIMC"
                                                            {{ $beneficiary->id_type == 'NIMC' ? 'selected' : '' }}>NIMC
                                                        </option>
                                                        <option value="Voters Card"
                                                            {{ $beneficiary->id_type == 'Voters Card' ? 'selected' : '' }}>
                                                            Voters
                                                            Card</option>
                                                        <option value="International Passport"
                                                            {{ $beneficiary->id_type == 'International Passport' ? 'selected' : '' }}>
                                                            International Passport</option>
                                                        <option value="Others"
                                                            {{ $beneficiary->id_type == 'Others' ? 'selected' : '' }}>
                                                            Others
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">ID No:</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="id_no"
                                                        value="{{ old('id_no', $beneficiary->id_no) }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">NIN:</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nin"
                                                        value="{{ old('nin', $beneficiary->nin) }}" maxlength="11"
                                                        placeholder="Enter 11-digit NIN">
                                                    <small class="text-muted">National Identification Number (11
                                                        digits)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">Place of Work:</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="place_of_work"
                                                        value="{{ old('place_of_work', $beneficiary->place_of_work) }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">Employment Date:</label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" name="date_of_employment"
                                                        value="{{ old('date_of_employment', $beneficiary->date_of_employment ? date('Y-m-d', strtotime($beneficiary->date_of_employment)) : '') }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-4 form-label">Retirement Date:</label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" name="date_of_retirement"
                                                        value="{{ old('date_of_retirement', $beneficiary->date_of_retirement ? date('Y-m-d', strtotime($beneficiary->date_of_retirement)) : '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-2 form-label">Category: <span class="text-danger">*</span></label>
                                        <div class="col-md-10">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="category"
                                                    id="gl_step" value="GL/STEP" required
                                                    {{ $beneficiary->category == 'GL/STEP' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="gl_step">GL/STEP</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="category"
                                                    id="private" value="Organized Private Sector"
                                                    {{ $beneficiary->category == 'Organized Private Sector' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="private">Organized Private
                                                    Sector</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="category"
                                                    id="others" value="Others"
                                                    {{ $beneficiary->category == 'Others' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="others">Others</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Spouse Information -->
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="hasSpouse"
                                            name="has_spouse" value="1"
                                            {{ $beneficiary->spouse ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="hasSpouse">
                                            <h5 class="mb-0">Dependents: Spouse</h5>
                                        </label>
                                    </div>
                                </div>
                                <div id="spouseSection" class="card shadow-none border mb-4"
                                    style="{{ $beneficiary->spouse ? '' : 'display: none;' }}">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">BOSCHMA ID:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control"
                                                        value="{{ $beneficiary->spouse ? $beneficiary->spouse->boschma_no : $beneficiary->boschma_no . 'A' }}"
                                                        readonly disabled>
                                                    <small class="text-muted">This will be assigned automatically (Primary
                                                        ID + 'A')</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Spouse Name:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control spouse-field"
                                                        name="spouse_name"
                                                        value="{{ old('spouse_name', $beneficiary->spouse ? $beneficiary->spouse->name : '') }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Gender:</label>
                                                <div class="col-md-9">
                                                    <div class="form-check form-check-inline mt-2">
                                                        <input class="form-check-input spouse-field" type="radio"
                                                            name="spouse_gender" id="spouse_male" value="Male"
                                                            {{ $beneficiary->spouse && $beneficiary->spouse->gender == 'Male' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="spouse_male">Male</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input spouse-field" type="radio"
                                                            name="spouse_gender" id="spouse_female" value="Female"
                                                            {{ $beneficiary->spouse && $beneficiary->spouse->gender == 'Female' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="spouse_female">Female</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Date of Birth:</label>
                                                <div class="col-md-9">
                                                    <input type="date" class="form-control spouse-field"
                                                        name="spouse_dob"
                                                        value="{{ old('spouse_dob', $beneficiary->spouse && $beneficiary->spouse->dob ? date('Y-m-d', strtotime($beneficiary->spouse->dob)) : '') }}">
                                                    <small class="text-muted">Format: dd-mm-yyyy</small>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Phone No:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control spouse-field"
                                                        name="spouse_phone"
                                                        value="{{ old('spouse_phone', $beneficiary->spouse ? $beneficiary->spouse->phone : '') }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">Email:</label>
                                                <div class="col-md-9">
                                                    <input type="email" class="form-control spouse-field"
                                                        name="spouse_email"
                                                        value="{{ old('spouse_email', $beneficiary->spouse ? $beneficiary->spouse->email : '') }}">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label class="col-md-3 form-label">NIN:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control spouse-field"
                                                        name="spouse_nin"
                                                        value="{{ old('spouse_nin', $beneficiary->spouse ? $beneficiary->spouse->nin : '') }}"
                                                        maxlength="11" placeholder="Enter 11-digit NIN">
                                                    <small class="text-muted">National Identification Number</small>
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
                                                            data-max-file-size="2M"
                                                            data-default-file="{{ $beneficiary->spouse && $beneficiary->spouse->photo ? url('storage/' . $beneficiary->spouse->photo) : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Children Information -->
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="hasChildren"
                                            name="has_children" value="1"
                                            {{ $beneficiary->children && $beneficiary->children->count() > 0 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="hasChildren">
                                            <h5 class="mb-0">Dependents: Children</h5>
                                        </label>
                                    </div>
                                </div>
                                <div id="childrenSection" class="card shadow-none border mb-4"
                                    style="{{ $beneficiary->children && $beneficiary->children->count() > 0 ? '' : 'display: none;' }}">
                                    <div class="card-body">
                                        <p class="text-muted">You can add up to 4 children. For children below the age of
                                            18 years, attach a photocopy of National Population Commission Birth
                                            Certificate.</p>

                                        @php
                                            // Get existing children or create empty array
                                            $children = $beneficiary->children ?? [];
                                            // Create an array of suffixes
                                            $suffixes = ['B', 'C', 'D', 'E'];
                                            $childCount = count($children);
                                        @endphp

                                        <div id="childrenContainer">
                                            @foreach ($children as $index => $child)
                                                <div class="child-record mb-4" id="child-{{ $index }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center bg-light p-2">
                                                        <h6 class="mb-0">Child {{ $index + 1 }}
                                                            ({{ $beneficiary->boschma_no }}{{ $suffixes[$index] }})</h6>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger remove-child"
                                                            data-index="{{ $index }}">
                                                            <i class="fe fe-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-5">
                                                            <div class="row mb-3">
                                                                <label class="col-md-3 form-label">Child Full Name:</label>
                                                                <div class="col-md-9">
                                                                    <input type="text" class="form-control child-field"
                                                                        name="child_name[]"
                                                                        value="{{ old('child_name.' . $index, $child->name) }}"
                                                                        required>
                                                                    <input type="hidden" name="child_id[]"
                                                                        value="{{ $child->id }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-3 form-label">Gender:</label>
                                                                <div class="col-md-9">
                                                                    <div class="form-check form-check-inline mt-2">
                                                                        <input class="form-check-input child-field"
                                                                            type="radio"
                                                                            name="child_gender[{{ $index }}]"
                                                                            id="child{{ $index }}_male"
                                                                            value="Male"
                                                                            {{ $child->gender == 'Male' ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="child{{ $index }}_male">Male</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input child-field"
                                                                            type="radio"
                                                                            name="child_gender[{{ $index }}]"
                                                                            id="child{{ $index }}_female"
                                                                            value="Female"
                                                                            {{ $child->gender == 'Female' ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="child{{ $index }}_female">Female</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-3 form-label">Date of Birth:</label>
                                                                <div class="col-md-9">
                                                                    <input type="date" class="form-control child-field"
                                                                        name="child_date_of_birth[]"
                                                                        value="{{ old('child_date_of_birth.' . $index, $child->dob ? date('Y-m-d', strtotime($child->dob)) : '') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-3 form-label">Birth Certificate
                                                                    No:</label>
                                                                <div class="col-md-9">
                                                                    <input type="text" class="form-control child-field"
                                                                        name="child_birth_certificate_no[]"
                                                                        value="{{ old('child_birth_certificate_no.' . $index, $child->birth_certificate_no) }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-3">
                                                                <label class="col-md-3 form-label">NIN:</label>
                                                                <div class="col-md-9">
                                                                    <input type="text" class="form-control child-field"
                                                                        name="child_nin[]"
                                                                        value="{{ old('child_nin.' . $index, $child->nin) }}"
                                                                        maxlength="11" placeholder="Enter 11-digit NIN">
                                                                    <small class="text-muted">National Identification
                                                                        Number</small>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-7">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="card mb-3">
                                                                        <div class="card-body text-center">
                                                                            <h6>Child's Photo</h6>
                                                                            <div class="mb-3 mt-3">
                                                                                <input type="file"
                                                                                    class="dropify-create child-field"
                                                                                    name="child_photo[]"
                                                                                    data-height="180"
                                                                                    data-allowed-file-extensions="jpg jpeg png"
                                                                                    data-max-file-size="2M"
                                                                                    data-default-file="{{ $child->photo ? url('storage/' . $child->photo) : '' }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="card">
                                                                        <div class="card-body text-center">
                                                                            <h6>Birth Certificate</h6>
                                                                            <div class="mb-3 mt-3">
                                                                                <input type="file"
                                                                                    class="dropify-create child-field"
                                                                                    name="child_birth_certificate_file[]"
                                                                                    data-height="180"
                                                                                    data-allowed-file-extensions="jpg jpeg png pdf"
                                                                                    data-max-file-size="2M"
                                                                                    data-default-file="{{ $child->birth_certificate_file ? url('storage/' . $child->birth_certificate_file) : '' }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-3">
                                            <button type="button" class="btn btn-outline-primary" id="addChildBtn">
                                                <i class="fe fe-plus-circle"></i> Add Child
                                            </button>
                                            <small class="text-muted ms-2">
                                                <span id="childCount">{{ $childCount }}</span>/4 children added
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fe fe-save"></i> Update Enrollment
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

    <script>
        $(document).ready(function() {
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
                    // Make text fields required, but not file inputs (photos don't need to be re-uploaded)
                    $('.spouse-field').not('input[type="file"]').prop('required', true);
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
            let childCount = parseInt($('#childCount').text());
            const MAX_CHILDREN = 4;
            const beneficiaryId = '{{ $beneficiary->boschma_no }}';
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
                const childHtml = `
                <div class="child-record mb-4" id="child-${index}">
                    <div class="d-flex justify-content-between align-items-center bg-light p-2">
                        <h6 class="mb-0">Child ${index + 1} (${beneficiaryId}${childSuffixes[index]})</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-child" data-index="${index}">
                            <i class="fe fe-trash"></i> Remove
                        </button>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-5">
                            <div class="row mb-3">
                                <label class="col-md-3 form-label">Child Full Name:</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control child-field" name="child_name[]" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label class="col-md-3 form-label">Gender:</label>
                                <div class="col-md-9">
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input child-field" type="radio" name="child_gender[${index}]" id="child${index}_male" value="Male">
                                        <label class="form-check-label" for="child${index}_male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input child-field" type="radio" name="child_gender[${index}]" id="child${index}_female" value="Female">
                                        <label class="form-check-label" for="child${index}_female">Female</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label class="col-md-3 form-label">Date of Birth:</label>
                                <div class="col-md-9">
                                    <input type="date" class="form-control child-field" name="child_date_of_birth[]">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label class="col-md-3 form-label">Birth Certificate No:</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control child-field" name="child_birth_certificate_no[]">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-md-3 form-label">NIN:</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control child-field" name="child_nin[]" maxlength="11" placeholder="Enter 11-digit NIN">
                                    <small class="text-muted">National Identification Number</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body text-center">
                                            <h6>Child's Photo</h6>
                                            <div class="mb-3 mt-3">
                                                <input type="file" class="dropify-create child-field" name="child_photo[]" data-height="180" data-allowed-file-extensions="jpg jpeg png" data-max-file-size="2M">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                    <div class="card-body text-center">
                                        <h6>Birth Certificate</h6>
                                        <div class="mb-3 mt-3">
                                            <input type="file" class="dropify-create child-field" name="child_birth_certificate_file[]" data-height="180" data-allowed-file-extensions="jpg jpeg png pdf" data-max-file-size="2M">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                $('#childrenContainer').append(childHtml);
                initializeDropify(); // Reinitialize dropify for the new file input
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
        });
    </script>
@endsection
