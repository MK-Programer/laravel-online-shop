<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel Shop | {{ $tab_name }}</title>
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('admin/assets/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('admin/assets/css/adminlte.min.css') }}">
        <!-- Dropzone style -->
        <link rel="stylesheet" href="{{ asset('admin/assets/plugins/dropzone/min/dropzone.min.css') }}">
        <!-- Custom style -->
        <link rel="stylesheet" href="{{ asset('admin/assets/css/custom.css') }}">
        <!--- Summernote Style --->
        <link rel="stylesheet" href="{{ asset('admin/assets/plugins/summernote/summernote.min.css') }}">
        <!--- Select2 Style --->
        <link rel="stylesheet" href="{{ asset('admin/assets/plugins/select2/css/select2.min.css') }}">

        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <body class="hold-transition sidebar-mini">
        <!-- Site wrapper -->
        <div class="wrapper">
            <!-- Navbar -->
            @include('admin.layouts.navs.topbar')
            <!-- /.navbar -->
            <!-- Main Sidebar Container -->
            @include('admin.layouts.navs.sidebar')
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Global Page Loader -->
                @include('admin.layouts.loading')
                @yield('content')
            </div>
            <!-- /.content-wrapper -->
            @include('admin.layouts.footer')
        </div>
        <!-- ./wrapper -->
        <!-- jQuery -->
        <script src="{{ asset('admin/assets/plugins/jquery/jquery.min.js') }}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('admin/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <!-- AdminLTE App -->
        <script src="{{ asset('admin/assets/js/adminlte.min.js') }}"></script>
        <!-- Dropzone JS-->
        <script src="{{ asset('admin/assets/plugins/dropzone/min/dropzone.min.js') }}"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="{{ asset('admin/assets/js/demo.js') }}"></script>
        <!--- Global helper --->
        <script src="{{ asset('assets/js/global-helper.js') }}"></script>
        <!--- Form helper --->
        <script src="{{ asset('assets/js/form-helper.js') }}"></script>
        <!--- Image helper --->
        <script>
            const tempImageUploadUrl = "{{ route('admin.temp-image-upload') }}";
            const tempImageDeleteUrl = "{{ route('admin.temp-image-delete') }}";
            const defaultMaxFiles = "{{ config('app.default_max_files') }}";
        </script>
        <script src="{{ asset('admin/assets/js/image-helper.js') }}"></script>
        <!--- Summernote JS --->
        <script src="{{ asset('admin/assets/plugins/summernote/summernote.min.js') }}"></script>
        <!--- Custom Alerts --->
        <script src="{{ asset('assets/js/alerts.js') }}"></script>
        <!-- Select2 JS -->
        <script src="{{ asset('admin/assets/plugins/select2/js/select2.min.js') }}"></script>
        <!--- Custom JS --->
        <script type="text/javascript">
            // Adding csrf-token to any ajax request headers
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function() {
                $('.summernote').summernote({
                    height: 250,
                    toolbar: [
                        // Keep only what you need
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['fontsize', 'color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']], // removed 'picture' and 'video'
                        ['view', ['codeview']]
                    ],
                    // Disable drag & drop image upload
                    disableDragAndDrop: true
                });
            });
        </script>
        @yield('js')
    </body>

</html>
