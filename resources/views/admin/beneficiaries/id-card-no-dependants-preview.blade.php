@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">ID Card Preview</h4>
                    <p class="text-muted mb-0">{{ $beneficiary->fullname }} &mdash; <span class="badge bg-info">{{ $beneficiary->program->name ?? 'No Dependants' }}</span></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('beneficiaries.show', $beneficiary->id) }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-1"></i> Back
                    </a>
                    <a href="{{ route('beneficiaries.id-card.download', $beneficiary->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fe fe-download me-1"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5" style="background: #e9ecef;">
                    {{-- Card labels --}}
                    <div class="d-flex justify-content-center gap-5 mb-2">
                        <div class="text-center" style="width: 340px;"><small class="text-muted fw-bold">FRONT</small></div>
                        <div class="text-center" style="width: 340px;"><small class="text-muted fw-bold">BACK</small></div>
                    </div>

                    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                        {{-- ===== FRONT CARD (CR80 ratio: 340px x 214px) ===== --}}
                        <div style="width: 340px; height: 214px; background: #fff; border-radius: 4px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); overflow: hidden; border: 1px solid #999; position: relative;">
                            {{-- Watermark --}}
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.05; z-index: 0;">
                                <img src="{{ asset('assets/img/brand/logo.png') }}" style="width: 130px;" alt="">
                            </div>

                            {{-- Header with absolute logos --}}
                            <div style="position: relative; z-index: 1; border-bottom: 1.5px solid #016634;">
                                {{-- Left logo: absolute --}}
                                <img src="{{ asset('assets/img/brand/logo.png') }}" style="position: absolute; left: 5px; top: 22px; width: 42px; max-height: 38px; z-index: 2;" alt="">
                                {{-- Right logo: absolute --}}
                                @if($beneficiary->program && $beneficiary->program->logo)
                                    <img src="{{ asset('storage/' . $beneficiary->program->logo) }}" style="position: absolute; right: 5px; top: 22px; width: 42px; max-height: 38px; z-index: 2;" alt="">
                                @else
                                    <img src="{{ asset('assets/img/brand/logo.png') }}" style="position: absolute; right: 5px; top: 22px; width: 42px; max-height: 38px; z-index: 2;" alt="">
                                @endif

                                {{-- Agency name --}}
                                <div style="text-align: center; padding: 4px 8px 0;">
                                    <div style="font-size: 8px; font-weight: 900; text-transform: uppercase; color: #000; line-height: 1.15;">BORNO STATE CONTRIBUTORY HEALTH<br>CARE MANAGEMENT AGENCY (BOSCHMA)</div>
                                </div>
                                {{-- Tagline + Scheme: padded to clear logos --}}
                                <div style="text-align: center; padding: 1px 52px 0;">
                                    <div style="font-size: 6px; font-style: italic; color: #016634; font-weight: 600;">Wellness For Sustainable Development</div>
                                    <div style="font-size: 7.5px; font-weight: 900; color: #000; text-transform: uppercase; border-bottom: 1.5px solid #c00; padding-bottom: 1px;">{{ strtoupper($beneficiary->program->description ?? $beneficiary->program->name ?? 'HEALTH CARE PROGRAM') }}</div>
                                </div>
                                {{-- Address --}}
                                <div style="text-align: center; padding: 1px 8px 2px;">
                                    <div style="font-size: 6px; color: #333; line-height: 1.3;">
                                        No. 7 Lagos Street, Adjacent Lagos House Maiduguri, Borno State<br>
                                        08122224040 | info@boschma.bo.gov.ng | boschma.bornostate.gov.ng
                                    </div>
                                </div>
                            </div>

                            {{-- Body: QR | Info | Photo --}}
                            <div style="display: flex; padding: 4px 5px 0; position: relative; z-index: 1;">
                                <div style="width: 58px; padding-top: 1px;">
                                    <div style="width: 54px; height: 54px; border: 1px solid #ddd; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 7px; color: #aaa;">QR</div>
                                </div>
                                <div style="flex: 1; padding: 0 4px;">
                                    <div style="margin-bottom: 1px;">
                                        <div style="font-size: 6px; font-weight: 700; color: #016634;">Name</div>
                                        <div style="font-size: 9px; font-weight: 900; color: #000;">{{ strtoupper($beneficiary->fullname) }}</div>
                                    </div>
                                    <div style="margin-bottom: 1px;">
                                        <div style="font-size: 6px; font-weight: 700; color: #016634;">Gender</div>
                                        <div style="font-size: 9px; font-weight: 900; color: #000;">{{ strtoupper($beneficiary->gender) }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 6px; font-weight: 700; color: #016634;">Category</div>
                                        <div style="font-size: 9px; font-weight: 900; color: #000;">{{ strtoupper($beneficiary->category ?? 'N/A') }}</div>
                                    </div>
                                </div>
                                <div style="width: 74px; text-align: center;">
                                    <div style="width: 70px; height: 86px; border: 1px solid #016634; overflow: hidden; background: #f5f5f5;">
                                        @if($beneficiary->photo)
                                            <img src="{{ asset('storage/' . $beneficiary->photo) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0f0f0; color: #999; font-size: 20px;">&#128100;</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Bottom: Facility + ID --}}
                            <div style="display: flex; padding: 1px 5px 3px; position: relative; z-index: 1; align-items: flex-end;">
                                <div style="flex: 1;">
                                    <div style="font-size: 6px; font-weight: 700; color: #016634;">Facility</div>
                                    <div style="font-size: 9px; font-weight: 900; color: #000;">{{ strtoupper($beneficiary->facility->name ?? 'N/A') }}</div>
                                </div>
                                <div style="width: 74px; text-align: center;">
                                    <div style="font-size: 11px; font-weight: 900; color: #000;">{{ $beneficiary->boschma_no }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== BACK CARD (CR80 ratio: 340px x 214px) ===== --}}
                        <div style="width: 340px; height: 214px; background: #fff; border-radius: 4px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); overflow: hidden; border: 1px solid #999; position: relative; display: flex; align-items: center; justify-content: center;">
                            {{-- Watermark --}}
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.05; z-index: 0;">
                                <img src="{{ asset('assets/img/brand/logo.png') }}" style="width: 130px;" alt="">
                            </div>

                            <div style="text-align: center; padding: 12px 14px; position: relative; z-index: 1;">
                                <div style="font-size: 8px; font-weight: 700; color: #000; text-transform: uppercase; line-height: 1.5; margin-bottom: 6px;">
                                    THIS IS TO CERTIFY THAT THE PERSON WHOSE NAME AND
                                    PHOTOGRAPH APPEARS OVERLEAF IS A BENEFICIARY OF
                                    {{ strtoupper($beneficiary->program->description ?? $beneficiary->program->name ?? 'HEALTH CARE PROGRAM') }} UNDER THE BORNO STATE CONTRIBUTORY HEALTH CARE
                                    MANAGEMENT AGENCY
                                </div>
                                <div style="font-size: 7.5px; font-weight: 700; color: #000; text-transform: uppercase; line-height: 1.5; margin-bottom: 6px;">
                                    IF FOUND, PLEASE RETURN TO THE
                                    ADDRESS OVERLEAF OR THE NEAREST
                                    POLICE STATION.
                                </div>
                                <div>
                                    <img src="{{ asset('assets/img/brand/sign.png') }}" style="width: 52px;" alt="Signature">
                                    <div style="font-size: 7px; font-weight: 900; text-transform: uppercase; margin-top: 1px;">AUTHORISED SIGNATORY</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <span class="badge bg-success bg-gradient px-3 py-2">
                            <i class="fe fe-info me-1"></i> {{ $beneficiary->program->name ?? 'Program' }} Card &mdash; Standard CR80 size (85.6mm &times; 54mm) &mdash; 5 per page in bulk PDF
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
