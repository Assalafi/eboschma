<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary List</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
        }
        .logo-cell {
            width: 15%;
            text-align: center;
        }
        .logo-cell img {
            max-height: 80px;
            max-width: 100px;
        }
        .text-cell {
            width: 70%;
            text-align: center;
            padding: 0 10px;
        }
        .text-cell h1 {
            color: #01542B;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .text-cell p.subtitle {
            color: #ff0000;
            font-style: italic;
            font-size: 12px;
            margin: 0 0 5px 0;
        }
        .text-cell h2 {
            color: #ff0000;
            font-size: 16px;
            font-weight: bold;
            margin: 0 auto;
            text-transform: uppercase;
            border-bottom: 2px solid #01542B;
            display: inline-block;
            padding-bottom: 2px;
        }
        .report-title-section {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }
        .facility-info {
            text-align: left;
            margin-top: 10px;
            font-size: 12px;
        }
        .facility-info strong {
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }
        .data-table th.center, .data-table td.center {
            text-align: center;
        }
        .group-row {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @php
                        $boschmaLogo = public_path('assets/img/brand/logo.png');
                        $boschmaLogoBase64 = '';
                        if (file_exists($boschmaLogo)) {
                            $type = pathinfo($boschmaLogo, PATHINFO_EXTENSION);
                            $data = file_get_contents($boschmaLogo);
                            $boschmaLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    @endphp
                    @if($boschmaLogoBase64)
                        <img src="{{ $boschmaLogoBase64 }}" alt="BOSCHMA Logo">
                    @endif
                </td>
                <td class="text-cell">
                    <h1>BORNO STATE CONTRIBUTORY HEALTHCARE<br>MANAGEMENT AGENCY (BOSCHMA)</h1>
                    <p class="subtitle">Wellness for sustainable development</p>
                    <h2>BASIC HEALTH CARE PROVISION FUND</h2>
                </td>
                <td class="logo-cell">
                    @php
                        $bhcpfLogo = public_path('assets/img/brand/BHCPF_logo.png');
                        $bhcpfLogoBase64 = '';
                        if (file_exists($bhcpfLogo)) {
                            $type = pathinfo($bhcpfLogo, PATHINFO_EXTENSION);
                            $data = file_get_contents($bhcpfLogo);
                            $bhcpfLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    @endphp
                    @if($bhcpfLogoBase64)
                        <img src="{{ $bhcpfLogoBase64 }}" alt="BHCPF Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title-section">
        <h3 class="report-title">BENEFICIARY LIST</h3>
        <div class="facility-info">
            HEALTH FACILITY: <strong>{{ $facilityName }}</strong>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" width="5%">S/N</th>
                <th width="20%">FULLNAME</th>
                <th class="center" width="8%">GENDER</th>
                <th class="center" width="10%">DOB</th>
                <th class="center" width="12%">MARITAL STATUS</th>
                <th width="12%">PHONE</th>
                <th width="15%">NIN</th>
                <th class="center" width="18%">IDNUMBER</th>
            </tr>
        </thead>
        <tbody>
            @php $serialNumber = 1; @endphp
            @foreach($groupedBeneficiaries as $ageGroup => $beneficiariesGroup)
                <tr>
                    <td colspan="8" class="group-row">AGED {{ $ageGroup }}</td>
                </tr>
                @foreach($beneficiariesGroup as $beneficiary)
                    <tr>
                        <td class="center">{{ $serialNumber++ }}</td>
                        <td>{{ strtoupper($beneficiary->fullname) }}</td>
                        <td class="center">{{ strtoupper($beneficiary->gender ?? '') }}</td>
                        <td class="center">{{ $beneficiary->date_of_birth ? \Carbon\Carbon::parse($beneficiary->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                        <td class="center">{{ strtoupper($beneficiary->marital_status ?? 'N/A') }}</td>
                        <td>{{ $beneficiary->phone_no ?? 'N/A' }}</td>
                        <td>{{ $beneficiary->nin ?? 'N/A' }}</td>
                        <td class="center">{{ $beneficiary->boschma_no ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            @endforeach
            
            @if(empty($groupedBeneficiaries))
                <tr>
                    <td colspan="8" class="center">No beneficiaries found for the selected criteria.</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
