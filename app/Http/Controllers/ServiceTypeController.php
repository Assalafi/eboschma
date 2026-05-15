<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ServiceType::query()
                ->select('service_types.*')
                ->with('serviceCategory')
                ->withCount('serviceItems');

            // Apply category filter
            if ($request->filled('category_id')) {
                $query->where('service_category_id', $request->category_id);
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    $searchValue = $request->input('search.value');
                    if (!empty($searchValue)) {
                        $query->where(function($q) use ($searchValue) {
                            $q->where('service_types.name', 'LIKE', "%{$searchValue}%")
                              ->orWhereHas('serviceCategory', function($subQ) use ($searchValue) {
                                  $subQ->where('service_categories.name', 'LIKE', "%{$searchValue}%");
                              });
                        });
                    }
                })
                ->addColumn('service_category_name', function ($type) {
                    return $type->serviceCategory ? $type->serviceCategory->name : 'N/A';
                })
                ->addColumn('service_items_count', function ($type) {
                    return $type->service_items_count;
                })
                ->addColumn('action', function ($type) {
                    $actions = '<a href="' . route('service-types.show', $type->id) . '" class="btn btn-sm btn-info me-1" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>';
                    
                    $actions .= '<a href="' . route('service-types.edit', $type->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('service-types.destroy', $type->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this service type?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $categories = ServiceCategory::orderBy('name')->get();
        return view('admin.service-types.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = ServiceCategory::orderBy('name')->get();
        return view('admin.service-types.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $type = ServiceType::create([
                'service_category_id' => $request->service_category_id,
                'name' => $request->name,
            ]);

            Log::info('Service type created', ['id' => $type->id, 'name' => $type->name]);

            return redirect()
                ->route('service-types.index')
                ->with('success', 'Service type created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating service type', ['error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating service type. Please try again.');
        }
    }

    /**
     * Display the specified resource with its service items.
     */
    public function show(string $id): View
    {
        $type = ServiceType::with('serviceCategory')
            ->withCount('serviceItems')
            ->findOrFail($id);
        $serviceItems = $type->serviceItems()
            ->orderBy('name')
            ->get();
        
        return view('admin.service-types.show', compact('type', 'serviceItems'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $type = ServiceType::findOrFail($id);
        $categories = ServiceCategory::orderBy('name')->get();
        return view('admin.service-types.edit', compact('type', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $type = ServiceType::findOrFail($id);

        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $type->update([
                'service_category_id' => $request->service_category_id,
                'name' => $request->name,
            ]);

            Log::info('Service type updated', ['id' => $type->id, 'name' => $type->name]);

            return redirect()
                ->route('service-types.index')
                ->with('success', 'Service type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating service type', ['id' => $type->id, 'error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating service type. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $type = ServiceType::findOrFail($id);

        try {
            // Check if type has service items
            if ($type->serviceItems()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete service type. It has associated service items.');
            }

            $type->delete();

            Log::info('Service type deleted', ['id' => $type->id, 'name' => $type->name]);

            return redirect()
                ->route('service-types.index')
                ->with('success', 'Service type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting service type', ['id' => $type->id, 'error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with('error', 'Error deleting service type. Please try again.');
        }
    }

    /**
     * Get service types by category (AJAX).
     */
    public function getServiceTypesByCategory(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        
        if (!$categoryId) {
            return response()->json([]);
        }

        $types = ServiceType::where('service_category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($types);
    }
}
