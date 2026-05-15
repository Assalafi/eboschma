<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Ward;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Room::with(['ward.facility']);

            if ($request->has('ward_id') && $request->ward_id != '') {
                $query->where('ward_id', $request->ward_id);
            }

            if ($request->has('facility_id') && $request->facility_id != '') {
                $query->whereHas('ward', function ($q) use ($request) {
                    $q->where('facility_id', $request->facility_id);
                });
            }

            return DataTables::of($query)
                ->addColumn('ward_name', function ($room) {
                    return $room->ward ? $room->ward->name : 'N/A';
                })
                ->addColumn('facility_name', function ($room) {
                    return $room->ward && $room->ward->facility ? $room->ward->facility->name : 'N/A';
                })
                ->addColumn('beds_count', function ($room) {
                    return $room->beds()->count();
                })
                ->addColumn('available_beds', function ($room) {
                    return $room->beds()->where('is_occupied', false)->where('is_active', true)->count();
                })
                ->addColumn('status', function ($room) {
                    return $room->status_badge;
                })
                ->addColumn('action', function ($room) {
                    $actions = '<a href="' . route('rooms.edit', $room->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('rooms.destroy', $room->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure? This will delete all beds in this room.\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->orderBy('name')->get();
        $stats = [
            'total' => Room::count(),
            'active' => Room::where('is_active', true)->count(),
        ];

        return view('admin.rooms.index', compact('facilities', 'wards', 'stats'));
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        return view('admin.rooms.create', compact('facilities', 'wards'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'rooms' => 'required|array|min:1',
            'rooms.*.name' => 'required|string|max:255',
            'rooms.*.is_active' => 'nullable|boolean',
        ]);

        try {
            $created = 0;
            foreach ($request->rooms as $roomData) {
                Room::create([
                    'name' => $roomData['name'],
                    'ward_id' => $request->ward_id,
                    'is_active' => isset($roomData['is_active']) ? (bool)$roomData['is_active'] : true,
                ]);
                $created++;
            }

            Log::info('Bulk created ' . $created . ' rooms for ward: ' . $request->ward_id);

            return redirect()->route('rooms.index')
                ->with('success', $created . ' room(s) created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating rooms: ' . $e->getMessage());
            return back()->with('error', 'Failed to create rooms. Please try again.')
                ->withInput();
        }
    }

    public function edit(string $id): View
    {
        $room = Room::with('ward')->findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        return view('admin.rooms.edit', compact('room', 'facilities', 'wards'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'ward_id' => 'required|exists:wards,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $room->update([
                'name' => $request->name,
                'ward_id' => $request->ward_id,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Updated room: ' . $room->id);

            return redirect()->route('rooms.index')
                ->with('success', 'Room updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return back()->with('error', 'Failed to update room. Please try again.')
                ->withInput();
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();
            
            Log::info('Deleted room: ' . $id);
            
            return redirect()->route('rooms.index')
                ->with('success', 'Room deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting room: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete room. Please try again.');
        }
    }

    /**
     * Get rooms by ward (AJAX)
     */
    public function getByWard(Request $request): JsonResponse
    {
        $rooms = Room::where('ward_id', $request->ward_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rooms);
    }
}
