<aside id="app-sidebar"
    class="fixed top-0 left-0 z-50 h-screen sidebar-gradient text-white flex flex-col w-64 border-r border-white/5 shadow-2xl"
    aria-label="Sidebar">

    {{-- BRANDING --}}
    <div class="flex justify-center items-center h-20 overflow-hidden border-b border-white/5 shrink-0 p-6">
        <a href="{{ url('/') }}" class="flex items-center justify-center">
            <img src="{{ asset('images/logocecar-bw.png') }}" alt="Logo CECAR" class="h-10 w-auto filter drop-shadow">
        </a>
    </div>

    {{-- NAVIGATION --}}
    <div class="flex-1 overflow-y-auto px-3 py-4 no-scrollbar">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('gestor.dashboard') }}"
                    class="sidebar-item {{ request()->routeIs('gestor.dashboard') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6m-6 0h-2a1 1 0 00-1 1v1m4 0v-1m-4 0v-1"></path>
                    </svg>
                    <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="{{ route('gestor.crear') }}"
                    class="sidebar-item {{ request()->routeIs('gestor.crear') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Añadir Proyecto</span>
                </a>
            </li>
            <li>
                <a href="{{ route('gestor.listaEvaluadores') }}"
                    class="sidebar-item {{ request()->routeIs('gestor.listaEvaluadores') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2M17 9V7a2 2 0 00-2-2H9a2 2 0 00-2 2v2m6 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Lista de Evaluadores</span>
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