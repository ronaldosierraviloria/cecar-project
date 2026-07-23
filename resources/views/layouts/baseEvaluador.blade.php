<!DOCTYPE html>
<html lang="es" x-data="adminApp()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="session-lifetime" content="{{ config('session.lifetime') }}">

    {{-- ── SEO ── --}}
    <title>@yield('title', 'Panel del Evaluador') — Sistema de Grado CECAR</title>
    <meta name="application-name" content="Sistema de Grado CECAR">
    <meta name="description" content="@yield('meta_description', 'Panel del Evaluador del Sistema de Gestión de Trabajos de Grado de CECAR. Revisión y evaluación de trabajos de grado.')">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#07321e">

    {{-- ── Favicon ── --}}
    <link rel="icon" type="image/png" href="{{ asset('images/icon.webp') }}">

    {{-- ── Font: Preconnect + Preload ── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload"
          as="font"
          type="font/woff2"
          href="https://fonts.gstatic.com/s/poppins/v23/pxiEyp8kv8JHgFVrJJfecg.woff2"
          crossorigin
          fetchpriority="high">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        #app-sidebar {
            transform: translate3d(-100%, 0, 0);
            visibility: hidden;
            backface-visibility: hidden;
        }

        html.sidebar-open #app-sidebar {
            transform: translate3d(0, 0, 0);
            visibility: visible;
        }

        @media (min-width: 768px) {
            html.sidebar-open #app-main {
                margin-left: 16rem;
            }
        }

        body.alpine-ready #app-sidebar {
            transition: transform 300ms ease-in-out;
            will-change: transform;
        }

        #sidebar-backdrop {
            opacity: 0;
            pointer-events: none;
            transition: opacity 180ms ease;
        }

        #sidebar-backdrop.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        @media (max-width: 767px) {
            body.alpine-ready #app-sidebar,
            #sidebar-backdrop {
                transition: none !important;
            }
        }

        body.alpine-ready #app-main {
            transition: margin-left 300ms ease-in-out;
            will-change: margin-left;
        }
        [x-cloak] { display: none !important; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
        .active-link {
            background-color: var(--cecar-lime);
            color: var(--cecar-green) !important;
            font-weight: 600;
        }
        .active-link svg { color: var(--cecar-green); }
        .sidebar-link-inactive svg { color: var(--cecar-lime); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        (function () {
            var initialOpen = false;

            try {
                var stored = localStorage.getItem('sidebarOpen');
                initialOpen = stored === 'true';
            } catch (error) {
                initialOpen = false;
            }

            window.__sidebarInitialOpen = initialOpen;
            document.documentElement.classList.toggle('sidebar-open', initialOpen);
        })();
    </script>
    @stack('styles')
</head>

<body class="bg-gray-100 font-poppins antialiased">

    <div class="flex h-screen">

        {{-- SIDEBAR COMPONENT --}}
        @include('layouts.partials.evaluadorSidebar')

        {{-- BACKDROP MOBILE --}}
        <div id="sidebar-backdrop"
               class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
             onclick="window.__closeSidebar && window.__closeSidebar()">
        </div>

        {{-- MAIN --}}
        <main id="app-main" class="flex-1 overflow-y-auto h-screen pb-20"
              style="margin-left: 0">

            {{-- HEADER COMPONENT --}}
            @include('layouts.partials.evaluadorHeader')

            {{-- CONTENT --}}
            <section class="p-4 md:p-6">
                @yield('content')
            </section>

        </main>

    </div>



    {{-- GLOBAL LOADING OVERLAY --}}
    <x-loading-overlay />

    {{-- ALPINE LOGIC --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminApp', () => ({
                sidebarOpen: window.__sidebarInitialOpen ?? (window.innerWidth >= 768),
                init() {
                    this.$watch('sidebarOpen', value => {
                        localStorage.setItem('sidebarOpen', value);
                        this._applyLayout(value);
                    });
                    this._applyLayout(this.sidebarOpen);
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        document.body.classList.add('alpine-ready');
                    }));

                    const sidebar = document.getElementById('app-sidebar');
                    if (sidebar) {
                        sidebar.addEventListener('click', event => {
                            const link = event.target.closest('a[href]');
                            if (!link) return;

                            if (window.innerWidth >= 768) return;

                            event.preventDefault();
                            event.stopPropagation();

                            localStorage.setItem('sidebarOpen', 'false');
                            document.documentElement.classList.remove('sidebar-open');
                            this.sidebarOpen = false;

                            if (link.href !== window.location.href) {
                                window.location.href = link.href;
                            }
                        });
                    }

                    window.__toggleSidebar = () => { this.sidebarOpen = !this.sidebarOpen; };
                    window.__closeSidebar = () => { this.sidebarOpen = false; };
                },
                _applyLayout(open) {
                    const sidebar  = document.getElementById('app-sidebar');
                    const main     = document.getElementById('app-main');
                    const backdrop = document.getElementById('sidebar-backdrop');
                    const isDesktop = window.innerWidth >= 768;
                    if (sidebar)  sidebar.style.transform  = open ? 'translate3d(0, 0, 0)' : 'translate3d(-100%, 0, 0)';
                    if (sidebar)  sidebar.style.visibility = open ? 'visible' : 'hidden';
                    if (main)     main.style.marginLeft    = (open && isDesktop) ? '16rem' : '0';
                    if (backdrop) backdrop.classList.toggle('is-visible', open && !isDesktop);
                }
            }));
        });
        window.addEventListener('resize', () => {
            const alpine = document.querySelector('[x-data="adminApp()"]')?._x_dataStack?.[0];
            if (alpine) alpine._applyLayout(alpine.sidebarOpen);
        });
    </script>
    @stack('scripts')

    {{-- ── Speculation Rules: prefetch same-origin navigation links on hover ── --}}
    <script type="speculationrules">
    {
        "prefetch": [{
            "where": {
                "and": [
                    { "href_matches": "/*" },
                    { "not": { "href_matches": "/logout" } },
                    { "not": { "selector_matches": "[rel~=nofollow]" } },
                    { "not": { "selector_matches": ".no-prefetch" } }
                ]
            },
            "eagerness": "moderate"
        }]
    }
    </script>
</body>

</html>