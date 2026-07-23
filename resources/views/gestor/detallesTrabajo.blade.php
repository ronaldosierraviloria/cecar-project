@extends('layouts.baseGestor')

@section('title', 'Detalles del Proyecto | Panel Gestor')
@section('meta_description', 'Vista detallada de un trabajo de grado: estudiante, estado, evaluadores asignados, rubrica e informes.')

@section('content')
@php
$hasStatus = session()->has('success') || session()->has('error');
$tipo_nombre = optional($trabajo->tipo)->nombre_tipo ?? 'Sin tipo';
$esPropuesta = $trabajo->plantilla_rubrica === 'propuesta_de_grado';
$todosEvalFinalizados = $trabajo->evaluadores->isNotEmpty() && $trabajo->evaluadores->every(fn($e) => $e->pivot->estado_revision === 'Finalizado');
$algunoFinalizado = $trabajo->evaluadores->contains(fn($e) => $e->pivot->estado_revision === 'Finalizado');
$puedeSubirInformeFinal = $todosEvalFinalizados && $esPropuesta;
@endphp
<div class="min-h-screen bg-[#f4f4f4] -m-4 md:-m-6 p-4 md:p-8">
    <div x-data="{ showModal: @json($hasStatus), selectedFile: null, submitting: false }" class="max-w-7xl mx-auto">

        <!-- Encabezado -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('gestor.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-[#07321e] hover:bg-gray-100 hover:border-indigo-100 transition-all shadow-sm"
                    data-tooltip-target="tooltip-back" data-tooltip-placement="right">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div id="tooltip-back" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                    Volver al dashboard
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Detalles del Proyecto</h1>
                    <p class="text-sm text-gray-500 mt-1">Información general y gestión del documento.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form action="{{ route('gestor.trabajo.retirar', $trabajo->id_trabajo) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm
                        {{ $trabajo->retirado
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'
                            : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }}">
                        @if($trabajo->retirado)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reactivar
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Retirar
                        @endif
                    </button>
                </form>
                <a href="{{ route('trabajo.archivo', $trabajo->id_trabajo) }}?download=1"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#07321e] text-white rounded-xl font-bold text-sm hover:bg-[#07321e]/80 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Descargar PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Principal -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Información General -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-start justify-between gap-4 mb-6">
                            <div class="space-y-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tight
                                    {{ match($tipo_nombre) {
                                        'Investigación', 'Trabajo De Grado' => 'tag-trabajo',
                                        'Emprendimiento' => 'tag-emprendimiento',
                                        'Pasantía' => 'tag-pasantia',
                                        default => 'tag-default'
                                    } }}">
                                    {{ $tipo_nombre }}
                                </span>
                                <h2 class="text-2xl font-bold text-gray-900 leading-tight">{{ $trabajo->titulo }}</h2>

                                @if($puedeSubirInformeFinal)
                                <div class="mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3">
                                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Acción Requerida: Propuesta Calificada</h4>
                                        <p class="text-[11px] text-emerald-700 mt-1 leading-relaxed">Todos los evaluadores han finalizado la evaluación de esta propuesta. <strong>Es necesario subir el documento final</strong> para convertirla en Trabajo de Grado.</p>
                                        <a href="{{ route('gestor.trabajo.informe-final', $trabajo->id_trabajo) }}" class="inline-flex items-center gap-1.5 mt-2.5 px-3 py-1.5 text-[11px] font-bold text-white bg-[#07321e] rounded-lg hover:bg-[#1a4d2e] transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Subir Informe Final
                                        </a>
                                    </div>
                                </div>
                                @elseif($trabajo->estado === 'retroalimentacion_emitida')
                                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wide">Acción Requerida: Retroalimentación Emitida</h4>
                                        <p class="text-[11px] text-amber-700 mt-1 leading-relaxed">Los jurados evaluadores han finalizado sus observaciones. <strong>Es necesario subir una nueva versión del documento corregido</strong> cuando los estudiantes envíen sus correcciones.</p>
                                    </div>
                                </div>
                                @endif

                                <div class="mt-3 flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estado actual:</span>
                                    @php
                                    $estadoLabel = match(true) {
                                    $todosEvalFinalizados => 'Calificada',
                                    $algunoFinalizado => 'Esperando finalización',
                                    $trabajo->estado === 'subido' => 'Subido / Inicial',
                                    $trabajo->estado === 'en_revision' => 'En revisión',
                                    $trabajo->estado === 'retroalimentacion_emitida' => 'Retroalimentación emitida',
                                    $trabajo->estado === 'version_corregida_subida' => 'Versión corregida subida',
                                    $trabajo->estado === 'aprobado' => 'Aprobado',
                                    default => ucfirst(str_replace('_', ' ', $trabajo->estado ?? 'subido'))
                                    };
                                    $estadoColor = match(true) {
                                    $todosEvalFinalizados => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    $algunoFinalizado => 'bg-amber-50 text-amber-700 border-amber-200',
                                    $trabajo->estado === 'subido' || $trabajo->estado === 'aprobado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    $trabajo->estado === 'en_revision' || $trabajo->estado === 'version_corregida_subida' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200'
                                    };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold uppercase tracking-wider {{ $estadoColor }}">
                                        {{ $estadoLabel }}
                                    </span>
                                    @if($trabajo->retirado)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg border text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 border-gray-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Retirado
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg border text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600 border-emerald-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Activo
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-8 pt-8 border-t border-gray-100">
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Fecha de Registro</span>
                                <span class="text-sm font-bold text-gray-700">{{ \Carbon\Carbon::parse($trabajo->fecha_subida)->format('d \d\e F, Y') }}</span>
                                @php
                                $dias = (int) \Carbon\Carbon::parse($trabajo->fecha_subida)->diffInDays();
                                @endphp
                                <div class="flex items-center gap-1.5 mt-1 text-emerald-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-[10px] font-bold uppercase tracking-tight">
                                        Hace {{ $dias }} {{ $dias == 1 ? 'día' : 'días' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Archivo Actual</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                    <span class="text-sm font-bold text-gray-700 truncate">{{ basename($trabajo->archivo_pdf) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estudiantes -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-8 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Estudiantes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="px-8 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nombre Completo</th>
                                    <th class="px-8 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Correo</th>
                                    <th class="px-8 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Área</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($trabajo->estudiante as $est)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-green font-bold text-xs">
                                                {{ substr($est->nombre, 0, 1) }}{{ substr($est->apellido, 0, 1) }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-700">{{ $est->nombre }} {{ $est->apellido }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $est->correo ?? '—' }}
                                    </td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ optional($est->area)->nombre_area ?? '—' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-10 text-center text-sm text-gray-400 italic">No hay estudiantes registrados en este proyecto.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Director y Subdirector -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-8 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Director y Subdirector</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="px-8 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Rol</th>
                                    <th class="px-8 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nombre Completo</th>
                                    <th class="px-8 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($trabajo->directores as $dir)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase {{ $dir->pivot->rol === 'director' ? 'bg-[#c2d500]/20 text-[#07321e]' : 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($dir->pivot->rol ?? 'Director') }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-gray-700">{{ $dir->nombre }} {{ $dir->apellido }}</span>
                                    </td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $dir->correo_electronico }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-sm text-gray-400 italic">No hay director o subdirector asignado a este proyecto.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral -->
            <div class="space-y-8">

                @if($todosEvalFinalizados)
                {{-- ✅ TARJETA DE RESULTADO DE EVALUACIÓN --}}
                @php
                $primeraEval = $trabajo->evaluaciones->first();
                $headerResultadoClases = match($primeraEval->resultado ?? '') {
                    'puede_sustentar', 'aceptada' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
                    'sustentacion_con_correcciones', 'aceptada_con_mejoras' => 'bg-amber-50 text-amber-700 border-amber-300',
                    'no_sustentar', 'rechazada' => 'bg-rose-50 text-rose-700 border-rose-300',
                    default => 'bg-gray-50 text-gray-700 border-gray-200'
                };
                $evalResultadoIcono = fn($r) => match($r) {
                    'puede_sustentar', 'aceptada' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'sustentacion_con_correcciones', 'aceptada_con_mejoras' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'no_sustentar', 'rechazada' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                };
                $evalResultadoTexto = fn($r) => match($r) {
                    'puede_sustentar' => 'Puede Sustentar',
                    'no_sustentar' => 'No Sustentar',
                    'sustentacion_con_correcciones' => 'Sustentación con Correcciones',
                    'aceptada' => 'Aceptada',
                    'aceptada_con_mejoras' => 'Aceptada con Mejoras',
                    'rechazada' => 'Rechazada',
                    default => $r ? ucfirst(str_replace('_', ' ', $r)) : 'Sin resultado'
                };
                $evalColorBadge = fn($r) => match($r) {
                    'puede_sustentar', 'aceptada' => 'bg-emerald-100 text-emerald-800',
                    'sustentacion_con_correcciones', 'aceptada_con_mejoras' => 'bg-amber-100 text-amber-800',
                    'no_sustentar', 'rechazada' => 'bg-rose-100 text-rose-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border-2 {{ $headerResultadoClases }} overflow-hidden">
                    <div class="px-6 py-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $evalResultadoIcono($primeraEval->resultado ?? '') }}" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-gray-900">Evaluación Completada</h3>
                            <p class="text-xs text-gray-700 mt-0.5">Todos los evaluadores han finalizado la revisión.</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Resultados de cada evaluador --}}
                        @forelse($trabajo->evaluaciones as $eval)
                        @php
                        $evalNota = $eval->nota_final;
                        $evalNombre = optional($eval->profesor->usuario)->nombre ?? 'Evaluador';
                        $evalApellido = optional($eval->profesor->usuario)->apellido ?? '';
                        @endphp
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#c2d500] flex items-center justify-center text-[#07321e] font-bold text-xs">
                                        {{ substr($evalNombre, 0, 1) }}{{ substr($evalApellido, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">{{ $evalNombre }} {{ $evalApellido }}</span>
                                </div>
                                @if($evalNota)
                                <span class="text-lg font-bold text-gray-900">{{ number_format((float) $evalNota, 2) }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-tight {{ $evalColorBadge($eval->resultado) }}">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $evalResultadoIcono($eval->resultado) }}" />
                                    </svg>
                                    {{ $evalResultadoTexto($eval->resultado) }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <p class="text-xs text-gray-400 italic">No hay evaluaciones registradas.</p>
                        </div>
                        @endforelse

                        @if($puedeSubirInformeFinal)
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-600 mb-3 font-medium">Esta propuesta ha sido calificada. Sube el informe final para convertirla en Trabajo de Grado.</p>
                            <a href="{{ route('gestor.trabajo.informe-final', $trabajo->id_trabajo) }}"
                                class="w-full py-3 bg-[#07321e] text-white rounded-xl font-bold text-sm hover:bg-[#07321e]/80 transition-colors flex items-center justify-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Subir Informe Final
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif($algunoFinalizado)
                {{-- ⏳ TARJETA: ESPERANDO FINALIZACIÓN --}}
                @php
                $finalizadosCount = $trabajo->evaluadores->filter(fn($e) => $e->pivot->estado_revision === 'Finalizado')->count();
                $totalEvaluadores = $trabajo->evaluadores->count();
                $pendientes = $trabajo->evaluadores->filter(fn($e) => $e->pivot->estado_revision !== 'Finalizado');
                $nombrePendiente = $pendientes->first() ? optional($pendientes->first()->usuario)->nombre . ' ' . optional($pendientes->first()->usuario)->apellido : 'Otro evaluador';
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border-2 border-amber-200 overflow-hidden">
                    <div class="px-6 py-4 bg-amber-50 border-b border-amber-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-5 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-gray-900">Esperando finalización</h3>
                            <p class="text-xs text-gray-700 mt-0.5">{{ $finalizadosCount }} de {{ $totalEvaluadores }} evaluadores han finalizado la revisión.</p>
                        </div>
                        <span class="px-2 py-1 bg-amber-200 text-amber-800 rounded-lg text-xs font-bold uppercase">{{ $finalizadosCount }}/{{ $totalEvaluadores }}</span>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($trabajo->evaluadores as $eval)
                        @php
                        $finalizo = $eval->pivot->estado_revision === 'Finalizado';
                        $nombreEv = optional($eval->usuario)->nombre ?? 'Evaluador';
                        $apellidoEv = optional($eval->usuario)->apellido ?? '';
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $finalizo ? 'bg-emerald-50 border border-emerald-200' : 'bg-gray-50 border border-gray-200' }}">
                            <div class="w-8 h-8 rounded-full {{ $finalizo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center text-xs font-bold shrink-0">
                                {{ substr($nombreEv, 0, 1) }}{{ substr($apellidoEv, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-700 truncate">{{ $nombreEv }} {{ $apellidoEv }}</p>
                                <p class="text-[11px] {{ $finalizo ? 'text-emerald-600' : 'text-gray-400' }} font-medium">
                                    {{ $finalizo ? 'Finalizado — Firmando evaluación' : 'Pendiente por finalizar' }}
                                </p>
                            </div>
                            @if($finalizo)
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-amber-400 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @endif
                        </div>
                        @endforeach
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-[11px] text-gray-400 text-center italic">La calificación se mostrará una vez que ambos evaluadores finalicen y firmen la evaluación.</p>
                        </div>
                    </div>
                </div>
                @elseif($trabajo->estado === 'retroalimentacion_emitida')
                {{-- ✅ FORMULARIO ACTIVO: ambos evaluadores finalizaron --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 borde-gray-300 overflow-hidden">
                    <div class="px-6 py-4 bg-amber-50 border-b border-amber-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-gray-900">¡Acción Requerida!</h3>
                            <p class="text-xs text-gray-700 mt-0.5">Ambos jurados finalizaron. Sube la versión corregida.</p>
                        </div>
                        <span class="px-2 py-1 bg-amber-200 text-amber-800 rounded-lg text-xs font-bold uppercase">Actual: {{ strtoupper($trabajo->version_actual ?? 'v1') }}</span>
                    </div>
                    <div class="p-6">
                        @php
                        $nextVerNum = ((int) filter_var($trabajo->version_actual ?? 'v1', FILTER_SANITIZE_NUMBER_INT)) + 1;
                        $nextVer = 'V' . ($nextVerNum > 1 ? $nextVerNum : 2);
                        @endphp
                        <form action="{{ route('gestor.trabajo.subirNuevaVersion', $trabajo->id_trabajo) }}" method="POST" enctype="multipart/form-data" @submit="submitting = true">
                            @csrf
                            <div class="space-y-4">
                                <div class="relative h-[180px] group">
                                    <input type="file" id="archivo_pdf" name="archivo_pdf" accept="application/pdf" required
                                        @change="selectedFile = $event.target.files[0]?.name || null"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        :class="selectedFile ? 'pointer-events-none opacity-0' : ''">
                                    <template x-if="!selectedFile">
                                        <div class="h-full flex flex-col items-center justify-center bg-gray-50/60 border-2 border-dashed border-gray-300 rounded-2xl group-hover:bg-white group-hover:border-gray-400 transition-all">
                                            <svg class="w-8 h-8 text-gray-400 mb-2 group-hover:tex-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-xs font-bold text-gray-700">Seleccionar PDF corregido</span>
                                            <span class="text-[10px] text-gray-500 mt-1">Se guardará como versión {{ $nextVer }}</span>
                                        </div>
                                    </template>
                                    <template x-if="selectedFile">
                                        <div class="h-full flex flex-col items-center justify-center bg-[#f0fdf4] border-2 border-solid border-emerald-200 rounded-2xl">
                                            <svg class="w-8 h-8 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-xs font-bold text-emerald-700 text-center px-2 break-all" x-text="selectedFile"></span>
                                            <span class="text-[10px] text-emerald-500 mt-1">Listo para subir</span>
                                        </div>
                                    </template>
                                </div>

                                <button type="submit" :disabled="submitting"
                                    class="w-full py-3 bg-[#07321e] text-white rounded-xl font-bold text-sm hover:bg-[#07321e]/80 transition-colors flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="!selectedFile ? 'opacity-50 pointer-events-none' : ''">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Subir Versión {{ $nextVer }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @else
                {{-- ℹ️ PANEL INFORMATIVO: el formulario está bloqueado --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Cargar Nueva Versión</h3>
                        <p class="text-xs text-gray-400 mt-1">Disponible cuando ambos jurados finalicen.</p>
                    </div>
                    <div class="p-6">
                        {{-- Progreso del flujo --}}
                        @php
                        $etapas = [
                        ['key' => 'subido', 'label' => 'Documento subido'],
                        ['key' => 'en_revision', 'label' => 'En revisión'],
                        ['key' => 'retroalimentacion_emitida', 'label' => 'Retroalimentación emitida'],
                        ['key' => 'version_corregida_subida', 'label' => 'Versión corregida subida'],
                        ['key' => 'aprobado', 'label' => 'Aprobado'],
                        ];
                        $estadoActual = $trabajo->estado ?? 'subido';
                        $estadoIndex = array_search($estadoActual, array_column($etapas, 'key'));
                        if ($estadoIndex === false) $estadoIndex = 0;
                        @endphp

                        <div class="space-y-3 mb-5">
                            @foreach($etapas as $i => $etapa)
                            @php
                            $esPasado = $i < $estadoIndex;
                                $esActual=$i===$estadoIndex;
                                $esFuturo=$i> $estadoIndex;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0
                                    {{ $esPasado  ? 'bg-emerald-100 text-emerald-600' : '' }}
                                    {{ $esActual  ? 'bg-[#c2d500] text-[#07321e]' : '' }}
                                    {{ $esFuturo  ? 'bg-gray-100 text-gray-300' : '' }}
                                ">
                                        @if($esPasado)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                        @elseif($esActual)
                                        <div class="w-2 h-2 rounded-full bg-[#07321e]"></div>
                                        @else
                                        <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                        @endif
                                    </div>
                                    <span class="text-xs font-{{ $esActual ? 'bold' : 'medium' }}
                                    {{ $esPasado ? 'text-emerald-600 line-through' : '' }}
                                    {{ $esActual ? 'text-gray-900' : '' }}
                                    {{ $esFuturo ? 'text-gray-400' : '' }}
                                ">{{ $etapa['label'] }}</span>
                                    @if($esActual)
                                    <span class="ml-auto text-[9px] font-bold bg-[#c2d500]/20 text-[#07321e] px-1.5 py-0.5 rounded uppercase tracking-wide">Actual</span>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                <div class="ml-3 w-px h-3 {{ $esPasado ? 'bg-emerald-200' : 'bg-gray-100' }}"></div>
                                @endif
                                @endforeach
                        </div>

                        {{-- Mensaje explicativo según estado actual --}}
                        @if($estadoActual === 'subido')
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700 font-medium">
                            El documento fue subido. Está pendiente de ser enviado a revisión por el administrador.
                        </div>
                        @elseif($estadoActual === 'en_revision')
                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                            <p class="text-xs font-bold text-amber-800 mb-1">Evaluación en curso</p>
                            <p class="text-[11px] text-amber-700">Los jurados están revisando el documento. Podrás subir una nueva versión cuando ambos finalicen su retroalimentación.</p>
                        </div>
                        @elseif($estadoActual === 'version_corregida_subida')
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3">
                            <p class="text-xs font-bold text-indigo-800 mb-1">Versión corregida entregada</p>
                            <p class="text-[11px] text-indigo-700">La versión corregida ya fue subida. Esperando aprobación del administrador.</p>
                        </div>
                        @elseif($estadoActual === 'aprobado')
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs font-bold text-emerald-800">¡Proyecto aprobado!</p>
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-xs text-gray-500 font-medium">
                            Pendiente de avance en el flujo de revisión.
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

        </div>

        <!-- Modal de Éxito/Error -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-8 text-center z-10 transform transition-all border border-gray-100">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm"
                    :class="'bg-green-50 text-green-600'">
                    @if(session()->has('success'))
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ session()->has('success') ? '¡Completado!' : 'Error' }}</h2>
                <p class="text-gray-700 text-sm leading-relaxed mb-8 font-medium">{{ session('success') ?? session('error') }}</p>
                <button @click="showModal = false" class="w-full py-3 rounded-xl bg-gray-900 text-white font-bold hover:bg-black transition-colors">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection