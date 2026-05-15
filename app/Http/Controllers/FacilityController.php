<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacilitiesExport;
use App\Imports\FacilitiesImport;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Facility::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ward', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Filter by LGA
        if ($request->filled('lga')) {
            $query->byLga($request->get('lga'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->get('type'));
        }

        $facilities = $query->latest()->paginate(15)->appends($request->query());
        
        // Get filter options
        $lgas = Facility::getBornoLGAs();
        $types = Facility::getFacilityTypes();

        return view('admin.facilities.index', compact('facilities', 'lgas', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lgas = Facility::getBornoLGAs();
        $types = Facility::getFacilityTypes();
        $wards = Facility::getBornoWards();
        $secondaryServices = \App\Models\Service::where('type', 'Secondary')->orderBy('name')->get();
        
        return view('admin.facilities.create', compact('lgas', 'types', 'wards', 'secondaryServices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lga' => 'required|string|in:' . implode(',', Facility::getBornoLGAs()),
            'ward' => 'required|string|max:255',
            'type' => 'nullable|string|in:' . implode(',', Facility::getFacilityTypes()),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $facility = Facility::create($request->only(['name', 'lga', 'ward', 'type']));
            
            // Sync secondary services if provided
            if ($request->has('services')) {
                $facility->services()->sync($request->services);
            }
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create facility. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility)
    {
        return view('admin.facilities.show', compact('facility'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facility $facility)
    {
        $lgas = Facility::getBornoLGAs();
        $types = Facility::getFacilityTypes();
        $wards = Facility::getBornoWards();
        $secondaryServices = \App\Models\Service::where('type', 'Secondary')->orderBy('name')->get();
        
        return view('admin.facilities.edit', compact('facility', 'lgas', 'types', 'wards', 'secondaryServices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lga' => 'required|string|in:' . implode(',', Facility::getBornoLGAs()),
            'ward' => 'required|string|max:255',
            'type' => 'nullable|string|in:' . implode(',', Facility::getFacilityTypes()),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $facility->update($request->only(['name', 'lga', 'ward', 'type']));
            
            // Sync secondary services if provided
            if ($request->has('services')) {
                $facility->services()->sync($request->services);
            } else {
                // If no services selected, detach all
                $facility->services()->sync([]);
            }
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update facility. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        try {
            $facility->delete();
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete facility. Please try again.');
        }
    }

    /**
     * Show the upload form
     */
    public function uploadForm()
    {
        return view('admin.facilities.upload');
    }

    /**
     * Handle Excel upload
     */
    public function uploadExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $import = new FacilitiesImport();
            Excel::import($import, $request->file('excel_file'));
            
            $imported = $import->getImportedCount();
            $errors = $import->getErrors();
            
            if ($imported > 0) {
                $message = "Successfully imported {$imported} facilities.";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " rows had errors.";
                }
                return redirect()->route('facilities.index')->with('success', $message);
            } else {
                return back()->with('error', 'No facilities were imported. Please check your file format.');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing facilities: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $filename = 'facilities_import_template_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new FacilitiesExport(true), $filename, \Maatwebsite\Excel\Excel::XLSX, $headers);
    }

    /**
     * Bulk delete facilities
     */
    public function bulkDelete(Request $request)
    {
        // Simple test to check if route is working
        if ($request->has('test')) {
            $userName = Auth::check() ? Auth::user()->name : 'Not authenticated';
            return response()->json(['success' => true, 'message' => 'Route is working! User: ' . $userName]);
        }
        
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
            }
            return redirect()->route('login');
        }
        
        // Handle both JSON and form requests
        $data = $request->isJson() ? $request->json()->all() : $request->all();
        
        // Debug logging
        Log::info('Bulk delete request data', ['data' => $data]);
        Log::info('Request method', ['method' => $request->method()]);
        Log::info('Request headers', ['headers' => $request->headers->all()]);
        
        $validator = Validator::make($data, [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:facilities,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid facility IDs: ' . implode(', ', $validator->errors()->all())
                ], 400);
            }
            return back()->with('error', 'Invalid facility IDs selected.');
        }

        try {
            DB::beginTransaction();
            
            $ids = $data['ids'];
            $count = Facility::whereIn('id', $ids)->count();
            
            if ($count === 0) {
                DB::rollback();
                return response()->json(['success' => false, 'message' => 'No facilities found to delete.'], 404);
            }
            
            Facility::whereIn('id', $ids)->delete();
            
            DB::commit();
            
            $message = "Successfully deleted {$count} " . ($count === 1 ? 'facility' : 'facilities') . ".";
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted_count' => $count
                ]);
            }
            
            return redirect()->route('facilities.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $errorMessage = 'Failed to delete facilities. Please try again.';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }
}
