@extends('layouts.baseAdmin')

@section('title', 'Asignar Evaluadores | Panel Admin')
@section('meta_description', 'Asignación y gestión de evaluadores para un trabajo de grado específico. Selecciona jurados, director y subdirector.')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm"
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
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">Asignación de Evaluadores</h1>
                <p class="text-sm text-gray-500 mt-1">Gestiona los evaluadores para este proyecto.</p>
            </div>
        </div>
    </div>

    @php
        $studentArea = $trabajo->estudiante->first()?->area;
        $studentFaculty = $studentArea?->facultad;
    @endphp

    <div x-data="{
        showModal: false,
        modalType: '',
        modalMessage: '',
        activeTab: '{{ $isEditing ? 'asignados' : 'disponibles' }}',
        isEditing: {{ json_encode($isEditing) }},
        selectedEvaluadores: {{ json_encode($evaluadoresAsignadosIds ?? []) }},
        evaluadoresById: {{ json_encode($evaluadoresCatalogo->keyBy('id')) }},
        isSelected(id) {
            return this.selectedEvaluadores.includes(Number(id));
        },
        toggleEvaluador(id) {
            id = Number(id);
            if (this.isSelected(id)) {
                this.removeEvaluador(id);
                return;
            }
            if (this.selectedEvaluadores.length >= 2) {
                this.modalType = 'error';
                this.modalMessage = 'Ya has seleccionado 2 evaluadores. Quita uno para elegir otro.';
                this.showModal = true;
                return;
            }
            const evaluador = this.evaluadoresById[id];
            if (evaluador && evaluador.carga >= 2) {
                this.modalType = 'warning';
                this.modalMessage = 'El evaluador ' + evaluador.nombre + ' ya tiene una carga de ' + evaluador.carga + ' trabajo(s). Se aumentará la carga más allá de 2 trabajos.';
                this.showModal = true;
            }
            this.selectedEvaluadores.push(id);
        },
        removeEvaluador(id) {
            id = Number(id);
            this.selectedEvaluadores = this.selectedEvaluadores.filter(item => item !== id);
        },
        formatAssignDate(value) {
            if (!value) return 'Recién seleccionado';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return 'Asignado';
            return 'Asignado: ' + date.toLocaleDateString('es-ES');
        },
        canSubmit() {
            if (this.isEditing) {
                return this.selectedEvaluadores.length <= 2;
            }
            return this.selectedEvaluadores.length >= 1 && this.selectedEvaluadores.length <= 2;
        }
    }"
    x-init="
        @if(session('success'))
            showModal = true;
            modalType = 'success';
            modalMessage = {{ json_encode(session('success')) }};
        @elseif(session('error'))
            showModal = true;
            modalType = 'error';
            modalMessage = {{ json_encode(session('error')) }};
        @endif
    "
    class="space-y-8">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden text-left">
            <div class="p-8">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="space-y-1 text-left">
                        @php
                            $tipo_nombre = optional($trabajo->tipo)->nombre_tipo ?? 'Sin tipo';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-tight {{
                            match($tipo_nombre) {
                                'Investigación', 'Trabajo De Grado' => 'tag-trabajo',
                                'Emprendimiento' => 'tag-emprendimiento',
                                'Pasantía' => 'tag-pasantia',
                                default => 'tag-default'
                            }
                        }}">
                            {{ $tipo_nombre }}
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 leading-tight">{{ $trabajo->titulo }}</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8 pt-8 border-t border-gray-100">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 text-left">Estudiantes</span>
                        <div class="space-y-3">
                            @forelse ($trabajo->estudiante as $est)
                            <div class="flex items-center gap-3 group text-left">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                    {{ substr($est->nombre, 0, 1) }}{{ substr($est->apellido, 0, 1) }}
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-bold text-gray-700">{{ $est->nombre }} {{ $est->apellido }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">
                                        @if($est->area)
                                            {{ $est->area->nombre_area }}
                                        @else
                                            <span class="text-amber-500">Sin área asignada</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400 italic">No hay estudiantes asignados.</p>
                            @endforelse
                        </div>
                    </div>

                    @if($studentFaculty || $studentArea)
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 text-left">Filtro automático</span>
                        <div class="space-y-3">
                            @if($studentFaculty)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-700">{{ $studentFaculty->nombre_facultad }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Facultad</p>
                                </div>
                            </div>
                            @endif
                            @if($studentArea)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 font-bold text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-700">{{ $studentArea->nombre_area }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Área</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden text-left">
            <div class="px-8 py-6 border-b border-gray-100">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Seleccionar Evaluadores</h3>
                        @if($studentArea)
                        <p class="text-xs text-gray-500 font-medium mt-1">
                            Mostrando evaluadores del área <strong>{{ $studentArea->nombre_area }}</strong>.
                        </p>
                        @else
                        <p class="text-xs text-amber-600 font-medium mt-1">
                            Los estudiantes no tienen área asignada. Se muestran todos los evaluadores.
                        </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Seleccionados:</span>
                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold" x-text="selectedEvaluadores.length + '/2'"></span>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.guardarEvaluador', $trabajo->id_trabajo) }}" method="POST">
                @csrf

                <template x-for="id in selectedEvaluadores" :key="'evaluador-' + id">
                    <input type="hidden" name="evaluadores[]" :value="id">
                </template>

                <div class="px-8 pt-6 pb-0">
                    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl" role="tablist">
                        <button type="button" @click="activeTab = 'disponibles'" role="tab"
                            :class="activeTab === 'disponibles' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 px-4 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Disponibles
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full"
                                :class="activeTab === 'disponibles' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-200 text-gray-500'">
                                {{ $evaluadoresDisponibles->count() }}
                            </span>
                        </button>
                        <button type="button" @click="activeTab = 'asignados'" role="tab"
                            :class="activeTab === 'asignados' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 px-4 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Asignados
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full"
                                :class="activeTab === 'asignados' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-200 text-gray-500'"
                                x-text="selectedEvaluadores.length">
                            </span>
                        </button>
                    </div>
                </div>

                <div x-show="selectedEvaluadores.length >= 2" x-cloak class="mx-8 mt-6">
                    <div class="bg-amber-50 border border-amber-100 text-amber-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Límite alcanzado (máximo 2 evaluadores).
                    </div>
                </div>

                {{-- TAB: DISPONIBLES --}}
                <div x-show="activeTab === 'disponibles'" x-cloak class="mt-4">
                    <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-gray-50 z-10">
                                <tr>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100">Evaluador</th>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100">Facultad / Área</th>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Carga</th>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 text-right">Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($evaluadoresDisponibles as $ev)
                                <tr class="hover:bg-indigo-50/30 transition-colors group {{ $ev->trabajos_count >= 2 ? 'border-l-4 border-l-orange-500' : '' }}">
                                    <td class="px-8 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xs">
                                                {{ substr($ev->usuario->nombre, 0, 1) }}{{ substr($ev->usuario->apellido, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800">{{ $ev->usuario->nombre }} {{ $ev->usuario->apellido }}</p>
                                                <p class="text-xs text-gray-500">{{ $ev->usuario->correo }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-3">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ optional(optional($ev->area)->facultad)->nombre_facultad ?? 'N/A' }}</span>
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded-md text-[10px] font-bold uppercase tracking-wider w-fit">
                                                {{ $ev->area->nombre_area ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-3 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-xs font-bold {{ $ev->trabajos_count >= 2 ? 'text-orange-600' : ($ev->trabajos_count == 0 ? 'text-green-600' : 'text-amber-600') }}">
                                                {{ $ev->trabajos_count }}
                                            </span>
                                            @if($ev->trabajos_count >= 2)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-[9px] font-bold">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Alta carga
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-8 py-3 text-right">
                                        <button type="button"
                                            @click="toggleEvaluador({{ $ev->id_profesor }})"
                                            :disabled="isSelected({{ $ev->id_profesor }}) || (selectedEvaluadores.length >= 2 && !isSelected({{ $ev->id_profesor }}))"
                                            :class="isSelected({{ $ev->id_profesor }}) ? 'bg-indigo-100 text-indigo-700 cursor-default' : 'bg-[#07321e] text-white hover:bg-black'"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                            <span x-text="isSelected({{ $ev->id_profesor }}) ? 'Seleccionado' : 'Agregar'"></span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <p class="text-sm text-gray-400 font-medium">No hay evaluadores disponibles de esta área.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: ASIGNADOS --}}
                <div x-show="activeTab === 'asignados'" x-cloak class="mt-4">
                    <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-gray-50 z-10">
                                <tr>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100">Evaluador</th>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100">Facultad / Área</th>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Estado</th>
                                    <th class="px-8 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 text-right">Quitar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="id in selectedEvaluadores" :key="'assigned-' + id">
                                    <tr class="group" :class="evaluadoresById[id]?.decision_evaluador === 'rechazado' ? 'bg-red-50/40' : 'bg-indigo-50/20'" x-show="evaluadoresById[id]">
                                        <td class="px-8 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                                                     :class="evaluadoresById[id]?.decision_evaluador === 'rechazado' ? 'bg-red-100 text-red-600' : 'bg-indigo-100 text-indigo-600'"
                                                     x-text="evaluadoresById[id]?.iniciales"></div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-800" x-text="evaluadoresById[id]?.nombre"></p>
                                                    <p class="text-xs text-gray-500" x-text="evaluadoresById[id]?.correo"></p>
                                                    <p class="text-[10px] text-gray-400" x-text="formatAssignDate(evaluadoresById[id]?.fecha_asignacion)"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-3">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="evaluadoresById[id]?.facultad"></span>
                                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider w-fit"
                                                      :class="evaluadoresById[id]?.decision_evaluador === 'rechazado' ? 'bg-red-100 text-red-600' : 'bg-indigo-100 text-indigo-600'"
                                                      x-text="evaluadoresById[id]?.area"></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-3 text-center">
                                            <template x-if="evaluadoresById[id]?.decision_evaluador === 'rechazado'">
                                                <div>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-[10px] font-bold">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        Rechazado
                                                    </span>
                                                    <p class="text-[10px] text-red-500 mt-1 font-medium" x-show="evaluadoresById[id]?.motivo_rechazo" x-text="evaluadoresById[id]?.motivo_rechazo"></p>
                                                </div>
                                            </template>
                                            <template x-if="evaluadoresById[id]?.decision_evaluador !== 'rechazado'">
                                                <div>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-[10px] font-bold">
                                                        Asignado
                                                    </span>
                                                    <div class="flex items-center gap-1 mt-1">
                                                        <p class="text-[10px] text-gray-400" x-text="'Carga: ' + (evaluadoresById[id]?.carga ?? 0) + '/2'"></p>
                                                        <template x-if="(evaluadoresById[id]?.carga ?? 0) >= 2">
                                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded-full text-[8px] font-bold">
                                                                <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                                Alta carga
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-8 py-3 text-right">
                                            <button type="button"
                                                @click="removeEvaluador(id)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="selectedEvaluadores.length === 0">
                                    <td colspan="4" class="px-8 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm text-gray-400 font-medium">No hay evaluadores seleccionados.</p>
                                            <p class="text-xs text-gray-400">Agrega evaluadores desde la pestaña "Disponibles" o guarda para dejar el proyecto sin evaluadores.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-8 border-t border-gray-100 bg-gray-50/50">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-xs text-gray-400 text-center sm:text-left">
                            <span class="font-medium text-gray-500">Nota:</span>
                            <span x-show="isEditing">Usa la pestaña "Asignados" para quitar evaluadores. Puedes dejar el proyecto sin evaluadores al guardar.</span>
                            <span x-show="!isEditing">Mínimo 1 evaluador, máximo 2.</span>
                        </p>
                        <button type="submit"
                            :disabled="!canSubmit()"
                            class="w-full sm:w-auto px-10 py-3 bg-[#07321e] text-white rounded-xl font-bold text-base shadow-md hover:bg-black transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="isEditing ? 'Guardar cambios' : 'Guardar asignación'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- MODAL --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-8 text-center z-10 transform transition-all border border-gray-100">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm"
                     :class="modalType === 'success' ? 'bg-green-50 text-green-600' : (modalType === 'warning' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')">
                     <template x-if="modalType === 'success'">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                     </template>
                     <template x-if="modalType === 'warning'">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                     </template>
                     <template x-if="modalType === 'error'">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                     </template>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2" x-text="modalType === 'success' ? '¡Operación Exitosa!' : (modalType === 'warning' ? 'Advertencia' : 'Error')"></h2>
                <p class="text-gray-700 text-sm leading-relaxed mb-8 font-medium" x-text="modalMessage"></p>
                <div class="flex flex-col gap-2">
                    <button @click="showModal = false"
                            class="w-full py-3 rounded-xl bg-gray-900 text-white font-bold hover:bg-black transition-colors">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection