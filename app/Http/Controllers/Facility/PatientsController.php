<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PatientsController extends Controller
{
    /**
     * Display patients and beneficiaries for the facility.
     * Supports two tabs: 'patients' (default) and 'beneficiaries'.
     */
    public function index(Request $request)
    {
        $facilityId = Auth::guard('web')->user()->facility_id;
        $search = $request->get('search', '');
        $enrolleeType = $request->get('enrollee_type', '');
        $tab = $request->get('tab', 'patients');

        // Always compute stats for both tabs
        $stats = [
            // Patient counts (from patients table, scoped by facility)
            'total_patients' => DB::table('patients')
                ->where(function($q) use ($facilityId) {
                    $q->whereExists(function($sub) use ($facilityId) {
                        $sub->select(DB::raw(1))->from('beneficiaries')
                            ->whereRaw('beneficiaries.boschma_no = patients.enrollee_number')
                            ->where('beneficiaries.facility_id', $facilityId);
                    })->orWhereExists(function($sub) use ($facilityId) {
                        $sub->select(DB::raw(1))->from('spouses')
                            ->whereRaw('spouses.boschma_no = patients.enrollee_number')
                            ->where('spouses.facility_id', $facilityId);
                    })->orWhereExists(function($sub) use ($facilityId) {
                        $sub->select(DB::raw(1))->from('children')
                            ->whereRaw('children.boschma_no = patients.enrollee_number')
                            ->where('children.facility_id', $facilityId);
                    });
                })->count(),
            // Beneficiary counts (from enrollment tables)
            'total_beneficiaries' => DB::table('beneficiaries')
                ->where('facility_id', $facilityId)
                ->where('status', '!=', 'draft')
                ->count(),
            'total_children' => DB::table('children')
                ->where('facility_id', $facilityId)
                ->count(),
            'total_spouses' => DB::table('spouses')
                ->where('facility_id', $facilityId)
                ->count(),
        ];

        if ($tab === 'beneficiaries') {
            return $this->beneficiariesTab($request, $facilityId, $stats, $search, $enrolleeType);
        }

        return $this->patientsTab($request, $facilityId, $stats, $search, $enrolleeType);
    }

    /**
     * Patients tab: show records from the patients table joined with enrollee details.
     */
    private function patientsTab(Request $request, $facilityId, $stats, $search, $enrolleeType)
    {
        $query = DB::table('patients')
            ->leftJoin('beneficiaries', function($join) {
                $join->on('patients.enrollee_number', '=', 'beneficiaries.boschma_no');
            })
            ->leftJoin('children', function($join) {
                $join->on('patients.enrollee_number', '=', 'children.boschma_no');
            })
            ->leftJoin('spouses', function($join) {
                $join->on('patients.enrollee_number', '=', 'spouses.boschma_no');
            })
            ->select(
                'patients.id',
                'patients.file_number',
                'patients.enrollee_number',
                'patients.enrollee_type',
                'patients.created_at',
                DB::raw("COALESCE(beneficiaries.fullname, spouses.name, children.name) as name"),
                DB::raw("COALESCE(beneficiaries.gender, spouses.gender, children.gender) as gender"),
                DB::raw("COALESCE(beneficiaries.date_of_birth, spouses.dob, children.dob) as dob"),
                DB::raw("COALESCE(beneficiaries.phone_no, spouses.phone) as phone"),
                DB::raw("COALESCE(beneficiaries.photo, spouses.photo, children.photo) as photo")
            )
            ->where(function($q) use ($facilityId) {
                $q->where('beneficiaries.facility_id', $facilityId)
                  ->orWhere('spouses.facility_id', $facilityId)
                  ->orWhere('children.facility_id', $facilityId);
            });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('patients.file_number', 'LIKE', "%{$search}%")
                  ->orWhere('patients.enrollee_number', 'LIKE', "%{$search}%")
                  ->orWhere('beneficiaries.fullname', 'LIKE', "%{$search}%")
                  ->orWhere('spouses.name', 'LIKE', "%{$search}%")
                  ->orWhere('children.name', 'LIKE', "%{$search}%");
            });
        }

        if ($enrolleeType) {
            $query->where('patients.enrollee_type', $enrolleeType);
        }

        $patients = $query->orderBy('patients.created_at', 'desc')->paginate(15);

        $patients->getCollection()->transform(function ($patient) {
            $patient->created_at = \Carbon\Carbon::parse($patient->created_at);
            return $patient;
        });

        $tab = 'patients';
        return view('facility.patients.index', compact('patients', 'stats', 'search', 'enrolleeType', 'tab'));
    }

    /**
     * Beneficiaries tab: show enrolled beneficiaries/spouses/children from enrollment tables.
     */
    private function beneficiariesTab(Request $request, $facilityId, $stats, $search, $enrolleeType)
    {
        $beneficiariesQuery = DB::table('beneficiaries')
            ->where('facility_id', $facilityId)
            ->where('status', '!=', 'draft')
            ->select(
                'id',
                'boschma_no as enrollee_number',
                'fullname as name',
                'gender',
                'date_of_birth as dob',
                'phone_no as phone',
                'photo',
                'created_at',
                DB::raw("'beneficiary' as enrollee_type")
            );

        $spousesQuery = DB::table('spouses')
            ->where('facility_id', $facilityId)
            ->select(
                'id',
                'boschma_no as enrollee_number',
                'name',
                'gender',
                'dob',
                'phone',
                'photo',
                'created_at',
                DB::raw("'spouse' as enrollee_type")
            );

        $childrenQuery = DB::table('children')
            ->where('facility_id', $facilityId)
            ->select(
                'id',
                'boschma_no as enrollee_number',
                'name',
                'gender',
                'dob',
                DB::raw("NULL as phone"),
                'photo',
                'created_at',
                DB::raw("'child' as enrollee_type")
            );

        if ($enrolleeType === 'beneficiary') {
            $query = $beneficiariesQuery;
        } elseif ($enrolleeType === 'spouse') {
            $query = $spousesQuery;
        } elseif ($enrolleeType === 'child') {
            $query = $childrenQuery;
        } else {
            $query = $beneficiariesQuery->unionAll($spousesQuery)->unionAll($childrenQuery);
        }

        $wrappedQuery = DB::table(DB::raw("({$query->toSql()}) as enrollees"))
            ->mergeBindings($query);

        if ($search) {
            $wrappedQuery->where(function($q) use ($search) {
                $q->where('enrollees.name', 'LIKE', "%{$search}%")
                  ->orWhere('enrollees.enrollee_number', 'LIKE', "%{$search}%")
                  ->orWhere('enrollees.phone', 'LIKE', "%{$search}%");
            });
        }

        $patients = $wrappedQuery->orderBy('enrollees.created_at', 'desc')->paginate(15);

        $patients->getCollection()->transform(function ($patient) {
            $patient->created_at = \Carbon\Carbon::parse($patient->created_at);
            return $patient;
        });

        $tab = 'beneficiaries';
        return view('facility.patients.index', compact('patients', 'stats', 'search', 'enrolleeType', 'tab'));
    }

    /**
     * Display the specified patient details (from patients table).
     */
    public function show($id)
    {
        $facilityId = Auth::guard('web')->user()->facility_id;

        $patient = DB::table('patients')
            ->leftJoin('beneficiaries', function($join) {
                $join->on('patients.enrollee_number', '=', 'beneficiaries.boschma_no');
            })
            ->leftJoin('children', function($join) {
                $join->on('patients.enrollee_number', '=', 'children.boschma_no');
            })
            ->leftJoin('spouses', function($join) {
                $join->on('patients.enrollee_number', '=', 'spouses.boschma_no');
            })
            ->leftJoin('facilities', function($join) {
                $join->on('facilities.id', '=', DB::raw("COALESCE(beneficiaries.facility_id, spouses.facility_id, children.facility_id)"));
            })
            ->leftJoin('programs', 'beneficiaries.program_id', '=', 'programs.id')
            ->select(
                'patients.*',
                'beneficiaries.fullname',
                'beneficiaries.gender as beneficiary_gender',
                'beneficiaries.date_of_birth',
                'beneficiaries.phone_no',
                'beneficiaries.email',
                'beneficiaries.photo as beneficiary_photo',
                'beneficiaries.nin',
                'beneficiaries.id_type',
                'beneficiaries.id_no',
                'beneficiaries.dp_no',
                'beneficiaries.place_of_birth',
                'beneficiaries.marital_status',
                'beneficiaries.nationality',
                'beneficiaries.religion',
                'beneficiaries.contact_address',
                'beneficiaries.place_of_work',
                'beneficiaries.occupation',
                'beneficiaries.lga as beneficiary_lga',
                'beneficiaries.state as beneficiary_state',
                'beneficiaries.date_of_employment',
                'beneficiaries.date_of_retirement',
                'beneficiaries.id as beneficiary_record_id',
                'spouses.name as spouse_name',
                'spouses.gender as spouse_gender',
                'spouses.dob as spouse_dob',
                'spouses.phone as spouse_phone',
                'spouses.photo as spouse_photo',
                'spouses.nin as spouse_nin',
                'children.name as child_name',
                'children.gender as child_gender',
                'children.dob as child_dob',
                'children.photo as child_photo',
                'children.birth_certificate_no',
                'children.birth_certificate_file',
                'children.nin as child_nin',
                'facilities.name as facility_name',
                'programs.name as program_name'
            )
            ->where('patients.id', $id)
            ->where(function($q) use ($facilityId) {
                $q->where('beneficiaries.facility_id', $facilityId)
                  ->orWhere('spouses.facility_id', $facilityId)
                  ->orWhere('children.facility_id', $facilityId);
            })
            ->first();

        if (!$patient) {
            abort(404);
        }

        $patient->created_at = \Carbon\Carbon::parse($patient->created_at);
        $patient->updated_at = \Carbon\Carbon::parse($patient->updated_at);

        // Get family members if this is a beneficiary patient
        $familyMembers = [];
        if ($patient->enrollee_type === 'beneficiary' && $patient->beneficiary_record_id) {
            $spouse = DB::table('spouses')->where('beneficiary_id', $patient->beneficiary_record_id)->first();
            $children = DB::table('children')->where('beneficiary_id', $patient->beneficiary_record_id)->get();

            if ($spouse) {
                $familyMembers[] = [
                    'type' => 'spouse', 'name' => $spouse->name, 'boschma_no' => $spouse->boschma_no,
                    'gender' => $spouse->gender, 'dob' => $spouse->dob, 'photo' => $spouse->photo
                ];
            }
            foreach ($children as $child) {
                $familyMembers[] = [
                    'type' => 'child', 'name' => $child->name, 'boschma_no' => $child->boschma_no,
                    'gender' => $child->gender, 'dob' => $child->dob, 'photo' => $child->photo
                ];
            }
        }

        return view('facility.patients.show', compact('patient', 'familyMembers'));
    }

    /**
     * Display beneficiary details (from enrollment tables, not patients table).
     */
    public function showBeneficiary(Request $request, $id)
    {
        $facilityId = Auth::guard('web')->user()->facility_id;
        $type = $request->get('type', 'beneficiary');

        $patient = null;
        $familyMembers = [];

        if ($type === 'beneficiary') {
            $patient = DB::table('beneficiaries')
                ->leftJoin('facilities', 'beneficiaries.facility_id', '=', 'facilities.id')
                ->leftJoin('programs', 'beneficiaries.program_id', '=', 'programs.id')
                ->select('beneficiaries.*', 'facilities.name as facility_name', 'programs.name as program_name')
                ->where('beneficiaries.id', $id)
                ->where('beneficiaries.facility_id', $facilityId)
                ->first();

            if ($patient) {
                $patient->enrollee_type = 'beneficiary';
                $patient->enrollee_number = $patient->boschma_no;

                $spouse = DB::table('spouses')->where('beneficiary_id', $patient->id)->first();
                $children = DB::table('children')->where('beneficiary_id', $patient->id)->get();

                if ($spouse) {
                    $familyMembers[] = [
                        'type' => 'spouse', 'name' => $spouse->name, 'boschma_no' => $spouse->boschma_no,
                        'gender' => $spouse->gender, 'dob' => $spouse->dob, 'photo' => $spouse->photo
                    ];
                }
                foreach ($children as $child) {
                    $familyMembers[] = [
                        'type' => 'child', 'name' => $child->name, 'boschma_no' => $child->boschma_no,
                        'gender' => $child->gender, 'dob' => $child->dob, 'photo' => $child->photo
                    ];
                }
            }
        } elseif ($type === 'spouse') {
            $patient = DB::table('spouses')
                ->leftJoin('facilities', 'spouses.facility_id', '=', 'facilities.id')
                ->select('spouses.*', 'facilities.name as facility_name')
                ->where('spouses.id', $id)
                ->where('spouses.facility_id', $facilityId)
                ->first();

            if ($patient) {
                $patient->enrollee_type = 'spouse';
                $patient->enrollee_number = $patient->boschma_no;
                $patient->program_name = null;
            }
        } elseif ($type === 'child') {
            $patient = DB::table('children')
                ->leftJoin('facilities', 'children.facility_id', '=', 'facilities.id')
                ->select('children.*', 'facilities.name as facility_name')
                ->where('children.id', $id)
                ->where('children.facility_id', $facilityId)
                ->first();

            if ($patient) {
                $patient->enrollee_type = 'child';
                $patient->enrollee_number = $patient->boschma_no;
                $patient->program_name = null;
            }
        }

        if (!$patient) {
            abort(404);
        }

        $patient->created_at = \Carbon\Carbon::parse($patient->created_at);
        $patient->updated_at = \Carbon\Carbon::parse($patient->updated_at);

        return view('facility.patients.show-beneficiary', compact('patient', 'familyMembers'));
    }
}
