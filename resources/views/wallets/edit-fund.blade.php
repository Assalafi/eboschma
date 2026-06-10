@extends('layouts.app')

@section('title', 'Edit Fund - ' . ($transaction->wallet->facility->name ?? 'Unknown'))

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('wallets.show', $transaction->wallet->id) }}" class="text-muted">Wallet</a> /
                </div>
                <h2 class="page-title">Edit Funding Transaction</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Fund Details</h3>
                    </div>
                    <form action="{{ route('wallets.update-fund', $transaction->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Amount (₦)</label>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', abs($transaction->amount)) }}" required>
                                <small class="form-hint">Modifying this amount will automatically recalculate the wallet's current balance and update subsequent transaction balances.</small>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $transaction->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('wallets.show', $transaction->wallet->id) }}" class="btn btn-link">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti-check me-1"></i> Update Fund
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
