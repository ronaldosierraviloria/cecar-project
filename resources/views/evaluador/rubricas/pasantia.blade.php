{{-- Formato FO-TG-008: Evaluación de Pasantía }}

{{-- CABECERA OFICIAL CECAR FO-TG-008 --}}
<div class="bg-white border-2 border-black mb-6 p-4">
    <div class="grid grid-cols-3 items-center text-center divide-x-2 divide-black">
        <div class="p-2 flex justify-center items-center">
            <img src="{{ asset('images/logocecar.webp') }}" alt="Logo CECAR" class="h-10 w-auto">
        </div>
        <div class="p-2 flex flex-col justify-center">
            <span class="text-xs font-bold uppercase">Formato de Evaluación de</span>
            <span class="text-xs font-bold uppercase">Pasantía</span>
        </div>
        <div class="p-2 flex flex-col justify-center text-xs">
            <span class="font-bold">FCBIA</span>
            <span>FO-TG-008</span>
        </div>
    </div>
</div>

{{-- INFORMACIÓN BÁSICA --}}
<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 space-y-3">
    <div>
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Título de la Pasantía:</span>
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

{{-- CRITERIOS DE EVALUACIÓN --}}
<div class="space-y-6">
    @php
        $criteriosPasantia = [
            1 => 'Desempeño general durante la pasantía: Cumplimiento de horarios, responsabilidad, initiative y actitud profesional.',
            2 => 'Aplicación de conocimientos académicos: El pasante demuestra capacidad para aplicar los conocimientos adquiridos en el programa académico al contexto laboral.',
            3 => 'Calidad del informe final: El informe presenta estructura adecuada, coherencia, análisis reflexivo y cumplimiento del formato establecido.',
            4 => 'Relación con el proyecto de grado: La pasantía está relacionada con la línea de investigación o el tema del trabajo de grado.',
            5 => 'Impacto y resultados obtenidos: La pasantía generó aportes significativos a la empresa/institución y al desarrollo profesional del pasante.',
            6 => 'Satisfacción de la empresa/organización: La entidad receptora manifiesta conformidad con el desempeño y resultados del pasante.',
        ];
    @endphp

    @foreach($criteriosPasantia as $idx => $criterio)
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm question-card">
        <div class="flex gap-2">
            <span class="font-bold text-[#07321e] text-sm">{{ $idx }}.</span>
            <p class="text-sm font-semibold text-gray-800">{{ $criterio }}</p>
        </div>

        {{-- Comentario / Observación --}}
        <div class="mt-4">
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Comentarios / Observaciones</label>
            <textarea id="observacion_{{ $idx }}" rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-700 focus:ring-2 focus:ring-[#c2d500] focus:border-[#c2d500] outline-none transition-all resize-none"
                placeholder="Escribe aquí tu análisis detallado para este punto...">{{ (collect($evaluacionPrevia?->criterios ?? [])->firstWhere('id', $idx))['comentario'] ?? '' }}</textarea>
        </div>

        {{-- Valoración --}}
        <div class="mt-3">
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Valoración</label>
            <div class="flex flex-wrap gap-3">
                @php $resPrevio = (collect($evaluacionPrevia?->criterios ?? [])->firstWhere('id', $idx))['valoracion'] ?? ''; @endphp
                <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="valoracion_{{ $idx }}" value="excelente" {{ $resPrevio === 'excelente' ? 'checked' : '' }} class="w-4 h-4 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                    <span class="text-xs font-bold text-gray-800">Excelente</span>
                </label>
                <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="valoracion_{{ $idx }}" value="aceptable" {{ $resPrevio === 'aceptable' ? 'checked' : '' }} class="w-4 h-4 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                    <span class="text-xs font-bold text-gray-800">Aceptable</span>
                </label>
                <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="valoracion_{{ $idx }}" value="deficiente" {{ $resPrevio === 'deficiente' ? 'checked' : '' }} class="w-4 h-4 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                    <span class="text-xs font-bold text-gray-800">Deficiente</span>
                </label>
            </div>
        </div>
    </div>
    @endforeach

    {{-- 7. COMENTARIOS Y SUGERENCIAS DEL EVALUADOR --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm question-card">
        <div class="flex gap-2 mb-2">
            <span class="font-bold text-[#07321e] text-sm">7.</span>
            <p class="text-sm font-semibold text-gray-800">Comentarios y sugerencias del evaluador</p>
        </div>
        <textarea id="observacion_final" rows="4"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-700 focus:ring-2 focus:ring-[#c2d500] focus:border-[#c2d500] outline-none transition-all resize-none"
            placeholder="Ingrese las sugerencias globales o puntos a corregir...">{{ $evaluacionPrevia->observaciones_globales ?? '' }}</textarea>
    </div>

    {{-- 8. RESULTADO DE LA EVALUACIÓN --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm question-card">
        <div class="flex gap-2 mb-4">
            <span class="font-bold text-[#07321e] text-sm">8.</span>
            <p class="text-sm font-semibold text-gray-800">Resultado de la evaluación (Selección Única)</p>
        </div>
        <div class="space-y-3">
            @php
                $resPrevio = $evaluacionPrevia->resultado ?? '';
            @endphp
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="radio" name="resultado_evaluacion" value="puede_sustentar" {{ $resPrevio === 'puede_sustentar' ? 'checked' : '' }} class="w-5 h-5 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-800">Aprobado</span>
                    <span class="text-[10px] text-gray-500">La pasantía cumple con los requisitos y está apta para sustentar.</span>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="radio" name="resultado_evaluacion" value="sustentacion_con_correcciones" {{ $resPrevio === 'sustentacion_con_correcciones' ? 'checked' : '' }} class="w-5 h-5 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-800">Aprobado con observaciones</span>
                    <span class="text-[10px] text-gray-500">Aprobado, pero requiere correcciones menores antes de la sustentación.</span>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="radio" name="resultado_evaluacion" value="no_sustentar" {{ $resPrevio === 'no_sustentar' ? 'checked' : '' }} class="w-5 h-5 text-[#07321e] focus:ring-[#c2d500] border-gray-300">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-800">No aprobado</span>
                    <span class="text-[10px] text-gray-500">La pasantía no cumple con los requisitos mínimos. Requiere rehacer.</span>
                </div>
            </label>
        </div>
    </div>

</div>
