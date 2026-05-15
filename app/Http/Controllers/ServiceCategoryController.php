<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ServiceCategory::query()->withCount('serviceTypes', 'serviceItems');

            // DataTables sends search as array with 'value' key
            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('service_types_count', function ($category) {
                    return $category->service_types_count;
                })
                ->addColumn('service_items_count', function ($category) {
                    return $category->service_items_count;
                })
                ->addColumn('action', function ($category) {
                    $actions = '<a href="' . route('service-categories.show', $category->id) . '" class="btn btn-sm btn-info me-1" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>';
                    
                    $actions .= '<a href="' . route('service-categories.edit', $category->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('service-categories.destroy', $category->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this service category?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.service-categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.service-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name',
        ]);

        try {
            $category = ServiceCategory::create([
                'name' => $request->name,
            ]);

            Log::info('Service category created', ['id' => $category->id, 'name' => $category->name]);

            return redirect()
                ->route('service-categories.index')
                ->with('success', 'Service category created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating service category', ['error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating service category. Please try again.');
        }
    }

    /**
     * Display the specified resource with its service types.
     */
    public function show(string $id): View
    {
        $category = ServiceCategory::withCount(['serviceTypes', 'serviceItems'])
            ->findOrFail($id);
        $serviceTypes = $category->serviceTypes()
            ->withCount('serviceItems')
            ->orderBy('name')
            ->get();
        
        return view('admin.service-categories.show', compact('category', 'serviceTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $category = ServiceCategory::findOrFail($id);
        return view('admin.service-categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $category = ServiceCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name,' . $category->id,
        ]);

        try {
            $category->update([
                'name' => $request->name,
            ]);

            Log::info('Service category updated', ['id' => $category->id, 'name' => $category->name]);

            return redirect()
                ->route('service-categories.index')
                ->with('success', 'Service category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating service category', ['id' => $category->id, 'error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating service category. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $category = ServiceCategory::findOrFail($id);

        try {
            // Check if category has service types
            if ($category->serviceTypes()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete service category. It has associated service types.');
            }

            $category->delete();

            Log::info('Service category deleted', ['id' => $category->id, 'name' => $category->name]);

            return redirect()
                ->route('service-categories.index')
                ->with('success', 'Service category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting service category', ['id' => $category->id, 'error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with('error', 'Error deleting service category. Please try again.');
        }
    }
}
