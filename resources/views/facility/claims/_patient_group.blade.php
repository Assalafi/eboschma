@php
    $firstItem = $items->first();
    $patientName = $firstItem['patient_name'];
    $enrolleeNumber = $firstItem['enrollee_number'];
    $enrolleeType = $firstItem['enrollee_type'];
    $patientTotal = $items->sum('cost');
    $claimableItems = $items->where('can_claim', true);
    $hasClaimable = $claimableItems->count() > 0;
    $initials = collect(explode(' ', $patientName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp

<div class="cl-patient-group">
    <div class="cl-patient-header">
        <div class="patient-info">
            @if ($showClaimBtn && $hasClaimable)
                <input type="checkbox" class="cl-select-all" title="Select all claimable" onclick="event.stopPropagation()">
            @endif
            <div class="patient-avatar">{{ $initials }}</div>
            <div>
                <strong style="font-size:14px">{{ $patientName }}</strong>
                <span class="cl-badge cl-badge-blue" style="margin-left:4px">{{ $enrolleeNumber }}</span>
                <span class="cl-badge cl-badge-gray">{{ ucfirst($enrolleeType) }}</span>
                <div class="patient-meta">
                    {{ $items->where('type', 'service')->count() }} service(s) &bull; {{ $items->where('type', 'drug')->count() }} drug(s)
                    @if($items->where('type', 'admin')->count() > 0)
                        &bull; {{ $items->where('type', 'admin')->count() }} admin charge(s)
                    @endif
                    @if ($items->first()['visit_date'])
                        &bull; Visit: {{ \Carbon\Carbon::parse($items->first()['visit_date'])->format('d M Y') }}
                    @endif
                    @if ($items->first()['claim_number'])
                        &bull; <span class="cl-badge cl-badge-green" style="font-size:10px">{{ $items->first()['claim_number'] }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="patient-total">₦{{ number_format($patientTotal, 2) }}</div>
            <span class="toggle-arrow" style="color:#94a3b8;font-size:12px">▼</span>
        </div>
    </div>

    <div class="cl-patient-body">
        <table class="cl-items-table">
            <thead>
                <tr>
                    @if ($showClaimBtn && $hasClaimable)
                        <th style="width:36px"></th>
                    @endif
                    <th>Type</th>
                    <th>Item</th>
                    <th>Source</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Cost (₦)</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Claim</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items->sortByDesc('date') as $item)
                    <tr style="{{ $item['is_claimed'] ? 'opacity:.6' : '' }}">
                        @if ($showClaimBtn && $hasClaimable)
                            <td>
                                @if ($item['can_claim'])
                                    <input type="checkbox" class="item-cb"
                                        data-patient="{{ $patientId }}"
                                        data-patient-name="{{ $patientName }}"
                                        data-enrollee="{{ $enrolleeNumber }}"
                                        data-type="{{ $item['type'] }}"
                                        data-id="{{ $item['id'] }}"
                                        data-name="{{ $item['item_name'] }}"
                                        data-cost="{{ $item['cost'] }}"
                                        data-quantity="{{ $item['quantity'] }}"
                                        data-source="{{ $item['source_type'] }}"
                                        data-from-facility="{{ $item['from_facility_name'] ?? '' }}"
                                        data-service-type="{{ $item['service_type_name'] ?? 'Service' }}"
                                        data-service-category="{{ $item['service_category_name'] ?? 'Category' }}">
                                @endif
                            </td>
                        @endif
                        <td>
                            @if ($item['type'] === 'drug')
                                <span class="cl-badge cl-badge-blue"><i class="ti-package" style="font-size:10px"></i> Drug</span>
                            @elseif ($item['type'] === 'admin')
                                <span class="cl-badge cl-badge-amber"><i class="ti-settings" style="font-size:10px"></i> Admin</span>
                            @else
                                <span class="cl-badge cl-badge-purple"><i class="ti-briefcase" style="font-size:10px"></i> Service</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item['item_name'] }}</strong>
                            <div style="font-size:11px;color:#94a3b8">{{ $item['item_detail'] }}</div>
                        </td>
                        <td>
                            @if ($item['source_type'] === 'service_referral')
                                <span class="cl-source-tag cl-source-referral" title="Service referred from {{ $item['from_facility_name'] }}">
                                    <i class="ti-arrow-right" style="font-size:9px"></i> {{ Str::limit($item['from_facility_name'], 20) }}
                                </span>
                            @elseif ($item['source_type'] === 'patient_referral')
                                <span class="cl-source-tag cl-source-referral" title="Patient referred from {{ $item['from_facility_name'] }}">
                                    <i class="ti-user" style="font-size:9px"></i> {{ Str::limit($item['from_facility_name'], 20) }}
                                </span>
                            @else
                                <span class="cl-source-tag cl-source-direct">
                                    <i class="ti-home" style="font-size:9px"></i> Direct
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $encStatus = $item['encounter_status'];
                                $statusClass = match(true) {
                                    $encStatus === 'Completed' => 'cl-badge-green',
                                    str_contains(strtolower($encStatus), 'progress') || str_contains(strtolower($encStatus), 'ongoing') => 'cl-badge-amber',
                                    default => 'cl-badge-gray',
                                };
                            @endphp
                            <span class="cl-badge {{ $statusClass }}">{{ $encStatus }}</span>
                            @if ($tabName === 'ongoing' && !$item['all_drugs_dispensed'] && ($item['has_drugs_in_encounter'] ?? false))
                                <div style="font-size:10px;color:#dc2626;margin-top:2px">Drugs pending</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-end"><strong>₦{{ number_format($item['cost'], 2) }}</strong></td>
                        <td class="text-center" style="font-size:12px;white-space:nowrap">
                            {{ $item['date'] ? $item['date']->format('d M Y') : 'N/A' }}
                        </td>
                        <td class="text-center">
                            @if ($item['is_claimed'])
                                <span class="cl-badge cl-badge-green"><i class="ti-check" style="font-size:9px"></i> Claimed</span>
                            @else
                                <span class="cl-badge cl-badge-gray">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc">
                    @if ($showClaimBtn && $hasClaimable)
                        <td></td>
                    @endif
                    <td colspan="5" class="text-end"><strong>Patient Total:</strong></td>
                    <td></td>
                    <td class="text-end"><strong style="color:#006634">₦{{ number_format($patientTotal, 2) }}</strong></td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
