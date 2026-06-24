<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; line-height: 1.4; margin: 0; padding: 20px; }
        h2 { text-align: center; margin-bottom: 5px; font-size: 16px; }
        .report-meta { text-align: center; font-size: 10px; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        td.text-center { text-align: center; }
        td.text-end { text-align: right; }
        tfoot { background: #f5f5f5; font-weight: bold; }
        tfoot td.text-end { border-top: 2px solid #333; }
        .total-column { color: #006634; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Facility Claims Report</h2>
    @if($facility)
        <div class="report-meta">
            <strong>{{ $facility->name }}</strong><br>
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}<br>
            Generated: {{ now()->format('M d, Y g:i A') }}
        </div>
    @else
        <div class="report-meta">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}<br>
            Generated: {{ now()->format('M d, Y g:i A') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th style="text-align: center;">Claims</th>
                <th style="text-align: right;">Admin Charges</th>
                <th style="text-align: right;">Pharmacy</th>
                <th style="text-align: right;">Laboratory</th>
                <th style="text-align: right;">Services</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthly as $m)
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('F Y') }}</td>
                    <td class="text-center">{{ number_format($m->claim_count) }}</td>
                    <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($m->admin_charges, 2) }}</td>
                    <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($m->pharmacy, 2) }}</td>
                    <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($m->laboratory, 2) }}</td>
                    <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($m->services, 2) }}</td>
                    <td class="text-end total-column"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($m->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999;">No data for selected period.</td>
                </tr>
            @endforelse
        </tbody>
        @if($monthly->count() > 0)
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="text-center">{{ number_format($monthly->sum('claim_count')) }}</td>
                <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($monthly->sum('admin_charges'), 2) }}</td>
                <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($monthly->sum('pharmacy'), 2) }}</td>
                <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($monthly->sum('laboratory'), 2) }}</td>
                <td class="text-end"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($monthly->sum('services'), 2) }}</td>
                <td class="text-end total-column"><span style="font-family: 'DejaVu Sans', sans-serif;">&#8358;</span>{{ number_format($monthly->sum('total_amount'), 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
