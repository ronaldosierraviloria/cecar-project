<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario — Sistema de Grado CECAR</title>
    <meta name="description" content="Crea tu cuenta en el Sistema de Gestión de Trabajos de Grado de CECAR.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#07321e">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="font" type="font/woff2"
          href="https://fonts.gstatic.com/s/poppins/v23/pxiEyp8kv8JHgFVrJJfecg.woff2"
          crossorigin fetchpriority="high">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4 py-8">

    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2">

            {{-- ── Columna Izquierda: Branding + Info ── --}}
            <div class="bg-[#07321e] p-8 md:p-10 flex flex-col justify-between">
                {{-- Branding --}}
                <div>
                    <div class="flex items-center gap-3 mb-8">
                        <img src="{{ asset('images/icon.webp') }}" alt="CECAR"
                             class="h-12 w-12 object-contain bg-white rounded-lg p-1">
                        <div>
                            <h1 class="text-lg font-semibold tracking-wide text-[#c2d500]">
                                Sistema de Gestión de Trabajos de Grado
                            </h1>
                            <p class="text-xs text-white/80 -mt-0.5">Corporación Universitaria del Caribe CECAR</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-[#c2d500]">Crea tu cuenta</h2>
                        <p class="text-sm text-white/90 mt-1">Completa el formulario para registrarte en el sistema.</p>
                    </div>

                    {{-- Pasos visuales --}}
                    <div class="space-y-4 mb-8">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-[#c2d500] flex items-center justify-center text-[#07321e] font-bold text-xs">1</div>
                            <div>
                                <p class="text-sm font-semibold text-white">Datos personales</p>
                                <p class="text-xs text-white/70">Nombre, apellido y correo institucional</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-[#c2d500] flex items-center justify-center text-[#07321e] font-bold text-xs">2</div>
                            <div>
                                <p class="text-sm font-semibold text-white">Rol en el sistema</p>
                                <p class="text-xs text-white/70">Gestor, Evaluador o Administrador</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-[#c2d500] flex items-center justify-center text-[#07321e] font-bold text-xs">3</div>
                            <div>
                                <p class="text-sm font-semibold text-white">Credenciales</p>
                                <p class="text-xs text-white/70">Define tu contraseña segura</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Imagen Campus --}}
                <div>
                    <div class="relative overflow-hidden rounded-xl border border-white/20">
                        <img src="{{ asset('images/campus.webp') }}" alt="Campus CECAR"
                             class="w-full h-40 object-cover opacity-70">
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#07321e]/60 via-transparent to-[#c2d500]/10"></div>
                    </div>
                    <p class="mt-4 text-center text-xs text-white/60">
                        © {{ date('Y') }} CECAR · Sistema de Gestión de Grado
                    </p>
                </div>
            </div>

            {{-- ── Columna Derecha: Formulario ── --}}
            <div class="p-8 md:p-10 overflow-y-auto"
                 x-data="{
                     selectedRole: '{{ old('rol') }}',
                     selectedFacultad: '{{ old('id_facultad') }}',
                     showPassword: false,
                     showConfirmPassword: false,
                     loading: false,
                     areas: {{ json_encode($areas->map(fn($a) => ['id' => $a->id_area, 'nombre' => $a->nombre_area, 'id_facultad' => $a->id_facultad])->values()) }},
                     get filteredAreas() {
                         if (!this.selectedFacultad) return [];
                         return this.areas.filter(a => Number(a.id_facultad) === Number(this.selectedFacultad));
                     }
                 }">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Registro de Usuario</h2>
                    <a href="{{ route('login') }}"
                       class="text-xs text-[#07321e] font-semibold hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver al inicio de sesión
                    </a>
                </div>

                {{-- Errores generales --}}
                @if ($errors->any())
                <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs text-red-600">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('success'))
                <div class="mb-5 p-3 bg-green-50 border border-green-200 rounded-xl">
                    <p class="text-xs text-green-700 font-medium">{{ session('success') }}</p>
                </div>
                @endif

                <form action="{{ route('register.post') }}" method="POST" class="space-y-5" @submit="loading = true">
                    @csrf

                    {{-- Nombre + Apellido --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}"
                                   placeholder="Ej: Juan"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent placeholder-gray-400 transition-all @error('nombre') border-red-400 bg-red-50 @enderror"
                                   required>
                            @error('nombre')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="apellido" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Apellido <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}"
                                   placeholder="Ej: Pérez"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent placeholder-gray-400 transition-all @error('apellido') border-red-400 bg-red-50 @enderror"
                                   required>
                            @error('apellido')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Correo --}}
                    <div>
                        <label for="correo" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" id="correo" name="correo" value="{{ old('correo') }}"
                                   placeholder="ejemplo@cecar.edu.co"
                                   class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent placeholder-gray-400 transition-all @error('correo') border-red-400 bg-red-50 @enderror"
                                   required>
                        </div>
                        @error('correo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rol --}}
                    <div>
                        <label for="rol" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Rol en el Sistema <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <select id="rol" name="rol" x-model="selectedRole"
                                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent appearance-none transition-all @error('rol') border-red-400 bg-red-50 @enderror"
                                    required>
                                <option value="" disabled :selected="!selectedRole">Seleccione un rol...</option>
                                <option value="Administrador" :selected="selectedRole === 'Administrador'">Administrador</option>
                                <option value="Gestor" :selected="selectedRole === 'Gestor'">Gestor</option>
                                <option value="Evaluador" :selected="selectedRole === 'Evaluador'">Evaluador</option>
                            </select>
                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </div>
                        @error('rol')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Facultad para Evaluador (filtra áreas) --}}
                    <div x-show="selectedRole === 'Evaluador'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Facultad
                        </label>
                        <select x-model="selectedFacultad"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent appearance-none transition-all">
                            <option value="">Seleccione una facultad...</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Área para Evaluador --}}
                    <div x-show="selectedRole === 'Evaluador' && selectedFacultad"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <label for="id_area" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Área de Especialidad <span class="text-red-500">*</span>
                        </label>
                        <select id="id_area" name="id_area"
                                :required="selectedRole === 'Evaluador'"
                                :disabled="!selectedFacultad"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent appearance-none transition-all @error('id_area') border-red-400 @enderror">
                            <option value="">Seleccione un área...</option>
                            <template x-for="area in filteredAreas" :key="area.id">
                                <option :value="area.id" x-text="area.nombre"></option>
                            </template>
                        </select>
                        @error('id_area')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseñas --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contraseña" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                       id="contraseña" name="contraseña"
                                       placeholder="Mínimo 8 caracteres"
                                       class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent placeholder-gray-400 transition-all @error('contraseña') border-red-400 bg-red-50 @enderror"
                                       required minlength="8">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-700">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.314-3.952M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.023 10.023 0 01-4.132 5.411M15 12a3 3 0 00-3-3m0 0a2.996 2.996 0 00-2.121.879M12 9l10 10M3 3l9 9"/>
                                    </svg>
                                </button>
                            </div>
                            @error('contraseña')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contraseña_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Confirmar Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showConfirmPassword ? 'text' : 'password'"
                                       id="contraseña_confirmation" name="contraseña_confirmation"
                                       placeholder="Repite tu contraseña"
                                       class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c2d500] focus:border-transparent placeholder-gray-400 transition-all"
                                       required minlength="8">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-700">
                                    <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showConfirmPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Botón de envío --}}
                    <button type="submit"
                            :disabled="loading"
                            class="w-full rounded-xl bg-[#07321e] text-white font-semibold px-4 py-3
                                   shadow-sm hover:bg-[#0a4429] focus:outline-none focus:ring-2 focus:ring-[#c2d500]
                                   focus:ring-offset-2 transition disabled:opacity-70 disabled:cursor-not-allowed mt-2">
                        <span x-show="!loading" class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Registrar Usuario
                        </span>
                        <span x-show="loading" x-cloak class="inline-flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>

                    <p class="text-center text-xs text-gray-500">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="text-[#07321e] font-semibold hover:underline">
                            Inicia sesión aquí
                        </a>
                    </p>
                </form>
            </div>

        </div>
    </div>

</body>
</html>
