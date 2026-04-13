<!DOCTYPE html>
    <html class="no-js"
        lang="{{ str_replace('_', '-', app()->getLocale()) }}"
         dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>{{ $title }}</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.svg') }}" />

    <!-- ========================= CSS here ========================= -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" /> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/LineIcons.3.0.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tiny-slider.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/glightbox.min.css') }}" />
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" /> --}}
    @stack('styles')
    @stack('style')

     @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/main-rtl.css') }}" />
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    @endif

    <!--  ============= pushar ===================  -->
{{-- <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script> --}}

   <script>
        window.userID = {{ auth()->id() ?? 'null' }};
    </script>
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

      <!--  ============= End  pushar ===================  -->

</head>

<body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
        <a href="https://browsehappy.com/">upgrade your browser</a> to improve
        your experience and security.
      </p>
    <![endif]-->

    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- /End Preloader -->

    <!-- Start Header Area -->
    @include('layouts.header')
    <!-- End Header Area -->

    <!-- Start Breadcrumbs -->
    {{ $breadcrumb ?? '' }}
    <!-- End Breadcrumbs -->

    {{ $slot }}

    <!-- Start Footer Area -->
    @include('layouts.front-footer')

    <!--/ End Footer Area -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <a href="https://wa.me/201091070473"
        class="whatsapp-float"
        target="_blank"
        aria-label="Chat on WhatsApp">
    <i class="lni lni-whatsapp"></i>
    </a>

    <!-- ========================= JS here ========================= -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/tiny-slider.js') }}"></script>
    <script src="{{ asset('assets/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @vite(['resources/js/app.js'])

    @stack('script')
    @stack('scripts')
</body>

</html>
