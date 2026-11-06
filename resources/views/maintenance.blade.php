<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('app.maintenance_title') }} | {{ Setting::get('site_name') }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/pages.css') }}">
</head>
<body class="maintenance-page">
    <main class="maintenance-card">
        @if (Setting::get('logo'))
            <img src="{{ asset('storage/'.Setting::get('logo')) }}" alt="{{ Setting::get('site_name') }}" class="maintenance-logo">
        @else
            <p class="maintenance-site">{{ Setting::get('site_name') }}</p>
        @endif

        <h1>{{ __('app.maintenance_title') }}</h1>
        <p>{{ Setting::get('maintenance_message') ?: __('app.maintenance_default_message') }}</p>

        @if (Setting::get('phone'))
            <p class="maintenance-contact">
                {{ __('app.phone') }}:
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', Setting::get('phone')) }}" dir="ltr">{{ Setting::get('phone') }}</a>
            </p>
        @endif

        <a href="{{ route('login') }}" class="maintenance-login">{{ __('app.staff_login') }}</a>
    </main>
</body>
</html>
