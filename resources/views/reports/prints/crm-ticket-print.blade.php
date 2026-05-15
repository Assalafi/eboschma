<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>CRM Ticket - {{ $ticket->ticket_id }}</title>
    <style>
        /* DomPDF Compatible CSS */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #01542B;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #01542B;
            font-size: 24px;
            margin: 0;
        }

        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        .section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            color: #01542B;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
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
            width: 30%;
            padding: 5px;
            font-weight: bold;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            width: 70%;
            padding: 5px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .description-box {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }

        .reply {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .reply-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .reply-author {
            font-weight: bold;
            color: #333;
        }

        .reply-date {
            color: #666;
            font-size: 11px;
        }

        .reply-content {
            margin: 10px 0;
        }

        .attachment {
            background-color: #e9ecef;
            padding: 8px;
            border-radius: 3px;
            margin: 5px 0;
            font-size: 11px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>CRM Ticket Details</h1>
        <p>{{ $ticket->ticket_id }} - Generated on {{ date('F j, Y \a\t H:i') }}</p>
    </div>

    <!-- Customer Information -->
    <div class="section">
        <div class="section-title">Customer Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value">{{ $ticket->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $ticket->email ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $ticket->phone ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">BOSCHMA No</div>
                <div class="info-value">
                    @if ($ticket->boschma_no)
                        <span class="badge badge-primary">{{ $ticket->boschma_no }}</span>
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Details -->
    <div class="section">
        <div class="section-title">Ticket Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Ticket ID</div>
                <div class="info-value"><code>{{ $ticket->ticket_id }}</code></div>
            </div>
            <div class="info-row">
                <div class="info-label">Category</div>
                <div class="info-value">{{ $ticket->category->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Department</div>
                <div class="info-value">{{ $ticket->department ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status</div>
                <div class="info-value">
                    @if ($ticket->status == 'completed')
                        <span class="badge badge-success">Completed</span>
                    @elseif($ticket->status == 'in_progress')
                        <span class="badge badge-warning">In Progress</span>
                    @else
                        <span class="badge badge-secondary">Pending</span>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Priority</div>
                <div class="info-value">
                    @if ($ticket->priority == 'high')
                        <span class="badge badge-danger">High</span>
                    @elseif($ticket->priority == 'medium')
                        <span class="badge badge-warning">Medium</span>
                    @else
                        <span class="badge badge-info">Low</span>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Assigned To</div>
                <div class="info-value">
                    @if ($ticket->assignedUser)
                        {{ $ticket->assignedUser->fullname }}
                    @else
                        Unassigned
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Facility</div>
                <div class="info-value">{{ $ticket->facility->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Created Date</div>
                <div class="info-value">{{ $ticket->created_at->format('F j, Y \a\t H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Due Date</div>
                <div class="info-value">{{ $ticket->due_date ? $ticket->due_date->format('F j, Y') : 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Resolved Date</div>
                <div class="info-value">
                    {{ $ticket->resolved_at ? $ticket->resolved_at->format('F j, Y \a\t H:i') : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Issue Complaint -->
    @if ($ticket->complaint)
        <div class="section">
            <div class="section-title">Complaint Subject</div>
            <div class="description-box">
                <p>{!! html_entity_decode($ticket->complaint) !!}</p>
            </div>
        </div>
    @endif

    <!-- Issue Description -->
    {{-- <div class="section">
        <div class="section-title">Issue Description</div>
        <div class="description-box">
            <p>{{ $ticket->description ?? 'No description provided' }}</p>
        </div>
    </div> --}}

    <!-- Conversation History -->
    <div class="section">
        <div class="section-title">Conversation History ({{ $ticket->replies ? $ticket->replies->count() : 0 }}
            replies)</div>

        @if ($ticket->replies && $ticket->replies->count() > 0)
            @foreach ($ticket->replies as $reply)
                <div class="reply">
                    <div class="reply-header">
                        <div class="reply-author">
                            {{ $reply->user->fullname ?? 'Unknown User' }}
                            @if ($reply->user->role)
                                <small style="color: #666;">({{ $reply->user->role }})</small>
                            @endif
                        </div>
                        <div class="reply-date">{{ $reply->created_at->format('F j, Y \a\t H:i') }}</div>
                    </div>
                    <div class="reply-content">
                        <p>{{ $reply->message ?? 'No message' }}</p>
                    </div>
                    @if ($reply->attachment_path)
                        <div class="attachment">
                            <strong>Attachment:</strong> {{ basename($reply->attachment_path) }}
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <p style="color: #666; font-style: italic;">No replies yet</p>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>CRM Ticket Report - Generated by Boschma Management System</p>
        <p>Page {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>

</html>
