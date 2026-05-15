<div class="main-menu main-sidebar main-sidebar-sticky side-menu bg-white">
    <div class="main-sidebar-header main-container-1 active bg-white">
        <div class="sidemenu-logo bg-white">
            <a class="main-logo" href="{{ route('facility.dashboard') }}">
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
                <li class="nav-header text-dark"><span class="nav-label text-dark">FACILITY PORTAL</span></li>
                <li class="nav-item @if (request()->routeIs('facility.dashboard')) active @endif">
                    <a class="nav-link" href="{{ route('facility.dashboard') }}">
                        <i class="ti-home sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item @if (request()->routeIs('facility.patients.*')) active @endif">
                    <a class="nav-link" href="{{ route('facility.patients.index') }}">
                        <i class="ti-user sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Patients</span>
                    </a>
                </li>

                @php
                    $adminPositions = ['Hospital Administrator', 'Admin', 'AAAA', 'AAA', 'AA'];
                    $pharmacyPositions = ['Chief Pharmacist', 'Pharmacist', 'Hospital Administrator', 'Admin'];
                    $userPosition = Auth::guard('web')->user()->staffPosition->name ?? '';
                    $isAdmin = in_array($userPosition, $adminPositions);
                    $isPharmacyAdmin = in_array($userPosition, $pharmacyPositions);
                @endphp

                @if ($isAdmin)
                    <li class="nav-item @if (request()->routeIs('facility.staff.*')) active @endif">
                        <a class="nav-link" href="{{ route('facility.staff.index') }}">
                            <i class="ti-user sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Staff</span>
                        </a>
                    </li>
                    <li class="nav-item @if (request()->routeIs('facility.nurse-ward.*')) active @endif">
                        <a class="nav-link" href="{{ route('facility.nurse-ward.index') }}">
                            <i class="ti-id-badge sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Nurse Ward</span>
                        </a>
                    </li>
                    <li class="nav-item @if (request()->routeIs('facility.doctor-ward.*')) active @endif">
                        <a class="nav-link" href="{{ route('facility.doctor-ward.index') }}">
                            <i class="ti-medical sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Doctor Ward</span>
                        </a>
                    </li>
                    <li class="nav-item @if (request()->routeIs('facility.services.*')) active @endif">
                        <a class="nav-link" href="{{ route('facility.services.index') }}">
                            <i class="ti-briefcase sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Services</span>
                        </a>
                    </li>
                @endif

                @if ($isPharmacyAdmin)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('facility.pharmacy.index') }}">
                            <i class="ti-package sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Pharmacy</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('facility.pharmacy.stock-requests') }}">
                            <i class="ti-layers-alt sidemenu-icon menu-icon text-dark"></i>
                            <span class="sidemenu-label text-dark">Stock Requests</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item @if (request()->routeIs('facility.encounters.*')) active @endif">
                    <a class="nav-link" href="{{ route('facility.encounters.index') }}">
                        <i class="ti-clipboard sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Patient History</span>
                    </a>
                </li>

                <li class="nav-item @if (request()->routeIs('facility.claims.billable')) active @endif">
                    <a class="nav-link" href="{{ route('facility.claims.billable') }}">
                        <i class="ti-money sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Claims</span>
                    </a>
                </li>

                <li class="nav-item @if (request()->routeIs('facility.claims.list') || request()->routeIs('facility.claims.show') || request()->routeIs('facility.claims.edit')) active @endif">
                    <a class="nav-link" href="{{ route('facility.claims.list') }}">
                        <i class="ti-receipt sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Claimed</span>
                    </a>
                </li>

                <li class="nav-item @if (request()->routeIs('facility.referrals.*')) active @endif">
                    <a class="nav-link" href="{{ route('facility.referrals.index') }}">
                        <i class="ti-exchange-vertical sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Referrals</span>
                    </a>
                </li>

                <li class="nav-label text-dark">Account</li>

                <li class="nav-item @if (request()->routeIs('facility.profile')) active @endif">
                    <a class="nav-link" href="{{ route('facility.profile') }}">
                        <i class="ti-user sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">My Profile</span>
                    </a>
                </li>

                <li class="nav-item @if (request()->routeIs('facility.settings')) active @endif">
                    <a class="nav-link" href="{{ route('facility.settings') }}">
                        <i class="ti-settings sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Settings</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="{{ route('facility.logout') }}"
                        onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                        <i class="ti-power-off sidemenu-icon menu-icon text-danger"></i>
                        <span class="sidemenu-label text-danger">Logout</span>
                    </a>
                    <form id="sidebar-logout-form" action="{{ route('facility.logout') }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                </li>
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

    /* Disabled state */
    .menu-nav .nav-item .nav-link.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .menu-nav .nav-item .nav-link.disabled:hover {
        background: transparent !important;
        margin: 0 !important;
    }

    .menu-nav .nav-item .nav-link.disabled .sidemenu-icon,
    .menu-nav .nav-item .nav-link.disabled .sidemenu-label {
        color: #6c757d !important;
    }
</style>
