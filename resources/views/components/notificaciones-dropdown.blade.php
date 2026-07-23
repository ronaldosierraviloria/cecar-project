<div x-data="notificacionesDropdown" class="relative">

    {{-- ── Botón Campana ── --}}
    <button @click="toggle"
            :class="campanaSacudida ? 'campana-shake' : ''"
            class="relative p-2 text-gray-500 hover:text-[var(--cecar-green)] transition-colors rounded-lg hover:bg-gray-100">

        <svg class="h-6 w-6 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        {{-- Badge de cantidad --}}
        <template x-if="noLeidas > 0">
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white rounded-full badge-pulse"
                  style="background-color: #ef4444;"
                  x-text="noLeidas > 99 ? '99+' : noLeidas">
            </span>
        </template>
    </button>

    {{-- ── Panel Dropdown ── --}}
    <div x-show="abierto"
         x-cloak
         @click.away="abierto = false"
         @keydown.escape.window="abierto = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden">

        {{-- Header --}}
        <div class="px-4 py-3.5 border-b border-gray-100 bg-gray-50/60">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[var(--cecar-green)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Notificaciones</h3>
                    <template x-if="noLeidas > 0">
                        <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold text-white rounded-full"
                              style="background-color: var(--cecar-green);"
                              x-text="noLeidas">
                        </span>
                    </template>
                </div>
                <button @click="marcarTodasLeidas"
                        x-show="noLeidas > 0"
                        class="text-xs font-medium text-[var(--cecar-green)] hover:underline transition-colors">
                    Marcar todas como leídas
                </button>
            </div>
        </div>

        {{-- Lista de notificaciones --}}
        <div class="max-h-[420px] overflow-y-auto divide-y divide-gray-50">
            <template x-for="notif in notificaciones" :key="notif.id">
                <div @click="marcarLeida(notif)"
                     :class="notif.read_at ? 'bg-white opacity-70' : 'bg-blue-50/30'"
                     class="flex items-start gap-3 px-4 py-3.5 cursor-pointer hover:bg-gray-50 transition-all duration-150 group">

                    {{-- Icono con color por tipo --}}
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm mt-0.5"
                         :class="colorPara(notif.data?.tipo || 'info').bg">
                        <svg class="w-4 h-4"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             :class="colorPara(notif.data?.tipo || 'info').icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  :d="iconoPara(notif.data?.tipo || 'info')"/>
                        </svg>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 leading-tight"
                           :class="notif.read_at ? 'font-medium text-gray-600' : 'font-semibold text-gray-900'"
                           x-text="notif.data?.titulo || notif.data?.title || 'Notificación'">
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2 leading-relaxed"
                           x-text="notif.data?.mensaje || notif.data?.message || ''">
                        </p>
                        <p class="text-[10px] text-gray-400 mt-1.5 font-medium"
                           x-text="formatearFecha(notif.created_at)">
                        </p>
                    </div>

                    {{-- Punto de no leída --}}
                    <div class="flex-shrink-0 flex flex-col items-center gap-2 mt-1">
                        <template x-if="!notif.read_at">
                            <span class="w-2 h-2 rounded-full flex-shrink-0"
                                  style="background-color: var(--cecar-green);">
                            </span>
                        </template>
                        {{-- Flecha indicadora al hover --}}
                        <svg class="w-3.5 h-3.5 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </template>

            {{-- Estado vacío --}}
            <template x-if="notificaciones.length === 0">
                <div class="px-4 py-12 text-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 bg-gray-100">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Sin notificaciones</p>
                    <p class="text-xs text-gray-400 mt-1">Aquí aparecerán tus alertas</p>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/60" x-show="notificaciones.length > 0">
            <button @click="borrarTodas"
                    class="w-full text-center text-xs font-medium text-red-400 hover:text-red-600 hover:underline transition-colors py-0.5">
                Borrar todas las notificaciones
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes campana-shake {
        0%, 100% { transform: rotate(0deg); }
        15%       { transform: rotate(14deg); }
        30%       { transform: rotate(-10deg); }
        45%       { transform: rotate(8deg); }
        60%       { transform: rotate(-5deg); }
        75%       { transform: rotate(3deg); }
    }

    .campana-shake svg {
        animation: campana-shake 0.8s ease-in-out;
        transform-origin: 50% 0%;
    }

    @keyframes badge-pulse-anim {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
        50%       { box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
    }

    .badge-pulse {
        animation: badge-pulse-anim 2s ease-in-out infinite;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
