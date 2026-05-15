<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\NurseWard;
use App\Models\Ward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class FacilityNurseWardController extends Controller
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
                ->with('error', 'Access denied. Only administrators can manage nurse ward assignments.');
        }

        $facilityId = $this->getFacilityId();

        if ($request->ajax()) {
            $query = NurseWard::with(['nurse', 'ward'])
                ->whereHas('ward', function($q) use ($facilityId) {
                    $q->where('facility_id', $facilityId);
                });

            if ($request->ward_id) {
                $query->where('ward_id', $request->ward_id);
            }

            return DataTables::of($query)
                ->addColumn('nurse_name', fn($row) => $row->nurse->name ?? 'N/A')
                ->addColumn('nurse_email', fn($row) => $row->nurse->email ?? 'N/A')
                ->addColumn('ward_name', fn($row) => $row->ward->name ?? 'N/A')
                ->addColumn('assigned_date_formatted', fn($row) => $row->assigned_date ? $row->assigned_date->format('M d, Y') : 'N/A')
                ->addColumn('status', fn($row) => $row->getStatusBadge())
                ->addColumn('action', function ($row) {
                    $editBtn = '<a href="' . route('facility.nurse-ward.edit', $row->id) . '" class="btn btn-sm btn-primary me-1"><i class="fe fe-edit"></i></a>';
                    $deleteBtn = '<form action="' . route('facility.nurse-ward.destroy', $row->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fe fe-trash"></i></button>
                    </form>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $wards = Ward::where('facility_id', $facilityId)->where('is_active', true)->get();
        $stats = [
            'total' => NurseWard::whereHas('ward', fn($q) => $q->where('facility_id', $facilityId))->count(),
            'active' => NurseWard::whereHas('ward', fn($q) => $q->where('facility_id', $facilityId))->where('is_active', true)->count(),
        ];

        return view('facility.nurse-ward.index', compact('wards', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied. Only administrators can manage nurse ward assignments.');
        }

        $facilityId = $this->getFacilityId();
        $wards = Ward::where('facility_id', $facilityId)->where('is_active', true)->get();
        
        // Get nurses from same facility (users with nurse role or position)
        $nurses = User::where('facility_id', $facilityId)
            ->whereHas('staffPosition', function($q) {
                $q->where('name', 'LIKE', '%Nurse%');
            })
            ->orWhere(function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->whereHas('roles', function($r) {
                      $r->where('name', 'LIKE', '%nurse%');
                  });
            })
            ->get();

        // If no nurses found with position/role, get all facility staff
        if ($nurses->isEmpty()) {
            $nurses = User::where('facility_id', $facilityId)->get();
        }

        return view('facility.nurse-ward.create', compact('wards', 'nurses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();

        $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.assigned_date' => 'required|date',
        ]);

        // Verify ward belongs to facility
        $ward = Ward::where('id', $request->ward_id)->where('facility_id', $facilityId)->first();
        if (!$ward) {
            return back()->with('error', 'Invalid ward selected.');
        }

        try {
            $created = 0;
            foreach ($request->assignments as $assignment) {
                // Verify nurse belongs to facility
                $nurse = User::where('id', $assignment['user_id'])->where('facility_id', $facilityId)->first();
                if (!$nurse) continue;

                // Check if assignment already exists
                $exists = NurseWard::where('user_id', $assignment['user_id'])
                    ->where('ward_id', $request->ward_id)
                    ->where('is_active', true)
                    ->exists();

                if (!$exists) {
                    NurseWard::create([
                        'id' => Str::uuid(),
                        'user_id' => $assignment['user_id'],
                        'ward_id' => $request->ward_id,
                        'assigned_date' => $assignment['assigned_date'],
                        'is_active' => isset($assignment['is_active']) ? true : false,
                    ]);
                    $created++;
                }
            }

            return redirect()->route('facility.nurse-ward.index')
                ->with('success', "{$created} nurse(s) assigned to ward successfully!");
        } catch (\Exception $e) {
            Log::error('Error creating nurse ward assignments: ' . $e->getMessage());
            return back()->with('error', 'Failed to create assignments. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();
        
        $assignment = NurseWard::with(['nurse', 'ward'])
            ->whereHas('ward', fn($q) => $q->where('facility_id', $facilityId))
            ->findOrFail($id);

        $wards = Ward::where('facility_id', $facilityId)->where('is_active', true)->get();
        $nurses = User::where('facility_id', $facilityId)->get();

        return view('facility.nurse-ward.edit', compact('assignment', 'wards', 'nurses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();

        $assignment = NurseWard::whereHas('ward', fn($q) => $q->where('facility_id', $facilityId))
            ->findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ward_id' => 'required|exists:wards,id',
            'assigned_date' => 'required|date',
        ]);

        // Verify ward belongs to facility
        $ward = Ward::where('id', $request->ward_id)->where('facility_id', $facilityId)->first();
        if (!$ward) {
            return back()->with('error', 'Invalid ward selected.');
        }

        // Verify nurse belongs to facility
        $nurse = User::where('id', $request->user_id)->where('facility_id', $facilityId)->first();
        if (!$nurse) {
            return back()->with('error', 'Invalid nurse selected.');
        }

        try {
            $assignment->update([
                'user_id' => $request->user_id,
                'ward_id' => $request->ward_id,
                'assigned_date' => $request->assigned_date,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('facility.nurse-ward.index')
                ->with('success', 'Assignment updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating nurse ward assignment: ' . $e->getMessage());
            return back()->with('error', 'Failed to update assignment.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('facility.dashboard')
                ->with('error', 'Access denied.');
        }

        $facilityId = $this->getFacilityId();

        $assignment = NurseWard::whereHas('ward', fn($q) => $q->where('facility_id', $facilityId))
            ->findOrFail($id);

        try {
            $assignment->delete();
            return redirect()->route('facility.nurse-ward.index')
                ->with('success', 'Assignment removed successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting nurse ward assignment: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove assignment.');
        }
    }
}
