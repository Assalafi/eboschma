<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Facility;
use App\Models\Staff;
use App\Models\Spouse;
use App\Models\Child;
use App\Exports\EnumeratorsExport;
use App\Exports\EnumeratorEnrollmentsExport;
use App\Exports\FacilitiesExport;
use App\Exports\FacilityEnrollmentsExport;
use App\Exports\DashboardExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index()
    {
        // Get overview statistics
        $stats = [
            'total_beneficiaries' => Beneficiary::count(),
            'total_enumerators' => Staff::role('Enumerator')->count(),
            'total_facilities' => Facility::count(),
            'pending_enrollments' => Beneficiary::where('status', 'pending')->count(),
            'active_enrollments' => Beneficiary::where('status', 'active')->count(),
        ];

        // Get recent enrollments
        $recent_enrollments = Beneficiary::with('facility')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('reports.index', compact('stats', 'recent_enrollments'));
    }

    public function enumerators()
    {
        // Get all enumerators with their enrollment counts
        $enumerators = Staff::role('Enumerator')
            ->withCount(['beneficiaries' => function($query) {
                $query->where('status', '!=', 'draft');
            }])
            ->withCount(['beneficiaries as main_facility_enrollments' => function($query) {
                $query->where('status', '!=', 'draft')
                      ->whereNotNull('facility_id');
            }])
            ->withCount(['beneficiaries as unique_facilities_count' => function($query) {
                $query->where('status', '!=', 'draft')
                      ->whereNotNull('facility_id')
                      ->select(DB::raw('COUNT(DISTINCT facility_id)'));
            }])
            ->orderBy('beneficiaries_count', 'desc')
            ->get(); // Get all results first to calculate unique facilities properly

        // Calculate unique facilities including spouses and children for each enumerator
        foreach ($enumerators as $enumerator) {
            // Get all facility IDs from beneficiaries for this enumerator
            $beneficiaryFacilities = $enumerator->beneficiaries()
                ->where('status', '!=', 'draft')
                ->whereNotNull('facility_id')
                ->pluck('facility_id');
            
            // Get spouses for beneficiaries created by this enumerator
            $spouseFacilities = DB::table('spouses')
                ->join('beneficiaries', 'spouses.beneficiary_id', '=', 'beneficiaries.id')
                ->where('beneficiaries.created_by', $enumerator->id)
                ->whereNotNull('spouses.facility_id')
                ->pluck('spouses.facility_id');
            
            // Get children for beneficiaries created by this enumerator
            $childrenFacilities = DB::table('children')
                ->join('beneficiaries', 'children.beneficiary_id', '=', 'beneficiaries.id')
                ->where('beneficiaries.created_by', $enumerator->id)
                ->whereNotNull('children.facility_id')
                ->pluck('children.facility_id');
            
            // Combine all facility IDs and get unique count
            $allFacilities = $beneficiaryFacilities
                ->merge($spouseFacilities)
                ->merge($childrenFacilities)
                ->unique()
                ->filter();
            
            $enumerator->unique_facilities_count = $allFacilities->count();
        }

        // Convert back to paginated collection
        $currentPage = request()->get('page', 1);
        $perPage = 20;
        $currentPageItems = $enumerators->slice(($currentPage - 1) * $perPage, $perPage);
        $paginatedEnumerators = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $enumerators->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('reports.enumerators', ['enumerators' => $paginatedEnumerators]);
    }

    public function exportEnumerators(Request $request)
    {
        // Check if exporting specific enumerator or all
        $enumeratorId = $request->get('enumerator_id');
        
        $filename = 'enumerator_performance_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new EnumeratorsExport($enumeratorId), $filename);
    }

    public function enumeratorEnrollments($id)
    {
        // Get the enumerator
        $enumerator = Staff::findOrFail($id);
        
        // Get all enrollments by this enumerator
        $enrollments = Beneficiary::where('created_by', $id)
            ->with('facility')
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reports.enumerator-enrollments', compact('enumerator', 'enrollments'));
    }

    public function exportEnumeratorEnrollments($id)
    {
        // Get the enumerator
        $enumerator = Staff::findOrFail($id);
        
        $filename = 'enumerator_' . $enumerator->fullname . '_enrollments_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new EnumeratorEnrollmentsExport($id, $enumerator->fullname), $filename);
    }

    public function facilityEnrollments($id)
    {
        // Get the facility
        $facility = Facility::findOrFail($id);
        
        // Create combined enrollments list with categories
        $enrollments = collect();
        
        // Get all beneficiaries for this facility
        $beneficiaries = Beneficiary::where('facility_id', $id)
            ->with('creator')
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($beneficiaries as $beneficiary) {
            // Add principal beneficiary
            $enrollments->push((object)[
                'id' => $beneficiary->id,
                'beneficiary_id' => $beneficiary->id, // Principal beneficiaries use their own ID
                'boschma_no' => $beneficiary->boschma_no,
                'fullname' => $beneficiary->fullname,
                'gender' => $beneficiary->gender,
                'phone_no' => $beneficiary->phone_no,
                'category' => 'Principal',
                'category_badge' => 'primary',
                'creator' => $beneficiary->creator,
                'status' => $beneficiary->status,
                'created_at' => $beneficiary->created_at,
                'route_name' => 'beneficiaries.show'
            ]);
        }
        
        // Get all spouses directly assigned to this facility
        $spouses = \App\Models\Spouse::where('facility_id', $id)
            ->with('beneficiary.creator')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($spouses as $spouse) {
            $enrollments->push((object)[
                'id' => $spouse->id,
                'beneficiary_id' => $spouse->beneficiary_id,
                'boschma_no' => $spouse->boschma_no,
                'fullname' => $spouse->name,
                'gender' => $spouse->gender,
                'phone_no' => $spouse->phone ?? 'N/A',
                'category' => 'Spouse',
                'category_badge' => 'success',
                'creator' => $spouse->beneficiary->creator ?? null,
                'status' => $spouse->beneficiary->status ?? 'active',
                'created_at' => $spouse->created_at,
                'route_name' => 'spouses.show'
            ]);
        }
        
        // Get all children directly assigned to this facility
        $children = \App\Models\Child::where('facility_id', $id)
            ->with('beneficiary.creator')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($children as $child) {
            $enrollments->push((object)[
                'id' => $child->id,
                'beneficiary_id' => $child->beneficiary_id,
                'boschma_no' => $child->boschma_no,
                'fullname' => $child->name,
                'gender' => $child->gender,
                'phone_no' => 'N/A', // Children don't have phone numbers
                'category' => 'Child',
                'category_badge' => 'info',
                'creator' => $child->beneficiary->creator ?? null,
                'status' => $child->beneficiary->status ?? 'active',
                'created_at' => $child->created_at,
                'route_name' => 'children.show'
            ]);
        }

        // Sort by created_at date
        $enrollments = $enrollments->sortByDesc('created_at');

        // Create paginator for the combined results
        $currentPage = request()->get('page', 1);
        $perPage = 20;
        $currentItems = $enrollments->forPage($currentPage, $perPage);
        
        $paginatedEnrollments = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $enrollments->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('reports.facility-enrollments', compact('facility', 'enrollments', 'paginatedEnrollments'));
    }

    public function exportFacilities(Request $request)
    {
        // Check if exporting specific facility or all
        $facilityId = $request->get('facility_id');
        
        if ($facilityId) {
            // Export specific facility enrollments (detailed records)
            $facility = Facility::findOrFail($facilityId);
            $filename = $facility->name . '_enrollments_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new FacilityEnrollmentsExport($facilityId, $facility->name), $filename);
        } else {
            // Export all facilities (summary data)
            $filename = 'facility_performance_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new FacilitiesExport(), $filename);
        }
    }

    public function facilities()
    {
        // Get all facilities with their enrollment counts
        $facilities = Facility::withCount(['beneficiaries' => function($query) {
                $query->where('status', '!=', 'draft');
            }])
            ->withCount(['beneficiaries as main_facility_enrollments' => function($query) {
                $query->where('status', '!=', 'draft')
                      ->whereColumn('facility_id', 'facilities.id');
            }])
            ->withCount(['spouses' => function($query) {
                $query->whereColumn('facility_id', 'facilities.id');
            }])
            ->withCount(['children' => function($query) {
                $query->whereColumn('facility_id', 'facilities.id');
            }])
            ->orderBy('beneficiaries_count', 'desc')
            ->paginate(20);

        // Calculate total enrollments for each facility (beneficiaries + spouses + children)
        foreach ($facilities as $facility) {
            $facility->total_enrollments = $facility->beneficiaries_count + $facility->spouses_count + $facility->children_count;
        }

        return view('reports.facilities', compact('facilities'));
    }

    public function enrollments()
    {
        // Get comprehensive enrollment statistics including all categories
        
        // Monthly trends for all enrollments (beneficiaries + spouses + children)
        $beneficiaries_by_month = Beneficiary::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('status', '!=', 'draft')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $spouses_by_month = \App\Models\Spouse::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereNotNull('created_at')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $children_by_month = \App\Models\Child::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereNotNull('created_at')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        // Combine monthly data using a more efficient approach
        $monthly_data = [];
        
        // Initialize all months with zero values
        $beneficiaries_by_month->each(function($item) use (&$monthly_data) { 
            $monthly_data[$item->month] = ['beneficiaries' => 0, 'spouses' => 0, 'children' => 0]; 
        });
        $spouses_by_month->each(function($item) use (&$monthly_data) { 
            if (!isset($monthly_data[$item->month])) {
                $monthly_data[$item->month] = ['beneficiaries' => 0, 'spouses' => 0, 'children' => 0];
            }
        });
        $children_by_month->each(function($item) use (&$monthly_data) { 
            if (!isset($monthly_data[$item->month])) {
                $monthly_data[$item->month] = ['beneficiaries' => 0, 'spouses' => 0, 'children' => 0];
            }
        });

        // Fill in the actual data
        $beneficiaries_by_month->each(function($item) use (&$monthly_data) { 
            $monthly_data[$item->month]['beneficiaries'] = $item->count;
        });
        $spouses_by_month->each(function($item) use (&$monthly_data) { 
            $monthly_data[$item->month]['spouses'] = $item->count;
        });
        $children_by_month->each(function($item) use (&$monthly_data) { 
            $monthly_data[$item->month]['children'] = $item->count;
        });

        // Convert to collection with totals
        $enrollments_by_month = collect();
        foreach($monthly_data as $month => $data) {
            $total = $data['beneficiaries'] + $data['spouses'] + $data['children'];
            $enrollments_by_month->push((object)[
                'month' => $month,
                'count' => $total,
                'beneficiaries' => $data['beneficiaries'],
                'spouses' => $data['spouses'],
                'children' => $data['children']
            ]);
        }

        $enrollments_by_month = $enrollments_by_month->sortByDesc('month')->take(12);

        // Status breakdown (for beneficiaries only, since spouses/children inherit status)
        $enrollments_by_status = Beneficiary::selectRaw('status, COUNT(*) as count')
            ->where('status', '!=', 'draft')
            ->groupBy('status')
            ->get();

        // Overall statistics
        $total_beneficiaries = Beneficiary::where('status', '!=', 'draft')->count();
        $total_spouses = \App\Models\Spouse::count();
        $total_children = \App\Models\Child::count();
        $total_enrollments = $total_beneficiaries + $total_spouses + $total_children;

        // Recent enrollments (last 30 days, all categories)
        $recent_date = now()->subDays(30);
        $recent_beneficiaries = Beneficiary::where('created_at', '>=', $recent_date)
            ->where('status', '!=', 'draft')
            ->with('creator', 'facility')
            ->orderBy('created_at', 'desc')
            ->get();

        $recent_spouses = \App\Models\Spouse::where('created_at', '>=', $recent_date)
            ->with('beneficiary.creator', 'beneficiary.facility')
            ->orderBy('created_at', 'desc')
            ->get();

        $recent_children = \App\Models\Child::where('created_at', '>=', $recent_date)
            ->with('beneficiary.creator', 'beneficiary.facility')
            ->orderBy('created_at', 'desc')
            ->get();

        // Combine recent enrollments
        $recent_enrollments = collect();
        
        // Add beneficiaries
        foreach($recent_beneficiaries as $beneficiary) {
            $recent_enrollments->push((object)[
                'id' => $beneficiary->id,
                'beneficiary_id' => $beneficiary->id,
                'boschma_no' => $beneficiary->boschma_no,
                'fullname' => $beneficiary->fullname,
                'category' => 'Principal',
                'category_badge' => 'primary',
                'gender' => $beneficiary->gender,
                'phone_no' => $beneficiary->phone_no,
                'status' => $beneficiary->status,
                'facility' => $beneficiary->facility,
                'creator' => $beneficiary->creator,
                'created_at' => $beneficiary->created_at,
                'route_name' => 'beneficiaries.show'
            ]);
        }

        // Add spouses
        foreach($recent_spouses as $spouse) {
            $recent_enrollments->push((object)[
                'id' => $spouse->id,
                'beneficiary_id' => $spouse->beneficiary_id,
                'boschma_no' => $spouse->boschma_no,
                'fullname' => $spouse->name,
                'category' => 'Spouse',
                'category_badge' => 'success',
                'gender' => $spouse->gender,
                'phone_no' => $spouse->phone ?? 'N/A',
                'status' => $spouse->beneficiary->status ?? 'active',
                'facility' => $spouse->beneficiary->facility ?? null,
                'creator' => $spouse->beneficiary->creator ?? null,
                'created_at' => $spouse->created_at,
                'route_name' => 'beneficiaries.show'
            ]);
        }

        // Add children
        foreach($recent_children as $child) {
            $recent_enrollments->push((object)[
                'id' => $child->id,
                'beneficiary_id' => $child->beneficiary_id,
                'boschma_no' => $child->boschma_no,
                'fullname' => $child->name,
                'category' => 'Child',
                'category_badge' => 'info',
                'gender' => $child->gender,
                'phone_no' => 'N/A',
                'status' => $child->beneficiary->status ?? 'active',
                'facility' => $child->beneficiary->facility ?? null,
                'creator' => $child->beneficiary->creator ?? null,
                'created_at' => $child->created_at,
                'route_name' => 'beneficiaries.show'
            ]);
        }

        // Sort by created_at and limit
        $recent_enrollments = $recent_enrollments->sortByDesc('created_at')->take(10);

        return view('reports.enrollments', compact(
            'enrollments_by_month', 
            'enrollments_by_status',
            'total_beneficiaries',
            'total_spouses', 
            'total_children',
            'total_enrollments',
            'recent_enrollments'
        ));
    }

    public function exportDashboard()
    {
        // Get comprehensive dashboard statistics
        $stats = [
            'total_beneficiaries' => Beneficiary::count(),
            'active_beneficiaries' => Beneficiary::where('status', 'active')->count(),
            'pending_beneficiaries' => Beneficiary::where('status', 'pending')->count(),
            'inactive_beneficiaries' => Beneficiary::where('status', 'inactive')->count(),
            'total_enumerators' => Staff::role('Enumerator')->count(),
            'total_facilities' => Facility::count(),
        ];

        $filename = 'dashboard_summary_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new DashboardExport($stats), $filename);
    }
}
