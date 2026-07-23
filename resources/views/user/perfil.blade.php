@extends(
$usuario->rol === 'Administrador' ? 'layouts.baseAdmin' :
($usuario->rol === 'Gestor' ? 'layouts.baseGestor' : 'layouts.baseEvaluador')
)

@section('title', 'Mi Perfil')

@section('content')
@php
$roleDescription = match($usuario->rol) {
'Administrador' => 'Como Administrador, tienes control total sobre la plataforma.',
'Gestor' => 'Como Gestor, eres responsable de la gestión de proyectos y rúbricas.',
'Evaluador' => 'Como Evaluador, tu función es la revisión y calificación de trabajos.',
default => 'Usuario del sistema.'
};
$roleTheme = ['bg' => 'from-[#07321e] to-[#0a4d2e]']; // ← CAMBIA AQUÍ EL DEGRADADO


$backRoute = match($usuario->rol) {
'Administrador' => route('admin.dashboard'),
'Gestor' => route('gestor.dashboard'),
'Evaluador' => route('evaluador.dashboard'),
default => 'javascript:history.back()'
};
@endphp

<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-[#07321e] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        {{-- Header del Perfil --}}
        <div class="h-32 bg-gradient-to-r {{ $roleTheme['bg'] }}"></div>

        <div class="px-8 pb-10">
            <div class="relative flex justify-between items-end -mt-12 mb-6">
                {{-- Avatar/Foto --}}
                <div class="p-1 bg-white rounded-2xl shadow-lg">
                    <div class="w-32 h-32 rounded-xl bg-gray-200 flex items-center justify-center overflow-hidden border-2 border-white" style="background-color: var(--cecar-lime);">
                        <span class="text-4xl font-bold text-[#07321e]">
                            {{ substr($usuario->nombre, 0, 1) }}{{ substr($usuario->apellido, 0, 1) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                    <p class="text-gray-500 font-medium flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 rounded-full bg-[#c2d500] animate-pulse"></span>
                        {{ $usuario->rol }} del Sistema
                    </p>

                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#07321e]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Correo Electrónico</p>
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
                            <h3 class="text-base font-bold text-white">Información de Acceso</h3>
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="w-9 h-9 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Fecha de Registro</p>
                                    <p class="text-sm text-gray-700 font-semibold">{{ $usuario->created_at ? \Carbon\Carbon::parse($usuario->created_at)->isoFormat('D [de] MMMM [de] YYYY') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

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
</div>
@endsection