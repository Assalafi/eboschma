<?php

namespace App\Http\Controllers;

use App\Models\StaffPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class StaffPositionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StaffPosition::query();

            // DataTables sends search as array with 'value' key
            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($position) {
                    $actions = '<a href="' . route('staff-positions.edit', $position->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('staff-positions.destroy', $position->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->addColumn('created_at_formatted', function ($position) {
                    return $position->created_at->format('M d, Y');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $stats = [
            'total' => StaffPosition::count(),
        ];

        return view('admin.staff-positions.index', compact('stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.staff-positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staff_positions,name',
            'description' => 'nullable|string',
        ]);

        try {
            StaffPosition::create($request->all());
            
            return redirect()->route('staff-positions.index')
                ->with('success', 'Staff position created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating staff position: ' . $e->getMessage());
            return back()->with('error', 'Failed to create staff position. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for bulk creating resources.
     */
    public function bulkCreate(): View
    {
        return view('admin.staff-positions.bulk-create');
    }

    /**
     * Store bulk created resources in storage.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'positions' => 'required|array|min:1',
            'positions.*.name' => 'required|string|max:255',
            'positions.*.description' => 'nullable|string',
        ]);

        $created = 0;
        $duplicates = [];
        $errors = [];

        foreach ($request->positions as $positionData) {
            try {
                // Check for duplicates
                $exists = StaffPosition::where('name', $positionData['name'])->exists();
                
                if ($exists) {
                    $duplicates[] = $positionData['name'];
                    continue;
                }

                StaffPosition::create($positionData);
                $created++;
            } catch (\Exception $e) {
                $errors[] = $positionData['name'] . ': ' . $e->getMessage();
                Log::error('Error creating position in bulk: ' . $e->getMessage(), $positionData);
            }
        }

        $message = "Bulk creation completed. Created {$created} position(s).";
        
        if (!empty($duplicates)) {
            $message .= " " . count($duplicates) . " position(s) already existed: " . implode(', ', array_slice($duplicates, 0, 3));
            if (count($duplicates) > 3) {
                $message .= " and " . (count($duplicates) - 3) . " more.";
            }
        }
        
        if (!empty($errors)) {
            $message .= " Some rows had errors: " . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " and " . (count($errors) - 3) . " more errors.";
            }
        }

        return redirect()->route('staff-positions.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $position = StaffPosition::findOrFail($id);
        
        return view('admin.staff-positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $position = StaffPosition::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:staff_positions,name,' . $id,
            'description' => 'nullable|string',
        ]);

        try {
            $position->update($request->all());
            
            return redirect()->route('staff-positions.index')
                ->with('success', 'Staff position updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating staff position: ' . $e->getMessage());
            return back()->with('error', 'Failed to update staff position. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $position = StaffPosition::findOrFail($id);
            $position->delete();
            
            return redirect()->route('staff-positions.index')
                ->with('success', 'Staff position deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting staff position: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete staff position. Please try again.');
        }
    }
}
