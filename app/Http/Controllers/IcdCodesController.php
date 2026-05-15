<?php

namespace App\Http\Controllers;

use App\Models\IcdCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IcdCodesExport;
use App\Imports\IcdCodesImport;

class IcdCodesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = IcdCode::query();

            // Apply filters
            if ($request->has('category') && $request->category != '') {
                $query->where('category', $request->category);
            }

            // DataTables sends search as array with 'value' key
            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('code', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%")
                      ->orWhere('category', 'LIKE', "%{$searchValue}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('category_badge', function ($icdCode) {
                    return $icdCode->category_badge;
                })
                ->addColumn('action', function ($icdCode) {
                    $actions = '<a href="' . route('icd-codes.edit', $icdCode->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('icd-codes.destroy', $icdCode->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->addColumn('created_at_formatted', function ($icdCode) {
                    return $icdCode->created_at->format('M d, Y');
                })
                ->rawColumns(['category_badge', 'action'])
                ->make(true);
        }

        $categories = IcdCode::getCategories();
        $stats = [
            'total' => IcdCode::count(),
            'categories' => IcdCode::distinct('category')->count(),
        ];

        return view('admin.icd-codes.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = IcdCode::getCategories();
        
        return view('admin.icd-codes.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:icd_codes,code',
            'description' => 'required|string|max:500',
            'category' => 'required|string|in:' . implode(',', array_map(function($item) {
                return '"' . str_replace('"', '""', $item) . '"';
            }, array_merge(array_keys(IcdCode::getCategories()), array_values(IcdCode::getCategories())))),
        ]);

        try {
            // Convert old code ranges to descriptions for backward compatibility
            $data = $request->all();
            $data['category'] = $this->convertCategoryToDescription($data['category']);
            
            IcdCode::create($data);
            
            return redirect()->route('icd-codes.index')
                ->with('success', 'ICD code created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating ICD code: ' . $e->getMessage());
            return back()->with('error', 'Failed to create ICD code. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for bulk creating new resources.
     */
    public function bulkCreate(): View
    {
        $categories = IcdCode::getCategories();
        
        return view('admin.icd-codes.bulk-create', compact('categories'));
    }

    /**
     * Store bulk created resources in storage.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $codes = $request->input('codes', []);
        
        if (empty($codes)) {
            return back()->with('error', 'No ICD codes provided.');
        }

        try {
            $createdCount = 0;
            foreach ($codes as $codeData) {
                if (!empty($codeData['code']) && !empty($codeData['description'])) {
                    IcdCode::create([
                        'code' => $codeData['code'],
                        'description' => $codeData['description'],
                        'category' => $codeData['category'],
                    ]);
                    $createdCount++;
                }
            }
            
            return redirect()->route('icd-codes.index')
                ->with('success', "{$createdCount} ICD codes created successfully!");
        } catch (\Exception $e) {
            Log::error('Error bulk creating ICD codes: ' . $e->getMessage());
            return back()->with('error', 'Failed to create ICD codes. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $icdCode = IcdCode::findOrFail($id);
        $categories = IcdCode::getCategories();
        
        return view('admin.icd-codes.edit', compact('icdCode', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $icdCode = IcdCode::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:10|unique:icd_codes,code,' . $id,
            'description' => 'required|string|max:500',
            'category' => 'required|string|in:' . implode(',', array_map(function($item) {
                return '"' . str_replace('"', '""', $item) . '"';
            }, array_merge(array_keys(IcdCode::getCategories()), array_values(IcdCode::getCategories())))),
        ]);

        try {
            // Convert old code ranges to descriptions for backward compatibility
            $data = $request->all();
            $data['category'] = $this->convertCategoryToDescription($data['category']);
            
            $icdCode->update($data);
            
            return redirect()->route('icd-codes.index')
                ->with('success', 'ICD code updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating ICD code: ' . $e->getMessage());
            return back()->with('error', 'Failed to update ICD code. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $icdCode = IcdCode::findOrFail($id);
            $icdCode->delete();
            
            return redirect()->route('icd-codes.index')
                ->with('success', 'ICD code deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting ICD code: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete ICD code. Please try again.');
        }
    }

    /**
     * Show the upload form.
     */
    public function upload(): View
    {
        return view('admin.icd-codes.upload');
    }

    /**
     * Process the uploaded file.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            
            Excel::import(new IcdCodesImport, $file);
            
            return redirect()->route('icd-codes.index')
                ->with('success', 'ICD codes imported successfully!');
        } catch (\Exception $e) {
            Log::error('Error importing ICD codes: ' . $e->getMessage());
            return back()->with('error', 'Failed to import ICD codes: ' . $e->getMessage());
        }
    }

    /**
     * Export ICD codes to Excel.
     */
    public function export(): JsonResponse
    {
        try {
            $filename = 'icd_codes_' . date('Y_m_d_His') . '.xlsx';
            
            Excel::store(new IcdCodesExport, $filename, 'public');
            
            return response()->json([
                'success' => true,
                'file_url' => asset('storage/' . $filename),
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting ICD codes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export ICD codes.',
            ], 500);
        }
    }

    /**
     * Download sample import template.
     */
    public function downloadTemplate(): RedirectResponse
    {
        $filename = 'icd_codes_template.xlsx';
        
        try {
            Excel::store(new \App\Exports\IcdCodesTemplateExport, $filename, 'public');
            
            return redirect(asset('storage/' . $filename));
        } catch (\Exception $e) {
            Log::error('Error downloading template: ' . $e->getMessage());
            return back()->with('error', 'Failed to download template.');
        }
    }

    /**
     * Convert category code range to description for backward compatibility
     */
    private function convertCategoryToDescription($category)
    {
        $categories = IcdCode::getCategories();
        
        // If it's already a description, return as is
        if (in_array($category, array_values($categories))) {
            return $category;
        }
        
        // If it's a code range, convert to description
        if (isset($categories[$category])) {
            return $categories[$category];
        }
        
        // Fallback: return original value
        return $category;
    }
}
