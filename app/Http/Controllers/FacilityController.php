<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacilitiesExport;
use App\Exports\FacilityServicesExport;
use App\Imports\FacilitiesImport;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Facility::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ward', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Filter by LGA
        if ($request->filled('lga')) {
            $query->byLga($request->get('lga'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->get('type'));
        }

        $facilities = $query->latest()->paginate(15)->appends($request->query());
        
        // Get filter options
        $lgas = Facility::getBornoLGAs();
        $types = Facility::getFacilityTypes();

        return view('admin.facilities.index', compact('facilities', 'lgas', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lgas = Facility::getBornoLGAs();
        $types = Facility::getFacilityTypes();
        $wards = Facility::getBornoWards();
        $secondaryServices = \App\Models\Service::where('type', 'Secondary')->orderBy('name')->get();
        
        return view('admin.facilities.create', compact('lgas', 'types', 'wards', 'secondaryServices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lga' => 'required|string|in:' . implode(',', Facility::getBornoLGAs()),
            'ward' => 'required|string|max:255',
            'type' => 'nullable|string|in:' . implode(',', Facility::getFacilityTypes()),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $facility = Facility::create($request->only(['name', 'lga', 'ward', 'type']));
            
            // Sync secondary services if provided
            if ($request->has('services')) {
                $facility->services()->sync($request->services);
            }
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create facility. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility)
    {
        $activitySummary = $this->facilityActivitySummary($facility->id);
        $availableServices = $facility->facilityServices()
            ->where('is_available', true)
            ->with('serviceItem.serviceType.serviceCategory')
            ->orderBy('id')
            ->paginate(8, ['*'], 'available_services_page');
        $unavailableServices = $facility->facilityServices()
            ->where('is_available', false)
            ->with('serviceItem.serviceType.serviceCategory')
            ->orderBy('id')
            ->paginate(8, ['*'], 'unavailable_services_page');
        $servicesTotal = $availableServices->total() + $unavailableServices->total();

        return view('admin.facilities.show', compact(
            'facility',
            'activitySummary',
            'availableServices',
            'unavailableServices',
            'servicesTotal'
        ));
    }

    /**
     * Export every configured service for a facility, not only the current page.
     */
    public function exportServices(Facility $facility)
    {
        $filename = 'facility-services-' . $facility->id . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new FacilityServicesExport($facility->id), $filename);
    }

    /**
     * Return the facility-linked records that are relevant to administrators.
     *
     * This intentionally uses schema checks because the admin application is
     * deployed alongside the EHR and some installations may not have every
     * optional module enabled yet.
     */
    private function facilityActivitySummary(int $facilityId): array
    {
        $definitions = [
            [
                'key' => 'beneficiaries',
                'label' => 'Primary enrolments',
                'description' => 'Beneficiaries registered with this facility',
                'table' => 'beneficiaries',
                'column' => 'facility_id',
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'key' => 'alternate_beneficiaries',
                'label' => 'Alternate-facility enrolments',
                'description' => 'Beneficiaries using this as an alternate facility',
                'table' => 'beneficiaries',
                'column' => 'alt_facility_id',
                'icon' => 'user-plus',
                'tone' => 'info',
            ],
            [
                'key' => 'children',
                'label' => 'Children records',
                'description' => 'Children linked to beneficiaries here',
                'table' => 'children',
                'column' => 'facility_id',
                'icon' => 'smile',
                'tone' => 'info',
            ],
            [
                'key' => 'spouses',
                'label' => 'Spouse records',
                'description' => 'Spouse records linked to beneficiaries here',
                'table' => 'spouses',
                'column' => 'facility_id',
                'icon' => 'heart',
                'tone' => 'danger',
            ],
            [
                'key' => 'beneficiary_facility_changes',
                'label' => 'Enrolment reassignment history',
                'description' => 'Historical facility changes involving this facility',
                'table' => 'beneficiary_facility_changes',
                'columns' => ['old_facility_id', 'new_facility_id', 'old_alt_facility_id', 'new_alt_facility_id'],
                'icon' => 'shuffle',
                'tone' => 'warning',
            ],
            [
                'key' => 'admissions',
                'label' => 'Admissions',
                'description' => 'Patient admission records',
                'table' => 'admissions',
                'column' => 'facility_id',
                'icon' => 'log-in',
                'tone' => 'success',
            ],
            [
                'key' => 'encounters',
                'label' => 'EHR encounters',
                'description' => 'Clinical encounter records',
                'table' => 'encounters',
                'column' => 'facility_id',
                'icon' => 'activity',
                'tone' => 'success',
            ],
            [
                'key' => 'investigations',
                'label' => 'Investigations',
                'description' => 'Laboratory and diagnostic investigations',
                'table' => 'investigations',
                'column' => 'facility_id',
                'icon' => 'search',
                'tone' => 'info',
            ],
            [
                'key' => 'claims',
                'label' => 'Claims',
                'description' => 'Claims submitted from this facility',
                'table' => 'claims',
                'column' => 'facility_id',
                'icon' => 'file-text',
                'tone' => 'warning',
            ],
            [
                'key' => 'facility_claims',
                'label' => 'Facility claim submissions',
                'description' => 'EHR facility claim records',
                'table' => 'facility_claims',
                'column' => 'facility_id',
                'icon' => 'clipboard',
                'tone' => 'warning',
            ],
            [
                'key' => 'service_orders',
                'label' => 'Service orders',
                'description' => 'Services ordered at this facility',
                'table' => 'service_orders',
                'column' => 'facility_id',
                'icon' => 'shopping-bag',
                'tone' => 'primary',
            ],
            [
                'key' => 'outgoing_referrals',
                'label' => 'Outgoing referrals',
                'description' => 'Referrals sent to other facilities',
                'table' => 'service_referrals',
                'column' => 'from_facility_id',
                'icon' => 'corner-up-right',
                'tone' => 'primary',
            ],
            [
                'key' => 'incoming_referrals',
                'label' => 'Incoming referrals',
                'description' => 'Referrals received from other facilities',
                'table' => 'service_referrals',
                'column' => 'to_facility_id',
                'icon' => 'corner-down-left',
                'tone' => 'success',
            ],
            [
                'key' => 'drug_stocks',
                'label' => 'Drug stock records',
                'description' => 'Stock quantities and batches recorded here',
                'table' => 'drug_stocks',
                'column' => 'facility_id',
                'icon' => 'package',
                'tone' => 'success',
            ],
            [
                'key' => 'drug_stock_requests',
                'label' => 'Drug stock requests',
                'description' => 'Requests raised by this facility',
                'table' => 'drug_stock_requests',
                'column' => 'facility_id',
                'icon' => 'truck',
                'tone' => 'warning',
            ],
            [
                'key' => 'drugs',
                'label' => 'Facility drug catalogue',
                'description' => 'Drugs configured for this facility',
                'table' => 'drugs',
                'column' => 'facility_id',
                'icon' => 'thermometer',
                'tone' => 'info',
            ],
            [
                'key' => 'tickets',
                'label' => 'Support tickets',
                'description' => 'Support requests raised for this facility',
                'table' => 'tickets',
                'column' => 'facility_id',
                'icon' => 'help-circle',
                'tone' => 'danger',
            ],
            [
                'key' => 'facility_services',
                'label' => 'Configured service items',
                'description' => 'Service availability configured here',
                'table' => 'facility_services',
                'column' => 'facility_id',
                'icon' => 'grid',
                'tone' => 'primary',
            ],
            [
                'key' => 'assigned_services',
                'label' => 'Assigned services',
                'description' => 'Services assigned to this facility',
                'table' => 'facility_has_services',
                'column' => 'facility_id',
                'icon' => 'check-square',
                'tone' => 'success',
            ],
            [
                'key' => 'staff_assignments',
                'label' => 'Staff assignments',
                'description' => 'Staff linked through the facility roster',
                'table' => 'facility_staff',
                'column' => 'facility_id',
                'icon' => 'user-check',
                'tone' => 'info',
            ],
            [
                'key' => 'linked_users',
                'label' => 'Linked user accounts',
                'description' => 'User accounts assigned to this facility',
                'table' => 'users',
                'column' => 'facility_id',
                'icon' => 'user',
                'tone' => 'info',
            ],
            [
                'key' => 'wards',
                'label' => 'Wards',
                'description' => 'Wards configured for this facility',
                'table' => 'wards',
                'column' => 'facility_id',
                'icon' => 'layers',
                'tone' => 'primary',
            ],
            [
                'key' => 'facility_wallets',
                'label' => 'Facility wallets',
                'description' => 'Wallet and funding records',
                'table' => 'facility_wallets',
                'column' => 'facility_id',
                'icon' => 'credit-card',
                'tone' => 'success',
            ],
        ];

        return collect($definitions)
            ->filter(function (array $definition) {
                return Schema::hasTable($definition['table']);
            })
            ->map(function (array $definition) use ($facilityId) {
                if (!empty($definition['columns'])) {
                    $count = DB::table($definition['table'])
                        ->where(function ($query) use ($definition, $facilityId) {
                            foreach ($definition['columns'] as $index => $column) {
                                $method = $index === 0 ? 'where' : 'orWhere';
                                $query->{$method}($column, $facilityId);
                            }
                        })
                        ->count();
                } elseif (Schema::hasColumn($definition['table'], $definition['column'])) {
                    $count = DB::table($definition['table'])
                        ->where($definition['column'], $facilityId)
                        ->count();
                } else {
                    $count = 0;
                }

                $definition['count'] = $count;
                return $definition;
            })
            ->values()
            ->all();
    }

    private function facilityActivityTotal(array $activitySummary): int
    {
        return (int) collect($activitySummary)->sum('count');
    }

    private function facilityActivityMessage(Facility $facility, array $activitySummary): string
    {
        $activities = collect($activitySummary)
            ->filter(fn (array $activity) => $activity['count'] > 0)
            ->map(fn (array $activity) => $activity['label'] . ': ' . number_format($activity['count']))
            ->implode(', ');

        return 'Deletion blocked for ' . $facility->name . '. Linked activity exists (' . $activities . ').';
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facility $facility)
    {
        $lgas = Facility::getBornoLGAs();
        $types = Facility::getFacilityTypes();
        $wards = Facility::getBornoWards();
        $secondaryServices = \App\Models\Service::where('type', 'Secondary')->orderBy('name')->get();
        
        return view('admin.facilities.edit', compact('facility', 'lgas', 'types', 'wards', 'secondaryServices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lga' => 'required|string|in:' . implode(',', Facility::getBornoLGAs()),
            'ward' => 'required|string|max:255',
            'type' => 'nullable|string|in:' . implode(',', Facility::getFacilityTypes()),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $facility->update($request->only(['name', 'lga', 'ward', 'type']));
            
            // Sync secondary services if provided
            if ($request->has('services')) {
                $facility->services()->sync($request->services);
            } else {
                // If no services selected, detach all
                $facility->services()->sync([]);
            }
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update facility. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        $activitySummary = $this->facilityActivitySummary($facility->id);

        if ($this->facilityActivityTotal($activitySummary) > 0) {
            return redirect()
                ->route('facilities.show', $facility)
                ->with('error', $this->facilityActivityMessage($facility, $activitySummary));
        }

        try {
            $facility->delete();
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete facility. Please try again.');
        }
    }

    /**
     * Show the upload form
     */
    public function uploadForm()
    {
        return view('admin.facilities.upload');
    }

    /**
     * Handle Excel upload
     */
    public function uploadExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $import = new FacilitiesImport();
            Excel::import($import, $request->file('excel_file'));
            
            $imported = $import->getImportedCount();
            $errors = $import->getErrors();
            
            if ($imported > 0) {
                $message = "Successfully imported {$imported} facilities.";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " rows had errors.";
                }
                return redirect()->route('facilities.index')->with('success', $message);
            } else {
                return back()->with('error', 'No facilities were imported. Please check your file format.');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing facilities: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $filename = 'facilities_import_template_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new FacilitiesExport(true), $filename, \Maatwebsite\Excel\Excel::XLSX, $headers);
    }

    /**
     * Bulk delete facilities
     */
    public function bulkDelete(Request $request)
    {
        // Simple test to check if route is working
        if ($request->has('test')) {
            $userName = Auth::check() ? Auth::user()->name : 'Not authenticated';
            return response()->json(['success' => true, 'message' => 'Route is working! User: ' . $userName]);
        }
        
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
            }
            return redirect()->route('login');
        }
        
        // Handle both JSON and form requests
        $data = $request->isJson() ? $request->json()->all() : $request->all();
        
        // Debug logging
        Log::info('Bulk delete request data', ['data' => $data]);
        Log::info('Request method', ['method' => $request->method()]);
        Log::info('Request headers', ['headers' => $request->headers->all()]);
        
        $validator = Validator::make($data, [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:facilities,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid facility IDs: ' . implode(', ', $validator->errors()->all())
                ], 400);
            }
            return back()->with('error', 'Invalid facility IDs selected.');
        }

        try {
            DB::beginTransaction();
            
            $ids = $data['ids'];
            $facilities = Facility::whereIn('id', $ids)->get();
            $deletableIds = [];
            $blocked = [];
            
            if ($facilities->isEmpty()) {
                DB::rollback();
                return response()->json(['success' => false, 'message' => 'No facilities found to delete.'], 404);
            }

            foreach ($facilities as $facility) {
                $activitySummary = $this->facilityActivitySummary($facility->id);
                if ($this->facilityActivityTotal($activitySummary) > 0) {
                    $blocked[] = [
                        'id' => $facility->id,
                        'name' => $facility->name,
                        'total' => $this->facilityActivityTotal($activitySummary),
                        'activities' => collect($activitySummary)
                            ->filter(fn (array $activity) => $activity['count'] > 0)
                            ->values()
                            ->all(),
                    ];
                } else {
                    $deletableIds[] = $facility->id;
                }
            }

            if (!empty($deletableIds)) {
                Facility::whereIn('id', $deletableIds)->delete();
            }
            
            DB::commit();
            
            $deletedCount = count($deletableIds);
            $blockedCount = count($blocked);
            $message = $deletedCount > 0
                ? "Successfully deleted {$deletedCount} " . ($deletedCount === 1 ? 'facility' : 'facilities') . "."
                : 'No facilities were deleted.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $deletedCount > 0,
                    'message' => $message,
                    'deleted_count' => $deletedCount,
                    'blocked_count' => $blockedCount,
                    'blocked' => $blocked,
                ], $deletedCount > 0 ? 200 : 422);
            }

            $redirect = redirect()->route('facilities.index');
            if ($deletedCount > 0) {
                $redirect->with('success', $message);
            } else {
                $redirect->with('error', $message);
            }
            if ($blockedCount > 0) {
                $redirect->with('facility_activity_blocks', $blocked);
            }
            return $redirect;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $errorMessage = 'Failed to delete facilities. Please try again.';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }
}
