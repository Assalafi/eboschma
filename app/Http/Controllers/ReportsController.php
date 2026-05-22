<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Facility;
use App\Models\Staff;
use App\Models\Spouse;
use App\Models\Child;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Exports\EnumeratorsExport;
use App\Exports\EnumeratorEnrollmentsExport;
use App\Exports\FacilitiesExport;
use App\Exports\FacilityEnrollmentsExport;
use App\Exports\DashboardExport;
use App\Exports\CrmExport;
use App\Exports\MonthlyEnrollmentsExport;
use App\Exports\CategoryEnrollmentsExport;
use App\Exports\StatusEnrollmentsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function enumerators(Request $request)
    {
        $programId = $request->get('program_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $programs = \App\Models\Program::orderBy('name')->get();

        // Build date range filter
        $dateFromSql = $dateFrom ? $dateFrom . ' 00:00:00' : null;
        $dateToSql = $dateTo ? $dateTo . ' 23:59:59' : null;

        // Get all enumerators with their enrollment counts, filtered by program and date range if specified
        $enumerators = Staff::role('Enumerator')
            ->withCount(['beneficiaries' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->where('status', '!=', 'draft');
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
                }
            }])
            ->withCount(['beneficiaries as main_facility_enrollments' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->where('status', '!=', 'draft')
                      ->whereNotNull('facility_id');
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
                }
            }])
            ->withCount(['beneficiaries as unique_facilities_count' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->where('status', '!=', 'draft')
                      ->whereNotNull('facility_id')
                      ->select(DB::raw('COUNT(DISTINCT facility_id)'));
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
                }
            }])
            ->orderBy('beneficiaries_count', 'desc')
            ->get(); // Get all results first to calculate unique facilities properly

        // Calculate unique facilities including spouses and children for each enumerator
        foreach ($enumerators as $enumerator) {
            // Get all facility IDs from beneficiaries for this enumerator
            $beneficiaryQuery = $enumerator->beneficiaries()
                ->where('status', '!=', 'draft')
                ->whereNotNull('facility_id');
            if ($programId) {
                $beneficiaryQuery->where('program_id', $programId);
            }
            if ($dateFromSql && $dateToSql) {
                $beneficiaryQuery->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
            }
            $beneficiaryFacilities = $beneficiaryQuery->pluck('facility_id');
            
            // Get spouses for beneficiaries created by this enumerator
            $spouseQuery = DB::table('spouses')
                ->join('beneficiaries', 'spouses.beneficiary_id', '=', 'beneficiaries.id')
                ->where('beneficiaries.created_by', $enumerator->id)
                ->whereNotNull('spouses.facility_id');
            if ($programId) {
                $spouseQuery->where('beneficiaries.program_id', $programId);
            }
            if ($dateFromSql && $dateToSql) {
                $spouseQuery->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
            }
            $spouseFacilities = $spouseQuery->pluck('spouses.facility_id');
            
            // Get children for beneficiaries created by this enumerator
            $childrenQuery = DB::table('children')
                ->join('beneficiaries', 'children.beneficiary_id', '=', 'beneficiaries.id')
                ->where('beneficiaries.created_by', $enumerator->id)
                ->whereNotNull('children.facility_id');
            if ($programId) {
                $childrenQuery->where('beneficiaries.program_id', $programId);
            }
            if ($dateFromSql && $dateToSql) {
                $childrenQuery->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
            }
            $childrenFacilities = $childrenQuery->pluck('children.facility_id');
            
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
        $query = [];
        if ($programId) $query['program_id'] = $programId;
        if ($dateFrom) $query['date_from'] = $dateFrom;
        if ($dateTo) $query['date_to'] = $dateTo;
        $paginatedEnumerators = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $enumerators->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => $query,
            ]
        );

        $selectedProgram = $programId ? \App\Models\Program::find($programId) : null;

        return view('reports.enumerators', [
            'enumerators' => $paginatedEnumerators,
            'programs' => $programs,
            'programId' => $programId,
            'selectedProgram' => $selectedProgram,
        ]);
    }

    public function exportEnumerators(Request $request)
    {
        // Check if exporting specific enumerator or all
        $enumeratorId = $request->get('enumerator_id');
        $programId = $request->get('program_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $program = $programId ? \App\Models\Program::find($programId) : null;
        $programSuffix = $program ? '_' . str_replace(' ', '_', $program->name) : '';
        $dateSuffix = '';
        if ($dateFrom || $dateTo) {
            $dateSuffix = '_' . ($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end');
        }
        
        $filename = 'enumerator_performance' . $programSuffix . $dateSuffix . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new EnumeratorsExport($enumeratorId, $programId, $dateFrom, $dateTo), $filename);
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

    public function facilityEnrollments(Request $request, $id)
    {
        $programId = $request->get('program_id');
        $gender = $request->get('gender');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $programs = \App\Models\Program::orderBy('name')->get();

        // Build date range filter
        $dateFromSql = $dateFrom ? $dateFrom . ' 00:00:00' : null;
        $dateToSql = $dateTo ? $dateTo . ' 23:59:59' : null;

        // Get the facility
        $facility = Facility::findOrFail($id);
        
        // Create combined enrollments list with categories
        $enrollments = collect();
        
        // Get all beneficiaries for this facility
        $beneficiariesQuery = Beneficiary::where('facility_id', $id)
            ->with('creator')
            ->where('status', '!=', 'draft');

        if ($programId) {
            $beneficiariesQuery->where('program_id', $programId);
        }
        if ($gender) {
            $beneficiariesQuery->where('gender', $gender);
        }
        if ($dateFromSql && $dateToSql) {
            $beneficiariesQuery->whereBetween('created_at', [$dateFromSql, $dateToSql]);
        }
            
        $beneficiaries = $beneficiariesQuery->orderBy('created_at', 'desc')->get();

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
        $spousesQuery = \App\Models\Spouse::where('facility_id', $id)
            ->with('beneficiary.creator');

        if ($gender) {
            $spousesQuery->where('gender', $gender);
        }

        if ($programId || ($dateFromSql && $dateToSql)) {
            $spousesQuery->whereHas('beneficiary', function($q) use ($programId, $dateFromSql, $dateToSql) {
                if ($programId) $q->where('program_id', $programId);
                if ($dateFromSql && $dateToSql) $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
            });
        }
            
        $spouses = $spousesQuery->orderBy('created_at', 'desc')->get();

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
        $childrenQuery = \App\Models\Child::where('facility_id', $id)
            ->with('beneficiary.creator');

        if ($gender) {
            $childrenQuery->where('gender', $gender);
        }

        if ($programId || ($dateFromSql && $dateToSql)) {
            $childrenQuery->whereHas('beneficiary', function($q) use ($programId, $dateFromSql, $dateToSql) {
                if ($programId) $q->where('program_id', $programId);
                if ($dateFromSql && $dateToSql) $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
            });
        }
            
        $children = $childrenQuery->orderBy('created_at', 'desc')->get();

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
        
        $query = [];
        if ($programId) $query['program_id'] = $programId;
        if ($gender) $query['gender'] = $gender;
        if ($dateFrom) $query['date_from'] = $dateFrom;
        if ($dateTo) $query['date_to'] = $dateTo;
        
        $paginatedEnrollments = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $enrollments->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => $query,
            ]
        );

        $selectedProgram = $programId ? \App\Models\Program::find($programId) : null;

        return view('reports.facility-enrollments', compact('facility', 'enrollments', 'paginatedEnrollments', 'programs', 'programId', 'selectedProgram'));
    }

    public function exportFacilities(Request $request)
    {
        // Check if exporting specific facility or all
        $facilityId = $request->get('facility_id');
        $programId = $request->get('program_id');
        $lga = $request->get('lga');
        $gender = $request->get('gender');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $program = $programId ? \App\Models\Program::find($programId) : null;
        $programSuffix = $program ? '_' . str_replace(' ', '_', $program->name) : '';
        $lgaSuffix = $lga ? '_' . str_replace(' ', '_', $lga) : '';
        $genderSuffix = $gender ? '_' . str_replace(' ', '_', $gender) : '';
        $dateSuffix = '';
        if ($dateFrom || $dateTo) {
            $dateSuffix = '_' . ($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end');
        }
        
        if ($facilityId) {
            // Export specific facility enrollments (detailed records)
            $facility = Facility::findOrFail($facilityId);
            $filename = $facility->name . $programSuffix . $genderSuffix . $dateSuffix . '_enrollments_' . date('Y-m_d_H-i-s') . '.xlsx';
            return Excel::download(new FacilityEnrollmentsExport($facilityId, $facility->name, $programId, $dateFrom, $dateTo, $gender), $filename);
        } else {
            // Export all facilities (summary data)
            $filename = 'facility_performance' . $programSuffix . $lgaSuffix . $dateSuffix . '_' . date('Y-m_d_H-i-s') . '.xlsx';
            return Excel::download(new FacilitiesExport($programId, $lga, $dateFrom, $dateTo), $filename);
        }
    }

    public function facilities(Request $request)
    {
        $programId = $request->get('program_id');
        $lga = $request->get('lga');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $programs = \App\Models\Program::orderBy('name')->get();

        // Get unique LGAs from facilities
        $lgas = Facility::whereNotNull('lga')->distinct()->pluck('lga')->sort();

        // Build date range filter
        $dateFromSql = $dateFrom ? $dateFrom . ' 00:00:00' : null;
        $dateToSql = $dateTo ? $dateTo . ' 23:59:59' : null;

        // Start with facilities query
        $facilitiesQuery = Facility::query();

        // Apply LGA filter if specified
        if ($lga) {
            $facilitiesQuery->where('lga', $lga);
        }

        // Get all facilities with their enrollment counts, filtered by program and date range if specified
        $facilities = $facilitiesQuery->withCount(['beneficiaries' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->where('status', '!=', 'draft');
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
                }
            }])
            ->withCount(['beneficiaries as main_facility_enrollments' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->where('status', '!=', 'draft')
                      ->whereColumn('facility_id', 'facilities.id');
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereBetween('beneficiaries.created_at', [$dateFromSql, $dateToSql]);
                }
            }])
            ->withCount(['spouses' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->whereColumn('facility_id', 'facilities.id');
                if ($programId) {
                    $query->whereHas('beneficiary', function($q) use ($programId) {
                        $q->where('program_id', $programId);
                    });
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereHas('beneficiary', function($q) use ($dateFromSql, $dateToSql) {
                        $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
                    });
                }
            }])
            ->withCount(['children' => function($query) use ($programId, $dateFromSql, $dateToSql) {
                $query->whereColumn('facility_id', 'facilities.id');
                if ($programId) {
                    $query->whereHas('beneficiary', function($q) use ($programId) {
                        $q->where('program_id', $programId);
                    });
                }
                if ($dateFromSql && $dateToSql) {
                    $query->whereHas('beneficiary', function($q) use ($dateFromSql, $dateToSql) {
                        $q->whereBetween('created_at', [$dateFromSql, $dateToSql]);
                    });
                }
            }])
            ->orderBy('beneficiaries_count', 'desc')
            ->paginate(20);

        // Append query parameters to pagination links
        $query = [];
        if ($programId) $query['program_id'] = $programId;
        if ($lga) $query['lga'] = $lga;
        if ($dateFrom) $query['date_from'] = $dateFrom;
        if ($dateTo) $query['date_to'] = $dateTo;
        $facilities->appends($query);

        // Calculate total enrollments for each facility (beneficiaries + spouses + children)
        foreach ($facilities as $facility) {
            $facility->total_enrollments = $facility->beneficiaries_count + $facility->spouses_count + $facility->children_count;
        }

        $selectedProgram = $programId ? \App\Models\Program::find($programId) : null;

        return view('reports.facilities', compact('facilities', 'programs', 'lgas', 'programId', 'selectedProgram'));
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

    public function exportEnrollments()
    {
        $filename = 'all_enrollments_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new CategoryEnrollmentsExport('all'), $filename);
    }

    public function exportMonthlyEnrollments($month)
    {
        $filename = 'enrollments_' . $month . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new MonthlyEnrollmentsExport($month), $filename);
    }

    public function exportCategoryEnrollments($category)
    {
        $filename = $category . '_enrollments_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new CategoryEnrollmentsExport($category), $filename);
    }

    public function exportStatusEnrollments($status)
    {
        $filename = $status . '_enrollments_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new StatusEnrollmentsExport($status), $filename);
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

    public function crm(Request $request)
    {
        // Build query with filters
        $query = Ticket::with(['category', 'assignedUser', 'facility', 'createdBy', 'replies']);

        // Date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('ticket_category_id', $request->category);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Date range filters
        if ($request->filled('assigned_date_from')) {
            $query->whereDate('created_at', '>=', $request->assigned_date_from);
        }

        if ($request->filled('assigned_date_to')) {
            $query->whereDate('created_at', '<=', $request->assigned_date_to);
        }

        if ($request->filled('resolved_date_from')) {
            $query->whereDate('resolved_at', '>=', $request->resolved_date_from);
        }

        if ($request->filled('resolved_date_to')) {
            $query->whereDate('resolved_at', '<=', $request->resolved_date_to);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_tickets' => $tickets->count(),
            'resolved_tickets' => $tickets->where('status', 'completed')->count(),
            'pending_tickets' => $tickets->where('status', 'pending')->count(),
            'avg_resolution_time' => $tickets->where('status', 'completed')
                ->whereNotNull('resolved_at')
                ->avg(function($ticket) {
                    return $ticket->created_at->diffInHours($ticket->resolved_at);
                })
        ];

        // Format average resolution time
        if ($stats['avg_resolution_time']) {
            $stats['avg_resolution_time'] = round($stats['avg_resolution_time'], 1) . 'h';
        } else {
            $stats['avg_resolution_time'] = 'N/A';
        }

        // Category statistics
        $categoryStats = Ticket::join('ticket_categories', 'tickets.ticket_category_id', '=', 'ticket_categories.id')
            ->select('ticket_categories.name', DB::raw('count(*) as count'))
            ->groupBy('ticket_categories.name')
            ->orderBy('count', 'desc')
            ->get();

        // Status statistics
        $statusStats = $tickets->groupBy('status')
            ->map(function($group, $status) {
                return [
                    'status' => ucfirst(str_replace('_', ' ', $status)),
                    'count' => $group->count()
                ];
            })->values();

        // Department statistics
        $departmentStats = $tickets->groupBy('department')
            ->map(function($group, $department) {
                return [
                    'department' => $department ?: 'N/A',
                    'count' => $group->count()
                ];
            })->sortByDesc('count')->values();

        // Get all categories for filter dropdown
        $categories = TicketCategory::orderBy('name')->get();

        return view('reports.crm', compact(
            'tickets',
            'stats',
            'categoryStats',
            'statusStats',
            'departmentStats',
            'categories'
        ));
    }

    public function crmExport(Request $request)
    {
        // Collect all filters for the export
        $filters = [
            'date_range' => $request->get('date_range'),
            'status' => $request->get('status'),
            'category' => $request->get('category'),
            'priority' => $request->get('priority'),
            'department' => $request->get('department'),
            'assigned_date_from' => $request->get('assigned_date_from'),
            'assigned_date_to' => $request->get('assigned_date_to'),
            'resolved_date_from' => $request->get('resolved_date_from'),
            'resolved_date_to' => $request->get('resolved_date_to'),
        ];

        // Remove empty values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $filename = 'crm_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new CrmExport($filters), $filename);
    }

    public function crmTicketDetail($ticket)
    {
        // Get ticket with all relationships and replies
        $ticket = Ticket::with([
            'category', 
            'assignedUser', 
            'facility', 
            'createdBy',
            'replies' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            }
        ])->findOrFail($ticket);

        return view('reports.crm-ticket-detail', compact('ticket'));
    }

    public function crmCategoryBreakdown(Request $request)
    {
        $query = Ticket::with(['category', 'assignedUser', 'facility', 'replies']);
        
        // Apply category filter
        if ($request->filled('category')) {
            $query->where('ticket_category_id', $request->category);
        }
        
        // Apply other filters
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->get();
        $category = TicketCategory::find($request->category);
        
        return view('reports.crm-category-breakdown', compact('tickets', 'category'));
    }

    public function crmStatusBreakdown(Request $request)
    {
        $query = Ticket::with(['category', 'assignedUser', 'facility', 'replies']);
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply other filters
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        if ($request->filled('category')) {
            $query->where('ticket_category_id', $request->category);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->get();
        $status = $request->status ? ucfirst(str_replace('_', ' ', $request->status)) : 'All Status';
        
        return view('reports.crm-status-breakdown', compact('tickets', 'status'));
    }

    public function crmDepartmentBreakdown(Request $request)
    {
        $query = Ticket::with(['category', 'assignedUser', 'facility', 'replies']);
        
        // Apply department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        // Apply other filters
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('ticket_category_id', $request->category);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->get();
        $department = $request->department;
        
        return view('reports.crm-department-breakdown', compact('tickets', 'department'));
    }

    public function crmPrint(Request $request)
    {
        // Check if this is individual ticket print
        if ($request->filled('ticket_id')) {
            return $this->crmPrintTicket($request);
        }

        // Build query with filters (same as crm method)
        $query = Ticket::with(['category', 'assignedUser', 'facility', 'createdBy', 'replies']);

        // Date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Assigned date range filter
        if ($request->filled('assigned_date_from')) {
            $query->whereDate('assigned_date', '>=', $request->assigned_date_from);
        }
        if ($request->filled('assigned_date_to')) {
            $query->whereDate('assigned_date', '<=', $request->assigned_date_to);
        }

        // Resolved date range filter
        if ($request->filled('resolved_date_from')) {
            $query->whereDate('resolved_at', '>=', $request->resolved_date_from);
        }
        if ($request->filled('resolved_date_to')) {
            $query->whereDate('resolved_at', '<=', $request->resolved_date_to);
        }

        // Other filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('ticket_category_id', $request->category);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        // Calculate stats
        $stats = [
            'total_tickets' => $tickets->count(),
            'completed_tickets' => $tickets->where('status', 'completed')->count(),
            'in_progress_tickets' => $tickets->where('status', 'in_progress')->count(),
            'pending_tickets' => $tickets->where('status', 'pending')->count(),
            'high_priority_tickets' => $tickets->where('priority', 'high')->count(),
            'assigned_tickets' => $tickets->whereNotNull('assigned_to')->count(),
            'avg_resolution_time' => $tickets->where('status', 'completed')->whereNotNull('resolved_at')->avg(function($ticket) {
                return $ticket->created_at->diffInHours($ticket->resolved_at);
            })
        ];

        // Category stats
        $categoryStats = $tickets->groupBy('category.name')->map(function($categoryTickets) {
            return $categoryTickets->count();
        })->sortDesc();

        // Status stats
        $statusStats = $tickets->groupBy('status')->map(function($statusTickets) {
            return $statusTickets->count();
        });

        // Department stats
        $departmentStats = $tickets->groupBy('department')->map(function($deptTickets) {
            return $deptTickets->count();
        })->sortDesc();

        // Determine view based on filters
        if ($request->filled('status')) {
            $view = 'reports.prints.crm-status-print';
            $title = 'CRM Status Report - ' . ucfirst(str_replace('_', ' ', $request->status));
        } elseif ($request->filled('category')) {
            $view = 'reports.prints.crm-category-print';
            $category = \App\Models\TicketCategory::find($request->category);
            $title = 'CRM Category Report - ' . ($category->name ?? 'Unknown Category');
        } elseif ($request->filled('department')) {
            $view = 'reports.prints.crm-department-print';
            $title = 'CRM Department Report - ' . $request->department;
        } else {
            $view = 'reports.prints.crm-print';
            $title = 'CRM Report';
        }

        // Generate PDF
        $pdf = PDF::loadView($view, compact(
            'tickets', 
            'stats', 
            'categoryStats', 
            'statusStats', 
            'departmentStats',
            'request'
        ));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Set filename based on filters
        $filename = str_replace(' ', '_', strtolower($title)) . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Download PDF
        return $pdf->stream($filename);
    }

    private function crmPrintTicket(Request $request)
    {
        // Get individual ticket with replies
        $ticket = Ticket::with([
            'category', 
            'assignedUser', 
            'facility', 
            'createdBy',
            'replies' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            }
        ])->findOrFail($request->ticket_id);

        // Generate PDF
        $pdf = PDF::loadView('reports.prints.crm-ticket-print', compact('ticket'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Set filename
        $filename = 'crm_ticket_' . $ticket->ticket_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Download PDF
        return $pdf->stream($filename);
    }
}
