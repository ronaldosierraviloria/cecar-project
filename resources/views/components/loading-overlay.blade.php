{{--
    Componente global de animación de carga.
    Se activa automáticamente al enviar cualquier formulario en la aplicación.
    Uso: <x-loading-overlay /> en el layout base.
--}}
<div 
    id="global-loading-overlay"
    style="display: none;"
    class="fixed inset-0 z-[9999] flex items-center justify-center"
    aria-live="assertive"
    aria-label="Procesando solicitud">

    {{-- Fondo difuminado --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    {{-- Tarjeta de carga --}}
    <div class="relative flex flex-col items-center gap-4 bg-white rounded-2xl shadow-2xl px-10 py-8 border border-gray-100">

        {{-- Logo --}}
        <img src="{{ asset('images/logocecar.webp') }}" alt="Logo CECAR" class="h-12 w-auto object-contain mb-1">

        {{-- Spinner animado --}}
        <div class="relative w-12 h-12">
            <svg class="w-12 h-12 animate-spin" viewBox="0 0 64 64" fill="none" style="animation-duration:0.9s;">
                <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="6"/>
                <path d="M32 4 a28 28 0 0 1 28 28" stroke="#c2d500" stroke-width="6" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- Texto --}}
        <div class="text-center">
            <p class="text-[#07321e] font-bold text-base">Procesando...</p>
            <p class="text-gray-400 text-sm mt-0.5">Por favor, espera un momento</p>
        </div>

        {{-- Barra de progreso indeterminada --}}
        <div class="w-48 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-[#c2d500] rounded-full animate-[loading-bar_1.5s_ease-in-out_infinite]"></div>
        </div>
    </div>
</div>

{{-- Estilos para la barra de progreso --}}
<style>
    @keyframes loading-bar {
        0%   { width: 0%;   margin-left: 0%; }
        50%  { width: 70%;  margin-left: 15%; }
        100% { width: 0%;   margin-left: 100%; }
    }
</style>

{{-- Script global: activa el overlay en CUALQUIER submit de formulario --}}
<script>
    (function () {
        const overlay = document.getElementById('global-loading-overlay');

        function showLoading() {
            if (overlay) overlay.style.display = 'flex';
        }

        // Escuchar submit en todos los formularios (incluso los que se agreguen dinámicamente)
        document.addEventListener('submit', function (e) {
            // No mostrar si el formulario tiene data-no-loading
            if (e.target && e.target.hasAttribute('data-no-loading')) return;
            showLoading();
        }, true);

        // Ocultar si la página vuelve (navegación con botón Atrás del navegador)
        window.addEventListener('pageshow', function (e) {
            if (e.persisted && overlay) overlay.style.display = 'none';
        });
    })();
</script>
