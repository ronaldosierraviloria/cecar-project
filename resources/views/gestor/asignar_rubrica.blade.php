@extends('layouts.baseGestor')

@section('title', 'Asignar Rúbrica | Panel Gestor')

@section('content')
<div class="min-h-screen bg-[#f4f4f4] -m-4 md:-m-6 p-4 md:p-8"
    x-data="{ loaded: false }"
    x-init="setTimeout(() => loaded = true, 50)">
    <div class="max-w-4xl mx-auto">
        <!-- Encabezado -->
        <div x-show="loaded"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('gestor.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-[#07321e] hover:border-indigo-100 transition-all shadow-sm"
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
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Asignar Rúbrica</h1>
                    <p class="text-sm text-gray-500 mt-1">Vincula el formato de evaluación correspondiente.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Detalles del Proyecto -->
            <div x-show="loaded"
                x-transition:enter="transition ease-out duration-500 delay-100"
                x-transition:enter-start="opacity-0 translate-y-6"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-4 border-b border-gray-50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#c2d500] rounded-xl flex items-center justify-center text-[#07321e]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Detalles del Proyecto</h2>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Título</label>
                            <h3 class="text-lg font-bold text-gray-900 leading-snug">{{ $trabajo->titulo }}</h3>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Categoría</label>
                            <span class="inline-flex px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold uppercase tracking-tight">
                                {{ $trabajo->tipo->nombre_tipo ?? 'Sin tipo' }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Integrantes</label>
                        <div class="space-y-2">
                            @foreach($trabajo->estudiante as $estu)
                            <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <div class="w-8 h-8 rounded-lg bg-[#c2d500] flex items-center justify-center text-xs font-bold text-[#07321e]">
                                    {{ substr($estu->nombre, 0, 1) }}
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ $estu->nombre }} {{ $estu->apellido }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asignación de Rúbrica -->
            <div x-show="loaded"
                x-transition:enter="transition ease-out duration-500 delay-200"
                x-transition:enter-start="opacity-0 translate-y-6"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#c2d500] rounded-xl flex items-center justify-center text-[#07321e]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-9-9v4m0 0h4m-4 0l9-9" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Asignar Formato de Evaluación</h2>
                </div>

                <div class="p-4">
                    <form action="{{ route('gestor.rubrica.asignar.store', $trabajo->id_trabajo) }}" method="POST" class="space-y-8">
                        @csrf
                        <div class="space-y-2">
                            <label for="id_rubrica" class="block text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Seleccionar Rúbrica</label>
                            <div class="relative">
                                <select name="id_rubrica" id="id_rubrica" required
                                    class="appearance-none w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all outline-none font-medium text-gray-700">
                                    <option value="">Seleccione una rúbrica...</option>
                                    @foreach($rubricas as $r)
                                    <option value="{{ $r->id_rubrica }}" {{ $trabajo->rubricas->contains('id_rubrica', $r->id_rubrica) ? 'selected' : '' }}>
                                        {{ basename($r->archivo) }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-10 py-4 bg-[#c2d500] text-[#07321e] rounded-xl font-bold text-base shadow-md hover:bg-[#b6c900] transition-colors flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $trabajo->rubricas->isEmpty() ? 'Asignar Rúbrica' : 'Actualizar Rúbrica' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estado Actual -->
            @if(!$trabajo->rubricas->isEmpty())
            <div x-show="loaded"
                x-transition:enter="transition ease-out duration-500 delay-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-emerald-50 rounded-2xl border border-emerald-100 p-8 flex flex-col md:flex-row items-center gap-6">
                <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-emerald-900">Rúbrica Vinculada</h4>
                    <p class="text-sm text-emerald-700 font-medium mt-1">Este proyecto ya tiene una rúbrica activa.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($trabajo->rubricas as $rub)
                        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-emerald-200 text-emerald-700 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-bold text-xs">{{ basename($rub->archivo) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <x-notification type="success" />
    <x-notification type="error" />
</div>
@endsection