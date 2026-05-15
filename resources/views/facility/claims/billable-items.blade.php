@extends('layouts.facility')

@section('title', 'Claims - Smart Claims')

@section('content')
<style>
:root { --cl-primary: #006634; --cl-primary-dark: #004d28; --cl-primary-light: #e6f7f0; --cl-border: #e2e8f0; }
.cl-header { background: linear-gradient(135deg, var(--cl-primary-dark), var(--cl-primary)); border-radius: 14px; padding: 18px 24px; color: #fff; margin-bottom: 20px; }
.cl-header * { color: #fff !important; }
.cl-header .breadcrumb { background: transparent !important; border: none !important; padding: 0 !important; margin: 0 !important; }
.cl-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.7) !important; }
.cl-header h4 { font-weight: 700; color: #fff; margin: 0; }
.cl-card { background: #fff; border-radius: 14px; border: 1px solid var(--cl-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.cl-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.cl-badge-green { background: #dcfce7; color: #166534; }
.cl-badge-amber { background: #fef3c7; color: #92400e; }
.cl-badge-blue { background: #dbeafe; color: #1e40af; }
.cl-badge-red { background: #fee2e2; color: #991b1b; }
.cl-badge-purple { background: #f3e8ff; color: #6b21a8; }
.cl-badge-gray { background: #f1f5f9; color: #475569; }
.cl-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--cl-border); padding: 0 4px; overflow-x: auto; }
.cl-tab { padding: 12px 18px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; border: none; background: none; white-space: nowrap; display: flex; align-items: center; gap: 6px; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .15s; }
.cl-tab:hover { color: var(--cl-primary); }
.cl-tab.active { color: var(--cl-primary); font-weight: 700; border-bottom-color: var(--cl-primary); }
.cl-tab .tab-count { padding: 1px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; }
.cl-toolbar { display: flex; align-items: center; gap: 10px; padding: 12px 20px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; background: #fafcfb; }
.cl-search-wrap { position: relative; min-width: 220px; max-width: 340px; flex-shrink: 0; }
.cl-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #94a3b8; pointer-events: none; }
.cl-search-input { width: 100%; padding: 7px 12px 7px 32px; border: 1.5px solid var(--cl-border); border-radius: 8px; font-size: 13px; background: #fff; }
.cl-search-input:focus { border-color: var(--cl-primary); outline: none; box-shadow: 0 0 0 3px rgba(98,89,202,.1); }
.cl-filter { padding: 6px 10px; border: 1.5px solid var(--cl-border); border-radius: 7px; font-size: 12px; color: #475569; background: #fff; cursor: pointer; }
.cl-filter:focus { border-color: var(--cl-primary); outline: none; }
.cl-body { min-height: 200px; }
.cl-patient-group { border-bottom: 1px solid #f1f5f9; }
.cl-patient-group:last-child { border-bottom: none; }
.cl-patient-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; background: #fafcfe; cursor: pointer; transition: background .15s; }
.cl-patient-header:hover { background: var(--cl-primary-light); }
.cl-patient-header .patient-info { display: flex; align-items: center; gap: 10px; }
.cl-patient-header .patient-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--cl-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
.cl-patient-header .patient-meta { font-size: 12px; color: #64748b; }
.cl-patient-header .patient-total { font-size: 13px; font-weight: 600; color: #1e293b; }
.cl-items-table { width: 100%; font-size: 13px; }
.cl-items-table th { background: #f8fafc; padding: 8px 16px; font-weight: 600; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; border-bottom: 1px solid #f1f5f9; }
.cl-items-table td { padding: 10px 16px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
.cl-items-table tr:hover td { background: #fafcfe; }
.cl-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.cl-btn-primary { background: var(--cl-primary); color: #fff; }
.cl-btn-primary:hover { background: var(--cl-primary-dark); color: #fff; }
.cl-btn-outline { background: transparent; border: 1.5px solid var(--cl-border); color: #64748b; }
.cl-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.cl-btn-sm { padding: 4px 10px; font-size: 11px; }
.cl-stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px; }
.cl-stat { background: #fff; border-radius: 12px; border: 1px solid var(--cl-border); padding: 14px 18px; }
.cl-stat-value { font-size: 22px; font-weight: 700; color: #1e293b; }
.cl-stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .3px; }
.cl-source-tag { display: inline-flex; align-items: center; gap: 3px; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; }
.cl-source-direct { background: #dbeafe; color: #1e40af; }
.cl-source-referral { background: #fef3c7; color: #92400e; }
.cl-empty { text-align: center; padding: 40px 20px; color: #94a3b8; }
.cl-empty i { font-size: 40px; margin-bottom: 10px; display: block; }
.cl-tab-panel { display: none; }
.cl-tab-panel.active { display: block; }
.cl-select-all { margin-right: 6px; }
.cl-claim-bar { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 2px solid var(--cl-primary); padding: 12px 20px; z-index: 1050; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); }
.cl-claim-bar.visible { display: flex; align-items: center; justify-content: space-between; }
</style>

    <div class="container-fluid">

        {{-- Header --}}
        <div class="cl-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav style="--bs-breadcrumb-divider: '/'" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size:12px">
                        <li class="breadcrumb-item"><a href="{{ route('facility.dashboard') }}" style="color:rgba(255,255,255,.7)">Dashboard</a></li>
                        <li class="breadcrumb-item active" style="color:#fff">Claims</li>
                    </ol>
                </nav>
                <h4>Claims Management</h4>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('facility.claims.list') }}" class="cl-btn cl-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
                    <i class="ti-receipt"></i> Claimed History
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="ti-alert-circle mr-2"></i>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="ti-check mr-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Summary Stats --}}
        <div class="cl-stat-cards">
            <div class="cl-stat">
                <div class="cl-stat-value" style="color:var(--cl-primary)">{{ $counts['awaiting'] }}</div>
                <div class="cl-stat-label">Awaiting Claim</div>
            </div>
            <div class="cl-stat">
                <div class="cl-stat-value" style="color:#d97706">{{ $counts['referrals'] }}</div>
                <div class="cl-stat-label">Referral Services</div>
            </div>
            <div class="cl-stat">
                <div class="cl-stat-value" style="color:#2563eb">{{ $counts['ongoing'] }}</div>
                <div class="cl-stat-label">Ongoing Encounters</div>
            </div>
            <div class="cl-stat">
                <div class="cl-stat-value">₦{{ number_format($totalUnclaimed, 2) }}</div>
                <div class="cl-stat-label">Total Unclaimed</div>
            </div>
            <div class="cl-stat">
                <div class="cl-stat-value" style="color:#6b21a8">₦{{ number_format($totalClaimed, 2) }}</div>
                <div class="cl-stat-label">Total Claimed</div>
            </div>
        </div>

        {{-- Tabbed Card --}}
        <div class="cl-card">
            <div class="cl-tabs" role="tablist">
                <button class="cl-tab {{ $tab === 'awaiting' ? 'active' : '' }}" data-tab="awaiting" type="button">
                    <i class="ti-check-box" style="font-size:14px"></i> Awaiting Claim
                    <span class="tab-count" style="background:var(--cl-primary-light);color:var(--cl-primary)">{{ $counts['awaiting'] }}</span>
                </button>
                <button class="cl-tab {{ $tab === 'referrals' ? 'active' : '' }}" data-tab="referrals" type="button">
                    <i class="ti-exchange-vertical" style="font-size:14px"></i> Referred Claims
                    <span class="tab-count" style="background:#fef3c7;color:#d97706">{{ $counts['referrals'] }}</span>
                </button>
                <button class="cl-tab {{ $tab === 'ongoing' ? 'active' : '' }}" data-tab="ongoing" type="button">
                    <i class="ti-timer" style="font-size:14px"></i> Ongoing
                    <span class="tab-count" style="background:#dbeafe;color:#2563eb">{{ $counts['ongoing'] }}</span>
                </button>
                <button class="cl-tab {{ $tab === 'history' ? 'active' : '' }}" data-tab="history" type="button">
                    <i class="ti-archive" style="font-size:14px"></i> Claimed History
                    <span class="tab-count" style="background:#f3e8ff;color:#6b21a8">{{ $counts['history'] }}</span>
                </button>
            </div>

            {{-- Filters toolbar --}}
            <div class="cl-toolbar">
                <div class="cl-search-wrap">
                    <i class="ti-search"></i>
                    <input type="text" class="cl-search-input" id="liveSearch" placeholder="Search patient, enrollee no, or item..." value="{{ $search }}">
                </div>
                <label style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px">From
                    <input type="date" class="cl-filter" id="filterDateFrom" value="{{ $dateFrom }}">
                </label>
                <label style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px">To
                    <input type="date" class="cl-filter" id="filterDateTo" value="{{ $dateTo }}">
                </label>
                <select class="cl-filter" id="filterItemType">
                    <option value="">All Types</option>
                    <option value="drug" {{ $itemType == 'drug' ? 'selected' : '' }}>Drugs Only</option>
                    <option value="service" {{ $itemType == 'service' ? 'selected' : '' }}>Services Only</option>
                    <option value="admin" {{ $itemType == 'admin' ? 'selected' : '' }}>Admin Charges</option>
                </select>
                <select class="cl-filter" id="filterProgram">
                    <option value="">All Programs</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                    @endforeach
                </select>
                <button class="cl-btn cl-btn-outline cl-btn-sm" onclick="applyFilters()"><i class="ti-search"></i> Filter</button>
                <button class="cl-btn cl-btn-outline cl-btn-sm" onclick="resetFilters()"><i class="ti-reload"></i> Reset</button>
            </div>

            {{-- TAB: Awaiting Claim --}}
            <div class="cl-tab-panel {{ $tab === 'awaiting' ? 'active' : '' }}" data-panel="awaiting">
                <div class="cl-body">
                    @if ($awaitingItems->count() > 0)
                        @foreach ($awaitingItems as $patientId => $items)
                            @include('facility.claims._patient_group', ['items' => $items, 'patientId' => $patientId, 'showClaimBtn' => true, 'tabName' => 'awaiting'])
                        @endforeach
                    @else
                        <div class="cl-empty">
                            <i class="ti-check-box"></i>
                            <p>No items awaiting claim.</p>
                            <small>Items appear here when encounters are completed and all drugs are dispensed.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB: Referrals --}}
            <div class="cl-tab-panel {{ $tab === 'referrals' ? 'active' : '' }}" data-panel="referrals">
                <div class="cl-body">
                    @if ($referralItems->count() > 0)
                        @foreach ($referralItems as $patientId => $items)
                            @include('facility.claims._patient_group', ['items' => $items, 'patientId' => $patientId, 'showClaimBtn' => true, 'tabName' => 'referrals'])
                        @endforeach
                    @else
                        <div class="cl-empty">
                            <i class="ti-exchange-vertical"></i>
                            <p>No referral services found.</p>
                            <small>Services referred from other facilities will appear here.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB: Ongoing --}}
            <div class="cl-tab-panel {{ $tab === 'ongoing' ? 'active' : '' }}" data-panel="ongoing">
                <div class="cl-body">
                    @if ($ongoingItems->count() > 0)
                        @foreach ($ongoingItems as $patientId => $items)
                            @include('facility.claims._patient_group', ['items' => $items, 'patientId' => $patientId, 'showClaimBtn' => false, 'tabName' => 'ongoing'])
                        @endforeach
                    @else
                        <div class="cl-empty">
                            <i class="ti-timer"></i>
                            <p>No ongoing encounters with billable items.</p>
                            <small>Items from in-progress encounters will appear here. Complete the encounter and dispense all drugs to claim.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB: Claimed History --}}
            <div class="cl-tab-panel {{ $tab === 'history' ? 'active' : '' }}" data-panel="history">
                <div class="cl-body">
                    @if ($historyItems->count() > 0)
                        @foreach ($historyItems as $patientId => $items)
                            @include('facility.claims._patient_group', ['items' => $items, 'patientId' => $patientId, 'showClaimBtn' => false, 'tabName' => 'history'])
                        @endforeach
                    @else
                        <div class="cl-empty">
                            <i class="ti-archive"></i>
                            <p>No claimed items yet.</p>
                            <small>Items that have been successfully claimed will appear here.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sticky Claim Bar --}}
            <div class="cl-claim-bar" id="claimBar">
                <div>
                    <strong id="barSelectedCount">0</strong> items selected &bull;
                    <strong id="barSelectedTotal">₦0.00</strong>
                </div>
                <button class="cl-btn cl-btn-primary" onclick="openClaimModal()">
                    <i class="ti-check"></i> Create Claim
                </button>
            </div>
        </div>
    </div>

    {{-- Claim Creation Modal --}}
    <div class="modal fade" id="claimModal" tabindex="-1" aria-labelledby="claimModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="claimForm" method="POST" action="{{ route('facility.claims.store-billable') }}">
                    @csrf
                    <input type="hidden" name="patient_id" id="modalPatientId">
                    <div class="modal-header" style="background:var(--cl-primary);color:#fff">
                        <h5 class="modal-title" id="claimModalLabel"><i class="ti-check mr-1"></i> Create Claim</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <style>.modal-header * { color: #fff !important; }</style>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Patient(s)</label>
                                <div id="modalPatientName"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Claim Type <span class="text-danger">*</span></label>
                                <select name="claim_type" class="form-control" id="modalClaimType" required>
                                    <option value="outpatient" selected>Outpatient</option>
                                    <option value="inpatient">Inpatient</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="referral">Referral</option>
                                </select>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-2">Selected Items:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="selectedItemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Item Name</th>
                                        <th>Source</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Cost (₦)</th>
                                    </tr>
                                </thead>
                                <tbody id="selectedItemsBody"></tbody>
                                <tfoot>
                                    <tr style="background:var(--cl-primary);color:#fff">
                                        <th colspan="4" class="text-end">Total Claim Amount:</th>
                                        <th class="text-end" id="modalTotalAmount">₦0.00</th>
                                    </tr>
                                </tfoot>
                                <style>#selectedItemsTable tfoot * { color: #fff !important; }</style>
                            </table>
                        </div>
                        <div id="hiddenInputs"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitClaimBtn">
                            <i class="ti-check mr-1"></i> Create Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
(function(){
    // Tab switching
    document.querySelectorAll('.cl-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.cl-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.cl-tab-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.querySelector('[data-panel="' + this.dataset.tab + '"]').classList.add('active');
            uncheckAll();
        });
    });

    // Patient group toggle
    document.querySelectorAll('.cl-patient-header').forEach(function(header) {
        header.addEventListener('click', function(e) {
            if (e.target.type === 'checkbox' || e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
            var body = this.nextElementSibling;
            body.style.display = body.style.display === 'none' ? '' : 'none';
            var arrow = this.querySelector('.toggle-arrow');
            if (arrow) arrow.textContent = body.style.display === 'none' ? '▶' : '▼';
        });
    });

    // Select all per patient group
    document.querySelectorAll('.cl-select-all').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var group = this.closest('.cl-patient-group');
            group.querySelectorAll('.item-cb').forEach(function(icb) {
                icb.checked = cb.checked;
            });
            updateClaimBar();
        });
    });

    // Individual checkbox
    document.querySelectorAll('.item-cb').forEach(function(cb) {
        cb.addEventListener('change', updateClaimBar);
    });

    function uncheckAll() {
        document.querySelectorAll('.item-cb, .cl-select-all').forEach(cb => cb.checked = false);
        updateClaimBar();
    }

    function updateClaimBar() {
        var checked = document.querySelectorAll('.cl-tab-panel.active .item-cb:checked');
        var count = checked.length;
        var total = 0;
        checked.forEach(cb => total += parseFloat(cb.dataset.cost || 0));
        var bar = document.getElementById('claimBar');
        if (count > 0) {
            bar.classList.add('visible');
            document.getElementById('barSelectedCount').textContent = count;
            document.getElementById('barSelectedTotal').textContent = '₦' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        } else {
            bar.classList.remove('visible');
        }
    }

    // Filter functions
    window.applyFilters = function() {
        var params = new URLSearchParams();
        var search = document.getElementById('liveSearch').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var itemType = document.getElementById('filterItemType').value;
        var programId = document.getElementById('filterProgram').value;
        var activeTab = document.querySelector('.cl-tab.active');
        if (search) params.set('search', search);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (itemType) params.set('item_type', itemType);
        if (programId) params.set('program_id', programId);
        if (activeTab) params.set('tab', activeTab.dataset.tab);
        window.location.href = '{{ route("facility.claims.billable") }}?' + params.toString();
    };

    window.resetFilters = function() {
        window.location.href = '{{ route("facility.claims.billable") }}';
    };

    // Enter key on search
    document.getElementById('liveSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); applyFilters(); }
    });

    // Open claim modal
    window.openClaimModal = function() {
        var checked = document.querySelectorAll('.cl-tab-panel.active .item-cb:checked');
        if (checked.length === 0) {
            alert('Please select at least one item to create a claim.');
            return;
        }

        // Group items by patient
        var patients = {};
        var hasReferral = false;

        checked.forEach(function(cb) {
            var patientId = cb.dataset.patient;
            if (!patients[patientId]) {
                patients[patientId] = {
                    id: patientId,
                    name: cb.dataset.patientName,
                    enrollee: cb.dataset.enrollee,
                    items: []
                };
            }
            patients[patientId].items.push({
                type: cb.dataset.type,
                id: cb.dataset.id,
                name: cb.dataset.name,
                cost: parseFloat(cb.dataset.cost),
                qty: parseInt(cb.dataset.quantity) || 1,
                source: cb.dataset.source || 'direct',
                fromFacility: cb.dataset.fromFacility || ''
            });
            if (cb.dataset.source !== 'direct') hasReferral = true;
        });

        // Show multi-patient info if needed
        var patientIds = Object.keys(patients);
        var isMultiPatient = patientIds.length > 1;
        
        var patientInfo = '';
        if (isMultiPatient) {
            patientInfo = '<div class="alert alert-info mb-2"><i class="ti-info-alt mr-1"></i> Creating claims for ' + patientIds.length + ' patients</div>';
            patientIds.forEach(function(pid) {
                patientInfo += '<div class="mb-1"><strong>' + patients[pid].name + '</strong> (' + patients[pid].enrollee + ') - ' + patients[pid].items.length + ' items</div>';
            });
        } else {
            var p = patients[patientIds[0]];
            patientInfo = '<div><strong>' + p.name + '</strong><br><small class="text-muted">' + p.enrollee + '</small></div>';
        }

        document.getElementById('modalPatientId').value = isMultiPatient ? 'multiple' : patientIds[0];
        document.getElementById('modalPatientName').innerHTML = patientInfo;

        var tbody = document.getElementById('selectedItemsBody');
        var hiddenInputs = document.getElementById('hiddenInputs');
        tbody.innerHTML = '';
        hiddenInputs.innerHTML = '';
        var total = 0;

        checked.forEach(function(cb) {
            var type = cb.dataset.type;
            var id = cb.dataset.id;
            var name = cb.dataset.name;
            var cost = parseFloat(cb.dataset.cost);
            var qty = parseInt(cb.dataset.quantity) || 1;
            var source = cb.dataset.source || 'direct';
            var fromFac = cb.dataset.fromFacility || '';
            var patientId = cb.dataset.patient;

            if (source !== 'direct') hasReferral = true;

            var typeBadge = type === 'drug'
                ? '<span class="cl-badge cl-badge-blue">Drug</span>'
                : (type === 'admin' ? '<span class="cl-badge cl-badge-amber">Admin</span>' : '<span class="cl-badge cl-badge-purple">Service</span>');

            var sourceTag = source === 'direct'
                ? '<span class="cl-source-tag cl-source-direct">Direct</span>'
                : '<span class="cl-source-tag cl-source-referral">Ref: ' + fromFac + '</span>';

            var patientName = cb.dataset.patientName;
            tbody.innerHTML += '<tr><td>' + typeBadge + '</td><td>' + name + '<br><small class="text-muted">' + patientName + '</small></td><td>' + sourceTag + '</td><td class="text-center">' + qty + '</td><td class="text-end">₦' + cost.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td></tr>';
            total += cost;

            var prefix, nameKey;
            if (type === 'drug') {
                prefix = 'drug_items[' + id + ']';
                nameKey = 'drug_name';
            } else if (type === 'admin') {
                prefix = 'admin_items[' + id + ']';
                nameKey = 'service_name';
            } else {
                prefix = 'service_items[' + id + ']';
                nameKey = 'service_name';
            }
            hiddenInputs.innerHTML += '<input type="hidden" name="' + prefix + '[cost]" value="' + cost + '">';
            hiddenInputs.innerHTML += '<input type="hidden" name="' + prefix + '[' + nameKey + ']" value="' + name + '">';
            hiddenInputs.innerHTML += '<input type="hidden" name="' + prefix + '[quantity]" value="' + qty + '">';
            hiddenInputs.innerHTML += '<input type="hidden" name="' + prefix + '[patient_id]" value="' + patientId + '">';
        });

        document.getElementById('modalTotalAmount').textContent = '₦' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

        if (hasReferral) {
            document.getElementById('modalClaimType').value = 'referral';
        }

        var modal = new bootstrap.Modal(document.getElementById('claimModal'));
        modal.show();
    };

    // Form submit loading
    document.getElementById('claimForm').addEventListener('submit', function() {
        var btn = document.getElementById('submitClaimBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ti-reload fa-spin mr-1"></i> Creating...';
    });
})();
</script>
@endpush
@endsection
