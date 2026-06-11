<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Models\FacilityClaim;
use App\Models\FacilityClaimMedication;
use App\Models\FacilityClaimService;
use App\Models\ClinicalConsultation;
use App\Models\ClinicalDiagnosis;
use App\Models\EncounterAction;
use App\Models\Patient;
use App\Models\PharmacyDispensation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Admission;
use App\Models\ServiceReferral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class FacilityClaimController extends Controller
{
    /**
     * Display encounter details
     */
    public function showEncounter($id)
    {
        $facilityId = Auth::user()->facility_id;
        
        $encounter = Encounter::with([
            'patient',
            'consultations.diagnoses.icdCode',
            'consultations.prescriptions.prescriptionItems.drug.dispensations',
            'actions',
            'serviceOrders.serviceOrderItems.serviceItem',
            'vitalSigns'
        ])
        ->where('facility_id', $facilityId)
        ->findOrFail($id);

        // Get patient details
        $patient = $encounter->patient;
        $enrolleeDetails = $patient->enrolleeDetails;

        // Prepare encounter data
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
                //dd($dispensation);
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

        return view('facility.encounters.show', compact(
            'encounter',
            'patient',
            'enrolleeDetails',
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
     * Display list of completed encounters ready for claims
     */
    public function index()
    {
        $facilityId = Auth::user()->facility_id;
        
        if (request()->ajax()) {
            $encounters = Encounter::with(['patient', 'consultations', 'serviceOrders'])
                ->where('facility_id', $facilityId)
                ->orderBy('visit_date', 'desc');

            return DataTables::of($encounters)
                ->addColumn('patient_info', function($encounter) {
                    $patient = $encounter->patient;
                    if (!$patient) return 'N/A';
                    
                    $enrolleeDetails = $patient->enrolleeDetails;
                    $fullname = $enrolleeDetails ? $enrolleeDetails->fullname : 'N/A';
                    
                    return "<strong>{$patient->enrollee_number}</strong><br>
                            <small>{$fullname}</small>";
                })
                ->addColumn('visit_info', function($encounter) {
                    return $encounter->visit_date->format('d M Y') . '<br>' .
                           '<small>' . $encounter->nature_of_visit . '</small>';
                })
                ->addColumn('status_badge', function($encounter) {
                    $statusColors = [
                        Encounter::STATUS_REGISTERED => 'bg-info',
                        Encounter::STATUS_IN_PROGRESS => 'bg-warning text-dark',
                        Encounter::STATUS_COMPLETED => 'bg-success',
                        Encounter::STATUS_CANCELLED => 'bg-danger',
                    ];
                    
                    $statusIcons = [
                        Encounter::STATUS_REGISTERED => '📋',
                        Encounter::STATUS_IN_PROGRESS => '⏳',
                        Encounter::STATUS_COMPLETED => '✓',
                        Encounter::STATUS_CANCELLED => '✗',
                    ];
                    
                    $color = $statusColors[$encounter->status] ?? 'bg-secondary';
                    $icon = $statusIcons[$encounter->status] ?? '';
                    
                    return '<span class="badge ' . $color . ' text-white">' . $icon . ' ' . $encounter->status . '</span>';
                })
                ->addColumn('consultation_status', function($encounter) {
                    $consultations = $encounter->consultations->count();
                    return $consultations > 0 ? 
                        '<span class="badge bg-success">' . $consultations . ' Consultation(s)</span>' :
                        '<span class="badge bg-warning">No Consultations</span>';
                })
                ->addColumn('action', function($encounter) {
                    $actions = '<div class="d-flex gap-1">';
                    
                    // View button for all encounters
                    $actions .= '<a href="' . route('facility.encounters.show', $encounter->id) . '" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    👁️ View
                                </a>';
                    
                    // Create Referral button for specific statuses
                    $referralEligibleStatuses = ['Registered', 'Triaged', 'Consultation', 'Investigation', 'Admitted', 'Surgery'];
                    if (in_array($encounter->status, $referralEligibleStatuses)) {
                        $actions .= '<a href="' . route('facility.referrals.create', $encounter->id) . '" 
                                       class="btn btn-sm btn-warning" title="Create Referral">
                                        🔄 Refer
                                    </a>';
                    }
                    
                    // Create Claim button only for completed encounters
                    if ($encounter->status === Encounter::STATUS_COMPLETED) {
                        $actions .= '<a href="' . route('facility.claims.create', $encounter->id) . '" 
                                       class="btn btn-sm btn-success" title="Create Claim">
                                        📋 Claim
                                    </a>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['patient_info', 'visit_info', 'status_badge', 'consultation_status', 'action'])
                ->make(true);
        }

        return view('facility.claims.index');
    }

    /**
     * Display all claims for facility
     */
    public function claims()
    {
        $facilityId = Auth::user()->facility_id;
        
        if (request()->ajax()) {
            $claims = FacilityClaim::with(['patient', 'encounter'])
                ->where('facility_id', $facilityId)
                ->orderBy('created_at', 'desc');

            return DataTables::of($claims)
                ->addColumn('claim_info', function($claim) {
                    return "<strong>{$claim->claim_number}</strong><br>
                            <small>{$claim->patient_name}</small>";
                })
                ->addColumn('encounter_info', function($claim) {
                    return $claim->service_date->format('d M Y') . '<br>' .
                           '<small>' . ucfirst($claim->claim_type) . '</small>';
                })
                ->addColumn('amounts', function($claim) {
                    return '<strong>Total: ' . $claim->formatted_total_amount . '</strong><br>' .
                           '<small>Pharm: ₦' . number_format($claim->pharmacy_amount, 2) . 
                           ' | Lab: ₦' . number_format($claim->laboratory_amount, 2) . '</small>';
                })
                ->addColumn('status_badge', function($claim) {
                    return $claim->status_badge;
                })
                ->addColumn('action', function($claim) {
                    $actions = '<div class="d-flex gap-1">';
                    $actions .= '<a href="' . route('facility.claims.show', $claim->id) . '" 
                                    class="btn btn-sm btn-info" title="View">👁️</a>';
                    
                    if ($claim->status === FacilityClaim::STATUS_DRAFT) {
                        $actions .= '<a href="' . route('facility.claims.edit', $claim->id) . '" 
                                        class="btn btn-sm btn-warning" title="Edit">✏️</a>';
                        $actions .= '<button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteClaim(' . $claim->id . ')" title="Delete">🗑️</button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['claim_info', 'encounter_info', 'amounts', 'status_badge', 'action'])
                ->make(true);
        }

        return view('facility.claims.list');
    }

    /**
     * Show form to create claim from encounter
     */
    public function create($encounterId)
    {
        $encounter = Encounter::with([
            'patient',
            'consultations.diagnoses.icdCode',
            'consultations.prescriptions.prescriptionItems.drug.dispensations',
            'actions',
            'serviceOrders.serviceOrderItems.serviceItem'
        ])->findOrFail($encounterId);

        // Get patient details
        $patient = $encounter->patient;
        $enrolleeDetails = $patient->enrolleeDetails;

        // Prepare encounter data
        $consultations = $encounter->consultations;
        $diagnoses = $consultations->flatMap->diagnoses;
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
                
                // Get dispensing status safely
                $status = 'pending';
                if ($dispensation) {
                    $status = $dispensation->status ?? 'pending';
                } elseif ($item->dispensing_status) {
                    $status = $item->dispensing_status;
                }
                
                // Only calculate cost for dispensed items
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
                
                // Only add to total if dispensed
                if ($status === 'dispensed') {
                    $pharmacyTotal += $cost;
                }
            }
        }
        
        // Get service orders and calculate service costs
        $services = [];
        $servicesTotal = 0;
        
        foreach ($encounter->serviceOrders as $order) {
            foreach ($order->serviceOrderItems as $orderItem) {
                $serviceItem = $orderItem->serviceItem;
                
                // Get service order status safely
                $status = $orderItem->status ?? 'pending';
                
                // Always show the service, but only calculate cost for completed/approved services
                $price = 0;
                if (in_array($status, ['completed', 'approved', 'delivered'])) {
                    $price = $serviceItem->price ?? 0;
                }
                
                $services[] = [
                    'item' => $orderItem,
                    'service' => $serviceItem,
                    'price' => $price,
                    'status' => $status
                ];
                
                // Only add to total if completed/approved
                if (in_array($status, ['completed', 'approved', 'delivered'])) {
                    $servicesTotal += $price;
                }
            }
        }

        return view('facility.claims.create', compact(
            'encounter',
            'patient',
            'enrolleeDetails',
            'consultations',
            'diagnoses',
            'actions',
            'vitalSigns',
            'medications',
            'services',
            'pharmacyTotal',
            'servicesTotal'
        ));
    }

    /**
     * Store new claim
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'encounter_id' => 'required|exists:encounters,id',
            'claim_type' => 'required|in:outpatient,inpatient,emergency,referral',
            'medications' => 'nullable|array',
            'services' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $encounter = Encounter::with('patient')->findOrFail($request->encounter_id);
            $patient = $encounter->patient;
            $enrolleeDetails = $patient->enrolleeDetails; // This is an accessor, not a relationship

            // Calculate totals
            $pharmacyAmount = collect($request->medications ?? [])->sum('amount');
            $servicesAmount = collect($request->services ?? [])->sum('amount');
            $totalAmount = $pharmacyAmount + $servicesAmount;

            // Create claim
            $claim = FacilityClaim::create([
                'encounter_id' => $encounter->id,
                'facility_id' => Auth::user()->facility_id,
                'patient_id' => $patient->id,
                'enrollee_number' => $patient->enrollee_number,
                'enrollee_type' => $patient->enrollee_type,
                'file_number' => $patient->file_number,
                'patient_name' => $enrolleeDetails->fullname ?? 'N/A',
                'boschma_no' => $patient->enrollee_number,
                'nin' => $enrolleeDetails->nin ?? null,
                'phone_number' => $enrolleeDetails->phone_no ?? $enrolleeDetails->phone ?? null,
                'gender' => $enrolleeDetails->gender ?? null,
                'date_of_birth' => $enrolleeDetails->date_of_birth ?? null,
                'claim_type' => $request->claim_type,
                'service_date' => $encounter->visit_date,
                'pharmacy_amount' => $pharmacyAmount,
                'services_amount' => $servicesAmount,
                'total_amount' => $totalAmount,
                'status' => FacilityClaim::STATUS_SUBMITTED,
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
            ]);

            // Save consultations
            foreach ($encounter->consultations as $consultation) {
                $claim->consultations()->create([
                    'consultation_id' => $consultation->id,
                    'consultation_notes' => $consultation->clinical_note,
                    'amount' => 0, // Non-priced
                ]);
            }

            // Save diagnoses
            foreach ($encounter->consultations as $consultation) {
                foreach ($consultation->diagnoses as $diagnosis) {
                    $claim->diagnoses()->create([
                        'diagnosis_id' => $diagnosis->id,
                        'icd_code' => $diagnosis->icdCode->code ?? null,
                        'diagnosis_type' => $diagnosis->diagnosis_type ?? 'primary',
                        'diagnosis_description' => $diagnosis->icdCode->description ?? 'N/A',
                    ]);
                }
            }

            // Save medications (only dispensed ones with amounts)
            if ($request->medications) {
                foreach ($request->medications as $medication) {
                    if (isset($medication['amount']) && $medication['amount'] > 0) {
                        $claim->medications()->create([
                            'prescription_item_id' => $medication['drug_id'] ?? null,
                            'drug_name' => $medication['drug_name'] ?? 'N/A',
                            'quantity' => 1,
                            'unit_price' => $medication['amount'],
                            'total_price' => $medication['amount'],
                        ]);
                    }
                }
            }

            // Save services (only completed ones with amounts)
            if ($request->services) {
                foreach ($request->services as $service) {
                    if (isset($service['amount']) && $service['amount'] > 0) {
                        $claim->services()->create([
                            'service_order_item_id' => null, // Don't store UUID in bigint field
                            'service_name' => $service['service_name'] ?? 'N/A',
                            'frequency' => 1,
                            'unit_price' => $service['amount'],
                            'total_price' => $service['amount'],
                        ]);
                    }
                }
            }

            // Save encounter actions
            foreach ($encounter->actions as $action) {
                $claim->activities()->create([
                    'encounter_action_id' => is_string($action->id) ? $action->id : null,
                    'activity_type' => $action->action_type,
                    'activity_description' => $action->description,
                    'performed_at' => $action->action_time,
                ]);
            }

            DB::commit();

            return redirect()->route('facility.claims.list')
                ->with('success', 'Claim created successfully. Claim Number: ' . $claim->claim_number);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Claim creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->withInput()->with('error', 'Error creating claim: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }

    /**
     * Display claim details
     */
    public function show($id)
    {
        $claim = FacilityClaim::with([
            'encounter',
            'patient',
            'consultations.consultation.diagnoses.icdCode',
            'diagnoses',
            'medications',
            'services',
            'activities',
            'documents'
        ])->findOrFail($id);

        return view('facility.claims.show', compact('claim'));
    }

    /**
     * Edit claim (only drafts)
     */
    public function edit($id)
    {
        $claim = FacilityClaim::with([
            'encounter.consultations.diagnoses.icdCode',
            'encounter.consultations.prescriptions.prescriptionItems.drug',
            'encounter.consultations.prescriptions.prescriptionItems.dispensations',
            'encounter.serviceOrders.serviceOrderItems.serviceItem',
            'encounter.actions',
            'encounter.vitalSigns.takenBy',
            'encounter.patient',
            'medications',
            'services',
            'consultations',
            'diagnoses',
            'activities'
        ])->findOrFail($id);

        if ($claim->status !== FacilityClaim::STATUS_DRAFT) {
            return redirect()->route('facility.claims.show', $id)
                ->with('error', 'Only draft claims can be edited');
        }

        $encounter = $claim->encounter;
        $patient = $encounter->patient;
        $enrolleeDetails = $patient->enrolleeDetails;
        
        // Prepare data similar to create method
        $consultations = $encounter->consultations;
        $diagnoses = $consultations->flatMap->diagnoses;
        $actions = $encounter->actions;
        $vitalSigns = $encounter->vitalSigns;
        
        // Get medications from claim (already filtered)
        $medications = $claim->medications->map(function($med) {
            return [
                'item' => null,
                'drug' => (object)['name' => $med->drug_name],
                'cost' => $med->total_price,
                'dispensing_status' => 'dispensed'
            ];
        })->toArray();
        
        // Get services from claim (already filtered)
        $services = $claim->services->map(function($srv) {
            return [
                'item' => null,
                'service' => (object)[
                    'name' => $srv->service_name,
                    'type' => $srv->service_type ?? 'N/A',
                    'description' => $srv->service_description ?? 'N/A'
                ],
                'price' => $srv->total_price,
                'status' => 'completed'
            ];
        })->toArray();
        
        $pharmacyTotal = $claim->pharmacy_amount;
        $servicesTotal = $claim->services_amount;

        return view('facility.claims.edit', compact(
            'claim',
            'encounter',
            'patient',
            'enrolleeDetails',
            'consultations',
            'diagnoses',
            'actions',
            'vitalSigns',
            'medications',
            'services',
            'pharmacyTotal',
            'servicesTotal'
        ));
    }

    /**
     * Update claim (only drafts)
     */
    public function update(Request $request, $id)
    {
        $claim = FacilityClaim::findOrFail($id);

        if ($claim->status !== FacilityClaim::STATUS_DRAFT) {
            return back()->with('error', 'Only draft claims can be updated');
        }

        $validated = $request->validate([
            'claim_type' => 'required|in:outpatient,inpatient,emergency,referral',
            'medications' => 'nullable|array',
            'services' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Calculate new totals
            $pharmacyAmount = collect($request->medications ?? [])->sum('amount');
            $servicesAmount = collect($request->services ?? [])->sum('amount');
            $totalAmount = $pharmacyAmount + $servicesAmount;

            // Update main claim
            $claim->update([
                'claim_type' => $request->claim_type,
                'pharmacy_amount' => $pharmacyAmount,
                'services_amount' => $servicesAmount,
                'total_amount' => $totalAmount,
            ]);

            // Delete old items and recreate
            $claim->medications()->delete();
            $claim->services()->delete();

            // Save medications (only with amounts)
            if ($request->medications) {
                foreach ($request->medications as $medication) {
                    if (isset($medication['amount']) && $medication['amount'] > 0) {
                        $claim->medications()->create([
                            'prescription_item_id' => $medication['drug_id'] ?? null,
                            'drug_name' => $medication['drug_name'] ?? 'N/A',
                            'quantity' => 1,
                            'unit_price' => $medication['amount'],
                            'total_price' => $medication['amount'],
                        ]);
                    }
                }
            }

            // Save services (only with amounts)
            if ($request->services) {
                foreach ($request->services as $service) {
                    if (isset($service['amount']) && $service['amount'] > 0) {
                        $claim->services()->create([
                            'service_order_item_id' => null,
                            'service_name' => $service['service_name'] ?? 'N/A',
                            'frequency' => 1,
                            'unit_price' => $service['amount'],
                            'total_price' => $service['amount'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('facility.claims.show', $claim->id)
                ->with('success', 'Claim updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Claim update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating claim: ' . $e->getMessage());
        }
    }

    /**
     * Delete claim (only drafts)
     */
    public function destroy($id)
    {
        $claim = FacilityClaim::findOrFail($id);

        if ($claim->status !== FacilityClaim::STATUS_DRAFT) {
            return response()->json(['error' => 'Only draft claims can be deleted'], 400);
        }

        $claim->delete();

        return response()->json(['success' => 'Claim deleted successfully']);
    }

    /**
     * Display billable items - smart claims page with referral-aware tabs
     * Tabs: Awaiting Claim (completed encounters), Referrals (from other facilities),
     *       Ongoing (in-progress encounters), History (already claimed)
     */
    public function billableItems(Request $request)
    {
        ini_set('memory_limit', '512M'); // Prevent memory exhaustion for large datasets
        $facilityId = Auth::user()->facility_id;
        $tab = $request->get('tab', 'awaiting');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');
        $programId = $request->get('program_id');
        $itemType = $request->get('item_type');
        
        // Get programs for filter dropdown
        $programs = \App\Models\Program::orderBy('name')->get();
        
        // --- 1. Get IDs of already-claimed items ---
        $claimedPrescriptionItemIds = FacilityClaimMedication::whereHas('claim', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)->whereNull('deleted_at');
        })->whereNotNull('prescription_item_id')->pluck('prescription_item_id')->toArray();
        
        $claimedServiceOrderItemIds = FacilityClaimService::whereHas('claim', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)->whereNull('deleted_at');
        })->whereNotNull('service_order_item_id')->pluck('service_order_item_id')->toArray();
        
        // --- 2. Get service referrals TO this facility (service referrals where this facility performs the service) ---
        $serviceReferrals = ServiceReferral::where('to_facility_id', $facilityId)
            ->with(['encounter.patient', 'serviceItem', 'fromFacility', 'encounter.serviceOrders.serviceOrderItems.serviceItem'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Build a lookup: encounter_id + service_item_id => referral info
        $referralLookup = [];
        foreach ($serviceReferrals as $ref) {
            $key = $ref->encounter_id . '_' . $ref->service_item_id;
            $referralLookup[$key] = [
                'from_facility_id' => $ref->from_facility_id,
                'from_facility_name' => $ref->fromFacility->name ?? 'Unknown Facility',
                'referral_type' => $ref->referral_type,
                'referral_status' => $ref->status,
                'referral_id' => $ref->id,
            ];
        }
        
        // Also build a set of encounter_ids that are patient referrals to this facility (external only)
        $patientReferralEncounterIds = ServiceReferral::where('to_facility_id', $facilityId)
            ->where('referral_type', 'patient')
            ->where('from_facility_id', '!=', $facilityId) // Only external referrals
            ->pluck('encounter_id')
            ->toArray();
        
        // Build a lookup for service referrals (external referrals where current facility performs the service)
        $serviceReferralLookup = ServiceReferral::where('to_facility_id', $facilityId)
            ->where('referral_type', 'service')
            ->where('from_facility_id', '!=', $facilityId) // External referrals only
            ->with(['fromFacility'])
            ->get()
            ->keyBy(function($referral) {
                return $referral->encounter_id . '_' . $referral->service_item_id;
            });
        
        // --- 3. Get ALL service order items performed at this facility ---
        $servicesQuery = ServiceOrderItem::whereHas('serviceOrder', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereHas('serviceItem', function($q) {
                $q->where('price', '>', 0);
            })
            ->whereHas('serviceOrder.encounter.patient', function($q) use ($programId) {
                if ($programId) {
                    $q->where(function($subQ) use ($programId) {
                        $subQ->where(function($beneficiaryQ) use ($programId) {
                            $beneficiaryQ->where('enrollee_type', 'beneficiary')
                                ->whereHas('beneficiary', function($bQ) use ($programId) {
                                    $bQ->where('program_id', $programId);
                                });
                        })->orWhere(function($spouseQ) use ($programId) {
                            $spouseQ->where('enrollee_type', 'spouse')
                                ->whereHas('spouse', function($sQ) use ($programId) {
                                    $sQ->whereHas('beneficiary', function($bQ) use ($programId) {
                                        $bQ->where('program_id', $programId);
                                    });
                                });
                        })->orWhere(function($childQ) use ($programId) {
                            $childQ->where('enrollee_type', 'child')
                                ->whereHas('child', function($cQ) use ($programId) {
                                    $cQ->whereHas('beneficiary', function($bQ) use ($programId) {
                                        $bQ->where('program_id', $programId);
                                    });
                                });
                        });
                    });
                }
            })
            ->with([
                'serviceOrder.encounter.patient',
                'serviceItem',
            ]);
        
        if ($dateFrom) {
            $servicesQuery->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $servicesQuery->where('created_at', '<=', $dateTo . ' 23:59:59');
        }
        
        // Filter by item type
        if ($itemType === 'drug' || $itemType === 'admin') {
            $allServices = collect(); // Don't show services if only drugs or admin charges requested
        } else {
            $allServices = $servicesQuery->orderBy('created_at', 'desc')->get();
        }
        
        // --- 4. Get ALL dispensed drugs at this facility ---
        $drugsQuery = PharmacyDispensation::whereHas('prescriptionItem.prescription.consultation.encounter', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId);
        })
        ->whereHas('prescriptionItem.prescription.consultation.encounter.patient', function($q) use ($programId) {
            if ($programId) {
                $q->where(function($subQ) use ($programId) {
                    $subQ->where(function($beneficiaryQ) use ($programId) {
                        $beneficiaryQ->where('enrollee_type', 'beneficiary')
                            ->whereHas('beneficiary', function($bQ) use ($programId) {
                                $bQ->where('program_id', $programId);
                            });
                    })->orWhere(function($spouseQ) use ($programId) {
                        $spouseQ->where('enrollee_type', 'spouse')
                            ->whereHas('spouse', function($sQ) use ($programId) {
                                $sQ->whereHas('beneficiary', function($bQ) use ($programId) {
                                    $bQ->where('program_id', $programId);
                                });
                            });
                    })->orWhere(function($childQ) use ($programId) {
                        $childQ->where('enrollee_type', 'child')
                            ->whereHas('child', function($cQ) use ($programId) {
                                $cQ->whereHas('beneficiary', function($bQ) use ($programId) {
                                    $bQ->where('program_id', $programId);
                                });
                            });
                    });
                });
            }
        })
        ->with([
            'prescriptionItem.drug',
            'prescriptionItem.prescription.consultation.encounter.patient',
        ]);
        
        if ($dateFrom) {
            $drugsQuery->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $drugsQuery->where('created_at', '<=', $dateTo . ' 23:59:59');
        }
        
        // Load drugs (skip if only admin charges requested)
        if ($itemType === 'admin') {
            $allDrugs = collect();
        } else {
            $allDrugs = $drugsQuery->orderBy('created_at', 'desc')->get();
        }
        
                
                        
        // --- 5. Build unified items list with referral awareness ---
        $allItems = collect();
        
        // Build encounter cache first for consistent tab categorization
        $encounterCache = [];
        
        // Pre-build cache for all encounters from services
        foreach ($allServices as $soi) {
            $serviceOrder = $soi->serviceOrder;
            $encounter = $serviceOrder ? $serviceOrder->encounter : null;
            if (!$encounter || isset($encounterCache[$encounter->id])) continue;
            
            // Build encounter data (same logic as before)
            $allDrugsDispensed = true;
            $allServicesCompleted = true;
            $hasDrugsInEncounter = false;
            
            // Check for actual dispensed drugs in this encounter
            $encounterDispensedDrugs = PharmacyDispensation::whereHas('prescriptionItem.prescription.consultation', function($q) use ($encounter) {
                $q->where('encounter_id', $encounter->id);
            })->get();
            
            $hasDrugsInEncounter = $encounterDispensedDrugs->count() > 0;
            
            // Simple logic: if no drugs in encounter, consider all drugs as dispensed
            if (!$hasDrugsInEncounter) {
                $allDrugsDispensed = true;
            } else {
                // Only check dispensing status if there are actual drugs
                $encounterPrescriptionItems = PrescriptionItem::whereHas('prescription.consultation', function($q) use ($encounter) {
                    $q->where('encounter_id', $encounter->id);
                })->whereNotNull('drug_id')->where('drug_id', '!=', '')->get();
                
                foreach ($encounterPrescriptionItems as $pi) {
                    $status = strtolower($pi->dispensing_status);
                    // dispensed, fully_dispensed, cancelled are all resolved states
                    if (!in_array($status, ['dispensed', 'fully_dispensed', 'cancelled'])) {
                        $allDrugsDispensed = false;
                        break;
                    }
                }
            }
            
            $encounterCache[$encounter->id] = [
                'allDrugsDispensed' => $allDrugsDispensed,
                'allServicesCompleted' => $allServicesCompleted,
                'hasDrugsInEncounter' => $hasDrugsInEncounter
            ];
        }
        
        // Process services
        foreach ($allServices as $soi) {
            $serviceOrder = $soi->serviceOrder;
            $encounter = $serviceOrder ? $serviceOrder->encounter : null;
            $patient = $encounter ? $encounter->patient : null;
            if (!$patient) continue;
            
            $serviceItem = $soi->serviceItem;
            $price = $serviceItem->price ?? 0;
            if ($price <= 0) continue;
            
            $isClaimed = in_array($soi->id, $claimedServiceOrderItemIds);
            $encounterStatus = $encounter->status ?? 'N/A';
            
            // Check if this service is from a referral
            $refKey = $encounter->id . '_' . $soi->service_item_id;
            $sourceType = 'direct';
            $fromFacilityName = null;
            if (isset($serviceReferrals[$refKey])) {
                $sourceType = 'service_referral';
                $fromFacilityName = $serviceReferrals[$refKey]->fromFacility->name ?? 'Unknown';
            } else {
                // Find the patient referral info
                $patRef = ServiceReferral::where('encounter_id', $encounter->id)
                    ->where('to_facility_id', $facilityId)
                    ->where('referral_type', 'patient')
                    ->with('fromFacility')
                    ->first();
                $fromFacilityName = $patRef ? ($patRef->fromFacility->name ?? 'Unknown') : null;
            }
            
            // Use cached encounter data
            $allDrugsDispensed = $encounterCache[$encounter->id]['allDrugsDispensed'];
            $allServicesCompleted = $encounterCache[$encounter->id]['allServicesCompleted'];
            $hasDrugsInEncounter = $encounterCache[$encounter->id]['hasDrugsInEncounter'];
            
            // Determine claimability
            $canClaim = false;
            if ($isClaimed) {
                $canClaim = false;
            } elseif ($sourceType === 'service_referral' || $sourceType === 'patient_referral') {
                $canClaim = true; // Referrals can claim anytime
            } elseif (in_array($encounterStatus, ['Completed', 'Referred']) && ($encounterStatus === 'Referred' || $allDrugsDispensed)) {
                $canClaim = true; // Direct: if completed with drugs dispensed, or referred
            }
            
            // Determine tab category
            $tabCategory = 'ongoing';
            
            if ($isClaimed) {
                $tabCategory = 'history';
            } elseif ($sourceType === 'service_referral') {
                $tabCategory = 'referrals'; // External service referrals
            } elseif ($sourceType === 'patient_referral') {
                $tabCategory = 'referrals'; // Patient referrals also go to referrals tab
            } elseif ($sourceType === 'direct' && in_array($encounterStatus, ['Completed', 'Referred']) && ($encounterStatus === 'Referred' || $allDrugsDispensed)) {
                $tabCategory = 'awaiting'; // Internal services (from/to same facility)
            }
            
                        
            $enrolleeDetails = $patient->enrolleeDetails;
            
            $allItems->push([
                'type' => 'service',
                'id' => $soi->id,
                'dispensation_id' => null,
                'patient_id' => $patient->id,
                'patient_name' => $enrolleeDetails->fullname ?? $enrolleeDetails->name ?? 'Unknown',
                'enrollee_number' => $patient->enrollee_number,
                'enrollee_type' => $patient->enrollee_type,
                'encounter_id' => $encounter->id ?? null,
                'encounter_status' => $encounterStatus,
                'visit_date' => $encounter->visit_date ?? null,
                'item_name' => $serviceItem->name ?? 'Unknown Service',
                'item_detail' => $serviceItem->type ?? 'Service',
                'quantity' => 1,
                'cost' => $price,
                'date' => $soi->created_at,
                'is_claimed' => $isClaimed,
                'can_claim' => $canClaim,
                'source_type' => $sourceType,
                'from_facility_name' => $fromFacilityName,
                'service_status' => $soi->status,
                'tab' => $tabCategory,
                'all_drugs_dispensed' => $allDrugsDispensed,
                'has_drugs_in_encounter' => $hasDrugsInEncounter,
                'claim_number' => null, // Will be populated for claimed items
            ]);
        }
        
        // Process drugs
        foreach ($allDrugs as $dispensation) {
            $prescriptionItem = $dispensation->prescriptionItem;
            if (!$prescriptionItem) continue;
            
            $encounter = $prescriptionItem->prescription->consultation->encounter ?? null;
            $patient = $encounter ? $encounter->patient : null;
            if (!$patient) continue;
            
            $drug = $prescriptionItem->drug;
            $cost = $dispensation->cost_of_medication > 0 
                ? $dispensation->cost_of_medication 
                : (($drug->unit_price ?? 0) * ($prescriptionItem->quantity ?? 1));
            if ($cost <= 0) continue;
            
            $isClaimed = in_array($prescriptionItem->id, $claimedPrescriptionItemIds);
            $encounterStatus = $encounter->status ?? 'N/A';
            
            // Check if encounter is a patient referral
            $isPatientReferral = in_array($encounter->id, $patientReferralEncounterIds);
            $sourceType = $isPatientReferral ? 'patient_referral' : 'direct';
            $fromFacilityName = null;
            
            if ($isPatientReferral) {
                $patRef = ServiceReferral::where('encounter_id', $encounter->id)
                    ->where('to_facility_id', $facilityId)
                    ->where('referral_type', 'patient')
                    ->with('fromFacility')
                    ->first();
                $fromFacilityName = $patRef ? ($patRef->fromFacility->name ?? 'Unknown') : null;
            }
            
            // Determine claimability
            $canClaim = false;
            if ($isClaimed) {
                $canClaim = false;
            } elseif ($isPatientReferral) {
                $canClaim = true;
            } elseif (in_array($encounterStatus, ['Completed', 'Referred'])) {
                $canClaim = true;
            }
            
            // Use the same tab categorization logic as services (from encounter cache)
            if (isset($encounterCache[$encounter->id])) {
                $tabCategory = 'ongoing'; // Default, will be updated based on encounter cache
                $encounterData = $encounterCache[$encounter->id];
                
                if ($isClaimed) {
                    $tabCategory = 'history';
                } elseif ($isPatientReferral) {
                    $tabCategory = 'referrals';
                } elseif (in_array($encounterStatus, ['Completed', 'Referred']) && ($encounterStatus === 'Referred' || $encounterData['allDrugsDispensed'])) {
                    $tabCategory = 'awaiting';
                }
            } else {
                // Fallback logic if encounter cache not available
                $tabCategory = 'ongoing';
                if ($isClaimed) {
                    $tabCategory = 'history';
                } elseif ($isPatientReferral) {
                    $tabCategory = 'referrals';
                } elseif (in_array($encounterStatus, ['Completed', 'Referred'])) {
                    $tabCategory = 'awaiting';
                }
            }
            
                        
            $enrolleeDetails = $patient->enrolleeDetails;
            
            $allItems->push([
                'type' => 'drug',
                'id' => $prescriptionItem->id,
                'dispensation_id' => $dispensation->id,
                'patient_id' => $patient->id,
                'patient_name' => $enrolleeDetails->fullname ?? $enrolleeDetails->name ?? 'Unknown',
                'enrollee_number' => $patient->enrollee_number,
                'enrollee_type' => $patient->enrollee_type,
                'encounter_id' => $encounter->id ?? null,
                'encounter_status' => $encounterStatus,
                'visit_date' => $encounter->visit_date ?? null,
                'item_name' => $drug->name ?? 'Unknown Drug',
                'item_detail' => ($prescriptionItem->dosage ?? '') . ' x ' . ($prescriptionItem->quantity ?? 1),
                'quantity' => $prescriptionItem->quantity ?? 1,
                'cost' => $cost,
                'date' => $dispensation->created_at,
                'is_claimed' => $isClaimed,
                'can_claim' => $canClaim,
                'source_type' => $sourceType,
                'from_facility_name' => $fromFacilityName,
                'service_status' => 'dispensed',
                'tab' => $tabCategory,
                'all_drugs_dispensed' => true,
                'has_drugs_in_encounter' => true,
                'claim_number' => null, // Will be populated for claimed items
            ]);
        }
        
        // --- 6. Auto-generate Admin Charges (Clinical Services) ---
        // Track which admin charges have already been claimed
        $claimedAdminCharges = FacilityClaimService::whereHas('claim', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)->whereNull('deleted_at');
        })->whereNull('service_order_item_id')
          ->whereNotNull('service_type')
          ->get()
          ->groupBy(function($item) {
              return $item->claim->encounter_id . '_' . $item->service_type;
          });
        
        // Get all encounters at this facility that have consultations
        $adminEncounters = Encounter::where('facility_id', $facilityId)
            ->whereHas('consultations')
            ->with(['patient', 'consultations', 'admissions'])
            ->when($dateFrom, fn($q) => $q->where('visit_date', '>=', $dateFrom . ' 00:00:00'))
            ->when($dateTo, fn($q) => $q->where('visit_date', '<=', $dateTo . ' 23:59:59'))
            ->orderBy('visit_date', 'desc')
            ->get();
        
        foreach ($adminEncounters as $encounter) {
            $patient = $encounter->patient;
            if (!$patient) continue;
            
            // Program filter
            if ($programId) {
                $enrolleeDetails = $patient->enrolleeDetails;
                if (!$enrolleeDetails) continue;
                $patientProgramId = null;
                if ($patient->enrollee_type === 'beneficiary') {
                    $patientProgramId = $enrolleeDetails->program_id ?? null;
                } elseif (method_exists($enrolleeDetails, 'beneficiary')) {
                    $patientProgramId = $enrolleeDetails->beneficiary->program_id ?? null;
                }
                if ($patientProgramId != $programId) continue;
            }
            
            // Item type filter - skip admin charges if only drugs or only services requested
            if ($itemType === 'drug' || $itemType === 'service') continue;
            
            $enrolleeDetails = $patient->enrolleeDetails;
            $encounterStatus = $encounter->status ?? 'N/A';
            
            // Use encounter cache for tab categorization if available
            $allDrugsDispensed = isset($encounterCache[$encounter->id]) 
                ? $encounterCache[$encounter->id]['allDrugsDispensed'] 
                : true;
            $hasDrugsInEncounter = isset($encounterCache[$encounter->id])
                ? $encounterCache[$encounter->id]['hasDrugsInEncounter']
                : false;
            
            // Determine tab category for admin charges
            $adminTabCategory = 'ongoing';
            if (in_array($encounterStatus, ['Completed', 'Referred']) && ($encounterStatus === 'Referred' || $allDrugsDispensed)) {
                $adminTabCategory = 'awaiting';
            }
            
            // Helper to check if admin charge is already claimed
            $isAdminClaimed = function($chargeType) use ($claimedAdminCharges, $encounter) {
                return $claimedAdminCharges->has($encounter->id . '_' . $chargeType);
            };
            
            // Helper to push admin charge item
            $pushAdminCharge = function($chargeType, $chargeName, $price, $quantity, $condition, $date) 
                use (&$allItems, $encounter, $patient, $enrolleeDetails, $encounterStatus, $adminTabCategory, $isAdminClaimed, $allDrugsDispensed, $hasDrugsInEncounter) {
                
                $isClaimed = $isAdminClaimed($chargeType);
                $canClaim = false;
                if (!$isClaimed && in_array($encounterStatus, ['Completed', 'Referred']) && ($encounterStatus === 'Referred' || $allDrugsDispensed)) {
                    $canClaim = true;
                }
                
                $tabCategory = $isClaimed ? 'history' : $adminTabCategory;
                
                $allItems->push([
                    'type' => 'admin',
                    'id' => $encounter->id . '_' . $chargeType,
                    'dispensation_id' => null,
                    'patient_id' => $patient->id,
                    'patient_name' => $enrolleeDetails->fullname ?? $enrolleeDetails->name ?? 'Unknown',
                    'enrollee_number' => $patient->enrollee_number,
                    'enrollee_type' => $patient->enrollee_type,
                    'encounter_id' => $encounter->id,
                    'encounter_status' => $encounterStatus,
                    'visit_date' => $encounter->visit_date ?? null,
                    'item_name' => $chargeName,
                    'item_detail' => $condition,
                    'quantity' => $quantity,
                    'cost' => $price * $quantity,
                    'date' => $date,
                    'is_claimed' => $isClaimed,
                    'can_claim' => $canClaim,
                    'source_type' => 'direct',
                    'from_facility_name' => null,
                    'service_status' => 'completed',
                    'tab' => $tabCategory,
                    'all_drugs_dispensed' => $allDrugsDispensed,
                    'has_drugs_in_encounter' => $hasDrugsInEncounter,
                    'claim_number' => null,
                    'admin_charge_type' => $chargeType,
                ]);
            };
            
            // 1. Specialist Initial Consultation - ₦1,500 (when patient is seen by a Doctor)
            $consultation = $encounter->consultations->first();
            if ($consultation) {
                $pushAdminCharge(
                    'consultation',
                    'Specialist Initial Consultation',
                    1500,
                    1,
                    'Patient seen by Doctor',
                    $consultation->created_at ?? $encounter->visit_date
                );
            }
            
            // Admission-based charges
            $admission = $encounter->admissions->first();
            if ($admission && $admission->discharge_date) {
                $admissionDate = \Carbon\Carbon::parse($admission->admission_date);
                $dischargeDate = \Carbon\Carbon::parse($admission->discharge_date);
                $daysAdmitted = max(1, ceil($admissionDate->diffInDays($dischargeDate)));
                
                // 2. Specialist Review (Per visit) - ₦1,000 (max 2 per admission)
                $reviewCount = min(2, $daysAdmitted);
                if ($reviewCount > 0) {
                    $pushAdminCharge(
                        'specialist_review',
                        'Specialist Review (Per visit)',
                        1000,
                        $reviewCount,
                        'Ward Doctor review (max 2/admission)',
                        $admission->discharge_date
                    );
                }
                
                // 3. Nursing Care (per day) - ₦700
                $pushAdminCharge(
                    'nursing_care',
                    'Nursing Care (per day)',
                    700,
                    $daysAdmitted,
                    $daysAdmitted . ' day(s) admitted',
                    $admission->discharge_date
                );
                
                // 4. Hospital Bed Occupancy - ₦1,000 (per day)
                $pushAdminCharge(
                    'bed_occupancy',
                    'Hospital Bed Occupancy',
                    1000,
                    $daysAdmitted,
                    $daysAdmitted . ' day(s) admitted',
                    $admission->discharge_date
                );
            }
        }
        
        // Apply search filter
        if ($search) {
            $searchLower = strtolower($search);
            $allItems = $allItems->filter(function($item) use ($searchLower) {
                return str_contains(strtolower($item['patient_name']), $searchLower) ||
                       str_contains(strtolower($item['enrollee_number']), $searchLower) ||
                       str_contains(strtolower($item['item_name']), $searchLower);
            });
        }
        
        // Split into tab groups
        $awaitingItems = $allItems->where('tab', 'awaiting')->groupBy('patient_id');
        $referralItems = $allItems->where('tab', 'referrals')->groupBy('patient_id');
        $ongoingItems = $allItems->where('tab', 'ongoing')->groupBy('patient_id');
        // Limit history items rendering to prevent massive memory usage in Blade view
        $historyItems = $allItems->where('tab', 'history')->sortByDesc('date')->take(200)->groupBy('patient_id');
                
        // Tab counts
        $counts = [
            'awaiting' => $allItems->where('tab', 'awaiting')->count(),
            'referrals' => $allItems->where('tab', 'referrals')->count(),
            'ongoing' => $allItems->where('tab', 'ongoing')->count(),
            'history' => $allItems->where('tab', 'history')->count(),
        ];
        
        // Summary stats
        $totalUnclaimed = $allItems->where('is_claimed', false)->where('tab', '!=', 'ongoing')->sum('cost');
        $totalClaimed = $allItems->where('is_claimed', true)->sum('cost');
        
        return view('facility.claims.billable-items', compact(
            'awaitingItems',
            'referralItems',
            'ongoingItems',
            'historyItems',
            'counts',
            'totalUnclaimed',
            'totalClaimed',
            'tab',
            'dateFrom',
            'dateTo',
            'search',
            'programId',
            'itemType',
            'programs'
        ));
    }

    /**
     * Store claim from selected billable items (not encounter-dependent)
     */
    public function storeFromBillable(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required',
            'claim_type' => 'required|in:outpatient,inpatient,emergency,referral',
            'drug_items' => 'nullable|array',
            'service_items' => 'nullable|array',
            'admin_items' => 'nullable|array',
        ]);
        
        $drugItems = $request->drug_items ?? [];
        $serviceItems = $request->service_items ?? [];
        $adminItems = $request->admin_items ?? [];
        
        if (empty($drugItems) && empty($serviceItems) && empty($adminItems)) {
            return back()->with('error', 'Please select at least one item to claim.');
        }
        
        try {
            DB::beginTransaction();
            
            $createdClaims = [];
            $totalAmount = 0;
            
            // Group items by patient_id
            $itemsByPatient = [];
            
            // Group drug items by patient
            foreach ($drugItems as $prescriptionItemId => $data) {
                $patientId = $data['patient_id'] ?? null;
                if ($patientId) {
                    if (!isset($itemsByPatient[$patientId])) {
                        $itemsByPatient[$patientId] = ['drugs' => [], 'services' => [], 'admin' => []];
                    }
                    $itemsByPatient[$patientId]['drugs'][$prescriptionItemId] = $data;
                }
            }
            
            // Group service items by patient
            foreach ($serviceItems as $serviceOrderItemId => $data) {
                $patientId = $data['patient_id'] ?? null;
                if ($patientId) {
                    if (!isset($itemsByPatient[$patientId])) {
                        $itemsByPatient[$patientId] = ['drugs' => [], 'services' => [], 'admin' => []];
                    }
                    $itemsByPatient[$patientId]['services'][$serviceOrderItemId] = $data;
                }
            }
            
            // Group admin items by patient
            foreach ($adminItems as $adminItemId => $data) {
                $patientId = $data['patient_id'] ?? null;
                if ($patientId) {
                    if (!isset($itemsByPatient[$patientId])) {
                        $itemsByPatient[$patientId] = ['drugs' => [], 'services' => [], 'admin' => []];
                    }
                    $itemsByPatient[$patientId]['admin'][$adminItemId] = $data;
                }
            }
            
            // Create a separate claim for each patient
            foreach ($itemsByPatient as $patientId => $patientItems) {
                $patient = Patient::findOrFail($patientId);
                $enrolleeDetails = $patient->enrolleeDetails;
                
                // Find an encounter_id if available (use the most recent encounter for this patient at this facility)
                $latestEncounter = Encounter::where('patient_id', $patient->id)
                    ->where('facility_id', Auth::user()->facility_id)
                    ->orderBy('visit_date', 'desc')
                    ->first();
                
                // Calculate totals for this patient
                $pharmacyAmount = 0;
                $servicesAmount = 0;
                $drugRecords = [];
                $serviceRecords = [];
                
                // Process drug items for this patient
                foreach ($patientItems['drugs'] as $prescriptionItemId => $data) {
                    $cost = floatval($data['cost'] ?? 0);
                    if ($cost > 0) {
                        $pharmacyAmount += $cost;
                        $drugRecords[] = [
                            'prescription_item_id' => $prescriptionItemId,
                            'drug_name' => $data['drug_name'] ?? 'N/A',
                            'quantity' => $data['quantity'] ?? 1,
                            'unit_price' => $cost / max(1, intval($data['quantity'] ?? 1)),
                            'total_price' => $cost,
                        ];
                    }
                }
                
                // Process service items for this patient
                foreach ($patientItems['services'] as $serviceOrderItemId => $data) {
                    $cost = floatval($data['cost'] ?? 0);
                    if ($cost > 0) {
                        $servicesAmount += $cost;
                        $serviceRecords[] = [
                            'service_order_item_id' => $serviceOrderItemId,
                            'service_name' => $data['service_name'] ?? 'N/A',
                            'frequency' => 1,
                            'unit_price' => $cost,
                            'total_price' => $cost,
                        ];
                    }
                }
                
                // Process admin charge items for this patient
                foreach (($patientItems['admin'] ?? []) as $adminItemId => $data) {
                    $cost = floatval($data['cost'] ?? 0);
                    if ($cost > 0) {
                        $servicesAmount += $cost;
                        // Extract charge type from admin item ID (format: encounter_id_chargeType)
                        $parts = explode('_', $adminItemId);
                        $chargeType = end($parts);
                        // Handle multi-part charge types like specialist_review, nursing_care, bed_occupancy
                        if (count($parts) > 2) {
                            $chargeType = implode('_', array_slice($parts, -2));
                            if (!in_array($chargeType, ['specialist_review', 'nursing_care', 'bed_occupancy'])) {
                                $chargeType = end($parts);
                            }
                        }
                        $serviceRecords[] = [
                            'service_order_item_id' => null,
                            'service_type' => $chargeType,
                            'service_name' => $data['service_name'] ?? 'N/A',
                            'frequency' => $data['quantity'] ?? 1,
                            'unit_price' => $cost / max(1, intval($data['quantity'] ?? 1)),
                            'total_price' => $cost,
                        ];
                    }
                }
                
                $patientTotal = $pharmacyAmount + $servicesAmount;
                
                if ($patientTotal <= 0) {
                    continue; // Skip patients with zero total
                }
                
                $totalAmount += $patientTotal;
                
                // Create the claim for this patient
                $claim = FacilityClaim::create([
                    'encounter_id' => $latestEncounter ? $latestEncounter->id : null,
                    'facility_id' => Auth::user()->facility_id,
                    'patient_id' => $patient->id,
                    'enrollee_number' => $patient->enrollee_number,
                    'enrollee_type' => $patient->enrollee_type,
                    'file_number' => $patient->file_number,
                    'patient_name' => $enrolleeDetails->fullname ?? $enrolleeDetails->name ?? 'N/A',
                    'boschma_no' => $patient->enrollee_number,
                    'nin' => $enrolleeDetails->nin ?? null,
                    'phone_number' => $enrolleeDetails->phone_no ?? $enrolleeDetails->phone ?? null,
                    'gender' => $enrolleeDetails->gender ?? null,
                    'date_of_birth' => $enrolleeDetails->date_of_birth ?? $enrolleeDetails->dob ?? null,
                    'claim_type' => $request->claim_type,
                    'service_date' => $latestEncounter ? $latestEncounter->visit_date : now(),
                    'pharmacy_amount' => $pharmacyAmount,
                    'services_amount' => $servicesAmount,
                    'total_amount' => $patientTotal,
                    'status' => FacilityClaim::STATUS_SUBMITTED,
                    'submitted_by' => Auth::id(),
                    'submitted_at' => now(),
                ]);
                
                // Save drug claim items
                foreach ($drugRecords as $record) {
                    $claim->medications()->create($record);
                }
                
                // Save service claim items
                foreach ($serviceRecords as $record) {
                    $claim->services()->create($record);
                }
                
                $createdClaims[] = $claim;
            }
            
            if (empty($createdClaims)) {
                return back()->with('error', 'No valid items found to create claims.');
            }
            
            DB::commit();
            
            // Build success message
            $claimCount = count($createdClaims);
            $claimNumbers = implode(', ', array_map(fn($c) => $c->claim_number, $createdClaims));
            
            if ($claimCount === 1) {
                return redirect()->route('facility.claims.show', $createdClaims[0]->id)
                    ->with('success', "Claim created successfully. Claim Number: {$createdClaims[0]->claim_number}");
            } else {
                return redirect()->route('facility.claims.list')
                    ->with('success', "{$claimCount} claims created successfully. Claim Numbers: {$claimNumbers}");
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Billable claim creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->withInput()->with('error', 'Error creating claim: ' . $e->getMessage());
        }
    }
}
