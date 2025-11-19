@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Upload Management</h6>
                            <p class="card-sub-title" style="color: #01542B;">Manage and process contribution file uploads</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('contributions.index') }}" class="btn btn-outline-secondary mr-2">
                                <i class="fe fe-arrow-left"></i> Back to Contributions
                            </a>
                            <a href="{{ route('contribution-uploads.create') }}" class="btn btn-primary" 
                                style="background-color: #01542B; border-color: #01542B;">
                                <i class="fe fe-upload"></i> New Upload
                            </a>
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

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mg-b-0 text-md-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Filename</th>
                                    <th>Period</th>
                                    <th>Total Rows</th>
                                    <th>Processed</th>
                                    <th>Status</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($uploads as $upload)
                                    <tr>
                                        <td>{{ $loop->iteration + ($uploads->currentPage() - 1) * $uploads->perPage() }}</td>
                                        <td>
                                            <strong>{{ $upload->stored_filename }}</strong>
                                            <br><small class="text-muted">{{ $upload->filename }}</small>
                                        </td>
                                        <td>{{ $upload->period }}</td>
                                        <td>{{ number_format($upload->total_rows) }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                    style="width: {{ $upload->progress_percentage }}%"
                                                    aria-valuenow="{{ $upload->progress_percentage }}" 
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    {{ $upload->progress_percentage }}%
                                                </div>
                                            </div>
                                            <small>{{ number_format($upload->processed_rows) }} / {{ number_format($upload->total_rows) }}</small>
                                        </td>
                                        <td>
                                            @if($upload->status == 'pending')
                                                <span class="text-warning">
                                                    <i class="fe fe-clock"></i> <strong>Pending</strong>
                                                </span>
                                            @elseif($upload->status == 'processing')
                                                <span class="text-info">
                                                    <i class="fe fe-loader"></i> <strong>Processing...</strong>
                                                </span>
                                            @elseif($upload->status == 'completed')
                                                <span class="text-success">
                                                    <i class="fe fe-check-circle"></i> <strong>Completed</strong>
                                                </span>
                                                <br><small class="text-muted">
                                                    Success: {{ $upload->success_count }} | Failed: {{ $upload->failed_count }}
                                                </small>
                                            @else
                                                <span class="text-danger">
                                                    <i class="fe fe-x-circle"></i> <strong>Failed</strong>
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $upload->user->name ?? 'N/A' }}</td>
                                        <td>{{ $upload->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($upload->status == 'pending')
                                                    <a href="{{ route('contribution-uploads.process', $upload->id) }}" 
                                                        class="btn btn-sm btn-success" title="Start Processing"
                                                        onclick="return confirm('Start processing this file?');">
                                                        <i class="fe fe-play"></i>
                                                    </a>
                                                @endif
                                                
                                                @if($upload->status == 'completed' && $upload->error_log)
                                                    <a href="{{ route('contribution-uploads.errors', $upload->id) }}" 
                                                        class="btn btn-sm btn-warning" title="View Errors">
                                                        <i class="fe fe-alert-triangle"></i>
                                                    </a>
                                                @endif
                                                
                                                <form action="{{ route('contribution-uploads.destroy', $upload->id) }}" 
                                                    method="POST" class="d-inline" 
                                                    onsubmit="return confirm('Are you sure you want to delete this upload?');">
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
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fe fe-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="mb-0">No uploads found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination and Results Info -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $uploads->firstItem() ?? 0 }} to {{ $uploads->lastItem() ?? 0 }}
                                    of {{ $uploads->total() ?? 0 }} results
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                {{ $uploads->links('pagination::bootstrap-4') }}
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
    // Auto-refresh for processing uploads
    function checkProcessingUploads() {
        @foreach($uploads as $upload)
            @if($upload->status == 'processing')
                $.ajax({
                    url: '{{ route("contribution-uploads.progress", $upload->id) }}',
                    method: 'GET',
                    success: function(data) {
                        // Update progress bar
                        const progressBar = $('tr:contains("{{ $upload->stored_filename }}") .progress-bar');
                        progressBar.css('width', data.progress_percentage + '%');
                        progressBar.text(data.progress_percentage + '%');
                        
                        // Update processed count
                        const processedText = $('tr:contains("{{ $upload->stored_filename }}") .progress').next('small');
                        processedText.text(data.processed_rows.toLocaleString() + ' / ' + data.total_rows.toLocaleString());
                        
                        // If completed or failed, reload page
                        if (data.status === 'completed' || data.status === 'failed') {
                            location.reload();
                        }
                    }
                });
            @endif
        @endforeach
    }
    
    // Check every 3 seconds if there are processing uploads
    @if($uploads->where('status', 'processing')->count() > 0)
        setInterval(checkProcessingUploads, 3000);
    @endif
});
</script>
@endsection
