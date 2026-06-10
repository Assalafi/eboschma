@extends('layouts.app')
@section('title', 'Fund Wallet')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="page-pretitle"><a href="{{ route('wallets.index') }}" class="text-muted">Wallets</a> / <a href="{{ route('wallets.show', $wallet->id) }}" class="text-muted">{{ $wallet->facility->name ?? '' }}</a> /</div>
        <h2 class="page-title"><i class="ti-wallet me-2 text-success"></i>Adjust Wallet Funds</h2>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="ti-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <div class="card" style="border-left: 4px solid #016634;">
                    <div class="card-body">
                        <h4>{{ $wallet->facility->name ?? 'Unknown' }}</h4>
                        <div class="text-muted mb-2">{{ $wallet->facility->lga ?? '' }}</div>
                        <div class="h2 text-success mb-0">Current Balance: {{ $wallet->formatted_balance }}</div>
                        <div class="mt-2">
                            <span class="text-muted">Funded: {{ $wallet->formatted_total_funded }}</span> ·
                            <span class="text-muted">Deducted: {{ $wallet->formatted_total_deducted }}</span> ·
                            <span class="text-muted">Returned: {{ $wallet->formatted_total_returned }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Add/Deduct Funds</h3></div>
                    <div class="card-body">
                        <form action="{{ route('wallets.fund', $wallet->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label required">Action Type</label>
                                <select name="action_type" class="form-select form-select-lg" required>
                                    <option value="fund" {{ old('action_type') == 'fund' ? 'selected' : '' }}>Add Funds (Credit)</option>
                                    <option value="deduct" {{ old('action_type') == 'deduct' ? 'selected' : '' }}>Deduct Funds (Debit)</option>
                                </select>
                                @error('action_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Amount (₦)</label>
                                <input type="number" name="amount" class="form-control form-control-lg @error('amount') is-invalid @enderror" value="{{ old('amount') }}" min="1" step="0.01" required autofocus placeholder="Enter amount...">
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="e.g. Monthly funding, Q1 allocation...">{{ old('description') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100"><i class="ti-check me-1"></i>Submit Adjustment</button>
                            <a href="{{ route('wallets.show', $wallet->id) }}" class="btn btn-ghost-secondary w-100 mt-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
