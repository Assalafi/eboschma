<div class="main-menu main-sidebar main-sidebar-sticky side-menu bg-white">
    <div class="main-sidebar-header main-container-1 active bg-white">
        <div class="sidemenu-logo bg-white">
            <a class="main-logo" href="/">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img desktop-logo" alt="logo">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;" class="header-brand-img icon-logo"
                    alt="logo">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img desktop-logo theme-logo" alt="logo">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img icon-logo theme-logo" alt="logo">
            </a>
        </div>
        <div class="main-sidebar-body main-body-1 bg-white">
            <div class="slide-left disabled" id="slide-left"><i class="fe fe-chevron-left text-dark"></i></div>
            <ul class="menu-nav nav text-dark">
                <br><br>
                @php
                    $user = auth('staff')->user() ?? auth()->user();
                    $isCustomerCareOnly = false;
                    if ($user && method_exists($user, 'hasRole') && $user->hasRole('Customer Care')) {
                        if (!$user->hasAnyRole(['admin', 'Super Admin'])) {
                            $isCustomerCareOnly = true;
                        }
                    }
                @endphp
                @if(!$isCustomerCareOnly)
                @can('dashboard.view')
                    <li class="nav-header text-dark"><span class="nav-label text-dark">BOSCHMA</span></li>
                    <li class="nav-item @if (request()->routeIs('dashboard') || request()->is('/')) active @endif">
                        <a class="nav-link" href="/">
                            <i class="ti-home sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Dashboard</span>
                        </a>
                    </li>
                @endcan
                @can('beneficiary.view')
                    <!-- Beneficiaries -->
                    <li class="nav-item @if (request()->routeIs('beneficiaries.index') ||
                            request()->routeIs('beneficiaries.show') ||
                            request()->routeIs('beneficiaries.edit') ||
                            request()->routeIs('beneficiaries.create')) active @endif">
                        <a class="nav-link" href="{{ route('beneficiaries.index') }}">
                            <i class="ti-id-badge sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Beneficiaries</span>
                        </a>
                    </li>
                @endcan
                <!-- Referral System -->
                @can('referral.view')
                    <li class="nav-item @if (request()->routeIs('referrals.index') ||
                            request()->routeIs('referrals.show') ||
                            request()->routeIs('referrals.edit') ||
                            request()->routeIs('referrals.create') ||
                            request()->routeIs('referrals.analytics') ||
                            request()->routeIs('referrals.settings')) active @endif">
                        <a class="nav-link with-sub" href="javascript:void(0)">
                            <i class="ti-share sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Referral System</span>
                            <i class="angle fe fe-chevron-right text-dark"></i>
                        </a>
                        <ul class="nav-sub">
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('referrals.index')) active @endif"
                                    href="{{ route('referrals.index') }}">All Referrals</a>
                            </li>
                            {{-- <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('referrals.create')) active @endif"
                                    href="{{ route('referrals.create') }}">Create Referral</a>
                            </li> --}}
                            {{-- <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('referrals.analytics')) active @endif"
                                    href="{{ route('referrals.analytics') }}">Analytics</a>
                            </li>
                            @can('referral.settings')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('referrals.settings')) active @endif"
                                        href="{{ route('referrals.settings') }}">Settings</a>
                                </li>
                            @endcan --}}
                        </ul>
                    </li>
                @endcan
                @can('claim.view')
                    <!-- Claims -->
                    <li class="nav-item @if (request()->routeIs('claims.index') ||
                            request()->routeIs('claims.show') ||
                            request()->routeIs('claims.edit') ||
                            request()->routeIs('claims.create') ||
                            request()->routeIs('claims.facility.list')) active @endif">
                        <a class="nav-link with-sub" href="javascript:void(0)">
                            <i class="ti-receipt sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Claims</span>
                            <i class="angle fe fe-chevron-right text-dark"></i>
                        </a>
                        <ul class="nav-sub">
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('claims.index')) active @endif"
                                    href="{{ route('claims.index') }}">All Claims</a>
                            </li>
                            {{-- <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('claims.create')) active @endif"
                                    href="{{ route('claims.create') }}">New Claim</a>
                            </li> --}}
                        </ul>
                    </li>
                @endcan
                @can('id-card.view')
                    <!-- Generate ID Cards -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('beneficiaries.bulk-id-cards') }}">
                            <i class="fe fe-download sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Generate ID Cards</span>
                        </a>
                    </li>
                @endcan
                @can('contribution.view')
                    <!-- Civil Servants -->
                    <li class="nav-item @if (request()->routeIs('civil-servants.index') ||
                            request()->routeIs('civil-servants.show') ||
                            request()->routeIs('civil-servants.edit') ||
                            request()->routeIs('civil-servants.create')) active @endif">
                        <a class="nav-link" href="{{ route('civil-servants.index') }}">
                            <i class="ti-id-badge sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Civil Servants</span>
                        </a>
                    </li>
                    <!-- Contributions -->
                    <li class="nav-item @if (request()->routeIs('contributions.index') ||
                            request()->routeIs('contributions.show') ||
                            request()->routeIs('contributions.edit') ||
                            request()->routeIs('contributions.create')) active @endif">
                        <a class="nav-link" href="{{ route('contributions.index') }}">
                            <i class="fe fe-dollar-sign sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Contributions</span>
                        </a>
                    </li>
                @endcan
                @can('drug-store.view')
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('drug-store.index') ||
                                request()->routeIs('drug-store.show') ||
                                request()->routeIs('drug-store.stock-in-form')) active @endif"
                            href="{{ route('drug-store.index') }}">
                            <i class="fe fe-box sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Drug Store</span>
                        </a>
                    </li>
                @endcan
                @can('drug-stock-requests.view')
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('drug-stock-requests.index') ||
                                request()->routeIs('drug-stock-requests.show') ||
                                request()->routeIs('drug-stock-requests.create') ||
                                request()->routeIs('drug-stock-requests.edit')) active @endif"
                            href="{{ route('drug-stock-requests.index') }}">
                            <i class="fe fe-shopping-cart sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Pharmacy Requests</span>
                        </a>
                    </li>
                @endcan
                @can('report.view')
                    <li class="nav-item">
                        <a class="nav-link with-sub" href="javascript:void(0)">
                            <i class="ti-bar-chart sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Reports</span>
                            <i class="angle fe fe-chevron-right text-dark"></i>
                        </a>
                        <ul class="nav-sub">
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if(request()->routeIs('reports.index') || request()->routeIs('reports.enumerators') || request()->routeIs('reports.facilities') || request()->routeIs('reports.enrollments')) active @endif"
                                    href="{{ route('reports.index') }}">Enrollment Reports</a>
                            </li>
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if(request()->routeIs('reports.ehr') || request()->routeIs('reports.ehr.export')) active @endif"
                                    href="{{ route('reports.ehr') }}">EHR Reports</a>
                            </li>
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if(request()->routeIs('reports.crm')) active @endif"
                                    href="{{ route('reports.crm') }}">CRM Reports</a>
                            </li>
                        </ul>
                    </li>
                @endcan
                @can('settings.view')
                    <!-- Settings -->
                    <li class="nav-item">
                        <a class="nav-link with-sub" href="javascript:void(0)">
                            <i class="fe fe-settings sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Settings</span>
                            <i class="angle fe fe-chevron-right text-dark"></i>
                        </a>
                        <ul class="nav-sub">
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('facilities.index') ||
                                        request()->routeIs('facilities.show') ||
                                        request()->routeIs('facilities.edit') ||
                                        request()->routeIs('facilities.create')) active @endif"
                                    href="{{ route('facilities.index') }}">Facilities</a>
                            </li>
                            @can('wards.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('wards.index') || request()->routeIs('wards.edit') || request()->routeIs('wards.create')) active @endif"
                                        href="{{ route('wards.index') }}">Wards</a>
                                </li>
                            @endcan
                            @can('rooms.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('rooms.index') || request()->routeIs('rooms.edit') || request()->routeIs('rooms.create')) active @endif"
                                        href="{{ route('rooms.index') }}">Rooms</a>
                                </li>
                            @endcan
                            @can('beds.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('beds.index') || request()->routeIs('beds.edit') || request()->routeIs('beds.create')) active @endif"
                                        href="{{ route('beds.index') }}">Beds</a>
                                </li>
                            @endcan
                            @can('nurse-ward.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('nurse-ward.index') ||
                                            request()->routeIs('nurse-ward.edit') ||
                                            request()->routeIs('nurse-ward.create')) active @endif"
                                        href="{{ route('nurse-ward.index') }}">Nurse Ward</a>
                                </li>
                            @endcan
                            @can('doctor-ward.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('doctor-ward.index') ||
                                            request()->routeIs('doctor-ward.edit') ||
                                            request()->routeIs('doctor-ward.create')) active @endif"
                                        href="{{ route('doctor-ward.index') }}">
                                        <i class="ti-medical me-2"></i>Doctor Ward
                                    </a>
                                </li>
                            @endcan
                            @can('facility-services.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('facility-services.index') || request()->routeIs('facility-services.create')) active @endif"
                                        href="{{ route('facility-services.index') }}">Facility Services</a>
                                </li>
                            @endcan
                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('beneficiary-categories.*')) active @endif"
                                    href="{{ route('beneficiary-categories.index') }}">Beneficiary Categories</a>
                            </li>
                            @can('drugs.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('drugs.index') ||
                                            request()->routeIs('drugs.show') ||
                                            request()->routeIs('drugs.edit') ||
                                            request()->routeIs('drugs.create') ||
                                            request()->routeIs('drugs.bulk.create')) active @endif"
                                        href="{{ route('drugs.index') }}">Drugs</a>
                                </li>
                            @endcan
                            @can('services.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('services.index') ||
                                            request()->routeIs('services.show') ||
                                            request()->routeIs('services.edit') ||
                                            request()->routeIs('services.create') ||
                                            request()->routeIs('services.bulk.create')) active @endif"
                                        href="{{ route('services.index') }}">Services</a>
                                </li>
                            @endcan
                            {{-- Services Items --}}
                            @can('service-categories.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('service-categories.index') ||
                                            request()->routeIs('service-categories.create') ||
                                            request()->routeIs('service-categories.edit')) active @endif"
                                        href="{{ route('service-categories.index') }}">Service Categories</a>
                                </li>
                            @endcan
                            @can('service-types.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('service-types.index') ||
                                            request()->routeIs('service-types.create') ||
                                            request()->routeIs('service-types.edit')) active @endif"
                                        href="{{ route('service-types.index') }}">Service Types</a>
                                </li>
                            @endcan
                            @can('service-items.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('service-items.index') ||
                                            request()->routeIs('service-items.create') ||
                                            request()->routeIs('service-items.edit')) active @endif"
                                        href="{{ route('service-items.index') }}">Service Items</a>
                                </li>
                            @endcan
                            @can('laboratory-tests.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('laboratory-tests.index') ||
                                            request()->routeIs('laboratory-tests.create') ||
                                            request()->routeIs('laboratory-tests.edit') ||
                                            request()->routeIs('laboratory-tests.bulk.create') ||
                                            request()->routeIs('laboratory-tests.upload')) active @endif"
                                        href="{{ route('laboratory-tests.index') }}">Laboratory Tests</a>
                                </li>
                            @endcan

                            @can('icd-codes.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('icd-codes.index') ||
                                            request()->routeIs('icd-codes.create') ||
                                            request()->routeIs('icd-codes.edit') ||
                                            request()->routeIs('icd-codes.bulk.create') ||
                                            request()->routeIs('icd-codes.upload')) active @endif"
                                        href="{{ route('icd-codes.index') }}">ICD Codes</a>
                                </li>
                            @endcan

                            <li class="nav-sub-item">
                                <a class="nav-sub-link text-dark @if (request()->routeIs('programs.index') ||
                                        request()->routeIs('programs.show') ||
                                        request()->routeIs('programs.edit') ||
                                        request()->routeIs('programs.create')) active @endif"
                                    href="{{ route('programs.index') }}">Programs</a>
                            </li>
                        </ul>
                    </li>
                @endcan
                <!-- User Management (Admin Only) -->
                @canany(['staff.view', 'role.view'])
                    <li class="nav-item">
                        <a class="nav-link with-sub" href="javascript:void(0)">
                            <i class="fe fe-users sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">User Management</span>
                            <i class="angle fe fe-chevron-right text-dark"></i>
                        </a>
                        <ul class="nav-sub">
                            @can('staff.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('staff.index') ||
                                            request()->routeIs('staff.show') ||
                                            request()->routeIs('staff.edit') ||
                                            request()->routeIs('staff.create')) active @endif"
                                        href="{{ route('staff.index') }}">Staff Members</a>
                                </li>
                            @endcan

                            @can('facility-staff.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('facility-staff.index') ||
                                            request()->routeIs('facility-staff.create') ||
                                            request()->routeIs('facility-staff.edit')) active @endif"
                                        href="{{ route('facility-staff.index') }}">Facility Staff</a>
                                </li>
                            @endcan

                            @can('staff-positions.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('staff-positions.index') ||
                                            request()->routeIs('staff-positions.create') ||
                                            request()->routeIs('staff-positions.edit') ||
                                            request()->routeIs('staff-positions.bulk.create')) active @endif"
                                        href="{{ route('staff-positions.index') }}">Staff Positions</a>
                                </li>
                            @endcan

                            @can('role.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('roles.index') ||
                                            request()->routeIs('roles.show') ||
                                            request()->routeIs('roles.edit') ||
                                            request()->routeIs('roles.create')) active @endif"
                                        href="{{ route('roles.index') }}">Roles</a>
                                </li>
                            @endcan
                            @can('role.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('permissions.index') || request()->routeIs('permissions.create')) active @endif"
                                        href="{{ route('permissions.index') }}">Permissions</a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcanany
                @endif

                <!-- CRM / Customer Care -->
                @can('crm.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('crm.index') }}">
                            <i class="ti-headphone-alt sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Customer Care</span>
                        </a>
                    </li>
                @endcan

            </ul>
            <div class="slide-right" id="slide-right"><i class="fe fe-chevron-right text-dark"></i></div>
        </div>
    </div>
</div>

<style>
    /* AGGRESSIVE FIX - Remove all dark corners and shadows */
    .main-sidebar,
    .main-sidebar *,
    .main-sidebar::before,
    .main-sidebar::after,
    .main-sidebar *::before,
    .main-sidebar *::after,
    .side-menu::before,
    .side-menu::after {
        box-shadow: none !important;
        border: none !important;
    }

    /* Force white background on entire sidebar */
    .main-sidebar,
    .main-sidebar-header,
    .main-sidebar-body,
    .sidemenu-logo {
        background: #ffffff !important;
        background-color: #ffffff !important;
    }

    /* Remove all backgrounds from nav items by default */
    .menu-nav .nav-item,
    .menu-nav .nav-item .nav-link {
        background: transparent !important;
        position: relative;
        overflow: visible !important;
    }

    /* Remove ALL pseudo elements */
    .menu-nav .nav-item::before,
    .menu-nav .nav-item::after,
    .menu-nav .nav-item .nav-link::before,
    .menu-nav .nav-item .nav-link::after,
    .menu-nav::before,
    .menu-nav::after {
        display: none !important;
        content: none !important;
    }

    /* Active state - green background with white text */
    .menu-nav .nav-item.active .nav-link {
        background-color: #016634 !important;
        background: #016634 !important;
        border-radius: 8px !important;
        margin: 4px 8px !important;
    }

    .menu-nav .nav-item.active .nav-link .sidemenu-icon,
    .menu-nav .nav-item.active .nav-link .sidemenu-label {
        color: #ffffff !important;
    }

    /* Hover state - light green background with green text */
    .menu-nav .nav-item .nav-link:hover {
        background-color: #e8f5e9 !important;
        background: #e8f5e9 !important;
        border-radius: 8px !important;
        margin: 4px 8px !important;
    }

    .menu-nav .nav-item .nav-link:hover .sidemenu-icon,
    .menu-nav .nav-item .nav-link:hover .sidemenu-label,
    .menu-nav .nav-item .nav-link:hover .angle {
        color: #016634 !important;
    }

    /* Sub-menu fixes */
    .nav-sub,
    .nav-sub .nav-sub-item,
    .nav-sub .nav-sub-link {
        background: transparent !important;
    }

    .nav-sub .nav-sub-item .nav-sub-link:hover {
        background-color: #e8f5e9 !important;
        color: #016634 !important;
    }

    .nav-sub .nav-sub-item .nav-sub-link.active {
        background-color: #c8e6c9 !important;
        color: #016634 !important;
        font-weight: 600;
    }
</style>
