@extends('layouts.app')

@section('title', 'Edit Store Stock - ' . $drug->name)

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="page-pretitle">Store Inventory Management</div>
                    <h2 class="page-title">
                        <i class="ti-edit me-2 text-primary"></i>Edit Store Stock
                    </h2>
                    <div class="text-muted mt-1">
                        <strong>{{ $drug->name }} ({{ $drug->strength }} {{ $drug->unit }})</strong> - Batch: {{ $stock->batch_number }}
                    </div>
                </div>
                <div class="col-md-4 d-print-none">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('drug-store.show', $drug->id) }}" class="btn btn-secondary">
                            <i class="ti-arrow-left me-1"></i>Back to Batches
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-8">
                    <form method="POST" action="{{ route('drug-store.update', $stock->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Edit Stock Batch</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Drug Name</label>
                                        <input type="text" class="form-control" value="{{ $drug->name }} ({{ $drug->strength }} {{ $drug->unit }})" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Program <span class="text-danger">*</span></label>
                                        <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                            <option value="">Select a program...</option>
                                            @foreach ($programs as $program)
                                                <option value="{{ $program->id }}"
                                                    {{ old('program_id', $stock->program_id) == $program->id ? 'selected' : '' }}>
                                                    {{ $program->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('program_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Batch Number</label>
                                        <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror" 
                                               value="{{ old('batch_number', $stock->batch_number) }}" required>
                                        @error('batch_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                               value="{{ old('expiry_date', $stock->expiry_date->format('Y-m-d')) }}" required>
                                        @error('expiry_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity Received</label>
                                        <input type="number" name="quantity_received" class="form-control @error('quantity_received') is-invalid @enderror" 
                                               value="{{ old('quantity_received', $stock->quantity_received) }}" min="{{ $stock->quantity_dispensed }}" required>
                                        <div class="form-text">
                                            Minimum: {{ $stock->quantity_dispensed }} (already dispensed)
                                        </div>
                                        @error('quantity_received')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Unit Cost (₦)</label>
                                        <input type="number" name="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror" 
                                               value="{{ old('unit_cost', $stock->unit_cost) }}" step="0.01" min="0" required>
                                        @error('unit_cost')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Supplier</label>
                                        <input type="text" name="supplier" class="form-control @error('supplier') is-invalid @enderror" 
                                               value="{{ old('supplier', $stock->supplier) }}" required>
                                        @error('supplier')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="active" {{ old('status', $stock->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="depleted" {{ old('status', $stock->status) == 'depleted' ? 'selected' : '' }}>Depleted</option>
                                            <option value="expired" {{ old('status', $stock->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $stock->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end gap-2">
                                <a href="{{ route('drug-store.show', $drug->id) }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti-save me-1"></i>Update Stock Batch
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <!-- Current Status Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Quantity Received:</span>
                                    <span class="fw-bold">{{ number_format($stock->quantity_received) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Quantity Dispensed:</span>
                                    <span class="fw-bold text-danger">{{ number_format($stock->quantity_dispensed) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Quantity Remaining:</span>
                                    <span class="fw-bold text-success">{{ number_format($stock->quantity_remaining) }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Unit Cost:</span>
                                    <span class="fw-bold">₦{{ number_format($stock->unit_cost, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Value:</span>
                                    <span class="fw-bold">₦{{ number_format($stock->quantity_remaining * $stock->unit_cost, 2) }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Stocked By:</span>
                                    <span class="fw-bold">{{ $stock->stockedBy->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Stocked At:</span>
                                    <span class="fw-bold">{{ $stock->stocked_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                            @if($stock->quantity_dispensed > 0)
                                <div class="alert alert-warning">
                                    <i class="ti-alert-triangle me-1"></i>
                                    <strong>Note:</strong> This batch has been partially dispensed. You cannot reduce the quantity below {{ $stock->quantity_dispensed }} units.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions Card -->
                    @if($stock->quantity_dispensed == 0)
                        <div class="card mt-3">
                            <div class="card-body">
                                <form method="POST" action="{{ route('drug-store.destroy', $stock->id) }}" onsubmit="return confirm('Are you sure you want to delete this stock batch? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="ti-trash me-1"></i>Delete Stock Batch
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
