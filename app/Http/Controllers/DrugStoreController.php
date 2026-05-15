<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugStoreStock;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class DrugStoreController extends Controller
{
    /**
     * Display central drug store inventory.
     */
    public function index(Request $request)
    {
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $tab = $request->get('tab', 'store');

            if ($tab === 'all_drugs') {
                return $this->allDrugsDataTable($request);
            }

            return $this->storeInventoryDataTable($request);
        }

        // Get overall stats
        $totalStockedIn = (int) DB::table('drug_store_stocks')
            ->whereNull('deleted_at')
            ->sum('quantity_received');
        $totalDispensedOut = (int) DB::table('drug_stocks')
            ->whereNull('deleted_at')
            ->sum('quantity_received');

        $stats = [
            'total_drugs' => DB::table('drugs')->count(),
            'total_available' => $totalStockedIn - $totalDispensedOut,
            'total_dispensed' => $totalDispensedOut,
            'total_value' => DB::table('drug_store_stocks')
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->where('quantity_remaining', '>', 0)
                ->selectRaw('SUM(quantity_remaining * unit_cost) as val')
                ->value('val') ?? 0,
            'low_stock_count' => DB::table('drug_store_stocks')
                ->whereNull('deleted_at')
                ->select('drug_id')
                ->where('status', 'active')
                ->where('quantity_remaining', '>', 0)
                ->where('expiry_date', '>', now())
                ->groupBy('drug_id')
                ->havingRaw('SUM(quantity_remaining) <= 50')
                ->get()
                ->count(),
            'near_expiry_count' => DB::table('drug_store_stocks')
                ->whereNull('deleted_at')
                ->where('quantity_remaining', '>', 0)
                ->where('expiry_date', '>', now())
                ->where('expiry_date', '<=', now()->addDays(30))
                ->count(),
            'drugs_in_store' => DB::table('drug_store_stocks')
                ->whereNull('deleted_at')
                ->distinct('drug_id')
                ->count('drug_id'),
        ];

        return view('drug-store.index', compact('stats'));
    }

    /**
     * Store inventory DataTable (drugs with store stock).
     */
    private function storeInventoryDataTable(Request $request)
    {
        $query = DB::table('drug_store_stocks')
            ->join('drugs', 'drug_store_stocks.drug_id', '=', 'drugs.id')
            ->whereNull('drug_store_stocks.deleted_at')
            ->select(
                'drugs.id as drug_id',
                'drugs.name as drug_name',
                'drugs.strength',
                'drugs.unit',
                'drugs.dosage_form',
                'drugs.unit_price',
                DB::raw('SUM(drug_store_stocks.quantity_received) as total_received'),
                DB::raw('SUM(drug_store_stocks.quantity_remaining) as total_remaining'),
                DB::raw('SUM(drug_store_stocks.quantity_dispensed) as total_dispensed'),
                DB::raw('COUNT(drug_store_stocks.id) as total_batches'),
                DB::raw('SUM(CASE WHEN drug_store_stocks.status = "active" AND drug_store_stocks.quantity_remaining > 0 AND drug_store_stocks.expiry_date > NOW() THEN drug_store_stocks.quantity_remaining ELSE 0 END) as available_qty'),
                DB::raw('SUM(CASE WHEN drug_store_stocks.expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND drug_store_stocks.expiry_date > NOW() AND drug_store_stocks.quantity_remaining > 0 THEN drug_store_stocks.quantity_remaining ELSE 0 END) as near_expiry_qty'),
                DB::raw('SUM(CASE WHEN drug_store_stocks.expiry_date <= NOW() THEN drug_store_stocks.quantity_remaining ELSE 0 END) as expired_qty'),
                DB::raw('SUM(drug_store_stocks.quantity_remaining * drug_store_stocks.unit_cost) as total_value')
            )
            ->groupBy('drugs.id', 'drugs.name', 'drugs.strength', 'drugs.unit', 'drugs.dosage_form', 'drugs.unit_price');

        if ($request->filled('stock_status')) {
            $status = $request->get('stock_status');
            if ($status === 'in_stock') {
                $query->havingRaw('available_qty > 0');
            } elseif ($status === 'out_of_stock') {
                $query->havingRaw('available_qty = 0');
            } elseif ($status === 'low_stock') {
                $query->havingRaw('available_qty > 0 AND available_qty <= 50');
            } elseif ($status === 'near_expiry') {
                $query->havingRaw('near_expiry_qty > 0');
            }
        }

        return DataTables::of($query)
            ->addColumn('drug_info', function ($row) {
                $drugName = strlen($row->drug_name) > 30 ? substr(e($row->drug_name), 0, 30) . '...' : e($row->drug_name);
                $strengthInfo = e($row->strength) . ' ' . e($row->unit) . ' &middot; ' . e($row->dosage_form);
                $strengthInfo = strlen($strengthInfo) > 25 ? substr($strengthInfo, 0, 25) . '...' : $strengthInfo;
                
                return '<div class="fw-bold" title="' . e($row->drug_name) . '">' . $drugName . '</div>' .
                       '<div class="text-muted small" title="' . e($row->strength) . ' ' . e($row->unit) . ' &middot; ' . e($row->dosage_form) . '">' . $strengthInfo . '</div>';
            })
            ->addColumn('available', function ($row) {
                $qty = (int) $row->available_qty;
                if ($qty == 0) {
                    return '<span class="badge bg-danger">Out of Stock</span>';
                } elseif ($qty <= 50) {
                    return '<span class="text-warning fw-bold">' . number_format($qty) . '</span> <span class="badge bg-warning-lt text-warning">Low</span>';
                }
                return '<span class="text-success fw-bold">' . number_format($qty) . '</span>';
            })
            ->addColumn('received', function ($row) {
                return number_format($row->total_received);
            })
            ->addColumn('dispensed', function ($row) {
                return number_format($row->total_dispensed);
            })
            ->addColumn('batches', function ($row) {
                return $row->total_batches;
            })
            ->addColumn('expiry_info', function ($row) {
                $html = '';
                if ($row->expired_qty > 0) {
                    $html .= '<span class="badge bg-danger me-1">' . number_format($row->expired_qty) . ' expired</span>';
                }
                if ($row->near_expiry_qty > 0) {
                    $html .= '<span class="badge bg-warning">' . number_format($row->near_expiry_qty) . ' expiring soon</span>';
                }
                if ($row->expired_qty == 0 && $row->near_expiry_qty == 0) {
                    $html = '<span class="text-success"><i class="ti-check"></i> Good</span>';
                }
                return $html;
            })
            ->addColumn('value', function ($row) {
                return '₦' . number_format($row->total_value, 2);
            })
            ->addColumn('action', function ($row) {
                return '<div class="d-flex gap-1">' .
                    '<a href="' . route('drug-store.show', $row->drug_id) . '" class="btn btn-sm btn-info" title="View Batches"><i class="ti-eye"></i></a>' .
                    '<a href="' . route('drug-store.stock-in-form') . '?drug_id=' . $row->drug_id . '" class="btn btn-sm btn-success" title="Stock In"><i class="ti-plus"></i></a>' .
                    '</div>';
            })
            ->rawColumns(['drug_info', 'available', 'expiry_info', 'action'])
            ->make(true);
    }

    /**
     * All Drugs DataTable (every drug in the system with summary).
     */
    private function allDrugsDataTable(Request $request)
    {
        $query = DB::table('drugs')
            ->leftJoin('drug_store_stocks', function ($join) {
                $join->on('drugs.id', '=', 'drug_store_stocks.drug_id')
                     ->whereNull('drug_store_stocks.deleted_at');
            })
            ->leftJoin('drug_stocks', function ($join) {
                $join->on('drugs.id', '=', 'drug_stocks.drug_id')
                     ->whereNull('drug_stocks.deleted_at');
            })
            ->select(
                'drugs.id as drug_id',
                'drugs.name as drug_name',
                'drugs.strength',
                'drugs.unit',
                'drugs.dosage_form',
                'drugs.unit_price',
                DB::raw('COALESCE(SUM(DISTINCT CASE WHEN drug_store_stocks.status = "active" AND drug_store_stocks.quantity_remaining > 0 AND drug_store_stocks.expiry_date > NOW() THEN drug_store_stocks.quantity_remaining ELSE 0 END), 0) as store_qty'),
                DB::raw('(SELECT COALESCE(SUM(dss.quantity_received), 0) FROM drug_store_stocks dss WHERE dss.drug_id = drugs.id AND dss.deleted_at IS NULL) as total_received_to_store'),
                DB::raw('(SELECT COALESCE(SUM(ds.quantity_received), 0) FROM drug_stocks ds WHERE ds.drug_id = drugs.id AND ds.deleted_at IS NULL) as total_dispensed_to_facilities'),
                DB::raw('(SELECT COUNT(*) FROM drug_stock_requests dsr WHERE dsr.drug_id = drugs.id AND dsr.status = "pending") as pending_requests'),
                DB::raw('(SELECT COUNT(*) FROM drug_stock_requests dsr WHERE dsr.drug_id = drugs.id AND dsr.status = "approved") as approved_requests')
            )
            ->groupBy('drugs.id', 'drugs.name', 'drugs.strength', 'drugs.unit', 'drugs.dosage_form', 'drugs.unit_price');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('drugs.name', 'like', "%{$search}%")
                  ->orWhere('drugs.strength', 'like', "%{$search}%")
                  ->orWhere('drugs.unit', 'like', "%{$search}%")
                  ->orWhere('drugs.dosage_form', 'like', "%{$search}%");
            });
        }

        if ($request->filled('availability')) {
            $availability = $request->get('availability');
            if ($availability === 'available') {
                $query->havingRaw('store_qty > 0');
            } elseif ($availability === 'not_available') {
                $query->havingRaw('store_qty = 0');
            }
        }

        if ($request->filled('price_range')) {
            $priceRange = $request->get('price_range');
            if ($priceRange === 'low') {
                $query->where('drugs.unit_price', '<', 100);
            } elseif ($priceRange === 'medium') {
                $query->whereBetween('drugs.unit_price', [100, 1000]);
            } elseif ($priceRange === 'high') {
                $query->where('drugs.unit_price', '>', 1000);
            }
        }

        if ($request->filled('has_requests')) {
            $hasRequests = $request->get('has_requests');
            if ($hasRequests === 'yes') {
                $query->havingRaw('(pending_requests + approved_requests) > 0');
            } elseif ($hasRequests === 'no') {
                $query->havingRaw('(pending_requests + approved_requests) = 0');
            }
        }

        return DataTables::of($query)
            ->addColumn('drug_info', function ($row) {
                $drugName = strlen($row->drug_name) > 30 ? substr(e($row->drug_name), 0, 30) . '...' : e($row->drug_name);
                $strengthInfo = e($row->strength) . ' ' . e($row->unit) . ' &middot; ' . e($row->dosage_form);
                $strengthInfo = strlen($strengthInfo) > 25 ? substr($strengthInfo, 0, 25) . '...' : $strengthInfo;
                
                return '<div class="fw-bold" title="' . e($row->drug_name) . '">' . $drugName . '</div>' .
                       '<div class="text-muted small" title="' . e($row->strength) . ' ' . e($row->unit) . ' &middot; ' . e($row->dosage_form) . '">' . $strengthInfo . '</div>';
            })
            ->addColumn('unit_price_fmt', function ($row) {
                return '₦' . number_format($row->unit_price, 2);
            })
            ->addColumn('store_available', function ($row) {
                $effective = (int) $row->total_received_to_store - (int) $row->total_dispensed_to_facilities;
                if ($effective < 0) {
                    return '<span class="text-danger fw-bold">' . number_format($effective) . '</span> <span class="badge bg-danger">Deficit</span>';
                } elseif ($effective == 0) {
                    return '<span class="badge bg-danger">Not in Store</span>';
                } elseif ($effective <= 50) {
                    return '<span class="text-warning fw-bold">' . number_format($effective) . '</span> <span class="badge bg-warning-lt text-warning">Low</span>';
                }
                return '<span class="text-success fw-bold">' . number_format($effective) . '</span>';
            })
            ->addColumn('dispensed_to_fac', function ($row) {
                $qty = (int) $row->total_dispensed_to_facilities;
                return $qty > 0 ? '<span class="fw-bold">' . number_format($qty) . '</span>' : '<span class="text-muted">0</span>';
            })
            ->addColumn('requests_info', function ($row) {
                $html = '';
                if ($row->pending_requests > 0) {
                    $html .= '<span class="badge bg-warning me-1">' . $row->pending_requests . ' pending</span>';
                }
                if ($row->approved_requests > 0) {
                    $html .= '<span class="badge bg-info">' . $row->approved_requests . ' approved</span>';
                }
                if ($row->pending_requests == 0 && $row->approved_requests == 0) {
                    $html = '<span class="text-muted">None</span>';
                }
                return $html;
            })
            ->addColumn('action', function ($row) {
                return '<div class="d-flex gap-1">' .
                    '<a href="' . route('drug-store.show', $row->drug_id) . '" class="btn btn-sm btn-info" title="View Batches"><i class="ti-eye"></i></a>' .
                    '<a href="' . route('drug-store.stock-in-form') . '?drug_id=' . $row->drug_id . '" class="btn btn-sm btn-success" title="Stock In"><i class="ti-plus"></i></a>' .
                    '</div>';
            })
            ->rawColumns(['drug_info', 'store_available', 'dispensed_to_fac', 'requests_info', 'action'])
            ->make(true);
    }

    /**
     * Show batches for a specific drug.
     */
    public function show(string $drugId)
    {
        $drug = Drug::findOrFail($drugId);

        $batches = DrugStoreStock::with('program')
            ->where('drug_id', $drugId)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $totalReceived = $batches->sum('quantity_received');
        $totalDispensed = DB::table('drug_stocks')
            ->where('drug_id', $drugId)
            ->whereNull('deleted_at')
            ->sum('quantity_received');

        // Calculate available stock: can be negative if drugs were dispensed
        // before the store stock system was implemented
        $available = $totalReceived - $totalDispensed;

        // Get stocking history
        $stockingHistory = DrugStoreStock::where('drug_id', $drugId)
            ->with('stockedBy')
            ->orderBy('stocked_at', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity_received' => $batch->quantity_received,
                    'quantity_remaining' => $batch->quantity_remaining,
                    'quantity_dispensed' => $batch->quantity_dispensed,
                    'unit_cost' => $batch->unit_cost,
                    'total_value' => $batch->quantity_received * $batch->unit_cost,
                    'supplier' => $batch->supplier,
                    'expiry_date' => $batch->expiry_date,
                    'status' => $batch->status,
                    'stocked_by' => $batch->stockedBy ? $batch->stockedBy->name : 'Unknown',
                    'stocked_at' => $batch->stocked_at,
                    'notes' => $batch->notes,
                ];
            });

        // Get dispensing history with facility names
        $dispensingHistory = DB::table('drug_stocks')
            ->join('facilities', 'drug_stocks.facility_id', '=', 'facilities.id')
            ->join('drug_stock_requests', 'drug_stocks.request_id', '=', 'drug_stock_requests.id')
            ->where('drug_stocks.drug_id', $drugId)
            ->whereNull('drug_stocks.deleted_at')
            ->select(
                'drug_stocks.id',
                'drug_stocks.quantity_received',
                'drug_stocks.batch_number',
                'drug_stocks.expiry_date',
                'drug_stocks.created_at',
                'facilities.name as facility_name',
                'drug_stock_requests.id as request_id',
                'drug_stock_requests.status as request_status'
            )
            ->orderBy('drug_stocks.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->created_at = \Carbon\Carbon::parse($item->created_at);
                $item->expiry_date = \Carbon\Carbon::parse($item->expiry_date);
                return $item;
            });

        return view('drug-store.show', compact('drug', 'batches', 'available', 'totalReceived', 'totalDispensed', 'stockingHistory', 'dispensingHistory'));
    }

    /**
     * Show the stock-in form.
     */
    public function stockInForm(Request $request): View
    {

        $drugs = Drug::orderBy('name')->get();
        $selectedDrugId = $request->get('drug_id');
        $programs = Program::active()->orderBy('name')->get();

        return view('drug-store.stock-in', compact('drugs', 'selectedDrugId', 'programs'));
    }

    /**
     * Process stock-in (add stock to store).
     */
    public function stockIn(Request $request): RedirectResponse
    {

        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'entries' => 'required|array|min:1',
            'entries.*.drug_id' => 'required|exists:drugs,id',
            'entries.*.batch_number' => 'required|string|max:100',
            'entries.*.expiry_date' => 'required|date|after:today',
            'entries.*.quantity' => 'required|integer|min:1',
            'entries.*.unit_cost' => 'required|numeric|min:0',
            'entries.*.supplier' => 'required|string|max:255',
            'entries.*.notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::guard('staff')->user()->id;
            $count = 0;

            foreach ($request->entries as $entry) {
                DrugStoreStock::create([
                    'drug_id' => $entry['drug_id'],
                    'program_id' => $request->program_id,
                    'batch_number' => $entry['batch_number'],
                    'expiry_date' => $entry['expiry_date'],
                    'quantity_received' => $entry['quantity'],
                    'quantity_remaining' => $entry['quantity'],
                    'quantity_dispensed' => 0,
                    'unit_cost' => $entry['unit_cost'],
                    'supplier' => $entry['supplier'],
                    'notes' => $entry['notes'] ?? null,
                    'status' => 'active',
                    'stocked_by' => $userId,
                    'stocked_at' => now(),
                ]);
                $count++;
            }

            DB::commit();

            return redirect()->route('drug-store.index')
                ->with('success', "Successfully added {$count} stock batch(es) to the store.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error stocking drug store: ' . $e->getMessage());
            return back()->with('error', 'Failed to stock drugs. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the edit form for a store stock batch.
     */
    public function edit(string $id): View
    {
        $stock = DrugStoreStock::findOrFail($id);
        $drug = $stock->drug;
        $programs = Program::active()->orderBy('name')->get();

        return view('drug-store.edit', compact('stock', 'drug', 'programs'));
    }

    /**
     * Update a store stock batch.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $stock = DrugStoreStock::findOrFail($id);

        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after:today',
            'quantity_received' => 'required|integer|min:' . ($stock->quantity_dispensed),
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,depleted,expired',
        ]);

        try {
            DB::beginTransaction();

            // Calculate new remaining quantity
            $newRemaining = $request->quantity_received - $stock->quantity_dispensed;

            $stock->update([
                'program_id' => $request->program_id,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'quantity_received' => $request->quantity_received,
                'quantity_remaining' => $newRemaining,
                'unit_cost' => $request->unit_cost,
                'supplier' => $request->supplier,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('drug-store.show', $stock->drug_id)
                ->with('success', 'Stock batch updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating store stock: ' . $e->getMessage());
            return back()->with('error', 'Failed to update stock batch. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a store stock batch.
     */
    public function destroy(string $id): RedirectResponse
    {
        $stock = DrugStoreStock::findOrFail($id);

        // Prevent deletion if some quantity has been dispensed
        if ($stock->quantity_dispensed > 0) {
            return back()->with('error', 'Cannot delete batch that has been partially dispensed.');
        }

        try {
            DB::beginTransaction();

            $drugId = $stock->drug_id;
            $stock->delete();

            DB::commit();

            return redirect()->route('drug-store.show', $drugId)
                ->with('success', 'Stock batch deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting store stock: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete stock batch.');
        }
    }
}
