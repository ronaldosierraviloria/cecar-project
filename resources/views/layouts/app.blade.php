<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' }"
    x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGGT - @yield('title', 'Panel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Phosphor Icons CDN --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/icon.webp') }}">
</head>

<body class="bg-base text-textPrimary min-h-screen flex">

    {{-- SIDEBAR --}}
    @include('layouts.partials.sidebar')

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col min-h-screen">

        {{-- HEADER --}}
        @include('layouts.partials.header')

        {{-- PAGE CONTENT --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>

</body>

</html>