<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \Log::info('ServicesController index called', [
            'is_ajax' => $request->ajax(),
            'request_data' => $request->all()
        ]);

        if ($request->ajax()) {
            $query = Service::query();

            // Apply filters
            if ($request->has('type') && $request->type != '') {
                $query->where('type', $request->type);
            }

            if ($request->has('search') && $request->search != '') {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->search}%")
                      ->orWhere('description', 'LIKE', "%{$request->search}%")
                      ->orWhere('type', 'LIKE', "%{$request->search}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($service) {
                    $actions = '<a href="' . route('services.show', $service->id) . '" class="btn btn-sm btn-info me-1" title="View">
                                    <i class="ti-eye"></i>
                                </a>';
                    
                    $actions .= '<a href="' . route('services.edit', $service->id) . '" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="ti-pencil"></i>
                                </a>';
                    
                    $actions .= '<form action="' . route('services.destroy', $service->id) . '" method="POST" style="display: inline;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                        <i class="ti-trash"></i>
                                    </button>
                                </form>';
                    
                    return $actions;
                })
                ->addColumn('type_badge', function ($service) {
                    return $service->type_badge;
                })
                ->addColumn('price_formatted', function ($service) {
                    return $service->price_with_currency;
                })
                ->addColumn('created_at_formatted', function ($service) {
                    return $service->created_at->format('M d, Y');
                })
                ->rawColumns(['action', 'type_badge'])
                ->make(true);
        }

        $types = Service::getTypes();
        $stats = [
            'total' => Service::count(),
            'primary' => Service::where('type', 'Primary')->count(),
            'secondary' => Service::where('type', 'Secondary')->count(),
        ];

        return view('admin.services.index', compact('types', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $types = Service::getTypes();
        return view('admin.services.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:Primary,Secondary',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            Service::create($request->all());
            
            return redirect()->route('services.index')
                ->with('success', 'Service created successfully.');
        } catch (\Exception $e) {
            \Log::error('Service creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create service: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for bulk creating services.
     */
    public function bulkCreate(): View
    {
        $types = Service::getTypes();
        return view('admin.services.bulk-create', compact('types'));
    }

    /**
     * Store multiple newly created resources in storage.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        \Log::info('Bulk service creation started', [
            'request_data' => $request->all(),
            'user_id' => auth('staff')->id(),
            'user_email' => auth('staff')->user()->email ?? 'unknown'
        ]);

        $request->validate([
            'services' => 'required|array',
            'services.*.name' => 'required|string|max:255',
            'services.*.description' => 'nullable|string',
            'services.*.type' => 'required|in:Primary,Secondary',
            'services.*.price' => 'required|numeric|min:0',
        ]);

        $created = 0;
        $errors = [];
        $duplicates = [];

        foreach ($request->services as $index => $serviceData) {
            // Skip empty rows
            if (empty($serviceData['name']) && empty($serviceData['type'])) {
                continue;
            }

            try {
                // Check for duplicate service name
                $existingService = Service::where('name', $serviceData['name'])->first();

                if ($existingService) {
                    \Log::info('Service already exists, skipping', [
                        'service_name' => $serviceData['name'],
                        'existing_id' => $existingService->id
                    ]);
                    $duplicates[] = $serviceData['name'];
                    continue;
                }

                // Create the service
                $service = Service::create([
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'] ?? null,
                    'type' => $serviceData['type'],
                    'price' => $serviceData['price'],
                ]);

                \Log::info('Service created successfully', [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'type' => $service->type
                ]);

                $created++;

            } catch (\Exception $e) {
                \Log::error('Service creation failed', [
                    'index' => $index,
                    'service_name' => $serviceData['name'] ?? 'unknown',
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString()
                ]);

                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        // Build success message
        $message = "Successfully created {$created} service(s).";
        
        if (!empty($duplicates)) {
            $message .= " " . count($duplicates) . " service(s) already existed: " . implode(', ', array_slice($duplicates, 0, 3));
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

        \Log::info('Bulk service creation completed', [
            'created' => $created,
            'duplicates' => count($duplicates),
            'errors' => count($errors),
            'total_processed' => count($request->services)
        ]);

        return redirect()->route('services.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $service = Service::findOrFail($id);
        $facilities = $service->facilities()->orderBy('name')->get();
        
        return view('admin.services.show', compact('service', 'facilities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $service = Service::findOrFail($id);
        $types = Service::getTypes();
        
        return view('admin.services.edit', compact('service', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:Primary,Secondary',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $service->update($request->all());
            
            return redirect()->route('services.index')
                ->with('success', 'Service updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Service update failed', [
                'service_id' => $id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update service: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $service = Service::findOrFail($id);
        
        try {
            $service->delete();
            
            return redirect()->route('services.index')
                ->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Service deletion failed', [
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('services.index')
                ->with('error', 'Failed to delete service: ' . $e->getMessage());
        }
    }
}
