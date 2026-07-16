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
                                  ->where('spouses.name', 'LIKE', "%{$keyword}%");
                          })
                          ->orWhereExists(function($sub) use ($keyword) {
                              $sub->select(\DB::raw(1))
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
                        $enrolleeDetails = $patient->enrolleeDetails; // This is an accessor
                        $fullname = $enrolleeDetails ? $enrolleeDetails->fullname : 'N/A';
                        $fileNumber = $patient->file_number ?? 'N/A';
                        
                        return "<div><strong>{$fullname}</strong></div>" .
                               "<small>File #: {$fileNumber}</small>";
                    }
                    return 'N/A';
                })
                ->addColumn('facility_info', function($referral) {
                    $fromName = $referral->fromFacility ? $referral->fromFacility->name : 'Unknown';
                    $toName = $referral->toFacility ? $referral->toFacility->name : 'Unknown';
                    
                    return "<div class='small text-muted'>From:</div>" .
                           "<strong>{$fromName}</strong>" .
                           "<div class='small text-muted'>To:</div>" .
                           "<strong>{$toName}</strong>";
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
                    $badges = $referral->status_badge;
                    
                    if ($referral->approval_status === 'approved') {
                        $badges .= ' <span class="badge bg-success mt-1">Approved</span>';
                    } elseif ($referral->approval_status === 'rejected') {
                        $badges .= ' <span class="badge bg-danger mt-1">Rejected</span>';
                    } else {
                        $badges .= ' <span class="badge bg-warning mt-1">Pending Approval</span>';
                    }
                    
                    return $badges;
                })
                ->addColumn('date', function($referral) {
                    return $referral->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function($referral) {
                    $actions = '<div class="btn-group">';
                    $actions .= '<a href="' . route('referrals.show', $referral->id) . '" class="btn btn-sm btn-info" title="View Details"><i class="ti-eye"></i></a>';
                    $actions .= '<a href="' . route('referrals.pdf', $referral->id) . '" class="btn btn-sm btn-primary" title="Download PDF" target="_blank"><i class="ti-download"></i></a>';
                    
                    if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin')) {
                        if ($referral->approval_status === 'pending') {
                            $actions .= '<button type="button" class="btn btn-sm btn-success" title="Approve" onclick="showApproveModal('.$referral->id.')"><i class="ti-check"></i></button>';
                            $actions .= '<button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal('.$referral->id.')"><i class="ti-close"></i></button>';
                        }
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['referral_info', 'patient_info', 'facility_info', 'reason', 'status_badge', 'action'])
                ->make(true);
        }

        // Get statistics for all referrals (admin sees everything)
        $stats = [
            'total' => ServiceReferral::count(),
            'accepted' => ServiceReferral::where('approval_status', 'approved')->count(),
            'completed' => ServiceReferral::where('status', '!=', 'pending')->count(),
            'pending' => ServiceReferral::where('approval_status', 'pending')->count(),
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

    public function approve(Request $request, $id)
    {
        $referral = ServiceReferral::findOrFail($id);
        $referral->approval_status = 'approved';
        $referral->approved_by = Auth::id();
        $referral->approved_at = now();
        $referral->save();

        return redirect()->back()->with('success', 'Referral approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $referral = ServiceReferral::findOrFail($id);
        $referral->approval_status = 'rejected';
        $referral->rejected_by = Auth::id();
        $referral->rejected_at = now();
        $referral->rejection_reason = $request->rejection_reason;
        $referral->save();

        return redirect()->back()->with('success', 'Referral rejected successfully.');
    }

    public function downloadPdf($id)
    {
        $referral = ServiceReferral::with([
            'encounter.patient',
            'fromFacility',
            'toFacility',
            'authorization',
            'encounter.consultations.diagnoses.icdCode',
        ])->findOrFail($id);

        $data = [
            'referral' => $referral,
        ];

        $pdf = app('dompdf.wrapper')->loadView('referrals._referral_pdf', $data);
        return $pdf->download("referral_{$id}.pdf");
    }
}