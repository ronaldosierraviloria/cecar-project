{{-- Formato FO-TG-006: Evaluación de la Propuesta de Trabajo de Grado --}}

{{-- CABECERA OFICIAL CECAR FO-TG-006 --}}
<div class="bg-white border-2 border-black mb-6 p-4">
    <div class="grid grid-cols-3 items-center text-center divide-x-2 divide-black">
        <div class="p-2 flex justify-center items-center">
            <img src="{{ asset('images/logocecar.webp') }}" alt="Logo CECAR" class="h-10 w-auto">
        </div>
        <div class="p-2 flex flex-col justify-center">
            <span class="text-xs font-bold uppercase">Evaluación de la</span>
            <span class="text-xs font-bold uppercase">Propuesta de Trabajo Grado</span>
        </div>
        <div class="p-2 flex flex-col justify-center text-xs">
            <span class="font-bold">FCBIA</span>
            <span>FO-TG-006</span>
        </div>
    </div>
</div>

{{-- INFORMACIÓN BÁSICA DEL ANTEPROYECTO --}}
<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 space-y-3">
    <div>
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Título de la Propuesta:</span>
        <p class="text-sm font-bold text-gray-800 border-b border-gray-300 pb-1 mt-1">{{ $trabajo->titulo }}</p>
    </div>
    <div>
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Presentado por:</span>
        <p class="text-sm font-medium text-gray-800 border-b border-gray-300 pb-1 mt-1">
            @if($trabajo->estudiante)
                @foreach($trabajo->estudiante as $est)
                    {{ $est->nombre }} {{ $est->apellido }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @else
                <span class="text-gray-400 italic">No asignado</span>
            @endif
        </p>
    </div>
    <div>
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Director(es):</span>
        <p class="text-sm font-medium text-gray-800 border-b border-gray-300 pb-1 mt-1">
            @if($trabajo->directores)
                @forelse($trabajo->directores as $dir)
                    {{ $dir->nombre }} {{ $dir->apellido }}{{ !$loop->last ? ', ' : '' }}
                @empty
                    <span class="text-gray-400 italic">No asignado</span>
                @endforelse
            @else
                <span class="text-gray-400 italic">No asignado</span>
            @endif
        </p>
    </div>
</div>

{{-- EVALUACIÓN CUANTITATIVA --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
    <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-3 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Evaluación Cuantitativa</h3>
        <span class="text-[10px] font-bold text-[#07321e] bg-[#c2d500]/20 px-2 py-0.5 rounded">Rango: 0.0 - 5.0</span>
    </div>
    <div class="divide-y divide-gray-100">
        @php
            $criteriosPropuesta = [
                1 => ['desc' => 'El título está acorde con el problema a resolver.', 'pct' => 5],
                2 => ['desc' => 'La formulación y justificación del problema responden al trabajo planteado.', 'pct' => 20],
                3 => ['desc' => 'El cumplimiento del objetivo general garantiza la solución al problema planteado.', 'pct' => 20],
                4 => ['desc' => 'El cumplimiento de los objetivos específicos asegura el logro del objetivo general y están acordes para un trabajo de pregrado.', 'pct' => 20],
                5 => ['desc' => 'El marco referencial presentado da respuesta al problema planteado.', 'pct' => 10],
                6 => ['desc' => 'La metodología planteada reporta antecedentes claves relacionados con el objeto de estudio y con la estrategia propuesta, permitiendo así el cumplimiento de los objetivos.', 'pct' => 20],
                7 => ['desc' => 'El tiempo estimado para el desarrollo de las actividades (cronograma), es conforme con el alcance planteado y las referencias bibliográficas son actualizadas y se relacionan con el tema de la investigación.', 'pct' => 5],
            ];
        @endphp

        @foreach($criteriosPropuesta as $idx => $crit)
        <div class="p-4 space-y-3">
            <div class="flex items-start justify-between gap-4">
                <div class="flex gap-2">
                    <span class="font-bold text-[#07321e] text-xs mt-0.5">{{ $idx }}.</span>
                    <p class="text-xs text-gray-700 font-medium">{{ $crit['desc'] }}</p>
                </div>
                <span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded shrink-0">
                    Peso: {{ $crit['pct'] }}%
                </span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                {{-- Nota campo --}}
                <div class="sm:col-span-1">
                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Calificación (0 - 5)</label>
                    <input type="number" id="nota_propuesta_{{ $idx }}" min="0" max="5" step="0.1" placeholder="0.0"
                        oninput="calcularNotaPropuesta()"
                        value="{{ (collect($evaluacionPrevia?->criterios ?? [])->firstWhere('id', $idx))['calificacion'] ?? '' }}"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-center font-bold focus:ring-2 focus:ring-[#c2d500] outline-none">
                </div>
                {{-- Comentario por criterio --}}
                <div class="sm:col-span-3">
                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Observaciones Específicas</label>
                    <input type="text" id="obs_propuesta_{{ $idx }}" placeholder="Comentario opcional para este criterio..."
                        value="{{ (collect($evaluacionPrevia?->criterios ?? [])->firstWhere('id', $idx))['comentario'] ?? '' }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-[#c2d500] outline-none">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- RESULTADO AUTOMÁTICO DE APROBACIÓN --}}
<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">El evaluador aprueba el anteproyecto (Mínimo 3.0)</h4>
    <div class="space-y-2">
        <div id="propuesta_aceptada" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 bg-gray-50">
            <span class="text-xs font-semibold text-gray-600">Aceptada (4.2 - 5.0)</span>
            <span class="check-indicator text-emerald-600 font-bold hidden">✔ Seleccionada</span>
        </div>
        <div id="propuesta_mejoras" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 bg-gray-50">
            <span class="text-xs font-semibold text-gray-600">Aceptada con modificaciones mayores (3.0 - 4.19)</span>
            <span class="check-indicator text-amber-600 font-bold hidden">✔ Seleccionada</span>
        </div>
        <div id="propuesta_rechazada" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 bg-gray-50">
            <span class="text-xs font-semibold text-gray-600">Rechazada (&lt; 3.0)</span>
            <span class="check-indicator text-red-600 font-bold hidden">✔ Seleccionada</span>
        </div>
    </div>
</div>

{{-- COMENTARIOS ADICIONALES --}}
<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
    <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">Comentarios y Observaciones Adicionales</label>
    <textarea id="observacion_final" rows="4" 
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-700 focus:ring-2 focus:ring-[#c2d500] focus:border-[#c2d500] outline-none transition-all resize-none"
        placeholder="Ingrese comentarios adicionales o sugerencias estructurales sobre la propuesta...">{{ $evaluacionPrevia->observaciones_globales ?? '' }}</textarea>
</div>

<script>
    // Configuración de pesos para calcular la nota automáticamente
    const pesos = {
        1: 0.05, // 5%
        2: 0.20, // 20%
        3: 0.20, // 20%
        4: 0.20, // 20%
        5: 0.10, // 10%
        6: 0.20, // 20%
        7: 0.05  // 5%
    };

    function calcularNotaPropuesta() {
        let notaPonderada = 0;
        let todosCalificados = true;

        for (let i = 1; i <= 7; i++) {
            const inputVal = document.getElementById('nota_propuesta_' + i).value;
            const nota = parseFloat(inputVal);

            if (isNaN(nota) || inputVal === '') {
                todosCalificados = false;
                continue;
            }

            // Validar rango
            let validNota = Math.max(0, Math.min(5, nota));
            notaPonderada += (validNota * pesos[i]);
        }

        // Actualizar UI del panel superior si existe la barra
        const notaFinalInput = document.getElementById('nota-final');
        if (notaFinalInput) {
            notaFinalInput.value = notaPonderada.toFixed(2);
            // Ejecutar la animación y actualización nativa del contenedor padre si existe
            if (typeof actualizarEstadoAuto === 'function') {
                actualizarEstadoAuto(notaPonderada.toFixed(2));
            }
        }

        // Resaltar resultado según los rangos indicados
        // Aceptada (4.2 - 5.0)
        // Aceptada con modificaciones mayores (3.0 - 4.19)
        // Rechazada (<3.0)
        const divAceptada = document.getElementById('propuesta_aceptada');
        const divMejoras = document.getElementById('propuesta_mejoras');
        const divRechazada = document.getElementById('propuesta_rechazada');

        // Reset
        [divAceptada, divMejoras, divRechazada].forEach(d => {
            d.className = "flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 font-semibold";
            d.querySelector('.check-indicator').classList.add('hidden');
        });

        if (todosCalificados || notaPonderada > 0) {
            if (notaPonderada >= 4.2) {
                divAceptada.className = "flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-white text-gray-800 font-bold";
                divAceptada.querySelector('.check-indicator').classList.remove('hidden');
            } else if (notaPonderada >= 3.0) {
                divMejoras.className = "flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-white text-gray-800 font-bold";
                divMejoras.querySelector('.check-indicator').classList.remove('hidden');
            } else {
                divRechazada.className = "flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-white text-gray-800 font-bold";
                divRechazada.querySelector('.check-indicator').classList.remove('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(calcularNotaPropuesta, 500);
    });
</script>
