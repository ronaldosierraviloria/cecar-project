<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rúbrica - {{ $evaluacion->trabajo->titulo }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; background: #fff; padding: 40px; color: #000; }
        .header-table { width: 100%; border: 2px solid #000; margin-bottom: 30px; }
        .header-table td { padding: 10px; text-align: center; vertical-align: middle; border: none; }
        .header-table .col-logo { width: 20%; }
        .header-table .col-title { width: 60%; }
        .header-table .col-code { width: 20%; }
        .header-table .divider { border-left: 2px solid #000; }
        .logo-img { max-height: 50px; filter: grayscale(100%); }
        .title-main { font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .code-text { font-size: 10px; }
        .section-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 4px; }
        .section-value { font-size: 12px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 10px; }
        .criterio-row { padding: 8px 0; border-bottom: 1px solid #eee; }
        .criterio-num { font-weight: bold; color: #07321e; font-size: 11px; }
        .criterio-desc { font-size: 11px; color: #333; }
        .criterio-pct { font-size: 9px; color: #888; }
        .criterio-nota { font-size: 14px; font-weight: bold; }
        .resultado-box { border: 2px solid #000; padding: 12px; text-align: center; margin: 20px 0; }
        .resultado-box .label { font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .resultado-box .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .resultado-box .nota { font-size: 24px; font-weight: bold; margin-top: 4px; }
        .field-label { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #666; margin-bottom: 2px; }
        .field-value { font-size: 11px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 12px; }
        .firma-img { max-height: 60px; border: 1px solid #ccc; padding: 6px; background: #f9f9f9; margin-top: 6px; }
        .firma-section { border-top: 2px solid #000; margin-top: 30px; padding-top: 20px; display: flex; justify-content: space-between; gap: 20px; }
        .firma-col { flex: 1; }
        .valoracion-badge { display: inline-block; padding: 2px 10px; font-size: 10px; font-weight: bold; border-radius: 4px; }
        .valoracion-excelente { color: #1f2937; }
        .valoracion-aceptable { color: #1f2937; }
        .valoracion-deficiente { color: #1f2937; }
        .estado-completada { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 8px; text-align: center; font-size: 11px; font-weight: bold; margin-top: 20px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    @php
        $trabajo = $evaluacion->trabajo;
        $criterios = $evaluacion->criterios ?? [];
        $tipo = $evaluacion->tipo_plantilla;
        $isPropuesta = $tipo === 'propuesta_de_grado';
        $isPasantia = $tipo === 'pasantia';
        $miNombre = auth()->user()->nombre . ' ' . auth()->user()->apellido;

        $resultadoTexto = match($evaluacion->resultado) {
            'aceptada' => 'Aceptada',
            'aceptada_con_mejoras' => 'Aceptada con mejoras',
            'rechazada' => 'Rechazada',
            'puede_sustentar' => $isPasantia ? 'Aprobado' : 'Puede sustentar',
            'sustentacion_con_correcciones' => $isPasantia ? 'Aprobado con observaciones' : 'Sustentación después de correcciones sugeridas',
            'no_sustentar' => $isPasantia ? 'No aprobado' : 'Requiere reestructurar y someter nuevamente',
            default => ucfirst($evaluacion->resultado)
        };
    @endphp

    <div id="contenido-rubrica">
        {{-- CABECERA OFICIAL --}}
        <table class="header-table">
            <tr>
                <td class="col-logo">
                    <img src="{{ asset('images/logocecar.webp') }}" alt="Logo CECAR" class="logo-img" onerror="this.style.display='none'">
                </td>
                <td class="col-title divider">
                    <div class="title-main">
                        @if($isPropuesta)
                            Evaluación de la Propuesta de Trabajo Grado
                        @elseif($isPasantia)
                            Formato de Evaluación de Pasantía
                        @else
                            Formato de Evaluación de Informe Final Trabajo Grado
                        @endif
                    </div>
                </td>
                <td class="col-code divider">
                    <div class="code-text"><strong>FCBIA</strong></div>
                    <div class="code-text">{{ $isPropuesta ? 'FO-TG-006' : ($isPasantia ? 'FO-TG-008' : 'FO-TG-0010') }}</div>
                </td>
            </tr>
        </table>

        {{-- INFORMACIÓN DEL PROYECTO --}}
        <div class="section-box">
            <div class="section-title">Título del {{ $isPropuesta ? 'Anteproyecto' : ($isPasantia ? 'Informe de Pasantía' : 'Informe Final') }}</div>
            <div class="section-value">{{ $trabajo->titulo }}</div>

            <div class="section-title">Presentado por</div>
            <div class="section-value">
                @if($trabajo->estudiante)
                    @foreach($trabajo->estudiante as $est)
                        {{ $est->nombre }} {{ $est->apellido }}@if(!$loop->last), @endif
                    @endforeach
                @else
                    No asignado
                @endif
            </div>

            <div class="section-title">Director(es)</div>
            <div class="section-value" style="margin-bottom:0">
                @if($trabajo->directores)
                    @forelse($trabajo->directores as $dir)
                        {{ $dir->nombre }} {{ $dir->apellido }}@if(!$loop->last), @endif
                    @empty
                        No asignado
                    @endforelse
                @else
                    No asignado
                @endif
            </div>
        </div>

        {{-- CRITERIOS --}}
        @if($isPropuesta)
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

            <div class="section-box">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div class="section-title" style="margin:0">Evaluación Cuantitativa</div>
                    <span style="font-size:9px;font-weight:bold;color:#07321e;">Rango: 0.0 - 5.0</span>
                </div>

                @foreach($criteriosPropuesta as $idx => $crit)
                    @php $criterioData = $criterios[$idx - 1] ?? []; @endphp
                    <div class="criterio-row">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="flex:1">
                                <span class="criterio-num">{{ $idx }}.</span>
                                <span class="criterio-desc">{{ $crit['desc'] }}</span>
                            </div>
                            <span class="criterio-pct" style="white-space:nowrap;margin-left:10px;">Peso: {{ $crit['pct'] }}%</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:15px; margin-top:4px;">
                            <div>
                                <div class="field-label" style="margin:0">Calificación</div>
                                <span class="criterio-nota">{{ isset($criterioData['calificacion']) ? number_format($criterioData['calificacion'], 1) : '_____' }}</span>
                            </div>
                            <div style="flex:1">
                                <div class="field-label" style="margin:0">Observaciones</div>
                                <span style="font-size:11px;">{{ $criterioData['comentario'] ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="resultado-box">
                <div style="display:flex; justify-content:center; gap:30px; align-items:center;">
                    <div>
                        <div class="label">Nota Final</div>
                        <div class="nota">{{ $evaluacion->nota_final !== null ? number_format($evaluacion->nota_final, 1) : '_____' }}</div>
                    </div>
                    <div>
                        <div class="label">Resultado</div>
                        <div class="value">{{ $resultadoTexto }}</div>
                    </div>
                </div>
            </div>
        @else
            @php
                $preguntas = $tipo === 'pasantia' ? [
                    1 => 'Desempeño general durante la pasantía.',
                    2 => 'Aplicación de conocimientos académicos.',
                    3 => 'Calidad del informe final.',
                    4 => 'Relación con el proyecto de grado.',
                    5 => 'Impacto y resultados obtenidos.',
                    6 => 'Satisfacción de la empresa/organización.',
                ] : [
                    1 => 'El título. ¿Está el título acorde con las expectativas planteadas en la investigación?',
                    2 => 'Introducción (incluye planteamiento del problema y justificación).',
                    3 => 'Marco referencial. ¿La revisión bibliográfica es apropiada?',
                    4 => 'Cumplimiento de objetivos en los resultados obtenidos.',
                    5 => 'Evaluación de la metodología.',
                    6 => 'Novedad y pertinencia de los resultados.',
                    7 => 'Conclusiones acordes a los resultados obtenidos.',
                ];
            @endphp

            @foreach($preguntas as $idx => $pregunta)
                @php $criterioData = $criterios[$idx - 1] ?? []; @endphp
                <div class="section-box">
                    <div style="display:flex; gap:6px; margin-bottom:8px;">
                        <span class="criterio-num">{{ $idx }}.</span>
                        <span class="criterio-desc">{{ $pregunta }}</span>
                    </div>
                    <div class="field-label">Comentarios / Observaciones</div>
                    <div class="field-value" style="font-weight:normal;border:none;padding:0;">{{ $criterioData['comentario'] ?? '' }}</div>
                    @if($isPasantia)
                        <div class="field-label" style="margin-top:6px;">Valoración</div>
                        @php
                            $valClase = match($criterioData['valoracion'] ?? '') {
                                'excelente' => 'valoracion-excelente',
                                'aceptable' => 'valoracion-aceptable',
                                'deficiente' => 'valoracion-deficiente',
                                default => ''
                            };
                        @endphp
                        <span class="valoracion-badge {{ $valClase }}">{{ $criterioData['valoracion'] ? ucfirst($criterioData['valoracion']) : '________' }}</span>
                    @endif
                </div>
            @endforeach

            <div class="resultado-box">
                <div class="label">Resultado de la Evaluación</div>
                <div class="value">{{ $resultadoTexto }}</div>
            </div>
        @endif

        {{-- OBSERVACIONES GLOBALES --}}
        @if($evaluacion->observaciones_globales)
        <div class="section-box">
            <div class="section-title">Comentarios y Observaciones Adicionales</div>
            <div style="font-size:11px;white-space:pre-wrap;">{{ $evaluacion->observaciones_globales }}</div>
        </div>
        @endif

        {{-- FIRMAS DE AMBOS EVALUADORES --}}
        <div class="firma-section">
            {{-- Firma Evaluador 1 --}}
            <div class="firma-col">
                <div class="section-title">Evaluador 1</div>
                <div class="field-value" style="font-weight:bold;border:none;">
                    {{ $evaluador1 ? $evaluador1->nombre . ' ' . $evaluador1->apellido : '—' }}
                </div>
                <div class="section-title" style="margin-top:8px;">Firma</div>
                @if($evaluacion->firma)
                    @if(auth()->user()->profesor && $evaluador1 && auth()->user()->profesor->id_profesor === $evaluador1->id_profesor)
                        <img src="{{ $evaluacion->firma }}" alt="Firma" class="firma-img">
                    @else
                        <img src="{{ $evaluacion->firma }}" alt="Firma" class="firma-img">
                    @endif
                @else
                    <div style="border:1px dashed #ccc; height:50px; margin-top:4px;"></div>
                @endif
            </div>

            {{-- Firma Evaluador 2 --}}
            @if($evaluador2)
            <div class="firma-col">
                <div class="section-title">Evaluador 2</div>
                <div class="field-value" style="font-weight:bold;border:none;">
                    {{ $evaluador2->nombre }} {{ $evaluador2->apellido }}
                </div>
                <div class="section-title" style="margin-top:8px;">Firma</div>
                @if($evaluacion->firma_evaluador_2)
                    <img src="{{ $evaluacion->firma_evaluador_2 }}" alt="Firma Evaluador 2" class="firma-img">
                @else
                    <div style="border:1px dashed #ccc; height:50px; margin-top:4px;"></div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="generarPDF()" style="padding:10px 24px; background:#c2d500; color:#07321e; border:none; border-radius:8px; font-weight:bold; cursor:pointer; font-size:14px;">
            Descargar PDF
        </button>
    </div>

    <script>
        function generarPDF() {
            const element = document.getElementById('contenido-rubrica');
            const opt = {
                margin:       8,
                filename:     'Rubrica_{{ preg_replace("/[^a-zA-Z0-9]/", "_", $evaluacion->trabajo->titulo) }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
        window.onload = function() {
            setTimeout(generarPDF, 500);
        };
    </script>
</body>
</html>
