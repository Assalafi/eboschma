[ignoring loop detection]
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Summary of Approved Claims Report</title>
    <style>
        @page {
            margin: 20px 25px 40px 25px;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #111827;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-size: 11px;
            line-height: 1.4;
        }

        .report-header {
            width: 100%;
            margin-bottom: 15px;
        }

        /* 1. Centered Logo Container */
        .logo-container {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .logo-placeholder {
            width: 60px;
            height: 60px;
            margin: 0 auto;
            border-radius: 50%;
            border: 2px solid #005A2B; /* Professional Nigerian State Green */
            background-color: #f9fafb;
            text-align: center;
        }

        .logo-placeholder-text {
            color: #005A2B;
            font-size: 9px;
            font-weight: bold;
            line-height: 60px; /* Centers text vertically */
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* 2. Main Center Titles */
        .title-container {
            text-align: center;
            margin-bottom: 15px;
        }

        .main-title {
            font-size: 16px;
            font-weight: bold;
            color: #005A2B; /* Primary Accent Color */
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 12px;
            font-weight: 800;
            color: #111827;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* 3, 4, 5. Left & Right Aligned Info Tables (dompdf optimized) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: middle;
            font-size: 11px;
        }

        .info-left {
            text-align: left;
            width: 55%;
            font-weight: bold;
            color: #1f2937;
        }

        .info-right {
            text-align: right;
            width: 45%;
            font-weight: bold;
            color: #1f2937;
        }

        /* Labels styling for distinct hierarchy */
        .label {
            color: #4b5563; /* Slate grey for labels */
            font-weight: normal;
            text-transform: uppercase;
            font-size: 10px;
            margin-right: 5px;
        }

        .value {
            color: #111827;
            font-size: 11px;
        }

        /* Styling for the Provider Code dotted line to mimic image placeholders */
        .dotted-line {
            color: #9ca3af;
            letter-spacing: 1.5px;
        }

        /* Solid Header Boundary Line */
        .header-divider {
            border-top: 2px solid #005A2B;
            margin-top: 12px;
            margin-bottom: 15px;
            height: 0;
        }

        /* Claims Table styling */
        .claims-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .claims-table th {
            background-color: #005A2B;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border: 1px solid #004d25;
            text-align: left;
        }

        .claims-table td {
            padding: 6px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
            text-align: left;
        }

        .claims-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .total-row {
            font-weight: bold;
            background-color: #f3f4f6 !important;
        }

        .total-row td {
            border-top: 2px solid #005A2B;
            border-bottom: 2px solid #005A2B;
        }

        /* Verification / Signature Section */
        .signature-section {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-col {
            width: 50%;
            vertical-align: top;
            padding: 0 25px;
        }

        .sig-label {
            font-size: 11.5px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #005A2B;
            padding-bottom: 3px;
        }

        .sig-field-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-field-label {
            width: 22%;
            font-size: 10.5px;
            color: #4b5563;
            text-align: left;
            padding: 4px 0;
            vertical-align: bottom;
        }

        .sig-field-line {
            width: 78%;
            border-bottom: 1px dotted #9ca3af;
            padding: 4px 0;
            height: 12px;
            vertical-align: bottom;
        }

        .sig-field-value {
            width: 78%;
            font-size: 10.5px;
            font-weight: bold;
            color: #111827;
            padding: 4px 0;
            border-bottom: 1px dotted #9ca3af;
            vertical-align: bottom;
        }

        /* Page Numbering */
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }

        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

    <!-- Summary Page Header -->
    <div class="report-header">
        
        <!-- 1. Centered Logo -->
        <div class="logo-container">
            <!-- Reference the local logo path directly for reliable DomPDF local image rendering -->
            <img src="{{ public_path('assets/img/brand/logo.png') }}" alt="BOSCHMA Logo" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='block';" style="width: 60px; height: 60px; margin: 0 auto; display: block;">
            
            <div id="fallback-logo" class="logo-placeholder" style="display: none;">
                <span class="logo-placeholder-text">Logo</span>
            </div>
        </div>

        <!-- 2. Centered Main Headings -->
        <div class="title-container">
            <h1 class="main-title">Borno State Contributory Health Care Management Agency</h1>
            <h2 class="subtitle">Directorate of Standards & Quality Assurance</h2>
            <h3 class="report-title">Summary of Approved Claims Report</h3>
        </div>

        @php
            $firstClaim = $claims->first();
            $facilityName = $firstClaim && $firstClaim->facility ? $firstClaim->facility->name : 'N/A';
            $monthFormatted = $firstClaim && $firstClaim->service_date ? \Carbon\Carbon::parse($firstClaim->service_date)->format('F, Y') : now()->format('F, Y');
        @endphp

        <!-- 3, 4, 5. Left/Right Aligned Details Rows -->
        <table class="info-table">
            <!-- Row 3: Provider Details -->
            <tr>
                <td class="info-left">
                    <span class="label">Provider Name:</span>
                    <span class="value">{{ $facilityName }}</span>
                </td>
                <td class="info-right">
                    <span class="label">Provider Code:</span>
                    <span class="value dotted-line">................................</span>
                </td>
            </tr>
            <!-- Row 4: Month and Account Details -->
            <tr>
                <td class="info-left">
                    <span class="label">Month:</span>
                    <span class="value">{{ $monthFormatted }} ({{ $claims->count() }})</span>
                </td>
                <td class="info-right">
                    <span class="label">ACCOUNT NUMBER:</span>
                    <span class="value dotted-line">................................</span>
                </td>
            </tr>
            <!-- Row 5: Account Name and Bank Details -->
            <tr>
                <td class="info-left">
                    <span class="label">Account Name:</span>
                    <span class="value dotted-line">................................</span>
                </td>
                <td class="info-right">
                    <span class="label">BANK:</span>
                    <span class="value dotted-line">................................</span>
                </td>
            </tr>
        </table>

        <!-- Solid Header Boundary Line -->
        <div class="header-divider"></div>

    </div>

    <!-- Tabular Claims list -->
    <table class="claims-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 4%">S/N</th>
                <th style="width: 12%">Enrollee No</th>
                <th style="width: 20%">Beneficiary Name</th>
                <th style="width: 10%">Type</th>
                <th style="width: 10%">Service Date</th>
                <th style="width: 16%">Diagnosis (ICD)</th>
                <th class="text-right" style="width: 8%">Services</th>
                <th class="text-right" style="width: 8%">Drugs</th>
                <th class="text-right" style="width: 8%">Lab</th>
                <th class="text-right" style="width: 10%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalServices = 0;
                $totalDrugs = 0;
                $totalLab = 0;
                $grandTotal = 0;
            @endphp
            @forelse($claims as $index => $claim)
                @php
                    $servicesAmt = $claim->services_amount ?? $claim->consultation_amount ?? 0;
                    $drugsAmt = $claim->pharmacy_amount ?? 0;
                    $labAmt = $claim->laboratory_amount ?? 0;
                    
                    $totalServices += $servicesAmt;
                    $totalDrugs += $drugsAmt;
                    $totalLab += $labAmt;
                    $grandTotal += $claim->total_amount;

                    // Resolve diagnoses dynamically from either claim relations or encounter consultations
                    $dxDescriptions = [];
                    if ($claim->diagnoses && $claim->diagnoses->count() > 0) {
                        $dxDescriptions = $claim->diagnoses->pluck('diagnosis_description')->filter()->toArray();
                        if (empty($dxDescriptions)) {
                            $dxDescriptions = $claim->diagnoses->pluck('icd_code')->filter()->toArray();
                        }
                    } elseif ($claim->encounter) {
                        $claim->encounter->loadMissing('consultations.diagnoses.icdCode');
                        if ($claim->encounter->consultations) {
                            foreach ($claim->encounter->consultations as $consultation) {
                                if ($consultation->diagnoses) {
                                    foreach ($consultation->diagnoses as $dx) {
                                        if ($dx->icdCode && $dx->icdCode->description) {
                                            $dxDescriptions[] = $dx->icdCode->description;
                                        } elseif ($dx->icdCode && $dx->icdCode->code) {
                                            $dxDescriptions[] = $dx->icdCode->code;
                                        } elseif ($dx->notes) {
                                            $dxDescriptions[] = $dx->notes;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $dxText = !empty($dxDescriptions) ? implode(', ', array_unique($dxDescriptions)) : 'N/A';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $claim->enrollee_number ?? 'N/A' }}</td>
                    <td>{{ $claim->patient_name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($claim->claim_type) }}</td>
                    <td>{{ $claim->service_date ? \Carbon\Carbon::parse($claim->service_date)->format('d-M-Y') : 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($dxText, 30, '...') }}</td>
                    <td class="text-right">₦{{ number_format($servicesAmt, 2) }}</td>
                    <td class="text-right">₦{{ number_format($drugsAmt, 2) }}</td>
                    <td class="text-right">₦{{ number_format($labAmt, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">₦{{ number_format($claim->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #6b7280;">
                        No Approved Claims found matching your filter criteria.
                    </td>
                </tr>
            @endforelse
            
            @if($claims->count() > 0)
                <tr class="total-row">
                    <td colspan="6" class="text-right">GRAND TOTALS:</td>
                    <td class="text-right">₦{{ number_format($totalServices, 2) }}</td>
                    <td class="text-right">₦{{ number_format($totalDrugs, 2) }}</td>
                    <td class="text-right">₦{{ number_format($totalLab, 2) }}</td>
                    <td class="text-right">₦{{ number_format($grandTotal, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Verification / Signatures Queue -->
    <!-- Verification / Signatures Queue -->
    @if($claims->count() > 0)
        <div class="signature-section">
            <table class="signature-table">
                <!-- Row 1: Director SQA & Internal Auditor -->
                <tr>
                    <!-- 1. Director SQA -->
                    <td class="sig-col">
                        <div class="sig-label">Director SQA:</div>
                        <table class="sig-field-table">
                            <tr>
                                <td class="sig-field-label">Name:</td>
                                <td class="sig-field-line"></td>
                            </tr>
                            <tr>
                                <td class="sig-field-label" style="height: 35px; vertical-align: bottom;">Sig/Stamp:</td>
                                <td class="sig-field-line" style="height: 35px; vertical-align: bottom;"></td>
                            </tr>
                            <tr>
                                <td class="sig-field-label">Date:</td>
                                <td class="sig-field-line"></td>
                            </tr>
                        </table>
                    </td>

                    <!-- 2. Internal Auditor -->
                    <td class="sig-col">
                        <div class="sig-label">Internal Auditor:</div>
                        <table class="sig-field-table">
                            <tr>
                                <td class="sig-field-label">Name:</td>
                                <td class="sig-field-line"></td>
                            </tr>
                            <tr>
                                <td class="sig-field-label" style="height: 35px; vertical-align: bottom;">Sig/Stamp:</td>
                                <td class="sig-field-line" style="height: 35px; vertical-align: bottom;"></td>
                            </tr>
                            <tr>
                                <td class="sig-field-label">Date:</td>
                                <td class="sig-field-line"></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Spacer between signatory rows -->
                <tr>
                    <td colspan="2" style="height: 25px;"></td>
                </tr>

                <!-- Row 2: Executive Secretary & Chief Accountant -->
                <tr>
                    <!-- 3. Executive Secretary -->
                    <td class="sig-col">
                        <div class="sig-label">Executive Secretary:</div>
                        <table class="sig-field-table">
                            <tr>
                                <td class="sig-field-label">Name:</td>
                                <td class="sig-field-value">Dr. Saleh Abba Kaza</td>
                            </tr>
                            <tr>
                                <td class="sig-field-label" style="height: 35px; vertical-align: bottom;">Sig/Stamp:</td>
                                <td class="sig-field-line" style="height: 35px; vertical-align: bottom; position: relative;">
                                    <div style="position: absolute; bottom: 4px; left: 10px; width: 80px; height: 30px;">
                                        <img src="{{ public_path('signature/es_signature.png') }}" style="max-height: 30px; max-width: 80px; display: block;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="sig-field-label">Date:</td>
                                <td class="sig-field-line"></td>
                            </tr>
                        </table>
                    </td>

                    <!-- 4. Chief Accountant -->
                    <td class="sig-col">
                        <div class="sig-label">Chief Accountant:</div>
                        <table class="sig-field-table">
                            <tr>
                                <td class="sig-field-label">Name:</td>
                                <td class="sig-field-value">Yada BK Imam</td>
                            </tr>
                            <tr>
                                <td class="sig-field-label" style="height: 35px; vertical-align: bottom;">Sig/Stamp:</td>
                                <td class="sig-field-line" style="height: 35px; vertical-align: bottom; position: relative;">
                                    <div style="position: absolute; bottom: 4px; left: 10px; width: 80px; height: 30px;">
                                        <img src="{{ public_path('signature/es_signature.png') }}" style="max-height: 30px; max-width: 80px; display: block;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="sig-field-label">Date:</td>
                                <td class="sig-field-line"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Footer Page Numbers -->
    <div class="footer">
        BOSCHMA Directorate of Standards & Quality Assurance Summary Report &bull; Page <span class="page-number"></span>
    </div>

</body>
</html>
