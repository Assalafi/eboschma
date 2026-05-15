<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="#" id="approveForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Stock Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this stock request?</p>
                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Add any notes for this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('drug-stock-requests.bulk-approve') }}" id="bulkApproveForm">
            @csrf
            <input type="hidden" name="request_ids" id="bulkRequestIds">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Approve Stock Requests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to approve <strong><span id="bulkApproveCount">0</span></strong> pending stock requests.</p>
                    <div class="alert alert-warning">
                        <i class="ti-alert-circle me-1"></i>This action cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Add any notes for this bulk approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve All Selected</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('drug-stock-requests.bulk-reject') }}" id="bulkRejectForm">
            @csrf
            <input type="hidden" name="request_ids" id="bulkRejectRequestIds">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Reject Stock Requests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to reject <strong><span id="bulkRejectCount">0</span></strong> pending stock requests.</p>
                    <div class="alert alert-danger">
                        <i class="ti-alert-triangle me-1"></i>This action cannot be undone. Rejected requests will need to be resubmitted.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejecting these requests..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject All Selected</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Dispense Modal -->
<div class="modal fade" id="bulkDispenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Dispense Stock Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to dispense <strong><span id="bulkDispenseCount">0</span></strong> approved stock requests.</p>
                <div class="alert alert-info">
                    <i class="ti-info-alt me-1"></i>This will create stock records for all selected approved requests. Make sure you have sufficient stock in the central store.
                </div>
                
                <div id="bulkDispenseContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bulkDispenseBtn">Dispense All Selected</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="#" id="rejectForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Stock Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this stock request?</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
