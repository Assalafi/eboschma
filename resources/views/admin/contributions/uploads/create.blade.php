@extends('layouts.app')

@section('content')
<div class="container-fluid pt-3">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6 class="main-content-label mb-1" style="color: #01542B;">Upload Contributions File</h6>
                            <p class="card-sub-title" style="color: #01542B;">Upload Excel/CSV file for batch processing</p>
                        </div>
                        <div>
                            <a href="{{ route('contribution-uploads.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {!! session('error') !!}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('contribution-uploads.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-info">
                            <h6><i class="fe fe-info"></i> File Format Requirements:</h6>
                            <ul class="mb-0">
                                <li>Column A: SN (Serial Number)</li>
                                <li>Column B: DP_NO</li>
                                <li>Column C: SALARY (Amount)</li>
                                <li>First row should be headers</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="fe fe-alert-triangle"></i> Important:</strong> 
                            <ul class="mb-0">
                                <li>File will be saved with name: month_year (e.g., 1_2025.xlsx)</li>
                                <li>If DP No already exists for this period, it will be replaced</li>
                                <li>After upload, click "Start Processing" to begin import</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('contributions.download-template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fe fe-download"></i> Download Template File
                            </a>
                            <small class="d-block text-muted mt-1">Download a sample Excel template to see the correct format</small>
                        </div>

                        <div class="form-group">
                            <label>Select File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                name="file" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted">Supported: CSV, Excel (.xlsx, .xls) | Max: 10MB</small>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Month <span class="text-danger">*</span></label>
                                    <select class="form-control @error('month') is-invalid @enderror" name="month" required>
                                        @php
                                            $nextMonth = date('n') == 12 ? 1 : date('n') + 1;
                                        @endphp
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ $i == $nextMonth ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Year <span class="text-danger">*</span></label>
                                    <select class="form-control @error('year') is-invalid @enderror" name="year" required>
                                        @php
                                            $currentYear = date('Y');
                                            $nextYear = date('n') == 12 ? $currentYear + 1 : $currentYear;
                                        @endphp
                                        @for($i = $currentYear - 1; $i <= $currentYear + 2; $i++)
                                            <option value="{{ $i }}" {{ $i == $nextYear ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light">
                            <p class="mb-0"><strong>What happens next?</strong></p>
                            <ol class="mb-0">
                                <li>File will be uploaded and saved on the server</li>
                                <li>System will count total valid rows</li>
                                <li>You'll be redirected to Upload Management page</li>
                                <li>Click "Start Processing" to begin the import</li>
                                <li>Watch real-time progress as records are processed</li>
                            </ol>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" style="background-color: #01542B; border-color: #01542B;">
                                <i class="fe fe-upload"></i> Upload File
                            </button>
                            <a href="{{ route('contribution-uploads.index') }}" class="btn btn-light btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
