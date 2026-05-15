@extends('layouts.app')
@section('title', $facility->name . ' - Stock Requests')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>.status-pill{cursor:pointer;transition:all 0.2s}.status-pill:hover,.status-pill.active{transform:scale(1.05);box-shadow:0 2px 8px rgba(0,0,0,0.2)}</style>
@endpush
@section('content')
<div class="page-header d-print-none"><div class="container-xl"><div class="row g-2 align-items-center">
<div class="col-md-8">
<ol class="breadcrumb breadcrumb-arrows mb-1">
    <li class="breadcrumb-item"><a href="{{ route('drug-stock-requests.index') }}">Stock Requests</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $facility->name }}</li>
</ol>
<h2 class="page-title"><i class="ti-building me-2 text-primary"></i>{{ $facility->name }}</h2>
<div class="text-muted mt-1">{{ $facility->lga ?? '' }}</div>
</div>
<div class="col-md-4 d-print-none"><div class="d-flex justify-content-end"><a href="{{ route('drug-stock-requests.index') }}" class="btn btn-outline-secondary"><i class="ti-arrow-left me-1"></i>Back</a></div></div>
</div></div></div>
<div class="page-body"><div class="container-xl">
<div class="d-flex flex-wrap gap-2 mb-3">
<span class="badge status-pill bg-secondary py-2 px-3 {{ !$selectedStatus?'active':'' }}" data-status="" style="font-size:.9rem">All ({{ $stats['pending']+$stats['approved']+$stats['rejected']+$stats['dispensed'] }})</span>
@if($stats['pending']>0)<span class="badge status-pill bg-warning py-2 px-3 {{ $selectedStatus=='pending'?'active':'' }}" data-status="pending" style="font-size:.9rem">Pending ({{ $stats['pending'] }})</span>@endif
@if($stats['approved']>0)<span class="badge status-pill bg-success py-2 px-3 {{ $selectedStatus=='approved'?'active':'' }}" data-status="approved" style="font-size:.9rem">Approved ({{ $stats['approved'] }})</span>@endif
@if($stats['rejected']>0)<span class="badge status-pill bg-danger py-2 px-3 {{ $selectedStatus=='rejected'?'active':'' }}" data-status="rejected" style="font-size:.9rem">Rejected ({{ $stats['rejected'] }})</span>@endif
@if($stats['dispensed']>0)<span class="badge status-pill bg-info py-2 px-3 {{ $selectedStatus=='dispensed'?'active':'' }}" data-status="dispensed" style="font-size:.9rem">Dispensed ({{ $stats['dispensed'] }})</span>@endif
</div>
<div class="card"><div class="card-header"><h3 class="card-title" id="tableTitle">{{ $selectedStatus?ucfirst($selectedStatus).' Requests':'All Requests' }}</h3>
@if($isBoschmaAdmin)<div class="card-actions">
                    <button class="btn btn-success btn-sm" id="bulkApproveBtn" disabled><i class="ti-check me-1"></i>Bulk Approve (<span id="selectedCount">0</span>)</button>
                    <button class="btn btn-danger btn-sm" id="bulkRejectBtn" disabled><i class="ti-x me-1"></i>Bulk Reject (<span id="selectedCountReject">0</span>)</button>
                    <button class="btn btn-primary btn-sm" id="bulkDispenseBtn" disabled><i class="ti-package me-1"></i>Bulk Dispense (<span id="selectedCountDispense">0</span>)</button>
                </div>@endif
</div><div class="card-body"><div class="table-responsive">
<table id="requestsTable" class="table table-vcenter table-hover"><thead><tr>
<th class="w-1"><input type="checkbox" class="form-check-input" id="selectAll"></th>
<th>ID</th><th>Drug</th><th>Program</th><th>Qty</th><th>Cost</th><th>Priority</th><th>Status</th><th>Date</th><th class="w-1">Actions</th>
</tr></thead><tbody></tbody></table>
</div></div></div>
</div></div>
@include('drug-stock-requests.partials.modals')
@endsection
@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
let currentStatus='{{ $selectedStatus }}';
let table=$('#requestsTable').DataTable({processing:true,serverSide:true,
ajax:{url:'{{ route("drug-stock-requests.facility-requests",$facility->id) }}',data:function(d){d.status=currentStatus;}},
columns:[{data:'checkbox',orderable:false,searchable:false},{data:'request_id'},{data:'drug_info'},{data:'program_name'},{data:'quantity'},{data:'cost'},{data:'priority'},{data:'status'},{data:'requested'},{data:'action',orderable:false}],
order:[[6,'desc']],pageLength:25});
$('.status-pill').on('click',function(){currentStatus=$(this).data('status');$('.status-pill').removeClass('active');$(this).addClass('active');
$('#tableTitle').text(currentStatus?currentStatus.charAt(0).toUpperCase()+currentStatus.slice(1)+' Requests':'All Requests');table.ajax.reload();});
function approveRequest(id){$('#approveForm').attr('action','{{ route("drug-stock-requests.approve",":id") }}'.replace(':id',id));new bootstrap.Modal($('#approveModal')[0]).show();}
function rejectRequest(id){$('#rejectForm').attr('action','{{ route("drug-stock-requests.reject",":id") }}'.replace(':id',id));new bootstrap.Modal($('#rejectModal')[0]).show();}
let selectedRequests=[];
function updateBulkButton(){$('#selectedCount').text(selectedRequests.length);$('#selectedCountReject').text(selectedRequests.length);$('#selectedCountDispense').text(selectedRequests.length);$('#bulkApproveBtn').prop('disabled',selectedRequests.length===0);$('#bulkRejectBtn').prop('disabled',selectedRequests.length===0);$('#bulkDispenseBtn').prop('disabled',selectedRequests.length===0);}
$(document).on('change','#selectAll',function(){let c=$(this).is(':checked');$('.request-checkbox:not(:disabled)').prop('checked',c);selectedRequests=[];if(c)$('.request-checkbox:checked').each(function(){selectedRequests.push($(this).val());});updateBulkButton();});
$(document).on('change','.request-checkbox',function(){let id=$(this).val();if($(this).is(':checked')){if(!selectedRequests.includes(id))selectedRequests.push(id);}else{selectedRequests=selectedRequests.filter(r=>r!==id);$('#selectAll').prop('checked',false);}updateBulkButton();});
$('#bulkApproveBtn').on('click',function(){if(selectedRequests.length>0){$('#bulkRequestIds').val(JSON.stringify(selectedRequests));$('#bulkApproveCount').text(selectedRequests.length);new bootstrap.Modal($('#bulkApproveModal')[0]).show();}});
$('#bulkRejectBtn').on('click',function(){if(selectedRequests.length>0){$('#bulkRejectRequestIds').val(JSON.stringify(selectedRequests));$('#bulkRejectCount').text(selectedRequests.length);new bootstrap.Modal($('#bulkRejectModal')[0]).show();}});
$('#bulkDispenseBtn').on('click',function(){if(selectedRequests.length>0){$('#bulkDispenseCount').text(selectedRequests.length);loadBulkDispenseContent();new bootstrap.Modal($('#bulkDispenseModal')[0]).show();}});
function loadBulkDispenseContent(){const approvedRequests=selectedRequests.filter(id=>{const row=$('.request-checkbox[value="'+id+'"]').closest('tr');return row.find('.badge').hasClass('bg-success');});$('#bulkDispenseContent').html('<div class="alert alert-warning">Only approved requests can be dispensed. Found '+approvedRequests.length+' approved requests out of '+selectedRequests.length+' selected.</div>');}
$('#requestsTable').on('draw.dt',function(){selectedRequests=[];$('#selectAll').prop('checked',false);updateBulkButton();});
</script>
@endpush
