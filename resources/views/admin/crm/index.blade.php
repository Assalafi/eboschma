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
                            <a href="{{ route('crm.create') }}" class="btn btn-primary">
                                <i class="ti-plus"></i> Create Ticket
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
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

    <!-- Floating Softphone Toggle Button -->
    <div id="zohoSoftphoneToggle" class="position-fixed bottom-0 end-0 m-4 shadow-lg rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 60px; height: 60px; cursor: pointer; z-index: 1050; animation: pulse 1.8s infinite;" onclick="toggleZohoSoftphone()">
        <i class="ti-headphone-alt" style="font-size: 1.8rem;"></i>
        <span class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle" style="margin-left: -8px; margin-top: 8px;"></span>
    </div>

    <!-- Slide-out Softphone Panel -->
    <div id="zohoSoftphonePanel" class="position-fixed top-0 end-0 h-100 shadow-lg bg-white" style="width: 340px; transform: translateX(340px); transition: transform 0.3s ease; z-index: 1040; border-top-left-radius: 20px; border-bottom-left-radius: 20px;">
        <div class="d-flex flex-column h-100">
            <!-- Header -->
            <div class="p-3 bg-primary text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: 20px;">
                <h5 class="mb-0 fw-bold"><i class="ti-mobile me-2"></i> Zoho Voice Softphone</h5>
                <button type="button" class="btn-close btn-close-white" style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;" onclick="toggleZohoSoftphone()">&times;</button>
            </div>
            
            <!-- Body -->
            <div class="flex-grow-1 p-3 overflow-auto">
                <div class="text-center mb-3">
                    <span class="badge bg-success"><i class="ti-check"></i> Zoho Line Connected</span>
                </div>
                
                <!-- Display -->
                <div class="bg-light rounded p-2 mb-3 text-center">
                    <input type="text" class="form-control text-center fw-bold fs-4 bg-transparent border-0" id="softphoneDisplay" placeholder="Enter phone..." readonly>
                    <small class="text-muted" id="softphoneStatus">Ready for Outbound Calling</small>
                </div>
                
                <!-- Keypad -->
                <div class="row g-2 mb-3">
                    @foreach(['1','2','3','4','5','6','7','8','9','*','0','#'] as $key)
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 py-3 fw-bold rounded-3 keypad-btn" onclick="pressSoftphoneKey('{{ $key }}')">{{ $key }}</button>
                        </div>
                    @endforeach
                </div>
                
                <!-- Actions -->
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <button class="btn btn-danger btn-sm px-3" onclick="clearSoftphoneDisplay()"><i class="ti-back-left"></i> Clear</button>
                    <button class="btn btn-success btn-lg rounded-circle p-3 d-flex align-items-center justify-content-center shadow" style="width: 54px; height: 54px;" onclick="softphoneCall()"><i class="ti-mobile" style="font-size: 1.5rem;"></i></button>
                </div>

                <hr>

                <!-- Demo Tools -->
                <div class="card bg-info bg-opacity-10 border border-info rounded p-2 mb-3">
                    <h6 class="fw-bold text-info mb-1"><i class="ti-settings"></i> Simulation tools</h6>
                    <small class="text-muted d-block mb-2">Simulate an incoming support call pop-up instantly.</small>
                    <button class="btn btn-info btn-xs w-100" onclick="simulateIncomingCall()"><i class="ti-bell"></i> Simulate Call-Pop</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Incoming Call Pop-up Overlay -->
    <div id="zohoCallPop" class="position-fixed bottom-0 start-0 m-4 shadow-lg bg-white border border-success" style="width: 320px; border-radius: 15px; transform: translateY(400px); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 1060; border-left: 6px solid #198754 !important;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-success animate-blink"><i class="ti-bell"></i> INCOMING CALL</span>
                <button type="button" class="btn-close" style="background: none; border: none; font-size: 1rem; color: #6c757d; cursor: pointer;" onclick="closeCallPop()">&times;</button>
            </div>
            
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti-user text-success" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0" id="callPopName">Aisha Ibrahim</h6>
                    <small class="text-muted" id="callPopDetails">BO/2026/0488 • Principal</small>
                </div>
            </div>

            <div class="text-center bg-light rounded p-2 mb-3">
                <span class="small fw-mono text-dark" id="callPopPhone">08092283733</span>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-outline-danger" onclick="closeCallPop()">Decline</button>
                <a href="#" id="callPopAcceptBtn" class="btn btn-sm btn-success px-3 fw-bold"><i class="ti-headphone-alt me-1"></i> Answer</a>
            </div>
        </div>
    </div>

    <style>
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

    <script>
        let softphoneOpen = false;

        window.toggleZohoSoftphone = function() {
            const panel = document.getElementById('zohoSoftphonePanel');
            softphoneOpen = !softphoneOpen;
            if (softphoneOpen) {
                panel.style.transform = 'translateX(0)';
            } else {
                panel.style.transform = 'translateX(340px)';
            }
        };

        window.pressSoftphoneKey = function(key) {
            const display = document.getElementById('softphoneDisplay');
            display.value += key;
        };

        window.clearSoftphoneDisplay = function() {
            document.getElementById('softphoneDisplay').value = '';
        };

        window.softphoneCall = function() {
            const phone = document.getElementById('softphoneDisplay').value;
            if (!phone) {
                alert('Please enter a valid phone number first!');
                return;
            }
            
            document.getElementById('softphoneStatus').textContent = 'Dialing ' + phone + '...';
            
            setTimeout(() => {
                document.getElementById('softphoneStatus').textContent = 'Ready for Outbound Calling';
                window.location.href = `/crm/create?phone=${encodeURIComponent(phone)}`;
            }, 1500);
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
            
            const pop = document.getElementById('zohoCallPop');
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
            const pop = document.getElementById('zohoCallPop');
            pop.style.transform = 'translateY(400px)';
        };
    </script>
@endsection
