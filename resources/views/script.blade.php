<script src="{{ url('assets/plugins/jquery/jquery.min.js') }}"></script>

<!-- Bootstrap js-->
<script src="{{ url('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ url('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

<!-- Internal ECharts js -->
<script src="{{ url('assets/plugins/echarts/echarts.js') }}"></script>
<script src="{{ url('assets/js/chart.echarts.js') }}"></script>

<!-- Internal Fileuploads js-->
<script src="{{ url('assets/plugins/fileuploads/js/fileupload.js') }}"></script>
<script src="{{ url('assets/plugins/fileuploads/js/file-upload.js') }}"></script>

<!-- Perfect-scrollbar js -->
<script src="{{ url('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>

<!-- Sidemenu js -->
<script src="{{ url('assets/plugins/sidemenu/sidemenu.js') }}" id="leftmenu"></script>

<!-- Sidebar js -->
<script src="{{ url('assets/plugins/sidebar/sidebar.js') }}"></script>

<!-- Internal Data Table js -->
<script src="{{ url('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/jszip.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
<script src="{{ url('assets/plugins/datatable/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ url('assets/js/table-data.js') }}"></script>

<!-- Form elements js -->
<script src="{{ url('assets/js/advanced-form-elements.js') }}"></script>
<script src="{{ url('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ url('assets/js/select2.js') }}"></script>

<!-- Internal Form-layouts-->
<script src="{{ url('assets/js/form-layouts.js') }}"></script>

<!-- Color Theme js -->
<script src="{{ url('assets/js/themeColors.js') }}"></script>

<!-- Sticky js -->
<script src="{{ url('assets/js/sticky.js') }}"></script>

<!-- Custom js -->
<script src="{{ url('assets/js/custom.js') }}"></script>

<!-- Peity js-->
<script src="{{ url('assets/plugins/peity/jquery.peity.min.js') }}"></script>

<!-- Owl Carousel js-->
<script src="{{ url('assets/plugins/owl-carousel/owl.carousel.js') }}"></script>

<!-- Internal Sweet-Alert js-->
<script src="{{ url('assets/plugins/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ url('assets/plugins/sweet-alert/jquery.sweet-alert.js') }}"></script>

<!-- Internal Polyfills js-->
<script src="{{ url('assets/plugins/polyfill/polyfill.min.js') }}"></script>
<script src="{{ url('assets/plugins/polyfill/classList.min.js') }}"></script>
<script src="{{ url('assets/plugins/polyfill/polyfill_mdn.js') }}"></script>

<!-- Internal Morris js -->
<script src="{{ url('assets/plugins/raphael/raphael.min.js') }}"></script>
<script src="{{ url('assets/plugins/morris.js/morris.min.js') }}"></script>

<!-- Sparkline js-->
<script src="{{ url('assets/plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script>

<!-- PDF Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Dropify for file uploads
        $('.dropify-create').dropify();

        // Reinitialize Dropify for modal forms
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.dropify-edit').dropify();
        });
        
        // Processing buttons with spinners
        const buttons = document.querySelectorAll('.process-button');

        buttons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Check if the button is inside a form
                const form = button.closest('form');
                if (form) {
                    // Validate the form
                    if (!form.checkValidity()) {
                        // Prevent submission and show validation errors
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    // Disable the button and add a spinner
                    button.disabled = true;
                    const originalText = button.innerHTML;
                    button.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Processing...
                    `;

                    // Submit the form
                    form.submit();
                }
            });
        });
        
        // Initialize datepickers for enrollment forms
        $('.enrollment-date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    // Select2 initialization for dropdowns
    $(document).ready(function () {
        // Initialize Select2 after modal is shown
        $('#createModal, #editModal').on('shown.bs.modal', function () {
            $('.select2').select2({
                placeholder: 'Choose one',
                searchInputPlaceholder: 'Search',
                width: '100%',
                dropdownParent: $(this)
            });
        });
        
        // Initialize Select2 for page load
        $('.select2-page').select2({
            placeholder: 'Choose one',
            searchInputPlaceholder: 'Search',
            width: '100%'
        });
        
        // Initialize datatables with export options
        $('.datatable-export').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            responsive: true
        });
    });
</script>

{{-- Display flash messages --}}
@if (session('success'))
    <script>
        swal({
            title: 'Success',
            text: '{{ session('success') }}',
            type: 'success',
            confirmButtonColor: '#57a94f'
        });
    </script>
@endif

@if (session('error'))
    <script>
        swal({
            title: 'Error',
            text: '{{ session('error') }}',
            type: 'error',
            confirmButtonColor: '#dc3545'
        });
    </script>
@endif

<!-- Internal Dashboard js-->
<script src="{{ url('assets/js/crypto-dashboard.js') }}"></script>

<!-- Conditional script inclusions -->
@if (request()->routeIs('students.*'))
    <script src="{{ url('assets/js/student-management.js') }}"></script>
@endif

@if (request()->routeIs('course-registration.*'))
    <script src="{{ url('assets/js/course-registration.js') }}"></script>
@endif

@if (request()->routeIs('examination.*'))
    <script src="{{ url('assets/js/examination-management.js') }}"></script>
@endif
