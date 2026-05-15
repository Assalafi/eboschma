<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaboratoryTestsExport;
use App\Imports\LaboratoryTestsImport;

class LaboratoryTestsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LaboratoryTest::query();

            // Apply filters
            if ($request->has('sample_type') && $request->sample_type != '') {
                $query->where('sample_type', $request->sample_type);
            }

            // DataTables sends search as array with 'value' key
            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%")
                      ->orWhere('sample_type', 'LIKE', "%{$searchValue}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('sample_type_badge', function ($test) {
                    return $test->sample_type_badge;
                })
                ->addColumn('formatted_price', function ($test) {
                    return $test->formatted_price;
                })
                ->addColumn('action', function ($test) {
                    $actions = '<a href="' . route('laboratory-tests.edit', $test->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('laboratory-tests.destroy', $test->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->addColumn('created_at_formatted', function ($test) {
                    return $test->created_at->format('M d, Y');
                })
                ->rawColumns(['sample_type_badge', 'action'])
                ->make(true);
        }

        $sampleTypes = LaboratoryTest::getSampleTypes();
        $stats = [
            'total' => LaboratoryTest::count(),
            'blood_tests' => LaboratoryTest::where('sample_type', 'Blood')->count(),
            'avg_price' => '₦' . number_format(LaboratoryTest::avg('price') ?? 0, 2),
        ];

        return view('admin.laboratory-tests.index', compact('sampleTypes', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $sampleTypes = LaboratoryTest::getSampleTypes();
        
        return view('admin.laboratory-tests.create', compact('sampleTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sample_type' => 'required|string|in:' . implode(',', array_keys(LaboratoryTest::getSampleTypes())),
            'price' => 'required|numeric|min:0',
        ]);

        try {
            LaboratoryTest::create($request->all());
            
            return redirect()->route('laboratory-tests.index')
                ->with('success', 'Laboratory test created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating laboratory test: ' . $e->getMessage());
            return back()->with('error', 'Failed to create laboratory test. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for bulk creating new resources.
     */
    public function bulkCreate(): View
    {
        $sampleTypes = LaboratoryTest::getSampleTypes();
        
        return view('admin.laboratory-tests.bulk-create', compact('sampleTypes'));
    }

    /**
     * Store bulk created resources in storage.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $tests = $request->input('tests', []);
        
        if (empty($tests)) {
            return back()->with('error', 'No laboratory tests provided.');
        }

        try {
            $createdCount = 0;
            foreach ($tests as $testData) {
                if (!empty($testData['name'])) {
                    LaboratoryTest::create([
                        'name' => $testData['name'],
                        'description' => $testData['description'] ?? null,
                        'sample_type' => $testData['sample_type'],
                        'price' => $testData['price'],
                    ]);
                    $createdCount++;
                }
            }
            
            return redirect()->route('laboratory-tests.index')
                ->with('success', "{$createdCount} laboratory tests created successfully!");
        } catch (\Exception $e) {
            Log::error('Error bulk creating laboratory tests: ' . $e->getMessage());
            return back()->with('error', 'Failed to create laboratory tests. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $test = LaboratoryTest::findOrFail($id);
        $sampleTypes = LaboratoryTest::getSampleTypes();
        
        return view('admin.laboratory-tests.edit', compact('test', 'sampleTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $test = LaboratoryTest::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sample_type' => 'required|string|in:' . implode(',', array_keys(LaboratoryTest::getSampleTypes())),
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $test->update($request->all());
            
            return redirect()->route('laboratory-tests.index')
                ->with('success', 'Laboratory test updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating laboratory test: ' . $e->getMessage());
            return back()->with('error', 'Failed to update laboratory test. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $test = LaboratoryTest::findOrFail($id);
            $test->delete();
            
            return redirect()->route('laboratory-tests.index')
                ->with('success', 'Laboratory test deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting laboratory test: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete laboratory test. Please try again.');
        }
    }

    /**
     * Show the upload form.
     */
    public function upload(): View
    {
        return view('admin.laboratory-tests.upload');
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
            
            Excel::import(new LaboratoryTestsImport, $file);
            
            return redirect()->route('laboratory-tests.index')
                ->with('success', 'Laboratory tests imported successfully!');
        } catch (\Exception $e) {
            Log::error('Error importing laboratory tests: ' . $e->getMessage());
            return back()->with('error', 'Failed to import laboratory tests: ' . $e->getMessage());
        }
    }

    /**
     * Export laboratory tests to Excel.
     */
    public function export(): JsonResponse
    {
        try {
            $filename = 'laboratory_tests_' . date('Y_m_d_His') . '.xlsx';
            
            Excel::store(new LaboratoryTestsExport, $filename, 'public');
            
            return response()->json([
                'success' => true,
                'file_url' => asset('storage/' . $filename),
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting laboratory tests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export laboratory tests.',
            ], 500);
        }
    }

    /**
     * Download sample import template.
     */
    public function downloadTemplate(): RedirectResponse
    {
        $filename = 'laboratory_tests_template.xlsx';
        
        try {
            Excel::store(new \App\Exports\LaboratoryTestsTemplateExport, $filename, 'public');
            
            return redirect(asset('storage/' . $filename));
        } catch (\Exception $e) {
            Log::error('Error downloading template: ' . $e->getMessage());
            return back()->with('error', 'Failed to download template.');
        }
    }
}
