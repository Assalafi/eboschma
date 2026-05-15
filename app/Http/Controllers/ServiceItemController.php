<?php

namespace App\Http\Controllers;

use App\Models\ServiceItem;
use App\Models\ServiceType;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ServiceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ServiceItem::query()
                ->select('service_items.*')
                ->with(['serviceType.serviceCategory']);

            // Apply filters
            if ($request->filled('category_id')) {
                $query->whereHas('serviceType', function($q) use ($request) {
                    $q->where('service_category_id', $request->category_id);
                });
            }

            if ($request->filled('type_id')) {
                $query->where('service_type_id', $request->type_id);
            }

            if ($request->filled('item_type')) {
                $query->where('service_items.type', $request->item_type);
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    $searchValue = $request->input('search.value');
                    if (!empty($searchValue)) {
                        $query->where(function($q) use ($searchValue) {
                            $q->where('service_items.name', 'LIKE', "%{$searchValue}%")
                              ->orWhere('service_items.description', 'LIKE', "%{$searchValue}%")
                              ->orWhere('service_items.type', 'LIKE', "%{$searchValue}%")
                              ->orWhere('service_items.price', 'LIKE', "%{$searchValue}%")
                              ->orWhereHas('serviceType', function($subQ) use ($searchValue) {
                                  $subQ->where('service_types.name', 'LIKE', "%{$searchValue}%");
                              })
                              ->orWhereHas('serviceType.serviceCategory', function($subQ) use ($searchValue) {
                                  $subQ->where('service_categories.name', 'LIKE', "%{$searchValue}%");
                              });
                        });
                    }
                })
                ->addColumn('service_category_name', function ($item) {
                    return $item->serviceType && $item->serviceType->serviceCategory 
                        ? $item->serviceType->serviceCategory->name 
                        : 'N/A';
                })
                ->addColumn('service_type_name', function ($item) {
                    return $item->serviceType ? $item->serviceType->name : 'N/A';
                })
                ->addColumn('price_formatted', function ($item) {
                    return $item->price_with_currency;
                })
                ->addColumn('type_badge', function ($item) {
                    return $item->type_badge;
                })
                ->addColumn('action', function ($item) {
                    $actions = '<a href="' . route('service-items.edit', $item->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('service-items.destroy', $item->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this service item?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->orderColumn('service_category_name', function ($query, $order) {
                    $query->leftJoin('service_types as st', 'service_items.service_type_id', '=', 'st.id')
                          ->leftJoin('service_categories as sc', 'st.service_category_id', '=', 'sc.id')
                          ->orderBy('sc.name', $order);
                })
                ->orderColumn('service_type_name', function ($query, $order) {
                    $query->leftJoin('service_types as st2', 'service_items.service_type_id', '=', 'st2.id')
                          ->orderBy('st2.name', $order);
                })
                ->rawColumns(['type_badge', 'action'])
                ->make(true);
        }

        $categories = ServiceCategory::orderBy('name')->get();
        $types = ServiceType::orderBy('name')->get();
        $itemTypes = ServiceItem::getTypes();

        return view('admin.service-items.index', compact('categories', 'types', 'itemTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = ServiceCategory::orderBy('name')->get();
        $types = ServiceType::orderBy('name')->get();
        $serviceTypes = ServiceItem::getTypes();
        
        return view('admin.service-items.create', compact('categories', 'types', 'serviceTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:Primary,Secondary,Other',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $item = ServiceItem::create([
                'service_type_id' => $request->service_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'price' => $request->price,
            ]);

            Log::info('Service item created', ['id' => $item->id, 'name' => $item->name]);

            return redirect()
                ->route('service-items.index')
                ->with('success', 'Service item created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating service item', ['error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating service item. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $item = ServiceItem::with('serviceType.serviceCategory')->findOrFail($id);
        $categories = ServiceCategory::orderBy('name')->get();
        $types = ServiceType::orderBy('name')->get();
        $serviceTypes = ServiceItem::getTypes();
        
        return view('admin.service-items.edit', compact('item', 'categories', 'types', 'serviceTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $item = ServiceItem::findOrFail($id);

        $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:Primary,Secondary,Other',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $item->update([
                'service_type_id' => $request->service_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'price' => $request->price,
            ]);

            Log::info('Service item updated', ['id' => $item->id, 'name' => $item->name]);

            return redirect()
                ->route('service-items.index')
                ->with('success', 'Service item updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating service item', ['id' => $item->id, 'error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating service item. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $item = ServiceItem::findOrFail($id);

        try {
            // Check if item has service order items
            if ($item->serviceOrderItems()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete service item. It has associated service orders.');
            }

            $item->delete();

            Log::info('Service item deleted', ['id' => $item->id, 'name' => $item->name]);

            return redirect()
                ->route('service-items.index')
                ->with('success', 'Service item deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting service item', ['id' => $item->id, 'error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with('error', 'Error deleting service item. Please try again.');
        }
    }

    /**
     * Get service items by type (AJAX).
     */
    public function getServiceItemsByType(Request $request): JsonResponse
    {
        $typeId = $request->input('type_id');
        
        if (!$typeId) {
            return response()->json([]);
        }

        $items = ServiceItem::where('service_type_id', $typeId)
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        return response()->json($items);
    }
}
