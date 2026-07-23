<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Sistema de Grado</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.webp') }}">

</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <!-- Card contenedora -->
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2">
            <!-- Columna Izquierda: Login -->
            <div class="bg-[#07321e] p-8 md:p-10 flex flex-col justify-center">
                <!-- Branding -->
                <div class="flex items-center gap-3 mb-8 ">
                    <img src="{{ asset('images/icon.webp') }}" alt="CECAR" class="h-12 w-12 object-contain bg-white rounded-lg p-1">
                    <div>
                        <h1 class="text-lg font-semibold tracking-wide text-[#c2d500]">Sistema de Gestión de Trabajos de Grado</h1>
                        <p class="text-xs text-white/80 -mt-0.5">Corporación Universitaria del Caribe CECAR</p>
                    </div>
                </div>

                <!-- Título -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-[#c2d500]">Bienvenido!</h2>
                    <p class="text-sm text-white/90 mt-1">Inicia sesión con tus credenciales</p>
                </div>

                <x-notification type="error" />
                <x-notification type="success" />

                <!-- Formulario -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4"
                    x-data="{ loading: false }"
                    @submit="loading = true">
                    @csrf

                    <!-- Correo -->
                    <div>
                        <label for="correo" class="block text-sm font-medium text-white mb-1">Correo institucional</label>
                        <div class="relative">
                            <!-- icono -->
                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12H8m0 0l-4 4m4-4l-4-4m16 8V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2z" />
                                </svg>
                            </span>
                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                value="{{ old('correo') }}"
                                required
                                placeholder="ejemplo@cecar.edu.co"
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/60
                                       focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent">
                        </div>
                        @error('correo')
                        <p class="mt-1 text-xs text-[#c2d500]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contraseña -->
                    <div x-data="{ show: false }">
                        <label for="contraseña" class="block text-sm font-medium text-white mb-1">Contraseña</label>
                        <div class="relative">
                            <!-- icono candado -->
                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 11c-1.105 0-2 .895-2 2v3h4v-3c0-1.105-.895-2-2-2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 11V7a5 5 0 10-10 0v4m-1 0h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z" />
                                </svg>
                            </span>

                            <input
                                :type="show ? 'text' : 'password'"
                                id="contraseña"
                                name="contraseña"
                                required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/60
                                       focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent">
                            <!-- toggle -->
                            <button type="button" @click="show = !show" class="absolute right-3 inset-y-0 flex items-center text-white/80 hover:text-white">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.314-3.952M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.023 10.023 0 01-4.132 5.411M15 12a3 3 0 00-3-3m0 0a2.996 2.996 0 00-2.121.879M12 9l10 10M3 3l9 9" />
                                </svg>
                            </button>
                        </div>
                        @error('contraseña')
                        <p class="mt-1 text-xs text-[#c2d500]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botón -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-lg bg-[#c2d500] text-[#07321e] font-semibold px-4 py-2.5
                               shadow-sm hover:bg-[#a9bd00] focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:ring-offset-2 focus:ring-offset-[#07321e] transition
                               disabled:opacity-70 disabled:cursor-not-allowed">
                        <span x-show="!loading">Iniciar Sesión</span>
                        <span x-show="loading" x-cloak class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Iniciando sesión...
                        </span>
                    </button>

                    <!-- Opcional: Recordarme -->
                    <label class="flex items-center gap-2 text-sm text-white/90">
                        <input type="checkbox" class="rounded border-white/30 bg-white/10 text-[#c2d500] focus:ring-[#c2d500]">
                        Recordarme en este equipo
                    </label>
                </form>

                <p class="mt-4 text-center text-xs text-white/70">
                    ¿No tienes una cuenta? 
                    <a href="{{ route('register') }}" class="text-[#c2d500] font-semibold hover:underline">
                        Regístrate aquí
                    </a>
                </p>

                <p class="mt-6 text-center text-xs text-white/70">
                    © {{ date('Y') }} CECAR · Sistema de Gestión de Grado
                </p>
            </div>

            <!-- Columna Derecha: Imagen dentro de la tarjeta -->
            <div class="bg-white p-8 md:p-10 flex items-center">
                <div class="w-full">
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 shadow-lg">
                        <img
                            src="{{ asset('images/campus.webp') }}"
                            alt="Campus CECAR"
                            class="w-full h-64 object-cover">
                        <!-- Overlay institucional suave -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#07321e]/20 via-transparent to-[#c2d500]/10"></div>
                    </div>

                    <div class="mt-5">
                        <h3 class="text-lg font-semibold text-gray-900">Excelencia Académica e Innovación</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Plataforma para la gestión, asignación y evaluación de trabajos de grado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Alpine es cargado via Vite (app.js) --}}
</body>

</html>