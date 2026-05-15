@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title mb-1">Notifications Center</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Notifications</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary" onclick="markAllRead()">
                            <i class="fas fa-check-double me-1"></i>Mark All Read
                        </button>
                        <a href="{{ route('claims.alerts') }}" class="btn btn-warning ms-2">
                            <i class="fas fa-bell me-1"></i>Alerts Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Stats -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Notifications
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($notifications->total()) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bell fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unread</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($unreadCount) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-envelope fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Read</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($notifications->total() - $unreadCount) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-envelope-open fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Recent Notifications
                            @if ($unreadCount > 0)
                                <span class="badge bg-warning ms-2">{{ $unreadCount }} unread</span>
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($notifications as $notification)
                            <div class="notification-item {{ $notification->read_at ? 'read' : 'unread' }} border-bottom pb-3 mb-3"
                                data-notification-id="{{ $notification->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i
                                                class="{{ getNotificationIcon($notification->type) }} me-2 text-{{ getNotificationColor($notification->type) }}"></i>
                                            <h6 class="mb-0">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                            @if (!$notification->read_at)
                                                <span class="badge bg-warning ms-2">New</span>
                                            @endif
                                        </div>
                                        <p class="text-muted mb-2">
                                            {{ $notification->data['message'] ?? ($notification->data['description'] ?? 'No message') }}
                                        </p>
                                        <small class="text-muted">
                                            {{ $notification->created_at->format('M d, Y H:i:s') }}
                                            @if ($notification->read_at)
                                                • Read {{ $notification->read_at->diffForHumans() }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="notification-actions ms-3">
                                        @if (!$notification->read_at)
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="markAsRead({{ $notification->id }})" title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteNotification({{ $notification->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-bell-slash fa-3x mb-3"></i>
                                <h5>No Notifications</h5>
                                <p>You don't have any notifications at this time.</p>
                            </div>
                        @endforelse

                        <!-- Pagination -->
                        @if ($notifications->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $notifications->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-xs {
            font-size: 0.7rem;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .notification-item {
            transition: background-color 0.2s ease;
        }

        .notification-item.unread {
            background-color: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding-left: 15px;
        }

        .notification-item.read {
            opacity: 0.8;
        }

        .notification-item:hover {
            background-color: #f1f3f9;
        }

        .notification-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .notification-item:hover .notification-actions {
            opacity: 1;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-refresh notification count every 30 seconds
            setInterval(updateNotificationCount, 30000);
        });

        function markAsRead(notificationId) {
            $.ajax({
                url: `{{ route('claims.notifications.read', ':id') }}`.replace(':id', notificationId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        const item = $(`[data-notification-id="${notificationId}"]`);
                        item.removeClass('unread').addClass('read');
                        item.find('.badge.bg-warning').remove();
                        item.find('.btn-outline-primary').remove();

                        showAlert('Notification marked as read', 'success');
                        updateNotificationCount();
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON ? xhr.responseJSON.message :
                        'Error marking notification as read';
                    showAlert(error, 'error');
                }
            });
        }

        function markAllRead() {
            if (!confirm('Mark all notifications as read?')) {
                return;
            }

            $.ajax({
                url: '{{ route('claims.notifications.read-all') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('.notification-item').removeClass('unread').addClass('read');
                        $('.notification-item .badge.bg-warning').remove();
                        $('.notification-item .btn-outline-primary').remove();
                        $('.badge.bg-warning').remove();

                        showAlert('All notifications marked as read', 'success');
                        updateNotificationCount();
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON ? xhr.responseJSON.message :
                        'Error marking notifications as read';
                    showAlert(error, 'error');
                }
            });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) {
                return;
            }

            $.ajax({
                url: `{{ route('claims.notifications.delete', ':id') }}`.replace(':id', notificationId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $(`[data-notification-id="${notificationId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });

                        showAlert('Notification deleted', 'success');
                        updateNotificationCount();
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting notification';
                    showAlert(error, 'error');
                }
            });
        }

        function updateNotificationCount() {
            $.ajax({
                url: '{{ route('claims.notifications.count') }}',
                type: 'GET',
                success: function(response) {
                    // Update header notification badge if it exists
                    const badge = $('.notification-badge');
                    if (badge.length) {
                        if (response.count > 0) {
                            badge.text(response.count).show();
                        } else {
                            badge.hide();
                        }
                    }
                }
            });
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

        // Helper functions for notification styling
        function getNotificationIcon(type) {
            const iconMap = {
                'claim_created': 'fas fa-plus-circle',
                'claim_approved': 'fas fa-check-circle',
                'claim_rejected': 'fas fa-times-circle',
                'ro_approved': 'fas fa-user-check',
                'ro_rejected': 'fas fa-user-times',
                'e5_approved': 'fas fa-shield-check',
                'e5_rejected': 'fas fa-shield-times',
                'bulk_upload': 'fas fa-upload',
                'audit_note': 'fas fa-sticky-note'
            };
            return iconMap[type] || 'fas fa-bell';
        }

        function getNotificationColor(type) {
            const colorMap = {
                'claim_created': 'primary',
                'claim_approved': 'success',
                'claim_rejected': 'danger',
                'ro_approved': 'warning',
                'ro_rejected': 'danger',
                'e5_approved': 'success',
                'e5_rejected': 'danger',
                'bulk_upload': 'info',
                'audit_note': 'secondary'
            };
            return colorMap[type] || 'primary';
        }
    </script>
@endpush
