<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Page Title' }}</title>
                <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
@auth
    <meta name="user-id" content="{{ auth()->id() }}">
@endauth
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

    </head>
    <body>
        <div>
            <livewire:layout.navigation />
            {{ $slot }}
        </div>
        @livewireScripts
        <script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>
        <script>
            console.log('dsd');
            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                .notification((notification) => {
                    Livewire.emit('refreshNotifications');
                });
        </script>
    </body>
</html>
