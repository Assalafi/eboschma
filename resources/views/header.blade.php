<div class="main-container container-fluid bg-white">
    <div class="main-header-left bg-white">
        <a class="main-header-menu-icon text-dark" href="javascript:void(0)" id="mainSidebarToggle"><span
                class="bg-dark"></span></a>
        <div class="hor-logo bg-white">
            <a class="main-logo" href="/">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img desktop-logo" alt="logo">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img desktop-logo-light" alt="logo">
            </a>
        </div>
    </div>

    <!-- Header Title -->
    <div class="d-flex align-items-center flex-grow-1 ms-3">
        <div class="header-title-wrapper">
            <!-- Mobile: Show only BOSCHMA -->
            <h1 class="header-title mb-0 d-md-none"
                style="color: #01542B; font-size: 18px; font-weight: 700; line-height: 1.2; text-transform: uppercase; letter-spacing: 0.3px;">
                BOSCHMA
            </h1>

            <!-- Desktop: Show full name -->
            <h1 class="header-title mb-0 d-none d-md-block"
                style="color: #01542B; font-size: 18px; font-weight: 700; line-height: 1.2; text-transform: uppercase; letter-spacing: 0.3px;">
                BORNO STATE CONTRIBUTORY HEALTH CARE MANAGEMENT AGENCY
            </h1>
            <p class="header-subtitle mb-0 d-none d-md-block"
                style="color: #01542B; font-size: 14px; font-weight: 500; margin-top: 2px;">
                Management Portal
            </p>
        </div>
    </div>

    <div class="main-header-right">
        <button class="navbar-toggler navresponsive-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="fe fe-more-vertical header-icons navbar-toggler-icon"></i>
        </button><!-- Navresponsive closed -->
        <div class="navbar navbar-expand-lg nav nav-item navbar-nav-right responsive-navbar navbar-dark">
            <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                <div class="d-flex order-lg-2 ms-auto">
                    <!-- Search -->
                    <div class="dropdown header-search">
                        <a class="nav-link icon header-search">
                            <i class="fe fe-search header-icons"></i>
                        </a>
                        <div class="dropdown-menu">
                            <div class="main-form-search p-2">
                                <div class="input-group">
                                    <input type="search" class="form-control" placeholder="Search for anything...">
                                    <button class="btn search-btn"><svg xmlns="http://www.w3.org/2000/svg"
                                            width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-search">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Search -->
                    <!-- Theme-Layout -->
                    <div class="dropdown d-flex main-header-theme">
                        <a class="nav-link icon layout-setting">
                            <span class="dark-layout">
                                <i class="fe fe-sun header-icons"></i>
                            </span>
                            <span class="light-layout">
                                <i class="fe fe-moon header-icons"></i>
                            </span>
                        </a>
                    </div>
                    <!-- Theme-Layout -->
                    <!-- Full screen -->
                    <div class="dropdown ">
                        <a class="nav-link icon full-screen-link">
                            <i class="fe fe-maximize fullscreen-button fullscreen header-icons"></i>
                            <i class="fe fe-minimize fullscreen-button exit-fullscreen header-icons"></i>
                        </a>
                    </div>
                    <!-- Full screen -->
                    <!-- Notification -->
                    <div class="dropdown main-header-notification">
                        <a class="nav-link icon" href="javascript:void(0)" onclick="toggleNotifications()">
                            <i class="fe fe-bell header-icons"></i>
                            <span class="badge bg-danger nav-link-badge" id="notificationCount">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end"
                            style="width: 350px; max-height: 400px; overflow-y: auto; display: none;">
                            <div class="header-navheading">
                                <p class="main-notification-text">
                                    <span id="notificationText">You have 0 unread notifications</span>
                                    <span class="badge bg-pill bg-primary ms-3" style="cursor: pointer;"
                                        onclick="viewAllNotifications()">View all</span>
                                </p>
                            </div>
                            <div class="main-notification-list" id="notificationList">
                                <div class="text-center py-3">
                                    <i class="fe fe-bell" style="font-size: 2rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No new notifications</p>
                                </div>
                            </div>
                            <div class="dropdown-footer">
                                <a href="javascript:void(0)" onclick="markAllAsRead()">Mark all as read</a>
                            </div>
                        </div>
                    </div>
                    <!-- Notification -->

                    <script>
                        // Notification System
                        let notifications = [];
                        let notificationInterval;

                        function loadNotifications() {
                            fetch('/crm/notifications/api', {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        notifications = data.notifications;
                                        updateNotificationDisplay();
                                    }
                                })
                                .catch(error => console.log('Notification load error:', error));
                        }

                        function updateNotificationDisplay() {
                            const countElement = document.getElementById('notificationCount');
                            const textElement = document.getElementById('notificationText');
                            const listElement = document.getElementById('notificationList');

                            const unreadCount = notifications.filter(n => !n.read).length;

                            // Update badge
                            countElement.textContent = unreadCount;
                            countElement.style.display = unreadCount > 0 ? 'inline-block' : 'none';

                            // Update text
                            if (unreadCount === 0) {
                                textElement.textContent = 'No new notifications';
                            } else if (unreadCount === 1) {
                                textElement.textContent = 'You have 1 unread notification';
                            } else {
                                textElement.textContent = `You have ${unreadCount} unread notifications`;
                            }

                            // Update list
                            if (notifications.length === 0) {
                                listElement.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fe fe-bell" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No notifications</p>
                    </div>
                `;
                            } else {
                                listElement.innerHTML = notifications.map(notification => `
                    <div class="dropdown-item ${!notification.read ? 'bg-light' : ''}" style="border-bottom: 1px solid #eee; padding: 12px 16px; cursor: pointer;" onclick="handleNotificationClick(${notification.id}, '${notification.ticket_id}', '${notification.type}')">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="rounded-circle bg-${notification.type === 'reply' ? 'primary' : 'info'} text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <i class="fe fe-${notification.type === 'reply' ? 'message-square' : 'bell'}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1" style="font-size: 0.9rem; font-weight: 600;">${notification.title}</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">${notification.time}</small>
                                </div>
                                <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.3;">${notification.message}</p>
                                ${!notification.read ? '<span class="badge bg-primary" style="font-size: 0.6rem;">New</span>' : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
                            }
                        }

                        function handleNotificationClick(notificationId, ticketId, type) {
                            // Mark as read
                            markNotificationAsRead(notificationId);

                            // Navigate to ticket
                            window.location.href = `/crm/${ticketId}`;
                        }

                        function markNotificationAsRead(notificationId) {
                            fetch(`/crm/notifications/${notificationId}/read`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update local notification
                                        const notification = notifications.find(n => n.id === notificationId);
                                        if (notification) {
                                            notification.read = true;
                                            updateNotificationDisplay();
                                        }
                                    }
                                })
                                .catch(error => console.log('Mark read error:', error));
                        }

                        function markAllAsRead() {
                            const unreadNotifications = notifications.filter(n => !n.read);
                            if (unreadNotifications.length === 0) return;

                            fetch('/crm/notifications/read-all', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Mark all as read locally
                                        notifications.forEach(n => n.read = true);
                                        updateNotificationDisplay();
                                    }
                                })
                                .catch(error => console.log('Mark all read error:', error));
                        }

                        function viewAllNotifications() {
                            window.location.href = '/crm';
                        }

                        function toggleNotifications() {
                            const dropdown = document.querySelector('.main-header-notification .dropdown-menu');
                            if (dropdown.style.display === 'block') {
                                dropdown.style.display = 'none';
                            } else {
                                dropdown.style.display = 'block';
                                loadNotifications(); // Refresh when opening
                            }
                        }

                        // Auto-refresh notifications every 30 seconds
                        notificationInterval = setInterval(loadNotifications, 30000);

                        // Load notifications on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(loadNotifications, 1000);
                        });

                        // Clean up interval when page unloads
                        window.addEventListener('beforeunload', function() {
                            if (notificationInterval) {
                                clearInterval(notificationInterval);
                            }
                        });
                    </script>
                    <!-- Profile -->
                    <div class="dropdown main-profile-menu">
                        <a class="d-flex" href="javascript:void(0)">
                            <span class="main-img-user"><img alt="avatar"
                                    src="{{ url('assets/img/brand/logo.png') }}"></span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="header-navheading">
                                <h6 class="main-notification-title">{{ session('user_email') ?? 'Admin User' }}</h6>
                            </div>
                            <a class="dropdown-item border-top" href="#">
                                <i class="fe fe-user"></i> My Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fe fe-edit"></i> Edit Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fe fe-settings"></i> Account Settings
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fe fe-settings"></i> Support
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fe fe-compass"></i> Activity
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fe fe-power"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Profile -->
                </div>
            </div>
        </div>
    </div>
</div>
