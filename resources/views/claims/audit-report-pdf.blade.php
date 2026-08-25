<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Claims Verification Audit Report - Boschma</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1a202c;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #047857;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .agency-title {
            font-size: 18px;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
        }
        .sub-title {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }
        .meta-box {
            background-color: #f3f4f6;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-table {
            width: 100%;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .kpi-container {
            width: 100%;
            margin-bottom: 15px;
        }
        .kpi-card {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }
        .kpi-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #047857;
            font-weight: bold;
        }
        .kpi-value {
            font-size: 15px;
            font-weight: bold;
            color: #065f46;
            margin-top: 2px;
        }
        .section-heading {
            font-size: 12px;
            font-weight: bold;
            color: #065f46;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        table.data-table th {
            background-color: #047857;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #047857;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .badge-verified { background-color: #d1fae5; color: #065f46; }
        .badge-approved { background-color: #dbeafe; color: #1e40af; }
        .badge-paid { background-color: #fef3c7; color: #92400e; }
        .badge-rejected { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="agency-title">Borno State Health Contributory Management Agency</div>
                <div class="sub-title">Claims Verification & Reviewer Performance Audit Report</div>
            </td>
            <td style="text-align: right; vertical-align: bottom; font-size: 10px; color: #4b5563;">
                Confidential Document<br>
                Generated: {{ now()->format('d M, Y H:i A') }}
            </td>
        </tr>
    </table>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td><strong>Audit Date Range:</strong> {{ $startDate->format('d M, Y') }} to {{ $endDate->format('d M, Y') }}</td>
                <td><strong>Program:</strong> 
                    @if(isset($programId) && $programId && isset($programs) && ($selProg = $programs->firstWhere('id', $programId)))
                        {{ $selProg->name }}
                    @else
                        All Programs
                    @endif
                </td>
                <td><strong>Selected Reviewer:</strong> 
                    @if($reviewerId && isset($nameMap[$reviewerId]))
                        {{ $nameMap[$reviewerId] }}
                    @else
                        All Reviewers / Staff
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- KPI Summary -->
    <table class="kpi-container" style="width: 100%;">
        <tr>
            <td style="width: 33%; padding-right: 5px;">
                <div class="kpi-card">
                    <div class="kpi-title">Active Reviewers</div>
                    <div class="kpi-value">{{ number_format($activeReviewersCount) }}</div>
                </div>
            </td>
            <td style="width: 33%; padding-left: 5px; padding-right: 5px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Claims Audited</div>
                    <div class="kpi-value">{{ number_format($totalClaimsProcessed) }}</div>
                </div>
            </td>
            <td style="width: 34%; padding-left: 5px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Processed Value</div>
                    <div class="kpi-value">₦{{ number_format($totalValueProcessed, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Reviewer Performance Summary -->
    <div class="section-heading">1. Reviewer Performance Breakdown <span style="font-size: 9px; font-weight: normal; text-transform: none; color: #4b5563;">— Individual activity breakdown and processing metrics</span></div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Reviewer Name</th>
                <th style="text-align: center;">Verified</th>
                <th style="text-align: center;">RO Appr.</th>
                <th style="text-align: center;">ES Appr.</th>
                <th style="text-align: center;">Paid</th>
                <th style="text-align: center;">Rejected</th>
                <th style="text-align: center;">Total Actions</th>
                <th style="text-align: right;">Total Value (₦)</th>
                <th style="text-align: center;">Last Active</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviewerStats as $stat)
                <tr>
                    <td><strong>{{ $stat['name'] }}</strong><br><span style="font-size: 8px; color: #6b7280;">{{ $stat['email'] }}</span></td>
                    <td style="text-align: center;">{{ $stat['verified_count'] }}</td>
                    <td style="text-align: center;">{{ $stat['approved_count'] }}</td>
                    <td style="text-align: center;">{{ $stat['es_approved_count'] }}</td>
                    <td style="text-align: center;">{{ $stat['paid_count'] }}</td>
                    <td style="text-align: center; color: #dc2626;">{{ $stat['rejected_count'] }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $stat['total_actions'] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #047857;">₦{{ number_format($stat['total_value'], 2) }}</td>
                    <td style="text-align: center;">{{ $stat['last_activity'] ? \Carbon\Carbon::parse($stat['last_activity'])->format('d M Y, H:i') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 10px;">No reviewer activity records found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Detailed Itemized Activity Logs -->
    <div class="section-heading">2. Itemized Claim Audit Trail <span style="font-size: 9px; font-weight: normal; text-transform: none; color: #4b5563;">— Chronological log of claims verifications and reviewer actions</span></div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Claim #</th>
                <th>Patient / Enrollee</th>
                <th>Action Status</th>
                <th>Reviewer</th>
                <th style="text-align: right;">Amount (₦)</th>
                <th>Notes / Comments</th>
            </tr>
        </thead>
        <tbody>
            @forelse($claims as $claim)
                @php
                    $revName = 'N/A';
                    if ($claim->verifier_id && isset($nameMap[$claim->verifier_id])) {
                        $revName = $nameMap[$claim->verifier_id];
                    } elseif ($claim->approver_id && isset($nameMap[$claim->approver_id])) {
                        $revName = $nameMap[$claim->approver_id];
                    } elseif ($claim->es_id && isset($nameMap[$claim->es_id])) {
                        $revName = $nameMap[$claim->es_id];
                    } elseif ($claim->finance_id && isset($nameMap[$claim->finance_id])) {
                        $revName = $nameMap[$claim->finance_id];
                    }

                    $notes = $claim->rejection_reason ?: ($claim->verifier_notes ?: ($claim->approver_notes ?: ($claim->es_notes ?: $claim->finance_notes)));
                @endphp
                <tr>
                    <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($claim->updated_at)->format('d M, Y H:i:s') }}</td>
                    <td><strong>{{ $claim->claim_number ?: ('CLM-'.$claim->id) }}</strong></td>
                    <td>{{ $claim->patient_name ?: 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $claim->status }}">
                            {{ strtoupper($claim->status) }}
                        </span>
                    </td>
                    <td><strong>{{ $revName }}</strong></td>
                    <td style="text-align: right;">₦{{ number_format($claim->total_amount ?? 0, 2) }}</td>
                    <td style="font-size: 9px; color: #4b5563;">{{ $notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 10px;">No detailed claim audit logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Borno State Health Contributory Management Agency (BOSCHMA) — Official Claims Audit Report System
    </div>

</body>
</html>
