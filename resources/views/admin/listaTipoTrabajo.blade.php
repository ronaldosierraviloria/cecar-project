@extends('layouts.baseAdmin')

@section('title', 'Panel De Administrador | Gestión de Tipos de Trabajo')

@section('content')
@php
$tipos = $tipos ?? collect([]);
@endphp
<div x-data="{ 
    showAddTipoModal: false, 
    showEditModal: false, 
    showDeleteModal: false, 
    showToggleModal: false,
    selectedTipo: { id: '', nombre: '', activo: 1 },
    loading: false 
}">
    <div class="bg-white p-3 rounded-2xl border border-gray-200 shadow-sm mb-6">
        <div class="flex flex-col lg:flex-row items-center gap-4">
            <!-- Barra de Búsqueda -->
            <div class="relative w-full lg:w-80">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="searchInput" placeholder="Buscar tipo de trabajo..."
                    class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all shadow-inner">
            </div>

            <!-- Espaciador (Escritorio) -->
            <div class="hidden lg:block lg:flex-1"></div>

            <!-- Botón Agregar (Lado Derecho) -->
            <div class="flex flex-col gap-1.5 w-full lg:w-auto border-t lg:border-t-0 lg:border-l border-gray-200 pt-3 lg:pt-0 lg:pl-6">
                <button @click="showAddTipoModal = true"
                    class="flex items-center justify-center gap-2 px-6 py-2.5 bg-[#c2d500] text-[#07321e] rounded-xl font-bold text-sm hover:bg-[#b6c900] transition-all shadow-sm active:scale-95 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nuevo Tipo
                </button>
            </div>
        </div>
    </div>

    <x-notification type="success" />

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-16 text-center">#</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de Trabajo</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Rúbrica</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tipos as $tipo)
                    <tr class="hover:bg-gray-50/80 transition-colors group tipo-row">
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-xs font-bold text-gray-400">#{{ $loop->iteration }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $tagClass = match($tipo->nombre_tipo) {
                            'Investigación', 'Trabajo De Grado' => 'tag-trabajo',
                            'Emprendimiento' => 'tag-emprendimiento',
                            'Pasantía' => 'tag-pasantia',
                            default => 'tag-default'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-tight {{ $tagClass }} tipo-name">
                                {{ $tipo->nombre_tipo }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($tipo->rubrica->where('activo', true)->first())
                            <a href="{{ asset('storage/' . $tipo->rubrica->where('activo', true)->first()->archivo) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-tight bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-200 hover:text-blue-800 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Ver Rúbrica
                            </a>
                            @else
                            <span class="text-xs text-gray-400 italic">No asignada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($tipo->activo)
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">Activo</span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="selectedTipo = { 
                                    id: '{{ $tipo->id_tipo }}', 
                                    nombre: '{{ $tipo->nombre_tipo }}' 
                                }; showEditModal = true"
                                    class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                    data-tooltip-target="tooltip-edit-tipo-{{ $tipo->id_tipo }}" data-tooltip-placement="left">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-9-9v4m0 0h4m-4 0l9-9"></path>
                                    </svg>
                                </button>
                                <div id="tooltip-edit-tipo-{{ $tipo->id_tipo }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                    Editar tipo de trabajo
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                                <button @click="selectedTipo = { id: '{{ $tipo->id_tipo }}', nombre: '{{ $tipo->nombre_tipo }}', activo: {{ $tipo->activo ? 'true' : 'false' }} }; showToggleModal = true"
                                    class="p-2 text-gray-400 {{ $tipo->activo ? 'hover:text-amber-600 hover:bg-amber-50' : 'hover:text-green-600 hover:bg-green-50' }} rounded-lg transition-all"
                                    title="{{ $tipo->activo ? 'Desactivar' : 'Activar' }}">
                                    @if($tipo->activo)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @endif
                                </button>
                                <button @click="selectedTipo = { id: '{{ $tipo->id_tipo }}', nombre: '{{ $tipo->nombre_tipo }}' }; showDeleteModal = true"
                                    class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                    data-tooltip-target="tooltip-delete-tipo-{{ $tipo->id_tipo }}" data-tooltip-placement="left">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <div id="tooltip-delete-tipo-{{ $tipo->id_tipo }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-bold text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                                    Eliminar tipo de trabajo
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm font-medium text-gray-400 italic">
                            No hay tipos de trabajo registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL PARA AGREGAR TIPO --}}
    <div x-show="showAddTipoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showAddTipoModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-[#f4f4f4] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                <form action="{{ route('admin.tipoTrabajo.store') }}" method="POST" enctype="multipart/form-data" @submit="loading = true">
                    @csrf
                    <div class="bg-[#07321e] px-4 py-2">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">Nuevo Tipo de Trabajo</h3>
                            <button type="button" @click="showAddTipoModal = false" class="text-white/70 hover:text-white transition duration-150 p-2 hover:bg-white/10 rounded-lg">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nombre del Tipo</label>
                            <input type="text" name="nombre_tipo" placeholder="Ej: Proyecto de Grado" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent transition-all outline-none" required>
                        </div>

                        <div class="flex flex-col sm:flex-row-reverse gap-3">
                            <button type="submit" :disabled="loading" class="px-8 py-3 rounded-xl font-bold text-center hover:bg-[#b6c900] transition-all shadow-md w-full sm:w-auto bg-[#c2d500] text-[#07321e]">
                                <span x-show="!loading">Registrar Tipo</span>
                                <span x-show="loading">Procesando...</span>
                            </button>
                            <button type="button" @click="showAddTipoModal = false" class="px-8 py-3 rounded-xl bg-white font-bold text-center border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all w-full sm:w-auto text-sm">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR TIPO --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-[#f4f4f4] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                <form :action="`{{ url('/admin/tipo-trabajo') }}/${selectedTipo.id}`" method="POST" enctype="multipart/form-data" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    <div class="bg-[#07321e] px-4 py-2">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">Editar Tipo de Trabajo</h3>
                            <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white transition duration-150 p-2 hover:bg-white/10 rounded-lg">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nombre del Tipo</label>
                            <input type="text" name="nombre_tipo" x-model="selectedTipo.nombre" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent transition-all outline-none" required>
                        </div>

                        <div class="mb-8">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Actualizar Rúbrica (Opcional)</label>
                            <input type="file" name="archivo_rubrica" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#c2d500]/10 file:text-[#07321e] hover:file:bg-[#c2d500]/20 transition-all border border-gray-200 rounded-xl bg-white p-1">
                        </div>

                        <div class="flex flex-col sm:flex-row-reverse gap-3">
                            <button type="submit" :disabled="loading" class="px-8 py-3 rounded-xl font-bold text-center hover:bg-[#b6c900] transition-all shadow-md w-full sm:w-auto bg-[#c2d500] text-[#07321e]">
                                <span x-show="!loading">Actualizar Tipo</span>
                                <span x-show="loading">Procesando...</span>
                            </button>
                            <button type="button" @click="showEditModal = false" class="px-8 py-3 rounded-xl bg-white font-bold text-center border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all w-full sm:w-auto text-sm">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PARA ELIMINAR TIPO --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-50 mb-6">
                        <svg class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">¿Eliminar Tipo de Trabajo?</h3>
                    <p class="text-sm text-gray-500 mb-8">
                        Estás a punto de eliminar <span class="font-bold text-gray-900" x-text="selectedTipo.nombre"></span>. Esta acción eliminará también las rúbricas asociadas.
                    </p>

                    <div class="flex flex-col sm:flex-row-reverse gap-3">
                        <form :action="`{{ url('/admin/eliminar-tipo-trabajo') }}/${selectedTipo.id}`" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-8 py-3 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700 transition duration-300 shadow-lg shadow-rose-200">
                                Eliminar
                            </button>
                        </form>
                        <button type="button" @click="showDeleteModal = false" class="w-full px-8 py-3 rounded-xl bg-white text-gray-500 font-bold border border-gray-200 hover:text-gray-700 hover:bg-gray-100 transition-all">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL PARA ACTIVAR/DESACTIVAR TIPO DE TRABAJO --}}
    <div x-show="showToggleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showToggleModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showToggleModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showToggleModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-6"
                         :class="selectedTipo.activo ? 'bg-amber-50' : 'bg-green-50'">
                        <template x-if="selectedTipo.activo">
                            <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="!selectedTipo.activo">
                            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="selectedTipo.activo ? '¿Desactivar Tipo de Trabajo?' : '¿Activar Tipo de Trabajo?'"></h3>
                    <p class="text-sm text-gray-500 mb-8">
                        Estás a punto de <span x-text="selectedTipo.activo ? 'desactivar' : 'activar'"></span> <span class="font-bold text-gray-900" x-text="selectedTipo.nombre"></span>. 
                        <span x-text="selectedTipo.activo ? 'Este tipo de trabajo dejará de estar disponible para nuevos proyectos.' : 'Este tipo de trabajo volverá a estar disponible.'"></span>
                    </p>

                    <div class="flex flex-col sm:flex-row-reverse gap-3">
                        <form :action="`{{ url('/admin/tipo-trabajo') }}/${selectedTipo.id}/toggle`" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full px-8 py-3 rounded-xl font-bold text-white transition duration-300 shadow-lg"
                                    :class="selectedTipo.activo ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-200' : 'bg-green-600 hover:bg-green-700 shadow-green-200'"
                                    x-text="selectedTipo.activo ? 'Sí, Desactivar' : 'Sí, Activar'">
                            </button>
                        </form>
                        <button type="button" @click="showToggleModal = false" class="w-full px-8 py-3 rounded-xl bg-white text-gray-500 font-bold border border-gray-200 hover:bg-gray-100 transition-all">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('.tipo-row');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();

            rows.forEach(row => {
                const name = row.querySelector('.tipo-name').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTable);
    });
</script>
@endsection