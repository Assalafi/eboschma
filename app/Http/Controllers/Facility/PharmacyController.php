<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\DrugStock;
use App\Models\DrugStockRequest;
use App\Models\DrugStockRequestItem;
use App\Models\Facility;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DrugsImport;
use App\Exports\DrugsExport;
use Yajra\DataTables\Facades\DataTables;

class PharmacyController extends Controller
{
    /**
     * Check if current user has pharmacy admin position
     */
    private function isPharmacyAdmin(): bool
    {
        $user = Auth::guard('web')->user();
        $pharmacyPositions = [
            'Chief Pharmacist',
            'Pharmacist',
            'Hospital Administrator',
            'Admin'
        ];
        
        return $user && in_array($user->staffPosition->name ?? '', $pharmacyPositions);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }

        $facilityId = Auth::guard('web')->user()->facility_id;
        
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = Drug::with(['stocks' => function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->where('status', 'approved');
            }]);
            
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('description', 'LIKE', "%{$search}%")
                              ->orWhere('strength', 'LIKE', "%{$search}%");
                        });
                    }
                    
                    if ($request->has('dosage_form') && !empty($request->get('dosage_form'))) {
                        $query->where('dosage_form', $request->get('dosage_form'));
                    }
                })
                ->addColumn('quantity', function($drug) {
                    return $drug->stocks->sum('quantity_remaining');
                })
                ->addColumn('stock_level', function($drug) use ($facilityId) {
                    $quantity = $drug->stocks->sum('quantity_remaining');
                    $nearExpiry = $drug->stocks
                        ->where('expiry_date', '<=', now()->addDays(30))
                        ->where('expiry_date', '>', now())
                        ->count();
                    
                    $badge = '';
                    if ($quantity == 0) {
                        $badge = '<span class="badge bg-danger">Out of Stock</span>';
                    } elseif ($quantity <= 10) {
                        $badge = '<span class="badge bg-warning">' . $quantity . ' units</span>';
                    } else {
                        $badge = '<span class="badge bg-success">' . $quantity . ' units</span>';
                    }
                    
                    if ($nearExpiry > 0) {
                        $badge .= '<div class="mt-1"><span class="badge bg-warning" style="font-size: 0.65rem;">⚠️ ' . $nearExpiry . ' expiring soon</span></div>';
                    }
                    
                    return $badge;
                })
                ->addColumn('stock_status', function($drug) {
                    $quantity = $drug->stocks->sum('quantity_remaining');
                    
                    if ($quantity == 0) {
                        return '<span class="badge bg-danger">Out of Stock</span>';
                    } elseif ($quantity <= 10) {
                        return '<span class="badge bg-warning">Low Stock</span>';
                    } else {
                        return '<span class="badge bg-success">In Stock</span>';
                    }
                })
                ->addColumn('unit_price_formatted', function($drug) {
                    return '₦' . number_format($drug->unit_price, 2);
                })
                ->addColumn('total_value', function($drug) {
                    $quantity = $drug->stocks->sum('quantity_remaining');
                    return '₦' . number_format($quantity * $drug->unit_price, 2);
                })
                ->addColumn('action', function($drug) {
                    return '
                        <div class="d-flex gap-1">
                            <a href="' . route('drug-stock-requests.create', ['drug_id' => $drug->id]) . '" 
                               class="btn btn-sm btn-success" title="Request Stock">
                                ➕
                            </a>
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="showStockDetails(' . $drug->id . ')" title="Stock Details">
                                👁️
                            </button>
                            <a href="' . route('facility.pharmacy.edit', $drug->id) . '" 
                               class="btn btn-sm btn-warning" title="Edit">
                                ✏️
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['stock_level', 'stock_status', 'action'])
                ->make(true);
        }
        
        // Get statistics for current facility using DrugStock
        $totalDrugs = Drug::count();
        
        // Calculate total quantity per drug to determine stock levels
        $drugQuantities = DrugStock::where('facility_id', $facilityId)
                            ->where('status', 'approved')
                            ->select('drug_id', DB::raw('SUM(quantity_remaining) as total_quantity'))
                            ->groupBy('drug_id')
                            ->get();
        
        $lowStockCount = $drugQuantities->where('total_quantity', '>', 0)
                                        ->where('total_quantity', '<=', 10)
                                        ->count();
        
        $inStockCount = $drugQuantities->where('total_quantity', '>', 10)->count();
        
        $drugsWithStock = $drugQuantities->where('total_quantity', '>', 0)->count();
        $outOfStockCount = $totalDrugs - $drugsWithStock;
        
        $stats = [
            'total_drugs' => $totalDrugs,
            'in_stock' => $inStockCount,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'total_value' => DrugStock::where('facility_id', $facilityId)
                                ->where('status', 'approved')
                                ->sum(DB::raw('quantity_remaining * unit_cost')) ?? 0,
            'near_expiry' => DrugStock::where('facility_id', $facilityId)
                                ->where('status', 'approved')
                                ->where('expiry_date', '<=', now()->addDays(30))
                                ->where('expiry_date', '>', now())
                                ->count(),
            'unique_drugs' => $totalDrugs
        ];
        
        $dosageForms = Drug::distinct()
            ->pluck('dosage_form')
            ->filter()
            ->sort()
            ->values();
        
        return view('facility.pharmacy.index', compact('stats', 'dosageForms'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        $dosageForms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Cream', 'Drops', 'Inhaler', 'Patch', 'Suppository'];
        
        return view('facility.pharmacy.create', compact('facilityId', 'dosageForms'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'dosage_form' => 'required|string|max:100',
            'strength' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
        ]);
        
        try {
            $data = $request->all();
            $data['facility_id'] = $facilityId;
            
            // Create drug without quantity (quantity is now managed through DrugStock)
            Drug::create($data);
            
            return redirect()->route('facility.pharmacy.index')
                ->with('success', 'Drug added successfully! To add stock, please create a stock request.');
        } catch (\Exception $e) {
            Log::error('Error creating drug: ' . $e->getMessage());
            return back()->with('error', 'Failed to add drug. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $drug = Drug::where('id', $id)
            ->where('facility_id', $facilityId)
            ->firstOrFail();
            
        $dosageForms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Cream', 'Drops', 'Inhaler', 'Patch', 'Suppository'];
        
        return view('facility.pharmacy.edit', compact('drug', 'dosageForms'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $drug = Drug::where('id', $id)
            ->where('facility_id', $facilityId)
            ->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'dosage_form' => 'required|string|max:100',
            'strength' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);
        
        try {
            $drug->update($request->all());
            
            return redirect()->route('facility.pharmacy.index')
                ->with('success', 'Drug updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating drug: ' . $e->getMessage());
            return back()->with('error', 'Failed to update drug. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        try {
            $drug = Drug::where('id', $id)
                ->where('facility_id', $facilityId)
                ->firstOrFail();
            
            $drug->delete();
            
            return redirect()->route('facility.pharmacy.index')
                ->with('success', 'Drug deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting drug: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete drug. Please try again.');
        }
    }
    
    /**
     * Show the form for bulk creating drugs
     */
    public function bulkCreate(): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        $dosageForms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Cream', 'Drops', 'Inhaler', 'Patch', 'Suppository'];
        
        return view('facility.pharmacy.bulk-create', compact('facilityId', 'dosageForms'));
    }
    
    /**
     * Store bulk created drugs
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $request->validate([
            'drugs' => 'required|array|min:1',
            'drugs.*.name' => 'required|string|max:255',
            'drugs.*.description' => 'nullable|string|max:1000',
            'drugs.*.dosage_form' => 'required|string|max:100',
            'drugs.*.strength' => 'required|string|max:100',
            'drugs.*.unit' => 'required|string|max:50',
            'drugs.*.quantity' => 'required|integer|min:0',
            'drugs.*.unit_price' => 'required|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($request->drugs as $index => $drugData) {
                try {
                    $drugData['facility_id'] = $facilityId;
                    Drug::create($drugData);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            $message = "Successfully added {$successCount} drugs.";
            if ($errorCount > 0) {
                $message .= " Failed to add {$errorCount} drugs.";
                Log::error('Bulk drug creation errors: ' . implode(', ', $errors));
                return redirect()->route('facility.pharmacy.index')
                    ->with('warning', $message);
            }
            
            return redirect()->route('facility.pharmacy.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk drug creation: ' . $e->getMessage());
            return back()->with('error', 'Failed to create drugs. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show the form for Excel upload
     */
    public function importForm(): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        return view('facility.pharmacy.import');
    }
    
    /**
     * Import drugs from Excel file
     */
    public function import(Request $request): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);
        
        try {
            $facilityId = Auth::guard('web')->user()->facility_id;
            
            // Import with facility ID
            Excel::import(new DrugsImport($facilityId), $request->file('excel_file'));
            
            return redirect()->route('facility.pharmacy.index')
                ->with('success', 'Drugs imported successfully from Excel file!');
        } catch (\Exception $e) {
            Log::error('Error importing drugs: ' . $e->getMessage());
            return back()->with('error', 'Failed to import drugs. Please check your file format and try again. Error: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Export drugs to Excel
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        return Excel::download(new DrugsExport($facilityId), 'drugs_' . date('Y-m-d_H-i-s') . '.xlsx');
    }
    
    /**
     * Download Excel template
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $templateDir = storage_path('app/templates');
        $templatePath = $templateDir . '/drugs_template.xlsx';
        
        // Ensure template directory exists
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        if (!file_exists($templatePath)) {
            // Create a simple template if it doesn't exist
            $this->createExcelTemplate($templatePath);
        }
        
        return response()->download($templatePath, 'drugs_import_template.xlsx');
    }
    
    /**
     * Create Excel template for drug import
     */
    private function createExcelTemplate(string $path): void
    {
        try {
            // Check if PHPSpreadsheet is available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                // Fallback: Create a simple CSV template
                $this->createCsvTemplate($path);
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = ['Name', 'Description', 'Dosage Form', 'Strength', 'Unit', 'Quantity', 'Unit Price'];
            $sheet->fromArray($headers, null, 'A1');
            
            // Add multiple example rows
            $examples = [
                ['Paracetamol', 'Pain reliever and fever reducer', 'Tablet', '500mg', 'tablets', 100, 50.00],
                ['Amoxicillin', 'Antibiotic for bacterial infections', 'Capsule', '250mg', 'capsules', 50, 120.00],
                ['Ibuprofen', 'Anti-inflammatory pain medication', 'Tablet', '400mg', 'tablets', 75, 80.00]
            ];
            $sheet->fromArray($examples, null, 'A2');
            
            // Style the header row
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFE0E0E0');
            
            // Auto-size columns
            foreach (range('A', 'G') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Add instructions on the side
            $sheet->setCellValue('I1', 'INSTRUCTIONS:');
            $sheet->setCellValue('I2', '1. Fill in drug information in columns A-G');
            $sheet->setCellValue('I3', '2. Required fields: Name, Dosage Form, Strength, Unit, Quantity, Unit Price');
            $sheet->setCellValue('I4', '3. Description is optional');
            $sheet->setCellValue('I5', '4. Save as .xlsx file');
            $sheet->setCellValue('I6', '5. Upload using the Import Excel feature');
            
            // Style instructions
            $sheet->getStyle('I1:I6')->getFont()->setBold(true);
            $sheet->getStyle('I1')->getFont()->setSize(12);
            $sheet->getStyle('I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFE6F3FF');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($path);
            
        } catch (\Exception $e) {
            // Fallback to CSV if Excel creation fails
            Log::error('Failed to create Excel template, falling back to CSV: ' . $e->getMessage());
            $this->createCsvTemplate($path);
        }
    }
    
    /**
     * Create CSV template as fallback
     */
    private function createCsvTemplate(string $path): void
    {
        $csvPath = str_replace('.xlsx', '.csv', $path);
        
        $handle = fopen($csvPath, 'w');
        if ($handle === false) {
            throw new \Exception('Cannot create template file');
        }
        
        // Add headers
        fputcsv($handle, ['Name', 'Description', 'Dosage Form', 'Strength', 'Unit', 'Quantity', 'Unit Price']);
        
        // Add example data
        fputcsv($handle, ['Paracetamol', 'Pain reliever', 'Tablet', '500mg', 'tablets', 100, 50.00]);
        
        fclose($handle);
        
        // Rename to .xlsx for consistency (even though it's CSV)
        if (file_exists($csvPath)) {
            rename($csvPath, $path);
        }
    }
    
    /**
     * Show the form for stocking drugs
     */
    public function stockForm(Request $request): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        // Build query with search and filtering
        $query = Drug::where('facility_id', $facilityId);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('dosage_form', 'LIKE', "%{$search}%")
                  ->orWhere('strength', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by stock level
        if ($request->filled('stock_filter')) {
            $filter = $request->input('stock_filter');
            switch ($filter) {
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)->where('quantity', '<=', 10);
                    break;
                case 'in_stock':
                    $query->where('quantity', '>', 10);
                    break;
            }
        }
        
        // Filter by dosage form
        if ($request->filled('dosage_form')) {
            $query->where('dosage_form', $request->input('dosage_form'));
        }
        
        // Order by name and paginate
        $drugs = $query->orderBy('name', 'asc')
            ->paginate(50)
            ->withQueryString();
        
        // Get statistics for the dashboard
        $totalDrugs = Drug::where('facility_id', $facilityId)->count();
        $outOfStock = Drug::where('facility_id', $facilityId)->where('quantity', 0)->count();
        $lowStock = Drug::where('facility_id', $facilityId)->where('quantity', '>', 0)->where('quantity', '<=', 10)->count();
        
        // Get available dosage forms for filter dropdown
        $dosageForms = Drug::where('facility_id', $facilityId)
            ->whereNotNull('dosage_form')
            ->where('dosage_form', '!=', '')
            ->distinct()
            ->pluck('dosage_form')
            ->sort()
            ->values();
        
        return view('facility.pharmacy.stock', compact('drugs', 'totalDrugs', 'outOfStock', 'lowStock', 'dosageForms'));
    }
    
    /**
     * Update stock levels
     */
    public function updateStock(Request $request): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $request->validate([
            'selected_drugs' => 'required|array|min:1',
            'selected_drugs.*' => 'required|string|exists:drugs,id',
            'stock_updates' => 'required|array',
            'stock_updates.*.drug_id' => 'required|string|exists:drugs,id',
            'stock_updates.*.quantity_change' => 'required|integer|min:0',
            'stock_updates.*.operation' => 'required|in:add,subtract,set'
        ], [
            'selected_drugs.required' => 'Please select at least one drug to update.',
            'selected_drugs.min' => 'Please select at least one drug to update.',
            'stock_updates.*.quantity_change.min' => 'Quantity change must be 0 or greater.'
        ]);
        
        try {
            DB::beginTransaction();
            
            $updatedCount = 0;
            
            // Only process selected drugs
            foreach ($request->selected_drugs as $drugId) {
                // Check if this drug has stock update data
                if (!isset($request->stock_updates[$drugId])) {
                    continue;
                }
                
                $update = $request->stock_updates[$drugId];
                
                // Verify drug belongs to this facility
                $drug = Drug::where('id', $drugId)
                    ->where('facility_id', $facilityId)
                    ->firstOrFail();
                
                $currentQuantity = $drug->quantity;
                $change = (int) $update['quantity_change'];
                
                // Skip if no change requested
                if ($change === 0 && $update['operation'] !== 'set') {
                    continue;
                }
                
                switch ($update['operation']) {
                    case 'add':
                        $newQuantity = $currentQuantity + $change;
                        break;
                    case 'subtract':
                        $newQuantity = max(0, $currentQuantity - $change);
                        break;
                    case 'set':
                        $newQuantity = max(0, $change);
                        break;
                }
                
                $drug->update(['quantity' => $newQuantity]);
                $updatedCount++;
            }
            
            DB::commit();
            
            if ($updatedCount === 0) {
                return back()->with('error', 'No valid stock updates were made. Please enter quantities greater than 0 for selected drugs.')
                    ->withInput();
            }
            
            return redirect()->route('facility.pharmacy.index')
                ->with('success', "Stock levels updated successfully! Updated {$updatedCount} drug(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating stock: ' . $e->getMessage());
            return back()->with('error', 'Failed to update stock levels. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Show low stock alerts with pagination and filtering
     */
    public function lowStock(Request $request): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only pharmacy administrators can manage drugs.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        // Build queries with search and filtering
        $lowStockQuery = Drug::where('facility_id', $facilityId)
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10);
            
        $outOfStockQuery = Drug::where('facility_id', $facilityId)
            ->where('quantity', '=', 0);
        
        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $searchTerm = "%{$search}%";
            
            $lowStockQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('description', 'LIKE', $searchTerm)
                  ->orWhere('dosage_form', 'LIKE', $searchTerm)
                  ->orWhere('strength', 'LIKE', $searchTerm);
            });
            
            $outOfStockQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('description', 'LIKE', $searchTerm)
                  ->orWhere('dosage_form', 'LIKE', $searchTerm)
                  ->orWhere('strength', 'LIKE', $searchTerm);
            });
        }
        
        // Apply dosage form filter if provided
        if ($request->filled('dosage_form')) {
            $dosageForm = $request->input('dosage_form');
            $lowStockQuery->where('dosage_form', $dosageForm);
            $outOfStockQuery->where('dosage_form', $dosageForm);
        }
        
        // Get paginated results
        $lowStockDrugs = $lowStockQuery->orderBy('quantity', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(25)
            ->withQueryString();
            
        $outOfStockDrugs = $outOfStockQuery->orderBy('name', 'asc')
            ->paginate(25)
            ->withQueryString();
        
        // Get available dosage forms for filter
        $dosageForms = Drug::where('facility_id', $facilityId)
            ->whereNotNull('dosage_form')
            ->where('dosage_form', '!=', '')
            ->distinct()
            ->pluck('dosage_form')
            ->sort()
            ->values();
        
        return view('facility.pharmacy.low-stock', compact('lowStockDrugs', 'outOfStockDrugs', 'dosageForms', 'search', 'dosageForm'));
    }
    
    /**
     * Show stock requests for current facility.
     */
    public function stockRequests(Request $request)
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can view stock requests.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = DrugStockRequest::with(['drug', 'program', 'items.drug', 'approvedBy', 'dispensedBy'])
                ->where('facility_id', $facilityId);
            
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function($q) use ($search) {
                            $q->where('reason', 'LIKE', "%{$search}%")
                              ->orWhere('notes', 'LIKE', "%{$search}%")
                              ->orWhereHas('drug', function($subQ) use ($search) {
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
                })
                ->addColumn('request_id', function($request) {
                    return '<span class="text-muted">#' . str_pad($request->id, 6, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('drug_info', function($request) {
                    if ($request->drug_id) {
                        return '<div>' . $request->drug->name . '</div>' .
                               '<div class="text-muted">' . $request->drug->strength . ' ' . $request->drug->unit . '</div>';
                    } else {
                        return '<div class="fw-bold text-primary">Bulk Request</div>' .
                               '<div class="text-muted">' . $request->items->count() . ' items</div>';
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
                           '<div class="text-muted">' . $request->requested_at->format('g:i A') . '</div>';
                })
                ->addColumn('action', function($request) {
                    return '<div class="d-flex gap-1">
                                <a href="' . route('facility.pharmacy.stock-requests.show', $request->id) . '" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    👁️
                                </a>
                            </div>';
                })
                ->rawColumns(['request_id', 'drug_info', 'priority', 'status', 'requested', 'action'])
                ->make(true);
        }
        
        // Get filter options
        $statuses = DrugStockRequest::getStatuses();
        $priorities = DrugStockRequest::getPriorities();
        
        // Get statistics
        $stats = [
            'pending' => DrugStockRequest::where('facility_id', $facilityId)->pending()->count(),
            'approved' => DrugStockRequest::where('facility_id', $facilityId)->approved()->count(),
            'rejected' => DrugStockRequest::where('facility_id', $facilityId)->rejected()->count(),
            'dispensed' => DrugStockRequest::where('facility_id', $facilityId)->dispensed()->count(),
        ];
        
        $hasWallet = \App\Models\FacilityWallet::where('facility_id', $facilityId)->count() > 0;
        
        return view('facility.pharmacy.stock-requests', compact('statuses', 'priorities', 'stats', 'hasWallet'));
    }
    
    /**
     * Show stock details for a specific drug.
     */
    public function stockDetails(string $id): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can view stock details.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        $drug = Drug::findOrFail($id);
        
        // Get stock summary
        $stockSummary = [
            'in_stock_batches' => $drug->drugStocks->where('status', 'dispensed')->where('quantity_remaining', '>', 0)->count(),
            'expired_batches' => $drug->drugStocks->where('status', 'expired')->count(),
            'near_expiry_count' => $drug->drugStocks->filter(function($stock) {
                return $stock->isNearExpiry();
            })->count(),
            'total_value' => $drug->drugStocks->where('status', 'dispensed')->sum(function($stock) {
                return $stock->quantity_remaining * $stock->unit_cost;
            }),
        ];
        
        // Group stocks by status
        $stocksByStatus = [
            'dispensed' => $drug->drugStocks->where('status', 'dispensed'),
            'pending' => $drug->drugStocks->where('status', 'pending'),
            'approved' => $drug->drugStocks->where('status', 'approved'),
            'rejected' => $drug->drugStocks->where('status', 'rejected'),
            'expired' => $drug->drugStocks->where('status', 'expired'),
        ];
        
        return view('facility.pharmacy.stock-details', compact('drug', 'stockSummary', 'stocksByStatus'));
    }
    
    /**
     * Show the form for creating a bulk stock request.
     */
    public function bulkStockRequest(): View
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can create stock requests.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        // Get drugs - try facility-specific first, then fallback to drugs with facility_id 0
        $drugs = Drug::where('facility_id', $facilityId)
                    ->orWhere('facility_id', 0)
                    ->with(['stocks' => function($query) use ($facilityId) {
                        $query->where('facility_id', $facilityId)
                              ->where('status', 'approved');
                    }])
                    ->orderBy('name')
                    ->paginate(100) // Show 100 drugs per page
                    ->through(function ($drug) use ($facilityId) {
                        // Calculate total quantity from stocks
                        $drug->quantity = $drug->stocks->sum('quantity_remaining');
                        // Ensure status field exists
                        $drug->status = $drug->status ?: 'active';
                        return $drug;
                    });
        
        $priorities = DrugStockRequest::getPriorities();
        $programs = Program::active()->orderBy('name')->get();
        
        $walletCount = \App\Models\FacilityWallet::where('facility_id', $facilityId)->count();
        
        $walletsByProgram = \App\Models\FacilityWallet::where('facility_id', $facilityId)
            ->where('status', 'active')
            ->get()
            ->keyBy('program_id')
            ->map(fn($w) => ['balance' => (float) $w->balance, 'wallet_number' => $w->wallet_number]);
        
        return view('facility.pharmacy.bulk-stock-request', compact('drugs', 'priorities', 'programs', 'walletCount', 'walletsByProgram'));
    }
    
    /**
     * Store a bulk stock request.
     */
    public function storeBulkStockRequest(Request $request): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can create stock requests.');
        }
        
        // Log the incoming request
        Log::info('Bulk stock request submission started', [
            'user_id' => Auth::guard('web')->user()->id,
            'facility_id' => Auth::guard('web')->user()->facility_id,
            'program_id' => $request->program_id,
            'bulk_quantity' => $request->bulk_quantity,
            'bulk_priority' => $request->bulk_priority,
            'selected_drugs' => $request->selected_drugs
        ]);
        
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'selected_drugs' => 'required|string',
            'bulk_quantity' => 'required|integer|min:1',
            'bulk_priority' => 'required|in:low,medium,high,urgent',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);
        
        try {
            $selectedDrugIds = json_decode($request->selected_drugs, true);
            
            if (empty($selectedDrugIds) || !is_array($selectedDrugIds)) {
                Log::error('Invalid selected drugs data', ['selected_drugs' => $request->selected_drugs]);
                return back()->with('error', 'No drugs selected for the bulk request.')
                    ->withInput();
            }
            
            Log::info('Processing bulk request for drugs', ['drug_count' => count($selectedDrugIds), 'drug_ids' => $selectedDrugIds]);
            
            DB::beginTransaction();
            
            // Get selected drugs
            $drugs = Drug::whereIn('id', $selectedDrugIds)->get();
            
            if ($drugs->count() !== count($selectedDrugIds)) {
                Log::error('Some drugs not found', ['requested' => $selectedDrugIds, 'found' => $drugs->pluck('id')->toArray()]);
                return back()->with('error', 'Some selected drugs were not found.')
                    ->withInput();
            }
            
            // Prepare bulk request data
            $bulkQuantity = $request->bulk_quantity;
            $bulkPriority = $request->bulk_priority;
            
            // Validate wallet balance
            $totalCost = 0;
            foreach ($drugs as $drug) {
                $totalCost += ($drug->unit_price * $bulkQuantity);
            }
            
            $wallet = \App\Models\FacilityWallet::getForFacilityAndProgram(Auth::guard('web')->user()->facility_id, $request->program_id);
            if (!$wallet) {
                return back()->with('error', 'No wallet found for this program.')->withInput();
            }
            
            if ($totalCost > $wallet->balance) {
                return back()->with('error', "Total estimated cost exceeds wallet balance (₦" . number_format($wallet->balance, 2) . ").")->withInput();
            }
            
            $requests = [];
            
            foreach ($drugs as $drug) {
                $estimatedCost = $drug->unit_price * $bulkQuantity;
                
                Log::info('Calculating cost for drug', [
                    'drug_name' => $drug->name,
                    'unit_price' => $drug->unit_price,
                    'quantity' => $bulkQuantity,
                    'estimated_cost' => $estimatedCost
                ]);
                
                $requests[] = [
                    'drug_id' => $drug->id,
                    'quantity_requested' => $bulkQuantity,
                    'estimated_cost' => $estimatedCost,
                    'priority' => $bulkPriority,
                ];
            }
            
            // Calculate total estimated cost for the bulk request
            $totalEstimatedCost = collect($requests)->sum('estimated_cost');
            
            Log::info('Total estimated cost calculated', [
                'total_cost' => $totalEstimatedCost,
                'item_count' => count($requests)
            ]);
            
            // Create the main bulk stock request
            $stockRequest = DrugStockRequest::create([
                'facility_id' => Auth::guard('web')->user()->facility_id,
                'program_id' => $request->program_id,
                'drug_id' => null, // Null for bulk requests
                'quantity_requested' => collect($requests)->sum('quantity_requested'),
                'estimated_cost' => $totalEstimatedCost,
                'priority' => $bulkPriority,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'requested_by' => Auth::guard('web')->user()->id,
                'requested_at' => now(),
            ]);
            
            Log::info('Main stock request created', ['request_id' => $stockRequest->id]);
            
            // Create individual items for the bulk request
            foreach ($requests as $requestData) {
                DrugStockRequestItem::create([
                    'stock_request_id' => $stockRequest->id,
                    'drug_id' => $requestData['drug_id'],
                    'quantity_requested' => $requestData['quantity_requested'],
                    'estimated_cost' => $requestData['estimated_cost'],
                    'priority' => $requestData['priority'],
                ]);
            }
            
            DB::commit();
            
            $itemCount = count($requests);
            $message = "Bulk stock request with {$itemCount} items submitted successfully! Awaiting Boschma admin approval.";
            
            Log::info('Bulk stock request completed successfully', [
                'request_id' => $stockRequest->id,
                'item_count' => $itemCount,
                'total_cost' => $totalEstimatedCost
            ]);
            
            return redirect()->route('facility.pharmacy.stock-requests')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bulk drug stock request: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to submit bulk stock request. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show the form for creating a new stock request.
     */
    public function createStockRequest(): View|RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can create stock requests.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        // Check if facility has any wallets
        $walletCount = \App\Models\FacilityWallet::where('facility_id', $facilityId)->count();
        
        // Build a map of program_id => wallet for the JS balance lookup
        $walletsByProgram = \App\Models\FacilityWallet::where('facility_id', $facilityId)
            ->where('status', 'active')
            ->get()
            ->keyBy('program_id')
            ->map(fn($w) => ['balance' => (float) $w->balance, 'wallet_number' => $w->wallet_number]);
        
        $drugs = Drug::orderBy('name')->get();
        $priorities = DrugStockRequest::getPriorities();
        $programs = Program::active()->orderBy('name')->get();
        
        return view('facility.pharmacy.create-stock-request', compact(
            'drugs', 'priorities', 'programs', 'facilityId', 'walletCount', 'walletsByProgram'
        ));
    }
    
    /**
     * Display the specified stock request.
     */
    public function showStockRequest(string $id): View
    {
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $stockRequest = DrugStockRequest::where('id', $id)
            ->where('facility_id', $facilityId)
            ->with(['drug', 'program', 'items.drug', 'requestedBy', 'approvedBy', 'dispensedBy', 'drugStocks'])
            ->firstOrFail();
        
        return view('facility.pharmacy.show-stock-request', compact('stockRequest'));
    }
    
    /**
     * Show the form for editing the specified stock request.
     */
    public function editStockRequest(string $id): View
    {
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $stockRequest = DrugStockRequest::where('id', $id)
            ->where('facility_id', $facilityId)
            ->where('status', 'pending') // Only allow editing pending requests
            ->with(['drug', 'items.drug'])
            ->firstOrFail();
        
        $drugs = Drug::orderBy('name')->get();
        $priorities = DrugStockRequest::getPriorities();
        $programs = Program::active()->orderBy('name')->get();
        
        return view('facility.pharmacy.edit-stock-request', compact('stockRequest', 'drugs', 'priorities', 'programs'));
    }
    
    /**
     * Update the specified stock request in storage.
     */
    public function updateStockRequest(Request $request, string $id): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can edit stock requests.');
        }
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $stockRequest = DrugStockRequest::where('id', $id)
            ->where('facility_id', $facilityId)
            ->where('status', 'pending') // Only allow editing pending requests
            ->firstOrFail();
        
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'requests' => 'required|array|min:1',
            'requests.*.drug_id' => 'required|exists:drugs,id',
            'requests.*.quantity_requested' => 'required|integer|min:1',
            'requests.*.estimated_cost' => 'required|numeric|min:0',
            'requests.*.priority' => 'required|in:low,medium,high,urgent',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Delete existing items
            $stockRequest->items()->delete();
            
            // Calculate total estimated cost for the bulk request
            $totalEstimatedCost = collect($request->requests)->sum('estimated_cost');
            
            // Update the main bulk stock request
            $stockRequest->update([
                'drug_id' => null, // Null for bulk requests
                'program_id' => $request->program_id,
                'quantity_requested' => collect($request->requests)->sum('quantity_requested'),
                'estimated_cost' => $totalEstimatedCost,
                'priority' => collect($request->requests)->contains('priority', 'urgent') ? 'urgent' : 
                              (collect($request->requests)->contains('priority', 'high') ? 'high' : 'medium'),
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);
            
            // Create new items for the bulk request
            foreach ($request->requests as $requestData) {
                DrugStockRequestItem::create([
                    'stock_request_id' => $stockRequest->id,
                    'drug_id' => $requestData['drug_id'],
                    'quantity_requested' => $requestData['quantity_requested'],
                    'estimated_cost' => $requestData['estimated_cost'],
                    'priority' => $requestData['priority'],
                ]);
            }
            
            DB::commit();
            
            $itemCount = count($request->requests);
            $message = $itemCount == 1 
                ? 'Stock request updated successfully!'
                : "Bulk stock request with {$itemCount} items updated successfully!";
            
            return redirect()->route('facility.pharmacy.stock-requests')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating drug stock request: ' . $e->getMessage());
            return back()->with('error', 'Failed to update stock request. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Store a newly created stock request in storage.
     */
    public function storeStockRequest(Request $request): RedirectResponse
    {
        // Check pharmacy admin permission
        if (!$this->isPharmacyAdmin()) {
            abort(403, 'Access denied. Only pharmacy administrators can create stock requests.');
        }
        
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'requests' => 'required|array|min:1',
            'requests.*.drug_id' => 'required|exists:drugs,id',
            'requests.*.quantity_requested' => 'required|integer|min:1',
            'requests.*.estimated_cost' => 'required|numeric|min:0',
            'requests.*.priority' => 'required|in:low,medium,high,urgent',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);
        
        $facilityId = Auth::guard('web')->user()->facility_id;
        $wallet = \App\Models\FacilityWallet::getForFacilityAndProgram($facilityId, $request->program_id);
        
        if (!$wallet) {
            return back()->withErrors(['program_id' => 'No wallet found for this program. Contact administrator.'])->withInput();
        }
        
        $totalCost = collect($request->requests)->sum('estimated_cost');
        if ($totalCost > $wallet->balance) {
            return back()->withErrors(['requests' => "Total cost \u20a6" . number_format($totalCost, 2) . " exceeds wallet balance \u20a6" . number_format($wallet->balance, 2) . "."])->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate total estimated cost for the bulk request
            $totalEstimatedCost = collect($request->requests)->sum('estimated_cost');
            
            // Create the main bulk stock request
            $stockRequest = DrugStockRequest::create([
                'facility_id' => Auth::guard('web')->user()->facility_id,
                'program_id' => $request->program_id,
                'drug_id' => null, // Null for bulk requests
                'quantity_requested' => collect($request->requests)->sum('quantity_requested'),
                'estimated_cost' => $totalEstimatedCost,
                'priority' => collect($request->requests)->contains('priority', 'urgent') ? 'urgent' : 
                              (collect($request->requests)->contains('priority', 'high') ? 'high' : 'medium'),
                'reason' => $request->reason,
                'notes' => $request->notes,
                'requested_by' => Auth::guard('web')->user()->id,
                'requested_at' => now(),
            ]);
            
            // Create individual items for the bulk request
            foreach ($request->requests as $requestData) {
                DrugStockRequestItem::create([
                    'stock_request_id' => $stockRequest->id,
                    'drug_id' => $requestData['drug_id'],
                    'quantity_requested' => $requestData['quantity_requested'],
                    'estimated_cost' => $requestData['estimated_cost'],
                    'priority' => $requestData['priority'],
                ]);
            }
            
            DB::commit();
            
            $itemCount = count($request->requests);
            $message = $itemCount == 1 
                ? 'Stock request submitted successfully! Awaiting Boschma admin approval.'
                : "Bulk stock request with {$itemCount} items submitted successfully! Awaiting Boschma admin approval.";
            
            return redirect()->route('facility.pharmacy.stock-requests')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating drug stock request: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit stock request. ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Get current stock level for a drug (AJAX endpoint).
     */
    public function getDrugStock(string $id)
    {
        $facilityId = Auth::guard('web')->user()->facility_id;
        
        $drug = Drug::where('id', $id)
            ->where('facility_id', $facilityId)
            ->firstOrFail();
        
        $totalStock = DrugStock::where('drug_id', $id)
            ->where('facility_id', $facilityId)
            ->where('status', 'dispensed')
            ->sum('quantity_remaining');
        
        $nearExpiry = DrugStock::where('drug_id', $id)
            ->where('facility_id', $facilityId)
            ->where('status', 'dispensed')
            ->nearExpiry()
            ->sum('quantity_remaining');
        
        return response()->json([
            'drug_id' => $drug->id,
            'drug_name' => $drug->name,
            'total_stock' => $totalStock,
            'near_expiry' => $nearExpiry,
            'status' => $totalStock > 0 ? ($totalStock <= 10 ? 'low' : 'good') : 'out',
            'status_text' => $totalStock > 0 ? ($totalStock <= 10 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
        ]);
    }
}
