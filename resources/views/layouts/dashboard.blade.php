{{-- صفحة الاب --}}
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
     {{-- <html lang= "ar">  --}}
  <head>
    <meta charset="utf-8">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>{{config('app.name')}}</title>

    <!-- Font Awesome Icons -->
      <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css') }}">


    <!-- Theme style -->
    @if(app()->getLocale() == 'ar')
          <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css') }}">
          <link rel="stylesheet" href="https://cdn.rtlcss.com/bootstrap/v4.5.3/css/bootstrap.min.css">
          <link rel="stylesheet" href="{{ asset('dist/css/custom.css') }}">

      @else
      <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css') }}">
      @endif

  {{-- =============================================================== --}}
    <!-- overlayScrollbars -->
    {{-- <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css"> --}}
  {{-- =============================================================== --}}

    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <script>
      window.userID = {{ auth()->id() ?? 'null' }};
  </script>
  {{-- <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script> --}}
      <script>
    window.addNotification = function(data) {

      let payload = data.notification;

      let count = document.getElementById('notification-count');

      if (count) {
          count.innerText = parseInt(count.innerText || 0) + 1;
      }

      let list = document.getElementById('notification-list');

      if (!list) return;

      let li = document.createElement('li');

      li.innerHTML = `
          <a href="/dashboard/orders/${payload.order_id}">
              🔔 ${payload.message}
          </a>
      `;

      list.prepend(li);
  };
  </script>

    @stack('style')

  </head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
 @include('layouts.dash_navbar')

  {{-- sidebar --}}
@include('layouts.sidebar')

@yield('title')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">@yield('title')</h1>
        </div><!-- /.col -->

        @section('breadcrumb')
          <li class="breadcrumb-item active">{{ __('app.home') }}</li>
        @show
        
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- content body -->
      <div class="content">
        <div class="container-fluid">

          @yield('content')
 
        </div>
      </div>
  <!-- /.content body -->

</div>

   <!-- Main Footer -->
 <footer class="main-footer">
  <!-- To the right -->
  <div class="float-right d-none d-sm-inline">
    Anything you want
  </div>
  <!-- Default to the left -->
  <strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
</footer>
</div>
<!-- ./wrapper -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script>
  // حل تعارض التلميحات (Tooltip) بين jQuery UI و Bootstrap
  $.widget.bridge('uibutton', $.ui.button)
</script>

@if(app()->getLocale() == 'ar')
    <script src="https://cdn.rtlcss.com/bootstrap/v4.5.3/js/bootstrap.bundle.min.js"></script>
@else
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@endif

<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.world.js') }}"></script>
<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
{{-- <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script> --}}

<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<script src="{{ asset('dist/js/demo.js') }}"></script>

{{-- <script src="{{ asset('dist/js/pages/dashboard.js') }}"></script> --}}


















{{-- =============================================================== --}}

@stack('script') 
@stack('scriptس') 
</body>
</body>
</html>