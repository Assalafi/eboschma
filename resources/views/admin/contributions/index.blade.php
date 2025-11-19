@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-uppercase"
                                style="font-size: 11px; letter-spacing: 0.5px; color: #01542B;">
                                Total Records</p>
                            <h3 class="mb-0 font-weight-bold" style="color: #01542B;">
                                {{ number_format($summary['total_records']) }}</h3>
                            <small style="color: #01542B;">All Contributions</small>
                        </div>
                        <div class="stats-icon bg-primary-light">
                            <i class="fe fe-file-text text-primary"></i>
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
                                Total Contributed</p>
                            <h3 class="mb-0 font-weight-bold text-success" style="color: #01542B;">
                                ₦{{ number_format($summary['total_contributed'], 2) }}
                            </h3>
                            <small style="color: #01542B;">3.5% Contributions</small>
                        </div>
                        <div class="stats-icon bg-success-light">
                            <i class="fe fe-trending-up text-success"></i>
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
                                Active Records</p>
                            <h3 class="mb-0 font-weight-bold text-info" style="color: #01542B;">
                                {{ number_format($summary['active_records']) }}</h3>
                            <small style="color: #01542B;">Currently Active</small>
                        </div>
                        <div class="stats-icon bg-info-light">
                            <i class="fe fe-check-circle text-info"></i>
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
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Contributions Management</h6>
                            <p class="card-sub-title" style="color: #01542B;">Manage monthly salary contributions (3.5%)</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('contribution-uploads.index') }}" class="btn btn-outline-primary mr-2">
                                <i class="fe fe-folder"></i> Manage Uploads
                            </a>
                            <button type="button" class="btn btn-primary mr-2" 
                                style="background-color: #01542B; border-color: #01542B;"
                                data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fe fe-upload"></i> Quick Upload
                            </button>
                            <button type="button" class="btn btn-primary" 
                                style="background-color: #01542B; border-color: #01542B;"
                                data-bs-toggle="modal" data-bs-target="#createModal">
                                <i class="fe fe-plus-circle"></i> New Record
                            </button>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {!! session('success') !!}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {!! session('error') !!}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {!! session('warning') !!}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filter Form -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <form action="{{ route('contributions.index') }}" method="GET">
                                <div class="row align-items-end">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="small mb-1" style="color: #01542B;">Search DP No</label>
                                        <input type="text" class="form-control form-control-sm" name="dp_no" 
                                            value="{{ request('dp_no') }}" placeholder="Search by DP Number">
                                    </div>
                                    <div class="col-lg-2 col-md-6 mb-2">
                                        <label class="small mb-1" style="color: #01542B;">Month</label>
                                        <select class="form-control form-control-sm" name="month">
                                            <option value="">All Months</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-6 mb-2">
                                        <label class="small mb-1" style="color: #01542B;">Year</label>
                                        <select class="form-control form-control-sm" name="year">
                                            <option value="">All Years</option>
                                            @foreach($years as $year)
                                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-6 mb-2">
                                        <label class="small mb-1" style="color: #01542B;">Status</label>
                                        <select class="form-control form-control-sm" name="status">
                                            <option value="">All Status</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-12 mb-2">
                                        <button type="submit" class="btn btn-primary btn-sm"
                                            style="background-color: #01542B; border-color: #01542B;">
                                            <i class="fe fe-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                @if (request()->hasAny(['dp_no', 'month', 'year', 'status']))
                                    <div class="mt-2">
                                        <a href="{{ route('contributions.index') }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fe fe-x"></i> Clear Filters
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mg-b-0 text-md-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>DP No</th>
                                    <th>Salary Amount</th>
                                    <th>Contributed (3.5%)</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contributions as $contribution)
                                    <tr>
                                        <td>{{ $loop->iteration + ($contributions->currentPage() - 1) * $contributions->perPage() }}</td>
                                        <td><strong style="color: #01542B;">{{ $contribution->dp_no }}</strong></td>
                                        <td><strong>₦{{ number_format($contribution->amount, 2) }}</strong></td>
                                        <td>
                                            <span class="text-success">
                                                <i class="fe fe-trending-up"></i> <strong>₦{{ number_format($contribution->contributed, 2) }}</strong>
                                            </span>
                                        </td>
                                        <td>{{ $contribution->period }}</td>
                                        <td>
                                            @if($contribution->status)
                                                <span class="text-success">
                                                    <i class="fe fe-check-circle"></i> <strong>Active</strong>
                                                </span>
                                            @else
                                                <span class="text-danger">
                                                    <i class="fe fe-x-circle"></i> <strong>Inactive</strong>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                    onclick="editContribution({{ $contribution->id }}, '{{ $contribution->dp_no }}', {{ $contribution->amount }}, {{ $contribution->month }}, {{ $contribution->year }}, {{ $contribution->status }})"
                                                    data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </button>
                                                <form action="{{ route('contributions.destroy', $contribution->id) }}" method="POST" class="d-inline" 
                                                    onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fe fe-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fe fe-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="mb-0">No records found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 gap-3">
                        <div>
                            <p class="text-muted mb-0">
                                Showing {{ $contributions->firstItem() ?? 0 }} to {{ $contributions->lastItem() ?? 0 }} 
                                of {{ $contributions->total() }} results
                            </p>
                        </div>
                        <div class="overflow-auto w-100 w-md-auto">
                            @if ($contributions->hasPages())
                                <nav aria-label="Contributions pagination">
                                    <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                        {{-- Previous Page Link --}}
                                        @if ($contributions->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $contributions->previousPageUrl() }}" rel="prev">Prev</a></li>
                                        @endif

                                        {{-- Pagination Elements with Smart Window --}}
                                        @php
                                            $currentPage = $contributions->currentPage();
                                            $lastPage = $contributions->lastPage();
                                            $onEachSide = 2; // Show 2 pages on each side of current page
                                            
                                            // Calculate start and end of the sliding window
                                            $start = max(1, $currentPage - $onEachSide);
                                            $end = min($lastPage, $currentPage + $onEachSide);
                                            
                                            // Adjust if we're near the beginning or end
                                            if ($currentPage <= $onEachSide + 1) {
                                                $end = min($lastPage, ($onEachSide * 2) + 2);
                                            }
                                            if ($currentPage >= $lastPage - $onEachSide) {
                                                $start = max(1, $lastPage - ($onEachSide * 2) - 1);
                                            }
                                        @endphp

                                        {{-- First Page --}}
                                        @if ($start > 1)
                                            <li class="page-item"><a class="page-link" href="{{ $contributions->url(1) }}">1</a></li>
                                            @if ($start > 2)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                        @endif

                                        {{-- Page Number Links --}}
                                        @for ($page = $start; $page <= $end; $page++)
                                            @if ($page == $currentPage)
                                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link" href="{{ $contributions->url($page) }}">{{ $page }}</a></li>
                                            @endif
                                        @endfor

                                        {{-- Last Page --}}
                                        @if ($end < $lastPage)
                                            @if ($end < $lastPage - 1)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item"><a class="page-link" href="{{ $contributions->url($lastPage) }}">{{ $lastPage }}</a></li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if ($contributions->hasMorePages())
                                            <li class="page-item"><a class="page-link" href="{{ $contributions->nextPageUrl() }}" rel="next">Next</a></li>
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('contributions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title" style="color: #01542B; font-weight: 600;">Upload Contributions File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>File Format:</strong><br>
                        Column A: SN (Serial Number)<br>
                        Column B: DP_NO<br>
                        Column C: SALARY (Amount)<br>
                        <small>First row should be headers</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>Note:</strong> If a DP No already has a record for the selected month/year, it will be replaced with the new data from the file.
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('contributions.download-template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fe fe-download"></i> Download Template File
                        </a>
                        <small class="d-block text-muted mt-1">Download a sample Excel template to see the correct format</small>
                    </div>

                    <div class="form-group">
                        <label>Select File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" accept=".csv,.xlsx,.xls" required>
                        <small class="text-muted">Supported: CSV, Excel (.xlsx, .xls) | Max: 10MB</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Month <span class="text-danger">*</span></label>
                                <select class="form-control" name="month" required>
                                    @php
                                        $nextMonth = date('n') == 12 ? 1 : date('n') + 1;
                                    @endphp
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $i == $nextMonth ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Year <span class="text-danger">*</span></label>
                                <select class="form-control" name="year" required>
                                    @php
                                        $currentYear = date('Y');
                                        $nextYear = date('n') == 12 ? $currentYear + 1 : $currentYear;
                                    @endphp
                                    @for($i = $currentYear - 1; $i <= $currentYear + 2; $i++)
                                        <option value="{{ $i }}" {{ $i == $nextYear ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #01542B; color: white;">
                        <i class="fe fe-upload"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('contributions.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title" style="color: #01542B; font-weight: 600;">Create Contribution Record</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Note:</strong> If this DP No already has a record for the selected month/year, it will be replaced.
                    </div>
                    
                    <div class="form-group">
                        <label>DP No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dp_no" required placeholder="Enter DP Number">
                    </div>

                    <div class="form-group">
                        <label>Salary Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" step="0.01" required placeholder="Enter salary amount" id="createAmount">
                        <small class="text-muted">3.5% will be calculated automatically</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Month <span class="text-danger">*</span></label>
                                <select class="form-control" name="month" required>
                                    @php
                                        $nextMonth = date('n') == 12 ? 1 : date('n') + 1;
                                    @endphp
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $i == $nextMonth ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Year <span class="text-danger">*</span></label>
                                <select class="form-control" name="year" required>
                                    @php
                                        $currentYear = date('Y');
                                        $nextYear = date('n') == 12 ? $currentYear + 1 : $currentYear;
                                    @endphp
                                    @for($i = $currentYear - 1; $i <= $currentYear + 2; $i++)
                                        <option value="{{ $i }}" {{ $i == $nextYear ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success" id="createPreview" style="display: none;">
                        <strong>Contribution Preview:</strong><br>
                        Salary: <span id="previewSalary">₦0.00</span><br>
                        3.5% Contribution: <span id="previewContribution">₦0.00</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #01542B; color: white;">
                        <i class="fe fe-save"></i> Create Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title" style="color: #01542B; font-weight: 600;">Edit Contribution Record</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>DP No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editDpNo" name="dp_no" required>
                    </div>

                    <div class="form-group">
                        <label>Salary Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="editAmount" name="amount" step="0.01" required>
                        <small class="text-muted">3.5% will be calculated automatically</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Month <span class="text-danger">*</span></label>
                                <select class="form-control" id="editMonth" name="month" required>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Year <span class="text-danger">*</span></label>
                                <select class="form-control" id="editYear" name="year" required>
                                    @for($i = 2020; $i <= date('Y') + 2; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="editStatus" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="alert alert-success" id="editPreview" style="display: none;">
                        <strong>Contribution Preview:</strong><br>
                        Salary: <span id="editPreviewSalary">₦0.00</span><br>
                        3.5% Contribution: <span id="editPreviewContribution">₦0.00</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #01542B; color: white;">
                        <i class="fe fe-save"></i> Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Create amount preview
    $('#createAmount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const contribution = amount * 0.035;
        
        $('#previewSalary').text('₦' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#previewContribution').text('₦' + contribution.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        if (amount > 0) {
            $('#createPreview').show();
        } else {
            $('#createPreview').hide();
        }
    });

    // Edit amount preview
    $('#editAmount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const contribution = amount * 0.035;
        
        $('#editPreviewSalary').text('₦' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#editPreviewContribution').text('₦' + contribution.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        if (amount > 0) {
            $('#editPreview').show();
        } else {
            $('#editPreview').hide();
        }
    });
});

// Edit function - global scope so onclick can access it
function editContribution(id, dpNo, amount, month, year, status) {
    $('#editForm').attr('action', '/contributions/' + id);
    $('#editDpNo').val(dpNo);
    $('#editAmount').val(amount).trigger('input');
    $('#editMonth').val(month);
    $('#editYear').val(year);
    $('#editStatus').val(status ? 1 : 0);
}
</script>

@endsection
