@extends('layouts.baseAdmin')

@section('title', 'Inicio | Panel Admin')
@section('meta_description', 'Vista general del sistema: total de trabajos, evaluadores asignados, estado de revisiones y estadísticas por tipo, estado y mes.')

@section('content')
<x-notification type="success" />

@php
$total = $trabajos->count();
$sinAsignarCount = $trabajos->filter(fn($t) => $t->evaluadores->count() === 0)->count();
$conAsignarCount = $total - $sinAsignarCount;
$facultades = $facultades ?? collect([]);
$areas = $areas ?? collect([]);
@endphp

<div class="mb-8">
    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">KPIs del Sistema</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
        {{-- Total --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-[#07321e]/5 text-[#07321e] shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $total }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Total Trabajos</p>
            </div>
        </div>

        {{-- Con evaluadores --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $conAsignarCount }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Con Evaluadores</p>
            </div>
        </div>

        {{-- Sin evaluadores --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-50 text-rose-500 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $sinAsignarCount }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Sin Evaluadores</p>
            </div>
        </div>

        {{-- En revisión --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 text-amber-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $enRevision }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">En Revisión</p>
            </div>
        </div>

        {{-- Aprobados --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $aprobados }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Aprobados</p>
            </div>
        </div>

        {{-- Pendientes --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-500 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $subidos }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Pendientes</p>
            </div>
        </div>

        {{-- Estudiantes --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalEstudiantes }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Estudiantes</p>
            </div>
        </div>

        {{-- Evaluadores --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-50 text-purple-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2M17 9V7a2 2 0 00-2-2H9a2 2 0 00-2 2v2m6 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalEvaluadores }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Evaluadores</p>
            </div>
        </div>

        {{-- Gestores --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalGestores }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Gestores</p>
            </div>
        </div>

        {{-- Directores --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-teal-50 text-teal-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalDirectores }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Directores</p>
            </div>
        </div>

        {{-- Facultades --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-50 text-orange-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalFacultades }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Facultades</p>
            </div>
        </div>

        {{-- Áreas --}}
        <div class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-pink-50 text-pink-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalAreas }}</p>
                <p class="text-[11px] font-medium text-gray-500 leading-tight">Áreas</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 3 — GRÁFICOS (ApexCharts)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="deferred-section">
    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Estadísticas del Sistema</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        {{-- Gráfico: Estado de Asignación --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-0.5">Asignación de Evaluadores</h3>
                <p class="text-xs text-gray-500 mb-4 font-normal">Relación de trabajos que ya cuentan con jurados asignados frente a los pendientes.</p>
            </div>
            <div id="chartEstado" class="apexchart-container mt-auto" style="min-height: 260px;"></div>
        </div>

        {{-- Gráfico: Trabajos por Tipo --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-0.5">Trabajos por Tipo</h3>
                <p class="text-xs text-gray-500 mb-4 font-normal">Distribución de los trabajos de grado según su modalidad de grado registrada.</p>
            </div>
            <div id="chartTipo" class="apexchart-container mt-auto" style="min-height: 260px;"></div>
        </div>

        {{-- Gráfico: Estado del Trabajo --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-0.5">Estado de los Trabajos</h3>
                <p class="text-xs text-gray-500 mb-4 font-normal">Proporción de trabajos según su etapa actual en el flujo de revisión y aprobación.</p>
            </div>
            <div id="chartStatus" class="apexchart-container mt-auto" style="min-height: 260px;"></div>
        </div>

        {{-- Gráfico: Trabajos por Mes (ocupa ancho completo en xl) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 lg:col-span-2 xl:col-span-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-0.5">Histórico de Carga</h3>
                <p class="text-xs text-gray-500 mb-4 font-normal">Evolución mensual de trabajos de grado subidos al sistema durante el año académico.</p>
            </div>
            <div id="chartMes" class="apexchart-container" style="min-height: 260px;"></div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    (function() {
        var labelsTipo = @json($chartTipoLabels);
        var dataTipo = @json($chartTipoData);
        var labelsMes = @json($chartMesesLabels);
        var dataMes = @json($chartMesesData);
        var labelsEstado = @json($chartEstadoLabels);
        var dataEstado = @json($chartEstadoData);
        var labelsStatus = @json($chartStatusLabels);
        var dataStatus = @json($chartStatusData);

        function initCharts() {
            if (typeof ApexCharts === 'undefined') {
                return setTimeout(initCharts, 100);
            }

            // Gráfico: Asignación de Evaluadores
            new ApexCharts(document.querySelector('#chartEstado'), {
                chart: { 
                    type: 'bar', 
                    height: 260, 
                    toolbar: { show: false },
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{ name: 'Trabajos', data: dataEstado }],
                xaxis: { 
                    categories: labelsEstado, 
                    labels: { style: { colors: '#4b5563', fontSize: '11px', fontWeight: 500 } } 
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9ca3af' },
                        formatter: function(val) { return Math.round(val); }
                    },
                    tickAmount: Math.max(...dataEstado) < 4 ? Math.max(...dataEstado) : 4
                },
                colors: ['#10b981', '#ef4444'],
                plotOptions: { 
                    bar: { 
                        borderRadius: 6, 
                        columnWidth: '45%', 
                        distributed: true,
                        dataLabels: { position: 'top' }
                    } 
                },
                dataLabels: { 
                    enabled: true, 
                    offsetY: -20,
                    style: { fontSize: '12px', colors: ['#374151'], fontWeight: 'bold' } 
                },
                legend: { show: false },
                grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
                responsive: [{ breakpoint: 480, options: { chart: { height: 240 } } }]
            }).render();

            // Gráfico: Trabajos por Tipo
            new ApexCharts(document.querySelector('#chartTipo'), {
                chart: { 
                    type: 'donut', 
                    height: 260,
                    fontFamily: 'Poppins, sans-serif'
                },
                labels: labelsTipo,
                series: dataTipo,
                colors: ['#07321e', '#c2d500', '#f59e0b', '#3b82f6', '#8b5cf6'],
                legend: { 
                    show: true,
                    position: 'bottom', 
                    fontSize: '11px',
                    labels: { colors: '#374151' },
                    itemMargin: { horizontal: 8, vertical: 4 }
                },
                dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(0) + '%' } },
                plotOptions: { 
                    pie: { 
                        donut: { 
                            size: '65%', 
                            labels: { 
                                show: true, 
                                total: { 
                                    show: true, 
                                    label: 'Total', 
                                    color: '#6b7280',
                                    fontSize: '13px',
                                    fontWeight: 500,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 'bold',
                                    color: '#111827',
                                    offsetY: 4
                                }
                            } 
                        } 
                    } 
                },
                responsive: [{ breakpoint: 480, options: { chart: { height: 240 } } }]
            }).render();

            // Gráfico: Estado del Trabajo
            new ApexCharts(document.querySelector('#chartStatus'), {
                chart: { 
                    type: 'donut', 
                    height: 260,
                    fontFamily: 'Poppins, sans-serif'
                },
                labels: labelsStatus,
                series: dataStatus,
                colors: ['#9ca3af', '#f59e0b', '#10b981'],
                legend: { 
                    show: true,
                    position: 'bottom', 
                    fontSize: '11px',
                    labels: { colors: '#374151' },
                    itemMargin: { horizontal: 8, vertical: 4 }
                },
                dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(0) + '%' } },
                plotOptions: { 
                    pie: { 
                        donut: { 
                            size: '65%', 
                            labels: { 
                                show: true, 
                                total: { 
                                    show: true, 
                                    label: 'Total', 
                                    color: '#6b7280',
                                    fontSize: '13px',
                                    fontWeight: 500,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 'bold',
                                    color: '#111827',
                                    offsetY: 4
                                }
                            } 
                        } 
                    } 
                },
                responsive: [{ breakpoint: 480, options: { chart: { height: 240 } } }]
            }).render();

            // Gráfico: Histórico de Carga
            new ApexCharts(document.querySelector('#chartMes'), {
                chart: { 
                    type: 'area', 
                    height: 260, 
                    toolbar: { show: false },
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{ name: 'Trabajos Subidos', data: dataMes }],
                xaxis: { 
                    categories: labelsMes, 
                    labels: { rotate: -45, style: { colors: '#4b5563', fontSize: '10px' } } 
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9ca3af' },
                        formatter: function(val) { return Math.round(val); }
                    },
                    tickAmount: Math.max(...dataMes) < 4 ? Math.max(...dataMes) : 4
                },
                colors: ['#07321e'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: function(val) { return val + ' trabajos' } } },
                grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
                responsive: [{ breakpoint: 480, options: { chart: { height: 240 } } }]
            }).render();
        }

        document.addEventListener('DOMContentLoaded', initCharts);
    })();
</script>
@endpush
@endsection