<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Program::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('format', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by has_dependant
        if ($request->filled('has_dependant')) {
            $query->where('has_dependant', $request->get('has_dependant'));
        }

        $programs = $query->latest()->paginate(15)->appends($request->query());

        return view('admin.programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.programs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:programs',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'format' => 'required|string|max:255',
            'has_dependant' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->safe()->except(['logo']);

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('programs/logos', 'public');
            }

            Program::create($data);

            return redirect()->route('programs.index')
                ->with('success', 'Program created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create program. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        return view('admin.programs.show', compact('program'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:programs,name,' . $program->id,
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'format' => 'required|string|max:255',
            'has_dependant' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->safe()->except(['logo']);

            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($program->logo) {
                    Storage::disk('public')->delete($program->logo);
                }
                $data['logo'] = $request->file('logo')->store('programs/logos', 'public');
            }

            $program->update($data);

            return redirect()->route('programs.index')
                ->with('success', 'Program updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update program. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        try {
            if ($program->logo) {
                Storage::disk('public')->delete($program->logo);
            }
            $program->delete();

            return redirect()->route('programs.index')
                ->with('success', 'Program deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete program. Please try again.');
        }
    }
}
