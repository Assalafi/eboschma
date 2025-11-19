<div class="container-fluid pt-3 pb-5">
    <!-- Modern Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="logo-circle">
                            <img src="{{ asset('assets/img/brand/logo.png') }}" alt="BOSCHMA"
                                onerror="this.src='{{ asset('assets/img/brand/favicon.png') }}'">
                        </div>
                        <div class="header-text">
                            <h2 class="mb-1">Welcome to BOSCHMA</h2>
                            <p class="text-white-75 mb-0">
                                <i class="fe fe-calendar me-2"></i>{{ date('l, F d, Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="admin-info">
                            <span class="admin-name">{{ Auth::user()->name ?? 'Admin' }}</span>
                            <span class="admin-role">System Administrator</span>
                        </div>
                    </div>
                </div>
                <div class="header-pattern"></div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fe fe-users"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total Beneficiaries</span>
                    <h3 class="stat-number counter">{{ number_format($totalBeneficiaries ?? 0) }}</h3>
                    <span class="stat-trend up"><i class="fe fe-trending-up"></i> 12% vs last month</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="fe fe-user-plus"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">New This Month</span>
                    <h3 class="stat-number counter">{{ number_format($newRegistrations ?? 0) }}</h3>
                    <span class="stat-trend up"><i class="fe fe-trending-up"></i> 8% increase</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fe fe-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Active Status</span>
                    <h3 class="stat-number counter">{{ number_format(($totalBeneficiaries ?? 0) - 50) }}</h3>
                    <span class="stat-trend up"><i class="fe fe-trending-up"></i> 95% active</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="fe fe-file-text"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Documents</span>
                    <h3 class="stat-number counter">{{ number_format(($totalBeneficiaries ?? 0) * 3) }}</h3>
                    <span class="stat-trend neutral"><i class="fe fe-minus"></i> Processed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('beneficiaries.create') }}" class="action-card action-card-primary">
                <div class="action-icon">
                    <i class="fe fe-user-plus"></i>
                </div>
                <div class="action-content">
                    <h5>Register New</h5>
                    <p>Add new beneficiary to system</p>
                </div>
                <i class="fe fe-arrow-right action-arrow"></i>
            </a>
        </div>
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('beneficiaries.index') }}" class="action-card action-card-success">
                <div class="action-icon">
                    <i class="fe fe-list"></i>
                </div>
                <div class="action-content">
                    <h5>View All</h5>
                    <p>Browse beneficiary records</p>
                </div>
                <i class="fe fe-arrow-right action-arrow"></i>
            </a>
        </div>
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('reports.index') }}" class="action-card action-card-info">
                <div class="action-icon">
                    <i class="fe fe-file-text"></i>
                </div>
                <div class="action-content">
                    <h5>Reports</h5>
                    <p>Generate system reports</p>
                </div>
                <i class="fe fe-arrow-right action-arrow"></i>
            </a>
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="row">
        <div class="col-12">
            <div class="timeline-card">
                <div class="timeline-header">
                    <h5 class="mb-0"><i class="fe fe-calendar me-2"></i>2025 Activities Timeline</h5>
                    <span class="timeline-badge">Planning Phase</span>
                </div>
                <div class="timeline-body">
                    <div class="modern-timeline">
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Beneficiary Registration</h6>
                                <p class="text-muted mb-2">Jan 15 - Feb 28, 2025</p>
                                <span class="timeline-status active">In Progress</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Mid-Year Review</h6>
                                <p class="text-muted mb-2">Jun 1 - Jun 30, 2025</p>
                                <span class="timeline-status pending">Pending</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Annual Review</h6>
                                <p class="text-muted mb-2">Dec 1 - Dec 20, 2025</p>
                                <span class="timeline-status pending">Scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
   BOSCHMA DASHBOARD - MODERN DESIGN
   Primary Color: #016634 (Dark Green)
   ============================================ */

    :root {
        --primary-color: #016634;
        --primary-dark: #014525;
        --primary-light: #02803f;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --danger-color: #ef4444;
    }

    /* Dashboard Header */
    .dashboard-header {
        position: relative;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-radius: 16px;
        padding: 32px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(1, 102, 52, 0.2);
    }

    .header-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .logo-circle {
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 20px;
        padding: 12px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-circle img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .header-text h2 {
        color: white;
        font-weight: 700;
        font-size: 28px;
        margin: 0;
    }

    .text-white-75 {
        color: rgba(255, 255, 255, 0.75);
        font-size: 14px;
    }

    .admin-info {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .admin-name {
        color: white;
        font-weight: 600;
        font-size: 16px;
    }

    .admin-role {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
    }

    .header-pattern {
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        height: 100%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.3;
    }

    /* Modern Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    }

    .stat-card-primary:hover {
        border-color: var(--primary-color);
    }

    .stat-card-success:hover {
        border-color: var(--success-color);
    }

    .stat-card-warning:hover {
        border-color: var(--warning-color);
    }

    .stat-card-info:hover {
        border-color: var(--info-color);
    }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .stat-card-primary .stat-icon {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
    }

    .stat-card-success .stat-icon {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
    }

    .stat-card-warning .stat-icon {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        color: white;
    }

    .stat-card-info .stat-icon {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: white;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        display: block;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
        margin: 0;
        line-height: 1;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        margin-top: 8px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 500;
    }

    .stat-trend.up {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-trend.neutral {
        background: #e5e7eb;
        color: #374151;
    }

    /* Modern Action Cards */
    .action-card {
        display: flex;
        align-items: center;
        gap: 16px;
        background: white;
        border-radius: 14px;
        padding: 24px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid #f3f4f6;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .action-card-primary::before {
        background: var(--primary-color);
    }

    .action-card-success::before {
        background: var(--success-color);
    }

    .action-card-info::before {
        background: var(--info-color);
    }

    .action-card:hover {
        transform: translateX(8px);
        border-color: currentColor;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .action-card-primary {
        color: var(--primary-color);
    }

    .action-card-success {
        color: var(--success-color);
    }

    .action-card-info {
        color: var(--info-color);
    }

    .action-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .action-card-primary .action-icon {
        background: rgba(1, 102, 52, 0.1);
        color: var(--primary-color);
    }

    .action-card-success .action-icon {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }

    .action-card-info .action-icon {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info-color);
    }

    .action-content {
        flex: 1;
    }

    .action-content h5 {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 4px 0;
    }

    .action-content p {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    .action-arrow {
        font-size: 20px;
        transition: all 0.3s ease;
        opacity: 0.6;
    }

    .action-card:hover .action-arrow {
        transform: translateX(4px);
        opacity: 1;
    }

    /* Modern Timeline Card */
    .timeline-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .timeline-header {
        background: linear-gradient(135deg, #f9fafb, #f3f4f6);
        padding: 24px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #e5e7eb;
    }

    .timeline-header h5 {
        color: #111827;
        font-weight: 600;
        font-size: 18px;
    }

    .timeline-badge {
        background: var(--primary-color);
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .timeline-body {
        padding: 32px;
    }

    .modern-timeline {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .timeline-item {
        display: flex;
        gap: 20px;
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 11px;
        top: 32px;
        width: 2px;
        height: calc(100% + 24px);
        background: #e5e7eb;
    }

    .timeline-item.active::after {
        background: var(--primary-color);
    }

    .timeline-marker {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e5e7eb;
        border: 4px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .timeline-item.active .timeline-marker {
        background: var(--primary-color);
        box-shadow: 0 2px 12px rgba(1, 102, 52, 0.3);
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-content h6 {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 4px 0;
    }

    .timeline-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .timeline-status.active {
        background: #d1fae5;
        color: #065f46;
    }

    .timeline-status.pending {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Counter Animation */
    .counter {
        display: inline-block;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 24px;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-right {
            width: 100%;
        }

        .admin-info {
            text-align: left;
        }

        .stat-number {
            font-size: 24px;
        }

        .action-card {
            padding: 20px;
        }
    }
</style>
