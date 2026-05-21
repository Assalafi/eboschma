@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <!-- Status Summary Cards -->
        <div class="row mb-3">
            <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-uppercase"
                                    style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                    Total</p>
                                <h3 class="mb-0 font-weight-bold" style="color: #01542B;">
                                    {{ number_format($statusCounts['all']) }}</h3>
                                <small style="color: #01542B;">All Members</small>
                            </div>
                            <div class="stats-icon bg-primary-light">
                                <i class="fe fe-users text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-uppercase"
                                    style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                    Active</p>
                                <h3 class="mb-0 font-weight-bold text-success" style="color: #01542B;">
                                    {{ number_format($statusCounts['active']) }}
                                </h3>
                                <small style="color: #01542B;">Currently Active</small>
                            </div>
                            <div class="stats-icon bg-success-light">
                                <i class="fe fe-check-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-uppercase"
                                    style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                    Pending</p>
                                <h3 class="mb-0 font-weight-bold text-warning" style="color: #01542B;">
                                    {{ number_format($statusCounts['pending']) }}
                                </h3>
                                <small style="color: #01542B;">Awaiting Approval</small>
                            </div>
                            <div class="stats-icon bg-warning-light">
                                <i class="fe fe-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-uppercase"
                                    style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                    Inactive</p>
                                <h3 class="mb-0 font-weight-bold text-danger" style="color: #01542B;">
                                    {{ number_format($statusCounts['inactive']) }}
                                </h3>
                                <small style="color: #01542B;">Deactivated</small>
                            </div>
                            <div class="stats-icon bg-danger-light">
                                <i class="fe fe-slash text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-uppercase"
                                    style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                    Rejected</p>
                                <h3 class="mb-0 font-weight-bold text-secondary" style="color: #01542B;">
                                    {{ number_format($statusCounts['rejected']) }}</h3>
                                <small style="color: #01542B;">Applications Declined</small>
                            </div>
                            <div class="stats-icon bg-secondary-light">
                                <i class="fe fe-x-circle text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h6 class="main-content-label mb-1" style="color: #01542B;">Beneficiaries Management</h6>
                                <p class="card-sub-title" style="color: #01542B;">List of all registered
                                    beneficiaries in the system</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('beneficiaries.upload.form') }}" class="btn btn-outline-primary"
                                    style="border-color: #01542B; color: #01542B;">
                                    <i class="fe fe-upload"></i> Bulk Upload
                                </a>
                                <a href="{{ route('beneficiaries.verify') }}" class="btn btn-primary"
                                    style="background-color: #01542B; border-color: #01542B;">
                                    <i class="fe fe-plus-circle"></i> New Enrollment
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Filter Form -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <form method="GET" action="{{ route('beneficiaries.index') }}" id="filter-form">
                                    <div class="row align-items-end">
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="small mb-1" style="color: #01542B;">Search</label>
                                            <input type="text" name="search" id="search"
                                                class="form-control form-control-sm"
                                                placeholder="Search by ID, Name, Phone..."
                                                value="{{ request('search') }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6 mb-2">
                                            <label class="small mb-1" style="color: #01542B;">Status</label>
                                            <select name="status" id="status" class="form-control form-control-sm">
                                                <option value="">All</option>
                                                <option value="active"
                                                    {{ request('status') == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="inactive"
                                                    {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                                </option>
                                                <option value="pending"
                                                    {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="rejected"
                                                    {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-6 mb-2">
                                            <label class="small mb-1" style="color: #01542B;">Program</label>
                                            <select name="program_id" id="program_id"
                                                class="form-control form-control-sm">
                                                <option value="">All Programs</option>
                                                @foreach ($programs ?? [] as $program)
                                                    <option value="{{ $program->id }}"
                                                        {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                                        {{ $program->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-6 mb-2">
                                            <label class="small mb-1" style="color: #01542B;">Facility</label>
                                            <select name="facility_id" id="facility_id"
                                                class="form-control form-control-sm">
                                                <option value="">All Facilities</option>
                                                @foreach ($facilities as $facility)
                                                    <option value="{{ $facility->id }}"
                                                        {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                                        {{ $facility->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-6 mb-2">
                                            <label class="small mb-1" style="color: #01542B;">Gender</label>
                                            <select name="gender" id="gender" class="form-control form-control-sm">
                                                <option value="">All</option>
                                                <option value="Male"
                                                    {{ request('gender') == 'Male' ? 'selected' : '' }}>Male
                                                </option>
                                                <option value="Female"
                                                    {{ request('gender') == 'Female' ? 'selected' : '' }}>
                                                    Female</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-12 mb-2 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm flex-fill"
                                                style="background-color: #01542B; border-color: #01542B;">
                                                <i class="fe fe-search"></i> Filter
                                            </button>
                                            <div class="btn-group flex-fill">
                                                <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle"
                                                    style="border-color: #01542B; color: #01542B;"
                                                    {{ request()->filled('facility_id') ? 'data-bs-toggle=dropdown aria-expanded=false' : 'disabled' }}
                                                    title="{{ request()->filled('facility_id') ? 'Export filtered results' : 'Please filter by a facility first to enable export' }}">
                                                    <i class="fe fe-download"></i> Export
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button type="button" class="dropdown-item"
                                                        onclick="submitExport('excel')">
                                                        <i class="fe fe-file-text mr-1"></i> Export to Excel
                                                    </button>
                                                    <button type="button" class="dropdown-item"
                                                        onclick="submitExport('pdf')">
                                                        <i class="fe fe-file mr-1"></i> Export to PDF
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if (request()->hasAny(['search', 'status', 'program_id', 'facility_id', 'gender']))
                                        <div class="mt-2">
                                            <a href="{{ route('beneficiaries.index') }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fe fe-x"></i> Clear Filters
                                            </a>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <!-- Bulk Actions Bar -->
                        <div id="bulk-actions" class="card border-0 shadow-sm mb-3 bg-light" style="display: none;">
                            <div class="card-body p-2">
                                <form id="bulk-action-form" method="POST"
                                    action="{{ route('beneficiaries.bulk-action') }}">
                                    @csrf
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="d-flex align-items-center mb-2 mb-md-0">
                                            <span class="badge badge-primary mr-2 px-2 py-1" style="font-size: 0.9rem;">
                                                <span id="selected-count">0</span> Selected
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <select name="action" id="bulk-action-select"
                                                class="form-control form-control-sm mr-2"
                                                style="width: auto; min-width: 180px;" required>
                                                <option value="">Select Action...</option>
                                                <option value="active">Mark as Active</option>
                                                <option value="inactive">Mark as Inactive</option>
                                                <option value="pending">Mark as Pending</option>
                                                <option value="rejected">Mark as Rejected</option>
                                                <option value="delete" class="text-danger">Delete Selected</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary mr-1">
                                                Apply
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="clear-selection">
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="beneficiaries-table"
                                class="table table-bordered table-hover mg-b-0 text-md-nowrap">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="40" class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select-all">
                                                <label class="custom-control-label" for="select-all"></label>
                                            </div>
                                        </th>
                                        <th>BOSCHMA ID</th>
                                        <th>Full Name</th>
                                        <th>Facility</th>
                                        <th>Gender</th>
                                        <th>Phone</th>
                                        <th>Dependents</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($beneficiaries as $beneficiary)
                                        <tr>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input beneficiary-checkbox"
                                                        id="checkbox-{{ $beneficiary->id }}"
                                                        value="{{ $beneficiary->id }}">
                                                    <label class="custom-control-label"
                                                        for="checkbox-{{ $beneficiary->id }}"></label>
                                                </div>
                                            </td>
                                            <td><strong
                                                    class="text-primary">{{ strlen($beneficiary->boschma_no) > 12 ? '...' . substr($beneficiary->boschma_no, -12) : $beneficiary->boschma_no }}</strong>
                                            </td>
                                            <td title="{{ $beneficiary->fullname }}">
                                                {{ strlen($beneficiary->fullname) > 10 ? substr($beneficiary->fullname, 0, 10) . '...' : $beneficiary->fullname }}
                                            </td>
                                            <td>
                                                @if ($beneficiary->facility)
                                                    <span class="badge bg-info"
                                                        title="{{ $beneficiary->facility->name }}">{{ strlen($beneficiary->facility->name) > 20 ? substr($beneficiary->facility->name, 0, 20) . '...' : $beneficiary->facility->name }}</span>
                                                    <small
                                                        class="d-block text-muted">{{ $beneficiary->facility->lga }}</small>
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>{{ $beneficiary->gender }}</td>
                                            <td>{{ $beneficiary->phone_no }}</td>
                                            <td>
                                                @php
                                                    $spouseCount = $beneficiary->spouse ? 1 : 0;
                                                    $childCount = $beneficiary->children->count();
                                                    $totalDependents = $spouseCount + $childCount;
                                                @endphp
                                                {{ $totalDependents }}
                                                @if ($totalDependents > 0)
                                                    <span class="text-muted">
                                                        (@if ($spouseCount)
                                                            (S:1)
                                                        @endif
                                                        @if ($childCount)
                                                            (C:{{ $childCount }})
                                                        @endif)
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($beneficiary->status == 'active')
                                                    <span class="">
                                                        <i class="fe fe-check-circle"></i> Active
                                                    </span>
                                                @elseif ($beneficiary->status == 'pending')
                                                    <span class="">
                                                        <i class="fe fe-clock"></i> Pending
                                                    </span>
                                                @elseif ($beneficiary->status == 'rejected')
                                                    <span class="">
                                                        <i class="fe fe-x-circle"></i> Rejected
                                                    </span>
                                                @else
                                                    <span class="">
                                                        <i class="fe fe-slash"></i> Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('beneficiaries.show', $beneficiary->id) }}"
                                                        class="btn btn-sm btn-primary" title="View">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    <a href="{{ route('beneficiaries.edit', $beneficiary->id) }}"
                                                        class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <a href="{{ route('beneficiaries.id-card.download', $beneficiary->id) }}"
                                                        class="btn btn-sm btn-primary" title="Download ID Card">
                                                        <i class="fe fe-file"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete-beneficiary"
                                                        data-id="{{ $beneficiary->id }}"
                                                        data-boschma="{{ $beneficiary->boschma_no }}"
                                                        data-name="{{ $beneficiary->fullname }}"
                                                        data-url="{{ route('beneficiaries.destroy', $beneficiary->id) }}"
                                                        title="Delete">
                                                        <i class="fe fe-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="fe fe-users" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <p class="mb-0">No beneficiaries found. <a
                                                        href="{{ route('beneficiaries.verify') }}">Create one now</a>.</p>
                                            </td>
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
                                    Showing {{ $beneficiaries->firstItem() ?? 0 }} to
                                    {{ $beneficiaries->lastItem() ?? 0 }}
                                    of {{ $beneficiaries->total() }} results
                                </p>
                            </div>
                            <div class="overflow-auto w-100 w-md-auto">
                                @if ($beneficiaries->hasPages())
                                    <nav aria-label="Beneficiaries pagination">
                                        <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                            {{-- Previous Page Link --}}
                                            @if ($beneficiaries->onFirstPage())
                                                <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $beneficiaries->previousPageUrl() }}"
                                                        rel="prev">Prev</a></li>
                                            @endif

                                            {{-- Pagination Elements with Smart Window --}}
                                            @php
                                                $currentPage = $beneficiaries->currentPage();
                                                $lastPage = $beneficiaries->lastPage();
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
                                                        href="{{ $beneficiaries->url(1) }}">1</a></li>
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
                                                            href="{{ $beneficiaries->url($page) }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endfor

                                            {{-- Last Page --}}
                                            @if ($end < $lastPage)
                                                @if ($end < $lastPage - 1)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $beneficiaries->url($lastPage) }}">{{ $lastPage }}</a>
                                                </li>
                                            @endif

                                            {{-- Next Page Link --}}
                                            @if ($beneficiaries->hasMorePages())
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $beneficiaries->nextPageUrl() }}"
                                                        rel="next">Next</a></li>
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

    <!-- Shared Delete Modal (outside DataTable) -->
    <div class="modal fade" id="delete-beneficiary-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete beneficiary
                        <strong id="delete-boschma"></strong>: <span id="delete-name"></span>?
                    </p>
                    <p class="text-danger">This will also delete all associated dependents and cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="delete-beneficiary-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Stats Cards */
        .stats-card {
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-icon i {
            font-size: 24px;
        }

        .bg-primary-light {
            background-color: rgba(79, 132, 171, 0.1);
        }

        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .bg-secondary-light {
            background-color: rgba(108, 117, 125, 0.1);
        }

        /* Modern Checkboxes */
        .custom-control {
            position: relative;
            display: block;
            min-height: 1.5rem;
            padding-left: 1.5rem;
        }

        .custom-control-label {
            position: relative;
            margin-bottom: 0;
            vertical-align: top;
        }

        .custom-control-label::before {
            position: absolute;
            top: 0.25rem;
            left: -1.5rem;
            display: block;
            width: 1rem;
            height: 1rem;
            pointer-events: none;
            content: "";
            background-color: #fff;
            border: 1px solid #adb5bd;
            border-radius: 4px;
        }

        .custom-control-label::after {
            position: absolute;
            top: 0.25rem;
            left: -1.5rem;
            display: block;
            width: 1rem;
            height: 1rem;
            content: "";
            background: no-repeat 50% / 50% 50%;
        }

        .custom-control-input {
            position: absolute;
            left: 0;
            z-index: -1;
            width: 1rem;
            height: 1.25rem;
            opacity: 0;
        }

        .custom-control-input:checked~.custom-control-label::before {
            color: #fff;
            border-color: #4F84AB;
            background-color: #4F84AB;
        }

        .custom-checkbox .custom-control-input:checked~.custom-control-label::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
        }

        .custom-control-input:focus~.custom-control-label::before {
            box-shadow: 0 0 0 0.2rem rgba(79, 132, 171, 0.25);
        }

        .custom-control-input:indeterminate~.custom-control-label::before {
            border-color: #4F84AB;
            background-color: #4F84AB;
        }

        .custom-checkbox .custom-control-input:indeterminate~.custom-control-label::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='4' viewBox='0 0 4 4'%3e%3cpath stroke='%23fff' d='M0 2h4'/%3e%3c/svg%3e");
        }

        /* Table Improvements */
        .table {
            font-size: 0.9rem;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 12px 8px;
        }

        .table tbody td {
            padding: 10px 8px;
            vertical-align: middle;
        }

        .table-hover tbody tr {
            transition: background-color 0.15s ease;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(79, 132, 171, 0.03);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.4em 0.7em;
            font-size: 0.8rem;
            border-radius: 4px;
        }

        .badge i {
            font-size: 0.85rem;
            margin-right: 3px;
        }

        .badge.bg-info {
            background-color: #17a2b8 !important;
            color: white;
        }

        /* Buttons */
        .btn {
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: #4F84AB;
            border-color: #4F84AB;
        }

        .btn-primary:hover {
            background-color: #3d6a8a;
            border-color: #3d6a8a;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        /* Card Shadows */
        .shadow-sm {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
        }

        .card {
            border-radius: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 10px;
            }

            .table {
                font-size: 0.85rem;
            }
        }
    </style>

    <script>
        function submitExport(format) {
            var form = document.getElementById('filter-form');
            // Remove any previous hidden export input
            var existing = form.querySelector('input[name="export"]');
            if (existing) existing.remove();
            // Add hidden input with the chosen format
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'export';
            input.value = format;
            form.appendChild(input);
            // For PDF, open in new tab
            if (format === 'pdf') {
                form.target = '_blank';
            } else {
                form.target = '';
            }
            form.submit();
            // Clean up so next Filter click works normally
            setTimeout(function() {
                input.remove();
                form.target = '';
            }, 100);
        }

        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#beneficiaries-table').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 8]
                }]
            });

            // Update bulk actions bar visibility and count
            function updateBulkActionsBar() {
                const checkedCount = $('.beneficiary-checkbox:checked').length;
                $('#selected-count').text(checkedCount);

                if (checkedCount > 0) {
                    $('#bulk-actions').slideDown(200);
                } else {
                    $('#bulk-actions').slideUp(200);
                }
            }

            // Select all checkboxes - using event delegation
            $(document).on('change', '#select-all', function() {
                const isChecked = $(this).is(':checked');
                $('.beneficiary-checkbox').each(function() {
                    $(this).prop('checked', isChecked);
                });
                updateBulkActionsBar();
            });

            // Individual checkbox change - using event delegation
            $(document).on('change', '.beneficiary-checkbox', function() {
                updateBulkActionsBar();

                // Update select-all checkbox state
                const totalCheckboxes = $('.beneficiary-checkbox').length;
                const checkedCheckboxes = $('.beneficiary-checkbox:checked').length;

                if (checkedCheckboxes === 0) {
                    $('#select-all').prop('checked', false);
                    $('#select-all').prop('indeterminate', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    $('#select-all').prop('checked', true);
                    $('#select-all').prop('indeterminate', false);
                } else {
                    $('#select-all').prop('checked', false);
                    $('#select-all').prop('indeterminate', true);
                }
            });

            // Clear selection button
            $(document).on('click', '#clear-selection', function() {
                $('.beneficiary-checkbox').prop('checked', false);
                $('#select-all').prop('checked', false);
                $('#select-all').prop('indeterminate', false);
                updateBulkActionsBar();
            });

            // Handle delete button click - populate shared modal
            $(document).on('click', '.btn-delete-beneficiary', function() {
                var url = $(this).data('url');
                var boschma = $(this).data('boschma');
                var name = $(this).data('name');
                $('#delete-boschma').text(boschma);
                $('#delete-name').text(name);
                $('#delete-beneficiary-form').attr('action', url);
                $('#delete-beneficiary-modal').modal('show');
            });

            // Handle bulk action form submission
            $('#bulk-action-form').on('submit', function(e) {
                e.preventDefault();

                const selectedIds = [];
                $('.beneficiary-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Please select at least one beneficiary');
                    return false;
                }

                const action = $('#bulk-action-select').val();
                if (!action) {
                    alert('Please select an action');
                    return false;
                }

                // Confirm action
                let confirmMessage = '';
                if (action === 'delete') {
                    confirmMessage =
                        `Are you sure you want to delete ${selectedIds.length} beneficiary(ies)? This action cannot be undone.`;
                } else {
                    confirmMessage =
                        `Are you sure you want to mark ${selectedIds.length} beneficiary(ies) as ${action}?`;
                }

                if (!confirm(confirmMessage)) {
                    return false;
                }

                // Remove any existing hidden inputs
                $('#bulk-action-form input[name="beneficiary_ids[]"]').remove();

                // Add selected IDs to form as hidden inputs
                selectedIds.forEach(function(id) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'beneficiary_ids[]',
                        value: id
                    }).appendTo('#bulk-action-form');
                });

                // Submit the form
                this.submit();
            });
        });
    </script>
@endsection
