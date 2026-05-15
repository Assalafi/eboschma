<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $beneficiary->program->name ?? 'Healthcare' }} ID Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; background: white; }
        table { border-collapse: collapse; }
        img { display: inline-block; }

        /* ===== CARD LAYOUT ===== */
        .card-pair {
            width: 173.2mm;
            margin: 0 auto 1.5mm auto;
            page-break-inside: avoid;
            font-size: 0;
        }
        .id-card {
            width: 85.6mm;
            height: 54mm;
            border: 0.3mm solid #888;
            overflow: hidden;
            position: relative;
            display: inline-block;
            vertical-align: top;
            background: #fff;
            font-size: 10px;
        }
        .id-card + .id-card { margin-left: 2mm; }
        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 35mm; height: 35mm;
            opacity: 0.06; z-index: 0;
        }

        /* ===== HEADER ===== */
        .header {
            position: relative;
            z-index: 1;
            border-bottom: 0.4mm solid #016634;
        }
        .header-logo {
            position: absolute;
            top: 1.3mm;
            width: 13mm;
            z-index: 2;
        }
        .header-logo-left { left: 0.1mm; }
        .header-logo-right { right: 0.1mm; }
        .agency-name {
            text-align: center;
            padding: 1mm 2mm 0;
            font-size: 2.5mm;
            font-weight: 900;
            text-transform: uppercase;
            color: #000;
            line-height: 1.15;
        }
        .tagline-wrap {
            text-align: center;
            padding: 0.3mm 14mm 0;
        }
        .tagline {
            font-size: 1.8mm;
            font-style: italic;
            color: #016634;
            font-weight: 600;
            line-height: 1.2;
        }
        .scheme-name {
            font-size: 2.2mm;
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
            border-bottom: 0.4mm solid #c00;
            padding-bottom: 0.3mm;
        }
        .address {
            text-align: center;
            padding: 0.3mm 2mm 0.6mm;
            font-size: 1.8mm;
            color: #333;
            line-height: 1.3;
            font-weight: 900;
        }

        /* ===== BODY ===== */
        .body-table {
            width: 100%;
            border: none;
            position: relative;
            z-index: 1;
        }
        .qr-cell {
            width: 16mm;
            vertical-align: bottom;
            padding: 1mm 0 0 1.5mm;
        }
        .qr-img {
            width: 16mm;
            height: 16mm;
        }
        .info-cell {
            vertical-align: top;
            padding: 0.8mm 1mm 0 1mm;
        }
        .info-group {
            margin-bottom: 0.3mm;
        }
        .field-label {
            font-size: 2.8mm;
            font-weight: 700;
            color: #016634;
            display: block;
            line-height: 1.1;
        }
        .field-value {
            font-size: 2.8mm;
            font-weight: 900;
            color: #000;
            display: block;
            line-height: 1.25;
        }
        .photo-cell {
            width: 20mm;
            vertical-align: top;
            padding: 0.8mm 1.5mm 0 0;
            text-align: center;
        }
        .photo-frame {
            width: 18mm;
            height: 22mm;
            border: 0.3mm solid #016634;
            overflow: hidden;
            background: #f0f0f0;
        }
        .photo-img {
            width: 18mm;
            height: 22mm;
            object-fit: cover;
        }
        .facility-cell {
            vertical-align: bottom;
            padding: 0.3mm 1mm 1mm 1.5mm;
        }
        .boschma-no-cell {
            vertical-align: bottom;
            padding: 0 1.5mm 1mm 0;
            text-align: center;
        }
        .boschma-no {
            font-size: 3.2mm;
            font-weight: 900;
            color: #000;
        }

        /* ===== BACK CARD ===== */
        .back-table {
            width: 100%;
            height: 100%;
            border: none;
            position: relative;
            z-index: 1;
        }
        .back-cell {
            text-align: center;
            vertical-align: middle;
            padding: 3mm 4mm;
            word-wrap: break-word;
            white-space: normal;
        }
        .back-certification {
            font-size: 2.4mm;
            font-weight: 700;
            color: #000;
            text-align: center;
            line-height: 1.5;
            text-transform: uppercase;
            margin-bottom: 2mm;
        }
        .back-notice {
            font-size: 2.2mm;
            font-weight: 700;
            color: #000;
            text-align: center;
            line-height: 1.5;
            text-transform: uppercase;
            margin-bottom: 2mm;
        }
        .back-signature img {
            width: 14mm;
            height: auto;
        }
        .back-signature-label {
            font-size: 2mm;
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
            margin-top: 0.5mm;
        }
    </style>
</head>

<body>
    <div class="card-pair">
        {{-- ===== FRONT CARD ===== --}}
        <div class="id-card">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="watermark" alt="">
            @endif

            {{-- ---- HEADER ---- --}}
            <div class="header">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" class="header-logo header-logo-left" alt="">
                @endif
                @if (isset($programLogoBase64) && $programLogoBase64)
                    <img src="{{ $programLogoBase64 }}" class="header-logo header-logo-right" alt="">
                @elseif ($logoBase64)
                    <img src="{{ $logoBase64 }}" class="header-logo header-logo-right" alt="">
                @endif

                <div class="agency-name">
                    BORNO STATE CONTRIBUTORY HEALTH<br>CARE MANAGEMENT AGENCY (BOSCHMA)
                </div>

                <div class="tagline-wrap">
                    <div class="tagline">Wellness For Sustainable Development</div>
                    <div class="scheme-name">
                        {{ strtoupper($beneficiary->program->description ?? $beneficiary->program->name ?? 'HEALTH CARE PROGRAM') }}
                    </div>
                </div>

                <div class="address">
                    No. 7 Lagos Street, Adjacent Lagos House Maiduguri, Borno State<br>
                    08122224040 | info@boschma.bo.gov.ng | boschma.bornostate.gov.ng
                </div>
            </div>

            {{-- ---- BODY: QR | Info | Photo ---- --}}
            <table class="body-table">
                <tr>
                    <td class="qr-cell">
                        @if ($qrCodeBase64)
                            <img src="{{ $qrCodeBase64 }}" class="qr-img" alt="QR">
                        @else
                            <div class="qr-img" style="border:0.3mm solid #000;text-align:center;font-size:1.5mm;line-height:14mm;">QR</div>
                        @endif
                    </td>
                    <td class="info-cell">
                        <div class="info-group">
                            <span class="field-label">Name</span>
                            <span class="field-value">{{ strtoupper($beneficiary->fullname) }}</span>
                        </div>
                        <div class="info-group">
                            <span class="field-label">Gender</span>
                            <span class="field-value">{{ strtoupper($beneficiary->gender) }}</span>
                        </div>
                        <div class="info-group">
                            <span class="field-label">Category</span>
                            <span class="field-value">{{ strtoupper($beneficiary->category ?? 'N/A') }}</span>
                        </div>
                    </td>
                    <td class="photo-cell">
                        <div class="photo-frame">
                            @if ($beneficiaryPhotoBase64)
                                <img src="{{ $beneficiaryPhotoBase64 }}" class="photo-img" alt="">
                            @else
                                <div style="width:100%;height:100%;text-align:center;line-height:22mm;color:#999;font-size:10px;">&#128100;</div>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="facility-cell">
                        <span class="field-label">Facility</span>
                        <span class="field-value">{{ strtoupper($beneficiary->facility->name ?? 'N/A') }}</span>
                    </td>
                    <td class="boschma-no-cell">
                        <div class="boschma-no">{{ $beneficiary->boschma_no }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ===== BACK CARD ===== --}}
        <div class="id-card">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="watermark" alt="">
            @endif

            <table class="back-table">
                <tr>
                    <td class="back-cell">
                        <div class="back-certification">
                            THIS IS TO CERTIFY THAT THE PERSON WHOSE NAME AND
                            PHOTOGRAPH APPEARS OVERLEAF IS A BENEFICIARY OF
                            {{ strtoupper($beneficiary->program->description ?? $beneficiary->program->name ?? 'HEALTH CARE PROGRAM') }} UNDER THE BORNO STATE CONTRIBUTORY HEALTH CARE
                            MANAGEMENT AGENCY
                        </div>
                        <div class="back-notice">
                            IF FOUND, PLEASE RETURN TO THE
                            ADDRESS OVERLEAF OR THE NEAREST
                            POLICE STATION.
                        </div>
                        <div class="back-signature">
                            @if ($signBase64)
                                <img src="{{ $signBase64 }}" alt="">
                            @endif
                            <div class="back-signature-label">AUTHORISED SIGNATORY</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
