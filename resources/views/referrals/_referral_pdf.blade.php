<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Referral Slip</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #000;
            line-height: 2.0;
            margin: 0;
            padding: 20px;
            position: relative;
        }
        
        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.15;
            z-index: -1;
            width: 300px;
        }

        /* Header containing logo and photo */
        .header-container {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }
        .logo-left {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
        }
        .photo-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: middle;
        }
        .logo-left img {
            width: 100px;
        }
        .photo-right img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px; /* Slight rounding based on image */
        }
        
        .agency-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        /* Information list */
        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.info-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        table.info-table td.label {
            font-weight: bold;
            width: 35%;
        }
        table.info-table td.value {
            width: 65%;
        }
        
        /* Blank row for spacing */
        tr.spacer td {
            padding: 10px 0;
        }

        .date-bottom {
            text-align: right;
            margin-top: 50px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $patient = $referral->encounter->patient ?? null;
        $enrolleeDetails = $patient ? $patient->enrolleeDetails : null;
        $photoPath = null;
        
        if ($enrolleeDetails && $enrolleeDetails->photo) {
            if (str_starts_with($enrolleeDetails->photo, 'http')) {
                // Not highly recommended for dompdf to fetch HTTP, but keeping fallback
                $photoPath = $enrolleeDetails->photo;
            } else {
                $photoPath = storage_path('app/public/' . $enrolleeDetails->photo);
            }
        }
        
        if (!$photoPath || !file_exists($photoPath) && !str_starts_with($photoPath, 'http')) {
            $photoPath = public_path('assets/img/users/1.jpg');
        }

        $logoPath = public_path('assets/img/brand/logo.png'); 

        // Helper to encode image
        $base64Photo = '';
        if ($photoPath) {
            try {
                if (str_starts_with($photoPath, 'http')) {
                    $imgData = file_get_contents($photoPath);
                    $base64Photo = 'data:image/jpeg;base64,' . base64_encode($imgData);
                } elseif (file_exists($photoPath)) {
                    $type = pathinfo($photoPath, PATHINFO_EXTENSION);
                    $imgData = file_get_contents($photoPath);
                    $base64Photo = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
                }
            } catch (\Exception $e) {}
        }
        
        $base64Logo = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $imgData = file_get_contents($logoPath);
            $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        $consultations = $referral->encounter ? $referral->encounter->consultations : collect([]);
        $firstConsult = $consultations->first();
        
        $clinicalFindings = $firstConsult->clinical_note ?? 'N/A';
        
        $diagnoses = [];
        if($firstConsult && $firstConsult->diagnoses) {
            foreach($firstConsult->diagnoses as $diag) {
                $diagnoses[] = $diag->icdCode->description ?? 'N/A';
            }
        }
        $diagnosisStr = count($diagnoses) > 0 ? implode(', ', $diagnoses) : 'N/A';
    @endphp

    <!-- Watermark -->
    @if($base64Logo)
        <img src="{{ $base64Logo }}" class="watermark" alt="Watermark">
    @endif

    <div class="header-container">
        <div class="logo-left">
            @if($base64Logo)
                <img src="{{ $base64Logo }}" alt="BOSCHMA Logo">
            @else
                <h2>BOSCHMA</h2>
            @endif
        </div>
        <div class="photo-right">
            @if($base64Photo)
                <img src="{{ $base64Photo }}" alt="Patient Photo">
            @endif
        </div>
    </div>

    <div class="agency-title">
        BORNO STATE CONTRIBUTORY HEALTHCARE MANAGEMENT AGENCY
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Authorization Code:</td>
            <td class="value">{{ $referral->authorization->authorization_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">BOSCHMA Number:</td>
            <td class="value">{{ $patient->enrollee_number ?? 'N/A' }}</td>
        </tr>
        
        <tr class="spacer"><td colspan="2"></td></tr>
        
        <tr>
            <td class="label">Beneficiary Name:</td>
            <td class="value">{{ $enrolleeDetails->fullname ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Phone Number:</td>
            <td class="value">{{ $enrolleeDetails->phone_no ?? ($enrolleeDetails->phone ?? 'N/A') }}</td>
        </tr>
        
        <tr class="spacer"><td colspan="2"></td></tr>
        
        <tr>
            <td class="label">From Facility:</td>
            <td class="value">{{ $referral->fromFacility->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Facility Referred to:</td>
            <td class="value">{{ $referral->toFacility->name ?? 'N/A' }}</td>
        </tr>
        
        <tr class="spacer"><td colspan="2"></td></tr>
        
        <tr>
            <td class="label">Clinical Findings:</td>
            <td class="value">{{ $clinicalFindings }}</td>
        </tr>
        <tr>
            <td class="label">Investigation:</td>
            <td class="value">N/A</td>
        </tr>
        <tr>
            <td class="label">Diagnosis:</td>
            <td class="value">{{ $diagnosisStr }}</td>
        </tr>
        <tr>
            <td class="label">Reason for Referral:</td>
            <td class="value">{{ $referral->reason ?? ($referral->serviceItem->name ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Treatment Before Referral:</td>
            <td class="value">NONE</td>
        </tr>
    </table>

    <div class="date-bottom">
        Date: {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
