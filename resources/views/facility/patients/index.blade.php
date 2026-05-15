@extends('layouts.facility')

@section('title', $tab === 'beneficiaries' ? 'Enrolled Beneficiaries' : 'Patients')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-md-flex justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-md-0">
                        <h1 class="page-title mb-2" style="color: #01542B; font-size: 24px; font-weight: 700;">
                            {{ $tab === 'beneficiaries' ? 'Enrolled Beneficiaries' : 'Patients' }}
                        </h1>
                        <p class="text-muted mb-0">
                            {{ $tab === 'beneficiaries' ? 'View beneficiaries enrolled to your facility' : 'Manage and view patient records for your facility' }}
                        </p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                            <i class="ti-printer me-1"></i> Print List
                        </button>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" style="border-bottom: 2px solid #e9ecef;">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'patients' ? 'active fw-bold' : '' }}"
                            href="{{ route('facility.patients.index', ['tab' => 'patients']) }}"
                            style="{{ $tab === 'patients' ? 'color: #01542B; border-color: #01542B #01542B #fff;' : 'color: #6c757d;' }}">
                            <i class="ti-heart-broken me-1"></i> Patients
                            <span class="badge bg-primary ms-1">{{ $stats['total_patients'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'beneficiaries' ? 'active fw-bold' : '' }}"
                            href="{{ route('facility.patients.index', ['tab' => 'beneficiaries']) }}"
                            style="{{ $tab === 'beneficiaries' ? 'color: #01542B; border-color: #01542B #01542B #fff;' : 'color: #6c757d;' }}">
                            <i class="ti-user me-1"></i> Enrolled Beneficiaries
                            <span
                                class="badge bg-success ms-1">{{ $stats['total_beneficiaries'] + $stats['total_spouses'] + $stats['total_children'] }}</span>
                        </a>
                    </li>
                </ul>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    @if ($tab === 'patients')
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-primary text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-heart-broken" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_patients'] }}</h3>
                                            <p class="text-muted mb-0 small">Total Patients</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-success text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-user" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-success">{{ $stats['total_beneficiaries'] }}</h3>
                                            <p class="text-muted mb-0 small">Enrolled Beneficiaries</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-info text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-id-badge" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-info">
                                                {{ $stats['total_beneficiaries'] + $stats['total_spouses'] + $stats['total_children'] }}
                                            </h3>
                                            <p class="text-muted mb-0 small">Total Enrollees</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-primary text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-user" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_beneficiaries'] }}</h3>
                                            <p class="text-muted mb-0 small">Beneficiaries</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-info text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-heart" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-info">{{ $stats['total_spouses'] }}</h3>
                                            <p class="text-muted mb-0 small">Spouses</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-success text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-face-smile" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-success">{{ $stats['total_children'] }}</h3>
                                            <p class="text-muted mb-0 small">Children</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg bg-warning text-white me-3"
                                            style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="ti-id-badge" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold text-warning">
                                                {{ $stats['total_beneficiaries'] + $stats['total_spouses'] + $stats['total_children'] }}
                                            </h3>
                                            <p class="text-muted mb-0 small">Total Enrollees</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Filters and Search -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <form method="GET" action="{{ route('facility.patients.index') }}">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold text-dark">
                                        Search {{ $tab === 'beneficiaries' ? 'Beneficiaries' : 'Patients' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="ti-search text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" name="search"
                                            value="{{ $search }}"
                                            placeholder="Search by name, {{ $tab === 'patients' ? 'file number' : 'BOSCHMA ID' }}...">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold text-dark">Enrollee Type</label>
                                    <select class="form-select" name="enrollee_type">
                                        <option value="">All Types</option>
                                        <option value="beneficiary"
                                            {{ $enrolleeType === 'beneficiary' ? 'selected' : '' }}>Beneficiary</option>
                                        <option value="spouse" {{ $enrolleeType === 'spouse' ? 'selected' : '' }}>Spouse
                                        </option>
                                        <option value="child" {{ $enrolleeType === 'child' ? 'selected' : '' }}>Child
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti-search me-2"></i>Search
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('facility.patients.index', ['tab' => $tab]) }}"
                                        class="btn btn-outline-secondary w-100">
                                        <i class="ti-refresh me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom" style="padding: 1.5rem;">
                        <h5 class="card-title mb-0 fw-bold" style="color: #01542B;">
                            @if ($tab === 'patients')
                                <i class="ti-heart-broken me-2 text-primary"></i>Patient Records
                            @else
                                <i class="ti-user me-2 text-primary"></i>Enrolled Beneficiaries
                            @endif
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($patients->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 fw-semibold text-dark">#</th>
                                            @if ($tab === 'patients')
                                                <th class="border-0 fw-semibold text-dark">File Number</th>
                                            @endif
                                            <th class="border-0 fw-semibold text-dark">BOSCHMA ID</th>
                                            <th class="border-0 fw-semibold text-dark">Name</th>
                                            <th class="border-0 fw-semibold text-dark">Type</th>
                                            <th class="border-0 fw-semibold text-dark">Gender</th>
                                            <th class="border-0 fw-semibold text-dark">Date of Birth</th>
                                            <th class="border-0 fw-semibold text-dark">Phone</th>
                                            <th class="border-0 fw-semibold text-dark">Registered</th>
                                            <th class="border-0 fw-semibold text-center text-dark">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($patients as $patient)
                                            <tr>
                                                <td class="align-middle">
                                                    <span
                                                        class="badge bg-light text-dark fw-semibold">{{ $loop->iteration + ($patients->currentPage() - 1) * $patients->perPage() }}</span>
                                                </td>
                                                @if ($tab === 'patients')
                                                    <td class="align-middle">
                                                        <span
                                                            class="badge bg-light text-dark fw-semibold">{{ $patient->file_number }}</span>
                                                    </td>
                                                @endif
                                                <td class="align-middle">
                                                    <span
                                                        class="fw-semibold text-primary">{{ $patient->enrollee_number }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        @if ($patient->photo)
                                                            <img src="{{ asset('storage/' . $patient->photo) }}"
                                                                class="avatar avatar-sm rounded-circle me-2"
                                                                style="width: 32px; height: 32px; object-fit: cover;">
                                                        @else
                                                            <div class="avatar avatar-sm rounded-circle bg-light me-2 d-flex align-items-center justify-content-center"
                                                                style="width: 32px; height: 32px;">
                                                                <i class="ti-user text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $patient->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    @if ($patient->enrollee_type === 'beneficiary')
                                                        <span class="badge bg-primary">Beneficiary</span>
                                                    @elseif($patient->enrollee_type === 'child')
                                                        <span class="badge bg-success">Child</span>
                                                    @else
                                                        <span class="badge bg-info">Spouse</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-muted">{{ $patient->gender ?? 'N/A' }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-muted">
                                                        @if ($patient->dob)
                                                            {{ \Carbon\Carbon::parse($patient->dob)->format('M d, Y') }}
                                                        @else
                                                            Not specified
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-muted">{{ $patient->phone ?? 'N/A' }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="text-muted">{{ $patient->created_at->format('M d, Y') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <div class="btn-group" role="group">
                                                        @if ($tab === 'patients')
                                                            <a href="{{ route('facility.patients.show', $patient->id) }}"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title="View Details">
                                                                <i class="ti-eye"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('facility.beneficiary.show', ['id' => $patient->id, 'type' => $patient->enrollee_type]) }}"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title="View Details">
                                                                <i class="ti-eye"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        Showing {{ $patients->firstItem() }} to {{ $patients->lastItem() }} of
                                        {{ $patients->total() }} entries
                                    </div>
                                    {{ $patients->appends(['tab' => $tab, 'search' => $search, 'enrollee_type' => $enrolleeType])->links('pagination.bootstrap-5') }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar avatar-lg bg-light text-muted mb-3"
                                    style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto;">
                                    <i class="ti-user" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="text-muted mb-2">
                                    {{ $tab === 'beneficiaries' ? 'No Beneficiaries Found' : 'No Patients Found' }}
                                </h5>
                                <p class="text-muted">
                                    {{ $tab === 'beneficiaries' ? 'No enrolled beneficiaries match your search criteria.' : 'No patient records match your search criteria.' }}
                                </p>
                                <a href="{{ route('facility.patients.index', ['tab' => $tab]) }}"
                                    class="btn btn-primary">
                                    <i class="ti-refresh me-2"></i>Clear Filters
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-tabs .nav-link {
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #01542B;
            color: #01542B;
        }

        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #01542B;
        }

        .table th {
            border-bottom: 2px solid #e9ecef !important;
        }

        .table td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f8f9fa !important;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .avatar {
            object-fit: cover;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        @media print {

            .btn,
            .btn-group,
            .card-header,
            .pagination,
            .nav-tabs {
                display: none !important;
            }

            .table {
                font-size: 12px;
            }
        }
    </style>
@endsection
