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
                <li class="nav-header text-dark"><span class="nav-label text-dark">BOSCHMA</span></li>
                <li class="nav-item @if (request()->routeIs('dashboard') || request()->is('/')) active @endif">
                    <a class="nav-link" href="/">
                        <i class="ti-home sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Dashboard</span>
                    </a>
                </li>

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

                <!-- Generate ID Cards -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('beneficiaries.bulk-id-cards') }}">
                        <i class="fe fe-download sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Generate ID Cards</span>
                    </a>
                </li>

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

                <!-- Reports -->
                <li class="nav-item @if (request()->routeIs('reports.index') || request()->routeIs('reports.*')) active @endif">
                    <a class="nav-link" href="{{ route('reports.index') }}">
                        <i class="ti-bar-chart sidemenu-icon menu-icon text-dark"></i>
                        <span class="sidemenu-label text-dark">Reports</span>
                    </a>
                </li>

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
                        <li class="nav-sub-item">
                            <a class="nav-sub-link text-dark @if (request()->routeIs('programs.index') ||
                                    request()->routeIs('programs.show') ||
                                    request()->routeIs('programs.edit') ||
                                    request()->routeIs('programs.create')) active @endif"
                                href="{{ route('programs.index') }}">Programs</a>
                        </li>
                    </ul>
                </li>

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
                            @can('role.view')
                                <li class="nav-sub-item">
                                    <a class="nav-sub-link text-dark @if (request()->routeIs('roles.index') ||
                                            request()->routeIs('roles.show') ||
                                            request()->routeIs('roles.edit') ||
                                            request()->routeIs('roles.create')) active @endif"
                                        href="{{ route('roles.index') }}">Roles & Permissions</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

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
