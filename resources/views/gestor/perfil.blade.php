@extends('layouts.baseGestor')

@section('title', 'Mi Perfil | Gestor')
@section('meta_description', 'Informacion del perfil del gestor en el Sistema de Gestion de Trabajos de Grado de CECAR.')

@section('content')
@php
$roleDescription = 'Como Gestor, eres responsable de la creación de proyectos y la gestión de rúbricas para los procesos de grado.';
$roleTheme = ['bg' => 'from-blue-800 to-indigo-900'];
@endphp

<div class="max-w-4xl mx-auto" x-data="{ showEditModal: false, loading: false }">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('gestor.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-[#07321e] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>

    <x-notification type="success" />
    <x-notification type="error" />

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        {{-- Header del Perfil --}}
        <div class="h-32 bg-gradient-to-r {{ $roleTheme['bg'] }}"></div>

        <div class="px-8 pb-10">
            <div class="relative flex justify-between items-end -mt-12 mb-6">
                {{-- Avatar --}}
                <div class="p-1 bg-white rounded-2xl shadow-lg">
                    <div class="w-32 h-32 rounded-xl flex items-center justify-center overflow-hidden border-2 border-white" style="background-color: var(--cecar-lime);">
                        <span class="text-4xl font-bold text-[#07321e]">
                            {{ substr($usuario->nombre, 0, 1) }}{{ substr($usuario->apellido, 0, 1) }}
                        </span>
                    </div>
                </div>

                {{-- Botón Editar --}}
                <button @click="showEditModal = true" class="px-5 py-2.5 bg-[#07321e] hover:bg-emerald-800 text-white rounded-xl font-bold text-sm shadow-md transition-all active:scale-95 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-9-9v4m0 0h4m-4 0l9-9" />
                    </svg>
                    Editar Perfil
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                    <p class="text-gray-500 font-medium flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 rounded-full bg-[#c2d500] animate-pulse"></span>
                        Gestor Académico
                    </p>

                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#07321e]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Correo Institucional</p>
                                <p class="text-gray-700 font-semibold">{{ $usuario->correo }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#07321e]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Miembro desde</p>
                                <p class="text-gray-700 font-semibold">{{ \Carbon\Carbon::parse($usuario->created_at)->isoFormat('D [de] MMMM [de] YYYY') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 flex flex-col justify-between overflow-hidden shadow-sm">
                    <div class="bg-gradient-to-r {{ $roleTheme['bg'] }} px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#c2d500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-white">Rol del Gestor</h3>
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ $roleDescription }}
                        </p>

                        <div class="mt-6 flex items-center justify-between py-3 px-4 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Estado</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded-full">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Activo
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR PERFIL --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEditModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showEditModal" class="inline-block align-bottom bg-[#f4f4f4] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <form action="{{ route('user.perfil.update') }}" method="POST" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-[#07321e] px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-bold text-white">Editar Perfil</h3>
                            <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white transition duration-150 p-2 hover:bg-white/10 rounded-lg">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nombre</label>
                                <input type="text" name="nombre" value="{{ $usuario->nombre }}" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Apellido</label>
                                <input type="text" name="apellido" value="{{ $usuario->apellido }}" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Correo Electrónico</label>
                            <input type="email" name="correo" value="{{ $usuario->correo }}" class="block bg-white w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none" required>
                        </div>

                        <hr class="my-4 border-gray-200" />

                        <div class="grid grid-cols-2 gap-4 mb-6" x-data="{ showPassword: false, showConfirmPassword: false }">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nueva Contraseña</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="contraseña" placeholder="Opcional..." class="block bg-white w-full px-4 py-3 pr-10 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" minlength="8">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-700">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Confirmar Contraseña</label>
                                <div class="relative">
                                    <input :type="showConfirmPassword ? 'text' : 'password'" name="contraseña_confirmation" placeholder="Opcional..." class="block bg-white w-full px-4 py-3 pr-10 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:border-transparent outline-none placeholder-gray-400" minlength="8">
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-700">
                                        <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-gray-100 justify-end">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl bg-white font-bold text-gray-500 border border-gray-200 hover:bg-gray-100 text-sm">Cancelar</button>
                            <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl font-bold bg-[#c2d500] text-[#07321e] hover:bg-[#b6c900] transition-all shadow-md text-sm">
                                <span x-show="!loading">Guardar Cambios</span>
                                <span x-show="loading">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection