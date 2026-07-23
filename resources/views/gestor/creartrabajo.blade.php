@extends('layouts.baseGestor')

@section('title', 'Añadir Proyecto | Panel Gestor')

@section('content')
@php
$hasStatus = session()->has('success') || session()->has('error');
@endphp
<div class="bg-[#f4f4f4] py-4 md:py-6">
    <div x-data="trabajoApp()" class="max-w-6xl mx-auto">
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
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Añadir Proyecto</h1>
                    <p class="text-sm text-gray-500 mt-1">Registra los datos oficiales del nuevo trabajo de grado.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('trabajo.guardar') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-6">
            @csrf

            <!-- Información General -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#c2d500] rounded-xl flex items-center justify-center text-[#07321e]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Información General</h2>
                    </div>
                </div>
                <div class="p-8 space-y-8">
                    <div class="space-y-2">
                        <label for="titulo" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Título del Proyecto</label>
                        <input type="text" id="titulo" name="titulo" required
                            placeholder="Ej: Sistema de Gestión de Inventarios..."
                            class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Categoría</label>
                                <button type="button" @click="selectedTipo = null; document.querySelectorAll('input[name=id_tipo]').forEach(el => el.checked = false)"
                                    class="text-[10px] font-bold text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2 py-1 rounded-lg transition-all flex items-center gap-1"
                                    :class="!selectedTipo ? 'opacity-0 pointer-events-none' : ''">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Limpiar selección
                                </button>
                            </div>
                            <div class="space-y-2">
                                @foreach ($tiposTrabajo as $tipo)
                                <label class="flex items-center p-3 rounded-xl border border-gray-100 bg-gray-50 cursor-pointer hover:bg-white hover:border-[#c2d500]/50 transition-all group">
                                    <input id="tipo_{{ $tipo->id_tipo }}" name="id_tipo" type="radio" value="{{ $tipo->id_tipo }}" required
                                        @change="selectedTipo = $event.target.value"
                                        class="w-4 h-4 text-[#07321e] border-gray-300 focus:ring-[#c2d500]">
                                    <span class="ml-3 text-sm font-semibold text-gray-600 group-hover:text-gray-900">{{ $tipo->nombre_tipo }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Documento Final (PDF)</label>
                                <button type="button" @click="limpiarArchivo()"
                                    class="text-[10px] font-bold text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2 py-1 rounded-lg transition-all flex items-center gap-1"
                                    :class="!selectedFile ? 'opacity-0 pointer-events-none' : ''">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1v3" />
                                    </svg>
                                    Eliminar archivo
                                </button>
                            </div>
                            <div class="relative h-[164px] group">
                                <input type="file" id="archivo_pdf" name="archivo_pdf" accept="application/pdf" required
                                    @change="selectedFile = $event.target.files[0]?.name || null"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    :class="selectedFile ? 'pointer-events-none opacity-0' : ''">
                                <template x-if="!selectedFile">
                                    <div class="h-full flex flex-col items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl group-hover:bg-white group-hover:border-[#c2d500] transition-all">
                                        <svg class="w-8 h-8 text-gray-400 mb-2 group-hover:text-[#c2d500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs font-bold text-gray-600">Adjuntar archivo</span>
                                        <span class="text-[10px] text-gray-400 mt-1">PDF máx. 20MB</span>
                                    </div>
                                </template>
                                <template x-if="selectedFile">
                                    <div class="h-full flex flex-col items-center justify-center bg-[#f0fdf4] border-2 border-solid border-emerald-200 rounded-2xl">
                                        <svg class="w-8 h-8 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-xs font-bold text-emerald-700" x-text="selectedFile"></span>
                                        <span class="text-[10px] text-emerald-500 mt-1">Archivo listo para subir</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plantilla de Rúbrica de Evaluación -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mt-6">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#c2d500] rounded-xl flex items-center justify-center text-[#07321e]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Rúbrica de Evaluación</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Selecciona la rúbrica que usarán los evaluadores</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-3">

                        {{-- Opción 1: Propuesta de Grado --}}
                        <label class="flex-1 cursor-pointer group min-w-0 relative">
                            <input type="radio" name="plantilla_rubrica" value="propuesta_de_grado" required class="peer absolute opacity-0 w-0 h-0">
                            <div class="flex items-center gap-3 border-2 border-gray-200 rounded-xl px-4 py-3 transition-all
                                peer-checked:border-[#c2d500] peer-checked:bg-[#f9fde6]
                                group-hover:border-[#c2d500]/60 group-hover:bg-gray-50">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 peer-checked:text-[#07321e]">Rúbrica Propuesta de Grado</span>
                                <svg class="w-5 h-5 text-[#c2d500] ml-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </label>

                        {{-- Opción 2: Pasantía --}}
                        <label class="flex-1 cursor-pointer group min-w-0 relative">
                            <input type="radio" name="plantilla_rubrica" value="pasantia" required class="peer absolute opacity-0 w-0 h-0">
                            <div class="flex items-center gap-3 border-2 border-gray-200 rounded-xl px-4 py-3 transition-all
                                peer-checked:border-[#c2d500] peer-checked:bg-[#f9fde6]
                                group-hover:border-[#c2d500]/60 group-hover:bg-gray-50">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 peer-checked:text-[#07321e]">Rúbrica Pasantía</span>
                                <svg class="w-5 h-5 text-[#c2d500] ml-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </label>

                    </div>
                    @error('plantilla_rubrica')
                        <p class="mt-2 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <!-- Director y Subdirector -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mt-6">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#c2d500] rounded-xl flex items-center justify-center text-[#07321e]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Director y Subdirector</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Director -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-[#07321e] border-b border-gray-100 pb-2">Director (Requerido)</h3>
                            <div class="space-y-2">
                                <label for="director_nombre" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Nombre</label>
                                <input type="text" id="director_nombre" name="director[nombre]" required
                                    placeholder="Nombre del Director"
                                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                            </div>
                            <div class="space-y-2">
                                <label for="director_apellido" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Apellido</label>
                                <input type="text" id="director_apellido" name="director[apellido]" required
                                    placeholder="Apellido del Director"
                                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                            </div>
                            <div class="space-y-2">
                                <label for="director_correo" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Correo Electrónico</label>
                                <input type="email" id="director_correo" name="director[correo]" required
                                    placeholder="correo.director@ejemplo.com"
                                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                            </div>
                        </div>

                        <!-- Subdirector -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-[#07321e] border-b border-gray-100 pb-2">Subdirector (Opcional)</h3>
                            <div class="space-y-2">
                                <label for="subdirector_nombre" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Nombre</label>
                                <input type="text" id="subdirector_nombre" name="subdirector[nombre]"
                                    placeholder="Nombre del Subdirector"
                                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                            </div>
                            <div class="space-y-2">
                                <label for="subdirector_apellido" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Apellido</label>
                                <input type="text" id="subdirector_apellido" name="subdirector[apellido]"
                                    placeholder="Apellido del Subdirector"
                                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                            </div>
                            <div class="space-y-2">
                                <label for="subdirector_correo" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Correo Electrónico</label>
                                <input type="email" id="subdirector_correo" name="subdirector[correo]"
                                    placeholder="correo.subdirector@ejemplo.com"
                                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estudiantes -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Estudiantes</h2>
                    </div>
                    <button type="button" @click="agregarEstudiante()"
                        class="px-4 py-2 bg-[#07321e] text-white rounded-lg text-xs font-bold hover:bg-black transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Añadir Estudiante
                    </button>
                </div>

                <div class="p-4 space-y-2 bg-gray-50/50">
                    <template x-for="(estudiante, index) in estudiantes" :key="index">
                        <div class="flex flex-col md:flex-row gap-4 p-4 bg-[#f4f4f4] border border-gray-200 rounded-xl shadow-sm relative group">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-5 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-gray-700 uppercase tracking-widest ml-1">Nombre</label>
                                    <input type="text" :name="'estudiantes['+index+'][nombre]'" x-model="estudiante.nombre" placeholder="Nombre" required
                                        class="w-full px-4 py-2.5 bg-gray border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-gray-700 uppercase tracking-widest ml-1">Apellido</label>
                                    <input type="text" :name="'estudiantes['+index+'][apellido]'" x-model="estudiante.apellido" placeholder="Apellido" required
                                        class="w-full px-4 py-2.5 bg-gray border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-gray-700 uppercase tracking-widest ml-1">Correo Electrónico</label>
                                    <input type="email" :name="'estudiantes['+index+'][correo]'" x-model="estudiante.correo" placeholder="correo@ejemplo.com"
                                        class="w-full px-4 py-2.5 bg-gray border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-gray-700 uppercase tracking-widest ml-1">Facultad</label>
                                    <select x-model="estudiante.id_facultad" @change="estudiante.id_area = ''"
                                        class="w-full px-4 py-2.5 bg-gray border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                                        <option value="">Seleccione Facultad...</option>
                                        @foreach ($facultades as $facultad)
                                        <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-gray-700 uppercase tracking-widest ml-1">Área</label>
                                    <select :name="'estudiantes['+index+'][id_area]'" x-model="estudiante.id_area" required
                                        :disabled="!estudiante.id_facultad"
                                        class="w-full px-4 py-2.5 bg-gray border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700"
                                        :class="!estudiante.id_facultad ? 'opacity-50 cursor-not-allowed' : ''">
                                        <option value="">Seleccione Área...</option>
                                        <template x-for="area in getAreas(estudiante.id_facultad)" :key="area.id_area">
                                            <option :value="area.id_area" x-text="area.nombre_area"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <button type="button" @click="eliminarEstudiante(index)"
                                class="md:mt-6 p-2.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1v3" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex flex-col sm:flex-row-reverse items-center justify-start gap-4 pt-4">
                <button type="submit" :disabled="isSubmitting"
                    class="w-full sm:w-auto px-10 py-3 bg-[#c2d500] text-[#07321e] rounded-xl font-bold text-base shadow-md hover:bg-[#b6c900] transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span>Subir Proyecto</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
                <a href="{{ route('gestor.dashboard') }}"
                    class="w-full sm:w-auto px-10 py-3 bg-white text-gray-500 font-bold text-base rounded-xl border border-gray-200 hover:text-gray-700 hover:bg-gray-100 transition-all text-center shadow-sm">
                    Cancelar
                </a>
            </div>
        </form>

        <div x-show="isSubmitting"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white/80 backdrop-blur-md" x-cloak>
            <div class="relative w-20 h-20 mb-6">
                <div class="absolute inset-0 border-4 border-[#c2d500]/20 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-[#c2d500] rounded-full animate-spin border-t-transparent"></div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Procesando envío...</h3>
            <p class="text-gray-500 text-sm mt-1">Estamos registrando la información.</p>
        </div>

        <!-- Modales -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-8 text-center z-10 transform transition-all border border-gray-100">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm"
                    :class="{{ session()->has('success') ? 'true' : 'false' }} ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
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
        <div x-show="showWarningModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showWarningModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-8 text-center z-10 transform transition-all">
                <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Atención</h2>
                <p class="text-gray-700 text-sm leading-relaxed mb-8 font-semibold" x-text="warningMessage"></p>
                <button @click="showWarningModal = false" class="w-full py-3 rounded-xl bg-[#c2d500] text-[#07321e] font-bold hover:bg-[#b6c900] transition-all">Entendido</button>
            </div>
        </div>
    </div> <!-- Cierre de x-data="trabajoApp()" -->
</div> <!-- Cierre de min-h-screen -->
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('trabajoApp', () => ({
            showModal: @json($hasStatus),
            isSubmitting: false,
            showWarningModal: false,
            warningMessage: '',
            selectedTipo: null,
            selectedFile: null,
            facultades: @json($facultades->map(fn($f) => ['id_facultad' => $f->id_facultad, 'nombre_facultad' => $f->nombre_facultad])->values()->all()),
            areas: @json($facultades->flatMap(fn($f) => $f->areas->map(fn($a) => ['id_area' => $a->id_area, 'nombre_area' => $a->nombre_area, 'id_facultad' => $a->id_facultad]))->values()->all()),
            estudiantes: [{
                nombre: '',
                apellido: '',
                correo: '',
                id_facultad: '',
                id_area: ''
            }],
            getAreas(facultadId) {
                return this.areas.filter(a => a.id_facultad == facultadId);
            },
            agregarEstudiante() {
                if (this.estudiantes.length >= 3) {
                    this.warningMessage = 'Solo se permite un máximo de 3 estudiantes.';
                    this.showWarningModal = true;
                    return;
                }
                this.estudiantes.push({
                    nombre: '',
                    apellido: '',
                    correo: '',
                    id_facultad: '',
                    id_area: ''
                });
            },
            limpiarArchivo() {
                this.selectedFile = null;
                const input = document.getElementById('archivo_pdf');
                if (input) {
                    input.value = '';
                    input.removeAttribute('required');
                    setTimeout(() => input.setAttribute('required', ''), 0);
                }
            },
            eliminarEstudiante(index) {
                if (this.estudiantes.length > 1) {
                    this.estudiantes.splice(index, 1);
                } else {
                    this.warningMessage = 'Debes incluir al menos un estudiante.';
                    this.showWarningModal = true;
                }
            }
        }));
    });
</script>
@endpush