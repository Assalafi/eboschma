<!DOCTYPE html>
<html lang="en">
<head>
    @include('css')
    @stack('styles')
</head>
<body class="ltr main-body leftmenu">
    <!-- Loader -->
    <div id="global-loader">
        <img src="{{ url('assets/img/loader.svg') }}" class="loader-img" alt="Loader">
    </div>
    <!-- End Loader -->

    <!-- Page -->
    <div class="page">
        <!-- Main Header-->
        <div class="main-header side-header sticky">
            @include('header')
        </div>
        <!-- End Main Header-->

        <!-- Sidemenu -->
        <div class="sticky">
            @include('sidebar')
        </div>
        <!-- End Sidemenu -->

        <!-- Main Content-->
        <div class="main-content side-content pt-0">
            @yield('content')
        </div>
        <!-- End Main Content-->

        <!-- Main Footer-->
        <div class="main-footer text-center">
            @include('footer')
        </div>
        <!--End Footer-->
    </div>
    <!-- End Page -->

    <a href="#top" id="back-to-top"><i class="fe fe-arrow-up"></i></a>

    @include('script')
    @stack('scripts')
</body>
</html>
