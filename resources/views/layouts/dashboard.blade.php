<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    @include('partials.theme-script')

    <title>@hasSection('title')@yield('title') | @endif{{ Setting::get('site_name') }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/LineIcons.3.0.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    @if (app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.rtlcss.com/bootstrap/v4.5.3/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ asset('dist/css/custom.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    <script>
        window.userID = {{ auth()->id() ?? 'null' }};

        window.addNotification = function (data) {
            let payload = data.notification;
            let count = document.getElementById('notification-count');

            if (count) {
                count.innerText = parseInt(count.innerText || 0) + 1;
                count.classList.remove('d-none');
            }

            let list = document.getElementById('notification-list');

            if (!list) return;

            let li = document.createElement('li');
            let link = document.createElement('a');

            link.className = 'dropdown-item';
            link.href = `/dashboard/orders/${payload.order_id}`;
            link.textContent = payload.message;
            li.appendChild(link);
            list.prepend(li);
        };
    </script>

    @stack('style')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    @include('layouts.dash_navbar')
    @include('layouts.sidebar')

        <div class="content-wrapper">
        @include('partials.maintenance-banner')

        <div class="content-header">
            <div class="container-fluid">
                <div class="dash-page-header">
                    <h1 class="m-0">@yield('title')</h1>
                    <ol class="breadcrumb">
                        @section('breadcrumb')
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">{{ __('app.dashboard') }}</a></li>
                        @show
                    </ol>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="main-footer d-flex flex-wrap justify-content-between">
        <span>{{ __('app.copyright', ['year' => date('Y')]) }}</span>
        <span>{{ __('app.developed_by') }}</span>
    </footer>
</div>

<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
@if (app()->getLocale() == 'ar')
    <script src="https://cdn.rtlcss.com/bootstrap/v4.5.3/js/bootstrap.bundle.min.js"></script>
@else
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@endif
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

@stack('script')
</body>
</html>
