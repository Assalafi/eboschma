<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="BOSCHMA - Enrollment Admin Panel">
<meta name="author" content="BOSCHMA">
<meta name="keywords" content="admin,dashboard,panel,bootstrap admin template,enrollment,student,management,education">

<!-- Favicon -->
<link rel="icon" href="{{ url('assets/img/brand/icon.png') }}" type="image/x-icon" />

<!-- Title -->
<title>BOSCHMA | ENROLLMENT</title>

<!-- Bootstrap css-->
<link id="style" href="{{ url('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

<link href="{{ url('assets/plugins/datatable/css/dataTables.bootstrap5.css') }}" rel="stylesheet" />
<link href="{{ url('assets/plugins/datatable/css/buttons.bootstrap5.min.css') }}" rel="stylesheet">
<link href="{{ url('assets/plugins/datatable/css/responsive.bootstrap5.css') }}" rel="stylesheet" />

<!-- Icons css-->
<link href="{{ url('assets/plugins/web-fonts/icons.css') }}" rel="stylesheet" />
<link href="{{ url('assets/plugins/web-fonts/font-awesome/font-awesome.min.css') }}" rel="stylesheet">
<link href="{{ url('assets/plugins/web-fonts/plugin.css') }}" rel="stylesheet" />
<!-- Font Awesome 6 Online -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Font Awesome Fallback -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">

<!-- Style css-->
<link href="{{ url('assets/css/style.css') }}" rel="stylesheet">

<!-- Select2 css -->
<link href="{{ url('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet">

<!-- Bootstrap Datepicker css -->
<link href="{{ url('assets/plugins/bootstrap-datepicker/bootstrap-datepicker.css') }}" rel="stylesheet">

<!-- Date Range Picker css -->
<link href="{{ url('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">

<!-- Owl-carousel css-->
<link href="{{ url('assets/plugins/owl-carousel/owl.carousel.css') }}" rel="stylesheet" />

<!-- Mutipleselect css-->
<link rel="stylesheet" href="{{ url('assets/plugins/multipleselect/multiple-select.css') }}">


<!-- InternalFileupload css-->
<link href="{{ url('assets/plugins/fileuploads/css/fileupload.css') }}" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .select2 {
        z-index: 9999 !important;
        /* Ensure it appears above the modal */
    }

    /* Ensure Font Awesome icons are visible */
    .fas,
    .far,
    .fab {
        display: inline-block !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        line-height: 1 !important;
    }

    /* Avatar icon styling */
    .avatar .fas {
        font-size: 1.2rem !important;
        width: 1.2rem !important;
        text-align: center !important;
    }

    /* Button icon styling */
    .btn .fas {
        font-size: 1rem !important;
        width: 1rem !important;
        text-align: center !important;
    }

    /* Modern hover effects for cards */
    .hover-lift {
        transition: all 0.3s ease;
        transform: translateY(0);
    }

    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
    }

    /* Enhanced gradient backgrounds */
    .bg-gradient {
        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 100%);
    }

    /* Modern badge styling */
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
    }

    /* Enhanced card styling */
    .card {
        transition: all 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    /* Modern button styling */
    .btn {
        transition: all 0.2s ease;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced avatar styling */
    .avatar {
        transition: all 0.2s ease;
    }

    .avatar:hover {
        transform: scale(1.05);
    }

    /* Modern table styling */
    .table {
        font-size: 0.9rem;
    }

    .table th {
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    /* Enhanced metrics display */
    .h3 {
        font-weight: 700;
        line-height: 1.2;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }
</style>
