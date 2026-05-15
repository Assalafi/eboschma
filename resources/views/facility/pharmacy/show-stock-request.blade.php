@extends('layouts.facility')

@section('title', 'Stock Request Details')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti-package me-2 text-primary"></i>Stock Request Details
                    </h2>
                    <div class="text-muted mt-1">View detailed information about this stock request</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('facility.pharmacy.stock-requests') }}" class="btn btn-secondary">
                        <i class="ti-arrow-left me-1"></i> Back to Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Request Details Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Request Information</h3>
                            <div class="card-actions">
                                {!! $stockRequest->status_badge !!}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Request ID</label>
                                        <div class="fw-bold">#{{ str_pad($stockRequest->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                    @if ($stockRequest->drug_id)
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Drug</label>
                                            <div class="fw-bold">{{ $stockRequest->drug->name }}</div>
                                            <div class="text-muted">{{ $stockRequest->drug->strength }}
                                                {{ $stockRequest->drug->unit }}</div>
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Request Type</label>
                                            <div class="fw-bold text-primary">Bulk Request
                                                ({{ $stockRequest->items->count() }} items)</div>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Quantity Requested</label>
                                        <div class="fw-bold fs-4 text-primary">{{ $stockRequest->formatted_quantity }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Program</label>
                                        <div class="fw-bold">{{ $stockRequest->program->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Estimated Cost</label>
                                        <div class="fw-bold fs-4 text-success">{{ $stockRequest->formatted_estimated_cost }}
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Priority</label>
                                        <div>{!! $stockRequest->priority_badge !!}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Requested</label>
                                        <div>{{ $stockRequest->requested_at->format('M j, Y') }}</div>
                                        <div class="text-muted">{{ $stockRequest->requested_at->format('g:i A') }}</div>
                                    </div>
                                </div>
                            </div>

                            @if ($stockRequest->reason)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Reason for Request</label>
                                    <div class="border rounded p-3 bg-light">
                                        {{ $stockRequest->reason }}
                                    </div>
                                </div>
                            @endif

                            @if ($stockRequest->notes)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Additional Notes</label>
                                    <div class="border rounded p-3 bg-light">
                                        {{ $stockRequest->notes }}
                                    </div>
                                </div>
                            @endif

                            <!-- Bulk Request Items -->
                            @if ($stockRequest->items->count() > 0)
                                <div class="mb-4">
                                    <label class="form-label text-muted fw-bold">Requested Items</label>
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Drug</th>
                                                    <th>Quantity</th>
                                                    <th>Cost</th>
                                                    <th>Priority</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($stockRequest->items as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold">{{ $item->drug->name }}</div>
                                                            <div class="text-muted">{{ $item->drug->strength }}
                                                                {{ $item->drug->unit }}</div>
                                                        </td>
                                                        <td>{{ $item->formatted_quantity }}</td>
                                                        <td class="text-success">{{ $item->formatted_estimated_cost }}</td>
                                                        <td>{!! $item->priority_badge !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dispensed Stock Information -->
                    @if ($stockRequest->status === 'dispensed' && $stockRequest->drugStocks->count() > 0)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Dispensed Stock Batches</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Batch Number</th>
                                                <th>Quantity</th>
                                                <th>Unit Cost</th>
                                                <th>Total Cost</th>
                                                <th>Expiry Date</th>
                                                <th>Supplier</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($stockRequest->drugStocks as $stock)
                                                <tr>
                                                    <td>{{ $stock->batch_number }}</td>
                                                    <td>{{ $stock->quantity_received }}</td>
                                                    <td>₦{{ number_format($stock->unit_cost, 2) }}</td>
                                                    <td class="text-success">
                                                        ₦{{ number_format($stock->quantity_received * $stock->unit_cost, 2) }}
                                                    </td>
                                                    <td>
                                                        <span class="{{ $stock->isExpiringSoon() ? 'text-warning' : '' }}">
                                                            {{ $stock->expiry_date->format('M j, Y') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $stock->supplier }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if ($stockRequest->status === 'rejected' && $stockRequest->rejection_reason)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title text-danger">Rejection Reason</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-danger">
                                    <i class="ti-alert-triangle me-2"></i>
                                    {{ $stockRequest->rejection_reason }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <!-- Request Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Request Timeline</h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline timeline-simple">
                                <!-- Requested -->
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">
                                            {{ $stockRequest->requested_at->format('M j, Y g:i A') }}</div>
                                        <div class="timeline-title">Request Submitted</div>
                                        <div class="timeline-body text-muted">
                                            By {{ $stockRequest->requestedBy->name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Approved -->
                                @if ($stockRequest->approved_at)
                                    <div class="timeline-item">
                                        <div class="timeline-point timeline-point-success"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time">
                                                {{ $stockRequest->approved_at->format('M j, Y g:i A') }}</div>
                                            <div class="timeline-title">Request Approved</div>
                                            <div class="timeline-body text-muted">
                                                By {{ $stockRequest->approvedBy->name ?? 'System' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Rejected -->
                                @if ($stockRequest->status === 'rejected' && $stockRequest->approved_at)
                                    <div class="timeline-item">
                                        <div class="timeline-point timeline-point-danger"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time">
                                                {{ $stockRequest->approved_at->format('M j, Y g:i A') }}</div>
                                            <div class="timeline-title">Request Rejected</div>
                                            <div class="timeline-body text-muted">
                                                By {{ $stockRequest->approvedBy->name ?? 'System' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Dispensed -->
                                @if ($stockRequest->dispensed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-point timeline-point-info"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time">
                                                {{ $stockRequest->dispensed_at->format('M j, Y g:i A') }}</div>
                                            <div class="timeline-title">Stock Dispensed</div>
                                            <div class="timeline-body text-muted">
                                                By {{ $stockRequest->dispensedBy->name ?? 'System' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Facility Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Facility Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3" style="background-image: url(/images/facility-placeholder.png)">
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $stockRequest->facility->name }}</div>
                                    <div class="text-muted">{{ $stockRequest->facility->type }}</div>
                                </div>
                            </div>
                            <div class="text-muted">
                                <small>All requests are processed for this facility.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
