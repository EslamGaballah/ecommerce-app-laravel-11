 <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])


          <!-- pusher Scripts -->
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
        </script>

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> 
