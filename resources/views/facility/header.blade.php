<div class="main-container container-fluid bg-white">
    <div class="main-header-left bg-white">
        <a class="main-header-menu-icon text-dark" href="javascript:void(0)" id="mainSidebarToggle"><span
                class="bg-dark"></span></a>
        <div class="hor-logo bg-white">
            <a class="main-logo" href="{{ route('facility.dashboard') }}">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img desktop-logo" alt="logo">
                <img src="{{ url('assets/img/brand/logo.png') }}" style="width: 70px;"
                    class="header-brand-img desktop-logo-light" alt="logo">
            </a>
        </div>
    </div>

    <!-- Header Title -->
    <div class="d-flex align-items-center flex-grow-1 ms-3">
        <div class="header-title-wrapper">
            <!-- Mobile: Show only BOSCHMA -->
            <h1 class="header-title mb-0 d-md-none"
                style="color: #01542B; font-size: 18px; font-weight: 700; line-height: 1.2; text-transform: uppercase; letter-spacing: 0.3px;">
                BOSCHMA
            </h1>

            <!-- Desktop: Show full name -->
            <h1 class="header-title mb-0 d-none d-md-block"
                style="color: #01542B; font-size: 18px; font-weight: 700; line-height: 1.2; text-transform: uppercase; letter-spacing: 0.3px;">
                BORNO STATE CONTRIBUTORY HEALTH CARE MANAGEMENT AGENCY
            </h1>
            <p class="header-subtitle mb-0 d-none d-md-block"
                style="color: #01542B; font-size: 14px; font-weight: 500; margin-top: 2px;">
                Facility Staff Portal
            </p>
        </div>
    </div>

    <div class="main-header-right">
        <button class="navbar-toggler navresponsive-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="fe fe-more-vertical header-icons navbar-toggler-icon"></i>
        </button><!-- Navresponsive closed -->
        <div class="navbar navbar-expand-lg nav nav-item navbar-nav-right responsive-navbar navbar-dark">
            <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                <div class="d-flex order-lg-2 ms-auto">
                    <!-- Search -->
                    <div class="dropdown header-search">
                        <a class="nav-link icon header-search">
                            <i class="fe fe-search header-icons"></i>
                        </a>
                        <div class="dropdown-menu">
                            <div class="main-form-search p-2">
                                <div class="input-group">
                                    <input type="search" class="form-control" placeholder="Search for anything...">
                                    <button class="btn search-btn"><svg xmlns="http://www.w3.org/2000/svg"
                                            width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-search">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Search -->
                    <!-- Theme-Layout -->
                    <div class="dropdown d-flex main-header-theme">
                        <a class="nav-link icon layout-setting">
                            <span class="dark-layout">
                                <i class="fe fe-sun header-icons"></i>
                            </span>
                            <span class="light-layout">
                                <i class="fe fe-moon header-icons"></i>
                            </span>
                        </a>
                    </div>
                    <!-- Theme-Layout -->
                    <!-- Full screen -->
                    <div class="dropdown ">
                        <a class="nav-link icon full-screen-link">
                            <i class="fe fe-maximize fullscreen-button fullscreen header-icons"></i>
                            <i class="fe fe-minimize fullscreen-button exit-fullscreen header-icons"></i>
                        </a>
                    </div>
                    <!-- Full screen -->

                    <!-- Profile -->
                    <div class="dropdown main-profile-menu">
                        <a class="d-flex" href="javascript:void(0)">
                            <span class="main-img-user"><img alt="avatar"
                                    src="{{ url('assets/img/brand/logo.png') }}"></span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="header-navheading">
                                <h6 class="main-notification-title">{{ Auth::guard('web')->user()->name }}</h6>
                                @if (Auth::guard('web')->user()->facility)
                                    <p class="text-muted">{{ Auth::guard('web')->user()->facility->name }}</p>
                                @endif
                            </div>
                            <a class="dropdown-item border-top" href="{{ route('facility.profile') }}">
                                <i class="fe fe-user"></i> My Profile
                            </a>
                            <a class="dropdown-item" href="{{ route('facility.settings') }}">
                                <i class="fe fe-settings"></i> Account Settings
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fe fe-help-circle"></i> Support
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fe fe-compass"></i> Activity
                            </a>
                            <form action="{{ route('facility.logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fe fe-power"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Profile -->
                </div>
            </div>
        </div>
    </div>
</div>
