<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="session-lifetime" content="{{ config('session.lifetime') }}">
    <title>@yield('title', 'Evaluación')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100 font-poppins antialiased overflow-hidden">

    <div class="w-full h-screen">
        @yield('content')
    </div>

    {{-- GLOBAL LOADING OVERLAY --}}
    <x-loading-overlay />

    @stack('scripts')
</body>

</html>
