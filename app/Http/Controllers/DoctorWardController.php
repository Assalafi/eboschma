<?php

namespace App\Http\Controllers;

use App\Models\DoctorWard;
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

class DoctorWardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DoctorWard::with(['doctor', 'ward.facility']);

            if ($request->has('ward_id') && $request->ward_id != '') {
                $query->where('ward_id', $request->ward_id);
            }

            if ($request->has('facility_id') && $request->facility_id != '') {
                $query->whereHas('ward', function ($q) use ($request) {
                    $q->where('facility_id', $request->facility_id);
                });
            }

            return DataTables::of($query)
                ->addColumn('doctor_name', function ($assignment) {
                    return $assignment->doctor ? $assignment->doctor->name : 'N/A';
                })
                ->addColumn('doctor_email', function ($assignment) {
                    return $assignment->doctor ? $assignment->doctor->email : 'N/A';
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
                    $actions = '<a href="' . route('doctor-ward.edit', $assignment->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('doctor-ward.destroy', $assignment->id) . '" method="POST" style="display: inline;">
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
            'total' => DoctorWard::count(),
            'active' => DoctorWard::where('is_active', true)->count(),
        ];

        return view('admin.doctor-ward.index', compact('facilities', 'wards', 'stats'));
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        
        // Get users who are doctors
        $doctors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['doctor', 'Doctor', 'DOCTOR']);
        })->orderBy('name')->get();

        // If no doctors found with specific roles, get all users
        if ($doctors->isEmpty()) {
            $doctors = User::orderBy('name')->get();
        }

        return view('admin.doctor-ward.create', compact('facilities', 'wards', 'doctors'));
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
                $exists = DoctorWard::where('user_id', $assignmentData['user_id'])
                    ->where('ward_id', $request->ward_id)
                    ->where('is_active', true)
                    ->exists();

                if (!$exists) {
                    DoctorWard::create([
                        'user_id' => $assignmentData['user_id'],
                        'ward_id' => $request->ward_id,
                        'assigned_date' => $assignmentData['assigned_date'],
                        'is_active' => isset($assignmentData['is_active']) ? (bool)$assignmentData['is_active'] : true,
                    ]);
                    $created++;
                }
            }

            Log::info('Bulk created ' . $created . ' doctor assignments for ward: ' . $request->ward_id);

            return redirect()->route('doctor-ward.index')
                ->with('success', $created . ' doctor assignment(s) created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating doctor assignments: ' . $e->getMessage());
            return back()->with('error', 'Failed to create doctor assignments. Please try again.')
                ->withInput();
        }
    }

    public function edit(string $id): View
    {
        $assignment = DoctorWard::with(['doctor', 'ward.facility'])->findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        $wards = Ward::with('facility')->where('is_active', true)->orderBy('name')->get();
        $doctors = User::orderBy('name')->get();
        
        return view('admin.doctor-ward.edit', compact('assignment', 'facilities', 'wards', 'doctors'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $assignment = DoctorWard::findOrFail($id);

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

            Log::info('Updated doctor assignment: ' . $assignment->id);

            return redirect()->route('doctor-ward.index')
                ->with('success', 'Doctor assignment updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating doctor assignment: ' . $e->getMessage());
            return back()->with('error', 'Failed to update doctor assignment. Please try again.')
                ->withInput();
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $assignment = DoctorWard::findOrFail($id);
            $assignment->delete();
            
            Log::info('Deleted doctor assignment: ' . $id);
            
            return redirect()->route('doctor-ward.index')
                ->with('success', 'Doctor assignment deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting doctor assignment: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete doctor assignment. Please try again.');
        }
    }

    /**
     * Get doctors by facility (AJAX)
     */
    public function getDoctorsByFacility(Request $request): JsonResponse
    {
        $doctors = User::where('facility_id', $request->facility_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($doctors);
    }
}
