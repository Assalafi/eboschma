@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <!-- Enhanced Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-primary bg-gradient rounded-circle p-3 me-3">
                        <i class="fe fe-download text-white" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 class="page-title mb-1 fw-bold">Generate ID Cards</h4>
                        <p class="text-muted mb-0">Background generation system for large-scale ID card production</p>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">
                    <i class="fe fe-arrow-left me-1"></i> Back to Beneficiaries
                </a>
            </div>
        </div>

        <!-- Enhanced Active Jobs Alert -->
        @if ($activeJobs->count() > 0)
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert"
                style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border-left: 4px solid #2196f3;">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-primary">{{ $activeJobs->count() }} active job(s)</strong> currently processing.
                        <a href="#" onclick="toggleJobHistory()" class="text-decoration-none fw-semibold">View
                            progress</a>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Enhanced Generation Options -->
            <div class="col-lg-5">
                <!-- Quick Generation Options -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary bg-gradient text-white border-0">
                        <div class="d-flex align-items-center">
                            <i class="fe fe-zap me-2" style="font-size: 1.2rem;"></i>
                            <h5 class="mb-0 fw-semibold">Quick Generation</h5>
                        </div>
                        <p class="mb-0 mt-1 small opacity-75">Select generation type to begin</p>
                    </div>
                    <div class="card-body p-4">
                        <form id="quickGenerationForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted mb-3">Generation Type</label>
                                <div class="d-grid gap-2">
                                    <button type="button"
                                        class="btn btn-outline-primary text-start p-3 border-2 generation-btn"
                                        onclick="selectGenerationType('all')">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-gradient rounded-circle p-2 me-3">
                                                <i class="fe fe-users text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">All Beneficiaries</strong>
                                                <small class="text-muted">Generate ID cards for all beneficiaries</small>
                                            </div>
                                        </div>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-primary text-start p-3 border-2 generation-btn"
                                        onclick="selectGenerationType('facility')">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-gradient rounded-circle p-2 me-3">
                                                <i class="fe fe-home text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">By Facility</strong>
                                                <small class="text-muted">Generate for specific facility</small>
                                            </div>
                                        </div>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-primary text-start p-3 border-2 generation-btn"
                                        onclick="selectGenerationType('status')">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-gradient rounded-circle p-2 me-3">
                                                <i class="fe fe-check-circle text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">By Status</strong>
                                                <small class="text-muted">Generate by beneficiary status</small>
                                            </div>
                                        </div>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-primary text-start p-3 border-2 generation-btn"
                                        onclick="selectGenerationType('workplace')">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-gradient rounded-circle p-2 me-3">
                                                <i class="fe fe-briefcase text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">By Workplace</strong>
                                                <small class="text-muted">Generate by workplace</small>
                                            </div>
                                        </div>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-success text-start p-3 border-2 generation-btn"
                                        onclick="selectGenerationType('custom_selection')">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-gradient rounded-circle p-2 me-3">
                                                <i class="fe fe-check-square text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">Custom Selection</strong>
                                                <small class="text-muted">Select specific beneficiaries</small>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Enhanced Custom Selection -->
                <div class="card shadow-sm mb-4 border-0" id="customSelectionCard" style="display: none;">
                    <div class="card-header bg-success bg-gradient text-white border-0">
                        <div class="d-flex align-items-center">
                            <i class="fe fe-check-square me-2" style="font-size: 1.2rem;"></i>
                            <h5 class="mb-0 fw-semibold">Custom Selection</h5>
                        </div>
                        <p class="mb-0 mt-1 small opacity-75">Review and generate for selected beneficiaries</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="customTitle" class="form-label fw-semibold text-muted">Job Title</label>
                            <input type="text" class="form-control form-control-lg" id="customTitle"
                                placeholder="Enter job title" style="border-radius: 10px;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-muted mb-3">
                                <i class="fe fe-users me-1"></i> Selected Beneficiaries
                                <span class="badge bg-success ms-2" id="selectedBadge">0</span>
                            </label>
                            <div class="border rounded-3 p-3"
                                style="max-height: 250px; overflow-y: auto; background: #f8f9fa;">
                                <div id="selectedBeneficiariesList" class="small text-muted">
                                    No beneficiaries selected
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg" onclick="startCustomGeneration()"
                                id="startCustomBtn" disabled style="border-radius: 10px;">
                                <i class="fe fe-play me-2"></i>Start Generation
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearCustomSelection()"
                                style="border-radius: 10px;">
                                <i class="fe fe-x me-2"></i>Clear Selection
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Beneficiaries List & Job History -->
            <div class="col-lg-7">
                <!-- Enhanced Filters Section -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="fe fe-filter me-2 text-primary"></i>
                            <h6 class="mb-0 fw-semibold">Filter Beneficiaries</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label fw-semibold text-muted small">Status</label>
                                <select class="form-select form-select-sm" id="statusFilter" name="status"
                                    data-placeholder="All Status" style="border-radius: 8px;">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="facilityFilter"
                                    class="form-label fw-semibold text-muted small">Facility</label>
                                <select class="form-select form-select-sm" id="facilityFilter" name="facility_id"
                                    data-placeholder="All Facilities" style="border-radius: 8px;">
                                    <option value="">All Facilities</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}"
                                            {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                            {{ $facility->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="workplaceFilter" class="form-label fw-semibold text-muted small">Place of
                                    Work</label>
                                <select class="form-select form-select-sm" id="workplaceFilter" name="workplace"
                                    data-placeholder="All Workplaces" style="border-radius: 8px;">
                                    <option value="">All Workplaces</option>
                                    @foreach ($workplaces as $workplace)
                                        <option value="{{ $workplace }}"
                                            {{ request('workplace') == $workplace ? 'selected' : '' }}>{{ $workplace }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold text-muted small">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px;">
                                        <i class="fe fe-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="clearFilters()" style="border-radius: 8px;">
                                        <i class="fe fe-x"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Enhanced Selection Actions -->
                <div class="card shadow-sm mb-4 border-0" id="selectionActions" style="display: none;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-gradient rounded-circle p-2 me-3">
                                    <i class="fe fa-check text-white"></i>
                                </div>
                                <div>
                                    <span class="text-muted fw-semibold">Selected: </span>
                                    <span class="badge bg-primary fs-6" id="selectedCount">0</span>
                                    <span class="text-muted fw-semibold"> beneficiaries</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()"
                                    style="border-radius: 8px;">
                                    <i class="fe fe-check-square me-1"></i> Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="clearSelection()" style="border-radius: 8px;">
                                    <i class="fe fe-square me-1"></i> Clear
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="showCustomSelection()"
                                    id="generateBtn" disabled style="border-radius: 8px;">
                                    <i class="fe fe-download me-1"></i> Generate (<span id="generateCount">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Beneficiaries List -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fe fe-users me-2 text-primary"></i>
                                <h6 class="mb-0 fw-semibold">Beneficiaries</h6>
                            </div>
                            <div class="text-muted small">
                                <i class="fe fe-database me-1"></i>
                                {{ $beneficiaries->total() }} total records
                            </div>
                        </div>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;" class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="beneficiariesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                            </div>
                                        </th>
                                        <th class="fw-semibold">BOSCHMA No</th>
                                        <th class="fw-semibold">Full Name</th>
                                        <th class="fw-semibold">Gender</th>
                                        <th class="fw-semibold">Facility</th>
                                        <th class="fw-semibold">Status</th>
                                        <th class="fw-semibold">Dependants</th>
                                        <th class="fw-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($beneficiaries as $beneficiary)
                                        <tr class="beneficiary-row">
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input beneficiary-checkbox"
                                                        value="{{ $beneficiary->id }}"
                                                        data-name="{{ $beneficiary->fullname }}"
                                                        data-boschma-no="{{ $beneficiary->boschma_no }}">
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-info bg-gradient text-white px-2 py-1">{{ $beneficiary->boschma_no }}</span>
                                            </td>
                                            <td class="fw-medium">{{ $beneficiary->fullname }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <i class="fe fe-user me-1"></i>{{ $beneficiary->gender }}
                                                </span>
                                            </td>
                                            <td class="small">{{ $beneficiary->facility->name ?? 'N/A' }}</td>
                                            <td>
                                                @if ($beneficiary->status == 'active')
                                                    <span class="badge bg-success bg-gradient">
                                                        <i class="fe fe-check-circle me-1"></i>Active
                                                    </span>
                                                @elseif($beneficiary->status == 'pending')
                                                    <span class="badge bg-warning">
                                                        <i class="fe fe-clock me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fe fe-slash me-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-gradient text-white">
                                                    <i class="fe fa-users me-1"></i>
                                                    {{ ($beneficiary->spouse ? 1 : 0) + $beneficiary->children->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('beneficiaries.show', $beneficiary->id) }}"
                                                        class="btn btn-outline-primary" title="View"
                                                        style="border-radius: 6px;">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    <a href="{{ route('beneficiaries.id-card.download', $beneficiary->id) }}"
                                                        class="btn btn-outline-success" title="Download ID Card"
                                                        style="border-radius: 6px;">
                                                        <i class="fe fe-download"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-center">
                                                    <i class="fe fe-users fe-3x text-muted mb-3"></i>
                                                    <p class="text-muted mb-0 fw-semibold">No beneficiaries found</p>
                                                    <small class="text-muted">Try adjusting your filters</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Enhanced Pagination -->
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 gap-3 p-3 border-top">
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

        <!-- Enhanced Job History Section -->
        <div class="card shadow-sm mt-4 border-0" id="jobHistorySection">
            <div class="card-header bg-dark bg-gradient text-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fe fe-clock me-2" style="font-size: 1.2rem;"></i>
                        <h5 class="mb-0 fw-semibold">Generation History</h5>
                    </div>
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="toggleJobHistory()"
                        style="border-radius: 8px;">
                        <i class="fe fe-x"></i> Hide
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Job ID</th>
                                <th class="fw-semibold">Title</th>
                                <th class="fw-semibold">Type</th>
                                <th class="fw-semibold">Criteria</th>
                                <th class="fw-semibold">Status</th>
                                <th class="fw-semibold">Progress</th>
                                <th class="fw-semibold">File</th>
                                <th class="fw-semibold">Created</th>
                                <th class="fw-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jobHistoryTable">
                            @forelse($jobHistory as $job)
                                <tr>
                                    <td><code class="bg-light px-2 py-1 rounded">{{ $job->job_id }}</code></td>
                                    <td class="fw-medium">{{ $job->title }}</td>
                                    <td>
                                        <span
                                            class="badge bg-info bg-gradient text-white">{{ $job->generation_type_label }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $job->criteria_description }}</td>
                                    <td>{!! $job->status_badge !!}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success bg-gradient" role="progressbar"
                                                style="width: {{ $job->progress_percentage }}%">
                                                {{ $job->progress_percentage }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i
                                                class="fe fe-check me-1"></i>{{ $job->processed_records }}/{{ $job->total_records }}
                                            @if ($job->failed_records > 0)
                                                <span class="text-danger">({{ $job->failed_records }} failed)</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if ($job->is_downloadable)
                                            <a href="{{ route('beneficiaries.bulk-id-cards.download-file', $job->job_id) }}"
                                                class="btn btn-sm btn-success" title="Download"
                                                style="border-radius: 6px;">
                                                <i class="fe fe-download"></i>
                                                <small class="ms-1">{{ $job->formatted_file_size }}</small>
                                            </a>
                                        @else
                                            <span class="text-muted small">{{ $job->formatted_file_size }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        <i class="fe fe-calendar me-1"></i>{{ $job->created_at->format('M j, H:i') }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if (in_array($job->status, ['pending', 'processing']))
                                                <button type="button" class="btn btn-outline-warning"
                                                    style="border-radius: 6px;"
                                                    onclick="cancelJob('{{ $job->job_id }}')" title="Cancel">
                                                    <i class="fe fe-x"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-primary"
                                                style="border-radius: 6px;"
                                                onclick="refreshJobStatus('{{ $job->job_id }}')" title="Refresh">
                                                <i class="fe fe-refresh-cw"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <div class="text-center">
                                            <i class="fe fe-inbox fe-2x mb-2"></i>
                                            <p class="mb-0">No generation history found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Generation Modal -->
    <div class="modal fade" id="generationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary bg-gradient text-white border-0">
                    <div class="d-flex align-items-center">
                        <i class="fe fe-play me-2" style="font-size: 1.2rem;"></i>
                        <h5 class="modal-title fw-semibold">Start ID Card Generation</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="generationForm">
                        <div class="mb-4">
                            <label for="jobTitle" class="form-label fw-semibold text-muted">Job Title</label>
                            <input type="text" class="form-control form-control-lg" id="jobTitle" required
                                placeholder="Enter a descriptive title for this generation job"
                                style="border-radius: 10px;">
                            <small class="text-muted">This title will help you identify this generation job in the
                                history</small>
                        </div>

                        <div id="facilityOptions" style="display: none;">
                            <div class="mb-4">
                                <label for="facilitySelect" class="form-label fw-semibold text-muted">
                                    <i class="fe fe-home me-1"></i>Select Facility
                                </label>
                                <select class="form-select form-select-lg" id="facilitySelect" required
                                    data-placeholder="Choose a facility" style="border-radius: 10px;">
                                    <option value="">Choose a facility</option>
                                    @foreach ($facilities as $facility)
                                        <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="statusOptions" style="display: none;">
                            <div class="mb-4">
                                <label for="statusSelect" class="form-label fw-semibold text-muted">
                                    <i class="fe fe-check-circle me-1"></i>Select Status
                                </label>
                                <select class="form-select form-select-lg" id="statusSelect" required
                                    data-placeholder="Choose status" style="border-radius: 10px;">
                                    <option value="">Choose status</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div id="workplaceOptions" style="display: none;">
                            <div class="mb-4">
                                <label for="workplaceSelect" class="form-label fw-semibold text-muted">
                                    <i class="fe fe-briefcase me-1"></i>Select Workplace
                                </label>
                                <select class="form-select form-select-lg" id="workplaceSelect" required
                                    data-placeholder="Choose a workplace" style="border-radius: 10px;">
                                    <option value="">Choose a workplace</option>
                                    @foreach ($workplaces as $workplace)
                                        <option value="{{ $workplace }}">{{ $workplace }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select from available workplaces</small>
                            </div>
                        </div>

                        <input type="hidden" id="generationType" name="generation_type">
                    </form>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="border-radius: 8px;">Cancel</button>
                    <button type="button" class="btn btn-primary btn-lg" onclick="startGeneration()"
                        style="border-radius: 8px;">
                        <i class="fe fe-play me-2"></i>Start Generation
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Enhanced Checkbox Styles */
        .beneficiary-checkbox:checked {
            background-color: #016634;
            border-color: #016634;
        }

        .form-check-input:checked {
            background-color: #016634;
            border-color: #016634;
        }

        /* Enhanced Table Styles */
        .table tbody tr:hover {
            background-color: rgba(1, 102, 52, 0.05);
            transition: background-color 0.3s ease;
        }

        .beneficiary-row {
            transition: all 0.3s ease;
        }

        .beneficiary-row:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Badge Styles */
        .badge {
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Enhanced Button Styles */
        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-group-sm>.btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Enhanced Progress Styles */
        .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            background-color: #016634;
            transition: width 0.6s ease;
        }

        /* Enhanced Generation Button Styles */
        .generation-btn {
            transition: all 0.3s ease;
            border-radius: 12px !important;
        }

        .generation-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-color: #016634 !important;
            background-color: rgba(1, 102, 52, 0.05) !important;
        }

        /* Enhanced Card Styles */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }

        /* Enhanced Modal Styles */
        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
        }

        .modal-footer {
            border-radius: 0 0 15px 15px;
        }

        /* Enhanced Alert Styles */
        .alert {
            border-radius: 10px;
        }

        /* Enhanced Form Control Styles */
        .form-control,
        .form-select {
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #016634;
            box-shadow: 0 0 0 0.2rem rgba(1, 102, 52, 0.25);
        }

        /* Job Status Refresh Animation */
        .job-status-refresh {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Enhanced Loading States */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Enhanced Selection Badge */
        #selectedBadge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(1, 102, 52, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(1, 102, 52, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(1, 102, 52, 0);
            }
        }

        /* Enhanced Responsive Design */
        @media (max-width: 768px) {
            .generation-btn {
                padding: 1rem !important;
            }

            .btn-group-sm>.btn {
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        let selectedBeneficiaries = new Set();
        let currentGenerationType = null;
        let refreshInterval = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectionUI();
            initializeEventListeners();
            initializeSearchableSelects();

            // Start auto-refresh for active jobs
            if ({{ $activeJobs->count() }} > 0) {
                startAutoRefresh();
            }
        });

        function initializeSearchableSelects() {
            // Initialize all searchable selects
            document.querySelectorAll('.searchable-select').forEach(select => {
                const placeholder = select.dataset.placeholder || 'Search...';

                // Skip if already initialized
                if (select.dataset.searchableInitialized) {
                    return;
                }

                // Mark as initialized
                select.dataset.searchableInitialized = 'true';

                // Add search input before the select
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.className = 'form-control mb-2';
                searchInput.placeholder = placeholder;
                searchInput.style.fontSize = '0.875rem';

                // Wrap select in a container
                const container = document.createElement('div');
                container.style.position = 'relative';
                select.parentNode.insertBefore(container, select);
                container.appendChild(searchInput);
                container.appendChild(select);

                // Store original options
                const originalOptions = Array.from(select.options);

                // Add search functionality
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    // Hide/show options based on search
                    Array.from(select.options).forEach(option => {
                        if (option.value === '' || option.text.toLowerCase().includes(searchTerm)) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // If current selection is hidden, clear it
                    if (select.value && select.options[select.selectedIndex].style.display === 'none') {
                        select.value = '';
                        searchInput.dataset.selectedValue = '';
                    }
                });

                // Store selected value when changed
                select.addEventListener('change', function() {
                    searchInput.dataset.selectedValue = this.value;
                });
            });
        }

        function initializeEventListeners() {
            // Select all checkbox
            document.getElementById('selectAllCheckbox').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.beneficiary-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    if (this.checked) {
                        selectedBeneficiaries.add(checkbox.value);
                    } else {
                        selectedBeneficiaries.delete(checkbox.value);
                    }
                });
                updateSelectionUI();
            });

            // Individual checkboxes
            document.querySelectorAll('.beneficiary-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedBeneficiaries.add(this.value);
                    } else {
                        selectedBeneficiaries.delete(this.value);
                    }
                    updateSelectionUI();
                });
            });

            // Filter form
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const params = new URLSearchParams(formData);
                window.location.href = '{{ route('beneficiaries.bulk-id-cards') }}?' + params.toString();
            });
        }

        function selectGenerationType(type) {
            currentGenerationType = type;

            // Hide all option panels
            document.getElementById('facilityOptions').style.display = 'none';
            document.getElementById('statusOptions').style.display = 'none';
            document.getElementById('workplaceOptions').style.display = 'none';

            // Show relevant options
            switch (type) {
                case 'facility':
                    document.getElementById('facilityOptions').style.display = 'block';
                    document.getElementById('jobTitle').value = 'ID Cards - ' + new Date().toLocaleDateString();
                    break;
                case 'status':
                    document.getElementById('statusOptions').style.display = 'block';
                    document.getElementById('jobTitle').value = 'ID Cards - ' + new Date().toLocaleDateString();
                    break;
                case 'workplace':
                    document.getElementById('workplaceOptions').style.display = 'block';
                    document.getElementById('jobTitle').value = 'ID Cards - ' + new Date().toLocaleDateString();
                    break;
                default:
                    document.getElementById('jobTitle').value = 'ID Cards - All Beneficiaries - ' + new Date()
                        .toLocaleDateString();
            }

            document.getElementById('generationType').value = type;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('generationModal'));
            modal.show();
        }

        function startGeneration() {
            const form = document.getElementById('generationForm');
            const formData = new FormData(form);

            // Get title value explicitly
            const titleValue = document.getElementById('jobTitle').value.trim();
            if (!titleValue) {
                showAlert('error', 'Please enter a job title');
                return;
            }

            // Add title explicitly
            formData.set('title', titleValue);

            // Add generation type
            formData.set('generation_type', currentGenerationType);

            // Add additional criteria based on type
            switch (currentGenerationType) {
                case 'facility':
                    formData.set('facility_id', document.getElementById('facilitySelect').value);
                    break;
                case 'status':
                    formData.set('status', document.getElementById('statusSelect').value);
                    break;
                case 'workplace':
                    formData.set('workplace', document.getElementById('workplaceSelect').value);
                    break;
            }

            // Convert to JSON
            const data = Object.fromEntries(formData.entries());

            fetch('{{ route('beneficiaries.bulk-id-cards.start') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('generationModal')).hide();

                        // Show success message
                        showAlert('success', data.message);

                        // Start monitoring this job
                        monitorJob(data.job_id);

                        // Show job history
                        document.getElementById('jobHistorySection').style.display = 'block';

                        // Start auto-refresh
                        startAutoRefresh();
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred while starting generation');
                });
        }

        function showCustomSelection() {
            if (selectedBeneficiaries.size === 0) {
                showAlert('warning', 'Please select at least one beneficiary');
                return;
            }

            currentGenerationType = 'custom_selection';

            // Update custom selection card
            const title = 'Custom Selection - ' + selectedBeneficiaries.size + ' beneficiaries - ' + new Date()
                .toLocaleDateString();
            document.getElementById('customTitle').value = title;

            updateSelectedBeneficiariesList();

            // Show custom selection card
            document.getElementById('customSelectionCard').style.display = 'block';

            // Scroll to custom selection
            document.getElementById('customSelectionCard').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function updateSelectedBeneficiariesList() {
            const list = document.getElementById('selectedBeneficiariesList');
            const badge = document.getElementById('selectedBadge');

            if (selectedBeneficiaries.size === 0) {
                list.innerHTML = '<div class="text-muted text-center py-3">No beneficiaries selected</div>';
                document.getElementById('startCustomBtn').disabled = true;
                badge.textContent = '0';
            } else {
                badge.textContent = selectedBeneficiaries.size;
                let html = '<div class="mb-3"><strong class="text-primary">' + selectedBeneficiaries.size +
                    ' beneficiaries selected:</strong></div>';

                document.querySelectorAll('.beneficiary-checkbox:checked').forEach((checkbox, index) => {
                    html += '<div class="mb-2 p-2 bg-white rounded-2 border">' +
                        '<div class="d-flex align-items-center">' +
                        '<span class="badge bg-primary me-2">' + (index + 1) + '</span>' +
                        '<div class="flex-grow-1">' +
                        '<strong class="d-block">' + checkbox.dataset.name + '</strong>' +
                        '<small class="text-muted">' + checkbox.dataset.boschmaNo + '</small>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                });

                list.innerHTML = html;
                document.getElementById('startCustomBtn').disabled = false;
            }
        }

        function startCustomGeneration() {
            const title = document.getElementById('customTitle').value;

            if (!title.trim()) {
                showAlert('warning', 'Please enter a job title');
                return;
            }

            const data = {
                generation_type: 'custom_selection',
                title: title,
                beneficiary_ids: Array.from(selectedBeneficiaries)
            };

            fetch('{{ route('beneficiaries.bulk-id-cards.start') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide custom selection card
                        document.getElementById('customSelectionCard').style.display = 'none';

                        // Clear selection
                        clearSelection();

                        // Show success message
                        showAlert('success', data.message);

                        // Start monitoring this job
                        monitorJob(data.job_id);

                        // Show job history
                        document.getElementById('jobHistorySection').style.display = 'block';

                        // Start auto-refresh
                        startAutoRefresh();
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred while starting generation');
                });
        }

        function clearCustomSelection() {
            document.getElementById('customSelectionCard').style.display = 'none';
            clearSelection();
        }

        function monitorJob(jobId) {
            // This will be called periodically to update job status
            const checkStatus = () => {
                fetch('{{ route('beneficiaries.bulk-id-cards.status', ':jobId') }}'.replace(':jobId', jobId))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateJobInHistory(data.job);

                            // Stop monitoring if job is completed or failed
                            if (data.job.status === 'completed' || data.job.status === 'failed') {
                                showAlert(data.job.status === 'completed' ? 'success' : 'error',
                                    'Job ' + data.job.status + ': ' + data.job.title);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error checking job status:', error);
                    });
            };

            // Check status every 5 seconds
            const interval = setInterval(checkStatus, 5000);

            // Store interval ID so we can clear it later
            window['jobInterval_' + jobId] = interval;

            // Initial check
            checkStatus();
        }

        function updateJobInHistory(job) {
            // Find the job row in the history table and update it
            const rows = document.querySelectorAll('#jobHistoryTable tr');
            rows.forEach(row => {
                const jobIdCell = row.querySelector('td:first-child code');
                if (jobIdCell && jobIdCell.textContent === job.job_id) {
                    // Update status badge
                    const statusCell = row.cells[4];
                    statusCell.innerHTML = job.status_badge;

                    // Update progress bar
                    const progressCell = row.cells[5];
                    progressCell.innerHTML = `
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: ${job.progress_percentage}%">
                            ${job.progress_percentage}%
                        </div>
                    </div>
                    <small class="text-muted">
                        ${job.processed_records}/${job.total_records}
                        ${job.failed_records > 0 ? `<span class="text-danger">(${job.failed_records} failed)</span>` : ''}
                    </small>
                `;

                    // Update file download link
                    if (job.is_downloadable) {
                        const fileCell = row.cells[6];
                        fileCell.innerHTML = `
                        <a href="{{ route('beneficiaries.bulk-id-cards.download-file', ':jobId') }}".replace(':jobId', job.job_id) 
                           class="btn btn-sm btn-success" title="Download">
                            <i class="fe fe-download"></i>
                            <small>${job.file_size}</small>
                        </a>
                    `;
                    }
                }
            });
        }

        function refreshJobStatus(jobId) {
            fetch('{{ route('beneficiaries.bulk-id-cards.status', ':jobId') }}'.replace(':jobId', jobId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateJobInHistory(data.job);
                        showAlert('success', 'Job status updated');
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to refresh job status');
                });
        }

        function cancelJob(jobId) {
            if (!confirm('Are you sure you want to cancel this job?')) {
                return;
            }

            fetch('{{ route('beneficiaries.bulk-id-cards.cancel', ':jobId') }}'.replace(':jobId', jobId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        refreshJobStatus(jobId);
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to cancel job');
                });
        }

        function toggleJobHistory() {
            const section = document.getElementById('jobHistorySection');
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }

        function startAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }

            refreshInterval = setInterval(() => {
                // Reload the page to get updated job status
                window.location.reload();
            }, 30000); // Refresh every 30 seconds
        }

        function updateSelectionUI() {
            const count = selectedBeneficiaries.size;
            const selectionActions = document.getElementById('selectionActions');
            const generateBtn = document.getElementById('generateBtn');
            const selectedCount = document.getElementById('selectedCount');
            const generateCount = document.getElementById('generateCount');

            selectedCount.textContent = count;
            generateCount.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'block';
                generateBtn.disabled = false;
            } else {
                selectionActions.style.display = 'none';
                generateBtn.disabled = true;
            }

            // Update select all checkbox
            const totalCheckboxes = document.querySelectorAll('.beneficiary-checkbox').length;
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            selectAllCheckbox.checked = count > 0 && count === totalCheckboxes;
            selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes;

            // Update custom selection if visible
            if (document.getElementById('customSelectionCard').style.display !== 'none') {
                updateSelectedBeneficiariesList();
            }
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('.beneficiary-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                selectedBeneficiaries.add(checkbox.value);
            });
            updateSelectionUI();
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.beneficiary-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedBeneficiaries.clear();
            updateSelectionUI();
        }

        function clearFilters() {
            document.getElementById('filterForm').reset();
            window.location.href = '{{ route('beneficiaries.bulk-id-cards') }}';
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className =
                `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
@endpush
