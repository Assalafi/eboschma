@extends('layouts.app')

@section('title', 'CRM Category Breakdown - ' . ($category->name ?? 'All Categories'))

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
                            Category Breakdown: {{ $category->name ?? 'All Categories' }}
                        </h2>
                        <div class="text-muted mt-1">{{ $tickets->count() }} tickets in this category</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.crm') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to CRM Report
                            </a>
                            <a href="{{ route('reports.crm.export') }}?category={{ request('category') }}"
                                class="btn btn-success d-none d-sm-inline-block">
                                <i class="ti ti-file-download me-2"></i>
                                Export
                            </a>
                            <a href="{{ route('reports.crm') }}/print?category={{ request('category') }}"
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
                <!-- Category Summary Card -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Tickets</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($tickets->count()) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">In {{ $category->name ?? 'this category' }}</div>
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
                                    <div class="subheader text-muted fs-6">In Progress</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($tickets->where('status', 'in_progress')->count()) }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Currently being handled</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Pending</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($tickets->where('status', 'pending')->count()) }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Awaiting action</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Tickets in {{ $category->name ?? 'this category' }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ticket ID</th>
                                                <th>Boschma No</th>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Created Date</th>
                                                <th>Assigned To</th>
                                                <th>Replies</th>
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
                                                            class="badge bg-info">{{ $ticket->department ?? 'N/A' }}</span>
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
                                                        @if ($ticket->assignedUser)
                                                            {{ $ticket->assignedUser->fullname }}
                                                        @else
                                                            <span class="text-muted">Unassigned</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($ticket->replies && $ticket->replies->count() > 0)
                                                            <span
                                                                class="badge bg-success">{{ $ticket->replies->count() }}</span>
                                                        @else
                                                            <span class="text-muted">0</span>
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
                                        <p class="text-muted">No tickets in {{ $category->name ?? 'this category' }}.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
