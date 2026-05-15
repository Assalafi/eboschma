@extends('layouts.app')

@section('title', 'Customer Care Report')

@section('content')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            <a href="{{ route('reports.index') }}">Reports</a>
                        </div>
                        <h2 class="page-title">
                            Customer Care Analytics
                        </h2>
                        <div class="text-muted mt-1">Support ticket performance and resolution metrics</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.index') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to Reports
                            </a>
                            <a href="{{ route('reports.crm.export') }}" class="btn btn-primary">
                                <i class="ti ti-file-download me-2"></i>
                                Export
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Advanced Filters Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0 text-dark">
                                            <i class="ti ti-filter me-2 text-primary"></i>
                                            Filters
                                        </h5>
                                        <small class="text-muted">Refine your CRM data</small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="ti ti-refresh me-1"></i> Reset
                                        </button>
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="toggleAdvancedFilters()">
                                            <i class="ti ti-settings me-1"></i> Advanced
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <form method="GET" action="{{ route('reports.crm') }}" id="crmFilterForm">
                                    <!-- Primary Filters -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label fw-semibold text-muted">
                                                Date Range
                                            </label>
                                            <select name="date_range" class="form-select" onchange="toggleDateRanges()">
                                                <option value="">All Time</option>
                                                <option value="today"
                                                    {{ request('date_range') == 'today' ? 'selected' : '' }}>
                                                    Today
                                                </option>
                                                <option value="week"
                                                    {{ request('date_range') == 'week' ? 'selected' : '' }}>
                                                    This Week
                                                </option>
                                                <option value="month"
                                                    {{ request('date_range') == 'month' ? 'selected' : '' }}>
                                                    This Month
                                                </option>
                                                <option value="quarter"
                                                    {{ request('date_range') == 'quarter' ? 'selected' : '' }}>
                                                    This Quarter
                                                </option>
                                                <option value="year"
                                                    {{ request('date_range') == 'year' ? 'selected' : '' }}>
                                                    This Year
                                                </option>
                                                <option value="custom"
                                                    {{ request('date_range') == 'custom' ? 'selected' : '' }}>
                                                    Custom Range
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label fw-semibold text-muted">
                                                Status
                                            </label>
                                            <select name="status" class="form-select">
                                                <option value="">All Status</option>
                                                <option value="pending"
                                                    {{ request('status') == 'pending' ? 'selected' : '' }}>
                                                    Pending
                                                </option>
                                                <option value="in_progress"
                                                    {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                                    In Progress
                                                </option>
                                                <option value="completed"
                                                    {{ request('status') == 'completed' ? 'selected' : '' }}>
                                                    Completed
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label fw-semibold text-muted">
                                                Category
                                            </label>
                                            <select name="category" class="form-select">
                                                <option value="">All Categories</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label fw-semibold text-muted">
                                                Priority
                                            </label>
                                            <select name="priority" class="form-select">
                                                <option value="">All Priorities</option>
                                                <option value="high"
                                                    {{ request('priority') == 'high' ? 'selected' : '' }}>
                                                    High
                                                </option>
                                                <option value="medium"
                                                    {{ request('priority') == 'medium' ? 'selected' : '' }}>
                                                    Medium
                                                </option>
                                                <option value="low"
                                                    {{ request('priority') == 'low' ? 'selected' : '' }}>
                                                    Low
                                                </option>
                                                <option value="critical"
                                                    {{ request('priority') == 'critical' ? 'selected' : '' }}>
                                                    Critical
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label fw-semibold text-muted">
                                                Department
                                            </label>
                                            <select name="department" class="form-select">
                                                <option value="">All Departments</option>
                                                <option value="ES Office"
                                                    {{ request('department') == 'ES Office' ? 'selected' : '' }}>
                                                    ES Office
                                                </option>
                                                <option value="Finance"
                                                    {{ request('department') == 'Finance' ? 'selected' : '' }}>
                                                    Finance
                                                </option>
                                                <option value="ICT"
                                                    {{ request('department') == 'ICT' ? 'selected' : '' }}>
                                                    ICT
                                                </option>
                                                <option value="Admin"
                                                    {{ request('department') == 'Admin' ? 'selected' : '' }}>
                                                    Admin
                                                </option>
                                                <option value="Programmes"
                                                    {{ request('department') == 'Programmes' ? 'selected' : '' }}>
                                                    Programmes
                                                </option>
                                                <option value="PRS"
                                                    {{ request('department') == 'PRS' ? 'selected' : '' }}>
                                                    PRS
                                                </option>
                                                <option value="SQA"
                                                    {{ request('department') == 'SQA' ? 'selected' : '' }}>
                                                    SQA
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ti ti-search me-2"></i>
                                                Apply Filters
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Advanced Date Filters -->
                                    <div id="advancedDateFilters"
                                        style="display: {{ request('date_range') == 'custom' ? 'block' : 'none' }};">
                                        <div class="border-top pt-4">
                                            <h6 class="text-muted mb-3">Custom Date Range</h6>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted">Assigned Date From</label>
                                                    <input type="date" name="assigned_date_from" class="form-control"
                                                        value="{{ request('assigned_date_from') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted">Assigned Date To</label>
                                                    <input type="date" name="assigned_date_to" class="form-control"
                                                        value="{{ request('assigned_date_to') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted">Resolved Date From</label>
                                                    <input type="date" name="resolved_date_from" class="form-control"
                                                        value="{{ request('resolved_date_from') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted">Resolved Date To</label>
                                                    <input type="date" name="resolved_date_to" class="form-control"
                                                        value="{{ request('resolved_date_to') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row row-deck row-cards mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Tickets</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($stats['total_tickets']) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">All support requests</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Resolved</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($stats['resolved_tickets']) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Successfully completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Avg Resolution Time</div>
                                </div>
                                <div class="h3 mb-2">{{ $stats['avg_resolution_time'] }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Hours to complete</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Pending</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($stats['pending_tickets']) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Awaiting resolution</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Analytics Dashboard -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient-info text-white py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="ti ti-chart-pie me-2"></i>
                                            Interactive Analytics
                                        </h5>
                                        <small class="opacity-75">Click any chart to view detailed breakdown</small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-light" onclick="refreshCharts()">
                                            <i class="ti ti-refresh me-1"></i> Refresh
                                        </button>
                                        <button type="button" class="btn btn-light" onclick="toggleChartView()">
                                            <i class="ti ti-layout me-1"></i> Layout
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4" id="chartsContainer">
                                    <!-- Category Chart -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border-0 shadow-sm chart-card" onclick="drillDownCategory()">
                                            <div class="card-body text-center">
                                                <h6 class="card-title text-primary mb-3">
                                                    <i class="ti ti-category me-2"></i>
                                                    Tickets by Category
                                                </h6>
                                                <div class="chart-container" style="position: relative; height: 250px;">
                                                    <canvas id="categoryChart"></canvas>
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="ti ti-click me-1"></i>
                                                    Click to view category details
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Chart -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border-0 shadow-sm chart-card" onclick="drillDownStatus()">
                                            <div class="card-body text-center">
                                                <h6 class="card-title text-success mb-3">
                                                    <i class="ti ti-circle-check me-2"></i>
                                                    Tickets by Status
                                                </h6>
                                                <div class="chart-container" style="position: relative; height: 250px;">
                                                    <canvas id="statusChart"></canvas>
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="ti ti-click me-1"></i>
                                                    Click to view status details
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Department Chart -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border-0 shadow-sm chart-card" onclick="drillDownDepartment()">
                                            <div class="card-body text-center">
                                                <h6 class="card-title text-warning mb-3">
                                                    <i class="ti ti-building me-2"></i>
                                                    Tickets by Department
                                                </h6>
                                                <div class="chart-container" style="position: relative; height: 250px;">
                                                    <canvas id="departmentChart"></canvas>
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="ti ti-click me-1"></i>
                                                    Click to view department details
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Details Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient-success text-white py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="ti ti-list-details me-2"></i>
                                            Ticket Details ({{ $tickets->count() }} tickets)
                                        </h5>
                                        <small class="opacity-75">Click any ticket to view full details and replies</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="input-group" style="width: 250px;">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="ti ti-search"></i>
                                            </span>
                                            <input type="text" id="ticketSearch" class="form-control border-start-0"
                                                placeholder="Search tickets..." onkeyup="searchTickets()">
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-light" onclick="toggleTableView()">
                                                <i class="ti ti-layout me-1"></i> View
                                            </button>
                                            <button type="button" class="btn btn-light"
                                                onclick="window.location.href='{{ route('reports.crm') }}/print?' + new URLSearchParams(new FormData(document.getElementById('crmFilterForm'))).toString()">
                                                <i class="ti ti-printer me-1"></i> Print PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="ticketsTable" style="min-width: 1200px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 60px; min-width: 60px;">
                                                    <i class="ti ti-hash me-1"></i>
                                                    #
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 140px; min-width: 140px;">
                                                    <i class="ti ti-hash me-1"></i>
                                                    Ticket ID
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 130px; min-width: 130px;">
                                                    <i class="ti ti-id me-1"></i>
                                                    Boschma No
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 250px; min-width: 250px;">
                                                    <i class="ti ti-user me-1"></i>
                                                    Name
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 160px; min-width: 160px;">
                                                    <i class="ti ti-tag me-1"></i>
                                                    Category
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 120px; min-width: 120px;">
                                                    <i class="ti ti-circle-check me-1"></i>
                                                    Status
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 90px; min-width: 90px;">
                                                    <i class="ti ti-flag me-1"></i>
                                                    Priority
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 130px; min-width: 130px;">
                                                    <i class="ti ti-calendar me-1"></i>
                                                    Created
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 100px; min-width: 100px;">
                                                    <i class="ti ti-clock me-1"></i>
                                                    Time
                                                </th>
                                                <th class="border-0 text-uppercase small fw-bold text-muted"
                                                    style="width: 180px; min-width: 180px;">
                                                    <i class="ti ti-user-check me-1"></i>
                                                    Assigned To
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tickets as $ticket)
                                                <tr class="ticket-row" onclick="viewTicketDetails({{ $ticket->id }})"
                                                    style="cursor: pointer; transition: all 0.2s ease;"
                                                    onmouseover="this.style.backgroundColor='#f8f9fa'"
                                                    onmouseout="this.style.backgroundColor='transparent'">
                                                    <td class="text-muted" style="vertical-align: middle;">
                                                        {{ $loop->iteration }}
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        <div class="d-flex align-items-center">
                                                            <code class="bg-light px-2 py-1 rounded text-primary fw-bold"
                                                                style="font-size: 11px;">{{ $ticket->ticket_id }}</code>
                                                            @if ($ticket->replies && $ticket->replies->count() > 0)
                                                                <span class="badge bg-info ms-1"
                                                                    style="font-size: 9px; padding: 2px 5px;">{{ $ticket->replies->count() }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        @if ($ticket->boschma_no)
                                                            <span class="badge bg-primary"
                                                                style="font-size: 10px; padding: 3px 6px;">{{ $ticket->boschma_no }}</span>
                                                        @else
                                                            <span class="text-muted" style="font-size: 11px;">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                                    style="width: 26px; height: 26px; font-size: 11px; font-weight: bold;">
                                                                    {{ strtoupper(substr($ticket->name, 0, 1)) }}
                                                                </div>
                                                            </div>
                                                            <div style="min-width: 0; flex: 1;">
                                                                <div class="fw-semibold text-dark"
                                                                    style="font-size: 12px; line-height: 1.2;">
                                                                    {{ $ticket->name }}</div>
                                                                @if ($ticket->email)
                                                                    <small class="text-muted d-block"
                                                                        style="font-size: 10px; line-height: 1.1;">{{ $ticket->email }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        <span class=""
                                                            style="font-size: 10px; padding: 3px 6px;">{{ $ticket->category->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        @php
                                                            $statusColors = [
                                                                'pending' => 'bg-warning',
                                                                'in_progress' => 'bg-info',
                                                                'completed' => 'bg-success',
                                                                'cancelled' => 'bg-danger',
                                                            ];
                                                            $statusColor =
                                                                $statusColors[$ticket->status] ?? 'bg-secondary';
                                                        @endphp
                                                        <span class="badge {{ $statusColor }}"
                                                            style="font-size: 10px; padding: 3px 6px;">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        @php
                                                            $priorityColors = [
                                                                'low' => 'bg-secondary',
                                                                'medium' => 'bg-warning',
                                                                'high' => 'bg-danger',
                                                                'critical' => 'bg-dark',
                                                            ];
                                                            $priorityColor =
                                                                $priorityColors[$ticket->priority] ?? 'bg-secondary';
                                                        @endphp
                                                        <span class="badge {{ $priorityColor }}"
                                                            style="font-size: 10px; padding: 3px 6px;">{{ ucfirst($ticket->priority) }}</span>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        <div style="font-size: 11px; line-height: 1.2;">
                                                            <div class="text-muted">
                                                                {{ $ticket->created_at->format('M j, Y') }}</div>
                                                            <div class="text-muted">
                                                                {{ $ticket->created_at->format('H:i') }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        <div style="font-size: 11px; line-height: 1.2;">
                                                            @if ($ticket->resolved_at)
                                                                <div class="text-success fw-bold">
                                                                    {{ round($ticket->created_at->diffInDays($ticket->resolved_at)) }}d
                                                                </div>
                                                                <div class="text-muted">
                                                                    {{ $ticket->created_at->diffInHours($ticket->resolved_at) % 24 }}h
                                                                </div>
                                                            @else
                                                                <div class="text-muted">
                                                                    {{ round($ticket->created_at->diffInDays(now())) }}d
                                                                </div>
                                                                <div class="text-muted">
                                                                    {{ $ticket->created_at->diffInHours(now()) % 24 }}h
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="py-3" style="vertical-align: middle;">
                                                        <div class="d-flex align-items-center">
                                                            @if ($ticket->assignedUser)
                                                                <div class="avatar avatar-sm me-2">
                                                                    <div class="avatar-placeholder bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                                                        style="width: 22px; height: 22px; font-size: 9px; font-weight: bold;">
                                                                        {{ strtoupper(substr($ticket->assignedUser->fullname, 0, 1)) }}
                                                                    </div>
                                                                </div>
                                                                <div style="min-width: 0; flex: 1;">
                                                                    <div class="fw-semibold"
                                                                        style="font-size: 11px; line-height: 1.2;">
                                                                        {{ $ticket->assignedUser->fullname }}</div>
                                                                </div>
                                                            @else
                                                                <span class="badge bg-secondary"
                                                                    style="font-size: 10px; padding: 3px 6px;">Unassigned</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if ($tickets->isEmpty())
                                        <div class="text-center py-8">
                                            <div class="mb-4">
                                                <i class="ti ti-inbox text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">No tickets found</h5>
                                            <p class="text-muted">Try adjusting your filters to see more results.</p>
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
            // Chart configuration
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;

            // Category Chart with improved label handling
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryLabels = @json($categoryStats->pluck('name'));
            const categoryData = @json($categoryStats->pluck('count'));

            // Truncate long labels
            const truncatedCategoryLabels = categoryLabels.map(label =>
                label.length > 15 ? label.substring(0, 12) + '...' : label
            );

            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: truncatedCategoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: [
                            '#01542B', '#28a745', '#ffc107', '#dc3545',
                            '#6f42c1', '#20c997', '#fd7e14', '#17a2b8'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return categoryLabels[context.dataIndex] + ': ' + context.parsed + ' tickets';
                                }
                            }
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const category = categoryLabels[index];
                            // Find category ID by name
                            const categoryId = @json($categories->pluck('id', 'name'))[category];
                            if (categoryId) {
                                window.location.href = '{{ route('reports.crm') }}/category/breakdown?category=' +
                                    categoryId;
                            }
                        }
                    }
                }
            });

            // Status Chart with improved design
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusLabels = @json($statusStats->pluck('status'));
            const statusData = @json($statusStats->pluck('count'));

            new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: 'Number of Tickets',
                        data: statusData,
                        backgroundColor: statusLabels.map(label => {
                            if (label.toLowerCase().includes('completed')) return '#28a745';
                            if (label.toLowerCase().includes('progress')) return '#ffc107';
                            return '#dc3545';
                        }),
                        borderRadius: 8,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' tickets';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const status = statusLabels[index].toLowerCase().replace(' ', '_');
                            window.location.href = '{{ route('reports.crm') }}/status/breakdown?status=' +
                                encodeURIComponent(
                                    status);
                        }
                    }
                }
            });

            // Department Chart with improved label handling
            const departmentCtx = document.getElementById('departmentChart').getContext('2d');
            const departmentLabels = @json($departmentStats->pluck('department'));
            const departmentData = @json($departmentStats->pluck('count'));

            new Chart(departmentCtx, {
                type: 'doughnut',
                data: {
                    labels: departmentLabels,
                    datasets: [{
                        data: departmentData,
                        backgroundColor: [
                            '#01542B', '#28a745', '#ffc107', '#dc3545',
                            '#6f42c1', '#20c997', '#fd7e14'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return departmentLabels[context.dataIndex] + ': ' + context.parsed + ' tickets';
                                }
                            }
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const department = departmentLabels[index];
                            window.location.href = '{{ route('reports.crm') }}/department/breakdown?department=' +
                                encodeURIComponent(department);
                        }
                    }
                }
            });

            // Interactive Functions
            function toggleAdvancedFilters() {
                const advancedFilters = document.getElementById('advancedDateFilters');
                advancedFilters.style.display = advancedFilters.style.display === 'none' ? 'block' : 'none';
            }

            function toggleDateRanges() {
                const dateRange = document.querySelector('select[name="date_range"]');
                const advancedFilters = document.getElementById('advancedDateFilters');

                if (dateRange.value === 'custom') {
                    advancedFilters.style.display = 'block';
                } else {
                    advancedFilters.style.display = 'none';
                }
            }

            function resetFilters() {
                document.getElementById('crmFilterForm').reset();
                window.location.href = '{{ route('reports.crm') }}';
            }

            function refreshCharts() {
                location.reload();
            }

            function toggleChartView() {
                const container = document.getElementById('chartsContainer');
                container.classList.toggle('row-cols-1');
                container.classList.toggle('row-cols-md-2');
                container.classList.toggle('row-cols-lg-3');
            }

            function drillDownCategory() {
                window.location.href = '{{ route('reports.crm') }}/category/breakdown';
            }

            function drillDownStatus() {
                window.location.href = '{{ route('reports.crm') }}/status/breakdown';
            }

            function drillDownDepartment() {
                window.location.href = '{{ route('reports.crm') }}/department/breakdown';
            }

            function viewTicketDetails(ticketId) {
                window.location.href = '{{ route('reports.crm') }}/' + ticketId;
            }

            function searchTickets() {
                const input = document.getElementById('ticketSearch');
                const filter = input.value.toUpperCase();
                const table = document.getElementById('ticketsTable');
                const rows = table.getElementsByTagName('tr');

                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let found = false;

                    for (let j = 0; j < cells.length; j++) {
                        const cell = cells[j];
                        if (cell) {
                            const textValue = cell.textContent || cell.innerText;
                            if (textValue.toUpperCase().indexOf(filter) > -1) {
                                found = true;
                                break;
                            }
                        }
                    }

                    rows[i].style.display = found ? '' : 'none';
                }
            }

            function toggleTableView() {
                const table = document.getElementById('ticketsTable');
                table.classList.toggle('table-sm');
            }

            function printTable() {
                // Get current filters from form
                const form = document.getElementById('crmFilterForm');
                const formData = new FormData(form);
                const params = new URLSearchParams();

                // Add all form data to URL params
                for (let [key, value] of formData.entries()) {
                    if (value) {
                        params.append(key, value);
                    }
                }

                // Open print view in same tab
                window.location.href = '{{ route('reports.crm') }}/print?' + params.toString();
            }

            // Add hover effects for chart cards
            document.querySelectorAll('.chart-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                    this.style.transition = 'all 0.3s ease';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 0.125rem 0.25rem rgba(0,0,0,0.075)';
                });
            });

            // Initialize tooltips
            document.addEventListener('DOMContentLoaded', function() {
                // Add Bootstrap tooltips if available
                if (typeof bootstrap !== 'undefined') {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });
        </script>
    @endsection
