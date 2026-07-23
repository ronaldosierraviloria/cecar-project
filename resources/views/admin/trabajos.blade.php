@extends('layouts.baseAdmin')

@section('title', 'Trabajos de Grado | Panel Admin')
@section('meta_description', 'Listado y gestión de todos los trabajos de grado registrados. Filtra, busca y asigna evaluadores desde este panel.')

@section('content')
<x-notification type="success" />

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Trabajos de Grado</h1>
        <p class="text-sm text-gray-500 mt-1">Gestiona y asigna evaluadores a los trabajos de grado de los estudiantes.</p>
    </div>
</div>

<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
        <h2 class="text-lg font-bold text-gray-800">Filtros de Búsqueda</h2>
        
        <div class="flex items-center gap-2">
            <button id="clearFilters" type="button" class="px-4 py-2 flex items-center gap-2 text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Limpiar
            </button>
            <button id="refresh-btn" class="hidden px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm transition font-medium shadow-sm items-center gap-2">
                <svg id="refresh-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refrescar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Type filter --}}
        <select id="typeFilter" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full py-2.5 px-3 font-medium">
            <option value="">Todos los Tipos</option>
            <option value="Trabajo De Grado">Trabajo De Grado</option>
            <option value="Pasantía">Pasantía</option>
            <option value="Emprendimiento">Emprendimiento</option>
        </select>

        {{-- Status filter --}}
        <select id="statusFilter" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full py-2.5 px-3 font-medium">
            <option value="">Cualquier Estado</option>
            <option value="sin_asignar">Sin Asignar</option>
            <option value="asignado">Con Evaluadores</option>
        </select>

        {{-- Facultad filter --}}
        <select id="facultadFilter" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full py-2.5 px-3 font-medium">
            <option value="">Todas las Facultades</option>
            @foreach($facultades as $facultad)
            <option value="{{ $facultad->id_facultad }}" {{ request('id_facultad') == $facultad->id_facultad ? 'selected' : '' }}>{{ $facultad->nombre_facultad }}</option>
            @endforeach
        </select>

        {{-- Area filter --}}
        <select id="areaFilter" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-[#c2d500] focus:border-[#c2d500] w-full py-2.5 px-3 font-medium" disabled>
            <option value="" data-placeholder="true">Todas las Áreas</option>
            @foreach($areas as $area)
            <option value="{{ $area->id_area }}" data-facultad-id="{{ $area->id_facultad }}" {{ request('id_area') == $area->id_area ? 'selected' : '' }}>
                {{ $area->nombre_area }}
            </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TABLA PRINCIPAL (Flowbite Table Styling)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-200">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-bold w-16">#</th>
                    <th scope="col" class="px-6 py-4 font-bold">Información del Proyecto</th>
                    <th scope="col" class="px-6 py-4 font-bold">Estudiantes</th>
                    <th scope="col" class="px-6 py-4 font-bold">Evaluadores</th>
                    <th scope="col" class="px-6 py-4 font-bold text-center">Fecha</th>
                    <th scope="col" class="px-6 py-4 font-bold text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($trabajos as $trabajo)
                @php
                $tipo = optional($trabajo->tipo)->nombre_tipo ?? 'Sin tipo';
                $evaluadores = $trabajo->evaluadores;
                $countEval = $evaluadores->count();
                $primerEstudiante = $trabajo->estudiante->first();
                $areaTrabajo = optional($primerEstudiante)->area;
                $facultadTrabajo = optional($areaTrabajo)->facultad;
                @endphp
                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50/80 transition-colors group project-row"
                    data-type="{{ $tipo }}"
                    data-status="{{ $countEval > 0 ? 'asignado' : 'sin_asignar' }}"
                    data-area="{{ optional($areaTrabajo)->id_area }}"
                    data-facultad="{{ optional($facultadTrabajo)->id_facultad }}">
                    {{-- # --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-xs font-bold text-gray-400">#{{ $loop->iteration }}</span>
                    </td>

                    {{-- Proyecto --}}
                    <th scope="row" class="px-6 py-4 min-w-[300px]">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm font-bold text-gray-900 line-clamp-2 leading-snug group-hover:text-[#07321e] transition-colors project-title" title="{{ $trabajo->titulo }}">
                                {{ $trabajo->titulo }}
                            </span>
                            <div class="flex items-center gap-2">
                                @php
                                $badgeClasses = match($tipo) {
                                'Investigación', 'Trabajo De Grado' => 'bg-green-100 text-green-800',
                                'Emprendimiento' => 'bg-blue-100 text-blue-800',
                                'Pasantía' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800'
                                };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-tight {{ $badgeClasses }}">
                                    {{ $tipo }}
                                </span>
                                @php
                                    $esPropuesta = $trabajo->plantilla_rubrica === 'propuesta_de_grado';
                                    $todosEvalFinalizados = $trabajo->evaluadores->isNotEmpty() && $trabajo->evaluadores->every(fn($e) => $e->pivot->estado_revision === 'Finalizado');
                                    $algunoFinalizado = $trabajo->evaluadores->contains(fn($e) => $e->pivot->estado_revision === 'Finalizado');
                                    $tieneEvaluadores = $trabajo->evaluadores->isNotEmpty();
                                    $algunoRechazado = $trabajo->evaluadores->contains(fn($e) => ($e->pivot->decision_evaluador ?? null) === 'rechazado');
                                    $estadoProcesoAdmin = match(true) {
                                        $algunoRechazado => ['label' => 'Rechazado por evaluador', 'class' => 'bg-red-50 text-red-700 border-red-200'],
                                        $todosEvalFinalizados => ['label' => 'Calificada', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        $trabajo->estado === 'retroalimentacion_emitida' => ['label' => 'Retroalimentación', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                        $trabajo->estado === 'version_corregida_subida' => ['label' => 'Versión corregida', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                        $algunoFinalizado => ['label' => 'Esperando finalización', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                        $tieneEvaluadores => ['label' => 'En revisión', 'class' => 'bg-sky-50 text-sky-700 border-sky-200'],
                                        $trabajo->estado === 'aprobado' => ['label' => 'Aprobado', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        default => ['label' => 'Subido', 'class' => 'bg-gray-50 text-gray-700 border-gray-200'],
                                    };
                                @endphp
                                @if($trabajo->retirado)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-tight bg-gray-100 text-gray-500 border border-gray-200">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Retirado
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-tight {{ $estadoProcesoAdmin['class'] }} border">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $estadoProcesoAdmin['label'] }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </th>

                    {{-- Estudiantes --}}
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1.5">
                            @forelse($trabajo->estudiante as $est)
                            <div class="flex items-center gap-2">
                                <div class="relative inline-flex items-center justify-center w-6 h-6 overflow-hidden bg-gray-200 rounded-full shrink-0">
                                    <span class="text-[9px] font-bold text-gray-600">{{ substr($est->nombre, 0, 1) }}{{ substr($est->apellido, 0, 1) }}</span>
                                </div>
                                <span class="text-xs font-medium text-gray-700 whitespace-nowrap">{{ $est->nombre }} {{ $est->apellido }}</span>
                            </div>
                            @empty
                            <span class="text-xs text-gray-400 italic">Sin asignar</span>
                            @endforelse
                        </div>
                    </td>

                    {{-- Evaluadores --}}
                    <td class="px-6 py-4">
                        @if($countEval > 0)
                        <div class="flex flex-col gap-1.5">
                            @foreach($evaluadores as $eval)
                            @php
                                $evalRechazado = ($eval->pivot->decision_evaluador ?? null) === 'rechazado';
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="relative inline-flex items-center justify-center w-6 h-6 overflow-hidden rounded-full shrink-0 {{ $evalRechazado ? 'bg-red-100' : 'bg-[#c2d500]' }}">
                                    <span class="text-[9px] font-bold {{ $evalRechazado ? 'text-red-700' : 'text-[#07321e]' }}">{{ substr($eval->usuario->nombre, 0, 1) }}{{ substr($eval->usuario->apellido, 0, 1) }}</span>
                                </div>
                                <span class="text-xs font-bold {{ $evalRechazado ? 'text-red-600' : 'text-gray-700' }} whitespace-nowrap">{{ $eval->usuario->nombre }} {{ $eval->usuario->apellido }}</span>
                                @if($evalRechazado)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold bg-red-100 text-red-700">Rechazado</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <span class="inline-flex items-center bg-red-100 text-red-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase">
                            <span class="w-2 h-2 me-1.5 bg-red-500 rounded-full animate-pulse"></span>
                            Sin evaluadores
                        </span>
                        @endif
                    </td>

                    {{-- Fecha --}}
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="inline-flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ \Carbon\Carbon::parse($trabajo->fecha_subida)->format('d/m/Y') }}
                        </div>
                    </td>

                    {{-- Acciones --}}
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Botón Asignar/Editar (oculto si el trabajo está retirado) --}}
                            @if(!$trabajo->retirado)
                            <a href="{{ route('admin.asignarEvaluador', $trabajo->id_trabajo) }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-[#07321e] bg-[#c2d500]/15 border border-[#c2d500]/30 rounded-xl hover:bg-[#c2d500]/30 focus:ring-4 focus:outline-none focus:ring-[#c2d500]/20 transition-all"
                                data-tooltip-target="tooltip-assign-{{ $trabajo->id_trabajo }}" data-tooltip-placement="left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                {{ $countEval > 0 ? 'Editar' : 'Asignar' }}
                            </a>
                            <div id="tooltip-assign-{{ $trabajo->id_trabajo }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                {{ $countEval > 0 ? 'Editar evaluadores asignados' : 'Asignar evaluadores a este trabajo' }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                            @endif

                            {{-- Botón Ver Detalles --}}
                            <a href="{{ route('admin.detallesTrabajo', $trabajo->id_trabajo) }}"
                                class="inline-flex items-center p-2 text-gray-400 hover:text-[#07321e] hover:bg-gray-100 rounded-xl focus:ring-4 focus:outline-none focus:ring-gray-200 transition-all"
                                data-tooltip-target="tooltip-view-{{ $trabajo->id_trabajo }}" data-tooltip-placement="left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <div id="tooltip-view-{{ $trabajo->id_trabajo }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                Ver detalles del trabajo
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-500 mb-1">No hay trabajos registrados</p>
                            <p class="text-xs text-gray-400">Los trabajos aparecerán aquí una vez sean cargados al sistema</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         PAGINACIÓN (Flowbite Pagination Style)
         ═══════════════════════════════════════════════════════════════ --}}
    <nav id="paginationContainer" class="flex flex-col md:flex-row items-center justify-between px-6 py-4 border-t border-gray-200 bg-white gap-3" aria-label="Paginación de trabajos">
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



{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS DE FILTRADO Y PAGINACIÓN Y GRÁFICOS
     ═══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeFilter = document.getElementById('typeFilter');
        const statusFilter = document.getElementById('statusFilter');
        const facultadFilter = document.getElementById('facultadFilter');
        const areaFilter = document.getElementById('areaFilter');
        const clearFilters = document.getElementById('clearFilters');

        let currentPage = 1;
        const itemsPerPage = 5;
        let filteredRows = [];

        function updateAreaFilterOptions() {
            if (!areaFilter || !facultadFilter) {
                return;
            }

            const selectedFaculty = facultadFilter.value;
            const placeholder = areaFilter.querySelector('option[data-placeholder="true"]');
            const areaOptions = areaFilter.querySelectorAll('option[data-facultad-id]');
            let visibleCount = 0;

            areaOptions.forEach(option => {
                const matchesFaculty = selectedFaculty && option.dataset.facultadId === selectedFaculty;
                option.hidden = !matchesFaculty;
                if (matchesFaculty) {
                    visibleCount++;
                }
            });

            if (placeholder) {
                placeholder.textContent = selectedFaculty ? 'Todas las áreas' : 'Seleccione una facultad primero';
            }

            if (!selectedFaculty) {
                areaFilter.value = '';
                areaFilter.disabled = true;
                return;
            }

            areaFilter.disabled = false;

            if (areaFilter.value) {
                const currentOption = areaFilter.querySelector(`option[value="${areaFilter.value}"]`);
                if (!currentOption || currentOption.hidden) {
                    areaFilter.value = '';
                }
            }

            if (visibleCount === 0) {
                areaFilter.value = '';
            }
        }

        function filterAndPaginate() {
            const rows = document.querySelectorAll('.project-row');
            const selectedType = typeFilter.value;
            const selectedStatus = statusFilter.value;
            const selectedFaculty = facultadFilter ? facultadFilter.value : '';
            const selectedArea = areaFilter ? areaFilter.value : '';

            filteredRows = [];
            rows.forEach(row => {
                const type = row.dataset.type;
                const status = row.dataset.status;
                const faculty = row.dataset.facultad || '';
                const area = row.dataset.area || '';

                const matchesType = !selectedType || type === selectedType;
                const matchesStatus = !selectedStatus || status === selectedStatus;
                const matchesFaculty = !selectedFaculty || faculty === selectedFaculty;
                const matchesArea = !selectedArea || area === selectedArea;

                if (matchesType && matchesStatus && matchesFaculty && matchesArea) {
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
                    const indexCol = row.querySelector('td span.text-gray-400');
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

        typeFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginate();
        });
        statusFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginate();
        });
        facultadFilter.addEventListener('change', () => {
            updateAreaFilterOptions();
            currentPage = 1;
            filterAndPaginate();
        });
        areaFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginate();
        });

        clearFilters.addEventListener('click', function() {
            typeFilter.value = '';
            statusFilter.value = '';
            facultadFilter.value = '';
            updateAreaFilterOptions();
            currentPage = 1;
            filterAndPaginate();
        });

        updateAreaFilterOptions();
        filterAndPaginate();
    });

</script>
@endpush
@endsection