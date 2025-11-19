@extends('layouts.app')

@section('title', 'ID Card - ' . $beneficiary->fullname)

@section('content')
<style>
    .id-preview-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .id-header {
        text-align: center;
        padding: 20px 0 30px;
        border-bottom: 3px solid #3498db;
        margin-bottom: 30px;
    }
    
    .id-header h1 {
        color: #2c3e50;
        font-size: 28px;
        margin: 0 0 10px;
        font-weight: bold;
    }
    
    .id-header p {
        color: #7f8c8d;
        margin: 0;
    }
    
    .member-card {
        border: 2px solid #e0e0e0;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .member-card-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .member-card-header h2 {
        margin: 0;
        font-size: 18px;
    }
    
    .member-card-header .badge {
        background: rgba(255,255,255,0.3);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 11px;
    }
    
    .member-card-body {
        padding: 25px;
        display: flex;
        gap: 25px;
    }
    
    .member-photo {
        width: 140px;
        height: 170px;
        border: 4px solid #3498db;
        border-radius: 10px;
        overflow: hidden;
        background: #f8f9fa;
        flex-shrink: 0;
    }
    
    .member-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .member-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
        color: #bdc3c7;
    }
    
    .member-info {
        flex: 1;
    }
    
    .member-info-row {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .member-info-row:last-child {
        border-bottom: none;
    }
    
    .member-info-label {
        font-weight: bold;
        color: #2c3e50;
        width: 130px;
    }
    
    .member-info-value {
        color: #555;
    }
    
    .boschma-id {
        background: #3498db;
        color: white;
        padding: 12px;
        border-radius: 8px;
        margin-top: 15px;
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 2px;
    }
    
    .dependants-card {
        border: 2px solid #e0e0e0;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .dependants-card-header {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
        padding: 15px 20px;
    }
    
    .dependants-card-header h2 {
        margin: 0;
        font-size: 18px;
    }
    
    .dependants-card-body {
        padding: 25px;
    }
    
    .dependant-item {
        background: #f8f9fa;
        border-left: 4px solid #27ae60;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 20px;
    }
    
    .dependant-item:last-child {
        margin-bottom: 0;
    }
    
    .dependant-photo {
        width: 100px;
        height: 120px;
        border: 3px solid #27ae60;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        flex-shrink: 0;
    }
    
    .dependant-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .dependant-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #bdc3c7;
    }
    
    .dependant-info-badge {
        background: #27ae60;
        color: white;
        display: inline-block;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 10px;
        text-transform: uppercase;
    }
    
    .dependant-name {
        font-size: 16px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 8px;
    }
    
    .dependant-details {
        font-size: 13px;
        color: #555;
    }
    
    .dependant-details p {
        margin: 4px 0;
    }
    
    .no-dependants {
        text-align: center;
        padding: 40px;
        color: #95a5a6;
        font-style: italic;
    }
    
    .action-buttons {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #ecf0f1;
    }
</style>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="ti ti-id me-2"></i>
                    ID Card Preview
                </h4>
                <div>
                    <a href="{{ route('beneficiaries.show', $beneficiary->id) }}" class="btn btn-light me-2">
                        <i class="ti ti-arrow-left me-1"></i>
                        Back
                    </a>
                    <a href="{{ route('beneficiaries.id-card.download', $beneficiary->id) }}" class="btn btn-primary" target="_blank">
                        <i class="ti ti-eye me-1"></i>
                        View PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="id-preview-container">
                <!-- Header -->
                <div class="id-header">
                    <h1>BOSCHMA HEALTH PROGRAM</h1>
                    <p>Official Identification Card</p>
                </div>
                
                <!-- Beneficiary Card -->
                <div class="member-card">
                    <div class="member-card-header">
                        <h2>PRIMARY BENEFICIARY</h2>
                        <div class="badge">ACTIVE MEMBER</div>
                    </div>
                    
                    <div class="member-card-body">
                        <div class="member-photo">
                            @if($beneficiary->photo)
                                <img src="{{ asset('storage/' . $beneficiary->photo) }}" alt="Beneficiary Photo">
                            @else
                                <div class="member-photo-placeholder">👤</div>
                            @endif
                        </div>
                        
                        <div class="member-info">
                            <div class="member-info-row">
                                <div class="member-info-label">Full Name:</div>
                                <div class="member-info-value">{{ $beneficiary->fullname }}</div>
                            </div>
                            <div class="member-info-row">
                                <div class="member-info-label">Gender:</div>
                                <div class="member-info-value">{{ $beneficiary->gender }}</div>
                            </div>
                            <div class="member-info-row">
                                <div class="member-info-label">Date of Birth:</div>
                                <div class="member-info-value">{{ $beneficiary->date_of_birth ? \Carbon\Carbon::parse($beneficiary->date_of_birth)->format('d M Y') : 'N/A' }}</div>
                            </div>
                            <div class="member-info-row">
                                <div class="member-info-label">NIN:</div>
                                <div class="member-info-value">{{ $beneficiary->nin }}</div>
                            </div>
                            <div class="member-info-row">
                                <div class="member-info-label">Phone:</div>
                                <div class="member-info-value">{{ $beneficiary->phone_no ?? 'N/A' }}</div>
                            </div>
                            <div class="member-info-row">
                                <div class="member-info-label">Facility:</div>
                                <div class="member-info-value">{{ $beneficiary->facility->name ?? 'N/A' }}</div>
                            </div>
                            <div class="member-info-row">
                                <div class="member-info-label">Valid Until:</div>
                                <div class="member-info-value">{{ \Carbon\Carbon::parse($beneficiary->created_at)->addYears(5)->format('d M Y') }}</div>
                            </div>
                            <div class="boschma-id">{{ $beneficiary->boschma_no }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Dependants Section -->
                <div class="dependants-card">
                    <div class="dependants-card-header">
                        <h2>REGISTERED DEPENDANTS</h2>
                    </div>
                    
                    <div class="dependants-card-body">
                        @php
                            $hasDependants = $beneficiary->spouse || ($beneficiary->children && $beneficiary->children->count() > 0);
                        @endphp
                        
                        @if($hasDependants)
                            {{-- Spouse --}}
                            @if($beneficiary->spouse)
                                <div class="dependant-item">
                                    <div class="dependant-photo">
                                        @if($beneficiary->spouse->photo)
                                            <img src="{{ asset('storage/' . $beneficiary->spouse->photo) }}" alt="Spouse Photo">
                                        @else
                                            <div class="dependant-photo-placeholder">👤</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="dependant-info-badge">Spouse</div>
                                        <div class="dependant-name">{{ $beneficiary->spouse->name }}</div>
                                        <div class="dependant-details">
                                            <p><strong>ID:</strong> {{ $beneficiary->spouse->boschma_no }}</p>
                                            <p><strong>NIN:</strong> {{ $beneficiary->spouse->nin ?? 'N/A' }}</p>
                                            <p><strong>Gender:</strong> {{ $beneficiary->spouse->gender ?? 'N/A' }}</p>
                                            <p><strong>DOB:</strong> {{ $beneficiary->spouse->dob ? \Carbon\Carbon::parse($beneficiary->spouse->dob)->format('d M Y') : 'N/A' }}</p>
                                            <p><strong>Phone:</strong> {{ $beneficiary->spouse->phone ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Children --}}
                            @if($beneficiary->children && $beneficiary->children->count() > 0)
                                @foreach($beneficiary->children as $index => $child)
                                    <div class="dependant-item">
                                        <div class="dependant-photo">
                                            @if($child->photo)
                                                <img src="{{ asset('storage/' . $child->photo) }}" alt="Child Photo">
                                            @else
                                                <div class="dependant-photo-placeholder">👶</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="dependant-info-badge">Child {{ $index + 1 }}</div>
                                            <div class="dependant-name">{{ $child->name }}</div>
                                            <div class="dependant-details">
                                                <p><strong>ID:</strong> {{ $child->boschma_no }}</p>
                                                <p><strong>NIN:</strong> {{ $child->nin ?? 'N/A' }}</p>
                                                <p><strong>Gender:</strong> {{ $child->gender ?? 'N/A' }}</p>
                                                <p><strong>DOB:</strong> {{ $child->dob ? \Carbon\Carbon::parse($child->dob)->format('d M Y') : 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="no-dependants">
                                <p>No dependants registered for this beneficiary</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <p class="text-muted mb-3">
                        <i class="ti ti-info-circle me-1"></i>
                        This card is valid for 5 years from the date of issue
                    </p>
                    <p class="text-muted small">
                        <strong>Emergency Contact:</strong> {{ $beneficiary->phone_no ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
