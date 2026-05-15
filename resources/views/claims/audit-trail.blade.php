@extends('layouts.app')

@section('title', 'Claim Audit Trail - ' . $claim->authorization_code)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Claim Audit Trail</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('claims.index') }}">Claims</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('claims.show', $claim->id) }}">{{ $claim->authorization_code }}</a>
                                </li>
                                <li class="breadcrumb-item active">Audit Trail</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('claims.show', $claim->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Claim
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claim Summary Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Claim Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Authorization Code:</strong><br>
                                {{ $claim->authorization_code }}
                            </div>
                            <div class="col-md-3">
                                <strong>Beneficiary:</strong><br>
                                {{ $claim->beneficiary_name }}
                            </div>
                            <div class="col-md-3">
                                <strong>Claim Amount:</strong><br>
                                ₦{{ number_format($claim->claim_amount, 2) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                <span
                                    class="badge bg-{{ $claim->status == 'approved' ? 'success' : ($claim->status == 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Audit Note -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-info">Add Audit Note</h6>
                    </div>
                    <div class="card-body">
                        <form id="auditNoteForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-10">
                                    <textarea name="note" id="auditNote" class="form-control" rows="2" placeholder="Enter audit note..." required></textarea>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-1"></i>Add Note
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit History -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Audit History</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @forelse($histories as $history)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ getTimelineColor($history->action) }}">
                                        <i class="{{ getTimelineIcon($history->action) }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                                </h6>
                                                <p class="text-muted mb-1">{{ $history->description }}</p>
                                                <small class="text-muted">
                                                    By: {{ $history->user ? $history->user->fullname : 'System' }}
                                                    @if ($history->ip_address)
                                                        • IP: {{ $history->ip_address }}
                                                    @endif
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                {{ $history->created_at->format('M d, Y H:i:s') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p>No audit history available for this claim.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claim Notes -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Claim Notes</h6>
                    </div>
                    <div class="card-body">
                        <div id="notesList">
                            @forelse($notes as $note)
                                <div class="note-item border-start border-4 border-info ps-3 mb-3"
                                    data-note-id="{{ $note->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1">{{ $note->note }}</p>
                                            <small class="text-muted">
                                                By: {{ $note->user ? $note->user->fullname : 'System' }}
                                                • {{ $note->created_at->format('M d, Y H:i:s') }}
                                            </small>
                                        </div>
                                        @if ($note->type === 'audit')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeNote({{ $note->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-sticky-note fa-2x mb-2"></i>
                                    <p>No notes available for this claim.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-marker {
            position: absolute;
            left: -23px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #dee2e6;
        }

        .note-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .bg-created {
            background-color: #6c757d;
        }

        .bg-updated {
            background-color: #17a2b8;
        }

        .bg-approved {
            background-color: #28a745;
        }

        .bg-rejected {
            background-color: #dc3545;
        }

        .bg-paid {
            background-color: #007bff;
        }

        .bg-ro_approved {
            background-color: #ffc107;
        }

        .bg-ro_rejected {
            background-color: #fd7e14;
        }

        .bg-e5_approved {
            background-color: #20c997;
        }

        .bg-e5_rejected {
            background-color: #e83e8c;
        }

        .bg-audit_note_added {
            background-color: #6f42c1;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#auditNoteForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route('claims.audit.add-note', $claim->id) }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Add new note to the list
                            const noteHtml = `
                        <div class="note-item border-start border-4 border-info ps-3 mb-3" data-note-id="${response.note.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-1">${response.note.note}</p>
                                    <small class="text-muted">
                                        By: ${response.note.user}
                                        • ${response.note.created_at}
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNote(${response.note.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;

                            $('#notesList').prepend(noteHtml);
                            $('#auditNote').val('');

                            // Show success message
                            showAlert('Audit note added successfully!', 'success');
                        } else {
                            showAlert(response.message || 'Error adding audit note', 'error');
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON ? xhr.responseJSON.message :
                            'Error adding audit note';
                        showAlert(error, 'error');
                    }
                });
            });
        });

        function removeNote(noteId) {
            if (confirm('Are you sure you want to remove this note?')) {
                // In a real implementation, you would make an AJAX call to remove the note
                $(`[data-note-id="${noteId}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
            }
        }

        function showAlert(message, type) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

            $('.card-body').first().prepend(alertHtml);

            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }

        // Helper functions for timeline styling (these should be defined in PHP or available globally)
        function getTimelineColor(action) {
            const colorMap = {
                'created': 'created',
                'updated': 'updated',
                'approved': 'approved',
                'rejected': 'rejected',
                'paid': 'paid',
                'ro_approved': 'ro_approved',
                'ro_rejected': 'ro_rejected',
                'e5_approved': 'e5_approved',
                'e5_rejected': 'e5_rejected',
                'audit_note_added': 'audit_note_added'
            };
            return colorMap[action] || 'secondary';
        }

        function getTimelineIcon(action) {
            const iconMap = {
                'created': 'fas fa-plus',
                'updated': 'fas fa-edit',
                'approved': 'fas fa-check',
                'rejected': 'fas fa-times',
                'paid': 'fas fa-dollar-sign',
                'ro_approved': 'fas fa-user-check',
                'ro_rejected': 'fas fa-user-times',
                'e5_approved': 'fas fa-shield-check',
                'e5_rejected': 'fas fa-shield-times',
                'audit_note_added': 'fas fa-sticky-note'
            };
            return iconMap[action] || 'fas fa-circle';
        }
    </script>
@endpush
