<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Beneficiary Record - {{ $beneficiary->boschma_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }

        .header h2 {
            font-size: 16px;
            margin: 5px 0;
            color: #555;
        }

        .header p {
            font-size: 12px;
            margin: 5px 0;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #f3f3f3;
            padding: 5px;
            margin-bottom: 10px;
            font-weight: bold;
            border-left: 3px solid #4F84AB;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 25%;
            padding: 5px;
            font-weight: bold;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            width: 75%;
            padding: 5px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .photo {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
        }

        .children-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .children-table th,
        .children-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 11px;
        }

        .children-table th {
            background-color: #f3f3f3;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BOSCHMA MEMBER RECORD</h1>
        <h2>Borno State Contributory Healthcare Management Agency</h2>
        <p>BOSCHMA No: {{ $beneficiary->boschma_no }}</p>
        <p style="margin-top: 5px;">
            <span class="badge"
                style="background-color: {{ $beneficiary->status == 'active' ? '#28a745' : '#dc3545' }}; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px;">
                {{ ucfirst($beneficiary->status) }}
            </span>
            <span style="margin-left: 10px; font-size: 11px; color: #666;">Generated on: {{ date('d-m-Y H:i') }}</span>
        </p>
    </div>

    <div class="section">
        <div class="section-title">Personal Information</div>
        <table width="100%">
            <tr>
                <td width="70%" valign="top">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">BOSCHMA No:</div>
                            <div class="info-value">{{ $beneficiary->boschma_no }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Full Name:</div>
                            <div class="info-value">{{ $beneficiary->surname }} {{ $beneficiary->first_name }}
                                {{ $beneficiary->other_name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Gender:</div>
                            <div class="info-value">{{ $beneficiary->gender }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date of Birth:</div>
                            <div class="info-value">
                                {{ $beneficiary->date_of_birth ? date('d-m-Y', strtotime($beneficiary->date_of_birth)) : 'N/A' }}
                                @if ($beneficiary->date_of_birth)
                                    ({{ \Carbon\Carbon::parse($beneficiary->date_of_birth)->age }} years)
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Phone Number:</div>
                            <div class="info-value">{{ $beneficiary->phone_no ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value">{{ $beneficiary->email ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ID Type:</div>
                            <div class="info-value">{{ $beneficiary->id_type ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ID Number:</div>
                            <div class="info-value">{{ $beneficiary->id_no ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Category:</div>
                            <div class="info-value">{{ $beneficiary->category ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Occupation:</div>
                            <div class="info-value">{{ $beneficiary->occupation ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Employer:</div>
                            <div class="info-value">{{ $beneficiary->place_of_work ?? 'N/A' }}</div>
                        </div>
                    </div>
                </td>
                <td width="30%" valign="top" align="center">
                    @if ($beneficiary->photo)
                        <img class="photo" src="{{ public_path('storage/' . $beneficiary->photo) }}"
                            alt="Beneficiary Photo">
                    @else
                        <div
                            style="width:150px; height:150px; border:1px solid #ddd; display:flex; justify-content:center; align-items:center;">
                            No Photo
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Address & Location</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Contact Address:</div>
                <div class="info-value">{{ $beneficiary->contact_address }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">LGA:</div>
                <div class="info-value">{{ $beneficiary->lga }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">State:</div>
                <div class="info-value">{{ $beneficiary->state }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nationality:</div>
                <div class="info-value">{{ $beneficiary->nationality }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Registration Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Registration Date:</div>
                <div class="info-value">{{ date('d-m-Y', strtotime($beneficiary->created_at)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NIN:</div>
                <div class="info-value">{{ $beneficiary->nin ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($beneficiary->status) }}</div>
            </div>
        </div>
    </div>

    @if ($beneficiary->additional_info)
        <div class="section">
            <div class="section-title">Additional Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-value">{{ $beneficiary->additional_info }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($beneficiary->spouse)
        <div class="section">
            <div class="section-title">Spouse Information</div>
            <table width="100%">
                <tr>
                    <td width="70%" valign="top">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-label">BOSCHMA No:</div>
                                <div class="info-value">{{ $beneficiary->spouse->boschma_no }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Full Name:</div>
                                <div class="info-value">{{ $beneficiary->spouse->surname }}
                                    {{ $beneficiary->spouse->first_name }} {{ $beneficiary->spouse->other_name }}
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Gender:</div>
                                <div class="info-value">{{ $beneficiary->spouse->gender }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Date of Birth:</div>
                                <div class="info-value">
                                    {{ $beneficiary->spouse->dob ? date('d-m-Y', strtotime($beneficiary->spouse->dob)) : 'N/A' }}
                                    @if ($beneficiary->spouse->dob)
                                        ({{ \Carbon\Carbon::parse($beneficiary->spouse->dob)->age }} years)
                                    @endif
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone Number:</div>
                                <div class="info-value">{{ $beneficiary->spouse->phone ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">{{ $beneficiary->spouse->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td width="30%" valign="top" align="center">
                        @if ($beneficiary->spouse->photo)
                            <img class="photo" src="{{ public_path('storage/' . $beneficiary->spouse->photo) }}"
                                alt="Spouse Photo">
                        @else
                            <div
                                style="width:150px; height:150px; border:1px solid #ddd; display:flex; justify-content:center; align-items:center;">
                                No Photo
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @if ($beneficiary->children && count($beneficiary->children) > 0)
        <div class="section">
            <div class="section-title">Children Information</div>
            <table class="children-table">
                <thead>
                    <tr>
                        <th>BOSCHMA No</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Birth Cert. No</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($beneficiary->children as $child)
                        <tr>
                            <td>{{ $child->boschma_no }}</td>
                            <td>{{ $child->surname }} {{ $child->first_name }} {{ $child->other_name }}</td>
                            <td>{{ $child->gender }}</td>
                            <td>
                                {{ $child->dob ? date('d-m-Y', strtotime($child->dob)) : 'N/A' }}
                                @if ($child->dob)
                                    ({{ \Carbon\Carbon::parse($child->dob)->age }} years)
                                @endif
                            </td>
                            <td>{{ $child->birth_certificate_no ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <h4>Children's Photos & Documents</h4>
                <table width="100%">
                    <tr>
                        @foreach ($beneficiary->children as $child)
                            <td width="{{ 100 / min(count($beneficiary->children), 3) }}%" align="center"
                                valign="top">
                                <div style="margin: 10px;">
                                    <strong>{{ $child->first_name }} {{ $child->surname }}</strong><br>
                                    @if ($child->photo)
                                        <img class="photo" src="{{ public_path('storage/' . $child->photo) }}"
                                            alt="Child Photo" style="max-width:100px; max-height:100px;">
                                    @else
                                        <div
                                            style="width:100px; height:100px; border:1px solid #ddd; display:flex; justify-content:center; align-items:center;">
                                            No Photo
                                        </div>
                                    @endif

                                    <div style="margin-top: 5px; font-size: 10px;">
                                        @if ($child->birth_certificate_file)
                                            <span style="color: green;">✓ Birth Certificate Uploaded</span>
                                        @else
                                            <span style="color: red;">✗ No Birth Certificate</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @if ($loop->iteration % 3 == 0 && !$loop->last)
                    </tr>
                    <tr>
    @endif
    @endforeach
    </tr>
    </table>
    </div>
    </div>
    @endif

    <div class="footer">
        <p>This document was generated from the BOSCHMA Enrollment System</p>
        <p>Official Record | Confidential | {{ $beneficiary->boschma_no }}</p>
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                $size = 9;
                $font = $fontMetrics->getFont("Arial");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) / 2;
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>
    </div>
</body>

</html>
