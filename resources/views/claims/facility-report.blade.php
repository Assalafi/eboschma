@extends('layouts.app')

@section('title', 'Facility Claims Report')

@section('content')
<div class="container" style="max-width:900px;">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        <div>
            <h4>Facility Claims Report</h4>
            @if($facility)
                <div class="text-muted">Facility: {{ $facility->name }}</div>
            @endif
            <div class="text-muted">Period: {{ $dateFrom }} to {{ $dateTo }}</div>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary d-print-none" onclick="window.print()"><i class="ti-printer"></i> Print</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Claims</th>
                            <th class="text-end">Admin Charges</th>
                            <th class="text-end">Pharmacy</th>
                            <th class="text-end">Laboratory</th>
                            <th class="text-end">Services</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthly as $m)
                            <tr>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('F Y') }}</td>
                                <td class="text-center">{{ number_format($m->claim_count) }}</td>
                                <td class="text-end">₦{{ number_format($m->admin_charges, 2) }}</td>
                                <td class="text-end">₦{{ number_format($m->pharmacy, 2) }}</td>
                                <td class="text-end">₦{{ number_format($m->laboratory, 2) }}</td>
                                <td class="text-end">₦{{ number_format($m->services, 2) }}</td>
                                <td class="text-end fw-bold">₦{{ number_format($m->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No data for selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
