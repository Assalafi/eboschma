@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
    <div class="container-fluid ref-wrap">

        {{-- PAGE HEAD --}}
        <div class="ref-pagehead d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <div class="ref-eyebrow">
                    <a href="{{ route('dashboard') }}">Dashboard</a><span>/</span><span>Staff</span>
                </div>
                <h1 class="ref-title">Staff Management</h1>
                <p class="ref-subtitle">Manage system staff members, their roles and account access</p>
            </div>
            <div>
                @if (auth('staff')->user()?->can('staff.create'))
                    <a href="{{ route('staff.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus-circle me-1"></i> Add New Staff
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- STATS --}}
        <div class="ref-stats">
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-primary"><i class="fe fe-users"></i></div>
                <div>
                    <div class="ref-stat-label">Total Staff</div>
                    <div class="ref-stat-number">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="ref-stat-note">All staff members</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-success"><i class="fe fe-user-check"></i></div>
                <div>
                    <div class="ref-stat-label">Active</div>
                    <div class="ref-stat-number">{{ number_format($stats['active'] ?? 0) }}</div>
                    <div class="ref-stat-note">Can sign in</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-danger"><i class="fe fe-user-x"></i></div>
                <div>
                    <div class="ref-stat-label">Inactive</div>
                    <div class="ref-stat-number">{{ number_format($stats['inactive'] ?? 0) }}</div>
                    <div class="ref-stat-note">Access disabled</div>
                </div>
            </div>
            <div class="ref-stat-card">
                <div class="ref-stat-icon is-info"><i class="fe fe-shield"></i></div>
                <div>
                    <div class="ref-stat-label">With Roles</div>
                    <div class="ref-stat-number">{{ number_format($stats['with_roles'] ?? 0) }}</div>
                    <div class="ref-stat-note">Assigned roles</div>
                </div>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="ref-panel">
            <div class="ref-panel-head">
                <h3><i class="fe fe-filter"></i> Filters</h3>
                <span class="ref-count">{{ number_format($staff->total()) }} results</span>
            </div>
            <div class="ref-panel-body">
                <form method="GET" action="{{ route('staff.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label ref-field-label">Search</label>
                            <div class="ref-input-icon">
                                <i class="fe fe-search"></i>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Name, email or phone..." value="{{ $search }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ref-field-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" @selected($status === 'active')>Active</option>
                                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-search"></i> Search
                            </button>
                            @if ($search || $status)
                                <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
                                    <i class="fe fe-rotate-ccw"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="ref-panel ref-table-panel">
            <div class="ref-panel-head">
                <h3><i class="fe fe-list"></i> Staff Directory</h3>
                @if ($search || $status)
                    <span class="ref-count">
                        {{ $staff->total() }} match{{ $staff->total() === 1 ? '' : 'es' }}
                    </span>
                @endif
            </div>
            <div class="ref-panel-body p-0">
                <div class="table-responsive">
                    <table class="table ref-dt mb-0">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($staff as $member)
                                <tr>
                                    <td>{{ $loop->iteration + ($staff->currentPage() - 1) * $staff->perPage() }}</td>
                                    <td><strong>{{ $member->fullname }}</strong></td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->phone ?: '-' }}</td>
                                    <td>
                                        @if ($member->roles->count() > 0)
                                            @foreach ($member->roles as $role)
                                                <span class="st-role"><i class="fe fe-shield"></i> {{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted"><i class="fe fe-alert-circle"></i> No Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($member->is_active)
                                            <span class="st-badge is-active"><span class="st-dot"></span>Active</span>
                                        @else
                                            <span class="st-badge is-inactive"><span class="st-dot"></span>Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">
                                        <i class="fe fe-calendar"></i> {{ $member->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="st-actions">
                                            @if (auth('staff')->user()?->can('staff.view'))
                                                <a href="{{ route('staff.show', $member) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View details">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                            @endif

                                            @if (auth('staff')->user()?->can('staff.edit'))
                                                <a href="{{ route('staff.edit', $member) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>

                                                @if (auth('staff')->id() !== $member->id)
                                                    <form action="{{ route('staff.toggle-status', $member) }}" method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('{{ $member->is_active ? 'Deactivate' : 'Activate' }} {{ $member->fullname }}?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="btn btn-sm {{ $member->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                            title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fe {{ $member->is_active ? 'fe-user-x' : 'fe-user-check' }}"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                        title="You cannot deactivate your own account">
                                                        <i class="fe fe-lock"></i>
                                                    </button>
                                                @endif
                                            @endif

                                            @if (auth('staff')->user()?->can('staff.delete'))
                                                @if (auth('staff')->id() !== $member->id)
                                                    <form action="{{ route('staff.destroy', $member) }}" method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Delete {{ $member->fullname }}? This cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fe fe-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fe fe-users" style="font-size: 2.5rem; opacity: .3;"></i>
                                        <p class="mb-0 mt-2">No staff members found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($staff->hasPages() || $staff->total() > 0)
                <div class="ref-pagination">
                    <div class="ref-pagination-info">
                        Showing {{ $staff->firstItem() ?? 0 }} to {{ $staff->lastItem() ?? 0 }}
                        of {{ number_format($staff->total()) }} results
                    </div>
                    @if ($staff->hasPages())
                        <nav aria-label="Staff pagination">
                            <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                @if ($staff->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $staff->previousPageUrl() }}" rel="prev">Prev</a></li>
                                @endif

                                @php
                                    $currentPage = $staff->currentPage();
                                    $lastPage = $staff->lastPage();
                                    $onEachSide = 2;
                                    $start = max(1, $currentPage - $onEachSide);
                                    $end = min($lastPage, $currentPage + $onEachSide);
                                    if ($currentPage <= $onEachSide + 1) {
                                        $end = min($lastPage, ($onEachSide * 2) + 2);
                                    }
                                    if ($currentPage >= $lastPage - $onEachSide) {
                                        $start = max(1, $lastPage - ($onEachSide * 2) - 1);
                                    }
                                @endphp

                                @if ($start > 1)
                                    <li class="page-item"><a class="page-link" href="{{ $staff->url(1) }}">1</a></li>
                                    @if ($start > 2)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif

                                @for ($page = $start; $page <= $end; $page++)
                                    @if ($page == $currentPage)
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $staff->url($page) }}">{{ $page }}</a></li>
                                    @endif
                                @endfor

                                @if ($end < $lastPage)
                                    @if ($end < $lastPage - 1)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                    <li class="page-item"><a class="page-link" href="{{ $staff->url($lastPage) }}">{{ $lastPage }}</a></li>
                                @endif

                                @if ($staff->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $staff->nextPageUrl() }}" rel="next">Next</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --ref-primary: #016634;
            --ref-primary-dark: #014525;
            --ref-success: #10b981;
            --ref-warning: #f59e0b;
            --ref-info: #3b82f6;
            --ref-danger: #ef4444;
            --ref-border: #e2e8f0;
            --ref-muted: #64748b;
            --ref-ink: #0f172a;
        }

        .ref-wrap { padding-top: .25rem; }

        .ref-pagehead { margin-bottom: 18px; }
        .ref-eyebrow { font-size: 12px; letter-spacing: .05em; text-transform: uppercase; color: var(--ref-muted); font-weight: 600; margin-bottom: 6px; }
        .ref-eyebrow a { color: var(--ref-primary); text-decoration: none; }
        .ref-eyebrow span { margin: 0 6px; opacity: .6; }
        .ref-title { font-size: 24px; font-weight: 700; color: var(--ref-ink); margin: 0 0 4px; }
        .ref-subtitle { color: var(--ref-muted); font-size: 13.5px; margin: 0; }

        .ref-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .ref-stat-card {
            background: #fff; border: 1px solid var(--ref-border); border-radius: 14px;
            padding: 16px 18px; display: flex; align-items: center; gap: 14px;
            box-shadow: 0 1px 3px rgba(15,23,42,.04); transition: transform .15s ease, box-shadow .15s ease;
        }
        .ref-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.08); }
        .ref-stat-icon {
            width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .ref-stat-icon.is-primary { background: rgba(1,102,52,.10); color: var(--ref-primary); }
        .ref-stat-icon.is-success { background: rgba(16,185,129,.12); color: #059669; }
        .ref-stat-icon.is-info { background: rgba(59,130,246,.12); color: #2563eb; }
        .ref-stat-icon.is-danger { background: rgba(239,68,68,.12); color: #dc2626; }
        .ref-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--ref-muted); font-weight: 700; }
        .ref-stat-number { font-size: 24px; font-weight: 700; color: var(--ref-ink); line-height: 1.15; }
        .ref-stat-note { font-size: 12px; color: var(--ref-muted); }

        .ref-panel { background: #fff; border: 1px solid var(--ref-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.04); margin-bottom: 18px; overflow: hidden; }
        .ref-panel-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--ref-border); background: #f8fafc; }
        .ref-panel-head h3 { margin: 0; font-size: 14px; font-weight: 700; color: var(--ref-ink); display: flex; align-items: center; gap: 8px; }
        .ref-panel-head h3 i { color: var(--ref-primary); }
        .ref-panel-body { padding: 18px; }
        .ref-count { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #059669; background: rgba(16,185,129,.12); padding: 3px 10px; border-radius: 999px; }

        .ref-field-label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--ref-muted); font-weight: 700; margin-bottom: 6px; }
        .ref-input-icon { position: relative; }
        .ref-input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; }
        .ref-input-icon .form-control { padding-left: 32px; }

        .ref-dt { border-collapse: collapse !important; font-size: 13px; }
        .ref-dt thead th {
            background: #f8fafc !important; color: var(--ref-muted) !important;
            text-transform: uppercase; letter-spacing: .04em; font-size: 11px; font-weight: 700;
            padding: 12px 16px !important; border-bottom: 1px solid var(--ref-border) !important;
            border-top: none !important; white-space: nowrap;
        }
        .ref-dt tbody td { padding: 13px 16px !important; border-bottom: 1px solid #f1f5f9 !important; vertical-align: middle; }
        .ref-dt tbody tr:hover td { background: #f8fafc !important; }
        .ref-dt tbody tr:last-child td { border-bottom: none !important; }

        .st-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 700; letter-spacing: .03em;
            padding: 4px 10px; border-radius: 999px; white-space: nowrap;
        }
        .st-badge .st-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .st-badge.is-active { background: rgba(16,185,129,.12); color: #047857; }
        .st-badge.is-active .st-dot { background: #10b981; }
        .st-badge.is-inactive { background: rgba(239,68,68,.10); color: #b91c1c; }
        .st-badge.is-inactive .st-dot { background: #ef4444; }

        .st-role {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 600; color: var(--ref-primary);
            background: rgba(1,102,52,.08); padding: 3px 9px;
            border-radius: 999px; margin: 2px 4px 2px 0;
        }

        .st-actions { display: inline-flex; align-items: center; gap: 6px; }
        .st-actions .btn { display: inline-flex; align-items: center; justify-content: center; }
        .st-actions .btn-outline-primary { color: var(--ref-primary); border-color: rgba(1,102,52,.35); }
        .st-actions .btn-outline-primary:hover { background: var(--ref-primary); border-color: var(--ref-primary); color: #fff; }
        .st-actions .btn-outline-success { color: #059669; border-color: rgba(16,185,129,.4); }
        .st-actions .btn-outline-success:hover { background: #059669; border-color: #059669; color: #fff; }
        .st-actions .btn-outline-warning { color: #b45309; border-color: rgba(245,158,11,.45); }
        .st-actions .btn-outline-warning:hover { background: #f59e0b; border-color: #f59e0b; color: #fff; }
        .st-actions .btn-outline-danger { color: #dc2626; border-color: rgba(239,68,68,.4); }
        .st-actions .btn-outline-danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }

        .ref-pagination {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 12px; padding: 14px 18px; border-top: 1px solid var(--ref-border);
        }
        .ref-pagination-info { font-size: 13px; color: var(--ref-muted); }
        .ref-pagination .page-link {
            color: var(--ref-primary); border: 1px solid var(--ref-border);
            border-radius: 8px; margin: 0 2px; padding: 5px 11px;
        }
        .ref-pagination .page-link:hover { background: rgba(1,102,52,.08); color: var(--ref-primary); }
        .ref-pagination .page-item.active .page-link {
            background: var(--ref-primary); border-color: var(--ref-primary); color: #fff;
        }
        .ref-pagination .page-item.disabled .page-link { color: #94a3b8; }

        .btn-primary { background-color: var(--ref-primary); border-color: var(--ref-primary); }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--ref-primary-dark); border-color: var(--ref-primary-dark); }

        @media (max-width: 640px) {
            .ref-panel-body { padding: 14px; }
            .ref-dt thead th, .ref-dt tbody td { padding: 10px 12px !important; }
            .ref-pagination { flex-direction: column; align-items: flex-start; }
        }
    </style>
@endpush
