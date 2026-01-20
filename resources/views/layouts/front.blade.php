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

    

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <!--  ============= pushar ===================  -->
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
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/tiny-slider.js') }}"></script>
    <script src="{{ asset('assets/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('script')
    
    @stack('scripts')
    
</body>

</html>