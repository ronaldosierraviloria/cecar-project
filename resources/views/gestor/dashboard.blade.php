@extends('layouts.baseGestor')

@section('title', 'Panel del Gestor | Proyectos')
@section('meta_description', 'Panel del Gestor: lista de trabajos de grado registrados, acceso a detalles, rubricas y subida de informes.')

@section('content')
<x-notification type="success" />
<div x-data="trabajoApp()">
    @php
    $total = $trabajos->count();
    $sinRubricaCount = $trabajos->filter(fn($t) => !$t->rubricas || $t->rubricas->count() === 0)->count();
    @endphp
    <div class="lg:flex lg:items-center gap-4 mb-6">
        {{-- KPIs: 70% en lg --}}
        <div class="flex flex-wrap items-center gap-3 lg:w-1/2">
            {{-- KPI: Total --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-xl border border-gray-200 shadow-sm flex-1 min-w-[140px]">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-[#07321e]/5 text-[#07321e]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $total }}</p>
                    <p class="text-xs font-medium text-gray-500 leading-tight">Total</p>
                </div>
            </div>
        </div>

        {{-- Filtros: resto --}}
        <div class="flex flex-wrap items-center gap-3 lg:flex-1 lg:justify-end mt-3 lg:mt-0">
            {{-- Type filter --}}
            <select id="typeFilter"
                class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full sm:w-40 py-2.5 px-3 font-medium">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                <option value="{{ $tipo->nombre_tipo }}">{{ $tipo->nombre_tipo }}</option>
                @endforeach
            </select>

            {{-- Status filter --}}
            <select id="statusFilter"
                class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full sm:w-40 py-2.5 px-3 font-medium">
                <option value="">Todos los estados</option>
                <option value="subido">Subido</option>
                <option value="en_revision">En Revisión</option>
                <option value="retroalimentacion_emitida">Retroalimentación</option>
                <option value="version_corregida_subida">Versión Corregida</option>
                <option value="calificada">Calificada</option>
                <option value="esperando">Esperando finalización</option>
            </select>

            {{-- Evaluators filter --}}
            <select id="evaluatorFilter"
                class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full sm:w-44 py-2.5 px-3 font-medium">
                <option value="">Evaluadores</option>
                <option value="asignados">Con evaluadores asignados</option>
                <option value="sin_asignar">Sin evaluadores</option>
            </select>

            {{-- Clear filters --}}
            <button id="clearFilters" type="button"
                class="p-2.5 text-gray-400 bg-gray-50 border border-gray-200 rounded-xl hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-all"
                title="Limpiar filtros">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Grid de Proyectos -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
        @forelse ($trabajos as $trabajo)
        @php
        $tipo = optional($trabajo->tipo)->nombre_tipo ?? 'Sin tipo';
        $hasRubrica = $trabajo->rubricas && $trabajo->rubricas->count() > 0;
        $esPropuesta = $trabajo->plantilla_rubrica === 'propuesta_de_grado';
        $todosEvalFinalizados = $trabajo->evaluadores->isNotEmpty() && $trabajo->evaluadores->every(fn($e) => $e->pivot->estado_revision === 'Finalizado');
        $algunoFinalizado = $trabajo->evaluadores->contains(fn($e) => $e->pivot->estado_revision === 'Finalizado');
        $puedeSubirInformeFinal = $todosEvalFinalizados && $esPropuesta;
        $estado = $trabajo->estado ?? 'subido';
        if ($todosEvalFinalizados) {
            $estado = 'calificada';
        } elseif ($algunoFinalizado) {
            $estado = 'esperando';
        }
        @endphp

        <div class="project-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow flex flex-col group"
            id="trabajo-{{ $trabajo->id_trabajo ?? 0 }}"
            data-type="{{ $tipo }}"
            data-status="{{ $estado }}"
            data-evaluators="{{ $trabajo->evaluadores->isNotEmpty() ? 'asignados' : 'sin_asignar' }}">

            @php
            $estadoLabels = [
            'subido' => 'Subido',
            'en_revision' => 'En Revisión',
            'retroalimentacion_emitida' => 'Retroalimentación',
            'version_corregida_subida' => 'Versión Corregida',
            'aprobado' => 'Aprobado',
            'calificada' => 'Calificada',
            'esperando' => 'Esperando finalización',
            ];
            $estadoColors = [
            'subido' => 'bg-blue-50 text-blue-700 border-blue-200',
            'en_revision' => 'bg-amber-50 text-amber-700 border-amber-200',
            'retroalimentacion_emitida' => 'bg-purple-50 text-purple-700 border-purple-200',
            'version_corregida_subida' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'aprobado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'calificada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'esperando' => 'bg-amber-50 text-amber-700 border-amber-200',
            ];
            $estadoIcons = [
            'subido' => 'M7 16l-4-4m0 0l4-4m-4 4h18',
            'en_revision' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            'retroalimentacion_emitida' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
            'version_corregida_subida' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'aprobado' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'calificada' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'esperando' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
            @endphp

            <!-- Cabecera -->
            <div class="px-5 pt-5 pb-3 flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-tight shrink-0 {{ 
                                match($tipo) {
                                    'Trabajo De Grado' => 'tag-trabajo',
                                    'Emprendimiento' => 'tag-emprendimiento',
                                    'Pasantía' => 'tag-pasantia',
                                    default => 'tag-default'
                                }
                            }}">
                        {{ $tipo }}
                    </span>

                    @if($trabajo->retirado)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-tight bg-gray-100 text-gray-500 border border-gray-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Retirado
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-tight bg-emerald-50 text-emerald-600 border border-emerald-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Activo
                    </span>
                    @endif
                </div>

                @if($estado !== 'esperando')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold border shrink-0 {{ $estadoColors[$estado] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $estadoIcons[$estado] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}" />
                    </svg>
                    {{ $estadoLabels[$estado] ?? ucfirst($estado) }}
                </span>
                @endif
            </div>

            <!-- Cuerpo -->
            <div class="px-5 pb-4 flex-1 flex flex-col">
                <h3 class="text-[15px] font-bold text-gray-900 leading-snug mb-3 group-hover:text-[#07321e] transition-colors line-clamp-2" title="{{ $trabajo->titulo }}">
                    {{ $trabajo->titulo }}
                </h3>

                <div class="flex items-center gap-3 mb-3 text-[11px] text-gray-400">
                    <span class="index-badge font-semibold">#{{ $loop->iteration }}</span>
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ \Carbon\Carbon::parse($trabajo->fecha_subida)->format('d/m/Y') }}
                    </span>
                </div>

                <div class="mt-auto">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Estudiantes</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($trabajo->estudiante as $est)
                        <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1.5">
                            <div class="relative inline-flex items-center justify-center w-6 h-6 overflow-hidden bg-gray-200 rounded-full shrink-0">
                                <span class="text-[9px] font-bold text-gray-600">{{ substr($est->nombre, 0, 1) }}{{ substr($est->apellido, 0, 1) }}</span>
                            </div>
                            <span class="text-[12px] font-medium text-gray-700 truncate max-w-[120px]" title="{{ $est->nombre }} {{ $est->apellido }}">
                                {{ $est->nombre }} {{ $est->apellido }}
                            </span>
                        </div>
                        @empty
                        <span class="text-xs text-gray-400 italic">Sin asignar</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pie / Acciones -->
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30 flex items-center justify-between">
                <div>
                    @if($hasRubrica)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-tight bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Rúbrica Asignada
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @if($estado === 'esperando')
                    <span class="inline-flex items-center gap-1 px-2 py-1.5 rounded-md text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Esperando finalización
                    </span>
                    @endif
                    <a href="{{ route('gestor.trabajo.detalles', $trabajo->id_trabajo) }}"
                        class="flex text-sm gap-1 items-center bg-gray-100 p-2 text-gray-400 hover:text-[#07321e] hover:bg-[#07321e]/5 rounded-lg transition-all" title="Ver Detalles">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-bold">Detalles</span>
                    </a>

                    @if(!$trabajo->retirado)
                    @if($puedeSubirInformeFinal)
                    <a href="{{ route('gestor.trabajo.informe-final', $trabajo->id_trabajo) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-[11px] font-bold text-white bg-[#07321e] rounded-lg hover:bg-[#1a4d2e] transition-all"
                        title="Subir Informe Final">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                        </svg>
                        Subir Informe Final
                    </a>
                    @endif
                    @endif

                    @if($trabajo->retirado)
                    <button @click="confirmarEliminar({{ $trabajo->id_trabajo }}, '{{ addslashes($trabajo->titulo) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-all"
                        title="Eliminar Proyecto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-6 py-16 text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">No hay proyectos registrados</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-sm">Los proyectos que sean agregados al sistema aparecerán aquí para ser gestionados.</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         PAGINACIÓN
         ═══════════════════════════════════════════════════════════════ --}}
    @if($trabajos->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <nav id="paginationContainer" class="flex flex-col md:flex-row items-center justify-between px-6 py-4 bg-white gap-3" aria-label="Paginación de trabajos">
            {{-- Info text --}}
            <span class="text-sm text-gray-700 font-medium">
                Mostrando <span class="font-bold text-gray-900" id="startRange">0</span> a
                <span class="font-bold text-gray-900" id="endRange">0</span> de
                <span class="font-bold text-gray-900" id="totalItems">0</span> resultados
            </span>

            {{-- Page buttons --}}
            <ul class="inline-flex items-center -space-x-px h-9 text-sm">
                <li>
                    <button id="prevBtn" class="flex items-center justify-center px-3 h-9 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span class="sr-only">Anterior</span>
                        <svg class="w-3 h-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4" />
                        </svg>
                    </button>
                </li>
                <li id="pageNumbers" class="contents"></li>
                <li>
                    <button id="nextBtn" class="flex items-center justify-center px-3 h-9 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span class="sr-only">Siguiente</span>
                        <svg class="w-3 h-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
                        </svg>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    @endif

    {{-- Modales --}}
    <!-- Confirmar Eliminación -->
    <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showConfirmModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">

                <div class="bg-rose-50 px-6 py-4 border-b border-rose-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center text-rose-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-rose-900">Confirmar Eliminación</h3>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <p class="text-sm text-gray-600 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar permanentemente el proyecto <span class="font-bold text-gray-900" x-text="'«' + trabajoTitulo + '»'"></span>? Esta acción no se puede deshacer.
                    </p>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex flex-col sm:flex-row-reverse gap-2">
                    <button @click="eliminar(trabajoId)"
                        class="px-5 py-2.5 bg-rose-600 text-white rounded-xl font-bold text-sm hover:bg-rose-700 transition-all shadow-sm">
                        Sí, Eliminar
                    </button>
                    <button @click="showConfirmModal = false"
                        class="px-5 py-2.5 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-sm hover:text-gray-900 hover:bg-gray-100 transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Éxito al Eliminar -->
    <div x-show="showSuccessModal" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed bottom-10 right-10 z-50">
        <div class="bg-[#07321e] text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-[#c2d500]/30">
            <div class="w-8 h-8 bg-[#c2d500] rounded-full flex items-center justify-center text-[#07321e]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold">¡Proyecto Eliminado!</p>
                <p class="text-[10px] text-white/70">El registro ha sido removido con éxito.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('trabajoApp', () => ({
            showSuccessModal: false,
            showConfirmModal: false,
            trabajoId: null,
            trabajoTitulo: '',

            confirmarEliminar(id, titulo) {
                this.trabajoId = id;
                this.trabajoTitulo = titulo;
                this.showConfirmModal = true;
            },

            eliminar(id) {
                this.showConfirmModal = false;

                fetch(`/gestor/trabajo/eliminar/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error en el servidor');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const el = document.getElementById(`trabajo-${id}`);
                            if (el) {
                                el.style.opacity = 0;
                                el.style.transform = 'scale(0.95)';
                                setTimeout(() => el.remove(), 300);
                            }
                            this.showSuccessModal = true;
                            setTimeout(() => this.showSuccessModal = false, 3000);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error al intentar eliminar el trabajo.');
                    });
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function() {
        const typeFilter = document.getElementById('typeFilter');
        const statusFilter = document.getElementById('statusFilter');
        const evaluatorFilter = document.getElementById('evaluatorFilter');
        const clearFilters = document.getElementById('clearFilters');

        let currentPage = 1;
        const itemsPerPage = 6;
        let filteredRows = [];

        function filterAndPaginate() {
            const rows = document.querySelectorAll('.project-card');
            const selectedType = typeFilter.value;
            const selectedStatus = statusFilter.value;
            const selectedEvaluator = evaluatorFilter.value;

            filteredRows = [];
            rows.forEach(row => {
                const type = row.dataset.type;
                const status = row.dataset.status;
                const evaluators = row.dataset.evaluators;

                const matchesType = !selectedType || type === selectedType;
                const matchesStatus = !selectedStatus || status === selectedStatus;
                const matchesEvaluator = !selectedEvaluator || evaluators === selectedEvaluator;

                if (matchesType && matchesStatus && matchesEvaluator) {
                    filteredRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            const totalItemsVal = filteredRows.length;
            const totalPages = Math.ceil(totalItemsVal / itemsPerPage) || 1;

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItemsVal);

            filteredRows.forEach((row, idx) => {
                if (idx >= startIndex && idx < endIndex) {
                    row.style.display = '';
                    const indexCol = row.querySelector('.index-badge');
                    if (indexCol) {
                        indexCol.textContent = `#${idx + 1}`;
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('totalItems').textContent = totalItemsVal;
            document.getElementById('startRange').textContent = totalItemsVal > 0 ? startIndex + 1 : 0;
            document.getElementById('endRange').textContent = endIndex;

            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;

            const pageNumbersContainer = document.getElementById('pageNumbers');
            let pageHtml = '';

            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            if (endPage - startPage < maxPagesToShow - 1) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                if (i === currentPage) {
                    pageHtml += `<li><span aria-current="page" class="flex items-center justify-center px-3 h-9 leading-tight text-white bg-[#07321e] border border-[#07321e] font-bold">${i}</span></li>`;
                } else {
                    pageHtml += `<li><button type="button" class="page-link-btn flex items-center justify-center px-3 h-9 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 transition-colors font-medium" data-page="${i}">${i}</button></li>`;
                }
            }
            pageNumbersContainer.innerHTML = pageHtml;

            document.querySelectorAll('.page-link-btn').forEach(btn => {
                btn.onclick = function() {
                    currentPage = parseInt(this.dataset.page);
                    filterAndPaginate();
                };
            });
        }

        document.getElementById('prevBtn').onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                filterAndPaginate();
            }
        };
        document.getElementById('nextBtn').onclick = () => {
            const totalPages = Math.ceil(filteredRows.length / itemsPerPage) || 1;
            if (currentPage < totalPages) {
                currentPage++;
                filterAndPaginate();
            }
        };

        if (typeFilter) typeFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginate();
        });
        if (statusFilter) statusFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginate();
        });
        if (evaluatorFilter) evaluatorFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginate();
        });

        if (clearFilters) {
            clearFilters.addEventListener('click', function() {
                if (typeFilter) typeFilter.value = '';
                if (statusFilter) statusFilter.value = '';
                if (evaluatorFilter) evaluatorFilter.value = '';
                currentPage = 1;
                filterAndPaginate();
            });
        }

        filterAndPaginate();
    });
</script>
@endpush