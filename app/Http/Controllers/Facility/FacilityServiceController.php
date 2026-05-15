<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\FacilityService;
use App\Models\ServiceItem;
use App\Models\ServiceType;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class FacilityServiceController extends Controller
{
    /**
     * Check if current user has admin position
     */
    private function isAdmin(): bool
    {
        $user = Auth::guard('web')->user();
        $adminPositions = [
            'Hospital Administrator',
            'Admin', 
            'AAAA',
            'AAA',
            'AA'
        ];
        
        return $user && in_array($user->staffPosition->name ?? '', $adminPositions);
    }

    /**
     * Get current facility ID
     */
    private function getFacilityId()
    {
        return Auth::guard('web')->user()->facility_id;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage facility services.');
        }

        $facilityId = $this->getFacilityId();

        if ($request->ajax()) {
            $query = FacilityService::with(['serviceItem.serviceType.serviceCategory'])
                ->where('facility_id', $facilityId);

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

            return DataTables::of($query)
                ->addColumn('service_name', fn($row) => $row->serviceItem->name ?? 'N/A')
                ->addColumn('service_type', fn($row) => $row->serviceItem->serviceType->name ?? 'N/A')
                ->addColumn('category', fn($row) => $row->serviceItem->serviceType->serviceCategory->name ?? 'N/A')
                ->addColumn('price', fn($row) => '₦' . number_format($row->serviceItem->price ?? 0, 2))
                ->addColumn('availability', fn($row) => $row->getAvailabilityBadge())
                ->addColumn('action', function ($row) {
                    $toggleBtn = $row->is_available 
                        ? '<form action="' . route('facility.services.toggle', $row->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn btn-sm btn-warning" title="Mark Unavailable"><i class="fe fe-eye-off"></i></button>
                          </form>'
                        : '<form action="' . route('facility.services.toggle', $row->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn btn-sm btn-success" title="Mark Available"><i class="fe fe-eye"></i></button>
                          </form>';
                    $deleteBtn = '<form action="' . route('facility.services.destroy', $row->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Remove this service?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fe fe-trash"></i></button>
                    </form>';
                    return $toggleBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['availability', 'action'])
                ->make(true);
        }

        $categories = ServiceCategory::orderBy('name')->get();
        $stats = [
            'total' => FacilityService::where('facility_id', $facilityId)->count(),
            'available' => FacilityService::where('facility_id', $facilityId)->where('is_available', true)->count(),
        ];

        return view('facility.services.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating/assigning services.
     */
    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();
        $categories = ServiceCategory::with(['serviceTypes.serviceItems'])->orderBy('name')->get();
        
        // Get already assigned service item IDs
        $assignedServiceIds = FacilityService::where('facility_id', $facilityId)
            ->pluck('service_item_id')
            ->toArray();

        return view('facility.services.create', compact('categories', 'assignedServiceIds'));
    }

    /**
     * Store newly assigned services.
     */
    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();

        $request->validate([
            'service_items' => 'required|array|min:1',
            'service_items.*' => 'exists:service_items,id',
        ]);

        try {
            $created = 0;
            foreach ($request->service_items as $serviceItemId) {
                // Check if already exists
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

            return redirect()->route('facility.services.index')
                ->with('success', "{$created} service(s) added to facility successfully!");
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
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();
        $service = FacilityService::where('facility_id', $facilityId)->findOrFail($id);

        try {
            $service->update(['is_available' => !$service->is_available]);
            $status = $service->is_available ? 'available' : 'unavailable';
            return redirect()->route('facility.services.index')
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
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();
        $service = FacilityService::where('facility_id', $facilityId)->findOrFail($id);

        try {
            $service->delete();
            return redirect()->route('facility.services.index')
                ->with('success', 'Service removed from facility!');
        } catch (\Exception $e) {
            Log::error('Error removing facility service: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove service.');
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
}
