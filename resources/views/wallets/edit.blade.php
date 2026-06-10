@extends('layouts.app')
@section('title', 'Edit Wallet')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="page-pretitle"><a href="{{ route('wallets.index') }}" class="text-muted">Wallets</a> / <a href="{{ route('wallets.show', $wallet->id) }}" class="text-muted">{{ $wallet->facility->name ?? '' }}</a> /</div>
        <h2 class="page-title"><i class="ti-pencil me-2 text-warning"></i>Edit Wallet</h2>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <form action="{{ route('wallets.update', $wallet->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">{{ $wallet->facility->name ?? 'Unknown' }}</h3></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Program</label>
                                <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                    <option value="">Select Program...</option>
                                    @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id', $wallet->program_id) == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                @error('program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Status</label>
                                <select name="status" class="form-select" required>
                                    @foreach(\App\Models\FacilityWallet::getStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ $wallet->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $wallet->notes) }}</textarea></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Hospital Account</h3></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $wallet->bank_name) }}"></div>
                            <div class="mb-3"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" value="{{ old('account_number', $wallet->account_number) }}"></div>
                            <div class="mb-3"><label class="form-label">Account Name</label><input type="text" name="account_name" class="form-control" value="{{ old('account_name', $wallet->account_name) }}"></div>
                        </div>
                    </div>
                    <div class="card mt-3"><div class="card-body">
                        <button type="submit" class="btn btn-primary w-100"><i class="ti-check me-1"></i>Update Wallet</button>
                        <a href="{{ route('wallets.show', $wallet->id) }}" class="btn btn-ghost-secondary w-100 mt-2">Cancel</a>
                    </div></div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
