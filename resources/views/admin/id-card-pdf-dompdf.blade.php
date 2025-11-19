<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Borno State Healthcare ID Card</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px 0;
            background: white;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            text-align: center;
        }

        table {
            border-collapse: collapse;
        }

        .id-card {
            width: 95mm;
            height: 100mm;
            /* background: rgb(0, 0, 0); */
            /* border-radius: 3mm; */
            border: 0.5mm solid #000000;
            overflow: hidden;
            position: relative;
            display: inline-block;
            vertical-align: top;
            margin: 0;
            /* box-shadow: 0 10px 40px rgba(1, 102, 52, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1); */
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .id-card:hover {
            transform: translateY(-2mm);
            box-shadow: 0 15px 50px rgba(1, 102, 52, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        /* Principal Card Styles */
        .principal-card {
            /* border-radius: 4mm; */
            /* box-shadow: 0 12px 40px rgba(1, 102, 52, 0.25),
                0 5px 15px rgba(0, 0, 0, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.8); */
            overflow: hidden;
            /* background: linear-gradient(135deg, #e0f2e9 0%, #d4f1e8 25%, #c8f0e7 50%, #e8f5e9 100%); */
            position: relative;
        }


        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 35mm;
            height: 35mm;
            opacity: 0.08;
            pointer-events: none;
            z-index: -1;
            object-fit: contain;
        }

        .watermark-text-primary {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 18mm;
            font-weight: 900;
            color: rgba(1, 102, 52, 0.06);
            letter-spacing: 2mm;
            white-space: nowrap;
            pointer-events: none;
            z-index: -1;
            text-transform: uppercase;
        }

        .card-header {
            /* background: rgba(1, 102, 52, 0.05); */
            padding: 2.9mm;
            height: 35mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
            margin: 0;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 2.5mm;
            width: 100%;
            position: relative;
        }

        .qr-section {
            align-items: center;
            justify-content: center;
            width: 15%;
            float: right;
            padding: 10px;
        }

        .qr-code-header {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-circle {
            width: 50px;
            text-align: center;
        }

        .agency-info {
            flex: 1;
            text-align: center;
        }

        .agency-name {
            font-size: 2.8mm;
            font-weight: 700;
            color: #016634;
            text-transform: uppercase;
            letter-spacing: 0.1mm;
        }

        .agency-subtitle {
            font-size: 2.1mm;
            font-weight: 500;
            color: #2c3e50;
            text-transform: uppercase;
            margin: 0;
        }

        .scheme-type {
            font-size: 1.5mm;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #016634 0%, #028a4c 50%, #03a862 100%);
            padding: 0.4mm 1mm;
            border-radius: 3mm;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(1, 102, 52, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            display: inline-block;
        }

        .card-body {
            padding: 2mm;
            height: 34mm;
            display: flex;
            gap: 1mm;
            position: relative;
            z-index: 1;
            margin: 0;
        }

        .photo-section {
            width: 23mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            /* filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1)); */
        }

        .avatar-circle {
            /* width: 22mm;
            height: 24mm; */
            /* border-radius: 3mm; */
            /* display: flex;
            align-items: center;
            justify-content: center; */
            /* background: #f5f5f5; */
            /* border: 1px solid #ddd; */
            /* position: relative;
            overflow: hidden; */
        }

        .avatar-circle::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 6mm;
            /* background: rgba(1, 102, 52, 0.1); */
        }

        .role-badge {
            background: #016634;
            color: white;
            padding: 0.8mm 2mm;
            border-radius: 2mm;
            font-size: 1.8mm;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2mm;
            margin-top: 1mm;
            box-shadow: 0 3px 10px rgba(1, 102, 52, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .role-badge-2 {
            background: #016634;
            color: white;
            padding: 0.8mm 2mm;
            border-radius: 2mm;
            font-size: 1.4mm;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2mm;
            margin-top: 1mm;
            margin-bottom: 5px;
            box-shadow: 0 3px 10px rgba(1, 102, 52, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .info-section {
            flex: 1;
            margin-left: 3mm;
            padding: 1mm 2mm;
            display: flex;
            flex-direction: column;
            gap: 1.2mm;
        }

        .info-row {
            margin: 0;
            padding: 0.8mm 1.5mm;
            /* background: rgba(1, 102, 52, 0.05); */
            border-radius: 2mm;
            line-height: 1.4;
            border: 1px solid rgba(1, 102, 52, 0.1);
            font-size: 1.6mm;
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
        }

        .info-row:hover {
            background: rgba(1, 102, 52, 0.08);
        }

        .label {
            font-weight: 600;
            color: #016634;
            font-size: 1.5mm;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.05mm;
            flex: 0.3;
            margin: 0;
        }

        .value {
            color: #2c3e50;
            font-size: 2.0mm;
            font-weight: 700;
            flex: 0.8;
            text-align: left;
        }

        /* When label is empty, value takes full width */
        .info-row:has(.label:empty) .value {
            flex: 1;
            text-align: center;
        }

        /* Alternative for browsers that don't support :has() */
        .info-row .label:empty+.value {
            flex: 1;
            text-align: center;
        }

        /* When info-row only has value (no label element) */
        .info-row:only-child .value,
        .info-row>.value:only-child {
            flex: 1;
            text-align: center;
        }

        .card-footer {
            background: rgba(1, 102, 52, 0.05);
            padding: 2mm 3mm;
            height: 9mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        .expiry-date {
            font-size: 1.3mm;
            text-align: left;
            line-height: 1.4;
        }

        .expiry-label {
            color: #7f8c8d;
            font-size: 1.1mm;
            font-weight: 600;
            display: inline;
            text-transform: uppercase;
            letter-spacing: 0.05mm;
        }

        .expiry-value {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.3mm;
            margin-left: 0.5mm;
        }

        .qr-codes {
            display: flex;
            gap: 1.5mm;
            align-items: center;
        }

        .qr-code {
            width: 7mm;
            height: 7mm;
            background: linear-gradient(145deg, #ffffff 0%, #f8f8f8 100%);
            border: none;
            border-radius: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #016634;
            font-size: 1mm;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .barcode {
            width: 14mm;
            height: 7mm;
            background: linear-gradient(145deg, #ffffff 0%, #f8f8f8 100%);
            border: none;
            border-radius: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Libre Barcode 128', monospace;
            font-size: 3.5mm;
            color: #2c3e50;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Dependants Card Styles */
        .dependants-card {
            /* border-radius: 4mm; */
            /* box-shadow: 0 12px 40px rgba(1, 102, 52, 0.25),
                0 5px 15px rgba(0, 0, 0, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.8); */
            overflow: hidden;
            /* background: linear-gradient(135deg, #e0f2e9 0%, #d4f1e8 25%, #c8f0e7 50%, #e8f5e9 100%); */
            position: relative;
        }


        .dependants-card .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30mm;
            height: 30mm;
            opacity: 0.08;
            pointer-events: none;
            z-index: -1;
            object-fit: contain;
        }

        .dependants-watermark-primary {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 10mm;
            font-weight: 900;
            color: rgba(1, 102, 52, 0.06);
            letter-spacing: 1.5mm;
            white-space: nowrap;
            pointer-events: none;
            z-index: -1;
            text-transform: uppercase;
        }

        .dependants-header {
            /* background: rgba(1, 102, 52, 0.05); */
            color: #016634;
            text-align: left;
            padding: 1.5mm 3mm;
            height: 2mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
            border-bottom: 2px solid #016634;
            width: 100%;
        }

        .dependants-title {
            font-size: 2mm;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2mm;
            text-align: center;
            width: 100%;
        }

        .dependant-count {
            font-size: 1.4mm;
            font-weight: 400;
            opacity: 0.8;
        }

        .dependants-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            padding: 1mm;
            gap: 1mm;
            height: 70mm;
            position: relative;
            z-index: 1;
        }

        /* Spouse Section - Left Column */
        .spouse-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.8mm;
            border: none;
            border-radius: 2mm;
            box-shadow: 0 1px 3px rgba(1, 102, 52, 0.15);
        }

        .spouse-photo {
            width: 26mm;
            height: 29mm;
            border-radius: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #016634;
            font-size: 5mm;
            margin-bottom: 0.8mm;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }

        .spouse-info {
            width: 100%;
            text-align: center;
        }

        .spouse-label {
            font-weight: 700;
            color: #016634;
            font-size: 1.7mm;
            text-transform: uppercase;
            margin-bottom: 0.4mm;
            display: block;
        }

        .spouse-name {
            font-weight: 800;
            color: #000000;
            font-size: 2.5mm;
            line-height: 1.2;
            margin-bottom: 0.4mm;
        }

        .spouse-details {
            color: #000000;
            font-size: 2.3mm;
            line-height: 1.3;
            text-align: center;
        }

        .spouse-details div {
            margin-bottom: 0.2mm;
        }

        /* Children Section - Right Column 2x2 Grid */
        .children-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 1mm;
        }

        .child-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.8mm;
            border: none;
            border-radius: 2mm;
            box-shadow: 0 1px 3px rgba(1, 102, 52, 0.15);
        }

        .child-photo {
            width: 19mm;
            height: 21mm;
            border-radius: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #016634;
            font-size: 3.5mm;
            margin-bottom: 0.4mm;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .child-info {
            width: 100%;
            text-align: center;
        }

        .child-label {
            font-weight: 900;
            color: #016634;
            font-size: 1.7mm;
            text-transform: uppercase;
            margin-bottom: 0.2mm;
            display: block;
            line-height: 1.1;
        }

        .child-name {
            font-weight: 700;
            color: #000000;
            font-size: 1.3mm;
            line-height: 1.2;
            margin-bottom: 0.2mm;
        }

        .child-details {
            color: #000000;
            font-size: 1.7mm;
            line-height: 1.2;
        }

        .child-details div {
            margin-bottom: 0.15mm;
        }

        .footer-summary {
            /* background: rgba(1, 102, 52, 0.05); */
            padding: 4mm;
            height: 6mm;
            align-items: center;
        }

        .footer-text {
            font-size: 1.5mm;
            font-weight: 700;
            color: #000000;
            line-height: 1.5;
            text-align: center;
        }

        .footer-stats {
            font-size: 1.1mm;
            color: #016634;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05mm;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Principal Card -->
        <div class="id-card principal-card">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="watermark-logo" alt="BOSCHMA Logo">
            @endif
            <div class="card-header">
                <div class="logo-section">
                    <div class="agency-info">
                        <center>
                            <div class="logo-circle">
                                @if ($logoBase64)
                                    <img src="{{ $logoBase64 }}" style="width: 60px;"
                                        class="header-brand-img desktop-logo" alt="logo">
                                @else
                                    <div style="width: 70px; height: 70px; background: #ccc; text-align: center;">No
                                        Logo
                                    </div>
                                @endif
                            </div>
                        </center>

                        <div class="agency-name">BORNO STATE CONTRIBUTORY</div>
                        <div class="agency-subtitle">HEALTHCARE MANAGEMENT AGENCY</div>
                        <div class="agency-subtitle">(BOSCHMA)</div>
                        <div class="scheme-type">FORMAL SECTOR SCHEME</div>
                        <div style="font-size: 2.0mm; margin-top: 0.3mm; font-style: italic; color: #016634;">"Wellness
                            for sustainable development"</div>
                        <div style="font-size: 1.6mm; margin-top: 0.2mm; color: #000000;">
                            No. 7 Lagos Street, Adjacent Lagos House, Maiduguri, Borno State<br>
                            Tel: 08122224040 | Email: info@boschma.bo.gov.ng <br> Website: boschma.bo.gov.ng
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="photo-section">
                    <div class="avatar-circle">
                        @if ($beneficiaryPhotoBase64)
                            <img src="{{ $beneficiaryPhotoBase64 }}"
                                style="width: 100%; height: 100%; object-fit: contain;" alt="Beneficiary Photo">
                        @else
                            <div style="font-size: 20px; color: #999;">👤</div>
                        @endif
                    </div>
                    <div class="role-badge">PRINCIPAL</div>
                </div>

                <div class="info-section">
                    <div class="info-row">
                        <span class="label">Full Name: </span><span class="value">{{ $beneficiary->fullname }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">BOSCHMA No: </span><span
                            class="value">{{ $beneficiary->boschma_no }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">DOB: </span><span
                            class="value">{{ \Carbon\Carbon::parse($beneficiary->date_of_birth)->format('d/m/Y') }}
                            ({{ \Carbon\Carbon::parse($beneficiary->date_of_birth)->age }} years old)</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Gender: </span><span class="value">{{ $beneficiary->gender }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Work Place: </span><span
                            class="value">{{ $beneficiary->place_of_work }}</span>
                    </div>
                    <div class="info-row">
                        <span class="value">{{ $beneficiary->facility->name ?? 'N/A' }}
                            ({{ $beneficiary->facility->ward }})</span>
                    </div>
                </div>
            </div>


            <div class="qr-section">
                <div class="qr-code-header">
                    @if ($qrCodeBase64)
                        <img src="{{ $qrCodeBase64 }}" style="width: 70px; height: 70px;" alt="QR Code">
                    @else
                        <div
                            style="width: 70px; height: 70px; border: 1px solid #000; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center;">
                            QR
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dependants Card -->
        <div class="id-card dependants-card">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="watermark-logo" alt="BOSCHMA Logo">
            @endif
            <div class="dependants-header">
                <div class="dependants-title">{{ $beneficiary->boschma_no }} DEPENDANTS

                    <div class="dependant-count">
                        {{ ($beneficiary->spouse ? 1 : 0) + $beneficiary->children->count() }} Registered
                    </div>
                </div>
            </div>

            <div class="dependants-content">
                <!-- Spouse Section -->
                <div class="spouse-section">
                    <div class="spouse-photo">
                        @if ($spousePhotoBase64)
                            <img src="{{ $spousePhotoBase64 }}" style="width: 100%; height: 100%; object-fit: contain;"
                                alt="Spouse Photo">
                        @else
                            👤
                        @endif
                    </div>

                    <div class="role-badge-2">SPOUSE</div>
                    <div class="spouse-info">
                        @if ($beneficiary->spouse)
                            <div class="spouse-name">{{ $beneficiary->spouse->name }} <span
                                    style="font-weight: 400; font-size: 1.6mm;"></span></div>
                            <div class="spouse-details">
                                <div>{{ $beneficiary->spouse->boschma_no }}</div>
                                <div>{{ $beneficiary->spouse->gender ?? 'N/A' }}</div>
                                {{-- dob --}}
                                <div>{{ date('d/m/Y', strtotime($beneficiary->spouse->dob)) ?? 'N/A' }}
                                    ({{ \Carbon\Carbon::parse($beneficiary->spouse->dob)->age }} years old)
                                </div>
                                <div>{{ $beneficiary->spouse->facility->name ?? 'N/A' }}
                                    ({{ $beneficiary->spouse->facility->ward }})
                                </div>
                            </div>
                        @else
                            <div class="spouse-name" style="color: #95a5a6;">Not registered</div>
                            <div class="spouse-details" style="color: #bdc3c7;">
                                No spouse enrolled
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Children Section -->
                <div class="children-section">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="child-item">
                            <div class="child-photo">
                                @if ($beneficiary->children && $beneficiary->children->count() > $i)
                                    @php $child = $beneficiary->children[$i]; @endphp
                                    @if (isset($childrenPhotosBase64[$child->id]))
                                        <img src="{{ $childrenPhotosBase64[$child->id] }}"
                                            style="width: 100%; height: 100%; object-fit: contain;" alt="Child Photo">
                                    @else
                                        👤
                                    @endif
                                @else
                                    👤
                                @endif
                            </div>
                            <div class="child-info">
                                @if ($beneficiary->children && $beneficiary->children->count() > $i)
                                    @php $child = $beneficiary->children[$i]; @endphp
                                    <div class="child-label">{{ $child->name }} <span
                                            style="font-weight: 500; font-size: 1.2mm;"></span></div>
                                    <div class="child-details">
                                        <div>{{ $child->boschma_no }}</div>
                                        <div>{{ $child->dob ?? '' }} | {{ $child->gender }}</div>
                                        <div>{{ $child->facility->name ?? 'N/A' }}</div>
                                    </div>
                                @else
                                    <div class="child-details" style="color: #bdc3c7;">
                                        <div>Not Registered</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="footer-summary">
                <div class="footer-text">
                    THIS IS TO CERTIFY THAT THE BEARER, WHOSE NAME AND PHOTOGRAPH APPEAR OVERLEAF, IS A BORNO STATE
                    CIVIL SERVANT AND A BENEFICIARY OF THE FORMAL SECTOR HEALTH INSURANCE SCHEME UNDER BOSCHMA. IF
                    FOUND, PLEASE RETURN TO THE ADDRESS OVERLEAF OR TO THE NEAREST POLICE STATION
                </div>
                <div class="footer-text">
                    <img src="{{ $signBase64 }}" style="width: 50px;" class="header-brand-img desktop-logo"
                        alt="sign">
                </div>
                <div class="footer-text">
                    AUTHORISED SIGNATORY
                </div>
            </div>
        </div>
    </div>
</body>

</html>
