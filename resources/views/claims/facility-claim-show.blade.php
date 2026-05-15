@extends('layouts.app')

@section('title', 'Facility Claim Details')

@section('content')
<style>
:root { --g: #006634; --gl: #e6f7f0; --gd: #004d28; }
.claim-doc { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #ccc; padding: 0; font-size: 13px; }
.claim-doc table { width: 100%; border-collapse: collapse; }
.claim-doc td, .claim-doc th { border: 1px solid #999; padding: 6px 10px; }
.claim-info td { border: 1px solid #999; padding: 8px 12px; font-size: 13px; }
.claim-info td strong { font-weight: 700; }
.section-header { background: var(--g); color: #fff; text-align: center; font-weight: 700; font-size: 13px; padding: 8px; text-transform: uppercase; }
.section-header td { border: 1px solid var(--gd); color: #fff !important; }
.items-head th { background: var(--gl); font-weight: 700; font-size: 12px; text-align: center; border: 1px solid #999; padding: 6px 8px; }
.items-body td { text-align: center; font-size: 13px; border: 1px solid #ccc; padding: 6px 8px; }
.items-body td:nth-child(2) { text-align: left; }
.sub-total td { background: var(--gl); font-weight: 700; border: 1px solid #999; }
.grand-total td { background: var(--g); color: #fff !important; font-weight: 700; font-size: 14px; border: 1px solid var(--gd); }
.result-inline { background: #f8fafc; border-left: 3px solid #059669; padding: 6px 10px; margin: 4px 0; border-radius: 0 6px 6px 0; font-size: 12px; }
.result-doc-link { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; background: #dbeafe; color: #1e40af; border-radius: 4px; font-size: 11px; text-decoration: none; margin: 2px; }
.result-doc-link:hover { background: #bfdbfe; color: #1e3a8a; }
.wf-card { max-width: 900px; margin: 20px auto; background: #fff; border: 1px solid #ccc; border-radius: 6px; overflow: hidden; }
.wf-header { background: var(--g); color: #fff; padding: 12px 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.wf-body { padding: 20px; }
.wf-timeline { position: relative; padding-left: 30px; }
.wf-timeline::before { content: ''; position: absolute; left: 11px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
.wf-step { position: relative; margin-bottom: 24px; }
.wf-step:last-child { margin-bottom: 0; }
.wf-dot { position: absolute; left: -30px; top: 2px; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; z-index: 1; }
.wf-dot-done { background: #059669; color: #fff; }
.wf-dot-active { background: #f59e0b; color: #fff; animation: pulse 1.5s infinite; }
.wf-dot-pending { background: #e2e8f0; color: #94a3b8; }
.wf-dot-rejected { background: #dc2626; color: #fff; }
@keyframes pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(245,158,11,.4); } 50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); } }
.wf-title { font-weight: 700; font-size: 14px; color: #1e293b; }
.wf-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.wf-notes { background: #fefce8; border-left: 3px solid #f59e0b; padding: 6px 10px; border-radius: 0 4px 4px 0; font-size: 12px; margin-top: 6px; color: #713f12; }
.wf-action-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-top: 16px; }
.wf-action-box h6 { color: var(--g); font-weight: 700; margin-bottom: 12px; }
.edit-input { width: 70px; text-align: center; border: 1px solid #059669; border-radius: 4px; padding: 2px 4px; font-size: 12px; }
.edit-btn { cursor: pointer; color: #059669; font-size: 11px; border: none; background: none; padding: 0 3px; }
.edit-btn:hover { color: #047857; }
.del-btn { cursor: pointer; color: #dc2626; font-size: 11px; border: none; background: none; padding: 0 3px; }
.del-btn:hover { color: #b91c1c; }
.reject-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-top: 12px; display: none; }
@media print { .d-print-none { display: none !important; } .claim-doc { border: none; max-width: 100%; } .wf-card { display: none; } }
</style>

    <div class="container-fluid">

        {{-- Action Buttons --}}
        <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
            <div>
                <a href="{{ route('claims.facility.show', $claim->facility_id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti-arrow-left"></i> Back to {{ $claim->facility_name }}
                </a>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('claims.facility-claim.download-pdf', $claim->id) }}" class="btn btn-sm btn-success">
                    <i class="ti-download"></i> Download PDF
                </a>
                <a href="#" onclick="window.print()" class="btn btn-sm btn-outline-primary">
                    <i class="ti-printer"></i> Print
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" style="max-width:900px;margin:0 auto 12px" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" style="max-width:900px;margin:0 auto 12px" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Claim Document --}}
        <div class="claim-doc">

            {{-- Patient / Claim Info Header --}}
            <table class="claim-info" style="margin-bottom:0">
                <tr>
                    <td style="width:55%"><strong>Healthcare Provider:</strong> &nbsp; {{ $claim->facility_name }}</td>
                    <td><strong>HCP BOSCHMA Reg No:</strong> &nbsp; {{ $claim->boschma_no ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>Enrollee's Name:</strong> &nbsp; {{ $claim->patient_name }}</td>
                    <td><strong>Sex:</strong> &nbsp; {{ $claim->gender ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Date of Birth/Age:</strong> &nbsp; {{ $claim->date_of_birth ? \Carbon\Carbon::parse($claim->date_of_birth)->format('Y-m-d') : 'N/A' }}</td>
                    <td><strong>Date:</strong> &nbsp; {{ \Carbon\Carbon::parse($claim->service_date)->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td><strong>Enrollee's ID No:</strong> &nbsp; {{ $claim->enrollee_number }}</td>
                    <td><strong>Patient Type:</strong> &nbsp;
                        @php
                            $hasAdmission = DB::table('admissions')->where('patient_id', $claim->patient_id)->exists();
                            $patientType = $hasAdmission ? 'IN' : 'OUT';
                        @endphp
                        <span class="badge bg-{{ $patientType === 'IN' ? 'primary' : 'secondary' }}">
                            {{ $patientType }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Presentation:</strong> &nbsp;
                        @php
                            $presentingComplaints = '';
                            if ($consultations && $consultations->isNotEmpty()) {
                                $firstConsultation = $consultations->first();
                                $presentingComplaints = $firstConsultation->presenting_complaints ?? 'N/A';
                                if (strlen($presentingComplaints) > 200) {
                                    $presentingComplaints = substr($presentingComplaints, 0, 200) . '...';
                                }
                            } else {
                                $presentingComplaints = 'N/A';
                            }
                        @endphp
                        <span style="font-size:13px;line-height:1.4">{{ $presentingComplaints }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Clinical Findings:</strong> &nbsp;
                        @php
                            $physicalExamination = '';
                            if ($consultations && $consultations->isNotEmpty()) {
                                $firstConsultation = $consultations->first();
                                $physicalExamination = $firstConsultation->physical_examination ?? 'N/A';
                                if (strlen($physicalExamination) > 200) {
                                    $physicalExamination = substr($physicalExamination, 0, 200) . '...';
                                }
                            } else {
                                $physicalExamination = 'N/A';
                            }
                        @endphp
                        <span style="font-size:13px;line-height:1.4">{{ $physicalExamination }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Diagnosis:</strong> &nbsp;
                        @if (count($confirmedDiagnoses) > 0)
                            <span style="color:var(--g);font-weight:600">
                                {{ collect($confirmedDiagnoses)->pluck('description')->implode(', ') }}
                            </span>
                        @elseif (count($provisionalDiagnoses) > 0)
                            <span style="color:#b45309;font-style:italic">
                                {{ collect($provisionalDiagnoses)->pluck('description')->implode(', ') }}
                                <small>(Provisional)</small>
                            </span>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Diagnosis Detail Row --}}
            @if (count($provisionalDiagnoses) > 0 || count($confirmedDiagnoses) > 0)
            <table style="margin-top:-1px">
                @if (count($provisionalDiagnoses) > 0)
                <tr>
                    <td style="width:120px;background:var(--gl);font-weight:700;font-size:12px;vertical-align:top">Provisional Dx:</td>
                    <td>
                        @foreach ($provisionalDiagnoses as $dx)
                            <span style="display:inline-block;background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:4px;font-size:12px;margin:2px">
                                {{ $dx['code'] }}{{ $dx['code'] ? ' — ' : '' }}{{ $dx['description'] }}
                            </span>
                        @endforeach
                    </td>
                </tr>
                @endif
                @if (count($confirmedDiagnoses) > 0)
                <tr>
                    <td style="width:120px;background:var(--gl);font-weight:700;font-size:12px;vertical-align:top">Confirmed Dx:</td>
                    <td>
                        @foreach ($confirmedDiagnoses as $dx)
                            <span style="display:inline-block;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:4px;font-size:12px;margin:2px">
                                {{ $dx['code'] }}{{ $dx['code'] ? ' — ' : '' }}{{ $dx['description'] }}
                            </span>
                        @endforeach
                    </td>
                </tr>
                @endif
            </table>
            @endif

            {{-- MEDICATIONS --}}
            <table style="margin-top:-1px">
                <tr class="section-header">
                    <td colspan="{{ $userPermissions['canEditItems'] ? 7 : 6 }}" style="text-align:center">Services Provided<br>Medication(s)</td>
                </tr>
                <tr class="items-head">
                    <th style="width:50px">S/N</th>
                    <th>Medication(s)</th>
                    <th style="width:100px">Rate</th>
                    <th style="width:100px">Frequency</th>
                    <th style="width:110px">Amount Claimed</th>
                    <th style="width:110px">Amount Due</th>
                    @if($userPermissions['canEditItems'])
                        <th style="width:70px" class="d-print-none">Actions</th>
                    @endif
                </tr>
                @forelse ($medications as $i => $med)
                    <tr class="items-body" id="med-row-{{ $i }}">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $med['name'] }}</td>
                        <td>
                            @if($userPermissions['canEditItems'])
                                <span class="val-display" data-field="rate">{{ number_format($med['cost'] / max(1, $med['quantity']), 0) }}</span>
                            @else
                                {{ number_format($med['cost'] / max(1, $med['quantity']), 0) }}
                            @endif
                        </td>
                        <td>
                            @if($userPermissions['canEditItems'])
                                <span class="val-display" data-field="qty">{{ $med['quantity'] }}</span>
                            @else
                                {{ $med['quantity'] }}
                            @endif
                        </td>
                        <td>{{ number_format($med['cost'], 0) }}</td>
                        <td>{{ number_format($med['cost'], 0) }}</td>
                        @if($userPermissions['canEditItems'])
                            <td class="d-print-none">
                                <button class="edit-btn" onclick="editItem('medication', '{{ $med['id'] }}', {{ $med['cost'] / max(1, $med['quantity']) }}, {{ $med['quantity'] }})" title="Edit"><i class="ti-pencil"></i></button>
                                <button class="del-btn" onclick="deleteItem('medication', '{{ $med['id'] }}', '{{ addslashes($med['name']) }}')" title="Delete"><i class="ti-trash"></i></button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr class="items-body"><td colspan="{{ $userPermissions['canEditItems'] ? 7 : 6 }}" style="text-align:center;color:#999">No medications</td></tr>
                @endforelse
                <tr class="sub-total">
                    <td colspan="4" style="text-align:center;font-weight:700">SUB TOTAL</td>
                    <td style="text-align:center">N {{ number_format(array_sum(array_column($medications, 'cost')), 0) }}</td>
                    <td style="text-align:center">N {{ number_format(array_sum(array_column($medications, 'cost')), 0) }}</td>
                    @if($userPermissions['canEditItems'])<td class="d-print-none"></td>@endif
                </tr>
            </table>

            {{-- RENDERED SERVICES --}}
            <table style="margin-top:-1px">
                <tr class="section-header">
                    <td colspan="{{ $userPermissions['canEditItems'] ? 7 : 6 }}" style="text-align:center">Services Provided<br>Rendered Service(s)</td>
                </tr>
                <tr class="items-head">
                    <th style="width:50px">S/N</th>
                    <th>Service(s)</th>
                    <th style="width:100px">Rate</th>
                    <th style="width:100px">Frequency</th>
                    <th style="width:110px">Amount Claimed</th>
                    <th style="width:110px">Amount Due</th>
                    @if($userPermissions['canEditItems'])
                        <th style="width:70px" class="d-print-none">Actions</th>
                    @endif
                </tr>
                @forelse ($services as $i => $service)
                    <tr class="items-body" id="svc-row-{{ $i }}">
                        <td>{{ $i + 1 }}</td>
                        <td style="text-align:left">
                            {{ $service['name'] }}
                            @if (!empty($service['results']))
                                @foreach ($service['results'] as $result)
                                    <div class="result-inline">
                                        @if ($result['value'])
                                            <strong>Result:</strong> {{ $result['value'] }}
                                            @if ($result['unit']) <small>({{ $result['unit'] }})</small> @endif
                                            @if ($result['reference_range']) <small style="color:#94a3b8">Ref: {{ $result['reference_range'] }}</small> @endif
                                        @endif
                                        @if ($result['remark'])
                                            &nbsp; <span style="padding:1px 6px;border-radius:3px;font-size:11px;{{ in_array($result['remark'], ['Normal','Negative']) ? 'background:#dcfce7;color:#166534' : 'background:#fee2e2;color:#991b1b' }}">{{ $result['remark'] }}</span>
                                        @endif
                                        @if ($result['note'])
                                            <div style="color:#475569;margin-top:3px"><em>{{ $result['note'] }}</em></div>
                                        @endif
                                        @if (!empty($result['documents']))
                                            <div style="margin-top:4px">
                                                @foreach ($result['documents'] as $di => $doc)
                                                    <a href="{{ Storage::url($doc) }}" target="_blank" class="result-doc-link">
                                                        <i class="ti-file"></i> Attachment {{ count($result['documents']) > 1 ? $di + 1 : '' }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if($userPermissions['canEditItems'])
                                <span class="val-display" data-field="price">{{ number_format($service['cost'], 0) }}</span>
                            @else
                                {{ number_format($service['cost'], 0) }}
                            @endif
                        </td>
                        <td>1</td>
                        <td>{{ number_format($service['cost'], 0) }}</td>
                        <td>{{ number_format($service['cost'], 0) }}</td>
                        @if($userPermissions['canEditItems'])
                            <td class="d-print-none">
                                <button class="edit-btn" onclick="editItem('service', '{{ $service['id'] }}', {{ $service['cost'] }}, 1)" title="Edit"><i class="ti-pencil"></i></button>
                                <button class="del-btn" onclick="deleteItem('service', '{{ $service['id'] }}', '{{ addslashes($service['name']) }}')" title="Delete"><i class="ti-trash"></i></button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr class="items-body"><td colspan="{{ $userPermissions['canEditItems'] ? 7 : 6 }}" style="text-align:center;color:#999">No rendered services</td></tr>
                @endforelse
                <tr class="sub-total">
                    <td colspan="4" style="text-align:center;font-weight:700">SUB TOTAL</td>
                    <td style="text-align:center">N {{ number_format(array_sum(array_column($services, 'cost')), 0) }}</td>
                    <td style="text-align:center">N {{ number_format(array_sum(array_column($services, 'cost')), 0) }}</td>
                    @if($userPermissions['canEditItems'])<td class="d-print-none"></td>@endif
                </tr>
            </table>

            {{-- GRAND TOTAL --}}
            <table style="margin-top:10px">
                <tr class="grand-total">
                    <td colspan="4" style="text-align:center">GRAND TOTAL</td>
                    <td style="text-align:center;width:110px">N {{ number_format($claim->total_amount, 0) }}</td>
                    <td style="text-align:center;width:110px">N {{ number_format($claim->total_amount, 0) }}</td>
                </tr>
            </table>

        </div>

        {{-- Approval Workflow --}}
        @php
            $steps = [
                ['key' => 'submitted', 'label' => 'Submitted', 'status' => $claim->status !== 'draft' ? 'done' : 'pending', 'by' => $submittedByName, 'at' => $claim->submitted_at, 'notes' => null],
                ['key' => 'verifier', 'label' => 'Verifier Review', 'status' => $claim->verifier_status ?? 'pending', 'by' => $verifierName, 'at' => $claim->verifier_updated_at ?? null, 'notes' => $claim->verifier_notes ?? null],
                ['key' => 'approver', 'label' => 'Approver Review', 'status' => $claim->approver_status ?? 'pending', 'by' => $approverName, 'at' => $claim->approver_updated_at ?? null, 'notes' => $claim->approver_notes ?? null],
                ['key' => 'es', 'label' => 'Executive Secretary', 'status' => $claim->es_status ?? 'pending', 'by' => $esName, 'at' => $claim->es_updated_at ?? null, 'notes' => $claim->es_notes ?? null],
                ['key' => 'finance', 'label' => 'Finance / Payment', 'status' => $claim->finance_status ?? 'pending', 'by' => $financeName, 'at' => $claim->finance_updated_at ?? null, 'notes' => $claim->finance_notes ?? null],
            ];
            $currentStep = 'submitted';
            // Super Admin can act on draft claims immediately
            if (($claim->verifier_status ?? 'pending') === 'pending' && ($claim->status !== 'draft' || $userPermissions['isSuperAdmin'])) $currentStep = 'verifier';
            if (($claim->verifier_status ?? 'pending') === 'approved' && ($claim->approver_status ?? 'pending') === 'pending') $currentStep = 'approver';
            if (($claim->approver_status ?? 'pending') === 'approved' && ($claim->es_status ?? 'pending') === 'pending') $currentStep = 'es';
            if (($claim->es_status ?? 'pending') === 'approved' && ($claim->finance_status ?? 'pending') === 'pending') $currentStep = 'finance';
            if (($claim->finance_status ?? 'pending') === 'paid') $currentStep = 'done';
        @endphp
        <div class="wf-card d-print-none">
            <div class="wf-header">
                <i class="ti-clipboard-check"></i> Approval Workflow
                <span style="margin-left:auto;font-size:12px;font-weight:400;opacity:.8">Claim #{{ $claim->claim_number ?? 'CLM-' . $claim->id }}</span>
            </div>
            <div class="wf-body">
                <div class="wf-timeline">
                    @foreach ($steps as $step)
                        @php
                            $isDone = $step['status'] === 'done' || $step['status'] === 'approved' || $step['status'] === 'paid';
                            $isActive = $step['key'] === $currentStep;
                            $isRejected = $step['status'] === 'rejected';
                            $dotClass = $isDone ? 'wf-dot-done' : ($isRejected ? 'wf-dot-rejected' : ($isActive ? 'wf-dot-active' : 'wf-dot-pending'));
                            $icon = $isDone ? '✓' : ($isRejected ? '✕' : ($isActive ? '●' : '○'));
                        @endphp
                        <div class="wf-step">
                            <div class="wf-dot {{ $dotClass }}">{{ $icon }}</div>
                            <div class="wf-title">{{ $step['label'] }}
                                @if ($isDone)
                                    <span style="font-size:11px;color:#059669;font-weight:400;margin-left:6px">Approved</span>
                                @elseif ($isRejected)
                                    <span style="font-size:11px;color:#dc2626;font-weight:400;margin-left:6px">Rejected</span>
                                @elseif ($isActive)
                                    <span style="font-size:11px;color:#f59e0b;font-weight:400;margin-left:6px">Awaiting Action</span>
                                @endif
                            </div>
                            @if ($step['by'])
                                <div class="wf-meta">By: {{ $step['by'] }} @if($step['at']) — {{ \Carbon\Carbon::parse($step['at'])->format('d M Y g:i A') }} @endif</div>
                            @endif
                            @if ($step['notes'])
                                <div class="wf-notes">{{ $step['notes'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Admin Actions – Super Admin sees ALL stages, others see only their stage --}}
                @if ($currentStep !== 'done')
                @php
                    // Prioritize current step, but Super Admin can act on any step
                    if ($userPermissions['isSuperAdmin']) {
                        // Super Admin: prioritize current step, but can act on any
                        $canActOnVerifier = $currentStep === 'verifier' && $userPermissions['canVerify'];
                        $canActOnApprover = $currentStep === 'approver' && $userPermissions['canApprove'];
                        $canActOnEs = $currentStep === 'es' && $userPermissions['canEsApprove'];
                        $canActOnFinance = $currentStep === 'finance' && $userPermissions['canFinance'];
                    } else {
                        // Regular users: only act on current step
                        $canActOnVerifier = $currentStep === 'verifier' && $userPermissions['canVerify'];
                        $canActOnApprover = $currentStep === 'approver' && $userPermissions['canApprove'];
                        $canActOnEs = $currentStep === 'es' && $userPermissions['canEsApprove'];
                        $canActOnFinance = $currentStep === 'finance' && $userPermissions['canFinance'];
                    }
                    $canAct = $canActOnVerifier || $canActOnApprover || $canActOnEs || $canActOnFinance;
                @endphp
                @if ($canAct)
                <div class="wf-action-box">
                    <h6><i class="ti-shield-check"></i> Actions
                        @if($userPermissions['isSuperAdmin']) <small style="color:#64748b;font-weight:400">(Super Admin)</small> @endif
                    </h6>
                    <form id="approvalForm" method="POST" action="{{ route('claims.approve', $claim->id) }}">
                        @csrf
                        <input type="hidden" name="approval_type" id="approvalType" value="{{ $currentStep }}">
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;color:#64748b">Notes (optional for approval)</label>
                            <textarea name="notes" id="approvalNotes" class="form-control form-control-sm" rows="2" placeholder="Add notes..."></textarea>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if ($canActOnVerifier)
                                <button type="submit" class="btn btn-sm btn-success" onclick="document.getElementById('approvalType').value='verifier'; return confirm('Are you sure you want to verify and approve this claim? This action will move the claim to the approver stage.')">
                                    <i class="ti-check"></i> Verify & Approve
                                </button>
                            @elseif ($canActOnApprover)
                                <button type="submit" class="btn btn-sm btn-success" onclick="document.getElementById('approvalType').value='approver'; return confirm('Are you sure you want to approve this claim? This action will move the claim to the Executive Secretary stage.')">
                                    <i class="ti-check"></i> RO Approve
                                </button>
                            @elseif ($canActOnEs)
                                <button type="submit" class="btn btn-sm btn-success" onclick="document.getElementById('approvalType').value='es'; return confirm('Are you sure you want to approve this claim? This action will move the claim to the Finance/Payment stage.')">
                                    <i class="ti-check"></i> ES Approve
                                </button>
                            @elseif ($canActOnFinance)
                                <button type="submit" class="btn btn-sm btn-success" onclick="document.getElementById('approvalType').value='finance'; return confirm('Are you sure you want to mark this claim as paid? This will complete the approval workflow.')">
                                    <i class="ti-money"></i> Mark as Paid
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Are you sure you want to reject this claim? You will need to provide rejection comments.')) toggleRejectBox()">
                                <i class="ti-close"></i> Reject
                            </button>
                        </div>
                    </form>

                    {{-- Reject box – requires comments --}}
                    <div class="reject-box" id="rejectBox">
                        <form method="POST" action="{{ route('claims.reject', $claim->id) }}" onsubmit="return validateReject()">
                            @csrf
                            <input type="hidden" name="reject_stage" value="{{ $currentStep }}">
                            <div class="mb-2">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#dc2626">
                                    Rejection Comments <span style="color:red">*</span> (required)
                                </label>
                                <textarea name="rejection_reason" id="rejectionReason" class="form-control form-control-sm" rows="3" placeholder="Explain why this claim is being rejected..." required></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="ti-alert-triangle"></i> Confirm Rejection
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleRejectBox()">Cancel</button>
                            </div>
                            <small class="text-muted mt-1 d-block">Rejection sends the claim back to the previous level for re-review.</small>
                        </form>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>

    </div>

    <script>
    function toggleRejectBox() {
        var box = document.getElementById('rejectBox');
        box.style.display = box.style.display === 'block' ? 'none' : 'block';
    }

    function validateReject() {
        var reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Rejection comments are required!');
            return false;
        }
        return confirm('Are you sure you want to reject this claim? It will be sent back to the previous level.');
    }

    @if($userPermissions['canEditItems'])
    function editItem(type, index, currentPrice, currentQty) {
        var newPrice = prompt('Enter new unit price:', currentPrice);
        if (newPrice === null) return;
        var newQty = currentQty;
        if (type === 'medication') {
            newQty = prompt('Enter new quantity:', currentQty);
            if (newQty === null) return;
        }
        fetch('{{ route("claims.facility-claim.update-item", $claim->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                item_type: type,
                item_index: index,
                price: parseFloat(newPrice),
                quantity: parseInt(newQty)
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { alert(data.message); }
        })
        .catch(e => alert('Error: ' + e.message));
    }

    function deleteItem(type, index, name) {
        if (!confirm('Delete "' + name + '" from this claim?')) return;
        fetch('{{ route("claims.facility-claim.delete-item", $claim->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ item_type: type, item_index: index })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { alert(data.message); }
        })
        .catch(e => alert('Error: ' + e.message));
    }
    @endif
    </script>
@endsection
