<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ServiceReferral;
use DataTables;
use Auth;

class ReferralController extends Controller
{
    /**
     * Display a listing of referrals.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $referrals = ServiceReferral::with(['encounter.patient', 'fromFacility', 'toFacility', 'serviceItem', 'authorization'])
                ->orderBy('created_at', 'desc');

            return DataTables::of($referrals)
                ->filterColumn('encounter.patient.firstname', function($query, $keyword) {
                    $query->whereHas('encounter.patient', function($q) use ($keyword) {
                        $q->where('enrollee_number', 'LIKE', "%{$keyword}%")
                          ->orWhere('file_number', 'LIKE', "%{$keyword}%")
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(\DB::raw(1))
                                  ->from('beneficiaries')
                                  ->whereColumn('beneficiaries.boschma_no', 'patients.enrollee_number')
                                  ->where('beneficiaries.fullname', 'LIKE', "%{$keyword}%");
                          })
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(\DB::raw(1))
                                  ->from('spouses')
                                  ->whereColumn('spouses.boschma_no', 'patients.enrollee_number')
                                  ->where('spouses.fullname', 'LIKE', "%{$keyword}%");
                          })
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(\DB::raw(1))
                                  ->from('children')
                                  ->whereColumn('children.boschma_no', 'patients.enrollee_number')
                                  ->where('children.fullname', 'LIKE', "%{$keyword}%");
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
                        $enrolleeDetails = $patient->enrolleeDetails; // This is an accessor
                        $fullname = $enrolleeDetails ? $enrolleeDetails->fullname : 'N/A';
                        $fileNumber = $patient->file_number ?? 'N/A';
                        
                        return "<div><strong>{$fullname}</strong></div>" .
                               "<small>File #: {$fileNumber}</small>";
                    }
                    return 'N/A';
                })
                ->addColumn('facility_info', function($referral) {
                    return "<div class='small text-muted'>From:</div>" .
                           "<strong>{$referral->fromFacility->name}</strong>" .
                           "<div class='small text-muted'>To:</div>" .
                           "<strong>{$referral->toFacility->name}</strong>";
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
                    return $referral->status_badge;
                })
                ->addColumn('date', function($referral) {
                    return $referral->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function($referral) {
                    return '<a href="' . route('referrals.show', $referral->id) . '" 
                                class="btn btn-sm btn-info" title="View Details">
                                <i class="ti-eye"></i> View
                            </a>';
                })
                ->rawColumns(['referral_info', 'patient_info', 'facility_info', 'reason', 'status_badge', 'action'])
                ->make(true);
        }

        // Get statistics for all referrals (admin sees everything)
        $stats = [
            'total' => ServiceReferral::count(),
            'outgoing' => ServiceReferral::count(), // All referrals for admin
            'incoming' => ServiceReferral::count(), // All referrals for admin  
            'pending' => ServiceReferral::where('status', ServiceReferral::STATUS_PENDING)->count(),
        ];

        return view('referrals.index', compact('stats'));
    }

    /**
     * Display the specified referral.
     */
    public function show($id)
    {
        $referral = ServiceReferral::with([
            'encounter.patient',
            'fromFacility', 
            'toFacility', 
            'serviceItem',
            'authorization',
            'encounter.consultations.diagnoses.icdCode',
            'encounter.consultations.prescriptions.prescriptionItems.drug',
            'encounter.vitalSigns',
            'encounter.actions',
            'encounter.serviceOrders.serviceOrderItems'
        ])->findOrFail($id);

        // Determine if this is an outgoing referral (from current user's facility if they have one)
        $isOutgoing = Auth::user()->facility_id ? 
            $referral->from_facility_id == Auth::user()->facility_id : 
            true; // Admin sees all as outgoing by default

        // Process encounter data for tabs
        $vitalSigns = $referral->encounter ? $referral->encounter->vitalSigns : collect([]);
        $consultations = $referral->encounter ? $referral->encounter->consultations : collect([]);
        $actions = $referral->encounter ? $referral->encounter->actions : collect([]);

        // Process medications (prescriptions)
        $medications = [];
        $pharmacyTotal = 0;
        if ($referral->encounter && $referral->encounter->consultations->count() > 0) {
            foreach ($referral->encounter->consultations as $consultation) {
                if ($consultation->prescriptions->count() > 0) {
                    foreach ($consultation->prescriptions as $prescription) {
                        if ($prescription->prescriptionItems->count() > 0) {
                            foreach ($prescription->prescriptionItems as $item) {
                                $cost = ($item->drug->price ?? 0) * ($item->quantity ?? 0);
                                $pharmacyTotal += $cost;
                                $medications[] = [
                                    'drug' => $item->drug,
                                    'item' => $item,
                                    'cost' => $cost,
                                    'dispensing_status' => $item->dispensing_status ?? 'pending'
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Process laboratory tests
        $laboratoryTests = [];
        $labTotal = 0;
        if ($referral->encounter && $referral->encounter->serviceOrders->count() > 0) {
            foreach ($referral->encounter->serviceOrders as $order) {
                if ($order->serviceItem && $order->serviceItem->type === 'Laboratory') {
                    $price = $order->serviceItem->price ?? 0;
                    $labTotal += $price;
                    $laboratoryTests[] = [
                        'service' => $order->serviceItem,
                        'order' => $order,
                        'price' => $price,
                        'status' => $order->status ?? 'pending'
                    ];
                }
            }
        }

        // Process other services
        $services = [];
        $servicesTotal = 0;
        if ($referral->encounter && $referral->encounter->serviceOrders->count() > 0) {
            foreach ($referral->encounter->serviceOrders as $order) {
                if ($order->serviceItem && $order->serviceItem->type !== 'Laboratory') {
                    $price = $order->serviceItem->price ?? 0;
                    $servicesTotal += $price;
                    $services[] = [
                        'service' => $order->serviceItem,
                        'order' => $order,
                        'price' => $price,
                        'status' => $order->status ?? 'pending'
                    ];
                }
            }
        }

        $totalAmount = $pharmacyTotal + $labTotal + $servicesTotal;

        return view('referrals.show', compact(
            'referral', 
            'isOutgoing',
            'vitalSigns',
            'consultations', 
            'actions',
            'medications',
            'laboratoryTests',
            'services',
            'pharmacyTotal',
            'labTotal',
            'servicesTotal',
            'totalAmount'
        ));
    }
}