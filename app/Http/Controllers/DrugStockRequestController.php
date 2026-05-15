<?php

namespace App\Http\Controllers;

use App\Models\DrugStockRequest;
use App\Models\Drug;
use App\Models\DrugStock;
use App\Models\DrugStoreStock;
use App\Models\Facility;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DrugStockRequestController extends Controller
{
    /**
     * Display a listing of drug stock requests.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('staff')->user();
        $isBoschmaAdmin = $this->isBoschmaAdmin();
        
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            // Facility-grouped view
            if ($request->get('view') === 'facilities') {
                return $this->facilityGroupedData($request, $isBoschmaAdmin, $user);
            }

            $query = DrugStockRequest::with(['facility', 'drug', 'program', 'items.drug', 'requestedBy', 'approvedBy', 'dispensedBy']);
            
            // Filter based on user role
            if (!$isBoschmaAdmin) {
                if ($user) {
                    $query->whereIn('status', ['approved', 'dispensed']);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
            
            return DataTables::of($query)
                ->filter(function ($query) use ($request, $isBoschmaAdmin) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function($q) use ($search) {
                            $q->where('reason', 'LIKE', "%{$search}%")
                              ->orWhere('notes', 'LIKE', "%{$search}%")
                              ->orWhereHas('drug', function($subQ) use ($search) {
                                  $subQ->where('name', 'LIKE', "%{$search}%");
                              })
                              ->orWhereHas('facility', function($subQ) use ($search) {
                                  $subQ->where('name', 'LIKE', "%{$search}%");
                              });
                        });
                    }
                    
                    if ($request->has('status') && !empty($request->get('status'))) {
                        $query->where('status', $request->get('status'));
                    }
                    
                    if ($request->has('priority') && !empty($request->get('priority'))) {
                        $query->where('priority', $request->get('priority'));
                    }
                    
                    if ($request->has('facility_id') && !empty($request->get('facility_id')) && $isBoschmaAdmin) {
                        $query->where('facility_id', $request->get('facility_id'));
                    }
                })
                ->addColumn('checkbox', function($request) {
                    $disabled = $request->status !== 'pending' ? 'disabled' : '';
                    return '<input type="checkbox" class="form-check-input request-checkbox" value="' . $request->id . '" ' . $disabled . '>';
                })
                ->addColumn('request_id', function($request) {
                    return '<span class="text-muted">#' . str_pad($request->id, 6, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('facility_name_raw', function($request) {
                    return $request->facility->name ?? '-';
                })
                ->addColumn('facility_name', function($request) {
                    $name = $request->facility->name ?? '-';
                    $wrapped = strlen($name) > 30 ? substr(e($name), 0, 30) . '...' : e($name);
                    return '<div title="' . e($name) . '">' . $wrapped . '</div>';
                })
                ->addColumn('drug_info', function($request) {
                    if ($request->drug_id) {
                        $drugName = strlen($request->drug->name) > 30 ? substr(e($request->drug->name), 0, 30) . '...' : e($request->drug->name);
                        $strengthInfo = e($request->drug->strength) . ' ' . e($request->drug->unit);
                        $strengthInfo = strlen($strengthInfo) > 25 ? substr($strengthInfo, 0, 25) . '...' : $strengthInfo;
                        
                        return '<div class="fw-bold" title="' . e($request->drug->name) . '">' . $drugName . '</div>' .
                               '<div class="text-muted small" title="' . e($request->drug->strength) . ' ' . e($request->drug->unit) . '">' . $strengthInfo . '</div>';
                    } else {
                        return '<div class="fw-bold text-primary">Bulk Request</div>' .
                               '<div class="text-muted small">' . $request->items->count() . ' items</div>';
                    }
                })
                ->addColumn('program_name', function($request) {
                    return $request->program->name ?? 'N/A';
                })
                ->addColumn('quantity', function($request) {
                    return $request->formatted_quantity;
                })
                ->addColumn('cost', function($request) {
                    return $request->formatted_estimated_cost;
                })
                ->addColumn('priority', function($request) {
                    return $request->priority_badge;
                })
                ->addColumn('status', function($request) {
                    return $request->status_badge;
                })
                ->addColumn('requested', function($request) {
                    return '<div>' . $request->requested_at->format('M j, Y') . '</div>' .
                           '<div class="text-muted small">' . $request->requested_at->format('g:i A') . '</div>';
                })
                ->addColumn('action', function($request) {
                    $actions = '<div class="d-flex gap-1">
                        <a href="' . route('drug-stock-requests.show', $request->id) . '" 
                           class="btn btn-sm btn-info" title="View">
                            👁️
                        </a>';
                    
                    if ($request->canBeEdited()) {
                        $actions .= '<a href="' . route('drug-stock-requests.edit', $request->id) . '" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        ✏️
                                    </a>';
                    }
                    
                    if ($request->canBeApproved()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-success"
                                       onclick="approveRequest(' . $request->id . ')" title="Approve">
                                        ✓
                                    </button>';
                    }
                    
                    if ($request->canBeRejected()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger"
                                       onclick="rejectRequest(' . $request->id . ')" title="Reject">
                                        ✕
                                    </button>';
                    }
                    
                    if ($request->canBeDispensed()) {
                        $actions .= '<a href="' . route('drug-stock-requests.dispense-form', $request->id) . '" 
                                       class="btn btn-sm btn-primary" title="Dispense">
                                        📦
                                    </a>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->order(function ($query) {
                    $query->orderBy('priority', 'desc')->orderBy('requested_at', 'desc');
                })
                ->rawColumns(['checkbox', 'request_id', 'facility_name', 'drug_info', 'priority', 'status', 'requested', 'action'])
                ->make(true);
        }
        
        // Get filter options
        $statuses = DrugStockRequest::getStatuses();
        $priorities = DrugStockRequest::getPriorities();
        $facilities = Facility::orderBy('name')->get();
        
        // Get statistics
        $stats = [
            'pending' => DrugStockRequest::pending()->count(),
            'approved' => DrugStockRequest::approved()->count(),
            'rejected' => DrugStockRequest::rejected()->count(),
            'dispensed' => DrugStockRequest::dispensed()->count(),
        ];
        
        return view('drug-stock-requests.index', compact('statuses', 'priorities', 'facilities', 'stats', 'isBoschmaAdmin'));
    }

    /**
     * Return facility-grouped data for the index DataTable.
     */
    private function facilityGroupedData(Request $request, bool $isBoschmaAdmin, $user)
    {
        $query = DB::table('drug_stock_requests')
            ->join('facilities', 'drug_stock_requests.facility_id', '=', 'facilities.id')
            ->whereNull('drug_stock_requests.deleted_at')
            ->select(
                'facilities.id as facility_id',
                'facilities.name as facility_name',
                'facilities.lga',
                DB::raw('COUNT(drug_stock_requests.id) as request_count'),
                DB::raw('SUM(CASE WHEN drug_stock_requests.drug_id IS NOT NULL THEN drug_stock_requests.quantity_requested ELSE 0 END) as total_quantity'),
                DB::raw('SUM(drug_stock_requests.estimated_cost) as total_cost'),
                DB::raw('MAX(drug_stock_requests.requested_at) as latest_request')
            );

        if (!$isBoschmaAdmin) {
            if ($user) {
                $query->whereIn('drug_stock_requests.status', ['approved', 'dispensed']);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status')) {
            $query->where('drug_stock_requests.status', $request->get('status'));
        }

        $query->groupBy('facilities.id', 'facilities.name', 'facilities.lga');

        $selectedStatus = $request->get('status', '');

        return DataTables::of($query)
            ->addColumn('facility_info', function ($row) {
                return '<div class="fw-bold">' . e($row->facility_name) . '</div>' .
                       '<div class="text-muted small">' . e($row->lga ?? '') . '</div>';
            })
            ->addColumn('request_count_fmt', function ($row) {
                return '<span class="badge bg-primary">' . $row->request_count . '</span>';
            })
            ->addColumn('total_quantity_fmt', function ($row) {
                return number_format($row->total_quantity);
            })
            ->addColumn('total_cost_fmt', function ($row) {
                return '₦' . number_format($row->total_cost ?? 0, 2);
            })
            ->addColumn('latest_request_fmt', function ($row) {
                return $row->latest_request ? \Carbon\Carbon::parse($row->latest_request)->format('M j, Y') : '-';
            })
            ->addColumn('action', function ($row) use ($selectedStatus) {
                $url = route('drug-stock-requests.facility-requests', $row->facility_id) . '?status=' . $selectedStatus;
                return '<a href="' . $url . '" class="btn btn-sm btn-primary"><i class="ti-eye me-1"></i>View Requests</a>';
            })
            ->rawColumns(['facility_info', 'request_count_fmt', 'action'])
            ->make(true);
    }

    /**
     * Show requests for a specific facility, optionally filtered by status.
     */
    public function facilityRequests(Request $request, string $facilityId)
    {
        $user = Auth::guard('staff')->user();
        $isBoschmaAdmin = $this->isBoschmaAdmin();
        $facility = Facility::findOrFail($facilityId);
        $selectedStatus = $request->get('status', '');

        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = DrugStockRequest::with(['drug', 'program', 'items.drug', 'requestedBy', 'approvedBy', 'dispensedBy'])
                ->where('facility_id', $facilityId);

            if (!$isBoschmaAdmin) {
                if ($user) {
                    $query->whereIn('status', ['approved', 'dispensed']);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->filled('status')) {
                        $query->where('status', $request->get('status'));
                    }
                    if ($request->filled('priority')) {
                        $query->where('priority', $request->get('priority'));
                    }
                })
                ->addColumn('checkbox', function($req) {
                    $disabled = $req->status !== 'pending' ? 'disabled' : '';
                    return '<input type="checkbox" class="form-check-input request-checkbox" value="' . $req->id . '" ' . $disabled . '>';
                })
                ->addColumn('request_id', function($req) {
                    return '<span class="text-muted">#' . str_pad($req->id, 6, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('drug_info', function($req) {
                    if ($req->drug_id) {
                        $drugName = strlen($req->drug->name) > 30 ? substr(e($req->drug->name), 0, 30) . '...' : e($req->drug->name);
                        $strengthInfo = e($req->drug->strength) . ' ' . e($req->drug->unit);
                        return '<div class="fw-bold" title="' . e($req->drug->name) . '">' . $drugName . '</div>' .
                               '<div class="text-muted small">' . $strengthInfo . '</div>';
                    } else {
                        return '<div class="fw-bold text-primary">Bulk Request</div>' .
                               '<div class="text-muted small">' . $req->items->count() . ' items</div>';
                    }
                })
                ->addColumn('program_name', function($req) {
                    return $req->program->name ?? 'N/A';
                })
                ->addColumn('quantity', function($req) {
                    return $req->formatted_quantity;
                })
                ->addColumn('cost', function($req) {
                    return $req->formatted_estimated_cost;
                })
                ->addColumn('priority', function($req) {
                    return $req->priority_badge;
                })
                ->addColumn('status', function($req) {
                    return $req->status_badge;
                })
                ->addColumn('requested', function($req) {
                    return '<div>' . $req->requested_at->format('M j, Y') . '</div>' .
                           '<div class="text-muted small">' . $req->requested_at->format('g:i A') . '</div>';
                })
                ->addColumn('action', function($req) {
                    $actions = '<div class="d-flex gap-1">
                        <a href="' . route('drug-stock-requests.show', $req->id) . '" class="btn btn-sm btn-info" title="View">👁️</a>';
                    if ($req->canBeEdited()) {
                        $actions .= '<a href="' . route('drug-stock-requests.edit', $req->id) . '" class="btn btn-sm btn-warning" title="Edit">✏️</a>';
                    }
                    if ($req->canBeApproved()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-success" onclick="approveRequest(' . $req->id . ')" title="Approve">✓</button>';
                    }
                    if ($req->canBeRejected()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="rejectRequest(' . $req->id . ')" title="Reject">✕</button>';
                    }
                    if ($req->canBeDispensed()) {
                        $actions .= '<a href="' . route('drug-stock-requests.dispense-form', $req->id) . '" class="btn btn-sm btn-primary" title="Dispense">📦</a>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->order(function ($query) {
                    $query->orderBy('priority', 'desc')->orderBy('requested_at', 'desc');
                })
                ->rawColumns(['checkbox', 'request_id', 'drug_info', 'priority', 'status', 'requested', 'action'])
                ->make(true);
        }

        $statuses = DrugStockRequest::getStatuses();
        $priorities = DrugStockRequest::getPriorities();

        // Get per-status counts for this facility
        $stats = [
            'pending' => DrugStockRequest::where('facility_id', $facilityId)->pending()->count(),
            'approved' => DrugStockRequest::where('facility_id', $facilityId)->approved()->count(),
            'rejected' => DrugStockRequest::where('facility_id', $facilityId)->rejected()->count(),
            'dispensed' => DrugStockRequest::where('facility_id', $facilityId)->dispensed()->count(),
        ];

        return view('drug-stock-requests.facility-requests', compact(
            'facility', 'selectedStatus', 'statuses', 'priorities', 'stats', 'isBoschmaAdmin'
        ));
    }
    
    /**
     * Show the form for creating a new drug stock request.
     */
    public function create(): View
    {
        $user = Auth::guard('staff')->user();
        
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can create stock requests.');
        }
        
        $facilityId = $user->facility_id;
        $drugs = Drug::where('facility_id', $facilityId)
                    ->orderBy('name')
                    ->get();
        
        $priorities = DrugStockRequest::getPriorities();
        $programs = Program::active()->orderBy('name')->get();
        
        return view('drug-stock-requests.create', compact('facilityId', 'drugs', 'priorities', 'programs'));
    }
    
    /**
     * Store a newly created drug stock request in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can create stock requests.');
        }
        
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'drug_id' => 'required|exists:drugs,id',
            'quantity_requested' => 'required|integer|min:1',
            'estimated_cost' => 'required|numeric|min:0',
            'priority' => 'required|in:low,medium,high,urgent',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);
        
        try {
            DB::transaction(function () use ($request, $user) {
                DrugStockRequest::create([
                    'facility_id' => $user->facility_id,
                    'program_id' => $request->program_id,
                    'drug_id' => $request->drug_id,
                    'quantity_requested' => $request->quantity_requested,
                    'estimated_cost' => $request->estimated_cost,
                    'priority' => $request->priority,
                    'reason' => $request->reason,
                    'notes' => $request->notes,
                    'requested_by' => $user->id,
                    'requested_at' => now(),
                ]);
            });
            
            return redirect()->route('drug-stock-requests.index')
                ->with('success', 'Stock request submitted successfully! Awaiting Boschma admin approval.');
                
        } catch (\Exception $e) {
            Log::error('Error creating drug stock request: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit stock request. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Display the specified drug stock request.
     */
    public function show(string $id): View
    {
        $user = Auth::guard('staff')->user();
        $isBoschmaAdmin = $this->isBoschmaAdmin();
        
        $request = DrugStockRequest::with(['facility', 'drug', 'program', 'requestedBy', 'approvedBy', 'dispensedBy', 'drugStocks'])
            ->findOrFail($id);
        
        // Check access permissions
        // if (!$isBoschmaAdmin && $request->facility_id !== $user->facility_id) {
        //     abort(403, 'Access denied.');
        // }
        
        return view('drug-stock-requests.show', compact('request', 'isBoschmaAdmin'));
    }
    
    /**
     * Update items in a bulk stock request.
     */
    public function updateItems(Request $request, string $id): JsonResponse
    {
        // Check Boschma admin permission
        if (!$this->isBoschmaAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied. Only Boschma administrators can modify requests.']);
        }
        
        $stockRequest = DrugStockRequest::with('items')->findOrFail($id);
        
        if (!$stockRequest->canBeApproved()) {
            return response()->json(['success' => false, 'message' => 'This request cannot be modified in its current status.']);
        }
        
        // Only allow modifications for bulk requests (no drug_id)
        if ($stockRequest->drug_id) {
            return response()->json(['success' => false, 'message' => 'Only bulk requests can be modified.']);
        }
        
        $request->validate([
            'updates' => 'nullable|array',
            'updates.*' => 'required|integer|min:1',
            'removed_items' => 'nullable|array',
            'removed_items.*' => 'required|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $updates = $request->input('updates', []);
            $removedItems = $request->input('removed_items', []);
            
            Log::info('Updating bulk stock request items', [
                'request_id' => $id,
                'updates' => $updates,
                'removed_items' => $removedItems
            ]);
            
            // Update quantities
            foreach ($updates as $itemId => $newQuantity) {
                $item = $stockRequest->items()->findOrFail($itemId);
                $oldQuantity = $item->quantity_requested;
                
                $item->quantity_requested = $newQuantity;
                $item->estimated_cost = $item->drug->unit_price * $newQuantity;
                $item->save();
                
                Log::info('Updated item quantity', [
                    'item_id' => $itemId,
                    'drug_name' => $item->drug->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity
                ]);
            }
            
            // Remove items
            foreach ($removedItems as $itemId) {
                $item = $stockRequest->items()->findOrFail($itemId);
                $drugName = $item->drug->name;
                $item->delete();
                
                Log::info('Removed item from request', [
                    'item_id' => $itemId,
                    'drug_name' => $drugName
                ]);
            }
            
            // Recalculate totals
            $stockRequest->quantity_requested = $stockRequest->items()->sum('quantity_requested');
            $stockRequest->estimated_cost = $stockRequest->items()->sum('estimated_cost');
            $stockRequest->save();
            
            // If all items were removed, delete the request
            if ($stockRequest->items()->count() === 0) {
                $stockRequest->delete();
                DB::commit();
                
                Log::info('Deleted empty bulk stock request', ['request_id' => $id]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'All items removed. Request has been deleted.',
                    'redirect' => route('drug-stock-requests.index')
                ]);
            }
            
            DB::commit();
            
            Log::info('Successfully updated bulk stock request', [
                'request_id' => $id,
                'new_total_quantity' => $stockRequest->quantity_requested,
                'new_total_cost' => $stockRequest->estimated_cost,
                'remaining_items' => $stockRequest->items()->count()
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Request updated successfully!',
                'new_total_quantity' => $stockRequest->quantity_requested,
                'new_total_cost' => $stockRequest->formatted_estimated_cost
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bulk stock request items: ' . $e->getMessage(), [
                'request_id' => $id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Failed to update request: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Approve a drug stock request.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        // Check Boschma admin permission
        if (!$this->isBoschmaAdmin()) {
            abort(403, 'Access denied. Only Boschma administrators can approve requests.');
        }
        
        $stockRequest = DrugStockRequest::findOrFail($id);
        
        if (!$stockRequest->canBeApproved()) {
            return back()->with('error', 'This request cannot be approved.');
        }
        
        $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);
        
        try {
            $stockRequest->approve(Auth::guard('staff')->user()->id, $request->approval_notes);
            
            return redirect()->route('drug-stock-requests.show', $stockRequest->id)
                ->with('success', 'Stock request approved successfully!');
                
        } catch (\Exception $e) {
            Log::error('Error approving drug stock request: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve request. ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a drug stock request.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        // Check Boschma admin permission
        if (!$this->isBoschmaAdmin()) {
            abort(403, 'Access denied. Only Boschma administrators can reject requests.');
        }
        
        $stockRequest = DrugStockRequest::findOrFail($id);
        
        if (!$stockRequest->canBeRejected()) {
            return back()->with('error', 'This request cannot be rejected.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        try {
            $stockRequest->reject(Auth::guard('staff')->user()->id, $request->rejection_reason);
            
            return redirect()->route('drug-stock-requests.show', $stockRequest->id)
                ->with('success', 'Stock request rejected.');
                
        } catch (\Exception $e) {
            Log::error('Error rejecting drug stock request: ' . $e->getMessage());
            return back()->with('error', 'Failed to reject request. ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk approve multiple drug stock requests.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'request_ids' => 'required|string',
            'approval_notes' => 'nullable|string|max:2000',
        ]);
        
        try {
            $requestIds = json_decode($request->request_ids, true);
            
            if (empty($requestIds) || !is_array($requestIds)) {
                return back()->with('error', 'No requests selected for approval.');
            }
            
            $userId = Auth::guard('staff')->user()->id;
            $approvedCount = 0;
            $skippedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($requestIds as $id) {
                $stockRequest = DrugStockRequest::find($id);
                
                if ($stockRequest && $stockRequest->canBeApproved()) {
                    $stockRequest->approve($userId, $request->approval_notes);
                    $approvedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            DB::commit();
            
            $message = "Successfully approved {$approvedCount} request(s).";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} request(s) were skipped (already processed).";
            }
            
            return redirect()->route('drug-stock-requests.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk approving drug stock requests: ' . $e->getMessage());
            return back()->with('error', 'Failed to bulk approve requests. ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk reject multiple drug stock requests.
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $request->validate([
            'request_ids' => 'required|string',
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        try {
            $requestIds = json_decode($request->request_ids, true);
            
            if (empty($requestIds) || !is_array($requestIds)) {
                return back()->with('error', 'No requests selected for rejection.');
            }
            
            $userId = Auth::guard('staff')->user()->id;
            $rejectedCount = 0;
            $skippedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($requestIds as $id) {
                $stockRequest = DrugStockRequest::find($id);
                
                if ($stockRequest && $stockRequest->canBeRejected()) {
                    $stockRequest->reject($userId, $request->rejection_reason);
                    $rejectedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            DB::commit();
            
            $message = "Successfully rejected {$rejectedCount} request(s).";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} request(s) were skipped (already processed).";
            }
            
            return redirect()->route('drug-stock-requests.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk rejecting drug stock requests: ' . $e->getMessage());
            return back()->with('error', 'Failed to bulk reject requests. ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for dispensing an approved request.
     */
    public function dispenseForm(string $id): View
    {
        // Check Boschma admin permission
        // if (!$this->isBoschmaAdmin()) {
        //     abort(403, 'Access denied. Only Boschma administrators can dispense requests.');
        // }
        
        $stockRequest = DrugStockRequest::with(['facility', 'drug', 'program'])->findOrFail($id);
        
        if (!$stockRequest->canBeDispensed()) {
            abort(404, 'This request cannot be dispensed.');
        }
        
        return view('drug-stock-requests.dispense', compact('stockRequest'));
    }
    
    /**
     * Dispense an approved drug stock request.
     */
    public function dispense(Request $request, string $id): RedirectResponse|JsonResponse
    {
        Log::info('=== DISPENSE METHOD STARTED ===');
        Log::info('Request ID: ' . $id);
        Log::info('Request method: ' . $request->method());
        Log::info('Content type: ' . $request->header('Content-Type'));
        Log::info('Request size: ' . $request->header('Content-Length') . ' bytes');
        
        // Check for potential input limit issues
        $inputCount = count($request->all());
        Log::info('Total input variables received: ' . $inputCount);
        
        if ($inputCount > 900) {
            Log::warning('Input variables approaching PHP limit: ' . $inputCount . ' (max_input_vars default: 1000)');
        }
        
        // Check if batches data is missing (likely due to input limits)
        if (!$request->has('batches')) {
            Log::error('Batches data missing from request - likely due to PHP input limits exceeded');
            Log::error('Form data keys received: ' . implode(', ', array_keys($request->all())));
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Form data too large. Please try dispensing smaller batches (maximum 80 items at once). PHP input limits exceeded.'
                ], 413);
            }
            return back()->with('error', 'Form data too large. Please try dispensing smaller batches (maximum 80 items at once). PHP input limits exceeded.')
                ->withInput();
        }
        
        Log::info('Batches data present, proceeding with validation');
        Log::info('Batch count: ' . count($request->batches));
        
        // Check Boschma admin permission
        // if (!$this->isBoschmaAdmin()) {
        //     Log::error('User is not Boschma admin');
        //     abort(403, 'Access denied. Only Boschma administrators can dispense requests.');
        // }
        
        Log::info('User is Boschma admin - proceeding');
        
        $stockRequest = DrugStockRequest::with(['items'])->findOrFail($id);
        Log::info('Stock request found: ' . json_encode([
            'id' => $stockRequest->id,
            'drug_id' => $stockRequest->drug_id,
            'status' => $stockRequest->status,
            'items_count' => $stockRequest->items->count()
        ]));
        
        if (!$stockRequest->canBeDispensed()) {
            Log::error('Request cannot be dispensed. Status: ' . $stockRequest->status);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This request cannot be dispensed.'], 400);
            }
            return back()->with('error', 'This request cannot be dispensed.');
        }
        
        // Check if this is a bulk request
        $isBulkRequest = !$stockRequest->drug_id;
        Log::info('Is bulk request: ' . ($isBulkRequest ? 'YES' : 'NO'));
        
        if ($isBulkRequest) {
            Log::info('Processing bulk request validation');
            Log::info('Batches received: ' . json_encode($request->batches));
            
            // Bulk request validation
            try {
                $request->validate([
                    'batches' => 'required|array|min:1',
                    'batches.*.batch_number' => 'required|string|max:100',
                    'batches.*.expiry_date' => 'required|date|after:today',
                    'batches.*.quantity_received' => 'required|integer|min:1',
                    'batches.*.unit_cost' => 'required|numeric|min:0',
                    'batches.*.supplier' => 'required|string|max:255',
                    'batches.*.notes' => 'nullable|string|max:1000',
                    'batches.*.item_id' => 'required|exists:drug_stock_request_items,id',
                ]);
                Log::info('Bulk request validation passed');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Bulk request validation failed: ' . json_encode($e->errors()));
                throw $e;
            }
            
            // Get out of stock items from request (these are item indices, not IDs)
            $outOfStockItemsJson = $request->input('out_of_stock_items', '[]');
            $outOfStockIndices = json_decode($outOfStockItemsJson, true) ?: [];
            Log::info('Out of stock item indices: ' . json_encode($outOfStockIndices));
            
            // Validate that each item has the correct total quantity (only for items not out of stock)
            $itemQuantities = [];
            foreach ($request->batches as $batch) {
                $itemId = $batch['item_id'];
                if (!isset($itemQuantities[$itemId])) {
                    $itemQuantities[$itemId] = 0;
                }
                $itemQuantities[$itemId] += $batch['quantity_received'];
            }
            Log::info('Item quantities calculated: ' . json_encode($itemQuantities));
            
            // Check each item's requested quantity matches dispensed quantity (skip out of stock items)
            foreach ($stockRequest->items as $index => $item) {
                if (in_array($index, $outOfStockIndices)) {
                    Log::info("Item {$item->drug->name} (index {$index}) is marked as out of stock - skipping quantity validation");
                    continue;
                }
                
                $dispensedQuantity = $itemQuantities[$item->id] ?? 0;
                Log::info("Item {$item->drug->name} (index {$index}): requested {$item->quantity_requested}, dispensed {$dispensedQuantity}");
                if ($dispensedQuantity != $item->quantity_requested) {
                    Log::error("Quantity mismatch for {$item->drug->name}: requested {$item->quantity_requested}, dispensed {$dispensedQuantity}");
                    $errorMsg = "Quantity mismatch for {$item->drug->name}: requested {$item->quantity_requested}, dispensed {$dispensedQuantity}.";
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    return back()->with('error', $errorMsg);
                }
            }
            
        } else {
            Log::info('Processing single drug request validation');
            
            // Single drug request validation
            try {
                $request->validate([
                    'batches' => 'required|array|min:1',
                    'batches.*.batch_number' => 'required|string|max:100',
                    'batches.*.expiry_date' => 'required|date|after:today',
                    'batches.*.quantity_received' => 'required|integer|min:1',
                    'batches.*.unit_cost' => 'required|numeric|min:0',
                    'batches.*.supplier' => 'required|string|max:255',
                    'batches.*.notes' => 'nullable|string|max:1000',
                ]);
                Log::info('Single drug request validation passed');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Single drug request validation failed: ' . json_encode($e->errors()));
                throw $e;
            }
            
            // Validate that total quantity matches requested quantity
            $totalQuantity = collect($request->batches)->sum('quantity_received');
            Log::info("Total quantity check: dispensed {$totalQuantity}, requested {$stockRequest->quantity_requested}");
            if ($totalQuantity != $stockRequest->quantity_requested) {
                Log::error("Quantity mismatch: dispensed {$totalQuantity}, requested {$stockRequest->quantity_requested}");
                $errorMsg = "Total quantity ({$totalQuantity}) must match requested quantity ({$stockRequest->quantity_requested}).";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return back()->with('error', $errorMsg);
            }
        }
        
        try {
            Log::info('Starting database transaction for stock creation');
            DB::beginTransaction();
            
            // Update request status and save out-of-stock items
            Log::info('Updating request status to dispensed');
            $stockRequest->update([
                'status' => 'dispensed',
                'dispensed_at' => now(),
                'dispensed_by' => Auth::guard('staff')->user()->id,
                'out_of_stock_items' => !empty($outOfStockIndices) ? json_encode($outOfStockIndices) : null,
            ]);
            Log::info('Request status updated successfully');
            Log::info('Out of stock items saved: ' . json_encode($outOfStockIndices));
            
            // Deduct from central drug store and create facility stock records
            Log::info('Creating drug stock records for ' . count($request->batches) . ' batches');
            $createdStocks = [];
            
            // Group quantities by drug_id for store deduction
            $drugDeductions = [];
            foreach ($request->batches as $batch) {
                $drugId = $isBulkRequest ? 
                    $stockRequest->items()->find($batch['item_id'])->drug_id : 
                    $stockRequest->drug_id;
                if (!isset($drugDeductions[$drugId])) {
                    $drugDeductions[$drugId] = 0;
                }
                $drugDeductions[$drugId] += $batch['quantity_received'];
            }
            
            // Deduct from central drug store (allow negative/deficit)
            foreach ($drugDeductions as $drugId => $totalQty) {
                $storeAvailable = DrugStoreStock::getAvailableQuantity($drugId);
                if ($storeAvailable >= $totalQty) {
                    DrugStoreStock::deductFromStore($drugId, $totalQty);
                    Log::info("Deducted {$totalQty} units of drug {$drugId} from central store");
                } else {
                    // Deduct whatever is available, rest goes as deficit
                    if ($storeAvailable > 0) {
                        DrugStoreStock::deductFromStore($drugId, $storeAvailable);
                        Log::info("Partially deducted {$storeAvailable} of {$totalQty} units of drug {$drugId} from central store (deficit: " . ($totalQty - $storeAvailable) . ")");
                    } else {
                        Log::info("No store stock for drug {$drugId}, dispensing {$totalQty} units as deficit");
                    }
                }
            }
            
            foreach ($request->batches as $index => $batch) {
                Log::info("Processing batch {$index}: " . json_encode($batch));
                
                $drugId = $isBulkRequest ? 
                    $stockRequest->items()->find($batch['item_id'])->drug_id : 
                    $stockRequest->drug_id;
                
                Log::info("Determined drug_id: {$drugId}");
                
                $stockData = [
                    'drug_id' => $drugId,
                    'facility_id' => $stockRequest->facility_id,
                    'program_id' => $stockRequest->program_id,
                    'batch_number' => $batch['batch_number'],
                    'expiry_date' => $batch['expiry_date'],
                    'quantity_received' => $batch['quantity_received'],
                    'quantity_remaining' => $batch['quantity_received'],
                    'unit_cost' => $batch['unit_cost'],
                    'supplier' => $batch['supplier'],
                    'notes' => $batch['notes'] ?? null,
                    'status' => 'approved',
                    'request_id' => $stockRequest->id,
                    'item_id' => $batch['item_id'] ?? null,
                ];
                
                Log::info("Creating stock record: " . json_encode($stockData));
                
                $drugStock = DrugStock::create($stockData);
                $createdStocks[] = $drugStock->id;
                
                Log::info("Stock record created successfully with ID: {$drugStock->id}");
            }
            
            Log::info('All stock records created: ' . json_encode($createdStocks));
            
            Log::info('Committing database transaction');
            DB::commit();
            Log::info('Database transaction committed successfully');
            
            Log::info('=== DISPENSE METHOD COMPLETED SUCCESSFULLY ===');
            
            // Return JSON response for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock dispensed successfully!',
                    'redirect' => route('drug-stock-requests.show', $stockRequest->id)
                ]);
            }
            
            return redirect()->route('drug-stock-requests.show', $stockRequest->id)
                ->with('success', 'Stock dispensed successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database transaction rolled back due to error: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            Log::error('=== DISPENSE METHOD FAILED ===');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to dispense stock. ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to dispense stock. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Check if current user is Boschma admin.
     */
    private function isBoschmaAdmin(): bool
    {
        $user = Auth::guard('staff')->user();
        $boschmaRoles = [
            'Super Admin',
            'Super Administrator',
            'Boschma Administrator',
            'System Administrator',
            'Admin'
        ];
        
        if ($user) {
            $roles = $user->getRoleNames()->toArray();
            return !empty(array_intersect($roles, $boschmaRoles));
        }
        
        return false;
    }
    
    /**
     * Show the form for editing the specified drug stock request.
     */
    public function edit(string $id): View
    {
        $isBoschmaAdmin = $this->isBoschmaAdmin();
        
        $request = DrugStockRequest::with(['facility', 'drug'])->findOrFail($id);
        
        // Only allow editing pending requests
        if ($request->status !== 'pending') {
            abort(403, 'Only pending requests can be edited.');
        }
        
        $drugs = Drug::orderBy('name')->get();
        $facilities = Facility::orderBy('name')->get();
        $priorities = DrugStockRequest::getPriorities();
        $programs = Program::active()->orderBy('name')->get();
        
        return view('drug-stock-requests.edit', compact('request', 'drugs', 'facilities', 'priorities', 'programs'));
    }

    /**
     * Update the specified drug stock request in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        Log::info('DrugStockRequestController::update - Method called');
        Log::info('Request ID: ' . $id);
        Log::info('Request data: ' . json_encode($request->all()));
        
        $stockRequest = DrugStockRequest::findOrFail($id);
        
        // Only allow editing pending requests
        if ($stockRequest->status !== 'pending') {
            abort(403, 'Only pending requests can be edited.');
        }
        
        // Validate bulk request structure
        Log::info('Starting validation...');
        try {
            $validated = $request->validate([
                'program_id' => 'required|exists:programs,id',
                'requests' => 'required|array|min:1',
                'requests.*.drug_id' => 'required|exists:drugs,id',
                'requests.*.quantity_requested' => 'required|integer|min:1',
                'requests.*.estimated_cost' => 'required|numeric|min:0',
                'requests.*.priority' => 'required|in:low,medium,high,urgent',
                'facility_id' => 'required|exists:facilities,id',
                'reason' => 'required|string|max:1000',
                'notes' => 'nullable|string|max:2000',
                'requested_at' => 'required|date',
            ]);
            Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        try {
            Log::info('Starting database transaction...');
            DB::beginTransaction();
            
            // Clear existing items
            Log::info('Clearing existing items...');
            $deletedItems = $stockRequest->items()->delete();
            Log::info('Deleted ' . $deletedItems . ' existing items');
            
            // Update main request (set drug_id to null for bulk request)
            $totalEstimatedCost = 0;
            foreach ($request->requests as $requestData) {
                $totalEstimatedCost += $requestData['estimated_cost'];
            }
            
            $updateData = [
                'drug_id' => null, // Bulk request has no single drug
                'program_id' => $request->program_id,
                'facility_id' => $request->facility_id,
                'quantity_requested' => array_sum(array_column($request->requests, 'quantity_requested')),
                'estimated_cost' => $totalEstimatedCost,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'requested_at' => $request->requested_at,
            ];
            Log::info('Updating main request with data: ' . json_encode($updateData));
            
            $stockRequest->update($updateData);
            Log::info('Main request updated successfully');
            
            // Create new items
            Log::info('Creating new items...');
            $itemCount = 0;
            foreach ($request->requests as $index => $requestData) {
                Log::info('Creating item ' . $index . ' with data: ' . json_encode($requestData));
                $stockRequest->items()->create([
                    'drug_id' => $requestData['drug_id'],
                    'quantity_requested' => $requestData['quantity_requested'],
                    'estimated_cost' => $requestData['estimated_cost'],
                    'priority' => $requestData['priority'],
                ]);
                $itemCount++;
            }
            Log::info('Created ' . $itemCount . ' new items');
            
            Log::info('Committing transaction...');
            DB::commit();
            Log::info('Transaction committed successfully');
            
            Log::info('Redirecting to show page with success message...');
            return redirect()->route('drug-stock-requests.show', $stockRequest->id)
                ->with('success', 'Drug stock request updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating drug stock request: ' . $e->getMessage());
            return back()->with('error', 'Failed to update stock request. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Check if current user has pharmacy admin position.
     */
    private function isPharmacyAdmin(): bool
    {
        $user = Auth::guard('staff')->user();
        $pharmacyPositions = [
            'Chief Pharmacist',
            'Pharmacist',
            'Hospital Administrator',
            'Admin'
        ];
        
        return $user && in_array($user->staffPosition->name ?? '', $pharmacyPositions);
    }
}
