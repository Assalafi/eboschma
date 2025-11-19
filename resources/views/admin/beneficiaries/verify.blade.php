@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header mb-3">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title mb-1" style="color: #01542B;"><i class="fe fe-shield"></i> Enrollment Verification
                    </h4>
                    <p class="text-muted mb-0 small">Verify beneficiary details before proceeding to enrollment</p>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header py-2" style="background-color: #01542B; color: white;">
                        <h6 class="mb-0"><i class="fe fe-shield"></i> Verification Steps</h6>
                    </div>
                    <div class="card-body p-3">
                        <form id="verificationForm">
                            <!-- Compact Step Indicators -->
                            <div class="row mb-3">
                                <div class="col-4 text-center">
                                    <div class="step-indicator" id="indicator_1">
                                        <span class="step-number">1</span>
                                        <p class="mb-0 small">Program</p>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="step-indicator" id="indicator_2">
                                        <span class="step-number">2</span>
                                        <p class="mb-0 small">NIN Check</p>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="step-indicator" id="indicator_3">
                                        <span class="step-number">3</span>
                                        <p class="mb-0 small">DP Verify</p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Step 1: Program Selection -->
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <label for="program_id" class="font-weight-bold" style="color: #01542B;"><span
                                            class="badge badge-sm" style="background-color: #01542B;">1</span> Program <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="program_id" name="program_id" required>
                                        <option value="">-- Select Program --</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}
                                                ({{ $program->format }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Step 2: NIN Verification -->
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <label for="nin" class="font-weight-bold" style="color: #01542B;"><span
                                            class="badge badge-sm" style="background-color: #01542B;">2</span> NIN <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="nin" name="nin"
                                            placeholder="11-digit NIN" maxlength="11" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-sm" type="button" id="verifyNinBtn"
                                                style="background-color: #01542B; color: white;" disabled>
                                                <i class="fe fe-search"></i> Verify
                                            </button>
                                        </div>
                                    </div>
                                    <div id="ninFeedback" class="mt-1"></div>
                                </div>
                            </div>

                            <!-- Step 3: DP Number Verification -->
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <label for="dp_no" class="font-weight-bold" style="color: #01542B;"><span
                                            class="badge badge-sm" style="background-color: #01542B;">3</span> DP Number
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dp_no" name="dp_no"
                                            placeholder="Enter DP Number" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-sm" type="button" id="verifyDpBtn"
                                                style="background-color: #01542B; color: white;" disabled>
                                                <i class="fe fe-search"></i> Verify
                                            </button>
                                        </div>
                                    </div>
                                    <div id="dpFeedback" class="mt-1"></div>
                                </div>
                            </div>

                            <!-- Civil Servant Details Display -->
                            <div id="civilServantDetails" class="mt-2" style="display: none;">
                                <div class="alert alert-success mb-2 py-2">
                                    <strong><i class="fe fe-check-circle"></i> Civil Servant Found</strong>
                                    <div class="row mt-2 small">
                                        <div class="col-md-4"><strong>Name:</strong> <span id="cs_fullname"></span></div>
                                        <div class="col-md-4"><strong>Gender:</strong> <span id="cs_gender"></span></div>
                                        <div class="col-md-4"><strong>Phone:</strong> <span id="cs_phone"></span></div>
                                        <div class="col-md-4"><strong>Email:</strong> <span id="cs_email"></span></div>
                                        <div class="col-md-4"><strong>LGA:</strong> <span id="cs_lga"></span></div>
                                        <div class="col-md-4"><strong>DP:</strong> <span id="cs_dp_no"></span></div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Action Buttons -->
                            <div class="text-right mt-2">
                                <a href="{{ route('beneficiaries.index') }}" class="btn btn-secondary">
                                    <i class="fe fe-x"></i> Cancel
                                </a>
                                <button type="submit" id="proceedBtn" class="btn text-white"
                                    style="background-color: #01542B;" disabled>
                                    <i class="fe fe-arrow-right"></i> Proceed to Enrollment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Verification Status Sidebar -->
            <div class="col-lg-3">
                <div class="card shadow-sm" style="border-left: 4px solid #01542B;">
                    <div class="card-header py-2" style="background-color: #f8f9fa; border-bottom: 1px solid #01542B;">
                        <h6 class="mb-0" style="color: #01542B;"><i class="fe fe-check-square"></i> Status</h6>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-unstyled mb-0 small">
                            <li id="check_program" class="mb-1 py-1">
                                <i class="fe fe-circle text-muted"></i> Program Selected
                            </li>
                            <li id="check_nin" class="mb-1 py-1">
                                <i class="fe fe-circle text-muted"></i> NIN Available
                            </li>
                            <li id="check_dp" class="mb-1 py-1">
                                <i class="fe fe-circle text-muted"></i> DP Verified
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="card shadow-sm mt-2" style="border-left: 4px solid #01542B;">
                    <div class="card-body p-2">
                        <h6 class="mb-1" style="color: #01542B; font-size: 0.9rem;"><i class="fe fe-info"></i> Info
                        </h6>
                        <p class="mb-0 small text-muted">Complete all 3 verification steps to proceed with enrollment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Step Indicators */
        .step-indicator {
            display: inline-block;
            padding: 10px;
        }

        .step-number {
            display: inline-block;
            width: 35px;
            height: 35px;
            line-height: 35px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .step-indicator.active .step-number {
            background-color: #01542B;
            color: white;
            box-shadow: 0 2px 8px rgba(1, 84, 43, 0.3);
        }

        .step-indicator.completed .step-number {
            background-color: #28a745;
            color: white;
        }

        /* Compact Alerts */
        .alert {
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        /* Badge Sizing */
        .badge-sm {
            font-size: 0.75rem;
            padding: 3px 6px;
            margin-right: 5px;
        }

        /* Button Styling */
        #proceedBtn:not(:disabled):hover {
            background-color: #013d1f !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(1, 84, 43, 0.3);
            transition: all 0.2s ease;
        }

        #proceedBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #verifyNinBtn:not(:disabled):hover,
        #verifyDpBtn:not(:disabled):hover {
            opacity: 0.9;
        }

        /* Spinner */
        .spinner-border-sm {
            width: 0.875rem;
            height: 0.875rem;
            border-width: 0.125em;
        }

        /* Compact Form Controls */
        .form-control {
            font-size: 0.95rem;
        }

        /* Card Adjustments */
        .card {
            border-radius: 6px;
        }

        .card-header {
            border-radius: 6px 6px 0 0 !important;
        }

        /* ========================================
           MOBILE RESPONSIVE STYLES
        ======================================== */
        @media (max-width: 768px) {

            /* Page Header */
            .page-header {
                margin-bottom: 10px !important;
            }

            .page-title {
                font-size: 1.25rem !important;
            }

            /* Step Indicators - Smaller on Mobile */
            .step-indicator {
                padding: 5px;
            }

            .step-number {
                width: 30px;
                height: 30px;
                line-height: 30px;
                font-size: 0.9rem;
            }

            .step-indicator p {
                font-size: 0.7rem !important;
            }

            /* Card Body - Less Padding */
            .card-body {
                padding: 15px !important;
            }

            .card-header {
                padding: 10px 15px !important;
            }

            .card-header h6 {
                font-size: 0.9rem;
            }

            /* Form Spacing */
            .row.mb-2 {
                margin-bottom: 12px !important;
            }

            .row.mb-3 {
                margin-bottom: 15px !important;
            }

            /* Labels */
            label {
                font-size: 0.9rem !important;
                margin-bottom: 5px !important;
            }

            /* Input Groups - Stack on Mobile */
            .input-group {
                flex-wrap: nowrap;
            }

            .input-group .form-control {
                font-size: 0.9rem;
                min-width: 0;
            }

            .input-group-append .btn {
                font-size: 0.85rem;
                padding: 6px 10px;
                white-space: nowrap;
            }

            /* Buttons - Better Touch Targets */
            .btn {
                padding: 10px 15px;
                font-size: 0.9rem;
                min-height: 44px;
                /* iOS recommended touch target */
            }

            .btn-sm {
                min-height: 38px;
            }

            /* Action Buttons - Stack on Small Screens */
            .text-right {
                text-align: center !important;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .text-right .btn {
                width: 100%;
                margin: 0 !important;
            }

            /* Alerts - Smaller Text */
            .alert {
                font-size: 0.85rem;
                padding: 10px;
            }

            /* Civil Servant Details - Stack Columns */
            #civilServantDetails .row .col-md-4 {
                margin-bottom: 8px;
                font-size: 0.85rem;
            }

            #civilServantDetails .alert {
                padding: 10px 12px;
            }

            /* Sidebar - Full Width on Mobile */
            .col-lg-3 {
                margin-top: 15px;
            }

            /* Sidebar Cards - Compact */
            .col-lg-3 .card-body {
                padding: 12px !important;
            }

            .col-lg-3 ul li {
                font-size: 0.85rem !important;
                padding: 5px 0 !important;
            }

            /* Badge */
            .badge-sm {
                font-size: 0.7rem;
                padding: 2px 5px;
            }

            /* Form Controls */
            .form-control {
                font-size: 0.9rem !important;
                height: calc(2.5em + 0.75rem + 2px);
            }

            select.form-control {
                height: calc(2.5em + 0.75rem + 2px);
            }
        }

        @media (max-width: 576px) {

            /* Extra Small Devices */
            .page-title {
                font-size: 1.1rem !important;
            }

            .card-header h6 {
                font-size: 0.85rem;
            }

            /* Step Indicators - Even Smaller */
            .step-number {
                width: 28px;
                height: 28px;
                line-height: 28px;
                font-size: 0.85rem;
            }

            .step-indicator p {
                font-size: 0.65rem !important;
            }

            /* Tighter Spacing */
            .card-body {
                padding: 12px !important;
            }

            /* Input Groups */
            .input-group-append .btn {
                padding: 6px 8px;
                font-size: 0.8rem;
            }

            .input-group-append .btn i {
                margin-right: 0;
            }

            /* Hide text, show icon only on very small screens */
            .input-group-append .btn-text-hide {
                font-size: 0;
            }

            .input-group-append .btn i::before {
                font-size: 1rem;
            }

            /* Civil Servant Details */
            #civilServantDetails .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        @media (min-width: 769px) and (max-width: 991px) {

            /* Tablet View */
            .col-lg-9 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .col-lg-3 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-top: 15px;
            }

            /* Sidebar as Horizontal Cards on Tablet */
            .col-lg-3>.row {
                display: flex;
            }

            .col-lg-3 .card {
                margin-bottom: 15px;
            }
        }
    </style>

    <script>
        let verificationStatus = {
            program: false,
            nin: false,
            dp: false,
            civilServantData: null
        };

        document.addEventListener('DOMContentLoaded', function() {
            const programSelect = document.getElementById('program_id');
            const ninInput = document.getElementById('nin');
            const dpInput = document.getElementById('dp_no');
            const verifyNinBtn = document.getElementById('verifyNinBtn');
            const verifyDpBtn = document.getElementById('verifyDpBtn');
            const proceedBtn = document.getElementById('proceedBtn');
            const verificationForm = document.getElementById('verificationForm');

            // Program selection
            programSelect.addEventListener('change', function() {
                verificationStatus.program = this.value !== '';
                updateChecklist('program', verificationStatus.program);
                checkFormCompletion();
            });

            // NIN input validation
            ninInput.addEventListener('input', function() {
                const value = this.value.replace(/\D/g, ''); // Only numbers
                this.value = value;

                verifyNinBtn.disabled = value.length !== 11;

                if (value.length !== 11) {
                    verificationStatus.nin = false;
                    updateChecklist('nin', false);
                    checkFormCompletion();
                }
            });

            // DP Number input validation
            dpInput.addEventListener('input', function() {
                const value = this.value.trim();
                verifyDpBtn.disabled = value.length === 0;

                if (value.length === 0) {
                    verificationStatus.dp = false;
                    updateChecklist('dp', false);
                    checkFormCompletion();
                }
            });

            // Verify NIN
            verifyNinBtn.addEventListener('click', function() {
                const nin = ninInput.value;
                if (nin.length !== 11) return;

                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Checking...';
                this.disabled = true;

                fetch(`/beneficiaries/verify-nin/${nin}`)
                    .then(response => response.json())
                    .then(data => {
                        const feedbackDiv = document.getElementById('ninFeedback');

                        if (data.available && data.in_progress) {
                            // NIN belongs to in-progress enrollment - allow continuation
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fe fe-info-circle"></i> <strong>In Progress!</strong> 
                            ${data.message}
                        </div>
                    `;
                            verificationStatus.nin = true;
                            verificationStatus.ninBeneficiaryId = data
                            .beneficiary_id; // Store for continuation
                            updateChecklist('nin', true);
                        } else if (data.available) {
                            // NIN is completely available
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fe fe-check-circle"></i> <strong>NIN Available!</strong> This NIN is not currently in use.
                        </div>
                    `;
                            verificationStatus.nin = true;
                            updateChecklist('nin', true);
                        } else {
                            // NIN already exists and is completed
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fe fe-x-circle"></i> <strong>NIN Already Exists!</strong> 
                            This NIN is already used by ${data.used_by} (${data.record_type}${data.boschma_no ? ' - ' + data.boschma_no : ''}).
                        </div>
                    `;
                            verificationStatus.nin = false;
                            updateChecklist('nin', false);
                        }

                        checkFormCompletion();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('ninFeedback').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fe fe-alert-triangle"></i> Error checking NIN. Please try again.
                    </div>
                `;
                    })
                    .finally(() => {
                        this.innerHTML = originalHtml;
                        this.disabled = false;
                    });
            });

            // Verify DP Number
            verifyDpBtn.addEventListener('click', function() {
                const dpNo = dpInput.value.trim();
                if (!dpNo) return;

                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Checking...';
                this.disabled = true;

                fetch(`/beneficiaries/verify-dp/${encodeURIComponent(dpNo)}`)
                    .then(response => response.json())
                    .then(data => {
                        const feedbackDiv = document.getElementById('dpFeedback');
                        const detailsDiv = document.getElementById('civilServantDetails');

                        if (data.already_enrolled) {
                            // DP already fully enrolled - don't allow
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fe fe-x-circle"></i> <strong>Already Enrolled!</strong> 
                            ${data.message}
                        </div>
                    `;
                            detailsDiv.style.display = 'none';
                            verificationStatus.dp = false;
                            updateChecklist('dp', false);
                        } else if (data.found && data.in_progress) {
                            // DP has enrollment in progress - allow continuation
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fe fe-info-circle"></i> <strong>In Progress!</strong> 
                            ${data.message}
                        </div>
                    `;

                            // Display beneficiary details
                            document.getElementById('cs_fullname').textContent = data.civil_servant
                                .fullname || 'N/A';
                            document.getElementById('cs_dp_no').textContent = data.civil_servant
                                .dp_no || 'N/A';
                            document.getElementById('cs_gender').textContent = data.civil_servant
                                .gender || 'N/A';
                            document.getElementById('cs_phone').textContent = data.civil_servant
                                .phone_no || 'N/A';
                            document.getElementById('cs_email').textContent = data.civil_servant
                                .email || 'N/A';
                            document.getElementById('cs_lga').textContent = data.civil_servant.lga ||
                                'N/A';

                            detailsDiv.style.display = 'block';
                            verificationStatus.dp = true;
                            verificationStatus.civilServantData = data.civil_servant;
                            verificationStatus.beneficiaryId = data
                            .beneficiary_id; // Store for continuation
                            verificationStatus.inProgress = true;
                            updateChecklist('dp', true);
                        } else if (data.found) {
                            // New civil servant found
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fe fe-check-circle"></i> <strong>Civil Servant Found!</strong> Record verified successfully.
                        </div>
                    `;

                            // Display civil servant details
                            document.getElementById('cs_fullname').textContent = data.civil_servant
                                .fullname || 'N/A';
                            document.getElementById('cs_dp_no').textContent = data.civil_servant
                                .dp_no || 'N/A';
                            document.getElementById('cs_gender').textContent = data.civil_servant
                                .gender || 'N/A';
                            document.getElementById('cs_phone').textContent = data.civil_servant
                                .phone_no || 'N/A';
                            document.getElementById('cs_email').textContent = data.civil_servant
                                .email || 'N/A';
                            document.getElementById('cs_lga').textContent = data.civil_servant.lga ||
                                'N/A';

                            detailsDiv.style.display = 'block';
                            verificationStatus.dp = true;
                            verificationStatus.civilServantData = data.civil_servant;
                            verificationStatus.inProgress = false;
                            updateChecklist('dp', true);
                        } else {
                            feedbackDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fe fe-x-circle"></i> <strong>DP Number Not Found!</strong> 
                            No civil servant record exists for this DP Number.
                        </div>
                    `;
                            detailsDiv.style.display = 'none';
                            verificationStatus.dp = false;
                            updateChecklist('dp', false);
                        }

                        checkFormCompletion();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('dpFeedback').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fe fe-alert-triangle"></i> Error verifying DP Number. Please try again.
                    </div>
                `;
                    })
                    .finally(() => {
                        this.innerHTML = originalHtml;
                        this.disabled = false;
                    });
            });

            // Form submission
            verificationForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (verificationStatus.program && verificationStatus.nin && verificationStatus.dp) {
                    // Store verification data in session storage
                    // Use beneficiary_id from either NIN or DP verification (both should match if in progress)
                    const beneficiaryId = verificationStatus.beneficiaryId || verificationStatus
                        .ninBeneficiaryId || null;
                    const inProgress = verificationStatus.inProgress || (verificationStatus
                        .ninBeneficiaryId ? true : false);

                    const verificationData = {
                        program_id: programSelect.value,
                        nin: ninInput.value,
                        dp_no: dpInput.value,
                        civil_servant: verificationStatus.civilServantData,
                        in_progress: inProgress,
                        beneficiary_id: beneficiaryId
                    };

                    sessionStorage.setItem('beneficiaryVerification', JSON.stringify(verificationData));

                    // Redirect to create page
                    window.location.href = "{{ route('beneficiaries.create') }}?verified=true";
                }
            });

            function updateChecklist(type, status) {
                const checkElement = document.getElementById(`check_${type}`);
                const icon = checkElement.querySelector('i');

                // Update checklist icon
                if (status) {
                    icon.className = 'fe fe-check-circle text-success';
                } else {
                    icon.className = 'fe fe-circle text-muted';
                }

                // Update step indicator
                const stepMap = {
                    program: 1,
                    nin: 2,
                    dp: 3
                };
                const stepNum = stepMap[type];
                const indicator = document.getElementById(`indicator_${stepNum}`);

                if (indicator) {
                    if (status) {
                        indicator.classList.remove('active');
                        indicator.classList.add('completed');
                    } else {
                        indicator.classList.remove('completed');
                    }
                }
            }

            function checkFormCompletion() {
                const allVerified = verificationStatus.program && verificationStatus.nin && verificationStatus.dp;
                proceedBtn.disabled = !allVerified;

                if (allVerified) {
                    proceedBtn.style.opacity = '1';
                } else {
                    proceedBtn.style.opacity = '0.6';
                }
            }
        });
    </script>
@endsection
