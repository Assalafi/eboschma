<?php

namespace App\Http\Controllers;

use App\Models\FacilityWallet;
use App\Models\WalletTransaction;
use App\Models\Facility;
use App\Models\Program;
use App\Models\DrugStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    /**
     * Display wallet listing with overview stats.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = FacilityWallet::with(['facility', 'program']);

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->whereHas('facility', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
                    }
                    if ($request->filled('status')) {
                        $query->where('status', $request->get('status'));
                    }
                })
                ->addColumn('facility_name', function ($wallet) {
                    $name = $wallet->facility->name ?? 'Unknown';
                    $program = $wallet->program->name ?? 'N/A';
                    $walletNo = $wallet->wallet_number ?? '-';
                    return '<div class="fw-bold">' . e($name) . '</div>' .
                           '<div class="text-muted small">Program: ' . e($program) . ' | Wallet: ' . e($walletNo) . '</div>';
                })
                ->addColumn('balance_fmt', function ($wallet) {
                    $color = $wallet->balance > 0 ? 'text-success' : ($wallet->balance < 0 ? 'text-danger' : 'text-muted');
                    return '<span class="fw-bold ' . $color . '">' . $wallet->formatted_balance . '</span>';
                })
                ->addColumn('total_funded_fmt', function ($wallet) {
                    return '<span class="text-success">' . $wallet->formatted_total_funded . '</span>';
                })
                ->addColumn('total_deducted_fmt', function ($wallet) {
                    return '<span class="text-danger">' . $wallet->formatted_total_deducted . '</span>';
                })
                ->addColumn('total_returned_fmt', function ($wallet) {
                    return '<span class="text-info">' . $wallet->formatted_total_returned . '</span>';
                })
                ->addColumn('status', function ($wallet) {
                    return $wallet->status_badge;
                })
                ->addColumn('bank_info', function ($wallet) {
                    if ($wallet->bank_name) {
                        return '<div class="small">' . e($wallet->bank_name) . '</div>' .
                            '<div class="text-muted small">' . e($wallet->account_number) . '</div>';
                    }
                    return '<span class="text-muted small">Not set</span>';
                })
                ->addColumn('action', function ($wallet) {
                    return '<div class="d-flex gap-1">' .
                        '<a href="' . route('wallets.show', $wallet->id) . '" class="btn btn-sm btn-info" title="View"><i class="ti-eye"></i></a>' .
                        '<a href="' . route('wallets.fund-form', $wallet->id) . '" class="btn btn-sm btn-success" title="Fund / Deduct"><i class="ti-wallet"></i></a>' .
                        '<a href="' . route('wallets.edit', $wallet->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="ti-pencil"></i></a>' .
                        '</div>';
                })
                ->rawColumns(['facility_name', 'balance_fmt', 'total_funded_fmt', 'total_deducted_fmt', 'total_returned_fmt', 'status', 'bank_info', 'action'])
                ->make(true);
        }

        // Overview stats
        $stats = [
            'total_wallets' => FacilityWallet::count(),
            'active_wallets' => FacilityWallet::active()->count(),
            'total_balance' => FacilityWallet::active()->sum('balance'),
            'total_funded' => FacilityWallet::sum('total_funded'),
            'total_deducted' => FacilityWallet::sum('total_deducted'),
            'total_returned' => FacilityWallet::sum('total_returned'),
            'total_drugs_dispensed_value' => DB::table('wallet_transactions')
                ->where('type', WalletTransaction::TYPE_DISPENSATION_RETURN)
                ->sum('drug_cost'),
        ];

        return view('wallets.index', compact('stats'));
    }

    /**
     * Show create wallet form.
     */
    public function create()
    {
        $facilities = Facility::orderBy('name')->get();
        $programs = Program::active()->orderBy('name')->get();

        return view('wallets.create', compact('facilities', 'programs'));
    }

    /**
     * Store a new wallet.
     */
    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'program_id' => [
                'required',
                'exists:programs,id',
                \Illuminate\Validation\Rule::unique('facility_wallets')->where(function ($query) use ($request) {
                    return $query->where('facility_id', $request->facility_id)
                                 ->where('program_id', $request->program_id)
                                 ->whereNull('deleted_at');
                })
            ],
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'initial_balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $wallet = FacilityWallet::create([
                'facility_id' => $request->facility_id,
                'program_id' => $request->program_id,
                'balance' => $request->initial_balance ?? 0,
                'total_funded' => $request->initial_balance ?? 0,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'notes' => $request->notes,
                'status' => FacilityWallet::STATUS_ACTIVE,
                'created_by' => Auth::guard('staff')->user()->id,
            ]);

            // Record initial funding if any
            if ($request->initial_balance > 0) {
                $wallet->transactions()->create([
                    'type' => WalletTransaction::TYPE_FUNDING,
                    'amount' => $request->initial_balance,
                    'balance_before' => 0,
                    'balance_after' => $request->initial_balance,
                    'description' => 'Initial wallet funding',
                    'performed_by' => Auth::guard('staff')->user()->id,
                ]);
            }

            DB::commit();

            return redirect()->route('wallets.show', $wallet->id)
                ->with('success', 'Wallet created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating wallet: ' . $e->getMessage());
            return back()->with('error', 'Failed to create wallet. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show wallet details with transaction history.
     */
    public function show(Request $request, string $id)
    {
        $wallet = FacilityWallet::with('facility')->findOrFail($id);

        // Handle AJAX for transaction DataTable
        if ($request->ajax()) {
            $query = WalletTransaction::where('wallet_id', $id);

            if ($request->filled('type')) {
                $query->where('type', $request->get('type'));
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            return DataTables::of($query)
                ->addColumn('date_fmt', function ($txn) {
                    return '<div>' . $txn->created_at->format('M j, Y') . '</div>' .
                        '<div class="text-muted small">' . $txn->created_at->format('g:i A') . '</div>';
                })
                ->addColumn('type_badge', function ($txn) {
                    return $txn->type_badge;
                })
                ->addColumn('amount_fmt', function ($txn) {
                    $prefix = $txn->amount >= 0 ? '+' : '';
                    $color = $txn->amount >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="fw-bold ' . $color . '">' . $prefix . '₦' . number_format(abs($txn->amount), 2) . '</span>';
                })
                ->addColumn('balance_after_fmt', function ($txn) {
                    return '₦' . number_format($txn->balance_after, 2);
                })
                ->addColumn('drug_info', function ($txn) {
                    if ($txn->drug_name) {
                        return '<div class="fw-bold">' . e($txn->drug_name) . '</div>' .
                            '<div class="text-muted small">' . $txn->drug_quantity . ' unit(s) · Cost: ₦' . number_format($txn->drug_cost, 2) . '</div>';
                    }
                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('description_fmt', function ($txn) {
                    $desc = e($txn->description);
                    if (strlen($desc) > 60) {
                        $desc = '<span title="' . e($txn->description) . '">' . substr($desc, 0, 60) . '...</span>';
                    }
                    return $desc;
                })
                ->addColumn('action', function ($txn) {
                    if ($txn->type === WalletTransaction::TYPE_FUNDING) {
                        return '<a href="' . route('wallets.edit-fund', $txn->id) . '" class="btn btn-sm btn-warning" title="Edit Fund"><i class="ti-pencil"></i></a>';
                    }
                    return '';
                })
                ->order(function ($query) {
                    $query->orderBy('created_at', 'desc');
                })
                ->rawColumns(['date_fmt', 'type_badge', 'amount_fmt', 'drug_info', 'description_fmt', 'action'])
                ->make(true);
        }

        // Stats for this wallet
        $walletStats = [
            'total_dispensation_returns' => $wallet->transactions()
                ->where('type', WalletTransaction::TYPE_DISPENSATION_RETURN)->count(),
            'total_drugs_dispensed_value' => $wallet->transactions()
                ->where('type', WalletTransaction::TYPE_DISPENSATION_RETURN)->sum('drug_cost'),
            'total_stock_deductions' => $wallet->transactions()
                ->where('type', WalletTransaction::TYPE_DRUG_STOCK_DEDUCTION)->count(),
            'funding_count' => $wallet->transactions()
                ->where('type', WalletTransaction::TYPE_FUNDING)->count(),
            'last_funded' => $wallet->transactions()
                ->where('type', WalletTransaction::TYPE_FUNDING)->latest()->first(),
            'last_transaction' => $wallet->transactions()->latest()->first(),
        ];

        // Get dispensation return details grouped by drug
        $drugReturns = DB::table('wallet_transactions')
            ->where('wallet_id', $id)
            ->where('type', WalletTransaction::TYPE_DISPENSATION_RETURN)
            ->selectRaw('drug_name, COUNT(*) as return_count, SUM(amount) as total_returned, SUM(drug_cost) as total_drug_cost, SUM(drug_quantity) as total_quantity')
            ->groupBy('drug_name')
            ->orderByDesc('total_returned')
            ->limit(20)
            ->get();

        $transactionTypes = WalletTransaction::getTypes();

        return view('wallets.show', compact('wallet', 'walletStats', 'drugReturns', 'transactionTypes'));
    }

    /**
     * Show edit wallet form.
     */
    public function edit(string $id)
    {
        $wallet = FacilityWallet::with('facility')->findOrFail($id);
        $programs = \App\Models\Program::active()->orderBy('name')->get();
        return view('wallets.edit', compact('wallet', 'programs'));
    }

    /**
     * Update wallet details.
     */
    public function update(Request $request, string $id)
    {
        $wallet = FacilityWallet::findOrFail($id);

        $request->validate([
            'program_id' => [
                'required',
                'exists:programs,id',
                \Illuminate\Validation\Rule::unique('facility_wallets')->where(function ($query) use ($request, $wallet) {
                    return $query->where('facility_id', $wallet->facility_id)
                                 ->where('program_id', $request->program_id)
                                 ->whereNull('deleted_at');
                })->ignore($wallet->id)
            ],
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (empty($wallet->wallet_number)) {
            $wallet->wallet_number = FacilityWallet::generateUniqueWalletNumber();
        }

        $wallet->update($request->only(['program_id', 'bank_name', 'account_number', 'account_name', 'status', 'notes']));

        return redirect()->route('wallets.show', $wallet->id)
            ->with('success', 'Wallet updated successfully!');
    }

    /**
     * Show fund wallet form.
     */
    public function fundForm(string $id)
    {
        $wallet = FacilityWallet::with('facility')->findOrFail($id);
        return view('wallets.fund', compact('wallet'));
    }

    /**
     * Fund the wallet.
     */
    public function fund(Request $request, string $id)
    {
        $wallet = FacilityWallet::findOrFail($id);

        $request->validate([
            'action_type' => 'required|in:fund,deduct',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            if ($request->action_type === 'fund') {
                $wallet->fund(
                    $request->amount,
                    Auth::guard('staff')->user()->id,
                    $request->description
                );
                $message = 'Wallet funded successfully with ₦' . number_format($request->amount, 2) . '!';
            } else {
                $wallet->deduct(
                    $request->amount,
                    Auth::guard('staff')->user()->id,
                    $request->description
                );
                $message = '₦' . number_format($request->amount, 2) . ' deducted from wallet successfully!';
            }

            DB::commit();

            return redirect()->route('wallets.show', $wallet->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error funding wallet: ' . $e->getMessage());
            return back()->with('error', 'Failed to fund wallet. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit fund form for a specific funding transaction.
     */
    public function editFund(string $id)
    {
        $transaction = WalletTransaction::with('wallet.facility')->findOrFail($id);
        
        if ($transaction->type !== WalletTransaction::TYPE_FUNDING) {
            return back()->with('error', 'Only funding transactions can be edited.');
        }

        return view('wallets.edit-fund', compact('transaction'));
    }

    /**
     * Update the funding transaction.
     */
    public function updateFund(Request $request, string $id)
    {
        $transaction = WalletTransaction::with('wallet')->findOrFail($id);

        if ($transaction->type !== WalletTransaction::TYPE_FUNDING) {
            return back()->with('error', 'Only funding transactions can be edited.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $oldAmount = $transaction->amount;
            $newAmount = $request->amount;
            $difference = $newAmount - $oldAmount;

            if ($difference != 0) {
                // Check if reducing the fund would result in a negative wallet balance
                $wallet = $transaction->wallet;
                if ($difference < 0 && $wallet->balance < abs($difference)) {
                    throw new \Exception("Cannot reduce fund amount. The wallet has insufficient balance (₦" . number_format($wallet->balance, 2) . ").");
                }

                // Update the transaction
                $transaction->amount = $newAmount;
                $transaction->balance_after += $difference;
                
                // Update the wallet
                $wallet->balance += $difference;
                $wallet->total_funded += $difference;
                $wallet->save();

                // Update all subsequent transactions for this wallet to fix their running balances
                WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('created_at', '>', $transaction->created_at)
                    ->update([
                        'balance_before' => DB::raw("balance_before + $difference"),
                        'balance_after' => DB::raw("balance_after + $difference")
                    ]);
            }

            $transaction->description = $request->description;
            $transaction->save();

            DB::commit();

            return redirect()->route('wallets.show', $transaction->wallet->id)
                ->with('success', 'Funding transaction updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating fund transaction: ' . $e->getMessage());
            return back()->with('error', 'Failed to update transaction. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Check wallet balance for a facility (AJAX endpoint).
     */
    public function checkBalance(string $facilityId, string $programId)
    {
        $wallet = FacilityWallet::getForFacilityAndProgram($facilityId, $programId);

        if (!$wallet) {
            return response()->json([
                'has_wallet' => false,
                'message' => 'No wallet found for this facility.',
            ]);
        }

        return response()->json([
            'has_wallet' => true,
            'balance' => $wallet->balance,
            'formatted_balance' => $wallet->formatted_balance,
            'status' => $wallet->status,
            'is_active' => $wallet->status === FacilityWallet::STATUS_ACTIVE,
        ]);
    }
}
