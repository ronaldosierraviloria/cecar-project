@extends('layouts.baseGestor')

@section('title', 'Panel del Gestor | Lista de Evaluadores')
@section('meta_description', 'Directorio de evaluadores disponibles en el sistema de grado de CECAR.')

@section('content')
@php
$usuario = Auth::user() ?? (object)['nombre' => 'Gestor', 'apellido' => '', 'rol' => 'Gestor'];
$evaluadores = $evaluadores ?? collect([]);
$facultades = $facultades ?? collect([]);
@endphp

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lista de Evaluadores</h1>
        <p class="text-sm text-gray-500 mt-1">Evaluadores registrados con su facultad, área y cantidad de trabajos asignados.</p>
    </div>
</div>

<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6">
    <form method="GET" action="{{ route('gestor.listaEvaluadores') }}" id="filterForm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
                <input type="text" name="busqueda" placeholder="Buscar evaluador..."
                    value="{{ request('busqueda') }}"
                    class="block w-full pl-9 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:bg-white transition-all shadow-inner">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(request('id_facultad') || request('busqueda'))
                <a href="{{ route('gestor.listaEvaluadores') }}"
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

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-16 text-center">#</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Evaluador</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Facultad</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Área</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trabajos Asignados</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($evaluadores as $evaluador)
                <tr class="hover:bg-gray-50/80 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-xs font-bold text-gray-400">#{{ $loop->iteration }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="relative inline-flex items-center justify-center w-9 h-9 overflow-hidden bg-[#07321e]/10 rounded-full shrink-0">
                                <span class="text-xs font-bold text-[#07321e]">{{ substr($evaluador->usuario->nombre ?? 'E', 0, 1) }}{{ substr($evaluador->usuario->apellido ?? 'V', 0, 1) }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">
                                {{ $evaluador->usuario->nombre ?? 'N/A' }} {{ $evaluador->usuario->apellido ?? '' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-1.5 text-xs text-gray-800 font-medium">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $evaluador->usuario->correo ?? 'N/A' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-700">
                            {{ $evaluador->area->facultad->nombre_facultad ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-tight bg-[#c2d500]/10 text-[#07321e] border border-[#c2d500]/20">
                            {{ $evaluador->area->nombre_area ?? 'Sin Área' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                        $count = $evaluador->trabajos_count ?? 0;
                        $badgeColor = $count == 0 ? 'bg-gray-100 text-gray-600' : ($count >= 2 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');
                        @endphp
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-sm font-bold border {{ $badgeColor }}">
                            {{ $count }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm font-medium text-gray-400 italic">
                        No hay evaluadores registrados en el sistema.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.querySelector('input[name="busqueda"]');
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