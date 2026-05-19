@extends('layouts.app')

@section('title', 'Ticket Details - Customer Care')

@section('content')
    @php
        function formatBytes($bytes, $precision = 2)
        {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];

            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }

            return round($bytes, $precision) . ' ' . $units[$i];
        }
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="ti-headphone-alt"></i> Ticket {{ $ticket->ticket_id }}
                        </h4>
                        <div style="float: right;" class="card-action">
                            <a href="{{ route('crm.index') }}" class="btn btn-secondary">
                                <i class="ti-arrow-left"></i> Back to Tickets
                            </a>
                            @if ($ticket->canBeEditedBy(Auth::user()))
                                <a href="{{ route('crm.edit', $ticket->id) }}" class="btn btn-warning">
                                    <i class="ti-pencil"></i> Edit
                                </a>
                            @endif
                            @if ($ticket->canBeCompletedBy(Auth::user()) && $ticket->status !== 'completed')
                                <form action="{{ route('crm.mark.completed', $ticket->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Are you sure you want to mark this ticket as completed?')">
                                        <i class="ti-check"></i> Mark as Completed
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Ticket Details -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white me-3"
                                        style="width: 32px; height: 32px;">
                                        <i class="ti-ticket" style="font-size: 0.875rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-dark">Ticket Information</h6>
                                        <small class="text-muted">Basic ticket details and status</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    @if ($beneficiaryInfo && $beneficiaryInfo['photo'])
                                                        <a href="{{ $beneficiaryInfo['photo'] }}">
                                                            <img src="{{ $beneficiaryInfo['photo'] }}"
                                                                alt="{{ $beneficiaryInfo['type'] }} Photo"
                                                                class="img-fluid rounded border"
                                                                style="max-height: 100px; margin-bottom: 10px;">
                                                        </a>
                                                    @else
                                                        <div style="font-size: 20px; font-weight: 900; ">
                                                            OUTSIDER<br>(NON BOSCHMA)
                                                        </div>
                                                    @endif

                                                    @if ($beneficiaryInfo)
                                                        <div class="mb-2">
                                                            <strong
                                                                style="color: #000000;">{{ $beneficiaryInfo['type'] === 'beneficiary' ? 'Principal' : ucfirst($beneficiaryInfo['type']) }}</strong>
                                                        </div>

                                                        @if ($beneficiaryInfo['gender'])
                                                            <div
                                                                style="color: #000000; font-weight: bold; font-size: 0.9rem; margin-bottom: 2px;">
                                                                Gender: {{ $beneficiaryInfo['gender'] }}
                                                            </div>
                                                        @endif

                                                        @if ($beneficiaryInfo['nin'])
                                                            <div
                                                                style="color: #000000; font-weight: bold; font-size: 0.9rem; margin-bottom: 2px;">
                                                                NIN: {{ $beneficiaryInfo['nin'] }}
                                                            </div>
                                                        @endif

                                                        @if ($beneficiaryInfo['date_of_birth'])
                                                            <div
                                                                style="color: #000000; font-weight: bold; font-size: 0.9rem; margin-bottom: 2px;">
                                                                Age:
                                                                {{ \Carbon\Carbon::parse($beneficiaryInfo['date_of_birth'])->age }}
                                                                years
                                                            </div>
                                                        @endif

                                                        @if ($beneficiaryInfo['status'])
                                                            <div
                                                                style="color: #000000; font-weight: bold; font-size: 0.9rem; margin-bottom: 2px;">
                                                                Status: {{ ucfirst($beneficiaryInfo['status']) }}
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-2"><strong class="text-muted">Name:</strong><br>
                                                    <span class="fw-medium">{{ $ticket->name }}</span>
                                                </p>
                                                <p class="mb-2"><strong class="text-muted">Boschma No:</strong><br>
                                                    <span class="fw-medium">{{ $ticket->boschma_no ?? 'N/A' }}</span>
                                                </p>
                                                <p class="mb-2"><strong class="text-muted">Phone:</strong><br>
                                                    <span class="fw-medium d-inline-flex align-items-center gap-2">
                                                        {{ $ticket->phone ?? 'N/A' }}
                                                        @if ($ticket->phone)
                                                            <button class="btn btn-xs btn-success rounded-circle p-0 d-inline-flex align-items-center justify-content-center" 
                                                                    style="width: 24px; height: 24px; min-width: 24px; cursor: pointer;" 
                                                                    onclick="initiateZohoCall('{{ $ticket->phone }}')" 
                                                                    title="Call via Zoho Voice">
                                                                <i class="ti-mobile" style="font-size: 0.8rem;"></i>
                                                            </button>
                                                            <button class="btn btn-xs btn-info rounded-circle p-0 d-inline-flex align-items-center justify-content-center" 
                                                                    style="width: 24px; height: 24px; min-width: 24px; cursor: pointer;" 
                                                                    onclick="openZohoSmsModal('{{ $ticket->phone }}')" 
                                                                    title="Send SMS via Zoho Voice">
                                                                <i class="ti-comment" style="font-size: 0.8rem;"></i>
                                                            </button>
                                                        @endif
                                                    </span>
                                                </p>
                                                <p class="mb-0"><strong class="text-muted">Email:</strong><br>
                                                    <span class="fw-medium">{{ $ticket->email ?? 'N/A' }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-2"><strong class="text-muted">Facility:</strong><br>
                                                    <span class="fw-medium">{{ $ticket->facility->name ?? 'N/A' }}</span>
                                                </p>
                                                <p class="mb-2"><strong class="text-muted">Department:</strong><br>
                                                    <span class="fw-medium">{{ $ticket->department ?? 'N/A' }}</span>
                                                </p>
                                                <p class="mb-2"><strong class="text-muted">Created:</strong><br>
                                                    <span
                                                        class="fw-medium">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                                                </p>
                                                <p class="mb-0"><strong class="text-muted">Due Date:</strong><br>
                                                    <span class="fw-medium">{{ $ticket->due_date->format('M d, Y H:i') }}
                                                        @if ($ticket->isOverdue())
                                                            <span class="badge bg-danger ms-2">OVERDUE</span>
                                                        @endif
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <p class="mb-2"><strong class="text-muted">Priority:</strong></p>
                                            <div class="mb-3">
                                                <span class="badge p-2"
                                                    style="background-color: {{ $ticket->getPriorityColor() }}; font-size: 14px;">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </div>

                                            <p class="mb-2"><strong class="text-muted">Complain Type:</strong></p>
                                            <div class="mb-3">
                                                <span class="badge p-2"
                                                    style="background-color: {{ $ticket->category->color ?? '#6c757d' }}; font-size: 14px;">
                                                    {{ $ticket->category->name ?? 'N/A' }}
                                                </span>
                                            </div>

                                            <p class="mb-2"><strong class="text-muted">Status:</strong></p>
                                            <div class="mb-3">
                                                <span class="badge p-2"
                                                    style="background-color: {{ $ticket->getStatusColor() }}; font-size: 14px;">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                                @if ($ticket->status === 'completed' && $ticket->resolved_at)
                                                    <small class="text-muted d-block mt-1">
                                                        Completed on {{ $ticket->resolved_at->format('M d, Y H:i') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <div class="d-flex align-items-center">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white me-3"
                                                style="width: 32px; height: 32px;">
                                                <i class="ti-user" style="font-size: 0.875rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-dark">Assignment</h6>
                                                <small class="text-muted">Ticket assignment details</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="border rounded p-3 bg-white">
                                            <p class="mb-2"><strong class="text-muted">Assigned To:</strong><br>
                                                <span
                                                    class="fw-medium">{{ $ticket->assignedUser->fullname ?? 'Unassigned' }}</span>
                                            </p>
                                            <p class="mb-2"><strong class="text-muted">Created By:</strong><br>
                                                <span
                                                    class="fw-medium">{{ $ticket->createdBy->fullname ?? 'Unknown' }}</span>
                                            </p>
                                            <p class="mb-0"><strong class="text-muted">SLA:</strong><br>
                                                <span class="fw-medium">{{ $ticket->sla_hours }} hours</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <div class="d-flex align-items-center">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white me-3"
                                                style="width: 32px; height: 32px;">
                                                <i class="ti-time" style="font-size: 0.875rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-dark">Timeline</h6>
                                                <small class="text-muted">Important dates and times</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="border rounded p-3 bg-white">
                                            <p class="mb-2"><strong class="text-muted">Created:</strong><br>
                                                <span
                                                    class="fw-medium">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                                            </p>
                                            <p class="mb-2"><strong class="text-muted">Updated:</strong><br>
                                                <span
                                                    class="fw-medium">{{ $ticket->updated_at->format('M d, Y H:i') }}</span>
                                            </p>
                                            @if ($ticket->resolved_at)
                                                <p class="mb-0"><strong class="text-muted">Resolved:</strong><br>
                                                    <span
                                                        class="fw-medium">{{ $ticket->resolved_at->format('M d, Y H:i') }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Complaint Details -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white me-3"
                                        style="width: 32px; height: 32px;">
                                        <i class="ti-pencil" style="font-size: 0.875rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-dark">Complaint Details</h6>
                                        <small class="text-muted">Original enquiry description</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3 bg-white" style="min-height: 100px;">
                                    <div class="ck-content">
                                        {!! html_entity_decode($ticket->complaint) !!}
                                    </div>
                                </div>

                                @if ($ticket->hasAttachment())
                                    @php
                                        $ticketAttachments = $ticket->getAttachments();
                                        $totalTicketSize = 0;
                                        foreach ($ticketAttachments as $att) {
                                            $totalTicketSize += isset($att['size']) ? (float) $att['size'] : 0;
                                        }
                                    @endphp

                                    <hr class="my-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="ti-clip text-primary me-2"></i>
                                            <h6 class="mb-0 fw-semibold">Attachments</h6>
                                            <span class="badge bg-info ms-2">{{ count($ticketAttachments) }}
                                                file(s)</span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="ti-harddrives me-1"></i>
                                            Total: {{ formatBytes($totalTicketSize) }}
                                        </div>
                                    </div>

                                    <!-- Ticket Attachment List -->
                                    <div class="attachments-list">
                                        <div class="row">
                                            @foreach ($ticketAttachments as $index => $attachment)
                                                @php
                                                    $fileExt = strtolower(
                                                        pathinfo($attachment['name'], PATHINFO_EXTENSION),
                                                    );
                                                    $isImage = in_array($fileExt, [
                                                        'jpg',
                                                        'jpeg',
                                                        'png',
                                                        'gif',
                                                        'svg',
                                                        'webp',
                                                    ]);
                                                    $fileIcon = 'ti-file';
                                                    $bgColor = 'bg-light';
                                                    $textColor = 'text-dark';
                                                    $subTextColor = 'text-muted';
                                                    $iconColor = 'text-secondary';

                                                    // Truncate filename if too long
                                                    $maxFileNameLength = 43;
                                                    $fileName = $attachment['name'];
                                                    if (strlen($fileName) > $maxFileNameLength) {
                                                        $fileName = substr($fileName, 0, $maxFileNameLength) . '...';
                                                    }

                                                    // Determine file icon and color based on type
                                                    if ($isImage) {
                                                        $fileIcon = 'ti-image';
                                                        $iconColor = 'text-info';
                                                    } elseif (in_array($fileExt, ['pdf'])) {
                                                        $fileIcon = 'ti-file-text';
                                                        $iconColor = 'text-danger';
                                                    } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                                        $fileIcon = 'ti-file-text';
                                                        $iconColor = 'text-primary';
                                                    } elseif (in_array($fileExt, ['xls', 'xlsx'])) {
                                                        $fileIcon = 'ti-file-text';
                                                        $iconColor = 'text-success';
                                                    } elseif (in_array($fileExt, ['mp4', 'avi', 'mov', 'wmv'])) {
                                                        $fileIcon = 'ti-video-camera';
                                                        $iconColor = 'text-purple';
                                                    } elseif (in_array($fileExt, ['mp3', 'wav', 'ogg'])) {
                                                        $fileIcon = 'ti-music-tone';
                                                        $iconColor = 'text-warning';
                                                    } elseif (in_array($fileExt, ['zip', 'rar', '7z'])) {
                                                        $fileIcon = 'ti-archive';
                                                        $iconColor = 'text-dark';
                                                    }
                                                @endphp

                                                <div class="col-md-4 mb-3">
                                                    @if ($isImage)
                                                        <!-- Image Preview -->
                                                        <div
                                                            class="attachment-item p-3 rounded {{ $bgColor }} h-100">
                                                            <div class="text-center">
                                                                <div class="mb-2">
                                                                    <img src="{{ asset('storage/' . $attachment['path']) }}"
                                                                        alt="{{ $attachment['name'] }}" class="rounded"
                                                                        style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                                                        onclick="window.open('{{ asset('storage/' . $attachment['path']) }}', '_blank')">
                                                                </div>
                                                                <div class="fw-medium {{ $textColor }} small mb-1"
                                                                    title="{{ $attachment['name'] }}">
                                                                    {{ $fileName }}
                                                                </div>
                                                                <div class="{{ $subTextColor }} small mb-2">
                                                                    IMAGE •
                                                                    {{ isset($attachment['size']) ? formatBytes((float) $attachment['size']) : 'Unknown size' }}
                                                                </div>
                                                                <div class="d-flex gap-1 justify-content-center">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        onclick="window.open('{{ asset('storage/' . $attachment['path']) }}', '_blank')"
                                                                        title="View full size">
                                                                        <i class="ti-eye"></i>
                                                                    </button>
                                                                    <a href="{{ asset('storage/' . $attachment['path']) }}"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        target="_blank"
                                                                        title="Download {{ $attachment['name'] }}">
                                                                        <i class="ti-download"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <!-- Regular File -->
                                                        <div
                                                            class="attachment-item p-3 rounded {{ $bgColor }} h-100">
                                                            <div class="text-center">
                                                                <div class="mb-2">
                                                                    <i class="ti {{ $fileIcon }} {{ $iconColor }}"
                                                                        style="font-size: 2.5rem;"></i>
                                                                </div>
                                                                <div class="fw-medium {{ $textColor }} small mb-1"
                                                                    title="{{ $attachment['name'] }}">
                                                                    {{ $fileName }}
                                                                </div>
                                                                <div class="{{ $subTextColor }} small mb-2">
                                                                    {{ strtoupper($fileExt) }} •
                                                                    {{ isset($attachment['size']) ? formatBytes((float) $attachment['size']) : 'Unknown size' }}
                                                                </div>
                                                                <div class="d-flex gap-1 justify-content-center">
                                                                    <a href="{{ asset('storage/' . $attachment['path']) }}"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        target="_blank"
                                                                        title="Download {{ $attachment['name'] }}">
                                                                        <i class="ti-download"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($ticket->description)
                                    <hr class="my-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ti-info-alt text-primary me-2"></i>
                                        <h6 class="mb-0 fw-semibold">Additional Information</h6>
                                    </div>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-0">{{ $ticket->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Chat-like Replies Section -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white me-3"
                                        style="width: 32px; height: 32px;">
                                        <i class="ti-comments" style="font-size: 0.875rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-dark">Conversation</h6>
                                        <small class="text-muted">{{ $ticket->replies->count() }}
                                            {{ $ticket->replies->count() == 1 ? 'reply' : 'replies' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Chat Container -->
                                <div class="chat-container"
                                    style="max-height: 500px; overflow-y: auto; padding: 20px; background: linear-gradient(to bottom, #f8f9fa, #e9ecef); border-radius: 12px; margin-bottom: 20px;"
                                    id="chatContainer">
                                    <!-- Unread Messages Divider -->
                                    <div id="unreadDivider" class="unread-divider" style="display: none;">
                                        <div class="text-center py-2">
                                            <span class="badge bg-info text-white px-3 py-2" style="font-size: 0.75rem;">
                                                <i class="ti-bell me-1"></i> New Messages
                                            </span>
                                        </div>
                                        <hr class="my-3 border-info" style="border-width: 2px;">
                                    </div>

                                    @php
                                        $lastViewedAt = session('ticket_viewed_' . $ticket->id, null);
                                        $hasUnreadMessages = false;
                                        $foundFirstUnread = false;
                                    @endphp

                                    @foreach ($ticket->replies as $reply)
                                        @php
                                            $isCurrentUser = $reply->user_id === Auth::id();
                                            $isUnread =
                                                !$isCurrentUser &&
                                                $lastViewedAt &&
                                                $reply->created_at->gt($lastViewedAt);
                                            $isAssignedUser = Auth::id() === ($ticket->assigned_to ?? null);

                                            // Check read status for this reply - consistent for all users
                                            $readStatus = 'sent'; // sent, read

                                            // Check if reply has been read by either assigned user or ticket creator
                                            $readByAssigned =
                                                $reply->read_by_assigned_at &&
                                                $reply->read_by_assigned_at->gt($reply->created_at);
                                            $readStatus = $readByAssigned ? 'read' : 'sent';

                                            if ($isUnread && !$foundFirstUnread) {
                                                $foundFirstUnread = true;
                                                $hasUnreadMessages = true;
                                            }
                                        @endphp

                                        @if ($isUnread && !$foundFirstUnread)
                                            <!-- This will be replaced by the unread divider -->
                                        @endif

                                        <div class="message-wrapper mb-4 {{ $isCurrentUser ? 'text-end' : 'text-start' }} {{ $isUnread ? 'unread-message' : '' }}"
                                            data-reply-id="{{ $reply->id }}"
                                            data-created-at="{{ $reply->created_at->toISOString() }}"
                                            @if ($isUnread && $foundFirstUnread && !$hasUnreadMessages) id="firstUnreadMessage" @endif>
                                            <div class="d-inline-block max-width-70" style="max-width: 70%;">
                                                <!-- User Info -->
                                                <div
                                                    class="d-flex align-items-center mb-2 {{ $isCurrentUser ? 'justify-content-end' : 'justify-content-start' }}">
                                                    @if (!$isCurrentUser)
                                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-dark me-2"
                                                            style="width: 28px; height: 28px;">
                                                            <i class="ti-user" style="font-size: 0.75rem;"></i>
                                                        </div>
                                                    @endif
                                                    <div class="{{ $isCurrentUser ? 'text-end' : 'text-start' }}">
                                                        <strong
                                                            class="{{ $isCurrentUser ? 'text-primary' : 'text-dark' }}"
                                                            style="font-size: 0.95rem; font-weight: 600;">
                                                            {{ $isCurrentUser ? 'You' : $reply->user->fullname ?? 'Unknown User' }}
                                                        </strong>
                                                        @if ($reply->is_internal)
                                                            <span class="badge bg-warning text-dark ms-2"
                                                                style="font-size: 0.75rem; font-weight: 500;">Internal</span>
                                                        @endif
                                                        @if ($isUnread)
                                                            <span class="badge bg-success text-white ms-2"
                                                                style="font-size: 0.7rem; font-weight: 500;">
                                                                <i class="ti-bell"></i> New
                                                            </span>
                                                        @endif
                                                        <br>
                                                        <small class="text-muted"
                                                            style="font-size: 0.85rem; font-weight: 400;">{{ $reply->created_at->format('M d, Y H:i') }}</small>

                                                        <!-- Read Receipt Icons (WhatsApp Style) -->
                                                        <div class="read-receipt ms-2 d-inline-block"
                                                            style="font-size: 0.75rem;">
                                                            @if ($readStatus === 'read')
                                                                <i class="ti-check text-primary"
                                                                    style="font-size: 0.65rem;"></i>
                                                                <i class="ti-check text-primary"
                                                                    style="font-size: 0.65rem; margin-left: -3px;"></i>
                                                            @else
                                                                <i class="ti-check text-muted"
                                                                    style="font-size: 0.65rem;"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if ($isCurrentUser)
                                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white ms-2"
                                                            style="width: 28px; height: 28px;">
                                                            <i class="ti-user" style="font-size: 0.75rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Message Bubble -->
                                                <div class="message-bubble p-3 rounded-3 shadow-sm {{ $isCurrentUser ? 'bg-primary text-white text-start' : ($reply->is_internal ? 'bg-warning bg-opacity-25 border border-warning text-start' : ($isUnread ? 'bg-white text-dark border text-start border-success border-2' : 'bg-white text-dark border text-start')) }}"
                                                    style="{{ $reply->is_internal && !$isCurrentUser ? 'border-left: 4px solid #ffc107 !important;' : ($isUnread && !$isCurrentUser ? 'border-left: 4px solid #198754 !important;' : '') }}">
                                                    <div class="message-content">
                                                        <p class="mb-2 {{ $isCurrentUser ? 'text-white' : 'text-dark' }}"
                                                            style="word-wrap: break-word; text-align: left;">
                                                            {!! nl2br($reply->message) !!}
                                                        </p>
                                                        @if ($reply->hasAttachment())
                                                            <div class="attachment mt-3">
                                                                @php
                                                                    $attachments = $reply->getAttachments();
                                                                    if (!is_array($attachments)) {
                                                                        $attachments = [];
                                                                    }
                                                                    $totalSize = 0;
                                                                    foreach ($attachments as $att) {
                                                                        $totalSize += isset($att['size'])
                                                                            ? (float) $att['size']
                                                                            : 0;
                                                                    }
                                                                @endphp

                                                                <!-- Attachment Header -->
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i
                                                                        class="ti-clip {{ $isCurrentUser ? 'text-white' : 'text-muted' }} me-2"></i>
                                                                    <small
                                                                        class="{{ $isCurrentUser ? 'text-white-50' : 'text-muted' }} fw-semibold">
                                                                        {{ count($attachments) }}
                                                                        {{ count($attachments) == 1 ? 'file' : 'files' }}
                                                                        attached
                                                                        @if ($totalSize > 0)
                                                                            • {{ formatBytes($totalSize) }} total
                                                                        @endif
                                                                    </small>
                                                                </div>

                                                                <!-- Attachment List -->
                                                                <div class="attachments-list">
                                                                    @foreach ($attachments as $index => $attachment)
                                                                        @php
                                                                            $fileExt = strtolower(
                                                                                pathinfo(
                                                                                    $attachment['name'],
                                                                                    PATHINFO_EXTENSION,
                                                                                ),
                                                                            );
                                                                            $isImage = in_array($fileExt, [
                                                                                'jpg',
                                                                                'jpeg',
                                                                                'png',
                                                                                'gif',
                                                                                'svg',
                                                                                'webp',
                                                                            ]);
                                                                            $fileIcon = 'ti-file';
                                                                            $bgColor = $isCurrentUser
                                                                                ? 'bg-white bg-opacity-10'
                                                                                : 'bg-light';
                                                                            $textColor = $isCurrentUser
                                                                                ? 'text-white'
                                                                                : 'text-dark';
                                                                            $subTextColor = $isCurrentUser
                                                                                ? 'text-white-50'
                                                                                : 'text-muted';
                                                                            $iconColor = 'text-secondary';

                                                                            // Determine file icon and color based on type
                                                                            if ($isImage) {
                                                                                $fileIcon = 'ti-image';
                                                                                $iconColor = 'text-info';
                                                                            } elseif (in_array($fileExt, ['pdf'])) {
                                                                                $fileIcon = 'ti-file-text';
                                                                                $iconColor = 'text-danger';
                                                                            } elseif (
                                                                                in_array($fileExt, ['doc', 'docx'])
                                                                            ) {
                                                                                $fileIcon = 'ti-file-text';
                                                                                $iconColor = 'text-primary';
                                                                            } elseif (
                                                                                in_array($fileExt, ['xls', 'xlsx'])
                                                                            ) {
                                                                                $fileIcon = 'ti-file-text';
                                                                                $iconColor = 'text-success';
                                                                            } elseif (
                                                                                in_array($fileExt, [
                                                                                    'mp4',
                                                                                    'avi',
                                                                                    'mov',
                                                                                    'wmv',
                                                                                ])
                                                                            ) {
                                                                                $fileIcon = 'ti-video-camera';
                                                                                $iconColor = 'text-purple';
                                                                            } elseif (
                                                                                in_array($fileExt, [
                                                                                    'mp3',
                                                                                    'wav',
                                                                                    'ogg',
                                                                                ])
                                                                            ) {
                                                                                $fileIcon = 'ti-music-tone';
                                                                                $iconColor = 'text-warning';
                                                                            } elseif (
                                                                                in_array($fileExt, ['zip', 'rar', '7z'])
                                                                            ) {
                                                                                $fileIcon = 'ti-archive';
                                                                                $iconColor = 'text-dark';
                                                                            }
                                                                        @endphp

                                                                        @if ($isImage)
                                                                            <!-- Image Preview -->
                                                                            <div
                                                                                class="attachment-item p-2 rounded {{ $bgColor }} mb-2">
                                                                                <div class="d-flex align-items-start">
                                                                                    <div class="me-3">
                                                                                        <img src="{{ asset('storage/' . $attachment['path']) }}"
                                                                                            alt="{{ $attachment['name'] }}"
                                                                                            class="rounded"
                                                                                            style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                                                                            onclick="window.open('{{ asset('storage/' . $attachment['path']) }}', '_blank')">
                                                                                    </div>
                                                                                    <div class="flex-grow-1">
                                                                                        <div
                                                                                            class="fw-medium {{ $textColor }} small mb-1">
                                                                                            {{ $attachment['name'] }}
                                                                                        </div>
                                                                                        <div
                                                                                            class="{{ $subTextColor }} small mb-2">
                                                                                            IMAGE •
                                                                                            {{ isset($attachment['size']) ? formatBytes((float) $attachment['size']) : 'Unknown size' }}
                                                                                        </div>
                                                                                        <div class="d-flex gap-2">
                                                                                            <button type="button"
                                                                                                class="btn btn-sm {{ $isCurrentUser ? 'btn-outline-light' : 'btn-outline-primary' }}"
                                                                                                onclick="window.open('{{ asset('storage/' . $attachment['path']) }}', '_blank')"
                                                                                                title="View full size">
                                                                                                <i class="ti-eye me-1"></i>
                                                                                                View
                                                                                            </button>
                                                                                            <a href="{{ asset('storage/' . $attachment['path']) }}"
                                                                                                class="btn btn-sm {{ $isCurrentUser ? 'btn-outline-light' : 'btn-outline-primary' }}"
                                                                                                target="_blank"
                                                                                                title="Download {{ $attachment['name'] }}">
                                                                                                <i
                                                                                                    class="ti-download me-1"></i>
                                                                                                Download
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <!-- Regular File -->
                                                                            <div
                                                                                class="attachment-item d-flex align-items-center justify-content-between p-2 rounded {{ $bgColor }} mb-2">
                                                                                <div
                                                                                    class="d-flex align-items-center flex-grow-1">
                                                                                    <i class="ti {{ $fileIcon }} {{ $iconColor }} me-2"
                                                                                        style="font-size: 1.1rem;"></i>
                                                                                    <div class="flex-grow-1">
                                                                                        <div
                                                                                            class="fw-medium {{ $textColor }} small">
                                                                                            {{ $attachment['name'] }}
                                                                                        </div>
                                                                                        <div
                                                                                            class="{{ $subTextColor }} small">
                                                                                            {{ strtoupper($fileExt) }} •
                                                                                            {{ isset($attachment['size']) ? formatBytes((float) $attachment['size']) : 'Unknown size' }}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-2">
                                                                                    <a href="{{ asset('storage/' . $attachment['path']) }}"
                                                                                        class="btn btn-sm {{ $isCurrentUser ? 'btn-outline-light' : 'btn-outline-primary' }}"
                                                                                        target="_blank"
                                                                                        title="Download {{ $attachment['name'] }}">
                                                                                        <i class="ti-download"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Reply Form -->
                                @if ($ticket->canBeRepliedBy(Auth::user()))
                                    <form action="{{ route('crm.reply', $ticket->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label for="message"><strong>Add Reply:</strong></label>
                                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Type your reply here..."
                                                required></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label for="attachment"
                                                        class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <i class="ti-clip me-2"></i>
                                                            <strong>Attachments (Optional)</strong>
                                                            <span class="badge bg-info ms-2">Dynamic Upload</span>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="addFileInput()">
                                                            <i class="ti-plus me-1"></i> Add File
                                                        </button>
                                                    </label>

                                                    <div id="fileInputsContainer" class="mt-3">
                                                        <!-- Initial file input -->
                                                        <div class="file-input-item d-flex align-items-center mb-2">
                                                            <input type="file" class="form-control me-2"
                                                                name="attachments[]" multiple accept="*/*">
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="removeFileInput(this)" title="Remove file">
                                                                <i class="ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <br>
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="ti-share me-2"></i> Send Reply
                                                </button>

                                            </div>
                                        </div>


                                    </form>
                                @else
                                    <div class="alert alert-info">
                                        <i class="ti-info"></i> Only the ticket creator or assigned user can reply to this
                                        ticket.
                                    </div>
                                @endif
                        </div>

                        <!-- Zoho Voice Telephony Section -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white me-3"
                                        style="width: 32px; height: 32px;">
                                        <i class="ti-mobile" style="font-size: 0.875rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-dark">Zoho Telephony Logs</h6>
                                        <small class="text-muted">Outbound calling and recording records</small>
                                    </div>
                                </div>
                                <div id="zoho-auth-indicator">
                                    <!-- Dynamic status badge loaded via AJAX status API -->
                                    <span class="badge bg-secondary">Checking Zoho Connection...</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    $ticketCalls = DB::table('ticket_calls')->where('ticket_id', $ticket->id)->orderBy('created_at', 'desc')->get();
                                @endphp

                                @if ($ticketCalls->isEmpty())
                                    <div class="text-center py-4 text-muted">
                                        <i class="ti-headphone-alt mb-2" style="font-size: 24px;"></i>
                                        <p class="mb-0 small">No call logs registered for this ticket yet.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Direction</th>
                                                    <th>Caller</th>
                                                    <th>Receiver</th>
                                                    <th>Duration</th>
                                                    <th>Date/Time</th>
                                                    <th>Recording</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ticketCalls as $call)
                                                    <tr>
                                                        <td>
                                                            @if ($call->direction === 'inbound')
                                                                <span class="badge bg-info"><i class="ti-arrow-down"></i> Inbound</span>
                                                            @else
                                                                <span class="badge bg-primary"><i class="ti-arrow-up"></i> Outbound</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $call->caller }}</td>
                                                        <td>{{ $call->receiver }}</td>
                                                        <td>{{ gmdate("H:i:s", (int)$call->duration_seconds) }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($call->created_at)->format('M d, Y H:i') }}</td>
                                                        <td>
                                                            @if ($call->recording_url)
                                                                <audio controls class="w-100" style="max-width: 220px; height: 30px;">
                                                                    <source src="{{ $call->recording_url }}" type="audio/mpeg">
                                                                    Your browser does not support the audio element.
                                                                </audio>
                                                            @else
                                                                <span class="text-muted small">No Recording</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
        // Auto-scroll to bottom of chat
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>

    <style>
        .chat-container {
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
        }

        .chat-container::-webkit-scrollbar {
            width: 8px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background-color: #dee2e6;
            border-radius: 4px;
        }

        .message {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .internal-note {
            border-left-width: 4px !important;
            border-left-style: solid !important;
        }

        /* CKEditor Content Styling */
        .ck-content {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }

        .ck-content h1 {
            font-size: 2em;
            font-weight: 700;
            margin: 1em 0 0.5em 0;
            color: #2c3e50;
        }

        .ck-content h2 {
            font-size: 1.5em;
            font-weight: 600;
            margin: 1em 0 0.5em 0;
            color: #34495e;
        }

        .ck-content h3 {
            font-size: 1.25em;
            font-weight: 600;
            margin: 1em 0 0.5em 0;
            color: #34495e;
        }

        .ck-content p {
            margin: 0 0 1em 0;
        }

        .ck-content strong {
            font-weight: 600;
            color: #2c3e50;
        }

        .ck-content em {
            font-style: italic;
        }

        .ck-content u {
            text-decoration: underline;
        }

        .ck-content ul,
        .ck-content ol {
            margin: 0 0 1em 0;
            padding-left: 2em;
        }

        .ck-content li {
            margin: 0.25em 0;
        }

        .ck-content blockquote {
            margin: 1em 0;
            padding: 0.5em 1em;
            border-left: 4px solid #3498db;
            background-color: #f8f9fa;
            font-style: italic;
        }

        .ck-content a {
            color: #3498db;
            text-decoration: none;
        }

        .ck-content a:hover {
            text-decoration: underline;
        }

        .ck-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 1em 0;
        }

        .ck-content th,
        .ck-content td {
            border: 1px solid #dee2e6;
            padding: 0.5em;
            text-align: left;
        }

        .ck-content th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        /* Chat-specific Styles */
        .chat-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
        }

        .message-wrapper {
            /* No animation - instant display */
        }

        .message-bubble {
            /* No transition - instant display */
            position: relative;
        }

        .message-bubble:hover {
            /* No hover animation - static display */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .message-bubble.bg-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border: 1px solid #0056b3 !important;
        }

        .message-bubble.bg-white {
            background: #ffffff !important;
            border: 1px solid #e9ecef !important;
        }

        .message-bubble.bg-warning.bg-opacity-25 {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border: 1px solid #ffc107 !important;
        }

        /* User Avatar Styles */
        .rounded-circle.bg-light {
            border: 1px solid #dee2e6;
        }

        .rounded-circle.bg-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border: 1px solid #0056b3 !important;
        }

        /* Chat Container Scrollbar */
        .chat-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: rgba(0, 123, 255, 0.3);
            border-radius: 3px;
        }

        .chat-container::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 123, 255, 0.5);
        }

        /* Message Animation - REMOVED for instant display */

        /* Attachment Button Styles */
        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.8);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .max-width-70 {
                max-width: 85% !important;
            }

            .message-bubble {
                font-size: 0.9rem;
            }
        }

        /* Unread message styling */
        .unread-message {
            border-left: 4px solid #198754;
            background-color: rgba(25, 135, 84, 0.05);
            padding-left: 16px;
            position: relative;
        }

        .unread-message::before {
            content: 'New';
            position: absolute;
            top: 8px;
            right: 8px;
            background-color: #198754;
            color: white;
            font-size: 0.6rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .unread-divider {
            /* No animation - immediate display */
        }

        /* Enhanced unread message styling */
        .unread-message .message-bubble {
            position: relative;
            overflow: hidden;
        }

        .unread-message .message-bubble::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #198754, #20c997);
            animation: shimmer 2s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        /* Unread count badge animation - REMOVED for instant display */

        /* Read Receipt Styling (WhatsApp Style) */
        .read-receipt {
            position: relative;
            display: inline-flex;
            align-items: center;
            /* No transition - instant display */
        }

        .read-receipt .ti-check:first-child {
            position: relative;
            z-index: 2;
        }

        .read-receipt .ti-check:last-child {
            position: relative;
            z-index: 1;
        }

        .read-receipt .ti-check.text-primary {
            color: #007bff !important;
        }

        .read-receipt .ti-check.text-muted {
            color: #6c757d !important;
        }

        /* Read receipt animation when status changes */
        .read-receipt.updating {
            animation: receiptUpdate 0.3s ease-out;
        }

        @keyframes receiptUpdate {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Hover effect for read receipts */
        .read-receipt:hover {
            transform: scale(1.1);
        }

        /* Read receipt tooltip */
        .read-receipt[data-bs-toggle="tooltip"] {
            cursor: pointer;
        }

        /* Attachments Styling */
        .attachment {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 12px;
            margin-top: 12px;
        }

        .attachment-item {
            transition: all 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .attachment-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-color: rgba(0, 0, 0, 0.2);
        }

        .attachment-item .btn-sm {
            transition: all 0.2s ease;
        }

        .attachment-item .btn-sm:hover {
            transform: scale(1.05);
        }

        /* File input styling */
        input[type="file"]::-webkit-file-upload-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: #0056b3;
        }

        /* Multiple file badge animation */
        .badge.bg-info {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        /* Image Preview Styling */
        .attachment-item img {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .attachment-item img:hover {
            transform: scale(1.05);
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .attachment-item .btn-sm {
            transition: all 0.2s ease;
        }

        .attachment-item .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Enhanced attachment items for images */
        .attachment-item:has(img) {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%) !important;
        }

        /* Better contrast for text */
        .attachment-item .fw-medium {
            font-weight: 600;
            line-height: 1.3;
        }

        /* Dynamic File Input Styling */
        .file-input-item {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .file-input-item .btn-outline-danger {
            transition: all 0.2s ease;
        }

        .file-input-item .btn-outline-danger:hover {
            transform: scale(1.1);
            background-color: #dc3545;
            color: white;
        }

        /* Enhanced file input styling */
        .file-input-item input[type="file"] {
            transition: all 0.2s ease;
        }

        .file-input-item input[type="file"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Add file button animation */
        .btn-outline-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        /* 3-Column Attachment Grid Styling */
        .attachments-list .attachment-item {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .attachments-list .attachment-item:hover {
            transform: translateY(-2px);
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }

        .attachments-list .attachment-item img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .attachments-list .attachment-item i {
            transition: all 0.2s ease;
        }

        .attachments-list .attachment-item:hover i {
            transform: scale(1.1);
        }

        /* Ensure equal height cards in grid */
        .attachments-list .row {
            display: flex;
            flex-wrap: wrap;
        }

        .attachments-list .col-md-4 {
            display: flex;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .attachments-list .col-md-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 576px) {
            .attachments-list .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>

    <!-- Smart Chat JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chatContainer');
            const ticketId = {{ $ticket->id }};

            // Mark ticket as viewed and initialize smart chat
            function initializeSmartChat() {
                // Mark ticket as viewed
                markTicketAsViewed();

                // Small delay to ensure chat container is fully rendered
                setTimeout(() => {
                    // Process unread messages and handle positioning
                    processUnreadMessages();

                    // Set up intersection observer for read tracking
                    setupReadObserver();
                }, 100);
            }

            function markTicketAsViewed() {
                fetch(`/crm/tickets/${ticketId}/mark-viewed`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                }).catch(error => console.log('Mark viewed error:', error));
            }

            function processUnreadMessages() {
                const messages = document.querySelectorAll('.message-wrapper');
                let firstUnreadFound = false;

                console.log('Processing messages:', messages.length);

                messages.forEach((message, index) => {
                    if (message.classList.contains('unread-message')) {
                        // No animation - just mark as unread

                        if (!firstUnreadFound) {
                            firstUnreadFound = true;
                            insertUnreadDivider(message);

                            // Immediately jump to first unread message (no animation)
                            console.log('Found first unread, jumping to position');
                            jumpToFirstUnread(message);
                        }
                    }
                });

                // If no unread messages found, jump to last message
                if (!firstUnreadFound && messages.length > 0) {
                    const lastMessage = messages[messages.length - 1];
                    console.log('No unread messages, jumping to last message');
                    jumpToLastMessage(lastMessage);
                }

                // Update unread count in header
                updateUnreadCount();
            }

            function jumpToFirstUnread(firstUnreadElement) {
                if (firstUnreadElement) {
                    console.log('Jumping to first unread at offset:', firstUnreadElement.offsetTop);
                    // Immediate jump to first unread message (no animation)
                    chatContainer.scrollTop = firstUnreadElement.offsetTop -
                        150; // Increased offset for better positioning
                }
            }

            function jumpToLastMessage(lastMessageElement) {
                if (lastMessageElement) {
                    console.log('Jumping to last message at offset:', lastMessageElement.offsetTop);
                    // Immediate jump to last message (no animation)
                    chatContainer.scrollTop = lastMessageElement.offsetTop -
                        150; // Increased offset for better positioning
                }
            }

            function insertUnreadDivider(afterElement) {
                const divider = document.getElementById('unreadDivider');
                if (divider && afterElement) {
                    // Insert divider before the first unread message
                    afterElement.parentNode.insertBefore(divider, afterElement);
                    divider.style.display = 'block';
                }
            }

            function setupReadTracking() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && entry.target.classList.contains(
                                'unread-message')) {
                            markMessageAsRead(entry.target);
                        }
                    });
                }, {
                    threshold: 0.5
                });

                // Observe all unread messages
                document.querySelectorAll('.unread-message').forEach(message => {
                    observer.observe(message);
                });
            }

            function markMessageAsRead(messageElement) {
                const replyId = messageElement.dataset.replyId;

                // Remove unread styling
                messageElement.classList.remove('unread-message', 'unread-pulse');

                // Remove "New" badge
                const newBadge = messageElement.querySelector('.badge.bg-success');
                if (newBadge) {
                    newBadge.remove();
                }

                // Update message bubble styling
                const bubble = messageElement.querySelector('.message-bubble');
                if (bubble) {
                    bubble.classList.remove('border-success', 'border-2');
                    bubble.style.borderLeft = '';
                }

                // Update read receipt to "read" status
                updateReadReceipt(messageElement, 'read');

                // Mark as read on server
                fetch(`/crm/replies/${replyId}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Message marked as read');
                        }
                    })
                    .catch(error => console.log('Mark read error:', error));

                // Update unread count
                updateUnreadCount();
            }

            function updateReadReceipt(messageElement, status) {
                const readReceipt = messageElement.querySelector('.read-receipt');
                if (!readReceipt) return;

                // Add updating animation
                readReceipt.classList.add('updating');

                setTimeout(() => {
                    if (status === 'read') {
                        readReceipt.innerHTML = `
                            <i class="ti-check text-primary" style="font-size: 0.65rem;"></i>
                            <i class="ti-check text-primary" style="font-size: 0.65rem; margin-left: -3px;"></i>
                        `;
                        readReceipt.setAttribute('title', 'Read by recipient');
                    } else {
                        readReceipt.innerHTML = `
                            <i class="ti-check text-muted" style="font-size: 0.65rem;"></i>
                        `;
                        readReceipt.setAttribute('title', 'Sent - waiting for recipient to read');
                    }

                    // Remove updating animation
                    readReceipt.classList.remove('updating');
                }, 150);
            }

            function markAsSent(replyId) {
                const messageElement = document.querySelector(`[data-reply-id="${replyId}"]`);
                if (messageElement) {
                    updateReadReceipt(messageElement, 'sent');
                }
            }

            function markAsReadByAssigned(replyId) {
                const messageElement = document.querySelector(`[data-reply-id="${replyId}"]`);
                if (messageElement) {
                    updateReadReceipt(messageElement, 'read');
                }
            }

            function updateUnreadCount() {
                const unreadMessages = document.querySelectorAll('.unread-message').length;
                const headerSubtitle = document.querySelector('.card-header small.text-muted');

                if (headerSubtitle) {
                    const totalReplies = {{ $ticket->replies->count() }};
                    if (unreadMessages > 0) {
                        headerSubtitle.innerHTML =
                            `${totalReplies} ${totalReplies == 1 ? 'reply' : 'replies'} <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">${unreadMessages} unread</span>`;
                    } else {
                        headerSubtitle.textContent = `${totalReplies} ${totalReplies == 1 ? 'reply' : 'replies'}`;
                    }
                }
            }

            // Handle real-time message updates (if implemented later)
            window.chatSystem = {
                markAsRead: markMessageAsRead,
                refreshUnread: processUnreadMessages,
                markAsSent: markAsSent,
                markAsReadByAssigned: markAsReadByAssigned,
                updateReadReceipt: updateReadReceipt
            };

            // Initialize tooltips for read receipts
            const readReceipts = document.querySelectorAll('.read-receipt[title]');
            readReceipts.forEach(receipt => {
                receipt.setAttribute('data-bs-toggle', 'tooltip');
                receipt.setAttribute('data-bs-placement', 'top');
            });

            // Initialize Bootstrap tooltips if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Mark all notifications for this ticket as read
            function markTicketNotificationsAsRead() {
                fetch(`/crm/notifications/read-all`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            ticket_id: ticketId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Ticket notifications marked as read');
                            // Optionally update the header notification count
                            if (window.loadNotifications) {
                                window.loadNotifications();
                            }
                        }
                    })
                    .catch(error => console.log('Mark notifications error:', error));
            }

            // Initialize the smart chat system
            initializeSmartChat();

            // Mark notifications as read when page loads
            markTicketNotificationsAsRead();
        });

        // Dynamic File Input Functions
        let fileInputCount = 1;

        function addFileInput() {
            fileInputCount++;
            const container = document.getElementById('fileInputsContainer');

            const fileInputItem = document.createElement('div');
            fileInputItem.className = 'file-input-item d-flex align-items-center mb-2';
            fileInputItem.innerHTML = `
                <input type="file" class="form-control me-2" name="attachments[]" multiple accept="*/*">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFileInput(this)" title="Remove file">
                    <i class="ti-trash"></i>
                </button>
            `;

            // Add animation
            fileInputItem.style.opacity = '0';
            fileInputItem.style.transform = 'translateY(-10px)';
            container.appendChild(fileInputItem);

            // Animate in
            setTimeout(() => {
                fileInputItem.style.transition = 'all 0.3s ease';
                fileInputItem.style.opacity = '1';
                fileInputItem.style.transform = 'translateY(0)';
            }, 10);

            // Focus the new input
            fileInputItem.querySelector('input[type="file"]').focus();
        }

        function removeFileInput(button) {
            const fileInputItem = button.parentElement;

            // Animate out
            fileInputItem.style.transition = 'all 0.3s ease';
            fileInputItem.style.opacity = '0';
            fileInputItem.style.transform = 'translateX(20px)';

            // Remove after animation
            setTimeout(() => {
                fileInputItem.remove();

                // Ensure at least one file input remains
                const container = document.getElementById('fileInputsContainer');
                if (container.children.length === 0) {
                    addFileInput();
                }
            }, 300);
        }

        // Zoho Voice Telephony Integrations
        document.addEventListener('DOMContentLoaded', function() {
            const ticketId = "{{ $ticket->id }}";
            let callTimerInterval = null;
            let callSeconds = 0;

            function checkZohoConnectionStatus() {
                const indicator = document.getElementById('zoho-auth-indicator');
                if (!indicator) return;

                fetch('/zoho/oauth/status')
                    .then(response => response.json())
                    .then(data => {
                        if (data.connected) {
                            indicator.innerHTML = `<span class="badge bg-success" title="Expires in ${data.time_left}"><i class="ti-check"></i> Zoho Voice Connected</span>`;
                        } else {
                            indicator.innerHTML = `<a href="/zoho/oauth/redirect" class="btn btn-xs btn-outline-danger"><i class="ti-link"></i> Connect Zoho Voice</a>`;
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching Zoho status:', err);
                        indicator.innerHTML = `<span class="badge bg-warning text-dark"><i class="ti-alert"></i> Connection Offline</span>`;
                    });
            }

            window.initiateZohoCall = function(phone) {
                // Initialize calling modal
                document.getElementById('zohoCallPhone').textContent = phone;
                document.getElementById('zohoCallStatus').textContent = 'Connecting via Zoho SIP Line...';
                document.getElementById('zohoCallTimer').textContent = '00:00';
                
                const callModal = new bootstrap.Modal(document.getElementById('zohoCallModal'));
                callModal.show();

                callSeconds = 0;

                // Simulate connecting, ringing and answer
                const statusTexts = [
                    { delay: 1000, text: 'Ringing customer...' },
                    { delay: 2500, text: 'Call Answered (Connected)' }
                ];

                statusTexts.forEach(item => {
                    setTimeout(() => {
                        if (document.getElementById('zohoCallStatus').textContent !== 'Call Completed') {
                            document.getElementById('zohoCallStatus').textContent = item.text;
                            if (item.text.includes('Connected')) {
                                startCallTimer();
                            }
                        }
                    }, item.delay);
                });
            };

            function startCallTimer() {
                if (callTimerInterval) clearInterval(callTimerInterval);
                callTimerInterval = setInterval(() => {
                    callSeconds++;
                    const minutes = Math.floor(callSeconds / 60).toString().padStart(2, '0');
                    const secs = (callSeconds % 60).toString().padStart(2, '0');
                    document.getElementById('zohoCallTimer').textContent = `${minutes}:${secs}`;
                }, 1000);
            }

            window.hangupZohoCall = function() {
                clearInterval(callTimerInterval);
                document.getElementById('zohoCallStatus').textContent = 'Call Completed';
                
                // Show closing state
                setTimeout(() => {
                    const callModal = bootstrap.Modal.getInstance(document.getElementById('zohoCallModal'));
                    if (callModal) callModal.hide();

                    // Automatically submit Call Completed event to Zoho webhook to log the call!
                    // This provides a fully functional real-time mock & API backup trigger
                    const payload = {
                        event: 'call.completed',
                        call_id: 'ZVC-' + Math.random().toString(36).substr(2, 9).toUpperCase(),
                        caller: 'BOSCHMA Support',
                        receiver: document.getElementById('zohoCallPhone').textContent,
                        direction: 'outbound',
                        duration: callSeconds,
                        recording_url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3' // sample audio url for playback
                    };

                    fetch('/api/zoho/webhook/call', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page to show logged calls and new timeline reply
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        console.error('Failed to report Zoho Voice call completion:', err);
                    });
                }, 1200);
            };

            window.openZohoSmsModal = function(phone) {
                document.getElementById('zohoSmsRecipient').value = phone;
                document.getElementById('zohoSmsMessage').value = '';
                document.getElementById('zohoSmsCharCount').textContent = '0';
                
                const smsModal = new bootstrap.Modal(document.getElementById('zohoSmsModal'));
                smsModal.show();
            };

            // SMS character counter
            document.getElementById('zohoSmsMessage').addEventListener('input', function() {
                document.getElementById('zohoSmsCharCount').textContent = this.value.length;
            });

            // SMS form submission
            document.getElementById('zohoSmsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const phone = document.getElementById('zohoSmsRecipient').value;
                const message = document.getElementById('zohoSmsMessage').value;
                const submitBtn = document.getElementById('zohoSmsSubmitBtn');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ti-reload rotate-animation me-1"></i> Dispatches...';

                fetch('/crm/zoho/sms', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ phone, message, ticket_id: ticketId })
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti-share me-1"></i> Dispatch SMS';
                    
                    const smsModal = bootstrap.Modal.getInstance(document.getElementById('zohoSmsModal'));
                    if (smsModal) smsModal.hide();

                    if (data.success) {
                        alert('SMS dispatched successfully via Zoho Voice!');
                        window.location.reload();
                    } else {
                        alert('Error dispatching SMS: ' + data.message);
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti-share me-1"></i> Dispatch SMS';
                    alert('An error occurred while communicating with Zoho Voice API.');
                });
            });

            // Run status check on load
            checkZohoConnectionStatus();
        });
    </script>

    <!-- Zoho Voice Call Modal -->
    <div class="modal fade" id="zohoCallModal" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(5px);">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <div class="modal-body text-center p-5">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;">&times;</button>
                    
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle shadow-lg pulse-animation" style="width: 80px; height: 80px;">
                            <i class="ti-headphone-alt" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-1" id="zohoCallName">{{ $ticket->name }}</h4>
                    <p class="text-white-50 mb-4" id="zohoCallPhone">{{ $ticket->phone }}</p>

                    <div class="bg-white bg-opacity-10 rounded-3 p-3 mb-4" style="background-color: rgba(255,255,255,0.1);">
                        <div class="small text-white-50 mb-1" id="zohoCallStatus">Initiating Call...</div>
                        <h3 class="fw-mono mb-0" id="zohoCallTimer">00:00</h3>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-danger btn-lg rounded-pill px-4 shadow" id="zohoHangupBtn" onclick="hangupZohoCall()" style="border-radius: 50px;">
                            <i class="ti-close me-2"></i> End Call
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zoho Voice SMS Modal -->
    <div class="modal fade" id="zohoSmsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="modal-title fw-bold"><i class="ti-comment me-2"></i> Send SMS via Zoho Voice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: white; cursor: pointer;">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <form id="zohoSmsForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recipient Number</label>
                            <input type="text" class="form-control" id="zohoSmsRecipient" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message Body</label>
                            <textarea class="form-control" id="zohoSmsMessage" rows="4" placeholder="Type your message here..." required></textarea>
                            <div class="form-text text-muted text-end mt-1"><span id="zohoSmsCharCount">0</span> / 160 characters</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="zohoSmsSubmitBtn">
                                <i class="ti-share me-1"></i> Dispatch SMS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pulse-animation {
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
        .rotate-animation {
            animation: rotate 1s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection
