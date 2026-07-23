@extends('layouts.baseEvaluador')

@section('title', 'Trabajos Calificados - Panel Evaluador')
@section('meta_description', 'Historial de trabajos de grado calificados por este evaluador en CECAR.')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-800">Trabajos Calificados</h1>
        <p class="text-sm text-gray-500 mt-1">Trabajos donde ambos evaluadores han finalizado la evaluación.</p>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @forelse($trabajosCalificados as $trabajo)
            @php
                $tipo_nombre = $trabajo->tipo->nombre_tipo ?? 'Sin tipo';
                $tagClass = match($tipo_nombre) {
                    'Investigación', 'Trabajo De Grado' => 'tag-trabajo',
                    'Emprendimiento' => 'tag-emprendimiento',
                    'Pasantía' => 'tag-pasantia',
                    default => 'tag-default'
                };
                $evaluacion = $trabajo->evaluaciones->first();
                $esPasantia = $tipo_nombre === 'Pasantía';
                $resTexto = match($evaluacion->resultado ?? '') {
                    'aceptada', 'puede_sustentar' => 'Aprobado',
                    'aceptada_con_mejoras', 'sustentacion_con_correcciones' => 'Con observaciones',
                    'rechazada', 'no_sustentar' => 'Rechazado',
                    default => 'Finalizado'
                };
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
                <div class="px-4 pt-4 pb-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tight {{ $tagClass }}">{{ $tipo_nombre }}</span>
                        @if($trabajo->plantilla_rubrica === 'propuesta_de_grado')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Esperando informe Final
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Calificado
                            </span>
                        @endif
                        @if($evaluacion && $evaluacion->nota_final !== null)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#c2d500]/15 text-[#07321e]">{{ number_format($evaluacion->nota_final, 1) }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $resTexto === 'Aprobado' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : ($resTexto === 'Rechazado' ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">{{ $resTexto }}</span>
                        @endif
                    </div>
                </div>
                <div class="px-4 pb-3">
                    <h3 class="text-[13px] font-bold text-gray-900 leading-snug group-hover:text-[#07321e] transition-colors line-clamp-1" title="{{ $trabajo->titulo }}">{{ $trabajo->titulo }}</h3>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @forelse($trabajo->estudiante as $est)
                        <span class="inline-flex items-center gap-1.5 bg-gray-50 border border-gray-100 rounded-md px-2 py-1 text-xs font-medium text-gray-700">
                            <span class="w-4 h-4 rounded-full bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-600 shrink-0">{{ substr($est->nombre, 0, 1) }}{{ substr($est->apellido, 0, 1) }}</span>
                            {{ $est->nombre }} {{ $est->apellido }}
                        </span>
                        @empty
                        <span class="text-[10px] text-gray-400 italic">Sin estudiantes</span>
                        @endforelse
                    </div>
                </div>
                <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/80 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Evaluado: {{ \Carbon\Carbon::parse($trabajo->pivot->fecha_asignacion)->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('trabajo.archivo', $trabajo->id_trabajo) }}" target="_blank"
                            class="inline-flex items-center gap-1 px-2 py-1.5 text-[10px] font-bold text-white bg-[#07321e] rounded-md hover:bg-[#1a4d2e] transition-all" title="Descargar PDF">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </a>
                        <a href="{{ route('evaluador.detalles-evaluacion', $trabajo->id_trabajo) }}"
                            class="inline-flex items-center gap-1 px-2 py-1.5 text-[10px] font-bold text-[#07321e] bg-[#c2d500] rounded-md hover:bg-[#b6c900] transition-all">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detalles
                        </a>
                    </div>
                </div>
            </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-6 py-16 text-center flex flex-col items-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Sin trabajos calificados</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-sm">Los trabajos que califiques completamente aparecerán aquí.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
