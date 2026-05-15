<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>CRM Department Report - {{ request('department') }}</title>
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

        .summary-section {
            margin-bottom: 30px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .summary-box {
            text-align: center;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #01542B;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }

        .stats-section {
            margin-bottom: 30px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .stats-row {
            display: table-row;
        }

        .stats-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .stats-box {
            padding: 15px;
        }

        .stats-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .stats-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .stats-item {
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }

        .stats-item:last-child {
            border-bottom: none;
        }

        .stats-name {
            display: inline-block;
            width: 70%;
            font-size: 11px;
        }

        .stats-count {
            display: inline-block;
            width: 30%;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }

        .table-section {
            margin-bottom: 20px;
        }

        .table-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
            text-transform: uppercase;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 9px;
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

        .badge-blue {
            background-color: #0056b3;
            color: white;
        }

        .text-muted {
            color: #666;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .filters-info {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
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
        <h1>CRM Department Report</h1>
        <p>Department: {{ request('department') }} - Generated on {{ date('F j, Y \a\t H:i') }}</p>
    </div>

    <!-- Filter Information -->
    <div class="filters-info">
        <strong>Filter Applied:</strong> Department = {{ request('department') }}
        <br><strong>Total Tickets:</strong> {{ $tickets->count() }}
    </div>

    <!-- Summary Cards -->
    <div class="summary-section">
        <h2 style="color: #01542B; margin-bottom: 15px;">Summary Statistics</h2>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-box">
                        <div class="summary-number">{{ number_format($tickets->count()) }}</div>
                        <div class="summary-label">Total Tickets</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="summary-box">
                        <div class="summary-number">{{ number_format($tickets->where('status', 'completed')->count()) }}
                        </div>
                        <div class="summary-label">Completed</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="summary-box">
                        <div class="summary-number">
                            {{ round($tickets->where('status', 'completed')->whereNotNull('resolved_at')->avg(function ($t) {return $t->created_at->diffInHours($t->resolved_at);}) ?? 0) }}h
                        </div>
                        <div class="summary-label">Avg Resolution</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="summary-box">
                        <div class="summary-number">{{ number_format($tickets->where('priority', 'high')->count()) }}
                        </div>
                        <div class="summary-label">High Priority</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Performance Stats -->
    <div class="stats-section">
        <h2 style="color: #01542B; margin-bottom: 15px;">Department Performance Analysis</h2>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-box">
                        <div class="stats-title">Status Distribution</div>
                        <ul class="stats-list">
                            @foreach ($statusStats as $status => $count)
                                <li class="stats-item">
                                    <span class="stats-name">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    <span class="stats-count">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="stats-cell">
                    <div class="stats-box">
                        <div class="stats-title">Category Distribution</div>
                        <ul class="stats-list">
                            @foreach ($categoryStats as $category => $count)
                                <li class="stats-item">
                                    <span class="stats-name">{{ $category ?? 'N/A' }}</span>
                                    <span class="stats-count">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="table-section">
        <h2 style="color: #01542B; margin-bottom: 15px;">Ticket Details ({{ $tickets->count() }} tickets)</h2>
        <table>
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Boschma No</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Created Date</th>
                    <th>Resolved Date</th>
                    <th>Resolution Time</th>
                    <th>Assigned To</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td><code>{{ $ticket->ticket_id }}</code></td>
                        <td>
                            @if ($ticket->boschma_no)
                                <span class="badge badge-primary">{{ $ticket->boschma_no }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $ticket->name }}</td>
                        <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                        <td>
                            @if ($ticket->status == 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge badge-warning">In Progress</span>
                            @else
                                <span class="badge badge-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if ($ticket->priority == 'high')
                                <span class="badge badge-danger">High</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge badge-warning">Medium</span>
                            @else
                                <span class="badge badge-info">Low</span>
                            @endif
                        </td>
                        <td>{{ $ticket->created_at->format('M j, Y H:i') }}</td>
                        <td>
                            @if ($ticket->resolved_at)
                                {{ $ticket->resolved_at->format('M j, Y H:i') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($ticket->resolved_at)
                                {{ $ticket->created_at->diffInHours($ticket->resolved_at) }}h
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($ticket->assignedUser)
                                {{ $ticket->assignedUser->fullname }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($tickets->isEmpty())
            <div style="text-align: center; padding: 40px; color: #666;">
                <p style="font-size: 14px; margin: 0;">No tickets found for {{ request('department') }} department.</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>CRM Department Report - Generated by Boschma Management System</p>
        <p>Page {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>

</html>
