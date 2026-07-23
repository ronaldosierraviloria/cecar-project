@extends('layouts.baseAdmin')

@section('title', 'Panel De Administrador | Gestión de Usuarios')

@section('content')
@php
$usuario_actual = Auth::user() ?? (object)['nombre' => 'Administrador', 'apellido' => '', 'rol' => 'Administrador', 'correo' => 'admin@cecar.edu.co'];
$usuarios = $usuarios ?? collect([]);
$areas = $areas ?? collect([]);
$facultades = $facultades ?? collect([]);
@endphp

<div x-data='{
    showAddModal: false,
    showEditModal: false,
    showToggleModal: false,
    selectedUser: { id: "", nombre: "", apellido: "", correo: "", rol: "", id_area: "", id_facultad: "", activo: 1 },
    loading: false,
    selectedRole: "",
    selectedFacultad: "",
    selectedArea: "",
    areas: @json($areas->map(fn($a) => ["id" => $a->id_area, "nombre" => $a->nombre_area, "id_facultad" => $a->id_facultad])->values()),
    get filteredAreas() {
        if (!this.selectedFacultad) return [];
        return this.areas.filter(a => Number(a.id_facultad) === Number(this.selectedFacultad));
    }
}'>
    <div class="bg-white p-3 rounded-2xl border border-gray-200 shadow-sm mb-6">
        <div class="flex flex-col lg:flex-row items-center gap-4">
            <!-- Barra de Búsqueda -->
            <div class="relative w-full lg:w-80">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="searchInput" placeholder="Buscar usuario..."
                    class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all shadow-inner">
            </div>

            <!-- Filtro por Rol -->
            <form id="filterForm" method="GET" action="{{ route('admin.usuarios.index') }}" class="w-full lg:w-auto">
                <select name="rol" onchange="document.getElementById('filterForm').submit()" class="block w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] transition-all shadow-inner text-gray-600">
                    <option value="">Todos los Roles</option>
                    <option value="Administrador" {{ request('rol') == 'Administrador' ? 'selected' : '' }}>Administradores</option>
                    <option value="Gestor" {{ request('rol') == 'Gestor' ? 'selected' : '' }}>Gestores</option>
                    <option value="Evaluador" {{ request('rol') == 'Evaluador' ? 'selected' : '' }}>Evaluadores</option>
                </select>
            </form>

            <div class="hidden lg:block lg:flex-1"></div>

            <!-- Botón Agregar -->
            <div class="flex flex-col gap-1.5 w-full lg:w-auto border-t lg:border-t-0 lg:border-l border-gray-200 pt-3 lg:pt-0 lg:pl-6">
                <button @click="showAddModal = true; selectedRole = ''"
                    class="flex items-center justify-center gap-2 px-6 py-2.5 bg-[#c2d500] text-[#07321e] rounded-xl font-bold text-sm hover:bg-[#b6c900] transition-all shadow-sm active:scale-95 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nuevo Usuario
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
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Detalles</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($usuarios as $user)
                    <tr class="hover:bg-gray-50/80 transition-colors group user-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 user-name">
                                    {{ $user->nombre }} {{ $user->apellido }}
                                </span>
                                <span class="text-xs text-gray-500 user-email">{{ $user->correo }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->rol == 'Administrador' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->rol == 'Gestor' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->rol == 'Evaluador' ? 'bg-orange-100 text-orange-800' : '' }}">
                                {{ $user->rol }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($user->rol == 'Evaluador' && $user->profesor)
                                <div class="text-xs">
                                    <span class="font-semibold text-gray-700">Área:</span> {{ $user->profesor->area->nombre_area ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-gray-400 italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->activo)
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">Activo</span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="selectedUser = { 
                                    id: '{{ $user->id_usuario }}', 
                                    nombre: '{{ $user->nombre }}', 
                                    apellido: '{{ $user->apellido }}', 
                                    correo: '{{ $user->correo }}',
                                    rol: '{{ $user->rol }}',
                                    id_area: '{{ $user->profesor->id_area ?? '' }}',
                                    id_facultad: '{{ $user->id_facultad ?? '' }}'
                                }; showEditModal = true" 
                                    class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                    title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-9-9v4m0 0h4m-4 0l9-9"></path>
                                    </svg>
                                </button>
                                
                                @if(Auth::id() !== $user->id_usuario)
                                <button @click="selectedUser = { id: '{{ $user->id_usuario }}', nombre: '{{ $user->nombre }}', activo: {{ $user->activo ? 'true' : 'false' }} }; showToggleModal = true" 
                                    class="p-2 text-gray-400 {{ $user->activo ? 'hover:text-amber-600 hover:bg-amber-50' : 'hover:text-green-600 hover:bg-green-50' }} rounded-lg transition-all"
                                    title="{{ $user->activo ? 'Desactivar' : 'Activar' }}">
                                    @if($user->activo)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @endif
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm font-medium text-gray-400 italic">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL PARA AGREGAR USUARIO --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAddModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showAddModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showAddModal" class="inline-block align-bottom bg-[#f4f4f4] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
                <form action="{{ route('admin.usuarios.store') }}" method="POST" @submit="loading = true">
                    @csrf
                    <div class="bg-[#07321e] px-4 py-2">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">Registrar Nuevo Usuario</h3>
                            <button type="button" @click="showAddModal = false" class="text-white/70 hover:text-white transition duration-150 p-2 hover:bg-white/10 rounded-lg">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nombre</label>
                                <input type="text" name="nombre" placeholder="Ej: Juan" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Apellido</label>
                                <input type="text" name="apellido" placeholder="Ej: Pérez" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" required>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Correo Electrónico</label>
                            <input type="email" name="correo" placeholder="Ej: usuario@ejemplo.com" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" required>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Rol del Usuario</label>
                            <select name="rol" x-model="selectedRole" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none" required>
                                <option value="" disabled>Seleccione un rol...</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Gestor">Gestor</option>
                                <option value="Evaluador">Evaluador</option>
                            </select>
                        </div>

                        <div class="mb-6" x-show="selectedRole === 'Evaluador'">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Facultad</label>
                            <select x-model="selectedFacultad" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none">
                                <option value="">Seleccione una facultad...</option>
                                @foreach($facultades as $facultad)
                                    <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-6" x-show="selectedRole === 'Evaluador' && selectedFacultad" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Área de Especialidad (Requerido para Evaluadores)</label>
                            <select name="id_area" x-model="selectedArea" :required="selectedRole === 'Evaluador'" :disabled="!selectedFacultad" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none">
                                <option value="">Seleccione un área...</option>
                                <template x-for="area in filteredAreas" :key="area.id">
                                    <option :value="area.id" x-text="area.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div class="mb-6" x-show="selectedRole === 'Administrador'">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Facultad (Requerido para Administradores)</label>
                            <select name="id_facultad" :required="selectedRole === 'Administrador'" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none">
                                <option value="">Seleccione una facultad...</option>
                                @foreach($facultades as $facultad)
                                    <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" x-data="{ showPassword: false, showConfirmPassword: false }">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Contraseña</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="contraseña" placeholder="Contraseña..." class="block bg-white w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" required>
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-700 rounded-lg">
                                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Confirmar Contraseña</label>
                                <div class="relative">
                                    <input :type="showConfirmPassword ? 'text' : 'password'" name="contraseña_confirmation" placeholder="Contraseña..." class="block bg-white w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" required>
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-700 rounded-lg">
                                        <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row-reverse gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" :disabled="loading" class="px-8 py-3 rounded-xl font-bold text-center hover:bg-[#b6c900] transition-all shadow-md w-full sm:w-auto bg-[#c2d500] text-[#07321e]">
                                <span x-show="!loading">Registrar Usuario</span>
                                <span x-show="loading">Procesando...</span>
                            </button>
                            <button type="button" @click="showAddModal = false" class="px-8 py-3 rounded-xl bg-white font-bold text-center border border-gray-200 text-gray-500 hover:bg-gray-100 w-full sm:w-auto text-sm">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR USUARIO --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEditModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showEditModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showEditModal" class="inline-block align-bottom bg-[#f4f4f4] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
                <form :action="`{{ url('/admin/usuarios') }}/${selectedUser.id}`" method="POST" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    <div class="bg-[#07321e] px-4 py-2">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">Editar Usuario</h3>
                            <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white p-2 hover:bg-white/10 rounded-lg">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nombre</label>
                                <input type="text" name="nombre" x-model="selectedUser.nombre" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Apellido</label>
                                <input type="text" name="apellido" x-model="selectedUser.apellido" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] outline-none" required>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Correo Electrónico</label>
                            <input type="email" name="correo" x-model="selectedUser.correo" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] outline-none" required>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Rol</label>
                            <input type="text" x-model="selectedUser.rol" disabled class="block bg-gray-100 w-full px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                        </div>

                        <div class="mb-6" x-show="selectedUser.rol === 'Evaluador'">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Área de Especialidad</label>
                            <select name="id_area" x-model="selectedUser.id_area" :required="selectedUser.rol === 'Evaluador'" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] outline-none">
                                <option value="">Seleccione un área...</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-6" x-show="selectedUser.rol === 'Administrador'">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Facultad</label>
                            <select name="id_facultad" x-model="selectedUser.id_facultad" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] outline-none">
                                <option value="">Seleccione una facultad...</option>
                                @foreach($facultades as $facultad)
                                    <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-8" x-data="{ showPassword: false }">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nueva Contraseña (Opcional)</label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="contraseña" placeholder="Dejar en blanco para no cambiar" class="block bg-white w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] outline-none">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-700 p-1 rounded-lg">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row-reverse gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" :disabled="loading" class="px-8 py-3 rounded-xl font-bold hover:bg-[#b6c900] bg-[#c2d500] text-[#07321e]">
                                <span x-show="!loading">Actualizar</span>
                                <span x-show="loading">Procesando...</span>
                            </button>
                            <button type="button" @click="showEditModal = false" class="px-8 py-3 rounded-xl bg-white font-bold border border-gray-200 text-gray-500 hover:bg-gray-100">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PARA ACTIVAR/DESACTIVAR USUARIO --}}
    <div x-show="showToggleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showToggleModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showToggleModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showToggleModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-6"
                         :class="selectedUser.activo ? 'bg-amber-50' : 'bg-green-50'">
                        <template x-if="selectedUser.activo">
                            <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="!selectedUser.activo">
                            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="selectedUser.activo ? '¿Desactivar Usuario?' : '¿Activar Usuario?'"></h3>
                    <p class="text-sm text-gray-500 mb-8">
                        Estás a punto de <span x-text="selectedUser.activo ? 'desactivar' : 'activar'"></span> a <span class="font-bold text-gray-900" x-text="selectedUser.nombre"></span>. 
                        <span x-text="selectedUser.activo ? 'El usuario no podrá iniciar sesión hasta que sea reactivado.' : 'El usuario volverá a tener acceso al sistema.'"></span>
                    </p>

                    <div class="flex flex-col sm:flex-row-reverse gap-3">
                        <form :action="`{{ url('/admin/usuarios') }}/${selectedUser.id}/toggle`" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full px-8 py-3 rounded-xl font-bold text-white transition duration-300 shadow-lg"
                                    :class="selectedUser.activo ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-200' : 'bg-green-600 hover:bg-green-700 shadow-green-200'"
                                    x-text="selectedUser.activo ? 'Sí, Desactivar' : 'Sí, Activar'">
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
        const rows = document.querySelectorAll('.user-row');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();

            rows.forEach(row => {
                const name = row.querySelector('.user-name').textContent.toLowerCase();
                const email = row.querySelector('.user-email').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
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
