<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\ServiceReferral;
use App\Models\Encounter;
use App\Models\Facility;
use App\Models\ServiceItem;
use App\Models\Patient;
use App\Models\Authorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FacilityReferralController extends Controller
{
    /**
     * Show form to create referral from encounter
     */
    public function create($encounterId)
    {
        $facilityId = Auth::user()->facility_id;
        
        $encounter = Encounter::with(['patient', 'consultations.diagnoses.icdCode'])
            ->where('facility_id', $facilityId)
            ->findOrFail($encounterId);

        // Verify encounter status is eligible for referral
        $eligibleStatuses = ['Registered', 'Triaged', 'Consultation', 'Investigation', 'Admitted', 'Surgery'];
        if (!in_array($encounter->status, $eligibleStatuses)) {
            return redirect()->route('facility.encounters.index')
                ->with('error', 'This encounter status is not eligible for referral.');
        }

        // Get all facilities (including current facility for internal referrals)
        $facilities = Facility::orderBy('name')->get();

        // Get all service items
        $serviceItems = ServiceItem::orderBy('name')->get();

        $patient = $encounter->patient;
        $enrolleeDetails = $patient->enrolleeDetails;

        return view('facility.referrals.create', compact(
            'encounter',
            'patient',
            'enrolleeDetails',
            'facilities',
            'serviceItems'
        ));
    }

    /**
     * Store a newly created referral
     */
    public function store(Request $request)
    {
        $facilityId = Auth::user()->facility_id;

        $request->validate([
            'encounter_id' => 'required|exists:encounters,id',
            'to_facility_id' => 'required|exists:facilities,id',
            'referral_type' => 'required|in:service,patient',
            'service_item_id' => 'nullable|exists:service_items,id',
            'reason' => 'required|string|max:1000',
        ]);

        // Verify encounter belongs to this facility
        $encounter = Encounter::where('id', $request->encounter_id)
            ->where('facility_id', $facilityId)
            ->firstOrFail();

        // Create referral
        $referral = ServiceReferral::create([
            'encounter_id' => $request->encounter_id,
            'from_facility_id' => $facilityId,
            'to_facility_id' => $request->to_facility_id,
            'referral_type' => $request->referral_type,
            'service_item_id' => $request->service_item_id,
            'reason' => $request->reason,
            'status' => ServiceReferral::STATUS_ACCEPTED,
        ]);

        // Create authorization for the referral
        $authorization = Authorization::create([
            'authorization_code' => Authorization::generateCode(),
            'patient_id' => $encounter->patient_id,
            'encounter_id' => $request->encounter_id,
            'service_referral_id' => $referral->id,
            'approved_by' => Auth::id(), // Set to current user
            'expires_at' => now()->addDays(30), // Expires in 30 days
        ]);

        return redirect()->route('facility.referrals.show', $referral->id)
            ->with('success', 'Referral created successfully with authorization code: ' . $authorization->authorization_code);
    }

    /**
     * Display a listing of referrals
     */
    public function index()
    {
        $facilityId = Auth::user()->facility_id;
        
        if (request()->ajax()) {
            $referrals = ServiceReferral::with(['encounter.patient', 'fromFacility', 'toFacility', 'serviceItem', 'authorization'])
                ->where(function($query) use ($facilityId) {
                    $query->where('from_facility_id', $facilityId)
                          ->orWhere('to_facility_id', $facilityId);
                })
                ->when(request('program_id'), function ($q, $programId) {
                    $q->whereHas('encounter', function ($q) use ($programId) {
                        $q->where('program_id', $programId);
                    });
                })
                ->when(request('start_date'), function ($q, $startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                })
                ->when(request('end_date'), function ($q, $endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                })
                ->when(request('status'), function ($q, $status) {
                    // "rejected" can come from the admin approval flow (approval_status)
                    // or the facility lifecycle (status), so match either.
                    if ($status === 'rejected') {
                        $q->where(function ($q2) {
                            $q2->where('status', 'rejected')
                               ->orWhere('approval_status', 'rejected');
                        });
                    } else {
                        $q->where('status', $status);
                    }
                })
                ->when(request('direction'), function ($q, $direction) use ($facilityId) {
                    if ($direction === 'outgoing') {
                        $q->where('from_facility_id', $facilityId);
                    } elseif ($direction === 'incoming') {
                        $q->where('to_facility_id', $facilityId);
                    }
                })
                ->when(request('referral_type'), function ($q, $type) {
                    $q->where('referral_type', $type);
                })
                ->when(request('search'), function ($q, $search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->whereHas('authorization', function ($a) use ($search) {
                            $a->where('authorization_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('encounter.patient', function ($p) use ($search) {
                            $p->where('enrollee_number', 'like', "%{$search}%")
                              ->orWhere('file_number', 'like', "%{$search}%")
                              ->orWhereExists(function ($sub2) use ($search) {
                                  $sub2->select(DB::raw(1))
                                       ->from('beneficiaries')
                                       ->whereColumn('beneficiaries.boschma_no', 'patients.enrollee_number')
                                       ->where('beneficiaries.fullname', 'like', "%{$search}%");
                              })
                              ->orWhereExists(function ($sub2) use ($search) {
                                  $sub2->select(DB::raw(1))
                                       ->from('spouses')
                                       ->whereColumn('spouses.boschma_no', 'patients.enrollee_number')
                                       ->where('spouses.name', 'like', "%{$search}%");
                              })
                              ->orWhereExists(function ($sub2) use ($search) {
                                  $sub2->select(DB::raw(1))
                                       ->from('children')
                                       ->whereColumn('children.boschma_no', 'patients.enrollee_number')
                                       ->where('children.name', 'like', "%{$search}%");
                              });
                        });
                    });
                })
                ->orderBy('created_at', 'desc');

            return DataTables::of($referrals)
                ->filterColumn('encounter.patient.firstname', function($query, $keyword) {
                    $query->whereHas('encounter.patient', function($q) use ($keyword) {
                        $q->where('enrollee_number', 'LIKE', "%{$keyword}%")
                          ->orWhere('file_number', 'LIKE', "%{$keyword}%")
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(DB::raw(1))
                                  ->from('beneficiaries')
                                  ->whereColumn('beneficiaries.boschma_no', 'patients.enrollee_number')
                                  ->where('beneficiaries.fullname', 'LIKE', "%{$keyword}%");
                          })
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(DB::raw(1))
                                  ->from('spouses')
                                  ->whereColumn('spouses.boschma_no', 'patients.enrollee_number')
                                  ->where('spouses.name', 'LIKE', "%{$keyword}%");
                          })
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(DB::raw(1))
                                  ->from('children')
                                  ->whereColumn('children.boschma_no', 'patients.enrollee_number')
                                  ->where('children.name', 'LIKE', "%{$keyword}%");
                          });
                    });
                })
                ->addColumn('referral_info', function($referral) {
                    if ($referral->authorization) {
                        return "<div class='text-primary fw-bold'>" . $referral->authorization->authorization_code . "</div>" .
                               "<small class='text-success'>✓ Valid</small>";
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('patient_info', function($referral) {
                    if ($referral->encounter && $referral->encounter->patient) {
                        $patient = $referral->encounter->patient;
                        $enrolleeDetails = $patient->enrolleeDetails;
                        $fullname = $enrolleeDetails ? $enrolleeDetails->fullname : 'N/A';
                        $fileNumber = $patient->file_number ?? 'N/A';
                        
                        return "<div><strong>{$fullname}</strong></div>" .
                               "<small>File #: {$fileNumber}</small>";
                    }
                    return 'N/A';
                })
                ->addColumn('facility_info', function($referral) {
                    $isSender = $referral->from_facility_id == Auth::user()->facility_id;
                    $facility = $isSender ? $referral->toFacility : $referral->fromFacility;
                    $label = $isSender ? 'Referred To' : 'Referred From';
                    $facilityName = $facility ? $facility->name : 'Unknown Facility';
                    
                    return "<div class='small text-muted'>{$label}:</div>" .
                           "<strong>{$facilityName}</strong>";
                })
                ->addColumn('reason', function($referral) {
                    if ($referral->serviceItem) {
                        $serviceType = $referral->serviceItem->type ?? 'Service';
                        return "<div><strong>{$referral->serviceItem->name}</strong></div>" .
                               "<small class='text-muted'>{$serviceType}</small>";
                    }
                    return '<span class="text-muted">General Referral</span>';
                })
                ->addColumn('status_badge', function($referral) {
                    if ($referral->approval_status === 'rejected') {
                        return '<span class="badge bg-danger">Rejected</span>';
                    }

                    $badges = $referral->status_badge;

                    if ($referral->approval_status === 'approved') {
                        $badges .= ' <span class="badge bg-success mt-1">Approved</span>';
                    } else {
                        $badges .= ' <span class="badge bg-warning mt-1">Pending Approval</span>';
                    }

                    return $badges;
                })
                ->addColumn('date', function($referral) {
                    return $referral->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function($referral) {
                    return '<a href="' . route('facility.referrals.show', $referral->id) . '" 
                                class="btn btn-sm btn-info" title="View Details">
                                <i class="ti-eye"></i> View
                            </a>';
                })
                ->rawColumns(['referral_info', 'patient_info', 'facility_info', 'reason', 'status_badge', 'action'])
                ->make(true);
        }

        // Get statistics
        $facilityId = Auth::user()->facility_id;
        $stats = [
            'total' => ServiceReferral::where(function($q) use ($facilityId) {
                $q->where('from_facility_id', $facilityId)
                  ->orWhere('to_facility_id', $facilityId);
            })->count(),
            'outgoing' => ServiceReferral::where('from_facility_id', $facilityId)->count(),
            'incoming' => ServiceReferral::where('to_facility_id', $facilityId)->count(),
            'pending' => ServiceReferral::where(function($q) use ($facilityId) {
                $q->where('from_facility_id', $facilityId)
                  ->orWhere('to_facility_id', $facilityId);
            })->where('status', ServiceReferral::STATUS_PENDING)->count(),
        ];

        $programs = \App\Models\Program::active()->orderBy('name')->get();

        return view('facility.referrals.index', compact('stats', 'programs'));
    }

    /**
     * Display the specified referral
     */
    public function show($id)
    {
        $facilityId = Auth::user()->facility_id;
        
        $referral = ServiceReferral::with([
            'encounter.patient',
            'encounter.consultations.diagnoses.icdCode',
            'encounter.consultations.prescriptions.prescriptionItems.drug.dispensations',
            'encounter.actions',
            'encounter.serviceOrders.serviceOrderItems.serviceItem',
            'encounter.vitalSigns',
            'fromFacility',
            'toFacility',
            'serviceItem',
            'authorization'
        ])
        ->where(function($query) use ($facilityId) {
            $query->where('from_facility_id', $facilityId)
                  ->orWhere('to_facility_id', $facilityId);
        })
        ->findOrFail($id);

        $isOutgoing = $referral->from_facility_id == $facilityId;

        // Prepare encounter data similar to claims/show
        $encounter = $referral->encounter;
        $consultations = $encounter->consultations;
        $actions = $encounter->actions;
        $vitalSigns = $encounter->vitalSigns;
        
        // Get prescriptions and calculate pharmacy costs
        $prescriptions = $consultations->flatMap->prescriptions;
        $medications = [];
        $pharmacyTotal = 0;
        
        foreach ($prescriptions as $prescription) {
            foreach ($prescription->prescriptionItems as $item) {
                $dispensation = $item->dispensations->first();
                $drug = $item->drug;
                
                $status = 'pending';
                if ($dispensation) {
                    $status = $dispensation->status ?? 'pending';
                } elseif ($item->dispensing_status) {
                    $status = $item->dispensing_status;
                }
                
                $cost = 0;
                if ($status === 'dispensed') {
                    if ($dispensation && $dispensation->cost_of_medication > 0) {
                        $cost = $dispensation->cost_of_medication;
                    } elseif ($drug && $drug->unit_price > 0) {
                        $quantity = $item->quantity ?? 1;
                        $cost = $drug->unit_price * $quantity;
                    }
                }
                
                $medications[] = [
                    'item' => $item,
                    'drug' => $drug,
                    'cost' => $cost,
                    'dispensing_status' => $status
                ];
                
                if ($status === 'dispensed') {
                    $pharmacyTotal += $cost;
                }
            }
        }
        
        // Get service orders and calculate service costs
        $services = [];
        $laboratoryTests = [];
        $servicesTotal = 0;
        $labTotal = 0;
        
        foreach ($encounter->serviceOrders as $order) {
            foreach ($order->serviceOrderItems as $orderItem) {
                $serviceItem = $orderItem->serviceItem;
                $status = $orderItem->status ?? 'pending';
                
                $price = 0;
                if ($status === 'completed') {
                    $price = $serviceItem->price ?? 0;
                }
                
                $serviceData = [
                    'item' => $orderItem,
                    'service' => $serviceItem,
                    'price' => $price,
                    'status' => $status
                ];
                
                if (strtolower($serviceItem->category ?? '') === 'laboratory') {
                    $laboratoryTests[] = $serviceData;
                    if ($status === 'completed') {
                        $labTotal += $price;
                    }
                } else {
                    $services[] = $serviceData;
                    if ($status === 'completed') {
                        $servicesTotal += $price;
                    }
                }
            }
        }
        
        $totalAmount = $pharmacyTotal + $labTotal + $servicesTotal;

        return view('facility.referrals.show', compact(
            'referral', 
            'isOutgoing',
            'encounter',
            'consultations',
            'actions',
            'vitalSigns',
            'medications',
            'laboratoryTests',
            'services',
            'pharmacyTotal',
            'labTotal',
            'servicesTotal',
            'totalAmount'
        ));
    }

    /**
     * Update referral status (for incoming referrals)
     */
    public function updateStatus(Request $request, $id)
    {
        $facilityId = Auth::user()->facility_id;
        
        $referral = ServiceReferral::where('to_facility_id', $facilityId)
            ->findOrFail($id);

        // Prevent any action on referrals already rejected by the reviewer/admin
        if ($referral->approval_status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'This referral has been rejected and cannot be modified.'
            ], 422);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected,completed',
            'notes' => 'nullable|string'
        ]);

        $referral->status = $request->status;
        $referral->save();

        return response()->json([
            'success' => true,
            'message' => 'Referral status updated successfully'
        ]);
    }
}
