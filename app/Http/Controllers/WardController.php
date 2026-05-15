<?php

namespace App\Http\Controllers;

use App\Models\Ward;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class WardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Ward::with(['facility']);

            if ($request->has('facility_id') && $request->facility_id != '') {
                $query->where('facility_id', $request->facility_id);
            }

            return DataTables::of($query)
                ->addColumn('facility_name', function ($ward) {
                    return $ward->facility ? $ward->facility->name : 'N/A';
                })
                ->addColumn('rooms_count', function ($ward) {
                    return $ward->rooms()->count();
                })
                ->addColumn('beds_count', function ($ward) {
                    return $ward->rooms()->withCount('beds')->get()->sum('beds_count');
                })
                ->addColumn('status', function ($ward) {
                    return $ward->status_badge;
                })
                ->addColumn('action', function ($ward) {
                    $actions = '<a href="' . route('wards.edit', $ward->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('wards.destroy', $ward->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure? This will delete all rooms and beds in this ward.\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $facilities = Facility::orderBy('name')->get();
        $stats = [
            'total' => Ward::count(),
            'active' => Ward::where('is_active', true)->count(),
        ];

        return view('admin.wards.index', compact('facilities', 'stats'));
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.wards.create', compact('facilities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'wards' => 'required|array|min:1',
            'wards.*.name' => 'required|string|max:255',
            'wards.*.is_active' => 'nullable|boolean',
        ]);

        try {
            $created = 0;
            foreach ($request->wards as $wardData) {
                Ward::create([
                    'name' => $wardData['name'],
                    'facility_id' => $request->facility_id,
                    'is_active' => isset($wardData['is_active']) ? (bool)$wardData['is_active'] : true,
                ]);
                $created++;
            }

            Log::info('Bulk created ' . $created . ' wards for facility: ' . $request->facility_id);

            return redirect()->route('wards.index')
                ->with('success', $created . ' ward(s) created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating wards: ' . $e->getMessage());
            return back()->with('error', 'Failed to create wards. Please try again.')
                ->withInput();
        }
    }

    public function edit(string $id): View
    {
        $ward = Ward::findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        return view('admin.wards.edit', compact('ward', 'facilities'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $ward = Ward::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'facility_id' => 'required|exists:facilities,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $ward->update([
                'name' => $request->name,
                'facility_id' => $request->facility_id,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Updated ward: ' . $ward->id);

            return redirect()->route('wards.index')
                ->with('success', 'Ward updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating ward: ' . $e->getMessage());
            return back()->with('error', 'Failed to update ward. Please try again.')
                ->withInput();
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $ward = Ward::findOrFail($id);
            $ward->delete();
            
            Log::info('Deleted ward: ' . $id);
            
            return redirect()->route('wards.index')
                ->with('success', 'Ward deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting ward: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete ward. Please try again.');
        }
    }

    /**
     * Get wards by facility (AJAX)
     */
    public function getByFacility(Request $request): JsonResponse
    {
        $wards = Ward::where('facility_id', $request->facility_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($wards);
    }
}
