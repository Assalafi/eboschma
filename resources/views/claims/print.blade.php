<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Print - {{ $claim->authorization_code }}</title>
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
            padding: 0;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 20px;
        }

        .print-header h1 {
            color: #0066cc;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .print-header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .claim-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #0066cc;
        }

        .claim-summary h2 {
            color: #0066cc;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .summary-label {
            font-weight: 600;
            color: #555;
        }

        .summary-value {
            font-weight: 500;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            background: #0066cc;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 15px 0;
            border-radius: 6px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 3px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 500;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-paid {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .documents-section {
            margin-top: 20px;
        }

        .document-item {
            display: flex;
            align-items: center;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .document-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            color: #0066cc;
        }

        .approval-timeline {
            margin-top: 20px;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-left: 30px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 8px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #0066cc;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 14px;
            top: 18px;
            width: 2px;
            height: calc(100% + 7px);
            background: #ddd;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-title {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .timeline-date {
            font-size: 11px;
            color: #666;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        .no-print {
            margin: 30px 0;
            text-align: center;
        }

        .no-print button {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .no-print button:hover {
            background: #0056b3;
        }

        @media print {
            body {
                font-size: 11px;
            }

            .no-print {
                display: none;
            }

            .section {
                page-break-inside: avoid;
            }

            .claim-summary {
                page-break-inside: avoid;
            }
        }

        @media screen {
            body {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Print Header -->
    <div class="print-header">
        <h1>BOSCHMA HEALTHCARE CLAIM FORM</h1>
        <p class="subtitle">Official Claim Document - For Processing and Records</p>
    </div>

    <!-- Claim Summary -->
    <div class="claim-summary">
        <h2>Claim Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-label">Authorization Code:</span>
                <span class="summary-value">{{ $claim->authorization_code }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Claim Status:</span>
                <span class="summary-value">
                    <span class="status-badge status-{{ $claim->status }}">
                        {{ ucfirst($claim->status) }}
                    </span>
                </span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Claim Amount:</span>
                <span class="summary-value">₦{{ number_format($claim->claim_amount, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Service Date:</span>
                <span class="summary-value">{{ $claim->service_date->format('M j, Y') }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Date Submitted:</span>
                <span class="summary-value">{{ $claim->created_at->format('M j, Y g:i A') }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Last Updated:</span>
                <span class="summary-value">{{ $claim->updated_at->format('M j, Y g:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Beneficiary Information -->
    <div class="section">
        <h3 class="section-title">
            <i style="margin-right: 8px;">👤</i> Beneficiary Information
        </h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Full Name</span>
                <span class="info-value">{{ $claim->beneficiary_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">BOSCHMA ID</span>
                <span class="info-value">{{ $claim->boschma_id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">National ID (NIN)</span>
                <span class="info-value">{{ $claim->nin }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Phone Number</span>
                <span class="info-value">{{ $claim->phone_number }}</span>
            </div>
        </div>
    </div>

    <!-- Claim Details -->
    <div class="section">
        <h3 class="section-title">
            <i style="margin-right: 8px;">📋</i> Claim Details
        </h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Claim Type</span>
                <span class="info-value">{{ ucfirst($claim->claim_type) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Healthcare Provider</span>
                <span class="info-value">{{ $claim->healthcare_provider }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Provider Type</span>
                <span class="info-value">{{ ucfirst($claim->provider_type) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Service Date</span>
                <span class="info-value">{{ $claim->service_date->format('M j, Y') }}</span>
            </div>
        </div>

        <div style="margin-top: 15px;">
            <div class="info-item">
                <span class="info-label">Diagnosis / Condition</span>
                <span class="info-value" style="min-height: 40px;">{{ $claim->diagnosis ?: 'Not specified' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Treatment Description</span>
                <span class="info-value"
                    style="min-height: 40px;">{{ $claim->treatment_description ?: 'Not specified' }}</span>
            </div>
        </div>
    </div>

    <!-- Approval Workflow -->
    <div class="section">
        <h3 class="section-title">
            <i style="margin-right: 8px;">✅</i> Approval Workflow
        </h3>
        <div class="approval-timeline">
            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-title">Claim Submitted</div>
                    <div class="timeline-date">{{ $claim->created_at->format('M j, Y g:i A') }}</div>
                </div>
            </div>

            @if ($claim->ro_status)
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">Reviewing Officer Status</div>
                        <div class="timeline-date">
                            {{ ucfirst($claim->ro_status) }}
                            @if ($claim->ro_updated_at)
                                - {{ $claim->ro_updated_at->format('M j, Y g:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($claim->e5_status)
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">E5 Approval Status</div>
                        <div class="timeline-date">
                            {{ ucfirst($claim->e5_status) }}
                            @if ($claim->e5_updated_at)
                                - {{ $claim->e5_updated_at->format('M j, Y g:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($claim->status === 'paid')
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">Payment Processed</div>
                        <div class="timeline-date">
                            @if ($claim->payment_date)
                                {{ $claim->payment_date->format('M j, Y') }}
                            @endif
                            @if ($claim->payment_reference)
                                - Ref: {{ $claim->payment_reference }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Supporting Documents -->
    <div class="section">
        <h3 class="section-title">
            <i style="margin-right: 8px;">📎</i> Supporting Documents
        </h3>
        <div class="documents-section">
            @if ($claim->medical_report)
                <div class="document-item">
                    <span class="document-icon">📄</span>
                    <span>Medical Report - Attached</span>
                </div>
            @endif

            @if ($claim->prescription)
                <div class="document-item">
                    <span class="document-icon">💊</span>
                    <span>Prescription - Attached</span>
                </div>
            @endif

            @if ($claim->receipt)
                <div class="document-item">
                    <span class="document-icon">🧾</span>
                    <span>Receipt/Invoice - Attached</span>
                </div>
            @endif

            @if (!$claim->medical_report && !$claim->prescription && !$claim->receipt)
                <div class="info-value">No supporting documents attached</div>
            @endif
        </div>
    </div>

    <!-- Additional Notes -->
    @if ($claim->additional_notes)
        <div class="section">
            <h3 class="section-title">
                <i style="margin-right: 8px;">📝</i> Additional Notes
            </h3>
            <div class="info-value" style="min-height: 60px; white-space: pre-wrap;">{{ $claim->additional_notes }}
            </div>
        </div>
    @endif

    <!-- Rejection Reason -->
    @if ($claim->status === 'rejected' && $claim->rejection_reason)
        <div class="section">
            <h3 class="section-title" style="background: #dc3545;">
                <i style="margin-right: 8px;">❌</i> Rejection Reason
            </h3>
            <div class="info-value" style="min-height: 60px; white-space: pre-wrap; color: #721c24;">
                {{ $claim->rejection_reason }}</div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>BOSCHMA HEALTHCARE MANAGEMENT SYSTEM</strong></p>
        <p>This document is an official claim record. For inquiries, contact the healthcare administration office.</p>
        <p>Generated on: {{ now()->format('M j, Y g:i A') }} | Document ID:
            CLAIM-{{ $claim->id }}-{{ $claim->authorization_code }}</p>
    </div>

    <!-- Print Controls -->
    <div class="no-print">
        <button onclick="window.print()">
            🖨️ Print Document
        </button>
        <button onclick="window.close()">
            ❌ Close Window
        </button>
        <button onclick="window.location.href='{{ route('claims.show', $claim->id) }}'">
            ↩️ Back to Claim
        </button>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>

</html>
