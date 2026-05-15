<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DrugsExport;
use App\Imports\DrugsImport;
use Yajra\DataTables\DataTables;

class DrugsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:drugs.view', ['only' => ['index', 'show']]);
        $this->middleware('permission:drugs.create', ['only' => ['create', 'store', 'bulkCreate', 'bulkStore']]);
        $this->middleware('permission:drugs.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:drugs.delete', ['only' => ['destroy', 'bulkDelete']]);
    }

    public function index(Request $request)
    {
        \Log::info('DrugsController index called', [
            'is_ajax' => $request->ajax(),
            'request_data' => $request->all()
        ]);
        
        if ($request->ajax()) {
            $query = Drug::query();
            
            \Log::info('Building drugs query');
            
            // Apply filters
            if ($request->has('dosage_form') && $request->dosage_form != '') {
                $query->where('dosage_form', $request->dosage_form);
                \Log::info('Applied dosage_form filter', ['dosage_form' => $request->dosage_form]);
            }
            
            if ($request->has('search') && $request->search != '') {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->search}%")
                      ->orWhere('description', 'LIKE', "%{$request->search}%")
                      ->orWhere('dosage_form', 'LIKE', "%{$request->search}%")
                      ->orWhere('strength', 'LIKE', "%{$request->search}%")
                      ->orWhere('unit', 'LIKE', "%{$request->search}%");
                });
                \Log::info('Applied search filter', ['search' => $request->search]);
            }

            $dataTable = DataTables::of($query)
                ->addColumn('stock_level', function ($drug) {
                    // Get total stock across all facilities
                    $totalStock = DrugStock::where('drug_id', $drug->id)
                        ->where('status', DrugStock::STATUS_APPROVED)
                        ->sum('quantity_remaining');
                    
                    return number_format($totalStock);
                })
                ->addColumn('stock_status', function ($drug) {
                    $totalStock = DrugStock::where('drug_id', $drug->id)
                        ->where('status', DrugStock::STATUS_APPROVED)
                        ->sum('quantity_remaining');
                    
                    // Get near expiry count
                    $nearExpiry = DrugStock::where('drug_id', $drug->id)
                        ->where('status', DrugStock::STATUS_APPROVED)
                        ->where('quantity_remaining', '>', 0)
                        ->where('expiry_date', '<=', now()->addDays(30))
                        ->where('expiry_date', '>', now())
                        ->sum('quantity_remaining');
                    
                    if ($totalStock == 0) {
                        return '<span class="badge bg-danger">Out of Stock</span>';
                    } elseif ($totalStock <= 50) {
                        $badge = '<span class="badge bg-warning">Low Stock</span>';
                        if ($nearExpiry > 0) {
                            $badge .= ' <span class="badge bg-orange ms-1" title="' . number_format($nearExpiry) . ' units expiring soon">⚠️ Expiring</span>';
                        }
                        return $badge;
                    } else {
                        $badge = '<span class="badge bg-success">In Stock</span>';
                        if ($nearExpiry > 0) {
                            $badge .= ' <span class="badge bg-orange ms-1" title="' . number_format($nearExpiry) . ' units expiring soon">⚠️ Expiring</span>';
                        }
                        return $badge;
                    }
                })
                ->addColumn('action', function ($drug) {
                    $actions = '';
                    
                    // Request Stock button
                    if (Auth::user()->can('drug-stock-requests.create')) {
                        $actions .= '<a href="' . route('drug-stock-requests.create', ['drug_id' => $drug->id]) . '" 
                                        class="btn btn-sm btn-success me-1" title="Request Stock">
                                        <i class="ti-package"></i>
                                    </a>';
                    }
                    
                    // Stock Details button
                    $actions .= '<button type="button" class="btn btn-sm btn-info me-1" 
                                    onclick="showStockDetails(\'' . $drug->id . '\')" title="Stock Details">
                                    <i class="ti-eye"></i>
                                </button>';
                    
                    if (Auth::user()->can('drugs.edit')) {
                        $actions .= '<a href="' . route('drugs.edit', $drug->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                        <i class="ti-pencil"></i>
                                    </a>';
                    }
                    
                    if (Auth::user()->can('drugs.delete')) {
                        $actions .= '<form action="' . route('drugs.destroy', $drug->id) . '" method="POST" style="display: inline;">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                            <i class="ti-trash"></i>
                                        </button>
                                    </form>';
                    }
                    
                    return $actions;
                })
                ->addColumn('unit_price_formatted', function ($drug) {
                    return '₦' . number_format($drug->unit_price, 2);
                })
                ->rawColumns(['action', 'stock_status']);
                
            \Log::info('Returning DataTables response');
            return $dataTable->make(true);
        }

        $dosageForms = Drug::distinct()->pluck('dosage_form')->filter()->sort();
        
        // Get stock statistics

// LOW STOCK: Drugs with total approved quantity between 1 and 50
$lowStockCount = Drug::whereIn('id', function($query) {
    $query->select('drug_id')
          ->from('drug_stocks')
          ->where('status', DrugStock::STATUS_APPROVED)
          ->whereNull('deleted_at')
          ->groupBy('drug_id')
          ->havingRaw('SUM(quantity_remaining) <= 50')
          ->havingRaw('SUM(quantity_remaining) > 0');
})->count();

// OUT OF STOCK: Drugs with no approved stock or zero quantity
$outOfStockCount = Drug::whereDoesntHave('stocks', function($q) {
    $q->where('status', DrugStock::STATUS_APPROVED)
      ->where('quantity_remaining', '>', 0);
})->count();

// NEAR EXPIRY: Count of distinct drugs with near-expiry stock
$nearExpiryCount = DrugStock::where('status', DrugStock::STATUS_APPROVED)
    ->where('quantity_remaining', '>', 0)
    ->where('expiry_date', '<=', now()->addDays(30))
    ->where('expiry_date', '>', now())
    ->distinct('drug_id')
    ->count('drug_id');
        
        $stats = [
            'total' => Drug::count(),
            'tablets' => Drug::where('dosage_form', 'Tablet')->count(),
            'capsules' => Drug::where('dosage_form', 'Capsule')->count(),
            'liquids' => Drug::where('dosage_form', 'LIKE', '%Liquid%')->count(),
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'near_expiry' => $nearExpiryCount,
        ];

        return view('admin.drugs.index', compact('dosageForms', 'stats'));
    }

    public function create()
    {
        $dosageForms = Drug::distinct()->pluck('dosage_form')->filter()->sort();
        $units = Drug::distinct()->pluck('unit')->filter()->sort();
        $strengths = Drug::distinct()->pluck('strength')->filter()->sort();
        
        if ($dosageForms->isEmpty()) {
            $dosageForms = collect(['Tablet', 'Capsule', 'Liquid', 'Injection', 'Syrup', 'Cream']);
        }
        
        if ($units->isEmpty()) {
            $units = collect(['Tablet', 'Capsule', 'Bottle', 'Vial', 'Tube', 'Box']);
        }
        
        return view('admin.drugs.create', compact('dosageForms', 'units', 'strengths'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dosage_form' => 'required|string|max:255',
            'strength' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
        ]);

        // Check for existing drug with same specifications
        $existingDrug = Drug::where('name', $request->name)
            ->where('dosage_form', $request->dosage_form)
            ->where('strength', $request->strength)
            ->where('unit', $request->unit)
            ->where('unit_price', $request->unit_price)
            ->first();

        if ($existingDrug) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A drug with the same name, dosage form, strength, unit, and price already exists.');
        }

        Drug::create($request->all());

        return redirect()->route('drugs.index')
                         ->with('success', 'Drug created successfully.');
    }

    public function show(Drug $drug)
    {
        return view('admin.drugs.show', compact('drug'));
    }

    public function edit(Drug $drug)
    {
        $dosageForms = Drug::distinct()->pluck('dosage_form')->filter()->sort();
        $units = Drug::distinct()->pluck('unit')->filter()->sort();
        $strengths = Drug::distinct()->pluck('strength')->filter()->sort();
        
        if ($dosageForms->isEmpty()) {
            $dosageForms = collect(['Tablet', 'Capsule', 'Liquid', 'Injection', 'Syrup', 'Cream']);
        }
        
        if ($units->isEmpty()) {
            $units = collect(['Tablet', 'Capsule', 'Bottle', 'Vial', 'Tube', 'Box']);
        }
        
        return view('admin.drugs.edit', compact('drug', 'dosageForms', 'units', 'strengths'));
    }

    public function update(Request $request, Drug $drug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dosage_form' => 'required|string|max:255',
            'strength' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0'
        ]);

        $drug->update($validated);

        return redirect()->route('drugs.index')
                         ->with('success', 'Drug updated successfully.');
    }

    public function destroy(Drug $drug)
    {
        $drug->delete();
        
        return redirect()->route('drugs.index')
                         ->with('success', 'Drug deleted successfully.');
    }

    public function bulkCreate()
    {
        return view('admin.drugs.bulk-create');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'drugs' => 'required|array',
            'drugs.*.name' => 'required|string|max:255',
            'drugs.*.dosage_form' => 'required|string|max:255',
            'drugs.*.strength' => 'required|string|max:255',
            'drugs.*.unit' => 'required|string|max:255',
            'drugs.*.unit_price' => 'required|numeric|min:0',
            'drugs.*.description' => 'nullable|string',
        ]);

        $drugsData = $request->drugs;
        $created = 0;
        $errors = [];

        foreach ($drugsData as $index => $drugData) {
            // Skip empty rows
            if (empty($drugData['name']) && empty($drugData['dosage_form']) && empty($drugData['strength'])) {
                continue;
            }
            
            try {
                // Check for existing drug with same specifications
                $existingDrug = Drug::where('name', $drugData['name'])
                    ->where('dosage_form', $drugData['dosage_form'])
                    ->where('strength', $drugData['strength'])
                    ->where('unit', $drugData['unit'])
                    ->where('unit_price', $drugData['unit_price'])
                    ->first();

                if ($existingDrug) {
                    $errors[] = "Row " . ($index + 1) . ": Drug with these specifications already exists";
                    continue;
                }

                $drug = Drug::create([
                    'name' => $drugData['name'],
                    'description' => $drugData['description'] ?? null,
                    'dosage_form' => $drugData['dosage_form'],
                    'strength' => $drugData['strength'],
                    'unit' => $drugData['unit'],
                    'unit_price' => $drugData['unit_price'],
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if ($created > 0) {
            $message = "Successfully created {$created} drugs.";
            if (!empty($errors)) {
                $message .= " Some rows had errors: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more errors.";
                }
            }
            return redirect()->route('drugs.index')->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'No drugs were created. Errors: ' . implode(', ', array_slice($errors, 0, 5)));
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'drug_ids' => 'required|array',
            'drug_ids.*' => 'exists:drugs,id',
        ]);

        $deleted = Drug::whereIn('id', $request->drug_ids)->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} drugs."
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new DrugsImport(null); // null for admin-level drugs
            Excel::import($import, $request->file('file'));
            
            $imported = $import->getImportedCount();
            $errors = $import->getErrors();
            
            $message = "Successfully imported {$imported} drugs.";
            if (!empty($errors)) {
                $message .= " Some rows had errors: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more errors.";
                }
            }
            
            return redirect()->route('drugs.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        // Build the same query as used in the index method
        $query = Drug::query();
        
        // Apply filters
        if ($request->has('dosage_form') && $request->dosage_form != '') {
            $query->where('dosage_form', $request->dosage_form);
        }
        
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('dosage_form', 'LIKE', "%{$request->search}%")
                  ->orWhere('strength', 'LIKE', "%{$request->search}%")
                  ->orWhere('unit', 'LIKE', "%{$request->search}%");
            });
        }
        
        // Create filename with filter info
        $filename = 'drugs_export';
        if ($request->has('dosage_form') && $request->dosage_form != '') {
            $filename .= '_' . strtolower($request->dosage_form);
        }
        if ($request->has('search') && $request->search != '') {
            $filename .= '_search_' . str_replace(' ', '_', $request->search);
        }
        $filename .= '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new DrugsExport($query), $filename);
    }

    public function downloadTemplate()
    {
        //dd('HHiii');
        \Log::info('DrugsController downloadTemplate called - DRUGS VERSION');
        
        // Use timestamp in filename to force fresh download
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "drugs_template_{$timestamp}.xlsx";
        $templatePath = public_path("templates/{$filename}");
        
        // Clean up old template files
        $oldFiles = glob(public_path('templates/drugs_*template*.xlsx'));
        foreach($oldFiles as $oldFile) {
            if(file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Always regenerate template to ensure latest format
        $this->createImportTemplate($templatePath);
        \Log::info('New template created with timestamp');
        
        return response()->download($templatePath, $filename);
    }

    private function createImportTemplate($filePath)
    {

        \Log::info('Creating drugs template - START');
        
        $templateDir = dirname($filePath);
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        \Log::info('Spreadsheet created');
        
        $headers = [
            'name', 'description', 'dosage_form', 'strength', 'unit', 'unit_price'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        \Log::info('Headers added to A1');
        
        // Add sample data manually - testing with direct cell assignment
        $sheet->setCellValue('A2', 'Paracetamol');
        $sheet->setCellValue('B2', 'Pain relief and fever reducer medication');
        $sheet->setCellValue('C2', 'Tablet');
        $sheet->setCellValue('D2', '500mg');
        $sheet->setCellValue('E2', 'Tablet');
        $sheet->setCellValue('F2', '2500.00');
        \Log::info('Row 2 (Paracetamol) added');
        
        $sheet->setCellValue('A3', 'Amoxicillin');
        $sheet->setCellValue('B3', 'Broad spectrum antibiotic for bacterial infections');
        $sheet->setCellValue('C3', 'Capsule');
        $sheet->setCellValue('D3', '250mg');
        $sheet->setCellValue('E3', 'Capsule');
        $sheet->setCellValue('F3', '4500.00');
        \Log::info('Row 3 (Amoxicillin) added');
        
        $sheet->setCellValue('A4', 'Ibuprofen');
        $sheet->setCellValue('B4', 'Anti-inflammatory and analgesic for pain relief');
        $sheet->setCellValue('C4', 'Tablet');
        $sheet->setCellValue('D4', '400mg');
        $sheet->setCellValue('E4', 'Tablet');
        $sheet->setCellValue('F4', '3200.00');
        \Log::info('Row 4 (Ibuprofen) added');
        
        // Set column widths for better readability
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);
        
        // Set strength column to text format to prevent Excel auto-formatting
        $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        
        // Add header styling
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Add data borders
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A2:F4')->applyFromArray($dataStyle); // 3 sample rows
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
        \Log::info('Template saved to: ' . $filePath);
    }

    public function toggleStatus(Drug $drug)
    {
        $drug->toggleStatus();
        
        return response()->json([
            'success' => true,
            'message' => 'Drug status updated successfully.',
            'new_status' => $drug->is_active ? 'active' : 'inactive'
        ]);
    }

    public function getStockDetails($drugId)
    {
        $drug = Drug::findOrFail($drugId);
        
        // Get stock by facility with batch details
        $stockByFacility = DrugStock::with(['facility', 'request'])
            ->where('drug_id', $drugId)
            ->where('status', DrugStock::STATUS_APPROVED)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->groupBy('facility_id')
            ->map(function ($stocks, $facilityId) {
                $facility = $stocks->first()->facility;
                $totalQuantity = $stocks->sum('quantity_remaining');
                $nearExpiry = $stocks->filter(function($stock) {
                    return $stock->expiry_date <= now()->addDays(30);
                })->sum('quantity_remaining');
                
                return [
                    'facility_name' => $facility->name ?? 'Unknown Facility',
                    'total_quantity' => $totalQuantity,
                    'near_expiry' => $nearExpiry,
                    'batches' => $stocks->map(function($stock) {
                        return [
                            'batch_number' => $stock->batch_number,
                            'quantity_remaining' => $stock->quantity_remaining,
                            'expiry_date' => $stock->expiry_date->format('Y-m-d'),
                            'days_until_expiry' => $stock->days_until_expiry,
                            'supplier' => $stock->supplier,
                            'unit_cost' => $stock->formatted_unit_cost,
                            'status_badge' => $stock->expiry_status['badge'],
                        ];
                    })->values(),
                ];
            })
            ->values();
        
        $totalStock = DrugStock::where('drug_id', $drugId)
            ->where('status', DrugStock::STATUS_APPROVED)
            ->sum('quantity_remaining');
        
        return response()->json([
            'success' => true,
            'drug' => [
                'name' => $drug->name,
                'dosage_form' => $drug->dosage_form,
                'strength' => $drug->strength,
                'unit' => $drug->unit,
            ],
            'total_stock' => $totalStock,
            'stock_by_facility' => $stockByFacility,
        ]);
    }
}
