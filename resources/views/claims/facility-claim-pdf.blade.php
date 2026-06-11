<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>BHCPF Claim {{ $claim->claim_number ?? $claim->id }}</title>
<style>
body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; margin: 20px 30px; }
table { width: 100%; border-collapse: collapse; }
td, th { border: 1px solid #666; padding: 5px 8px; }
.hdr { text-align: center; margin-bottom: 10px; }
.hdr img { height: 70px; }
.hdr h3 { margin: 4px 0 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
.hdr h4 { margin: 2px 0 6px; font-size: 12px; text-transform: uppercase; font-weight: 700; }
.info td { font-size: 11px; border: 1px solid #666; padding: 5px 8px; }
.info td strong { font-weight: 700; }
.sec-hdr td { background: #006634; color: #fff; text-align: center; font-weight: 700; font-size: 11px; padding: 6px; text-transform: uppercase; border: 1px solid #004d28; }
.items th { background: #e6f7f0; font-weight: 700; font-size: 10px; text-align: center; border: 1px solid #666; padding: 5px; }
.items td { text-align: center; font-size: 11px; }
.items td:nth-child(2) { text-align: left; }
.sub td { background: #e6f7f0; font-weight: 700; border: 1px solid #666; }
.grand td { background: #006634; color: #fff; font-weight: 700; font-size: 12px; border: 1px solid #004d28; }
</style>
</head>
<body>

{{-- Logo + Header --}}
<div class="hdr">
    @if(file_exists($logoPath))
        <img src="{{ $logoPath }}" alt="Logo">
    @endif
    <h3>Borno State Contributory Health Care Management Agency</h3>
    <h4>BHCPF Claims</h4>
</div>

{{-- Patient / Claim Info --}}
<table class="info" style="margin-bottom:0">
    <tr>
        <td style="width:55%"><strong>Healthcare Provider:</strong> &nbsp; {{ $claim->facility_name }}</td>
        <td><strong>HCP BOSCHMA Reg No:</strong> &nbsp; {{ $claim->boschma_no ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Enrollee's Name:</strong> &nbsp; {{ $claim->patient_name }}</td>
        <td><strong>Sex:</strong> &nbsp; {{ $claim->gender ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td><strong>Date of Birth/Age:</strong> &nbsp; {{ $claim->date_of_birth ? \Carbon\Carbon::parse($claim->date_of_birth)->format('Y-m-d') : 'N/A' }}</td>
        <td><strong>Date:</strong> &nbsp; {{ \Carbon\Carbon::parse($claim->service_date)->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td><strong>Enrollee's ID No:</strong> &nbsp; {{ $claim->enrollee_number }}</td>
        <td><strong>Diagnosis:</strong> &nbsp; {{ $diagnosisText }}</td>
    </tr>
</table>

{{-- MEDICATIONS --}}
@php $medTotal = 0; @endphp
<table style="margin-top:-1px">
    <tr class="sec-hdr"><td colspan="6">Services Provided<br>Medication(s)</td></tr>
    <tr class="items">
        <th style="width:40px">S/N</th>
        <th>Medication(s)</th>
        <th style="width:80px">Rate</th>
        <th style="width:80px">Frequency</th>
        <th style="width:100px">Amount Claimed</th>
        <th style="width:100px">Amount Due</th>
    </tr>
    @forelse ($medications as $i => $med)
        @php
            $rate = ($med['quantity'] > 0) ? $med['cost'] / $med['quantity'] : $med['cost'];
            $medTotal += $med['cost'];
        @endphp
        <tr class="items">
            <td>{{ $i + 1 }}</td>
            <td style="text-align:left">{{ $med['name'] }}</td>
            <td>{{ number_format($rate, 0) }}</td>
            <td>{{ $med['quantity'] }}</td>
            <td>{{ number_format($med['cost'], 0) }}</td>
            <td>{{ number_format($med['cost'], 0) }}</td>
        </tr>
    @empty
        <tr class="items"><td colspan="6" style="text-align:center;color:#999">No medications</td></tr>
    @endforelse
    <tr class="sub">
        <td colspan="4" style="text-align:center">SUB TOTAL</td>
        <td style="text-align:center">N {{ number_format($medTotal, 0) }}</td>
        <td style="text-align:center">N {{ number_format($medTotal, 0) }}</td>
    </tr>
</table>

{{-- RENDERED SERVICES --}}
@php $svcTotal = 0; @endphp
<table style="margin-top:-1px">
    <tr class="sec-hdr"><td colspan="6">Services Provided<br>Rendered Service(s)</td></tr>
    <tr class="items">
        <th style="width:40px">S/N</th>
        <th>Service(s)</th>
        <th style="width:80px">Rate</th>
        <th style="width:80px">Frequency</th>
        <th style="width:100px">Amount Claimed</th>
        <th style="width:100px">Amount Due</th>
    </tr>
    @forelse ($services as $i => $svc)
        @php 
            $svcTotal += $svc['cost'];
            $rate = $svc['unit_price'] ?? ($svc['cost'] / max(1, $svc['frequency'] ?? 1));
        @endphp
        <tr class="items">
            <td>{{ $i + 1 }}</td>
            <td style="text-align:left">{{ $svc['name'] }}</td>
            <td>{{ number_format($rate, 0) }}</td>
            <td>{{ $svc['frequency'] ?? 1 }}</td>
            <td>{{ number_format($svc['cost'], 0) }}</td>
            <td>{{ number_format($svc['cost'], 0) }}</td>
        </tr>
    @empty
        <tr class="items"><td colspan="6" style="text-align:center;color:#999">No rendered services</td></tr>
    @endforelse
    <tr class="sub">
        <td colspan="4" style="text-align:center">SUB TOTAL</td>
        <td style="text-align:center">N {{ number_format($svcTotal, 0) }}</td>
        <td style="text-align:center">N {{ number_format($svcTotal, 0) }}</td>
    </tr>
</table>

{{-- GRAND TOTAL --}}
@php $grandTotal = $medTotal + $svcTotal; @endphp
<table style="margin-top:8px">
    <tr class="grand">
        <td colspan="4" style="text-align:center">GRAND TOTAL</td>
        <td style="text-align:center;width:100px">N {{ number_format($grandTotal, 0) }}</td>
        <td style="text-align:center;width:100px">N {{ number_format($grandTotal, 0) }}</td>
    </tr>
</table>

</body>
</html>
