{{-- صفحة الاب --}}
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="ar">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>{{config('app.name')}}</title>

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  {{-- <link rel="stylesheet" href="css/adminlte.css"> --}}
  {{-- <link rel="stylesheet" href="{{asset('/adminlte.css')}}"> --}}
  <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
 @include('layouts.navbar')

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
        <li class="breadcrumb-item active">Home</li>
        @show
        
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

      <div class="content">
        <div class="container-fluid">

          @yield('content')
 
        </div>
      </div>
</div>



  {{-- footer --}}
  @include('layouts.dash-footer')