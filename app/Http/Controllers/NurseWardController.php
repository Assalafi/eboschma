<?php

namespace App\Http\Controllers;

use App\Models\NurseWard;
use App\Models\Ward;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class NurseWardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NurseWard::with(['nurse', 'ward.facility']);

            if ($request->has('ward_id') && $request->ward_id != '') {
                $query->where('ward_id', $request->ward_id);
            }

            if ($request->has('facility_id') && $request->facility_id != '') {
                $query->whereHas('ward', function ($q) use ($request) {
                    $q->where('facility_id', $request->facility_id);
                });
            }

            return DataTables::of($query)
                ->addColumn('nurse_name', function ($assignment) {
                    return $assignment->nurse ? $assignment->nurse->name : 'N/A';
                })
                ->addColumn('nurse_email', function ($assignment) {
                    return $assignment->nurse ? $assignment->nurse->email : 'N/A';
                })
                ->addColumn('ward_name', function ($assignment) {
                    return $assignment->ward ? $assignment->ward->name : 'N/A';
                })
                ->addColumn('facility_name', function ($assignment) {
                    return $assignment->ward && $assignment->ward->facility 
                        ? $assignment->ward->facility->name : 'N/A';
                })
                ->addColumn('assigned_date_formatted', function ($assignment) {
                    return $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'N/A';
                })
                ->addColumn('status', function ($assignment) {
                    return $assignment->status_badge;
                })
                ->addColumn('action', function ($assignment) {
                    $actions = '<a href="' . route('nurse-ward.edit', $assignment->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('nurse-ward.destroy', $assignment->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
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
            'total' => NurseWard::count(),
            'active' => NurseWard::where('is_active', true)->count(),
        ];

        return view('admin.nurse-ward.index', compact('facilities', 'wards', 'stats'));
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        
        // Get users who are nurses (you may need to adjust this based on your role system)
        $nurses = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['nurse', 'Nurse', 'NURSE']);
        })->orWhereHas('staffPosition', function ($q) {
            $q->whereIn('name', ['Nurse', 'nurse', 'NURSE', 'Head Nurse', 'Staff Nurse']);
        })->orderBy('name')->get();

        // If no nurses found with specific roles, get all users
        if ($nurses->isEmpty()) {
            $nurses = User::orderBy('name')->get();
        }

        return view('admin.nurse-ward.create', compact('facilities', 'wards', 'nurses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.assigned_date' => 'required|date',
            'assignments.*.is_active' => 'nullable|boolean',
        ]);

        try {
            $created = 0;
            foreach ($request->assignments as $assignmentData) {
                // Check if assignment already exists
                $exists = NurseWard::where('user_id', $assignmentData['user_id'])
                    ->where('ward_id', $request->ward_id)
                    ->where('is_active', true)
                    ->exists();

                if (!$exists) {
                    NurseWard::create([
                        'user_id' => $assignmentData['user_id'],
                        'ward_id' => $request->ward_id,
                        'assigned_date' => $assignmentData['assigned_date'],
                        'is_active' => isset($assignmentData['is_active']) ? (bool)$assignmentData['is_active'] : true,
                    ]);
                    $created++;
                }
            }

            Log::info('Bulk created ' . $created . ' nurse assignments for ward: ' . $request->ward_id);

            return redirect()->route('nurse-ward.index')
                ->with('success', $created . ' nurse assignment(s) created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating nurse assignments: ' . $e->getMessage());
            return back()->with('error', 'Failed to create nurse assignments. Please try again.')
                ->withInput();
        }
    }

    public function edit(string $id): View
    {
        $assignment = NurseWard::with(['nurse', 'ward.facility'])->findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        $nurses = User::orderBy('name')->get();
        
        return view('admin.nurse-ward.edit', compact('assignment', 'facilities', 'wards', 'nurses'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $assignment = NurseWard::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ward_id' => 'required|exists:wards,id',
            'assigned_date' => 'required|date',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $assignment->update([
                'user_id' => $request->user_id,
                'ward_id' => $request->ward_id,
                'assigned_date' => $request->assigned_date,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Updated nurse assignment: ' . $assignment->id);

            return redirect()->route('nurse-ward.index')
                ->with('success', 'Nurse assignment updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating nurse assignment: ' . $e->getMessage());
            return back()->with('error', 'Failed to update nurse assignment. Please try again.')
                ->withInput();
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $assignment = NurseWard::findOrFail($id);
            $assignment->delete();
            
            Log::info('Deleted nurse assignment: ' . $id);
            
            return redirect()->route('nurse-ward.index')
                ->with('success', 'Nurse assignment deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting nurse assignment: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete nurse assignment. Please try again.');
        }
    }

    /**
     * Get nurses by facility (AJAX)
     */
    public function getNursesByFacility(Request $request): JsonResponse
    {
        $nurses = User::where('facility_id', $request->facility_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($nurses);
    }
}
