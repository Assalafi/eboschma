@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>ID Card Preview - {{ $beneficiary->fullname }}</h2>
                <div>
                    <a href="{{ route('beneficiaries.show', $beneficiary->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a href="{{ route('beneficiaries.id-card.download', $beneficiary->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-5" style="background: #f5f5f5;">
                    <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                        <!-- FRONT CARD -->
                        <div style="width: 85.6mm; height: 54mm; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-radius: 3mm; box-shadow: 0 2mm 8mm rgba(0,0,0,0.15); overflow: hidden;">
                            <div style="background: white; padding: 2mm; display: flex; align-items: center; gap: 2mm; border-bottom: 1mm solid #4CAF50;">
                                <div style="width: 12mm; height: 12mm; background: #4CAF50; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 8pt;">
                                    LOGO
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 6.5pt; font-weight: bold; color: #2E7D32; line-height: 1.2; text-transform: uppercase;">
                                        BORNO STATE CONTRIBUTORY<br>HEALTHCARE MANAGEMENT AGENCY
                                    </div>
                                    <div style="font-size: 5pt; color: #1565C0; font-weight: bold; margin-top: 0.5mm;">
                                        FORMAL SECTOR SCHEME
                                    </div>
                                </div>
                            </div>
                            
                            <div style="padding: 3mm; display: flex; gap: 3mm; position: relative;">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <div style="width: 20mm; height: 25mm; border: 1pt solid #4CAF50; overflow: hidden; background: white;">
                                        @if($beneficiary->photo)
                                            <img src="{{ asset('storage/' . $beneficiary->photo) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f5f5f5; color: #999; font-size: 16pt;">👤</div>
                                        @endif
                                    </div>
                                    <div style="background: #4CAF50; color: white; padding: 1mm 2mm; font-size: 5pt; font-weight: bold; margin-top: 1mm; text-align: center;">
                                        PRINCIPAL
                                    </div>
                                    <div style="font-size: 4.5pt; color: #555; line-height: 1.4; margin-top: 1mm;">
                                        <div><strong>Issue:</strong> {{ \Carbon\Carbon::parse($beneficiary->created_at)->format('m/Y') }}</div>
                                        <div><strong>Expiry:</strong> {{ \Carbon\Carbon::parse($beneficiary->created_at)->addYears(5)->format('m/Y') }}</div>
                                    </div>
                                </div>
                                
                                <div style="flex: 1;">
                                    <div style="margin-bottom: 1.5mm; font-size: 5.5pt; line-height: 1.3;">
                                        <span style="font-weight: bold; color: #333;">Full Name:</span> {{ $beneficiary->fullname }}
                                    </div>
                                    <div style="margin-bottom: 1.5mm; font-size: 5.5pt; line-height: 1.3;">
                                        <span style="font-weight: bold; color: #333;">BOSCHMA No:</span> {{ $beneficiary->boschma_no }}
                                    </div>
                                    <div style="margin-bottom: 1.5mm; font-size: 5.5pt; line-height: 1.3;">
                                        <span style="font-weight: bold; color: #333;">Gender:</span> {{ $beneficiary->gender }}
                                    </div>
                                    <div style="margin-bottom: 1.5mm; font-size: 5.5pt; line-height: 1.3;">
                                        <span style="font-weight: bold; color: #333;">Primary Facility:</span> {{ $beneficiary->facility->name ?? 'N/A' }}
                                    </div>
                                </div>
                                
                                <div style="position: absolute; bottom: 0; right: 0; display: flex; gap: 2mm;">
                                    <div style="width: 10mm; height: 10mm; background: white; border: 0.5pt solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 4pt; color: #999;">
                                        QR
                                    </div>
                                    <div style="width: 18mm; height: 8mm; background: white; border: 0.5pt solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 4pt; color: #999;">
                                        ||||||||||||
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- BACK CARD -->
                        <div style="width: 85.6mm; height: 54mm; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-radius: 3mm; box-shadow: 0 2mm 8mm rgba(0,0,0,0.15); overflow: hidden; position: relative;">
                            <div style="background: #4CAF50; padding: 2mm; text-align: center; color: white;">
                                <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">DEPENDANTS</div>
                            </div>
                            
                            <div style="padding: 2mm; display: grid; grid-template-columns: 1fr 1fr; gap: 2mm; background: white; margin: 2mm; border: 1pt solid #4CAF50; min-height: 40mm;">
                                @php
                                    $deps = [];
                                    if($beneficiary->spouse) {
                                        $deps[] = [
                                            'label' => 'Spouse',
                                            'name' => $beneficiary->spouse->name,
                                            'boschma' => $beneficiary->spouse->boschma_no,
                                            'gender' => $beneficiary->spouse->gender ?? 'N/A',
                                            'photo' => $beneficiary->spouse->photo,
                                            'facility' => $beneficiary->facility->name ?? 'N/A'
                                        ];
                                    }
                                    if($beneficiary->children && $beneficiary->children->count() > 0) {
                                        foreach($beneficiary->children as $i => $child) {
                                            $deps[] = [
                                                'label' => 'Child ' . ($i + 1) . ' Name',
                                                'name' => $child->name,
                                                'boschma' => $child->boschma_no,
                                                'gender' => $child->gender ?? 'N/A',
                                                'photo' => $child->photo,
                                                'facility' => $beneficiary->facility->name ?? 'N/A'
                                            ];
                                        }
                                    }
                                @endphp
                                
                                @for($i = 0; $i < 5; $i++)
                                    @if(isset($deps[$i]))
                                        <div style="border: 1pt solid #e0e0e0; padding: 1.5mm;">
                                            <div style="width: 100%; height: 14mm; border: 1pt solid #4CAF50; overflow: hidden; background: white; margin-bottom: 1mm;">
                                                @if($deps[$i]['photo'])
                                                    <img src="{{ asset('storage/' . $deps[$i]['photo']) }}" alt="{{ $deps[$i]['label'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f5f5f5; color: #999; font-size: 14pt;">{{ $i === 0 ? '👤' : '👶' }}</div>
                                                @endif
                                            </div>
                                            <div style="font-size: 5pt; font-weight: bold; color: white; background: #333; padding: 0.5mm 1mm; margin-bottom: 1mm; text-align: center;">
                                                {{ $deps[$i]['label'] }}
                                            </div>
                                            <div style="font-size: 4.5pt; line-height: 1.4; color: #333;">
                                                <div style="font-weight: bold; margin-bottom: 0.5mm; font-size: 5pt;">Full Name: {{ $deps[$i]['name'] }} ({{ $i === 0 ? 'Spouse' : 'Child' }})</div>
                                                <div>BOSCHMA No: {{ $deps[$i]['boschma'] }}</div>
                                                <div>Gender: {{ $deps[$i]['gender'] }}</div>
                                                <div>{{ $deps[$i]['facility'] }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div style="border: 1pt dashed #e0e0e0; padding: 1.5mm; opacity: 0.3;">
                                            <div style="width: 100%; height: 14mm; border: 1pt solid #4CAF50; overflow: hidden; background: #fafafa; margin-bottom: 1mm;">
                                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ddd; font-size: 14pt;">-</div>
                                            </div>
                                            <div style="font-size: 5pt; font-weight: bold; color: white; background: #ddd; padding: 0.5mm 1mm; margin-bottom: 1mm; text-align: center;">
                                                {{ $i === 0 && count($deps) === 0 ? 'Spouse' : 'Child ' . $i }}
                                            </div>
                                            <div style="font-size: 4.5pt; line-height: 1.4; color: #ccc;">
                                                <div>Not registered</div>
                                            </div>
                                        </div>
                                    @endif
                                @endfor
                            </div>
                            
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: white; padding: 1.5mm; font-size: 4pt; color: #555; text-align: center; border-top: 0.5pt solid #e0e0e0; line-height: 1.3;">
                                This card is the property of BOSCHMA and is non-transferrable. Present only at the designated facility.<br>
                                For support, call: +234 800 123 4455 or visit www.boschma.bo.gov.ng
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted"><i class="fas fa-info-circle"></i> This is a preview. Click "Download PDF" to generate the printable version.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
