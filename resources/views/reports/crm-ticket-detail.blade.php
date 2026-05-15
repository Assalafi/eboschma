@extends('layouts.app')

@section('title', 'CRM Ticket Details - ' . $ticket->ticket_id)

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
                            Ticket Details: {{ $ticket->ticket_id }}
                        </h2>
                        <div class="text-muted mt-1">Full ticket information and conversation history</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.crm') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to CRM Report
                            </a>
                            <a href="{{ route('reports.crm.export') }}?ticket_id={{ $ticket->id }}"
                                class="btn btn-success d-none d-sm-inline-block">
                                <i class="ti ti-file-download me-2"></i>
                                Export
                            </a>
                            <a href="{{ route('reports.crm') }}/print?ticket_id={{ $ticket->id }}"
                                class="btn btn-primary d-none d-sm-inline-block">
                                <i class="ti ti-printer me-2"></i>
                                Print PDF
                            </a>
                            <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-success d-none d-sm-inline-block">
                                <i class="ti ti-edit me-2"></i>
                                Edit Ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Ticket Overview Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient-primary text-white py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="ti ti-ticket me-2"></i>
                                            {{ $ticket->ticket_id }}
                                        </h5>
                                        <small class="opacity-75">
                                            Created {{ $ticket->created_at->format('M j, Y \a\t H:i') }}
                                            @if ($ticket->resolved_at)
                                                • Resolved {{ $ticket->resolved_at->format('M j, Y \a\t H:i') }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @if ($ticket->status == 'completed')
                                            <span class="badge bg-success badge-lg">✓ Completed</span>
                                        @elseif($ticket->status == 'in_progress')
                                            <span class="badge bg-warning badge-lg">⏳ In Progress</span>
                                        @else
                                            <span class="badge bg-secondary badge-lg">⏸️ Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Customer Information -->
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted mb-3">Customer Information</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Name</label>
                                            <div class="fw-semibold">{{ $ticket->name }}</div>
                                        </div>
                                        @if ($ticket->email)
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Email</label>
                                                <div>{{ $ticket->email }}</div>
                                            </div>
                                        @endif
                                        @if ($ticket->phone)
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Phone</label>
                                                <div>{{ $ticket->phone }}</div>
                                            </div>
                                        @endif
                                        @if ($ticket->boschma_no)
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Boschma No</label>
                                                <div><span class="badge bg-primary">{{ $ticket->boschma_no }}</span></div>
                                            </div>
                                        @endif
                                        @if ($ticket->beneficiary_type)
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Beneficiary Type</label>
                                                <div>{{ $ticket->beneficiary_type }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Ticket Details -->
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted mb-3">Ticket Details</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Category</label>
                                            <div><span class="">{{ $ticket->category->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Department</label>
                                            <div><span class="badge bg-info">{{ $ticket->department ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Priority</label>
                                            <div>
                                                @if ($ticket->priority == 'high')
                                                    <span class="badge bg-danger">🔥 High</span>
                                                @elseif($ticket->priority == 'medium')
                                                    <span class="badge bg-warning">⚡ Medium</span>
                                                @else
                                                    <span class="badge bg-info">💧 Low</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Assigned To</label>
                                            <div>
                                                @if ($ticket->assignedUser)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-xs me-2">
                                                            <div class="avatar-placeholder bg-success text-white">
                                                                {{ strtoupper(substr($ticket->assignedUser->fullname, 0, 1)) }}
                                                            </div>
                                                        </div>
                                                        <span>{{ $ticket->assignedUser->fullname }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Created By</label>
                                            <div>
                                                @if ($ticket->createdBy)
                                                    {{ $ticket->createdBy->fullname }}
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Complaint and Description -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6 class="text-uppercase text-muted mb-3">Issue Details</h6>
                                        @if ($ticket->complaint)
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Complaint Subject</label>
                                                <div class="fw-semibold">{!! html_entity_decode($ticket->complaint) !!}</div>
                                            </div>
                                        @endif
                                        @if ($ticket->description)
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Description</label>
                                                <div class="bg-light p-3 rounded">{{ nl2br(e($ticket->description)) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Timeline Information -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <div class="text-muted small">Created</div>
                                            <div class="fw-semibold">{{ $ticket->created_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-muted">{{ $ticket->created_at->format('H:i') }}</div>
                                        </div>
                                    </div>
                                    @if ($ticket->due_date)
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="text-muted small">Due Date</div>
                                                <div
                                                    class="fw-semibold {{ $ticket->due_date->isPast() && $ticket->status !== 'completed' ? 'text-danger' : '' }}">
                                                    {{ $ticket->due_date->format('M j, Y') }}
                                                </div>
                                                <div class="text-xs text-muted">{{ $ticket->due_date->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($ticket->resolved_at)
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="text-muted small">Resolution Time</div>
                                                <div class="fw-semibold">
                                                    {{ $ticket->created_at->diffInHours($ticket->resolved_at) }}h</div>
                                                <div class="text-xs text-muted">
                                                    {{ $ticket->created_at->diffInDays($ticket->resolved_at) }} days</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversation History -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient-info text-white py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="ti ti-message me-2"></i>
                                            Conversation History
                                        </h5>
                                        <small class="opacity-75">{{ $ticket->replies->count() }} replies</small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-light" onclick="toggleReplies()">
                                            <i class="ti ti-eye me-1"></i> Toggle
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if ($ticket->replies->isEmpty())
                                    <div class="text-center py-8">
                                        <div class="mb-4">
                                            <i class="ti ti-message-off text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-muted">No replies yet</h5>
                                        <p class="text-muted">This ticket hasn't received any responses.</p>
                                    </div>
                                @else
                                    <div class="timeline timeline-snap-pointer"
                                        style="--tb-line-width: 2px; --tb-line-style: dashed; --tb-line-color: #dee2e6;">
                                        @foreach ($ticket->replies as $reply)
                                            <div class="timeline-item">
                                                <div class="timeline-point timeline-point-primary"></div>
                                                <div class="timeline-content">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="avatar avatar-sm me-3">
                                                                    <div class="avatar-placeholder bg-primary text-white">
                                                                        {{ strtoupper(substr($reply->user->fullname, 0, 1)) }}
                                                                    </div>
                                                                </div>
                                                                <div class="flex-fill">
                                                                    <div class="fw-semibold">{{ $reply->user->fullname }}
                                                                    </div>
                                                                    <div class="text-muted small">
                                                                        {{ $reply->created_at->format('M j, Y \a\t H:i') }}
                                                                    </div>
                                                                </div>
                                                                @if ($reply->is_internal)
                                                                    <span class="badge bg-warning">Internal</span>
                                                                @else
                                                                    <span class="badge bg-success">Public</span>
                                                                @endif
                                                            </div>
                                                            <div class="mb-3">
                                                                {{ nl2br(e($reply->message)) }}
                                                            </div>
                                                            @if ($reply->attachment_name)
                                                                <div class="d-flex align-items-center text-muted">
                                                                    <i class="ti ti-paperclip me-2"></i>
                                                                    <span>{{ $reply->attachment_name }}</span>
                                                                    @if ($reply->attachment_path)
                                                                        <a href="{{ asset('storage/' . $reply->attachment_path) }}"
                                                                            class="ms-2 btn btn-sm btn-outline-primary">
                                                                            <i class="ti ti-download me-1"></i> Download
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleReplies() {
            const timeline = document.querySelector('.timeline');
            timeline.classList.toggle('timeline-compact');
        }

        // Add print styles
        window.addEventListener('beforeprint', function() {
            document.querySelectorAll('.btn, .d-print-none').forEach(el => {
                el.style.display = 'none';
            });
        });

        window.addEventListener('afterprint', function() {
            document.querySelectorAll('.btn, .d-print-none').forEach(el => {
                el.style.display = '';
            });
        });
    </script>
@endsection
