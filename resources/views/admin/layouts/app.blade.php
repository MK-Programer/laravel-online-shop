<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Laravel Shop | {{ $tab_name }}</title>
		<!-- Google Font: Source Sans Pro -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
		<!-- Font Awesome -->
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
		<!-- Theme style -->
		<link rel="stylesheet" href="{{ asset('admin-assets/css/adminlte.min.css') }}">
		<link rel="stylesheet" href="{{ asset('admin-assets/css/custom.css') }}">
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
				<div id="page_loader"
					style="position:fixed;top:0;left:0;width:100%;height:100%;
							background:transparent;z-index:9999;
							display:flex;justify-content:center;align-items:center;">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div>
                @yield('content')
			</div>
			<!-- /.content-wrapper -->
			@include('admin.layouts.footer')
		</div>
		<!-- ./wrapper -->
		<!-- jQuery -->
		<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>
		<!-- Bootstrap 4 -->
		<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
		<!-- AdminLTE App -->
		<script src="{{ asset('admin-assets/js/adminlte.min.js') }}"></script>
		<!-- AdminLTE for demo purposes -->
		<script src="{{ asset('admin-assets/js/demo.js') }}"></script>
		<!--- Global helper --->
		<script src="{{ asset('admin-assets/js/global-helper.js') }}"></script>
		<!--- Form helper --->
		<script src="{{ asset('admin-assets/js/form-helper.js') }}"></script>
       <!--- Custom Alerts --->
	   <script src="{{ asset('admin-assets/js/alerts.js') }}"></script>
		<!-- Adding csrf-token to any ajax request headers -->
		<script type="text/javascript">
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
		</script>
		@yield('js')
    </body>
</html>