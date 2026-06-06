@extends('layouts.app')

@section('title', 'Facility Wallets')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .wallet-stat-card {
            transition: all 0.2s ease;
            border-radius: 12px;
            overflow: hidden;
        }
        .wallet-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #667382;
        }
    </style>
@endpush

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="page-pretitle">Finance Management</div>
                    <h2 class="page-title">
                        <i class="fe fe-credit-card me-2 text-primary"></i>Facility Wallets
                    </h2>
                    <div class="text-muted mt-1">Manage facility wallet balances and track drug dispensation returns</div>
                </div>
                <div class="col-md-4 d-print-none">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('wallets.create') }}" class="btn btn-primary">
                            <i class="ti-plus me-1"></i>Create Wallet
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

            <!-- Overview Stats -->
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card wallet-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon bg-primary-lt text-primary">
                                    <i class="fe fe-credit-card"></i>
                                </div>
                                <div class="ms-auto">
                                    <span class="badge bg-primary">{{ $stats['active_wallets'] }} active</span>
                                </div>
                            </div>
                            <div class="stat-value text-primary">₦{{ number_format($stats['total_balance'], 2) }}</div>
                            <div class="stat-label mt-1">Total Balance (All Wallets)</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card wallet-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon bg-success-lt text-success">
                                    <i class="ti-wallet"></i>
                                </div>
                            </div>
                            <div class="stat-value text-success">₦{{ number_format($stats['total_funded'], 2) }}</div>
                            <div class="stat-label mt-1">Total Funded</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card wallet-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon bg-danger-lt text-danger">
                                    <i class="ti-minus"></i>
                                </div>
                            </div>
                            <div class="stat-value text-danger">₦{{ number_format($stats['total_deducted'], 2) }}</div>
                            <div class="stat-label mt-1">Total Deducted (Stock Requests)</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card wallet-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon bg-info-lt text-info">
                                    <i class="ti-receipt-refund"></i>
                                </div>
                            </div>
                            <div class="stat-value text-info">₦{{ number_format($stats['total_returned'], 2) }}</div>
                            <div class="stat-label mt-1">Total 10% Returns</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drugs Dispensed Overview -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card wallet-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-purple-lt text-purple me-3">
                                    <i class="fe fe-package"></i>
                                </div>
                                <div>
                                    <div class="stat-label">Total Drug Dispensation Value</div>
                                    <div class="stat-value" style="color: #7c3aed;">₦{{ number_format($stats['total_drugs_dispensed_value'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card wallet-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-cyan-lt text-cyan me-3">
                                    <i class="fe fe-bar-chart-2"></i>
                                </div>
                                <div>
                                    <div class="stat-label">Total Wallets</div>
                                    <div class="stat-value text-cyan">{{ $stats['total_wallets'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallets Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fe fe-list me-2"></i>All Facility Wallets
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="walletsTable" class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Facility</th>
                                    <th>Balance</th>
                                    <th>Total Funded</th>
                                    <th>Total Deducted</th>
                                    <th>10% Returns</th>
                                    <th>Bank Info</th>
                                    <th>Status</th>
                                    <th class="w-1">Actions</th>
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
            $('#walletsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("wallets.index") }}',
                columns: [
                    { data: 'facility_name' },
                    { data: 'balance_fmt', className: 'text-end' },
                    { data: 'total_funded_fmt', className: 'text-end' },
                    { data: 'total_deducted_fmt', className: 'text-end' },
                    { data: 'total_returned_fmt', className: 'text-end' },
                    { data: 'bank_info' },
                    { data: 'status', className: 'text-center' },
                    { data: 'action', orderable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    emptyTable: 'No wallets found. Create one to get started.'
                }
            });
        });
    </script>
@endpush
