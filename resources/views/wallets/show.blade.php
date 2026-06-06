@extends('layouts.app')

@section('title', 'Wallet - ' . ($wallet->facility->name ?? 'Unknown'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .wallet-hero { background: linear-gradient(135deg, #016634 0%, #02893f 100%); border-radius: 16px; color: #fff; }
        .wallet-hero .balance-label { font-size: 0.85rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; }
        .wallet-hero .balance-value { font-size: 2.5rem; font-weight: 800; }
        .mini-stat { background: rgba(255,255,255,0.15); border-radius: 10px; padding: 12px 16px; }
        .mini-stat .mini-label { font-size: 0.75rem; opacity: 0.8; }
        .mini-stat .mini-value { font-size: 1.1rem; font-weight: 700; }
        .drug-return-row { transition: background 0.15s; }
        .drug-return-row:hover { background: #f1f5f9; }
    </style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <a href="{{ route('wallets.index') }}" class="text-muted">Wallets</a> /
                    </div>
                    <h2 class="page-title">{{ $wallet->facility->name ?? 'Unknown Facility' }}</h2>
                </div>
                <div class="col-auto d-print-none">
                    <div class="d-flex gap-2">
                        <a href="{{ route('wallets.fund-form', $wallet->id) }}" class="btn btn-success">
                            <i class="ti-plus me-1"></i>Fund Wallet
                        </a>
                        <a href="{{ route('wallets.edit', $wallet->id) }}" class="btn btn-warning">
                            <i class="ti-pencil me-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Wallet Hero Card -->
            <div class="wallet-hero p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="balance-label">Current Balance</div>
                        <div class="balance-value">{{ $wallet->formatted_balance }}</div>
                        <div class="mt-2">{!! $wallet->status_badge !!}</div>
                        @if($wallet->bank_name)
                            <div class="mt-2" style="opacity:0.8">
                                <i class="fe fe-credit-card me-1"></i>{{ $wallet->bank_name }} · {{ $wallet->account_number }}
                                @if($wallet->account_name)
                                    <br><small>{{ $wallet->account_name }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="mini-stat">
                                    <div class="mini-label">Total Funded</div>
                                    <div class="mini-value">{{ $wallet->formatted_total_funded }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mini-stat">
                                    <div class="mini-label">Total Deducted</div>
                                    <div class="mini-value">{{ $wallet->formatted_total_deducted }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mini-stat">
                                    <div class="mini-label">10% Returns</div>
                                    <div class="mini-value">{{ $wallet->formatted_total_returned }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-4">
                                <div class="mini-stat">
                                    <div class="mini-label">Funding Count</div>
                                    <div class="mini-value">{{ $walletStats['funding_count'] }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mini-stat">
                                    <div class="mini-label">Stock Deductions</div>
                                    <div class="mini-value">{{ $walletStats['total_stock_deductions'] }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mini-stat">
                                    <div class="mini-label">Drug Returns</div>
                                    <div class="mini-value">{{ $walletStats['total_dispensation_returns'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drug Dispensation Value -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fe fe-package me-2"></i>Drug Dispensation Overview</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase">Total Drug Dispensation Value</div>
                                <div class="h2 mb-0">₦{{ number_format($walletStats['total_drugs_dispensed_value'], 2) }}</div>
                            </div>
                            <div class="mb-0">
                                <div class="text-muted small text-uppercase">Total 10% Returned</div>
                                <div class="h3 mb-0 text-success">{{ $wallet->formatted_total_returned }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti-pill me-2"></i>Top Drug Returns (10%)</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($drugReturns->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Drug</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Drug Cost</th>
                                                <th class="text-end">10% Returned</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($drugReturns as $dr)
                                                <tr class="drug-return-row">
                                                    <td class="fw-bold">{{ $dr->drug_name }}</td>
                                                    <td class="text-end">{{ number_format($dr->total_quantity) }}</td>
                                                    <td class="text-end">₦{{ number_format($dr->total_drug_cost, 2) }}</td>
                                                    <td class="text-end text-success fw-bold">₦{{ number_format($dr->total_returned, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fe fe-inbox" style="font-size:2rem"></i>
                                    <p class="mt-2 mb-0">No dispensation returns yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti-list me-2"></i>Transaction History</h3>
                    <div class="card-actions">
                        <div class="d-flex gap-2">
                            <select id="filter-type" class="form-select form-select-sm" style="width:180px;">
                                <option value="">All Types</option>
                                @foreach($transactionTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="date" id="filter-date-from" class="form-control form-control-sm" style="width:140px;" placeholder="From">
                            <input type="date" id="filter-date-to" class="form-control form-control-sm" style="width:140px;" placeholder="To">
                            <button type="button" id="applyFilter" class="btn btn-sm btn-primary">Filter</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="transactionsTable" class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Drug Details</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function() {
            var table = $('#transactionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("wallets.show", $wallet->id) }}',
                    data: function(d) {
                        d.type = $('#filter-type').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                    }
                },
                columns: [
                    { data: 'date_fmt' },
                    { data: 'type_badge' },
                    { data: 'amount_fmt', className: 'text-end' },
                    { data: 'balance_after_fmt', className: 'text-end' },
                    { data: 'drug_info' },
                    { data: 'description_fmt' }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    emptyTable: 'No transactions found.'
                }
            });

            $('#applyFilter').on('click', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
