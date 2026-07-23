<aside id="app-sidebar"
    class="fixed top-0 left-0 z-50 h-screen sidebar-gradient text-white flex flex-col w-64 border-r border-white/5 shadow-2xl"
    aria-label="Sidebar">

    {{-- BRANDING --}}
    <div class="flex justify-center items-center h-20 overflow-hidden border-b border-white/5 shrink-0 p-6">
        <a href="{{ url('/') }}" class="flex items-center justify-center">
            <img src="{{ asset('images/logocecar-bw.webp') }}" alt="Logo CECAR" class="h-10 w-auto filter drop-shadow">
        </a>
    </div>

    {{-- NAVIGATION --}}
    <div class="flex-1 overflow-y-auto px-3 py-4 no-scrollbar">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6m-6 0h-2a1 1 0 00-1 1v1m4 0v-1m-4 0v-1"></path>
                    </svg>
                    <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.trabajos') }}"
                    class="sidebar-item {{ request()->routeIs('admin.trabajos') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>Trabajos de Grado</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.listaEstudiantes') }}"
                    class="sidebar-item {{ request()->routeIs('admin.listaEstudiantes') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Estudiantes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.usuarios.index') }}"
                    class="sidebar-item {{ request()->routeIs('admin.usuarios.index') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Usuarios</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.listaAreas') }}"
                    class="sidebar-item {{ request()->routeIs('admin.listaAreas') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span>Facultades y Áreas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.listaTipoTrabajo') }}"
                    class="sidebar-item {{ request()->routeIs('admin.listaTipoTrabajo') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span>Tipos de Trabajo</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- CERRAR SESIÓN --}}
    <div class="p-3 mt-auto border-t border-white/5 bg-black/10">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-3 p-2 rounded-xl transition duration-300 text-white/60 hover:text-rose-300 hover:bg-white/[0.08]">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="text-xs font-semibold">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>