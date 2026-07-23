@extends('layouts.baseAdmin')

@section('title', 'Panel De Administrador | Facultades y Áreas')

@section('content')
@php
$usuario = Auth::user() ?? (object)['nombre' => 'Administrador', 'apellido' => '', 'rol' => 'Administrador'];
$facultades = $facultades ?? collect([]);
$areasSinFacultad = $areasSinFacultad ?? collect([]);
@endphp

<div x-data="{ 
    showAddFacultadModal: false, 
    showEditFacultadModal: false, 
    showDeleteFacultadModal: false, 
    selectedFacultad: { id: '', nombre: '' },

    showAddAreaModal: false, 
    showEditAreaModal: false, 
    showDeleteAreaModal: false, 
    selectedArea: { id: '', nombre: '', id_facultad: '' },

    openAccordion: null,
    
    toggleAccordion(id) {
        this.openAccordion = this.openAccordion === id ? null : id;
    }
}">
    <!-- Top Bar -->
    <div class="bg-white p-3 rounded-2xl border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <h2 class="text-xl font-bold text-gray-800">Facultades y Áreas de Especialidad</h2>
        <div class="flex gap-2">
            <button @click="showAddFacultadModal = true"
                class="flex items-center justify-center gap-2 px-4 py-2 bg-[#07321e] text-white rounded-xl font-bold text-sm hover:bg-[#07321e]/80 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nueva Facultad
            </button>
            <button @click="showAddAreaModal = true"
                class="flex items-center justify-center gap-2 px-4 py-2 bg-[#c2d500] text-[#07321e] rounded-xl font-bold text-sm hover:bg-[#b6c900] transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nueva Área
            </button>
        </div>
    </div>

    <x-notification type="success" />
    <x-notification type="error" />


    <!-- Accordion List -->
    <div class="space-y-4">
        @forelse ($facultades as $facultad)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Facultad Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 transition cursor-pointer"
                @click="toggleAccordion('{{ $facultad->id_facultad }}')">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                        :class="openAccordion === '{{ $facultad->id_facultad }}' ? 'rotate-90' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-lg font-bold text-gray-900">{{ $facultad->nombre_facultad }}</span>
                    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">{{ $facultad->areas->count() }} áreas</span>
                </div>
                <!-- Facultad Actions -->
                <div class="flex items-center gap-2" @click.stop>
                    <button @click="selectedFacultad = { id: '{{ $facultad->id_facultad }}', nombre: '{{ $facultad->nombre_facultad }}' }; showEditFacultadModal = true"
                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                        data-tooltip-target="tooltip-edit-facultad-{{ $facultad->id_facultad }}" data-tooltip-placement="left">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-9-9v4m0 0h4m-4 0l9-9"></path>
                        </svg>
                    </button>
                    <div id="tooltip-edit-facultad-{{ $facultad->id_facultad }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                        Editar facultad
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                    <button @click="selectedFacultad = { id: '{{ $facultad->id_facultad }}', nombre: '{{ $facultad->nombre_facultad }}' }; showDeleteFacultadModal = true"
                        class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                        data-tooltip-target="tooltip-delete-facultad-{{ $facultad->id_facultad }}" data-tooltip-placement="left">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <div id="tooltip-delete-facultad-{{ $facultad->id_facultad }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                        Eliminar facultad
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
            </div>

            <!-- Areas Content -->
            <div x-show="openAccordion === '{{ $facultad->id_facultad }}'" x-collapse x-cloak>
                <div class="border-t border-gray-100 p-4">
                    @if($facultad->areas->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($facultad->areas as $area)
                        <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
                            <span class="text-sm font-semibold text-gray-800">{{ $area->nombre_area }}</span>
                            <div class="flex items-center gap-1">
                                <button @click="selectedArea = { id: '{{ $area->id_area }}', nombre: '{{ $area->nombre_area }}', id_facultad: '{{ $facultad->id_facultad }}' }; showEditAreaModal = true"
                                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-all"
                                    data-tooltip-target="tooltip-edit-area-{{ $area->id_area }}" data-tooltip-placement="left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <div id="tooltip-edit-area-{{ $area->id_area }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                    Editar área
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                                <button @click="selectedArea = { id: '{{ $area->id_area }}', nombre: '{{ $area->nombre_area }}' }; showDeleteAreaModal = true"
                                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-all"
                                    data-tooltip-target="tooltip-delete-area-{{ $area->id_area }}" data-tooltip-placement="left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <div id="tooltip-delete-area-{{ $area->id_area }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                    Eliminar área
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic text-center py-4">No hay áreas registradas en esta facultad.</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white p-8 rounded-xl border border-gray-200 text-center shadow-sm">
            <p class="text-gray-500">No hay facultades registradas en el sistema.</p>
        </div>
        @endforelse

        <!-- Áreas sin Facultad -->
        @if($areasSinFacultad->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-rose-200 overflow-hidden mt-6">
            <div class="flex items-center justify-between px-6 py-4 bg-rose-50 hover:bg-rose-100 transition cursor-pointer"
                @click="toggleAccordion('sin-facultad')">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-500 transform transition-transform duration-200"
                        :class="openAccordion === 'sin-facultad' ? 'rotate-90' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-lg font-bold text-rose-900">Áreas sin Facultad Asignada (Heredadas)</span>
                    <span class="bg-rose-200 text-rose-800 text-xs font-bold px-2 py-1 rounded-full">{{ $areasSinFacultad->count() }} áreas</span>
                </div>
            </div>
            <div x-show="openAccordion === 'sin-facultad'" x-collapse x-cloak>
                <div class="border-t border-rose-100 p-4 bg-rose-50/50">
                    <p class="text-sm text-rose-600 mb-4 font-semibold">Estas áreas fueron creadas antes del sistema de facultades. Por favor, edítalas para asignarles una facultad.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($areasSinFacultad as $area)
                        <div class="flex items-center justify-between p-3 bg-white border border-rose-200 rounded-lg shadow-sm hover:shadow-md transition">
                            <span class="text-sm font-semibold text-gray-800">{{ $area->nombre_area }}</span>
                            <div class="flex items-center gap-1">
                                <button @click="selectedArea = { id: '{{ $area->id_area }}', nombre: '{{ $area->nombre_area }}', id_facultad: '' }; showEditAreaModal = true"
                                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-all"
                                    data-tooltip-target="tooltip-edit-area-{{ $area->id_area }}" data-tooltip-placement="left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <div id="tooltip-edit-area-{{ $area->id_area }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                    Asignar a facultad
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                                <button @click="selectedArea = { id: '{{ $area->id_area }}', nombre: '{{ $area->nombre_area }}' }; showDeleteAreaModal = true"
                                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-all"
                                    data-tooltip-target="tooltip-delete-area-{{ $area->id_area }}" data-tooltip-placement="left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <div id="tooltip-delete-area-{{ $area->id_area }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                    Eliminar área
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- MODALES DE FACULTAD -->

    {{-- Agregar Facultad --}}
    <div x-show="showAddFacultadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAddFacultadModal = false"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <form action="{{ route('admin.facultad.store') }}" method="POST" @submit="loading = true">
                    @csrf
                    <div class="bg-[#07321e] px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Registrar Nueva Facultad</h3>
                        <button type="button" @click="showAddFacultadModal = false" class="text-white/70 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg></button>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre de la Facultad</label>
                        <input type="text" name="nombre_facultad" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2" required>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showAddFacultadModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                            <button type="submit" :disabled="loading" class="px-4 py-2 bg-[#c2d500] text-white rounded-lg hover:opacity-90">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Editar Facultad --}}
    <div x-show="showEditFacultadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showEditFacultadModal = false"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <form :action="`{{ url('/admin/facultad') }}/${selectedFacultad.id}`" method="POST" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Editar Facultad</h3>
                        <button type="button" @click="showEditFacultadModal = false" class="text-white/70 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg></button>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre de la Facultad</label>
                        <input type="text" name="nombre_facultad" x-model="selectedFacultad.nombre" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2" required>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showEditFacultadModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                            <button type="submit" :disabled="loading" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Eliminar Facultad --}}
    <div x-show="showDeleteFacultadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteFacultadModal = false"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 mb-4">
                    <svg class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-gray-900">¿Eliminar Facultad?</h3>
                <p class="text-gray-500 mb-6">Estás a punto de eliminar la facultad <span class="font-bold text-gray-900" x-text="selectedFacultad.nombre"></span>. Asegúrate de que no tenga áreas asociadas.</p>
                <div class="flex justify-center gap-3">
                    <form :action="`{{ url('/admin/eliminar-facultad') }}/${selectedFacultad.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-rose-600 text-white rounded-lg font-bold hover:bg-rose-700">Eliminar</button>
                    </form>
                    <button type="button" @click="showDeleteFacultadModal = false" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- MODALES DE ÁREA -->

    {{-- Agregar Área --}}
    <div x-show="showAddAreaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAddAreaModal = false"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <form action="{{ route('admin.area.store') }}" method="POST" @submit="loading = true">
                    @csrf
                    <div class="bg-[#07321e] px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Registrar Nueva Área</h3>
                        <button type="button" @click="showAddAreaModal = false" class="text-white/70 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Facultad a la que pertenece</label>
                            <select name="id_facultad" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#c2d500] focus:border-[#c2d500] px-4 py-2" required>
                                <option value="" disabled selected>Seleccione una facultad</option>
                                @foreach($facultades as $fac)
                                <option value="{{ $fac->id_facultad }}">{{ $fac->nombre_facultad }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Área</label>
                            <input type="text" name="nombre_area" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#c2d500] focus:border-[#c2d500] px-4 py-2" required>
                        </div>
                        <div class="mt-6 flex justify-end gap-3 pt-2">
                            <button type="button" @click="showAddAreaModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                            <button type="submit" :disabled="loading" class="px-4 py-2 bg-[#c2d500] text-[#07321e] font-bold rounded-lg hover:bg-[#b0c200]">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Editar Área --}}
    <div x-show="showEditAreaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showEditAreaModal = false"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <form :action="`{{ url('/admin/area') }}/${selectedArea.id}`" method="POST" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    <div class="bg-[#07321e] px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Editar Área</h3>
                        <button type="button" @click="showEditAreaModal = false" class="text-white/70 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Facultad</label>
                            <select name="id_facultad" x-model="selectedArea.id_facultad" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#c2d500] focus:border-[#c2d500] px-4 py-2" required>
                                @foreach($facultades as $fac)
                                <option value="{{ $fac->id_facultad }}">{{ $fac->nombre_facultad }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Área</label>
                            <input type="text" name="nombre_area" x-model="selectedArea.nombre" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#c2d500] focus:border-[#c2d500] px-4 py-2" required>
                        </div>
                        <div class="mt-6 flex justify-end gap-3 pt-2">
                            <button type="button" @click="showEditAreaModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                            <button type="submit" :disabled="loading" class="px-4 py-2 bg-[#c2d500] text-[#07321e] font-bold rounded-lg hover:bg-[#b0c200]">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Eliminar Área --}}
    <div x-show="showDeleteAreaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteAreaModal = false"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 mb-4">
                    <svg class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-gray-900">¿Eliminar Área?</h3>
                <p class="text-gray-500 mb-6">Estás a punto de eliminar el área <span class="font-bold text-gray-900" x-text="selectedArea.nombre"></span>.</p>
                <div class="flex justify-center gap-3">
                    <form :action="`{{ url('/admin/eliminar-area') }}/${selectedArea.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-rose-600 text-white rounded-lg font-bold hover:bg-rose-700">Eliminar</button>
                    </form>
                    <button type="button" @click="showDeleteAreaModal = false" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection