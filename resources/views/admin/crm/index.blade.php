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
@endsection
