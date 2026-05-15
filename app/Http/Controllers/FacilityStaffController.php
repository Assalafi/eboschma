<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Facility;
use App\Models\StaffPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
// DB
use Illuminate\Support\Facades\DB;

class FacilityStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with(['facility', 'staffPosition']);

            // Apply filters
            if ($request->has('facility_id') && $request->facility_id != '') {
                $query->where('facility_id', $request->facility_id);
            }

            if ($request->has('staff_position_id') && $request->staff_position_id != '') {
                $query->where('staff_position_id', $request->staff_position_id);
            }

            // DataTables sends search as array with 'value' key
            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('email', 'LIKE', "%{$searchValue}%")
                      ->orWhere('phone', 'LIKE', "%{$searchValue}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('facility_name', function ($user) {
                    return $user->facility ? $user->facility->name : 'N/A';
                })
                ->addColumn('position_name', function ($user) {
                    return $user->staffPosition ? $user->staffPosition->name : 'N/A';
                })
                ->addColumn('role_name', function ($user) {
                    $roles = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('model_has_roles.model_id', $user->id)
                        ->where('model_has_roles.model_type', 'App\Models\User')
                        ->pluck('roles.name');
                    
                    return $roles->count() > 0 ? $roles->implode(', ') : 'N/A';
                })
                ->addColumn('passport', function ($user) {
                    if ($user->passport) {
                        return '<img src="' . asset('storage/' . $user->passport) . '" 
                                alt="Passport" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" 
                                class="img-thumbnail">';
                    }
                    return '<span class="text-muted">No photo</span>';
                })
                ->addColumn('action', function ($user) {
                    $actions = '<a href="' . route('facility-staff.edit', $user->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('facility-staff.destroy', $user->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->addColumn('created_at_formatted', function ($user) {
                    return $user->created_at->format('M d, Y');
                })
                ->rawColumns(['passport', 'action'])
                ->make(true);
        }

        $facilities = Facility::orderBy('name')->get();
        $staffPositions = StaffPosition::orderBy('name')->get();
        $roles = DB::table('roles')->where(['guard_name' => 'web'])->get();
        $stats = [
            'total' => User::count(),
            'with_photo' => User::whereNotNull('passport')->count(),
        ];

        return view('admin.facility-staff.index', compact('facilities', 'staffPositions', 'stats', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        $staffPositions = StaffPosition::orderBy('name')->get();
        $roles = DB::table('roles')->where(['guard_name' => 'web'])->get();
        
        return view('admin.facility-staff.create', compact('facilities', 'staffPositions', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'facility_id' => 'required|exists:facilities,id',
            'staff_position_id' => 'required|exists:staff_positions,id',
            'role_id' => 'required|array|min:1',
            'role_id.*' => 'required|exists:roles,id',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            Log::info('Creating facility staff with data: ' . json_encode($request->except(['password', 'passport'])));
            
            $data = $request->except(['passport', 'role_id']);
            $data['password'] = Hash::make($request->password);

            // Handle passport upload
            if ($request->hasFile('passport')) {
                $passport = $request->file('passport');
                $passportPath = $passport->store('staff-passports', 'public');
                $data['passport'] = $passportPath;
            }

            $user = User::create($data);
            Log::info('User created with ID: ' . $user->id);

            // Assign multiple roles
            if (!empty($request->role_id)) {
                foreach ($request->role_id as $roleId) {
                    Log::info('Assigning role: ' . $roleId . ' to user: ' . $user->id);
                    DB::table('model_has_roles')->insert([
                        'role_id' => $roleId,
                        'model_type' => 'App\Models\User',
                        'model_id' => $user->id,
                        'model_uuid' => $user->id,
                    ]);
                }
            } else {
                Log::warning('No roles provided for user: ' . $user->id);
            }

            return redirect()->route('facility-staff.index')
                ->with('success', 'Facility staff created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating facility staff: ' . $e->getMessage());
            Log::error('Request data: ' . json_encode($request->all()));
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return back()->with('error', 'Failed to create facility staff. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $staff = User::findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        $staffPositions = StaffPosition::orderBy('name')->get();
        $roles = DB::table('roles')->where(['guard_name' => 'web'])->get();
        
        return view('admin.facility-staff.edit', compact('staff', 'facilities', 'staffPositions', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'facility_id' => 'required|exists:facilities,id',
            'staff_position_id' => 'required|exists:staff_positions,id',
            'role_id' => 'required|array|min:1',
            'role_id.*' => 'required|exists:roles,id',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            Log::info('Updating facility staff ID: ' . $id . ' with data: ' . json_encode($request->except(['password', 'passport'])));
            
            $data = $request->except(['passport', 'role_id']);

            // Handle password update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            // Handle passport upload
            if ($request->hasFile('passport')) {
                // Delete old passport if exists
                if ($staff->passport) {
                    Storage::disk('public')->delete($staff->passport);
                }

                $passport = $request->file('passport');
                $passportPath = $passport->store('staff-passports', 'public');
                $data['passport'] = $passportPath;
            }

            // Use transaction for role assignment
            DB::transaction(function () use ($request, $id) {
                Log::info('Updating roles for user: ' . $id . ' with roles: ' . json_encode($request->role_id));
                
                // Remove existing roles
                $deleted = DB::table('model_has_roles')
                    ->where('model_id', $id)
                    ->where('model_type', 'App\Models\User')
                    ->delete();
                Log::info('Deleted ' . $deleted . ' existing roles');
                
                // Assign new roles
                if (!empty($request->role_id)) {
                    foreach ($request->role_id as $roleId) {
                        Log::info('Assigning role: ' . $roleId . ' to user: ' . $id);
                        DB::table('model_has_roles')->insert([
                            'role_id' => $roleId,
                            'model_type' => 'App\Models\User',
                            'model_id' => $id,
                            'model_uuid' => $id,
                        ]);
                    }
                } else {
                    Log::warning('No roles provided for user: ' . $id);
                }
            });

            $staff->update($data);
            Log::info('Staff updated successfully');

            
            return redirect()->route('facility-staff.index')
                ->with('success', 'Facility staff updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating facility staff: ' . $e->getMessage());
            Log::error('Request data: ' . json_encode($request->all()));
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return back()->with('error', 'Failed to update facility staff. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $staff = User::findOrFail($id);
            
            // Delete passport if exists
            if ($staff->passport) {
                Storage::disk('public')->delete($staff->passport);
            }
            
            $staff->delete();
            
            return redirect()->route('facility-staff.index')
                ->with('success', 'Facility staff deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting facility staff: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete facility staff. Please try again.');
        }
    }
}
