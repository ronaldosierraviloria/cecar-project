{{-- Formato FO-TG-0010: Evaluación de Informe Final de Trabajo de Grado --}}

{{-- CABECERA OFICIAL CECAR FO-TG-0010 --}}
<div class="bg-white border-2 border-black mb-6 p-4">
    <div class="grid grid-cols-3 items-center text-center divide-x-2 divide-black">
        <div class="p-2 flex justify-center items-center">
            <img src="{{ asset('images/logocecar.webp') }}" alt="Logo CECAR" class="h-10 w-auto">
        </div>
        <div class="p-2 flex flex-col justify-center">
            <span class="text-xs font-bold uppercase">Formato de Evaluación de</span>
            <span class="text-xs font-bold uppercase">Informe Final Trabajo Grado</span>
        </div>
        <div class="p-2 flex flex-col justify-center text-xs">
            <span class="font-bold">FCBIA</span>
            <span>FO-TG-0010</span>
        </div>
    </div>
</div>

{{-- INFORMACIÓN BÁSICA DEL PROYECTO --}}
<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 space-y-3">
    <div>
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Título del Informe Final:</span>
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

{{-- CRITERIOS DE LA RÚBRICA CON PLACEHOLDERS Y VALORACIÓN ÚNICA --}}
<div class="space-y-6">
    @php
        $preguntas = [
            1 => 'El título. ¿Está el título acorde con las expectativas planteadas en la investigación?',
            2 => 'Introducción (incluye planteamiento del problema y justificación). ¿Estuvo bien definido el problema investigado?, ¿fue clara su justificación desde el punto de vista académico, científico, tecnológico, social o económico?',
            3 => 'Marco referencial. ¿La revisión bibliográfica es apropiada, completa y coherente?, ¿El marco referencial está actualizado y acertado con respecto al problema que se estudió?, ¿Es consistente el uso del formato de citas y referencias bibliográficas?',
            4 => 'Cumplimiento de objetivos. ¿Se evidencia el cumplimiento de los objetivos en los resultados obtenidos?',
            5 => 'Evaluación de la metodología. ¿sigue métodos reconocidos internacionalmente?, ¿está la metodología claramente descrita?, ¿esta relacionada con los objetivos planteados?, ¿está validada y respaldada con referencias bibliográficas?, ¿el tratamiento estadístico de los datos fue claro y adecuado metodológicamente (si aplica)?',
            6 => 'Novedad y pertinencia de los resultados. ¿Los resultados obtenidos son claros y consistentes con los objetivos del proyecto?',
            7 => 'Conclusiones. ¿Las conclusiones están acordes a los resultados obtenidos?'
        ];
    @endphp

    @foreach($preguntas as $idx => $pregunta)
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm question-card">
        <div class="flex gap-2">
            <span class="font-bold text-[#07321e] text-sm">{{ $idx }}.</span>
            <p class="text-sm font-semibold text-gray-800">{{ $pregunta }}</p>
        </div>
        
        {{-- Comentario / Placeholder de respuesta --}}
        <div class="mt-4">
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Comentarios / Observaciones del Criterio</label>
            <textarea id="observacion_{{ $idx }}" rows="3" 
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-700 focus:ring-2 focus:ring-[#c2d500] focus:border-[#c2d500] outline-none transition-all resize-none"
                placeholder="Escribe aquí tu análisis detallado para este punto...">{{ (collect($evaluacionPrevia?->criterios ?? [])->firstWhere('id', $idx))['comentario'] ?? '' }}</textarea>
        </div>

    </div>
    @endforeach

    {{-- 8. COMENTARIOS Y SUGERENCIAS DEL EVALUADOR --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm question-card">
        <div class="flex gap-2 mb-2">
            <span class="font-bold text-[#07321e] text-sm">8.</span>
            <p class="text-sm font-semibold text-gray-800">Comentarios y sugerencias del evaluador</p>
        </div>
        <textarea id="observacion_final" rows="4" 
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-700 focus:ring-2 focus:ring-[#c2d500] focus:border-[#c2d500] outline-none transition-all resize-none"
            placeholder="Ingrese las sugerencias globales o puntos a corregir...">{{ $evaluacionPrevia->observaciones_globales ?? '' }}</textarea>
    </div>

    {{-- 9. RESULTADO DE LA EVALUACIÓN (OPCIÓN ÚNICA) --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm question-card">
        <div class="flex gap-2 mb-4">
            <span class="font-bold text-[#07321e] text-sm">9.</span>
            <p class="text-sm font-semibold text-gray-800">Resultado de la evaluación (Selección Única)</p>
        </div>
        <div class="space-y-3">
            @php
                $resPrevio = $evaluacionPrevia->resultado ?? '';
            @endphp
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="radio" name="resultado_evaluacion" value="sustentacion_con_correcciones" {{ $resPrevio === 'sustentacion_con_correcciones' ? 'checked' : '' }} class="w-5 h-5 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-800">Sustentación después de correcciones sugeridas</span>
                    <span class="text-[10px] text-gray-500">Requiere ajustar detalles menores antes de la defensa final.</span>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="radio" name="resultado_evaluacion" value="puede_sustentar" {{ $resPrevio === 'puede_sustentar' ? 'checked' : '' }} class="w-5 h-5 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-800">Puede sustentar</span>
                    <span class="text-[10px] text-gray-500">Aprobado y apto para su defensa pública inmediata.</span>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="radio" name="resultado_evaluacion" value="no_sustentar" {{ $resPrevio === 'no_sustentar' ? 'checked' : '' }} class="w-5 h-5 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-800">Requiere reestructurar y someter nuevamente</span>
                    <span class="text-[10px] text-gray-500">El trabajo requiere revisiones estructurales de fondo.</span>
                </div>
            </label>
        </div>
    </div>

</div>
