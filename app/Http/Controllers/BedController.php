<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Ward;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class BedController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Bed::with(['room.ward.facility']);

            if ($request->has('room_id') && $request->room_id != '') {
                $query->where('room_id', $request->room_id);
            }

            if ($request->has('ward_id') && $request->ward_id != '') {
                $query->whereHas('room', function ($q) use ($request) {
                    $q->where('ward_id', $request->ward_id);
                });
            }

            if ($request->has('facility_id') && $request->facility_id != '') {
                $query->whereHas('room.ward', function ($q) use ($request) {
                    $q->where('facility_id', $request->facility_id);
                });
            }

            return DataTables::of($query)
                ->addColumn('room_name', function ($bed) {
                    return $bed->room ? $bed->room->name : 'N/A';
                })
                ->addColumn('ward_name', function ($bed) {
                    return $bed->room && $bed->room->ward ? $bed->room->ward->name : 'N/A';
                })
                ->addColumn('facility_name', function ($bed) {
                    return $bed->room && $bed->room->ward && $bed->room->ward->facility 
                        ? $bed->room->ward->facility->name : 'N/A';
                })
                ->addColumn('occupancy', function ($bed) {
                    return $bed->occupancy_badge;
                })
                ->addColumn('status', function ($bed) {
                    return $bed->status_badge;
                })
                ->addColumn('action', function ($bed) {
                    $actions = '<a href="' . route('beds.edit', $bed->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('beds.destroy', $bed->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->rawColumns(['occupancy', 'status', 'action'])
                ->make(true);
        }

        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->orderBy('name')->get();
        $rooms = Room::with('ward')->orderBy('name')->get();
        $stats = [
            'total' => Bed::count(),
            'available' => Bed::where('is_occupied', false)->where('is_active', true)->count(),
            'occupied' => Bed::where('is_occupied', true)->count(),
        ];

        return view('admin.beds.index', compact('facilities', 'wards', 'rooms', 'stats'));
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        $rooms = Room::with('ward')->where('is_active', true)->orderBy('name')->get();
        return view('admin.beds.create', compact('facilities', 'wards', 'rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'beds' => 'required|array|min:1',
            'beds.*.name' => 'required|string|max:255',
            'beds.*.is_active' => 'nullable|boolean',
        ]);

        try {
            $created = 0;
            foreach ($request->beds as $bedData) {
                Bed::create([
                    'name' => $bedData['name'],
                    'room_id' => $request->room_id,
                    'is_occupied' => false,
                    'is_active' => isset($bedData['is_active']) ? (bool)$bedData['is_active'] : true,
                ]);
                $created++;
            }

            Log::info('Bulk created ' . $created . ' beds for room: ' . $request->room_id);

            return redirect()->route('beds.index')
                ->with('success', $created . ' bed(s) created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating beds: ' . $e->getMessage());
            return back()->with('error', 'Failed to create beds. Please try again.')
                ->withInput();
        }
    }

    public function edit(string $id): View
    {
        $bed = Bed::with('room.ward.facility')->findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        $rooms = Room::with('ward')->where('is_active', true)->orderBy('name')->get();
        return view('admin.beds.edit', compact('bed', 'facilities', 'wards', 'rooms'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $bed = Bed::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'room_id' => 'required|exists:rooms,id',
            'is_occupied' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $bed->update([
                'name' => $request->name,
                'room_id' => $request->room_id,
                'is_occupied' => $request->has('is_occupied'),
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Updated bed: ' . $bed->id);

            return redirect()->route('beds.index')
                ->with('success', 'Bed updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating bed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update bed. Please try again.')
                ->withInput();
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $bed = Bed::findOrFail($id);
            $bed->delete();
            
            Log::info('Deleted bed: ' . $id);
            
            return redirect()->route('beds.index')
                ->with('success', 'Bed deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting bed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete bed. Please try again.');
        }
    }

    /**
     * Get beds by room (AJAX)
     */
    public function getByRoom(Request $request): JsonResponse
    {
        $beds = Bed::where('room_id', $request->room_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'is_occupied']);

        return response()->json($beds);
    }
}
