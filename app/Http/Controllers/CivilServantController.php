<?php

namespace App\Http\Controllers;

use App\Models\CivilServant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CivilServantsImport;
use App\Exports\CivilServantsTemplateExport;

class CivilServantController extends Controller
{
    /**
     * Display a listing of civil servants.
     */
    public function index(Request $request)
    {
        $query = CivilServant::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'LIKE', "%{$search}%")
                  ->orWhere('dp_no', 'LIKE', "%{$search}%")
                  ->orWhere('nin', 'LIKE', "%{$search}%")
                  ->orWhere('mda', 'LIKE', "%{$search}%");
            });
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // State filter
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // MDA filter
        if ($request->filled('mda')) {
            $query->where('mda', 'LIKE', "%{$request->mda}%");
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('dob', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('dob', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $civilServants = $query->paginate(10)->appends($request->query());

        // Get unique states and MDAs for filter dropdowns
        $states = CivilServant::distinct()->whereNotNull('state')->pluck('state')->sort();
        $mdas = CivilServant::distinct()->pluck('mda')->sort();

        return view('admin.civil-servants.index', [
            'civilServants' => $civilServants,
            'states' => $states,
            'mdas' => $mdas,
            'filters' => $request->only(['search', 'gender', 'state', 'mda', 'date_from', 'date_to', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new civil servant.
     */
    public function create()
    {
        return view('admin.civil-servants.create');
    }

    /**
     * Store a newly created civil servant in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dp_no' => 'required|string|unique:civil_servants',
            'nin' => 'nullable|string|max:11',
            'bvn' => 'nullable|string|max:11', 
            'fullname' => 'required|string|max:255',
            'dob' => 'required|date',
            'state' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'mda' => 'required|string|max:255',
        ]);

        CivilServant::create($validated);

        return redirect()->route('civil-servants.index')
            ->with('success', 'Civil servant created successfully.');
    }

    /**
     * Display the specified civil servant.
     */
    public function show(CivilServant $civilServant)
    {
        return view('admin.civil-servants.show', [
            'civilServant' => $civilServant,
        ]);
    }

    /**
     * Show the form for editing the specified civil servant.
     */
    public function edit(CivilServant $civilServant)
    {
        return view('admin.civil-servants.edit', [
            'civilServant' => $civilServant,
        ]);
    }

    /**
     * Update the specified civil servant in storage.
     */
    public function update(Request $request, CivilServant $civilServant)
    {
        $validated = $request->validate([
            'dp_no' => 'required|string|unique:civil_servants,dp_no,' . $civilServant->id,
            'nin' => 'nullable|string|max:11',
            'bvn' => 'nullable|string|max:11',
            'fullname' => 'required|string|max:255',
            'dob' => 'required|date',
            'state' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'mda' => 'required|string|max:255',
        ]);

        $civilServant->update($validated);

        return redirect()->route('civil-servants.index')
            ->with('success', 'Civil servant updated successfully.');
    }

    /**
     * Remove the specified civil servant from storage.
     */
    public function destroy(CivilServant $civilServant)
    {
        try {
            $name = $civilServant->fullname;
            $civilServant->delete();

            return redirect()->route('civil-servants.index')
                ->with('success', "Civil servant '{$name}' deleted successfully.");
                
        } catch (\Exception $e) {
            Log::error('Single delete failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete civil servant: ' . $e->getMessage());
        }
    }

    /**
     * Show Excel upload form.
     */
    public function uploadForm()
    {
        return view('admin.civil-servants.upload');
    }

    /**
     * Handle Excel file upload and import.
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            Excel::import(new CivilServantsImport, $request->file('excel_file'));
            
            return redirect()->route('civil-servants.index')
                ->with('success', 'Civil servants imported successfully from Excel.');
                
        } catch (\Exception $e) {
            Log::error('Excel import failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to import Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for civil servants import.
     */
    public function downloadTemplate()
    {
        return Excel::download(new CivilServantsTemplateExport, 'civil_servants_template.xlsx');
    }

    /**
     * Bulk delete selected civil servants.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'exists:civil_servants,id',
        ]);

        try {
            $count = CivilServant::whereIn('id', $request->selected_ids)->count();
            CivilServant::whereIn('id', $request->selected_ids)->delete();
            
            return redirect()->route('civil-servants.index')
                ->with('success', "Successfully deleted {$count} civil servant(s).");
                
        } catch (\Exception $e) {
            Log::error('Bulk delete failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete selected civil servants: ' . $e->getMessage());
        }
    }
}
