@extends('layouts.app')
@section('title', 'Create Wallet')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="page-pretitle"><a href="{{ route('wallets.index') }}" class="text-muted">Wallets</a> /</div>
        <h2 class="page-title"><i class="ti-plus me-2 text-primary"></i>Create Facility Wallet</h2>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="ti-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <form action="{{ route('wallets.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Wallet Details</h3></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Facility</label>
                                <select name="facility_id" class="form-select @error('facility_id') is-invalid @enderror" required>
                                    <option value="">Select Facility...</option>
                                    @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }} ({{ $facility->lga }})</option>
                                    @endforeach
                                </select>
                                @error('facility_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($facilities->isEmpty())<div class="text-warning mt-2"><i class="ti-alert-triangle me-1"></i>All facilities already have wallets.</div>@endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Initial Balance (₦)</label>
                                <input type="number" name="initial_balance" class="form-control" value="{{ old('initial_balance', 0) }}" min="0" step="0.01">
                                <small class="text-muted">Optional. You can fund the wallet later.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Hospital Account (Optional)</h3></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}"></div>
                            <div class="mb-3"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}"></div>
                            <div class="mb-3"><label class="form-label">Account Name</label><input type="text" name="account_name" class="form-control" value="{{ old('account_name') }}"></div>
                        </div>
                    </div>
                    <div class="card mt-3"><div class="card-body">
                        <button type="submit" class="btn btn-primary w-100"><i class="ti-check me-1"></i>Create Wallet</button>
                        <a href="{{ route('wallets.index') }}" class="btn btn-ghost-secondary w-100 mt-2">Cancel</a>
                    </div></div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
