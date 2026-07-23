@extends('layouts.baseEvaluadorFullscreen')

@section('title', 'Revisar Trabajo — ' . ($trabajo->titulo ?? ''))

@push('styles')
<style>
    #revisar-layout {
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #panel-pdf {
        flex: 1;
        min-height: 0;
    }
    #panel-pdf iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    #panel-info {
        flex-shrink: 0;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div id="revisar-layout" x-data="revisarApp()" class="bg-gray-100">

    {{-- Barra superior --}}
    <div class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200 shadow-sm z-10">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('evaluador.dashboard') }}"
                class="p-2 bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-sm font-bold text-gray-900 truncate">{{ $trabajo->titulo }}</h1>
                <p class="text-[11px] text-gray-500">
                    {{ $trabajo->tipo->nombre_tipo ?? 'Sin tipo' }} ·
                    Límite: {{ $fechaLimite ? \Carbon\Carbon::parse($fechaLimite)->format('d/m/Y') : 'Sin fecha' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('trabajo.archivo', $trabajo->id_trabajo) }}?download" target="_blank"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar PDF
            </a>
        </div>
    </div>

    {{-- Cuerpo: PDF a la izquierda, info + acciones a la derecha --}}
    <div class="flex flex-1 min-h-0">

        {{-- Visor PDF --}}
        <div id="panel-pdf" class="bg-gray-500">
            <iframe src="{{ route('trabajo.archivo', $trabajo->id_trabajo) }}" title="PDF del trabajo"></iframe>
        </div>

        {{-- Panel lateral: info y acciones --}}
        <div id="panel-info" class="w-80 bg-white border-l border-gray-200 flex flex-col">

            {{-- Info del proyecto --}}
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Información del Proyecto</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estudiantes</p>
                        <div class="mt-1 space-y-1">
                            @forelse($trabajo->estudiante as $est)
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600 shrink-0">
                                        {{ substr($est->nombre, 0, 1) }}{{ substr($est->apellido, 0, 1) }}
                                    </span>
                                    <span class="text-sm font-bold text-gray-800">{{ $est->nombre }} {{ $est->apellido }}</span>
                                </div>
                            @empty
                                <span class="text-xs text-gray-400 italic">Sin estudiantes</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Director(es)</p>
                        <p class="text-sm font-bold text-gray-800 mt-1">
                            @if($trabajo->directores)
                                @forelse($trabajo->directores as $dir)
                                    {{ $dir->nombre }} {{ $dir->apellido }}@if(!$loop->last), @endif
                                @empty
                                    <span class="text-gray-400 italic font-normal">No asignado</span>
                                @endforelse
                            @else
                                <span class="text-gray-400 italic font-normal">No asignado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Decision --}}
            <div class="flex-1 flex flex-col justify-end p-5">
                @if($miDecision === 'aceptado')
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl mb-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-bold text-emerald-700">Ya aceptaste este trabajo</span>
                        </div>
                        <a href="{{ route('evaluador.dashboard') }}"
                            class="block text-xs text-gray-500 hover:text-gray-700 underline">Volver al dashboard</a>
                    </div>
                @elseif($miDecision === 'rechazado')
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-bold text-red-700">Ya rechazaste este trabajo</span>
                        </div>
                        <a href="{{ route('evaluador.dashboard') }}"
                            class="block text-xs text-gray-500 hover:text-gray-700 underline">Volver al dashboard</a>
                    </div>
                @else
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3 text-center">Decisión</p>
                    <p class="text-[11px] text-gray-500 text-center mb-4">Revisa el documento PDF y decide si aceptas o rechazas este trabajo para evaluación.</p>

                    <div class="space-y-2">
                        <button @click="aceptarTrabajo()"
                            :disabled="procesando"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:bg-emerald-700 transition-all disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Aceptar Trabajo
                        </button>

                        <button @click="showRechazarModal = true"
                            :disabled="procesando"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white text-red-600 border-2 border-red-200 rounded-xl font-bold text-sm hover:bg-red-50 transition-all disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rechazar Trabajo
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal: Rechazar con motivo --}}
    <div x-show="showRechazarModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showRechazarModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Rechazar Trabajo</h3>
            <p class="text-sm text-gray-500 mb-4">Indica el motivo del rechazo. El gestor será notificado.</p>

            <textarea x-model="motivoRechazo" rows="4" maxlength="500"
                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none resize-none"
                placeholder="Ejemplo: El documento no cumple con los requisitos mínimos..."></textarea>
            <p class="text-[10px] text-gray-400 mt-1 text-right" x-text="motivoRechazo.length + '/500'"></p>

            <div class="flex gap-3 mt-4">
                <button @click="showRechazarModal = false"
                    class="flex-1 px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                    Cancelar
                </button>
                <button @click="confirmarRechazo()"
                    :disabled="!motivoRechazo.trim() || procesando"
                    class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-all disabled:opacity-50">
                    Confirmar Rechazo
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-cloak x-transition
        class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-bold"
        :class="toast.error ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'">
        <span x-text="toast.message"></span>
    </div>
</div>

@push('scripts')
<script>
function revisarApp() {
    return {
        showRechazarModal: false,
        motivoRechazo: '',
        procesando: false,
        toast: { show: false, message: '', error: false },

        showToast(message, error = false) {
            this.toast = { show: true, message, error };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        async aceptarTrabajo() {
            this.procesando = true;
            try {
                const res = await fetch('{{ route("evaluador.aceptar-trabajo", $trabajo->id_trabajo) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('Trabajo aceptado correctamente.');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.showToast(data.message || 'Error al aceptar.', true);
                }
            } catch (e) {
                this.showToast('Error de conexión.', true);
            } finally {
                this.procesando = false;
            }
        },

        async confirmarRechazo() {
            if (!this.motivoRechazo.trim()) return;
            this.procesando = true;
            try {
                const res = await fetch('{{ route("evaluador.rechazar-trabajo", $trabajo->id_trabajo) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ motivo: this.motivoRechazo.trim() })
                });
                const data = await res.json();
                if (data.success) {
                    this.showRechazarModal = false;
                    this.showToast('Trabajo rechazado. El gestor ha sido notificado.');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.showToast(data.message || 'Error al rechazar.', true);
                }
            } catch (e) {
                this.showToast('Error de conexión.', true);
            } finally {
                this.procesando = false;
            }
        }
    };
}
</script>
@endpush
@endsection
