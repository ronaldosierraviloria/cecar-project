@extends('layouts.baseGestor')

@section('title', 'Subir Informe Final | Panel Gestor')
@section('meta_description', 'Carga del informe final de un trabajo de grado para su revision y aprobacion.')

@section('content')
<x-notification type="success" />
<x-notification type="error" />
<div class="min-h-screen bg-[#f4f4f4] -m-4 md:-m-6 p-4 md:p-8">
    <div x-data="{ isSubmitting: false, fileSelected: false, fileName: '' }" class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('gestor.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-[#07321e] hover:bg-gray-100 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">Subir Informe Final</h1>
                <p class="text-sm text-gray-500 mt-1">Convierte la propuesta en un Trabajo de Grado subiendo el informe final.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-8 py-6 border-b border-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#c2d500] rounded-xl flex items-center justify-center text-[#07321e]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Información del proyecto</h2>
                        <p class="text-sm text-gray-500">Propuesta evaluada — todos los jurados finalizaron su revisión.</p>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 space-y-4">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-[11px] font-bold uppercase tracking-tight tag-trabajo">
                        {{ $trabajo->tipo->nombre_tipo ?? 'Propuesta de Grado' }}
                    </span>
                    <span class="text-sm text-gray-400">→</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-[11px] font-bold uppercase tracking-tight bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Trabajo de Grado
                    </span>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $trabajo->titulo }}</h3>
                </div>

                @if($trabajo->estudiante->isNotEmpty())
                <div>
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Estudiantes</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($trabajo->estudiante as $est)
                        <span class="inline-flex items-center gap-1.5 bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1.5 text-[12px] font-medium text-gray-700">
                            {{ $est->nombre }} {{ $est->apellido }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#07321e] rounded-xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Subir documento final</h2>
                        <p class="text-sm text-gray-500">Selecciona el archivo PDF del informe final del trabajo de grado.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('gestor.trabajo.informe-final.store', $trabajo->id_trabajo) }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true" class="px-8 py-6">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Archivo PDF <span class="text-red-500">*</span></label>
                    <div class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all cursor-pointer"
                        :class="fileSelected ? 'border-[#07321e] bg-[#07321e]/5' : 'border-gray-200 hover:border-[#07321e]/30'"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent="$el.classList.add('border-[#07321e]', 'bg-[#07321e]/5')"
                        @dragleave.prevent="if (!fileSelected) $el.classList.remove('border-[#07321e]', 'bg-[#07321e]/5')"
                        @drop.prevent="$el.classList.remove('border-[#07321e]', 'bg-[#07321e]/5'); $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))">
                        <template x-if="!fileSelected">
                            <div>
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="text-sm text-gray-500 mb-1">
                                    <span class="font-bold text-[#07321e]">Haz clic</span> o arrastra el archivo aquí
                                </p>
                                <p class="text-xs text-gray-400">Solo PDF — Máximo 50 MB</p>
                            </div>
                        </template>
                        <template x-if="fileSelected">
                            <div>
                                <div class="w-14 h-14 mx-auto bg-[#07321e] rounded-xl flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-[#07321e] mb-1" x-text="fileName"></p>
                                <p class="text-xs text-gray-400">Haz clic para cambiar el archivo</p>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="archivo_pdf" accept=".pdf,application/pdf" required
                        x-ref="fileInput" class="hidden"
                        @change="fileSelected = $el.files.length > 0; fileName = $el.files[0]?.name || ''">
                    @error('archivo_pdf')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-bold mb-1">¿Qué sucederá?</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>El trabajo pasará de ser una <strong>Propuesta de Grado</strong> a un <strong>Trabajo de Grado</strong>.</li>
                                <li>Los evaluadores serán <strong>reasignados automáticamente</strong> para evaluar el trabajo de grado.</li>
                                <li>Los evaluadores recibirán una <strong>notificación</strong> informándoles del cambio.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('gestor.dashboard') }}" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-all">
                        Cancelar
                    </a>
                    <button type="submit" :disabled="isSubmitting"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[#07321e] hover:bg-[#1a4d2e] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        x-text="isSubmitting ? 'Subiendo...' : 'Subir Informe Final'">
                        Subir Informe Final
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
