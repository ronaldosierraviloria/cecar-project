@extends('layouts.baseEvaluador')

@section('title', 'Detalles de Evaluación — ' . ($evaluacion->trabajo->titulo ?? ''))

@section('content')
<div class="min-h-screen bg-[#f4f4f4] -m-4 md:-m-6 p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('evaluador.dashboard') }}"
                    class="p-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-[#07321e] hover:bg-gray-100 transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalles de la Evaluación</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $evaluacion->trabajo->titulo }}</p>
                </div>
            </div>
            <a href="{{ route('evaluador.rubrica-pdf', $evaluacion->trabajo->id_trabajo) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white rounded-xl font-bold text-sm hover:bg-rose-700 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar Rúbrica de Evaluación
            </a>
        </div>

        @php
            $trabajo = $evaluacion->trabajo;
            $tipoNombre = $trabajo->tipo->nombre_tipo ?? 'Sin tipo';
            $criterios = $evaluacion->criterios ?? [];

            $resultadoTexto = match($evaluacion->resultado) {
                'aceptada' => 'Aceptada',
                'aceptada_con_mejoras' => 'Aceptada con mejoras',
                'rechazada' => 'Rechazada',
                'puede_sustentar' => 'Puede sustentar',
                'sustentacion_con_correcciones' => 'Sustentación con correcciones',
                'no_sustentar' => 'No sustentar',
                default => ucfirst($evaluacion->resultado)
            };

            $estadoClase = match($evaluacion->resultado) {
                'aceptada', 'puede_sustentar' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'aceptada_con_mejoras', 'sustentacion_con_correcciones' => 'bg-amber-50 text-amber-700 border-amber-200',
                'rechazada', 'no_sustentar' => 'bg-red-50 text-red-700 border-red-200',
                default => 'bg-gray-50 text-gray-600 border-gray-200'
            };
        @endphp

        {{-- Resumen --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Resumen de la Evaluación</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Plantilla</p>
                    <p class="text-sm font-bold text-gray-800">
                        {{ $evaluacion->tipo_plantilla === 'propuesta_de_grado' ? 'Propuesta de Grado' : ($evaluacion->tipo_plantilla === 'pasantia' ? 'Pasantía' : 'Trabajo de Grado') }}
                    </p>
                </div>
                @if ($evaluacion->nota_final !== null)
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nota Final</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($evaluacion->nota_final, 1) }}</p>
                </div>
                @endif
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Resultado</p>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold border {{ $estadoClase }}">
                        {{ $resultadoTexto }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Estado</p>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">
                        Evaluación registrada
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Última actualización</p>
                    <p class="text-sm font-bold text-gray-800">{{ $evaluacion->updated_at ? \Carbon\Carbon::parse($evaluacion->updated_at)->format('d/m/Y H:i') : '' }}</p>
                </div>
            </div>
        </div>

        {{-- Información del Proyecto --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Información del Proyecto</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Título</p>
                    <p class="text-sm font-bold text-gray-800 mt-1">{{ $trabajo->titulo }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipo</p>
                        <p class="text-sm font-semibold text-gray-700 mt-1">{{ $tipoNombre }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estudiantes</p>
                        <p class="text-sm font-semibold text-gray-700 mt-1">
                            @if($trabajo->estudiante)
                                @foreach($trabajo->estudiante as $est)
                                    {{ $est->nombre }} {{ $est->apellido }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @else
                                <span class="text-gray-400 italic">No asignado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Criterios --}}
        @if(count($criterios) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Criterios Evaluados</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($criterios as $crit)
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <p class="text-sm font-semibold text-gray-800 flex-1">
                            <span class="text-[#07321e] font-bold">{{ $crit['id'] ?? $loop->iteration }}.</span>
                            {{ $crit['descripcion'] ?? 'Criterio' }}
                        </p>
                        @if(isset($crit['calificacion']))
                            <span class="text-lg font-black text-gray-900 shrink-0">{{ number_format($crit['calificacion'], 1) }}</span>
                        @elseif(isset($crit['valoracion']))
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold shrink-0 text-gray-800">
                                {{ ucfirst($crit['valoracion'] ?? '') }}
                            </span>
                        @endif
                    </div>
                    @if(!empty($crit['comentario']))
                        <p class="text-xs text-gray-500 mt-2 ml-5 italic">"{{ $crit['comentario'] }}"</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Observaciones Globales --}}
        @if($evaluacion->observaciones_globales)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Observaciones Globales</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $evaluacion->observaciones_globales }}</p>
            </div>
        </div>
        @endif

        {{-- Firmas de los Evaluadores --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Firmas de los Evaluadores</h2>
            </div>
            <div class="p-6 space-y-6">
                {{-- Firma Evaluador 1 --}}
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                        Evaluador 1: {{ $evaluador1 ? $evaluador1->nombre . ' ' . $evaluador1->apellido : '—' }}
                    </p>
                    @if($evaluacion->firma)
                        <img src="{{ $evaluacion->firma }}" alt="Firma Evaluador 1" class="h-20 border border-gray-200 rounded-lg p-2 bg-gray-50">
                    @else
                        <p class="text-xs text-gray-400 italic">Pendiente de firma</p>
                    @endif
                </div>

                {{-- Firma Evaluador 2 --}}
                @if($evaluador2)
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                        Evaluador 2: {{ $evaluador2->nombre }} {{ $evaluador2->apellido }}
                    </p>
                    @if($evaluacion->firma_evaluador_2)
                        <img src="{{ $evaluacion->firma_evaluador_2 }}" alt="Firma Evaluador 2" class="h-20 border border-gray-200 rounded-lg p-2 bg-gray-50">
                    @else
                        <p class="text-xs text-gray-400 italic">Pendiente de firma</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="flex justify-center pb-8">
            <a href="{{ route('evaluador.dashboard') }}"
                class="px-8 py-3 bg-[#c2d500] text-[#07321e] rounded-xl font-bold text-sm hover:bg-[#b6c900] transition-all">
                Volver al Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
