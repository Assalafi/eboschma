<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityService;
use App\Models\ServiceItem;
use App\Models\ServiceType;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class FacilityServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = FacilityService::with(['facility', 'serviceItem.serviceType.serviceCategory']);

            if ($request->facility_id) {
                $query->where('facility_id', $request->facility_id);
            }

            if ($request->category_id) {
                $query->whereHas('serviceItem.serviceType', function($q) use ($request) {
                    $q->where('service_category_id', $request->category_id);
                });
            }

            if ($request->type_id) {
                $query->whereHas('serviceItem', function($q) use ($request) {
                    $q->where('service_type_id', $request->type_id);
                });
            }

            if ($request->item_type) {
                $query->whereHas('serviceItem', function($q) use ($request) {
                    $q->where('type', $request->item_type);
                });
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('facility_name', fn($row) => $row->facility->name ?? 'N/A')
                ->addColumn('service_name', fn($row) => $row->serviceItem->name ?? 'N/A')
                ->addColumn('item_type', fn($row) => $row->serviceItem->type_badge ?? 'N/A')
                ->addColumn('service_type', fn($row) => $row->serviceItem->serviceType->name ?? 'N/A')
                ->addColumn('category', fn($row) => $row->serviceItem->serviceType->serviceCategory->name ?? 'N/A')
                ->addColumn('price', fn($row) => '₦' . number_format($row->serviceItem->price ?? 0, 2))
                ->addColumn('availability', fn($row) => $row->getAvailabilityBadge())
                ->addColumn('action', function ($row) {
                    $toggleBtn = $row->is_available 
                        ? '<form action="' . route('facility-services.toggle', $row->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn btn-sm btn-warning" title="Mark Unavailable"><i class="fe fe-eye-off"></i></button>
                          </form>'
                        : '<form action="' . route('facility-services.toggle', $row->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn btn-sm btn-success" title="Mark Available"><i class="fe fe-eye"></i></button>
                          </form>';
                    $deleteBtn = '<form action="' . route('facility-services.destroy', $row->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Remove this service?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fe fe-trash"></i></button>
                    </form>';
                    return $toggleBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['checkbox', 'item_type', 'availability', 'action'])
                ->make(true);
        }

        $facilities = Facility::orderBy('name')->get();
        $categories = ServiceCategory::orderBy('name')->get();
        $serviceTypes = ServiceType::orderBy('name')->get();
        $stats = [
            'total' => FacilityService::count(),
            'available' => FacilityService::where('is_available', true)->count(),
            'facilities_count' => FacilityService::distinct('facility_id')->count('facility_id'),
        ];

        return view('admin.facility-services.index', compact('facilities', 'categories', 'serviceTypes', 'stats'));
    }

    /**
     * Show the form for creating/assigning services.
     */
    public function create()
    {
        $facilities = Facility::orderBy('name')->get();
        $categories = ServiceCategory::with(['serviceTypes.serviceItems'])->orderBy('name')->get();

        return view('admin.facility-services.create', compact('facilities', 'categories'));
    }

    /**
     * Store newly assigned services.
     */
    public function store(Request $request)
    {
        $request->validate([
            'facility_ids' => 'required|array|min:1',
            'facility_ids.*' => 'exists:facilities,id',
            'service_items' => 'required|array|min:1',
            'service_items.*' => 'exists:service_items,id',
        ]);

        try {
            $created = 0;
            foreach ($request->facility_ids as $facilityId) {
                foreach ($request->service_items as $serviceItemId) {
                    $exists = FacilityService::where('facility_id', $facilityId)
                        ->where('service_item_id', $serviceItemId)
                        ->exists();

                    if (!$exists) {
                        FacilityService::create([
                            'facility_id' => $facilityId,
                            'service_item_id' => $serviceItemId,
                            'is_available' => true,
                        ]);
                        $created++;
                    }
                }
            }

            $facilitiesCount = count($request->facility_ids);
            return redirect()->route('facility-services.index')
                ->with('success', "{$created} service assignment(s) created across {$facilitiesCount} facility(ies)!");
        } catch (\Exception $e) {
            Log::error('Error assigning facility services: ' . $e->getMessage());
            return back()->with('error', 'Failed to assign services. Please try again.');
        }
    }

    /**
     * Toggle service availability.
     */
    public function toggle(string $id)
    {
        $service = FacilityService::findOrFail($id);

        try {
            $service->update(['is_available' => !$service->is_available]);
            $status = $service->is_available ? 'available' : 'unavailable';
            return redirect()->route('facility-services.index')
                ->with('success', "Service marked as {$status}!");
        } catch (\Exception $e) {
            Log::error('Error toggling facility service: ' . $e->getMessage());
            return back()->with('error', 'Failed to update service.');
        }
    }

    /**
     * Remove service from facility.
     */
    public function destroy(string $id)
    {
        $service = FacilityService::findOrFail($id);

        try {
            $service->delete();
            return redirect()->route('facility-services.index')
                ->with('success', 'Service removed from facility!');
        } catch (\Exception $e) {
            Log::error('Error removing facility service: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove service.');
        }
    }

    /**
     * Bulk delete facility service assignments.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:facility_services,id',
        ]);

        try {
            $deleted = FacilityService::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deleted} service assignment(s)."
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting facility services: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete services. Please try again.'
            ], 500);
        }
    }

    /**
     * Get service types by category (AJAX).
     */
    public function getTypesByCategory(Request $request)
    {
        $types = ServiceType::where('service_category_id', $request->category_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($types);
    }

    /**
     * Get assigned service IDs for a facility (AJAX).
     */
    public function getAssignedServices(Request $request)
    {
        $assignedIds = FacilityService::where('facility_id', $request->facility_id)
            ->pluck('service_item_id')
            ->toArray();
        return response()->json($assignedIds);
    }
}
