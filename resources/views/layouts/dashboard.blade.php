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

 
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('833b9593418dfdb26f5a', {
          cluster: 'eu',
          authEndpoint: "/broadcasting/auth",
          auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }
        });

        var userId = {{ auth()->id() }};

        var channel = pusher.subscribe(
            'private-App.Models.User.' + userId
        );

        channel.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated',
         function(data) {
            // alert(JSON.stringify(data));
            addNotification(data);
            
        });

        function addNotification(data) {

        let payload = data.notification;

        let count = document.getElementById('notification-count');

        count.innerText = parseInt(count.innerText) + 1;

        let li = document.createElement('li');

        li.innerHTML = `
            <a href="/dashboard/orders/${payload.order_id}">
                🔔 ${payload.message}
            </a>
        `;

        document.getElementById('notification-list').prepend(li);
    }
            // console.log('بيانات التنبيه:', data);
        // الوصول للبيانات التي أرسلتها في toBroadcast
        // console.log('رقم الطلب:', data.order_id);

  </script>

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

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
{{-- <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script> --}}
<script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
{{-- <script src="dist/js/adminlte.min.js"></script> --}}
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>


{{-- =============================================================== --}}

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
{{-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script> --}}
<!-- Bootstrap 4 rtl -->
<script src="https://cdn.rtlcss.com/bootstrap/v4.2.1/js/bootstrap.min.js"></script>
<!-- Bootstrap 4 -->
{{-- <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script> --}}
<!-- ChartJS -->
{{-- <script src="plugins/chart.js/Chart.min.js"></script> --}}
<!-- Sparkline -->
{{-- <script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.world.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script> --}}


















{{-- =============================================================== --}}

@stack('script') 
</body>
</body>
</html>