<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Details - {{ $referral->referral_id ?? 'REF-' . str_pad($referral->id, 6, '0', STR_PAD_LEFT) }}
    </title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #01542B;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #01542B;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #01542B;
        }

        .referral-id {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #01542B;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            text-align: right;
            color: #333;
        }

        .notes-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #01542B;
        }

        .timeline {
            margin-top: 20px;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-date {
            min-width: 120px;
            font-weight: bold;
            color: #555;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .timeline-description {
            color: #666;
            font-size: 11px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        .commission-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .commission-amount {
            font-size: 18px;
            font-weight: bold;
            color: #155724;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">BOSCHMA Referral System</div>
        <div class="company-info">Professional Referral Management</div>
        <div class="company-info">{{ config('app.address', 'Company Address') }}</div>
        <div class="company-info">{{ config('app.phone', '+1234567890') }} •
            {{ config('app.email', 'info@boschma.com') }}</div>
    </div>

    <div class="document-title">Referral Details Report</div>

    <div class="referral-id">
        Referral ID: {{ $referral->referral_id ?? 'REF-' . str_pad($referral->id, 6, '0', STR_PAD_LEFT) }}
        <span class="status-badge status-{{ $referral->status ?? 'pending' }}">
            {{ ucfirst($referral->status ?? 'pending') }}
        </span>
    </div>

    <div class="text-muted" style="margin-bottom: 20px;">
        Generated on: {{ now()->format('F j, Y \a\t g:i A') }}
        @if ($referral->created_at)
            • Created: {{ $referral->created_at->format('F j, Y \a\t g:i A') }}
        @endif
    </div>

    <!-- Referrer Information -->
    <div class="section">
        <div class="section-title">
            <i class="ti ti-user"></i> Referrer Information
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $referral->referrer_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $referral->referrer_email ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $referral->referrer_phone ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Referrer ID:</span>
                <span class="info-value">{{ $referral->referrer_id ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Referred Person Information -->
    <div class="section">
        <div class="section-title">
            <i class="ti ti-user-plus"></i> Referred Person Information
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $referral->referred_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $referral->referred_email ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $referral->referred_phone ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date of Birth:</span>
                <span
                    class="info-value">{{ $referral->referred_dob ? \Carbon\Carbon::parse($referral->referred_dob)->format('F j, Y') : 'N/A' }}</span>
            </div>
        </div>

        @if ($referral->referred_address)
            <div style="margin-top: 15px;">
                <div class="info-label" style="margin-bottom: 5px;">Address:</div>
                <div class="info-value" style="text-align: left;">{{ $referral->referred_address }}</div>
            </div>
        @endif
    </div>

    <!-- Referral Details -->
    <div class="section">
        <div class="section-title">
            <i class="ti ti-file-text"></i> Referral Details
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Service Type:</span>
                <span class="info-value">{{ ucfirst($referral->service_type ?? 'General') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Priority Level:</span>
                <span class="info-value">{{ ucfirst($referral->priority_level ?? 'Normal') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Commission Type:</span>
                <span class="info-value">{{ ucfirst($referral->commission_type ?? 'Fixed') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Follow-up Date:</span>
                <span
                    class="info-value">{{ $referral->follow_up_date ? \Carbon\Carbon::parse($referral->follow_up_date)->format('F j, Y') : 'Not set' }}</span>
            </div>
        </div>

        <!-- Commission Information -->
        <div class="commission-info">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: bold; margin-bottom: 5px;">Commission Amount</div>
                    <div class="commission-amount">${{ number_format($referral->commission_amount ?? 0, 2) }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold; margin-bottom: 5px;">Status</div>
                    <div>
                        @if (($referral->commission_status ?? 'pending') == 'paid')
                            <span style="color: #155724; font-weight: bold;">✓ Paid</span>
                        @else
                            <span style="color: #856404;">○ Pending</span>
                        @endif
                    </div>
                </div>
            </div>
            @if ($referral->commission_paid_at)
                <div style="margin-top: 10px; font-size: 11px; color: #666;">
                    Paid on: {{ \Carbon\Carbon::parse($referral->commission_paid_at)->format('F j, Y') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Assignment Information -->
    <div class="section">
        <div class="section-title">
            <i class="ti ti-settings"></i> Assignment & Timeline
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Assigned To:</span>
                <span class="info-value">{{ $referral->assigned_user_name ?? 'Unassigned' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Processing Time:</span>
                <span class="info-value">
                    @if ($referral->completed_at)
                        {{ $referral->created_at->diffInDays($referral->completed_at) }} days
                    @else
                        {{ $referral->created_at->diffInDays(now()) }} days ( ongoing )
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if ($referral->notes)
        <div class="section">
            <div class="section-title">
                <i class="ti ti-file-description"></i> Notes & Comments
            </div>
            <div class="notes-section">
                <div class="notes-title">Additional Information:</div>
                <div>{{ $referral->notes }}</div>
            </div>
        </div>
    @endif

    <!-- Documents -->
    @if (
        $referral->referrer_agreement ||
            $referral->referred_id_document ||
            $referral->additional_document_1 ||
            $referral->additional_document_2)
        <div class="section">
            <div class="section-title">
                <i class="ti ti-file-upload"></i> Supporting Documents
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Attached Documents:</strong>
            </div>
            <ul style="margin: 0; padding-left: 20px;">
                @if ($referral->referrer_agreement)
                    <li>Referrer Agreement</li>
                @endif
                @if ($referral->referred_id_document)
                    <li>ID Document</li>
                @endif
                @if ($referral->additional_document_1)
                    <li>Additional Document 1</li>
                @endif
                @if ($referral->additional_document_2)
                    <li>Additional Document 2</li>
                @endif
            </ul>
        </div>
    @endif

    <!-- Activity Timeline -->
    <div class="section">
        <div class="section-title">
            <i class="ti ti-clock"></i> Activity Timeline
        </div>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">{{ $referral->created_at->format('M j, Y g:i A') }}</div>
                <div class="timeline-content">
                    <div class="timeline-title">Referral Created</div>
                    <div class="timeline-description">Referral was created by
                        {{ $referral->created_by_name ?? 'System' }}</div>
                </div>
            </div>

            @if ($referral->approved_at)
                <div class="timeline-item">
                    <div class="timeline-date">
                        {{ \Carbon\Carbon::parse($referral->approved_at)->format('M j, Y g:i A') }}</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Referral Approved</div>
                        <div class="timeline-description">Referral was approved by
                            {{ $referral->approved_by_name ?? 'System' }}</div>
                    </div>
                </div>
            @endif

            @if ($referral->commission_paid_at)
                <div class="timeline-item">
                    <div class="timeline-date">
                        {{ \Carbon\Carbon::parse($referral->commission_paid_at)->format('M j, Y g:i A') }}</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Commission Paid</div>
                        <div class="timeline-description">Commission of
                            ${{ number_format($referral->commission_amount ?? 0, 2) }} was paid to referrer</div>
                    </div>
                </div>
            @endif

            @if ($referral->completed_at)
                <div class="timeline-item">
                    <div class="timeline-date">
                        {{ \Carbon\Carbon::parse($referral->completed_at)->format('M j, Y g:i A') }}</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Referral Completed</div>
                        <div class="timeline-description">Referral process was successfully completed</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <div>This document was generated electronically and is valid without signature.</div>
        <div>For any inquiries, please contact our referral department.</div>
        <div style="margin-top: 10px;">
            Page 1 of 1 | Generated on {{ now()->format('F j, Y \a\t g:i A') }}
        </div>
    </div>
</body>

</html>
