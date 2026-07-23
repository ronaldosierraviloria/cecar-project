@extends('layouts.baseAdmin')

@section('title', 'Panel De Administrador | Lista de Estudiantes')
@section('meta_description', 'Listado de estudiantes registrados en el sistema de trabajos de grado de CECAR.')

@section('content')
@php
$usuario = Auth::user() ?? (object)['nombre' => 'Administrador', 'apellido' => '', 'rol' => 'Administrador'];
$estudiantes = $estudiantes ?? collect([]);
$facultades = $facultades ?? collect([]);
$areas = $areas ?? collect([]);
@endphp

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lista de Estudiantes</h1>
        <p class="text-sm text-gray-500 mt-1">Estudiantes registrados en el sistema con sus áreas de especialidad.</p>
    </div>
</div>

<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6">
    <form method="GET" action="{{ route('admin.listaEstudiantes') }}" id="filterForm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative">
                <select name="id_facultad" onchange="document.getElementById('filterForm').submit()"
                    class="appearance-none block w-full pl-9 pr-10 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:bg-white shadow-inner transition-all text-gray-600 font-medium">
                    <option value="">Todas las facultades</option>
                    @foreach($facultades as $facultad)
                    <option value="{{ $facultad->id_facultad }}" {{ request('id_facultad') == $facultad->id_facultad ? 'selected' : '' }}>{{ $facultad->nombre_facultad }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            <div class="relative">
                <select name="id_area" onchange="document.getElementById('filterForm').submit()"
                    id="areaFilter"
                    class="appearance-none block w-full pl-9 pr-10 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:bg-white shadow-inner transition-all text-gray-600 font-medium"
                    {{ request('id_facultad') ? '' : 'disabled' }}>
                    <option value="">Todas las áreas</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id_area }}" {{ request('id_area') == $area->id_area ? 'selected' : '' }}>{{ $area->nombre_area }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" />
                    </svg>
                </div>
            </div>

            <div class="relative">
                <input type="text" name="busqueda" id="searchInput" placeholder="Buscar estudiante..."
                    value="{{ request('busqueda') }}"
                    class="block w-full pl-9 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all shadow-inner">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(request('id_facultad') || request('id_area') || request('busqueda'))
                <a href="{{ route('admin.listaEstudiantes') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Limpiar filtros
                </a>
                @endif
            </div>
        </div>
    </form>
</div>

<x-notification type="success" />
<x-notification type="error" />

<div x-data="{
    showModal: false,
    studentToDelete: null,
    studentName: '',
    deleting: false,
    confirmDelete(id, name) {
        this.studentToDelete = id;
        this.studentName = name;
        this.showModal = true;
    },
    deleteStudent() {
        if (!this.studentToDelete) return;
        this.deleting = true;
        fetch(`/admin/estudiante/eliminar/${this.studentToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('student-' + this.studentToDelete);
                if (row) {
                    row.style.opacity = 0;
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => row.remove(), 300);
                }
                this.showModal = false;
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar el estudiante.'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al intentar eliminar el estudiante.');
        })
        .finally(() => { this.deleting = false; });
    }
}">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-16 text-center">#</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Facultad</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Área</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($estudiantes as $estudiante)
                    <tr id="student-{{ $estudiante->id_estudiante }}" class="hover:bg-gray-50/80 transition-all duration-300 group">
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-xs font-bold text-gray-400">#{{ $loop->iteration }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="relative inline-flex items-center justify-center w-9 h-9 overflow-hidden bg-[#07321e]/10 rounded-full shrink-0">
                                    <span class="text-xs font-bold text-[#07321e]">{{ substr($estudiante->nombre, 0, 1) }}{{ substr($estudiante->apellido, 0, 1) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-gray-900">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</span>
                                    @if($estudiante->trabajo)
                                    <p class="text-[11px] text-gray-400 font-medium truncate max-w-[200px]" title="{{ $estudiante->trabajo->titulo }}">
                                        {{ $estudiante->trabajo->titulo }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5 text-xs text-gray-800 font-medium">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $estudiante->correo ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-700">
                                {{ $estudiante->area->facultad->nombre_facultad ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-tight bg-[#c2d500]/10 text-[#07321e] border border-[#c2d500]/20">
                                {{ $estudiante->area->nombre_area ?? 'Sin Área' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <button @click="confirmDelete({{ $estudiante->id_estudiante }}, '{{ addslashes($estudiante->nombre . ' ' . $estudiante->apellido) }}')"
                                class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                title="Eliminar estudiante">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm font-medium text-gray-400 italic">
                            No hay estudiantes registrados en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de confirmación --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-6">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-8 text-center z-10 border border-gray-100">
            <div class="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">¿Eliminar Estudiante?</h3>
            <p class="text-sm text-gray-500 mb-8 font-medium">
                Se eliminará permanentemente a <span class="font-bold text-gray-900" x-text="studentName"></span> del sistema. Esta acción no se puede deshacer.
            </p>
            <div class="flex flex-col sm:flex-row-reverse gap-3">
                <button @click="deleteStudent()" :disabled="deleting"
                    class="w-full px-8 py-3 rounded-2xl bg-rose-600 text-white font-bold hover:bg-rose-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-rose-200">
                    <span x-show="!deleting">Sí, Eliminar</span>
                    <svg x-show="deleting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                <button type="button" @click="showModal = false" class="w-full px-8 py-3 rounded-2xl bg-white text-gray-500 font-bold border border-gray-200 hover:text-gray-700 hover:bg-gray-100 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var facultadSelect = document.querySelector('select[name="id_facultad"]');
        var areaSelect = document.getElementById('areaFilter');

        function toggleAreaFilter() {
            var hasFacultad = facultadSelect.value !== '';
            areaSelect.disabled = !hasFacultad;
            if (!hasFacultad) {
                areaSelect.value = '';
            }
        }

        if (facultadSelect) {
            facultadSelect.addEventListener('change', toggleAreaFilter);
            toggleAreaFilter();
        }

        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            var debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 600);
            });
        }
    });
</script>
@endsection