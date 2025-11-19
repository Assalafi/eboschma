@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Error Log</h6>
                            <p class="card-sub-title" style="color: #01542B;">{{ $upload->filename }} - {{ $upload->period }}</p>
                        </div>
                        <div>
                            <a href="{{ route('contribution-uploads.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Total Rows:</strong> {{ number_format($upload->total_rows) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Processed:</strong> {{ number_format($upload->processed_rows) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Success:</strong> <span class="text-success">{{ number_format($upload->success_count) }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Failed:</strong> <span class="text-danger">{{ number_format($upload->failed_count) }}</span>
                            </div>
                        </div>
                    </div>

                    @if($upload->error_log)
                        <div class="card">
                            <div class="card-header" style="background-color: #f8f9fa;">
                                <h6 class="mb-0"><i class="fe fe-alert-triangle text-warning"></i> Error Details</h6>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0" style="max-height: 600px; overflow-y: auto;">{{ $upload->error_log }}</pre>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fe fe-check-circle"></i> No errors found! All records processed successfully.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
