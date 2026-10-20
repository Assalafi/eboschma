<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Support\UserActivity;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');

        $staff = Staff::with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', '%' . $search . '%')
                      ->orWhere('email',    'like', '%' . $search . '%')
                      ->orWhere('phone',    'like', '%' . $search . '%');
                });
            })
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Staff::count(),
            'active' => Staff::where('is_active', true)->count(),
            'inactive' => Staff::where('is_active', false)->count(),
            'with_roles' => Staff::has('roles')->count(),
        ];

        return view('admin.staff.index', compact('staff', 'search', 'status', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('guard_name', 'staff')->get();
        $facilities = \App\Models\Facility::orderBy('name')->get();
        return view('admin.staff.create', compact('roles', 'facilities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'required|exists:roles,name',
            'facilities' => 'nullable|array',
            'facilities.*' => 'exists:facilities,id',
        ]);

        $staff = Staff::create([
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign multiple roles
        $staff->syncRoles($validated['roles']);

        // Assign facilities
        if (!empty($validated['facilities'])) {
            $staff->facilities()->sync($validated['facilities']);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        $staff->load('roles', 'permissions');
        return view('admin.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        $roles = Role::where('guard_name', 'staff')->get();
        $facilities = \App\Models\Facility::orderBy('name')->get();
        $staff->load('roles', 'facilities');
        return view('admin.staff.edit', compact('staff', 'roles', 'facilities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $staff->id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'required|exists:roles,name',
            'facilities' => 'nullable|array',
            'facilities.*' => 'exists:facilities,id',
        ]);

        $staff->update([
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $staff->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync multiple roles
        $staff->syncRoles($validated['roles']);

        // Sync facilities
        if (isset($validated['facilities'])) {
            $staff->facilities()->sync($validated['facilities']);
        } else {
            $staff->facilities()->sync([]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully!');
    }

    /**
     * Activate or deactivate the specified staff member.
     */
    public function toggleStatus(Request $request, Staff $staff)
    {
        if (auth('staff')->id() === $staff->id) {
            $msg = 'You cannot activate or deactivate your own account.';

            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $staff->is_active = !$staff->is_active;
        $staff->save();

        $message = $staff->is_active
            ? 'Staff member activated successfully.'
            : 'Staff member deactivated successfully.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $staff->is_active,
                'message' => $message,
                'stats' => [
                    'total' => Staff::count(),
                    'active' => Staff::where('is_active', true)->count(),
                    'inactive' => Staff::where('is_active', false)->count(),
                    'with_roles' => Staff::has('roles')->count(),
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        // Prevent deleting yourself
        if (auth('staff')->id() === $staff->id) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        if (UserActivity::staffHasActivity($staff->id)) {
            return back()->with('error', 'This staff member has activity in the system and cannot be deleted. Deactivate the account instead.');
        }

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member deleted successfully!');
    }
}
