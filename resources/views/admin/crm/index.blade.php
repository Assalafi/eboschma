@extends('layouts.app')

@section('title', 'Customer Care - Tickets')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="ti-headphone-alt"></i> Customer Care Tickets
                        </h4>
                        <div style="float: right" class="card-action">
                            <a href="{{ route('referrals.index') }}" class="btn btn-warning me-2">
                                <i class="ti-share"></i> Referral Overview
                            </a>
                            <a href="{{ route('crm.facility-activity') }}" class="btn btn-info me-2">
                                <i class="ti-pulse"></i> Facility Activity
                            </a>
                            <a href="{{ route('crm.create') }}" class="btn btn-primary">
                                <i class="ti-plus"></i> Create Ticket
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Beneficiary Search Panel -->
                        <div class="card mb-4 border-info shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="ti-search me-2"></i>
                                    Beneficiary Search
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="beneficiary_search">Search by NIN, BOSCHMA ID, Name, or Phone</label>
                                            <input type="text" class="form-control" id="beneficiary_search" 
                                                placeholder="Enter search term...">
                                            <small class="text-muted">Type to search for beneficiaries, spouses, or children</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-info w-100" id="beneficiary_search_btn">
                                                <i class="ti-search me-1"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Search Results -->
                                <div id="searchResults" class="mt-3" style="display:none;">
                                    <div id="searchResultsContent"></div>
                                </div>
                                
                                <!-- Loading Spinner -->
                                <div id="searchLoading" class="text-center mt-3" style="display:none;">
                                    <div class="spinner-border text-info" role="status">
                                        <span class="sr-only">Searching...</span>
                                    </div>
                                    <p class="text-muted mt-2">Searching beneficiaries...</p>
                                </div>

                                <!-- No Results Message -->
                                <div id="searchNoResults" class="alert alert-warning mt-3" style="display:none;">
                                    <i class="ti-info-alt me-2"></i>
                                    <span id="searchNoResultsText"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-2 col-sm-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $tickets->total() }}</h5>
                                        <p class="card-text">Total Tickets</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ App\Models\Ticket::status('pending')->count() }}</h5>
                                        <p class="card-text">Pending</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ App\Models\Ticket::status('in_progress')->count() }}</h5>
                                        <p class="card-text">In Progress</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ App\Models\Ticket::status('completed')->count() }}</h5>
                                        <p class="card-text">Completed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ App\Models\Ticket::overdue()->count() }}</h5>
                                        <p class="card-text">Overdue</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ App\Models\Ticket::assignedTo(auth()->id())->count() }}
                                        </h5>
                                        <p class="card-text">My Tickets</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Staff Panel -->
                        <div class="card mb-4 border-success shadow-sm">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#activeStaffCollapse" style="cursor: pointer;">
                                <h5 class="card-title mb-0">
                                    <i class="ti-user me-2"></i> Active Staff (Last 30 Min)
                                </h5>
                                <div>
                                    <button class="btn btn-sm btn-light text-success me-2" onclick="event.stopPropagation(); fetchActiveStaff()">
                                        <i class="ti-reload"></i> Refresh
                                    </button>
                                    <i class="ti-angle-down"></i>
                                </div>
                            </div>
                            <div id="activeStaffCollapse" class="collapse show">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Role/Department</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="activeStaffList">
                                                <tr>
                                                    <td colspan="4" class="text-center py-3">
                                                        <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                                        Loading active staff...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Activity Monitor Panel -->
                        <div class="card mb-4 border-info shadow-sm">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#ehrActivityCollapse" style="cursor: pointer;">
                                <h5 class="card-title mb-0">
                                    <i class="ti-pulse me-2"></i> User Activity Monitor
                                </h5>
                                <div>
                                    <button class="btn btn-sm btn-light text-info me-2" onclick="event.stopPropagation(); fetchEhrActivity()">
                                        <i class="ti-reload"></i> Refresh
                                    </button>
                                    <i class="ti-angle-down"></i>
                                </div>
                            </div>
                            <div id="ehrActivityCollapse" class="collapse show">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Action Type</th>
                                                    <th>Staff</th>
                                                    <th>Patient</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ehrActivityList">
                                                <tr>
                                                    <td colspan="4" class="text-center py-3">
                                                        <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                                                        Loading activity...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <form method="GET" action="{{ route('crm.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Search</label>
                                    <input type="text" name="search" class="form-control"
                                        value="{{ request('search') }}" placeholder="Ticket ID, Name, etc.">
                                </div>
                                <div class="col-md-2">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="in_progress"
                                            {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Priority</label>
                                    <select name="priority" class="form-control">
                                        <option value="">All Priority</option>
                                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>
                                            Medium</option>
                                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High
                                        </option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>
                                            Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Assigned To</label>
                                    <select name="assigned_to" class="form-control">
                                        <option value="">Anyone</option>
                                        @foreach ($staff as $staffMember)
                                            <option value="{{ $staffMember->id }}"
                                                {{ request('assigned_to') == $staffMember->id ? 'selected' : '' }}>
                                                {{ $staffMember->fullname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Category</label>
                                    <select name="category" class="form-control">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                        <a href="{{ route('crm.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Tickets Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Name</th>
                                        <th>Boschma No</th>
                                        <th>Complain Type</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tickets as $ticket)
                                        <tr class="{{ $ticket->isOverdue() ? 'table-danger' : '' }}">
                                            <td>
                                                <a href="{{ route('crm.show', $ticket->id) }}" class="text-primary">
                                                    {{ $ticket->ticket_id }}
                                                </a>
                                            </td>
                                            <td>{{ $ticket->name }}</td>
                                            <td>{{ $ticket->boschma_no ?? '-' }}</td>
                                            <td>
                                                <span class="badge"
                                                    style="background-color: {{ $ticket->category->color ?? '#6c757d' }}">
                                                    {{ $ticket->category->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge"
                                                    style="background-color: {{ $ticket->getPriorityColor() }}">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge"
                                                    style="background-color: {{ $ticket->getStatusColor() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $ticket->assignedUser->fullname ?? 'Unassigned' }}</td>
                                            <td>
                                                {{ $ticket->due_date->format('M d, Y H:i') }}
                                                @if ($ticket->isOverdue())
                                                    <i class="ti-alert text-danger" title="Overdue"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('crm.show', $ticket->id) }}" class="btn btn-info"
                                                        title="View">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                    @if ($ticket->canBeEditedBy(Auth::user()))
                                                        <a href="{{ route('crm.edit', $ticket->id) }}"
                                                            class="btn btn-warning" title="Edit">
                                                            <i class="ti-pencil"></i>
                                                        </a>
                                                    @endif
                                                    @if ($ticket->canBeDeletedBy(Auth::user()))
                                                        <form action="{{ route('crm.destroy', $ticket->id) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this pending ticket?')">
                                                                <i class="ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No tickets found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 gap-3">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing {{ $tickets->firstItem() ?? 0 }} to
                                    {{ $tickets->lastItem() ?? 0 }}
                                    of {{ $tickets->total() }} results
                                </p>
                            </div>
                            <div class="overflow-auto w-100 w-md-auto">
                                @if ($tickets->hasPages())
                                    <nav aria-label="Tickets pagination">
                                        <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                            {{-- Previous Page Link --}}
                                            @if ($tickets->onFirstPage())
                                                <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $tickets->previousPageUrl() }}" rel="prev">Prev</a>
                                                </li>
                                            @endif

                                            {{-- Pagination Elements with Smart Window --}}
                                            @php
                                                $currentPage = $tickets->currentPage();
                                                $lastPage = $tickets->lastPage();
                                                $onEachSide = 2; // Show 2 pages on each side of current page

                                                // Calculate start and end of the sliding window
                                                $start = max(1, $currentPage - $onEachSide);
                                                $end = min($lastPage, $currentPage + $onEachSide);

                                                // Adjust if we're near the beginning or end
                                                if ($currentPage <= $onEachSide + 1) {
                                                    $end = min($lastPage, $onEachSide * 2 + 2);
                                                }
                                                if ($currentPage >= $lastPage - $onEachSide) {
                                                    $start = max(1, $lastPage - $onEachSide * 2 - 1);
                                                }
                                            @endphp

                                            {{-- First Page --}}
                                            @if ($start > 1)
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $tickets->url(1) }}">1</a></li>
                                                @if ($start > 2)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                            @endif

                                            {{-- Page Number Links --}}
                                            @for ($page = $start; $page <= $end; $page++)
                                                @if ($page == $currentPage)
                                                    <li class="page-item active"><span
                                                            class="page-link">{{ $page }}</span></li>
                                                @else
                                                    <li class="page-item"><a class="page-link"
                                                            href="{{ $tickets->url($page) }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endfor

                                            {{-- Last Page --}}
                                            @if ($end < $lastPage)
                                                @if ($end < $lastPage - 1)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $tickets->url($lastPage) }}">{{ $lastPage }}</a>
                                                </li>
                                            @endif

                                            {{-- Next Page Link --}}
                                            @if ($tickets->hasMorePages())
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $tickets->nextPageUrl() }}" rel="next">Next</a></li>
                                            @else
                                                <li class="page-item disabled"><span class="page-link">Next</span></li>
                                            @endif
                                        </ul>
                                    </nav>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Twilio Dialer Toggle Button -->
    <div id="twilioDialerToggle" class="position-fixed bottom-0 end-0 m-4 shadow-lg rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 60px; height: 60px; cursor: pointer; z-index: 1050; animation: pulse 1.8s infinite;" onclick="toggleTwilioDialer()">
        <i class="ti-headphone-alt" style="font-size: 1.8rem;"></i>
        <span class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle" style="margin-left: -8px; margin-top: 8px;"></span>
    </div>

    <!-- Slide-out Twilio Dialer Panel -->
    <div id="twilioDialerPanel" class="position-fixed top-0 end-0 h-100 shadow-lg bg-white" style="width: 340px; transform: translateX(340px); transition: transform 0.3s ease; z-index: 1040; border-top-left-radius: 20px; border-bottom-left-radius: 20px;">
        <div class="d-flex flex-column h-100">
            <!-- Header -->
            <div class="p-3 bg-primary text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: 20px;">
                <h5 class="mb-0 fw-bold"><i class="ti-mobile me-2"></i> Twilio Dialer</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success" id="twilioDialerStatus"><i class="ti-check"></i> Connected</span>
                    <button type="button" class="btn-close btn-close-white" style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;" onclick="toggleTwilioDialer()">&times;</button>
                </div>
            </div>

            <!-- Body -->
            <div class="flex-grow-1 p-3 overflow-auto">
                <!-- Display -->
                <div class="bg-light rounded p-2 mb-3 text-center">
                    <input type="text" class="form-control text-center fw-bold fs-4 bg-transparent border-0" id="twilioDialerDisplay" placeholder="Enter phone...">
                    <small class="text-muted" id="twilioDialerStatusText">Ready for Outbound Call</small>
                </div>

                <!-- Keypad -->
                <div class="row g-2 mb-3">
                    @foreach(['1','2','3','4','5','6','7','8','9','*','0','#'] as $key)
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 py-3 fw-bold rounded-3 keypad-btn" onclick="pressTwilioKey('{{ $key }}');">{{ $key }}</button>
                        </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <button class="btn btn-danger btn-sm px-3" onclick="hangupTwilioCall()"><i class="ti-close"></i> Hangup</button>
                    <button class="btn btn-success btn-lg rounded-circle p-3 d-flex align-items-center justify-content-center shadow" style="width: 54px; height: 54px;" onclick="dialWithTwilio()">
                        <i class="ti-mobile" style="font-size: 1.5rem;"></i>
                    </button>
                </div>

                <hr>

                <!-- Simulate Incoming Call Tool -->
                <div class="card bg-info bg-opacity-10 border border-info rounded p-2 mb-3">
                    <h6 class="fw-bold text-info mb-1"><i class="ti-settings"></i> Test Tools</h6>
                    <small class="text-muted d-block mb-2">Simulate an incoming call pop-up.</small>
                    <button class="btn btn-info btn-xs w-100" onclick="simulateIncomingCall()"><i class="ti-bell"></i> Simulate Call-Pop</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Incoming Twilio Call Pop-up Overlay -->
    <div id="twilioCallPop" class="position-fixed bottom-0 start-0 m-4 shadow-lg bg-white border border-success" style="width: 320px; border-radius: 15px; transform: translateY(400px); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 1060; border-left: 6px solid #198754 !important;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-success animate-blink"><i class="ti-bell"></i> INCOMING CALL (Twilio)</span>
                <button type="button" class="btn-close" style="background: none; border: none; font-size: 1rem; color: #6c757d; cursor: pointer;" onclick="closeCallPop()">&times;</button>
            </div>

            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti-user text-success" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0" id="callPopName">Caller</h6>
                    <small class="text-muted" id="callPopDetails">Enrolled Beneficiary</small>
                </div>
            </div>

            <div class="text-center bg-light rounded p-2 mb-3">
                <span class="small fw-mono text-dark" id="callPopPhone">Unknown</span>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-outline-danger" onclick="if(activeCall){activeCall.reject();} closeCallPop();">Decline</button>
                <a href="#" id="callPopAcceptBtn" class="btn btn-sm btn-success px-3 fw-bold"><i class="ti-headphone-alt me-1"></i> Answer</a>
            </div>
        </div>
    </div>

    <!-- Internal Message Modal (Phase 5 Placeholder) -->
    <div class="modal fade" id="internalMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="ti-comment-alt me-2"></i> Internal Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: white; cursor: pointer;">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Send a message to <strong id="msgStaffName"></strong></p>
                    <input type="hidden" id="msgStaffId">
                    <div class="form-group">
                        <textarea class="form-control" rows="4" placeholder="Type your message here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info text-white" onclick="alert('Message sent internally! (Phase 5 feature)'); bootstrap.Modal.getInstance(document.getElementById('internalMessageModal')).hide();">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Beneficiary Profile Side Panel (Slide-in) -->
    <div id="profilePanel" style="display:none; position:fixed; top:0; right:0; height:100%; width:420px; max-width:100%; z-index:1500; background:#fff; box-shadow:-6px 0 24px rgba(0,0,0,0.12); transform: translateX(100%); transition: transform 0.28s ease;">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
            <h5 class="mb-0">Patient Information</h5>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-2" onclick="closeSidePanel()">Close</button>
            </div>
        </div>
        <div id="profileLoader" class="text-center p-4" style="display:none;">
            <div class="spinner-border text-info" role="status"></div>
            <p class="text-muted mt-2">Loading profile...</p>
        </div>
        <div id="profileContent" style="padding:16px; display:none; overflow:auto; height:calc(100% - 72px);">
            <!-- Filled dynamically via JS -->
        </div>
    </div>

    <style>
        /* Profile panel slide-in */
        #profilePanel.slide-in {
            transform: translateX(0) !important;
        }

        .animate-blink {
            animation: blinker 1.2s linear infinite;
        }
        @keyframes blinker {
            50% { opacity: 0.3; }
        }
        .keypad-btn:active {
            background-color: #f8f9fa !important;
            transform: scale(0.95);
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(100, 116, 139, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(100, 116, 139, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(100, 116, 139, 0);
            }
        }
    </style>

    <!-- Twilio Voice SDK -->
    <script src="https://unpkg.com/@twilio/voice-sdk@2.10.1/dist/twilio.min.js"></script>

    <script>
        let dialerOpen = false;
        let twilioDevice = null;
        let activeCall = null;

        // Initialize Twilio WebRTC Device
        async function initializeTwilio() {
            try {
                // Use relative path to avoid CORS issues if accessing via 127.0.0.1 vs localhost
                const response = await fetch('/twilio/token', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} - ${response.statusText}`);
                }
                
                const data = await response.json();

                if (data.token) {
                    twilioDevice = new Twilio.Device(data.token, {
                        codecPreferences: ['opus', 'pcmu'],
                        fakeLocalDTMF: true,
                        enableRingingState: true
                    });

                    twilioDevice.on('registered', () => {
                        document.getElementById('twilioDialerStatus').innerHTML = '<i class="ti-check"></i> Ready';
                        document.getElementById('twilioDialerStatus').classList.replace('bg-warning', 'bg-success');
                        document.getElementById('twilioDialerStatusText').textContent = 'Ready for Outbound/Inbound Calls';
                        console.log('Twilio.Device Ready!');
                    });

                    twilioDevice.on('error', (error) => {
                        console.error('Twilio.Device Error: ', error.message);
                        document.getElementById('twilioDialerStatusText').textContent = 'Error: ' + error.message;
                    });

                    // Handle incoming calls from the browser!
                    twilioDevice.on('incoming', (connection) => {
                        console.log('Incoming connection from ', connection.parameters.From);
                        activeCall = connection;
                        
                        // Simulate the popup but with real incoming call data
                        document.getElementById('callPopName').textContent = 'Incoming Call';
                        document.getElementById('callPopPhone').textContent = connection.parameters.From;
                        document.getElementById('callPopDetails').textContent = 'From Twilio Network';
                        
                        const pop = document.getElementById('twilioCallPop');
                        pop.style.transform = 'translateY(0)';

                        // Add accept/reject logic
                        const acceptBtn = document.getElementById('callPopAcceptBtn');
                        acceptBtn.href = '#';
                        acceptBtn.onclick = (e) => {
                            e.preventDefault();
                            connection.accept();
                            closeCallPop();
                            document.getElementById('twilioDialerStatusText').textContent = 'In Call with ' + connection.parameters.From;
                        };
                        
                        connection.on('disconnect', () => {
                            document.getElementById('twilioDialerStatusText').textContent = 'Call Ended';
                            closeCallPop();
                            activeCall = null;
                        });
                    });

                    twilioDevice.register();
                }
            } catch (error) {
                console.error('Failed to initialize Twilio:', error);
                document.getElementById('twilioDialerStatusText').textContent = 'Error: ' + error.message;
            }
        }

        // Call init when document loads
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('twilioDialerStatus').innerHTML = '<i class="ti-time"></i> Connecting...';
            document.getElementById('twilioDialerStatus').classList.replace('bg-success', 'bg-warning');
            initializeTwilio();
        });

        window.toggleTwilioDialer = function() {
            const panel = document.getElementById('twilioDialerPanel');
            dialerOpen = !dialerOpen;
            panel.style.transform = dialerOpen ? 'translateX(0)' : 'translateX(340px)';
        };

        window.pressTwilioKey = function(key) {
            document.getElementById('twilioDialerDisplay').value += key;
        };

        window.clearTwilioDisplay = function() {
            document.getElementById('twilioDialerDisplay').value = '';
        };

        window.dialWithTwilio = function() {
            const phone = document.getElementById('twilioDialerDisplay').value.trim();
            if (!phone) {
                alert('Please enter a valid phone number first!');
                return;
            }

            if (!twilioDevice) {
                alert('Twilio is not initialized yet. Please wait a moment.');
                return;
            }

            document.getElementById('twilioDialerStatusText').textContent = 'Calling ' + phone + '...';

            // Connect using WebRTC (browser to phone)
            const params = { To: phone };
            twilioDevice.connect({ params: params }).then(call => {
                activeCall = call;
                
                call.on('accept', () => {
                    document.getElementById('twilioDialerStatusText').textContent = 'In Call with ' + phone;
                });
                
                call.on('disconnect', () => {
                    document.getElementById('twilioDialerStatusText').textContent = 'Call Ended';
                    activeCall = null;
                });

                call.on('cancel', () => {
                    document.getElementById('twilioDialerStatusText').textContent = 'Call Cancelled';
                    activeCall = null;
                });

                call.on('reject', () => {
                    document.getElementById('twilioDialerStatusText').textContent = 'Call Rejected';
                    activeCall = null;
                });

            }).catch(err => {
                console.error(err);
                document.getElementById('twilioDialerStatusText').textContent = 'Error: Could not connect call.';
            });
        };

        window.hangupTwilioCall = function() {
            if (activeCall) {
                activeCall.disconnect();
                activeCall = null;
            }
            document.getElementById('twilioDialerStatusText').textContent = 'Ready for Outbound Call';
        }

        // Make an internal call directly to a staff member
        window.callActiveStaff = function(staffId) {
            // Open the dialer if it's closed
            if (!dialerOpen) {
                toggleTwilioDialer();
            }
            // Set the target identity
            document.getElementById('twilioDialerDisplay').value = 'staff_' + staffId;
            // Automatically initiate the call
            dialWithTwilio();
        };

        window.simulateIncomingCall = function() {
            const profiles = [
                { name: 'Aisha Ibrahim', no: 'BO/2026/0488', type: 'Principal Enrollee', phone: '08092283733' },
                { name: 'Dr. Kabir Yusuf', no: 'BO/2026/1129', type: 'Provider Staff', phone: '09012345678' },
                { name: 'Yada BK Imam', no: 'BO/2026/0204', type: 'Principal Enrollee', phone: '07088990011' }
            ];

            const profile = profiles[Math.floor(Math.random() * profiles.length)];

            document.getElementById('callPopName').textContent = profile.name;
            document.getElementById('callPopDetails').textContent = `${profile.no} • ${profile.type}`;
            document.getElementById('callPopPhone').textContent = profile.phone;

            document.getElementById('callPopAcceptBtn').href = `/crm/create?phone=${encodeURIComponent(profile.phone)}&name=${encodeURIComponent(profile.name)}&boschma_no=${encodeURIComponent(profile.no)}`;

            const pop = document.getElementById('twilioCallPop');
            pop.style.transform = 'translateY(0)';

            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = audioCtx.createOscillator();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(440, audioCtx.currentTime);
                osc.connect(audioCtx.destination);
                osc.start();
                setTimeout(() => osc.stop(), 500);
            } catch(e) {}
        };

        window.closeCallPop = function() {
            const pop = document.getElementById('twilioCallPop');
            pop.style.transform = 'translateY(400px)';
        };

        // Beneficiary Search Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('beneficiary_search');
            const searchBtn = document.getElementById('beneficiary_search_btn');
            const searchResults = document.getElementById('searchResults');
            const searchLoading = document.getElementById('searchLoading');
            const searchNoResults = document.getElementById('searchNoResults');
            const searchResultsContent = document.getElementById('searchResultsContent');

            // Handle Enter key on search input
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Handle search button click
            searchBtn.addEventListener('click', performSearch);

            function performSearch() {
                const query = searchInput.value.trim();
                
                if (!query) {
                    alert('Please enter a search term');
                    return;
                }

                // Show loading, hide others
                searchLoading.style.display = 'block';
                searchResults.style.display = 'none';
                searchNoResults.style.display = 'none';

                // Make AJAX request
                fetch('{{ route("crm.search-beneficiary") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        searchLoading.style.display = 'none';

                        if (data.success && data.results.length > 0) {
                            displayResults(data.results);
                            searchResults.style.display = 'block';
                            searchNoResults.style.display = 'none';
                        } else {
                            searchNoResults.style.display = 'block';
                            document.getElementById('searchNoResultsText').textContent = 
                                data.message || 'No beneficiaries found matching your search';
                            searchResults.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        searchLoading.style.display = 'none';
                        console.error('Error:', error);
                        searchNoResults.style.display = 'block';
                        document.getElementById('searchNoResultsText').textContent = 
                            'An error occurred while searching. Please try again.';
                    });
            }
            
            // Active Staff fetch
            window.fetchActiveStaff = function() {
                const listBody = document.getElementById('activeStaffList');
                listBody.innerHTML = '<tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div> Loading...</td></tr>';
                
                fetch('{{ route("crm.active-staff") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.data.length === 0) {
                                listBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">No active staff in the last 30 minutes.</td></tr>';
                                return;
                            }
                            
                            let html = '';
                            data.data.forEach(staff => {
                                html += `
                                    <tr>
                                        <td class="fw-bold">${staff.name}</td>
                                        <td>${staff.role}</td>
                                        <td><span class="badge bg-success"><i class="ti-control-record"></i> ${staff.status}</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="callActiveStaff('${staff.id}')" title="Call via WebRTC">
                                                <i class="ti-mobile"></i> Call
                                            </button>
                                            <button class="btn btn-sm btn-outline-info ms-1" onclick="openInternalMessageModal('${staff.id}', '${staff.name.replace(/'/g, "\\'")}')" title="Message">
                                                <i class="ti-comment-alt"></i> Message
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                            listBody.innerHTML = html;
                        }
                    })
                    .catch(error => {
                        listBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-danger">Failed to load active staff.</td></tr>';
                    });
            };

            fetchActiveStaff();
            
            // EHR Activity fetch
            window.fetchEhrActivity = function() {
                const listBody = document.getElementById('ehrActivityList');
                listBody.innerHTML = '<tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-info"></div> Loading...</td></tr>';
                
                // If there's a facility_id filter, we could append it. Assuming no filter for dashboard or we can add it later.
                fetch('{{ route("crm.ehr-activity") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.data.length === 0) {
                                listBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">No recent EHR activity found.</td></tr>';
                                return;
                            }
                            
                            let html = '';
                            data.data.forEach(activity => {
                                let icon = 'ti-check-box';
                                if (activity.type === 'Consultation') icon = 'ti-stethoscope';
                                else if (activity.type === 'Vitals') icon = 'ti-heart-broken';
                                else if (activity.type === 'Lab') icon = 'ti-support';
                                else if (activity.type === 'Pharmacy') icon = 'ti-spray';
                                
                                html += `
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary"><i class="${icon}"></i> ${activity.type}</span>
                                        </td>
                                        <td class="fw-bold">${activity.staff}</td>
                                        <td>${activity.patient}</td>
                                        <td class="text-muted"><small>${activity.time_formatted}</small></td>
                                    </tr>
                                `;
                            });
                            listBody.innerHTML = html;
                        }
                    })
                    .catch(error => {
                        listBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-danger">Failed to load EHR activity.</td></tr>';
                    });
            };

            fetchEhrActivity();

            function displayResults(results) {
                let html = '<div class="row">';
                
                results.forEach(result => {
                    const typeLabel = result.type === 'beneficiary' ? 'Beneficiary' : 
                                     result.type === 'spouse' ? 'Spouse' : 'Child';
                    const typeColor = result.type === 'beneficiary' ? 'primary' : 
                                     result.type === 'spouse' ? 'warning' : 'info';
                    
                    html += `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-${typeColor}">
                                <div class="card-header bg-${typeColor} text-white">
                                    <span class="badge bg-light text-${typeColor}">${typeLabel}</span>
                                    <span class="float-right">${result.boschma_id}</span>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title">${result.name}</h6>
                                    <small class="text-muted d-block">
                                        <strong>Facility:</strong> ${result.facility}
                                    </small>
                                    ${result.phone ? `<small class="text-muted d-block"><strong>Phone:</strong> ${result.phone}</small>` : ''}
                                    <small class="text-muted d-block">
                                        <strong>Gender:</strong> ${result.gender || 'N/A'}
                                    </small>
                                    <small class="text-muted d-block">
                                        <strong>Status:</strong> <span class="badge badge-${result.status === 'active' ? 'success' : 'warning'}">${result.status}</span>
                                    </small>
                                    ${result.last_updated ? `<small class="text-muted d-block"><strong>Updated:</strong> ${result.last_updated}</small>` : ''}
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-sm btn-info flex-grow-1" onclick="viewBeneficiaryProfile('${result.id}')">
                                            <i class="ti-eye me-1"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-primary flex-grow-1" onclick="createTicketWithBeneficiary('${result.boschma_id}', '${result.name.replace(/'/g, "\\'")}', '${result.facility_id}')">
                                            <i class="ti-plus me-1"></i> Ticket
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                searchResultsContent.innerHTML = html;
            }

            window.createTicketWithBeneficiary = function(boschmaNo, name, facilityId) {
                // Redirect to create ticket page with beneficiary data pre-filled
                const url = '{{ route("crm.create") }}' + '?boschma_no=' + encodeURIComponent(boschmaNo) + 
                           '&name=' + encodeURIComponent(name) + 
                           '&facility_id=' + facilityId;
                window.location.href = url;
            };
            
            window.openInternalMessageModal = function(id, name) {
                // Phase 5 internal messaging placeholder modal
                document.getElementById('msgStaffName').textContent = name;
                document.getElementById('msgStaffId').value = id;
                const msgModal = new bootstrap.Modal(document.getElementById('internalMessageModal'));
                msgModal.show();
            };

            window.viewBeneficiaryProfile = function(boschmaId) {
                // Fetch profile data and open side panel
                const profilePanel = document.getElementById('profilePanel');
                const profileContent = document.getElementById('profileContent');
                const profileLoader = document.getElementById('profileLoader');
                
                profileContent.style.display = 'none';
                profileLoader.style.display = 'block';
                profilePanel.style.display = 'block';
                slideInPanel();

                fetch('/crm/beneficiary/' + encodeURIComponent(boschmaId) + '/profile', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayBeneficiaryProfile(data.profile, data.visit_history);
                        profileLoader.style.display = 'none';
                        profileContent.style.display = 'block';
                    } else {
                        profileContent.innerHTML = '<div class="alert alert-danger">Error loading profile: ' + data.message + '</div>';
                        profileLoader.style.display = 'none';
                        profileContent.style.display = 'block';
                    }
                })
                .catch(error => {
                    profileContent.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    profileLoader.style.display = 'none';
                    profileContent.style.display = 'block';
                });
            };

            window.displayBeneficiaryProfile = function(profile, visitHistory) {
                const profileContent = document.getElementById('profileContent');
                
                let html = `
                    <div class="profile-section">
                        <div class="row mb-3">
                            <div class="col-md-3 text-center">
                                ${profile.photo ? `<img src="${profile.photo}" alt="${profile.name}" class="img-fluid rounded" style="max-height: 150px; width: auto;">` : '<div class="bg-light rounded p-3"><i class="ti-user" style="font-size: 80px; color: #ccc;"></i></div>'}
                            </div>
                            <div class="col-md-9">
                                <h4>${profile.name}</h4>
                                <p class="mb-1"><strong>BOSCHMA ID:</strong> <span class="badge bg-primary">${profile.boschma_id}</span></p>
                                <p class="mb-1"><strong>Type:</strong> <span class="badge bg-info">${profile.type === 'beneficiary' ? 'Beneficiary' : profile.type === 'spouse' ? 'Spouse' : 'Child'}</span></p>
                                <p class="mb-1"><strong>Gender:</strong> ${profile.gender || 'N/A'}</p>
                                <p class="mb-1"><strong>Phone:</strong> ${profile.phone || 'N/A'}</p>
                                ${profile.date_of_birth ? `<p class="mb-1"><strong>Date of Birth:</strong> ${profile.date_of_birth}</p>` : ''}
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Enrolled Facility:</strong></p>
                                <p>${profile.enrolled_facility}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Current Status:</strong></p>
                                <p>
                                    ${profile.current_status === 'Waiting' ? '<span class="badge bg-warning">Waiting</span>' : 
                                      profile.current_status === 'In Consultation' ? '<span class="badge bg-info">In Consultation</span>' : 
                                      profile.current_status === 'Pharmacy' ? '<span class="badge bg-secondary">Pharmacy</span>' : 
                                      profile.current_status === 'Completed' ? '<span class="badge bg-success">Completed</span>' :
                                      '<span class="badge bg-secondary">' + profile.current_status + '</span>'}
                                </p>
                            </div>
                        </div>
                        
                        <p class="mt-2 mb-0"><strong>Last Visit:</strong> ${profile.last_visit_date}</p>
                    </div>
                    
                    <hr>
                    
                    <!-- Visit History Tabs -->
                    <ul class="nav nav-tabs mb-3" id="visitTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="visitHistory-tab" data-bs-toggle="tab" href="#visitHistory" role="tab">Visit History (${visitHistory.total})</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="visitHistory" role="tabpanel">
                            <div id="visitHistoryContent"></div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-primary w-100" onclick="createTicketForBeneficiary('${profile.boschma_id}', '${profile.name.replace(/'/g, "\\'")}')">
                            <i class="ti-plus me-1"></i> Create Ticket for this Beneficiary
                        </button>
                    </div>
                `;
                
                profileContent.innerHTML = html;
                // Use numeric/internal id for pagination and future calls to avoid URL-encoding issues
                loadVisitHistory(visitHistory, profile.id);
            };

            window.loadVisitHistory = function(visitHistory, profileId) {
                const visitHistoryContent = document.getElementById('visitHistoryContent');
                
                if (visitHistory.data.length === 0) {
                    visitHistoryContent.innerHTML = '<div class="alert alert-info">No visit history available</div>';
                } else {
                    let html = '<div class="list-group">';
                    
                    visitHistory.data.forEach(visit => {
                        html += `
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <strong>${visit.visit_date}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <strong>Nature:</strong> ${visit.nature_of_visit || 'N/A'}
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <strong>Facility:</strong> ${visit.facility}
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <span class="badge ${visit.status === 'Completed' ? 'bg-success' : visit.status === 'In Progress' ? 'bg-info' : 'bg-warning'}">${visit.status}</span>
                                    </div>
                                </div>
                                ${visit.reason_for_visit ? `<small class="text-muted d-block mt-1"><strong>Reason:</strong> ${visit.reason_for_visit}</small>` : ''}
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    
                    // Add pagination if needed
                    if (visitHistory.last_page > 1) {
                        html += '<nav class="mt-3"><ul class="pagination justify-content-center">';
                        
                        for (let i = 1; i <= visitHistory.last_page; i++) {
                            html += `<li class="page-item ${i === visitHistory.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadVisitHistoryPage(${i}, '${profileId}'); return false;">${i}</a>
                            </li>`;
                        }
                        
                        html += '</ul></nav>';
                    }
                    
                    visitHistoryContent.innerHTML = html;
                }
            };

            window.loadVisitHistoryPage = function(page, profileId) {
                const profilePanel = document.getElementById('profilePanel');
                const profileContent = document.getElementById('profileContent');
                const profileLoader = document.getElementById('profileLoader');
                
                profileContent.style.display = 'none';
                profileLoader.style.display = 'block';

                fetch('/crm/beneficiary/' + encodeURIComponent(profileId) + '/profile?page=' + page, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadVisitHistory(data.visit_history, boschmaId);
                        profileLoader.style.display = 'none';
                        profileContent.style.display = 'block';
                    }
                })
                .catch(error => {
                    profileContent.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    profileLoader.style.display = 'none';
                    profileContent.style.display = 'block';
                });
            };

            window.createTicketForBeneficiary = function(boschmaNo, name) {
                const url = '{{ route("crm.create") }}' + '?boschma_no=' + encodeURIComponent(boschmaNo) + 
                           '&name=' + encodeURIComponent(name);
                window.location.href = url;
            };

            window.slideInPanel = function() {
                const panel = document.getElementById('profilePanel');
                panel.classList.add('slide-in');
            };

            window.closeSidePanel = function() {
                const panel = document.getElementById('profilePanel');
                panel.classList.remove('slide-in');
                setTimeout(() => {
                    panel.style.display = 'none';
                }, 300);
            };
        });
    </script>
@endsection
