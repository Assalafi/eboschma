@extends('layouts.app')

@section('title', 'CRM Department Breakdown - ' . $department)

@section('content')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            <a href="{{ route('reports.crm') }}">CRM Report</a>
                        </div>
                        <h2 class="page-title">
                            Department Breakdown: {{ $department }}
                        </h2>
                        <div class="text-muted mt-1">{{ $tickets->count() }} tickets for this department</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.crm') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to CRM Report
                            </a>
                            <a href="{{ route('reports.crm.export') }}?department={{ request('department') }}"
                                class="btn btn-success d-none d-sm-inline-block">
                                <i class="ti ti-file-download me-2"></i>
                                Export
                            </a>
                            <a href="{{ route('reports.crm') }}/print?department={{ request('department') }}"
                                class="btn btn-primary d-none d-sm-inline-block">
                                <i class="ti ti-printer me-2"></i>
                                Print PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Department Summary Card -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Tickets</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($tickets->count()) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">For {{ $department }} department</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Completed</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($tickets->where('status', 'completed')->count()) }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Successfully resolved</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Avg Resolution</div>
                                </div>
                                <div class="h3 mb-2">
                                    {{ $tickets->where('status', 'completed')->whereNotNull('resolved_at')->avg(function ($t) {
                                            return $t->created_at->diffInHours($t->resolved_at);
                                        })
                                        ? round(
                                            $tickets->where('status', 'completed')->whereNotNull('resolved_at')->avg(function ($t) {
                                                    return $t->created_at->diffInHours($t->resolved_at);
                                                }),
                                        )
                                        : 0 }}h
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Average resolution time</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">High Priority</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($tickets->where('priority', 'high')->count()) }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Urgent tickets</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department Performance Chart -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Status Distribution</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Priority Distribution</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="priorityChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ $department }} Department Tickets</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
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
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tickets as $ticket)
                                                <tr>
                                                    <td>
                                                        <code>{{ $ticket->ticket_id }}</code>
                                                    </td>
                                                    <td>
                                                        @if ($ticket->boschma_no)
                                                            <span class="badge bg-primary">{{ $ticket->boschma_no }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $ticket->name }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-blue">{{ $ticket->category->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($ticket->status == 'completed')
                                                            <span class="badge bg-success">Completed</span>
                                                        @elseif($ticket->status == 'in_progress')
                                                            <span class="badge bg-warning">In Progress</span>
                                                        @else
                                                            <span class="badge bg-secondary">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($ticket->priority == 'high')
                                                            <span class="badge bg-danger">High</span>
                                                        @elseif($ticket->priority == 'medium')
                                                            <span class="badge bg-warning">Medium</span>
                                                        @else
                                                            <span class="badge bg-info">Low</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $ticket->created_at->format('M j, Y') }}</td>
                                                    <td>
                                                        @if ($ticket->resolved_at)
                                                            {{ $ticket->resolved_at->format('M j, Y') }}
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
                                                    <td>
                                                        <a href="{{ route('reports.crm') }}/{{ $ticket->id }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="ti ti-eye me-1"></i>
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($tickets->isEmpty())
                                    <div class="text-center py-8">
                                        <div class="mb-4">
                                            <i class="ti ti-inbox text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-muted">No tickets found</h5>
                                        <p class="text-muted">No tickets for {{ $department }} department.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = {
            completed: {{ $tickets->where('status', 'completed')->count() }},
            in_progress: {{ $tickets->where('status', 'in_progress')->count() }},
            pending: {{ $tickets->where('status', 'pending')->count() }}
        };

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                    data: [statusData.completed, statusData.in_progress, statusData.pending],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Priority Chart
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        const priorityData = {
            high: {{ $tickets->where('priority', 'high')->count() }},
            medium: {{ $tickets->where('priority', 'medium')->count() }},
            low: {{ $tickets->where('priority', 'low')->count() }}
        };

        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: ['High', 'Medium', 'Low'],
                datasets: [{
                    label: 'Number of Tickets',
                    data: [priorityData.high, priorityData.medium, priorityData.low],
                    backgroundColor: ['#dc3545', '#ffc107', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
