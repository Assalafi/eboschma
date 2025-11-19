@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1">Civil Servants Management</h6>
                            <p class="text-muted card-sub-title">List of all registered civil servants in the system ({{ $civilServants->total() }} total)</p>
                        </div>
                        <div>
                            <button type="button" id="bulk-delete-btn" class="btn btn-danger me-2" style="display: none;">
                                <i class="fe fe-trash"></i> Delete Selected
                            </button>
                            <a href="{{ route('civil-servants.create') }}" class="btn btn-primary me-2">
                                <i class="fe fe-plus-circle"></i> New Civil Servant
                            </a>
                            <a href="{{ route('civil-servants.upload.form') }}" class="btn btn-success">
                                <i class="fe fe-upload"></i> Upload Excel
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('civil-servants.index') }}" id="filter-form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Search</label>
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="Name, DP No, NIN, MDA..." 
                                                   value="{{ $filters['search'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-control">
                                                <option value="">All Genders</option>
                                                <option value="Male" {{ ($filters['gender'] ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ ($filters['gender'] ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label">State</label>
                                            <select name="state" class="form-control">
                                                <option value="">All States</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state }}" {{ ($filters['state'] ?? '') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">MDA</label>
                                            <select name="mda" class="form-control">
                                                <option value="">All MDAs</option>
                                                @foreach($mdas as $mda)
                                                    <option value="{{ $mda }}" {{ ($filters['mda'] ?? '') == $mda ? 'selected' : '' }}>{{ $mda }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-flex gap-1">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fe fe-search"></i> Filter
                                                </button>
                                                <a href="{{ route('civil-servants.index') }}" class="btn btn-light btn-sm">
                                                    <i class="fe fe-x"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Birth Date From</label>
                                            <input type="date" name="date_from" class="form-control" 
                                                   value="{{ $filters['date_from'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Birth Date To</label>
                                            <input type="date" name="date_to" class="form-control" 
                                                   value="{{ $filters['date_to'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Sort By</label>
                                            <select name="sort_by" class="form-control">
                                                <option value="created_at" {{ ($filters['sort_by'] ?? 'created_at') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                                                <option value="fullname" {{ ($filters['sort_by'] ?? '') == 'fullname' ? 'selected' : '' }}>Full Name</option>
                                                <option value="dp_no" {{ ($filters['sort_by'] ?? '') == 'dp_no' ? 'selected' : '' }}>DP Number</option>
                                                <option value="dob" {{ ($filters['sort_by'] ?? '') == 'dob' ? 'selected' : '' }}>Date of Birth</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Sort Order</label>
                                            <select name="sort_order" class="form-control">
                                                <option value="desc" {{ ($filters['sort_order'] ?? 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                                                <option value="asc" {{ ($filters['sort_order'] ?? '') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
                    
                    <form id="bulk-delete-form" action="{{ route('civil-servants.bulk-delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        
                        <div class="table-responsive">
                            <table id="civil-servants-table" class="table table-bordered table-striped mg-b-0 text-md-nowrap">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="select-all">
                                                <label class="form-check-label" for="select-all"></label>
                                            </div>
                                        </th>
                                        <th>DP No</th>
                                        <th>Full Name</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>MDA</th>
                                        <th>State</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($civilServants as $civilServant)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $civilServant->id }}" class="form-check-input row-checkbox">
                                            <label class="form-check-label"></label>
                                        </div>
                                    </td>
                                    <td><strong>{{ $civilServant->dp_no }}</strong></td>
                                    <td>{{ $civilServant->fullname }}</td>
                                    <td>{{ $civilServant->gender }}</td>
                                    <td>
                                        @if($civilServant->dob)
                                            {{ \Carbon\Carbon::parse($civilServant->dob)->age }}
                                        @endif
                                    </td>
                                    <td>{{ $civilServant->mda }}</td>
                                    <td>{{ $civilServant->state ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('civil-servants.show', $civilServant->id) }}" class="btn btn-sm btn-primary" title="View">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('civil-servants.edit', $civilServant->id) }}" class="btn btn-sm btn-info" title="Edit">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="if(confirm('Are you sure you want to delete civil servant {{ $civilServant->dp_no }}: {{ $civilServant->fullname }}?')) { document.getElementById('quick-delete-{{ $civilServant->id }}').submit(); }"
                                                title="Delete">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                            
                                            <!-- Quick Delete Form -->
                                            <form id="quick-delete-{{ $civilServant->id }}" action="{{ route('civil-servants.destroy', $civilServant->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="py-3">
                                            <i class="fe fe-users fe-3x text-muted mb-3"></i>
                                            <p class="text-muted">No civil servants found.</p>
                                            <a href="{{ route('civil-servants.create') }}" class="btn btn-primary">Add First Civil Servant</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </form>
                    
                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 gap-3">
                        <div>
                            <p class="text-muted mb-0">
                                Showing {{ $civilServants->firstItem() ?? 0 }} to {{ $civilServants->lastItem() ?? 0 }} 
                                of {{ $civilServants->total() }} results
                            </p>
                        </div>
                        <div class="overflow-auto w-100 w-md-auto">
                            @if ($civilServants->hasPages())
                                <nav aria-label="Civil servants pagination">
                                    <ul class="pagination pagination-sm mb-0 flex-nowrap">
                                        {{-- Previous Page Link --}}
                                        @if ($civilServants->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">Prev</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $civilServants->previousPageUrl() }}" rel="prev">Prev</a></li>
                                        @endif

                                        {{-- Pagination Elements with Smart Window --}}
                                        @php
                                            $currentPage = $civilServants->currentPage();
                                            $lastPage = $civilServants->lastPage();
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
                                            <li class="page-item"><a class="page-link" href="{{ $civilServants->url(1) }}">1</a></li>
                                            @if ($start > 2)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                        @endif

                                        {{-- Page Number Links --}}
                                        @for ($page = $start; $page <= $end; $page++)
                                            @if ($page == $currentPage)
                                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link" href="{{ $civilServants->url($page) }}">{{ $page }}</a></li>
                                            @endif
                                        @endfor

                                        {{-- Last Page --}}
                                        @if ($end < $lastPage)
                                            @if ($end < $lastPage - 1)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item"><a class="page-link" href="{{ $civilServants->url($lastPage) }}">{{ $lastPage }}</a></li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if ($civilServants->hasMorePages())
                                            <li class="page-item"><a class="page-link" href="{{ $civilServants->nextPageUrl() }}" rel="next">Next</a></li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Bulk Delete Modal -->
                    <div class="modal fade" id="bulk-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Bulk Delete Confirmation</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete <span id="selected-count">0</span> selected civil servant(s)?</p>
                                    <p class="text-danger">This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="confirm-bulk-delete">Delete Selected</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-submit filter form on select changes
    $('#filter-form select, #filter-form input[type="date"]').on('change', function() {
        $('#filter-form').submit();
    });
    
    // Live search with delay
    let searchTimeout;
    $('#filter-form input[name="search"]').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filter-form').submit();
        }, 500);
    });
    
    // Select All functionality
    $('#select-all').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).is(':checked'));
        toggleBulkDeleteButton();
    });
    
    // Individual checkbox functionality
    $('.row-checkbox').on('change', function() {
        let totalRows = $('.row-checkbox').length;
        let checkedRows = $('.row-checkbox:checked').length;
        
        $('#select-all').prop('checked', totalRows === checkedRows);
        toggleBulkDeleteButton();
    });
    
    // Toggle bulk delete button visibility
    function toggleBulkDeleteButton() {
        let checkedRows = $('.row-checkbox:checked').length;
        if (checkedRows > 0) {
            $('#bulk-delete-btn').show();
        } else {
            $('#bulk-delete-btn').hide();
        }
    }
    
    // Bulk delete button click
    $('#bulk-delete-btn').on('click', function() {
        let selectedCount = $('.row-checkbox:checked').length;
        if (selectedCount === 0) {
            alert('Please select at least one civil servant to delete.');
            return;
        }
        
        $('#selected-count').text(selectedCount);
        $('#bulk-delete-modal').modal('show');
    });
    
    // Confirm bulk delete
    $('#confirm-bulk-delete').on('click', function() {
        $('#bulk-delete-form').submit();
    });
    
    // Clear filters
    $('.btn-clear-filters').on('click', function(e) {
        e.preventDefault();
        window.location.href = '{{ route("civil-servants.index") }}';
    });
});
</script>
@endsection
