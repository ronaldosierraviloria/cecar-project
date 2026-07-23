@extends('layouts.baseEvaluadorFullscreen')

@section('title', 'Evaluación — ' . ($trabajo->titulo ?? 'Proyecto'))

@push('styles')
<style>
    #evaluacion-layout {
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #panel-pdf {
        min-width: 280px;
        max-width: 70%;
        width: 50%;
        flex-shrink: 0;
        transition: width 0.25s ease, flex 0.25s ease, opacity 0.25s ease;
    }

    #panel-rubrica {
        flex: 1;
        min-width: 300px;
        overflow-y: auto;
        transition: width 0.25s ease, flex 0.25s ease, opacity 0.25s ease;
    }

    #divider {
        width: 6px;
        background: #e5e7eb;
        cursor: col-resize;
        flex-shrink: 0;
        transition: background 0.15s;
        position: relative;
    }
    #divider:hover, #divider.dragging {
        background: #c2d500;
    }
    #divider::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 2px;
        height: 32px;
        background: #9ca3af;
        border-radius: 2px;
    }

    #panel-rubrica::-webkit-scrollbar { width: 6px; }
    #panel-rubrica::-webkit-scrollbar-track { background: #f9fafb; }
    #panel-rubrica::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    #panel-rubrica::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    .rubrica-table-header {
        border: 2px solid #000;
        background-color: #ffffff;
    }
    .rubrica-cell-border {
        border: 1px solid #d1d5db;
    }
    .question-card {
        border-left: 4px solid #d1d5db;
    }

    .signature-pad-container {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        background: #f9fafb;
        position: relative;
    }
    .signature-canvas {
        width: 100%;
        height: 150px;
        display: block;
    }
</style>
{{-- Cargar html2pdf.js desde CDN para descarga PDF local en navegador --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endpush

@section('content')
    {{-- ── MOBILE WARNING BANNER ── --}}
    <div class="md:hidden fixed inset-0 bg-[#07321e] z-[9999] flex flex-col items-center justify-center p-6 text-center">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-8 rounded-3xl max-w-sm shadow-2xl flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-[#c2d500]/20 flex items-center justify-center text-[#c2d500] mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-3">Recomendación de Dispositivo</h3>
            <p class="text-sm text-white/80 leading-relaxed mb-6">
                Para evaluar, visualizar la rúbrica y calificar de forma correcta, te recomendamos realizar este proceso desde una <strong>Computadora de Escritorio o Portátil</strong>.
            </p>
            <a href="{{ route('evaluador.dashboard') }}" class="w-full py-3 bg-[#c2d500] hover:bg-[#b5c700] text-[#07321e] font-bold rounded-xl text-sm transition-all shadow-md active:scale-95">
                Volver al Panel
            </a>
        </div>
    </div>

<div style="width: 100%; height: 100vh; display: flex; flex-direction: column; overflow: hidden;">

    {{-- BARRA SUPERIOR --}}
    <header class="bg-white border-b border-gray-200 px-5 py-3 flex items-center justify-between gap-4 shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('evaluador.dashboard') }}" class="flex items-center gap-1.5 text-[#07321e] hover:text-[#c2d500] transition-colors shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="w-px h-6 bg-gray-200 shrink-0"></div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-0.5">
                    @if($trabajo->plantilla_rubrica === 'propuesta_de_grado')
                        Formato FO-TG-006
                    @elseif($trabajo->plantilla_rubrica === 'pasantia')
                        Formato FO-TG-008
                    @else
                        Formato FO-TG-0010
                    @endif
                </p>
                <h1 class="text-sm font-bold text-gray-900 truncate max-w-md">Evaluación: {{ $trabajo->titulo }}</h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button id="toggle-pdf-btn" onclick="togglePanel('pdf')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-white text-[#07321e] hover:bg-gray-50 border border-gray-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Ocultar PDF
            </button>
            <button id="toggle-rubrica-btn" onclick="togglePanel('rubrica')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-white text-[#07321e] hover:bg-gray-50 border border-gray-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Ocultar Rúbrica
            </button>
            <button onclick="exportarComoPDF()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-white text-rose-700 hover:bg-rose-50 border border-rose-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar PDF
            </button>
            <button onclick="guardarProgreso()"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition-all">
                Guardar Progreso
            </button>
            <button onclick="guardarEvaluacionCompleta()"
                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg text-[11px] font-bold bg-[#c2d500] text-[#07321e] hover:bg-[#b6c900] transition-all shadow-sm">
                Guardar Calificación
            </button>
        </div>
    </header>

    {{-- SPLIT CONTAINER --}}
    <div class="flex flex-1 overflow-hidden" id="split-container">
        
        {{-- PANEL PDF (IZQUIERDA) --}}
        <div id="panel-pdf" class="flex flex-col bg-gray-900 overflow-hidden">
            <iframe id="pdf-iframe" src="{{ route('trabajo.archivo', $trabajo->id_trabajo) }}?v={{ now()->timestamp }}" allowfullscreen class="flex-1 min-h-0 w-full"></iframe>
        </div>

        {{-- DIVISOR --}}
        <div id="divider"></div>

        {{-- PANEL RÚBRICA (DERECHA) --}}
        <div id="panel-rubrica" class="bg-gray-50 overflow-y-auto">
            <div class="p-6 max-w-4xl mx-auto" id="contenido-a-exportar">
                
                {{-- Ordenamos los evaluadores por fecha de asignación para que coincida con los slots --}}
                @php
                    $evaluadoresOrdenados = $trabajo->evaluadores->sortBy('pivot.fecha_asignacion')->values();
                    $eval1 = $evaluadoresOrdenados->get(0);
                    $eval2 = $evaluadoresOrdenados->get(1);
                @endphp

                {{-- Información de evaluación compartida --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Solo mostrar badge del evaluador actual --}}
                        @if($eval1)
                            @php $esYo = $eval1->id_profesor === (auth()->user()->profesor->id_profesor ?? null); @endphp
                            @if($esYo)
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#c2d500]/20 text-[#07321e]">
                                    <span>{{ $eval1->nombre }} {{ $eval1->apellido }}</span>
                                    <span class="text-[10px]">(Tú)</span>
                                    @if(!empty($evaluacionPrevia?->firma))
                                        <span class="text-[10px] text-emerald-600">Firmado</span>
                                    @else
                                        <span class="text-[10px] text-amber-600">Pendiente</span>
                                    @endif
                                </div>
                            @endif
                        @endif
                        @if($eval2)
                            @php $esYo2 = $eval2->id_profesor === (auth()->user()->profesor->id_profesor ?? null); @endphp
                            @if($esYo2)
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#c2d500]/20 text-[#07321e]">
                                    <span>{{ $eval2->nombre }} {{ $eval2->apellido }}</span>
                                    <span class="text-[10px]">(Tú)</span>
                                    @if(!empty($evaluacionPrevia?->firma_evaluador_2))
                                        <span class="text-[10px] text-emerald-600">Firmado</span>
                                    @else
                                        <span class="text-[10px] text-amber-600">Pendiente</span>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- RENDERIZADO DINÁMICO DE RÚBRICA BASADO EN LA PLANTILLA ELEGIDA POR EL GESTOR --}}
                @if($trabajo->plantilla_rubrica === 'propuesta_de_grado')
                    @include('evaluador.rubricas.propuesta_de_grado', ['miSlot' => $miSlot])
                @elseif($trabajo->plantilla_rubrica === 'pasantia')
                    @include('evaluador.rubricas.pasantia', ['miSlot' => $miSlot])
                @else
                    @include('evaluador.rubricas.trabajo_de_grado', ['miSlot' => $miSlot])
                @endif

                {{-- HIDDEN: Slot del evaluador para el envío al servidor --}}
                <input type="hidden" id="miSlot" value="{{ $miSlot }}">

                {{-- SECCIÓN DE FIRMAS - Cada evaluador solo ve su propia firma --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 space-y-6">
                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wide border-b border-gray-100 pb-2">Tu Firma</h4>

                    <div class="p-4 rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-xs">
                                {{ $miSlot === 1 ? ($eval1 ? substr($eval1->nombre, 0, 1) : '?') : ($eval2 ? substr($eval2->nombre, 0, 1) : '?') }}
                            </div>
                            <div>
                                <span class="text-sm font-bold text-gray-800">
                                    {{ $miSlot === 1 ? ($eval1 ? $eval1->nombre . ' ' . $eval1->apellido : 'Evaluador 1') : ($eval2 ? $eval2->nombre . ' ' . $eval2->apellido : 'Evaluador 2') }}
                                </span>
                                <span class="text-[10px] text-amber-700 font-bold ml-2">(Eres tú - firma aquí)</span>
                                @if($miSlot === 1 && !empty($evaluacionPrevia?->firma))
                                    <span class="text-[10px] text-emerald-600 font-bold ml-2">✔ Firmado</span>
                                @elseif($miSlot === 2 && !empty($evaluacionPrevia?->firma_evaluador_2))
                                    <span class="text-[10px] text-emerald-600 font-bold ml-2">✔ Firmado</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Tu Firma Digitalizada</label>
                            <div class="signature-pad-container">
                                <canvas id="signature-canvas-{{ $miSlot }}" class="signature-canvas"></canvas>
                                <div class="absolute bottom-2 right-2 flex gap-2">
                                    <button onclick="clearSignature()" type="button" 
                                        class="px-2.5 py-1 text-[10px] font-bold bg-white text-gray-600 border border-gray-200 rounded hover:bg-gray-50 shadow-sm">
                                        Limpiar firma
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<div id="toast" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-[#07321e] text-white px-5 py-3 rounded-2xl shadow-2xl opacity-0 translate-y-[100px] transition-all duration-300">
    <span id="toast-msg" class="text-sm font-bold">Operación exitosa.</span>
</div>
@endsection

@push('scripts')
<script>
    // DIVISOR ARRASTRABLE
    (function () {
        const container = document.getElementById('split-container');
        const panelPdf  = document.getElementById('panel-pdf');
        const divider   = document.getElementById('divider');
        let isDragging  = false;

        divider.addEventListener('mousedown', () => {
            if (!panelPdf || !divider) return;
            isDragging = true;
            divider.classList.add('dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging || !container || !panelPdf) return;
            const rect = container.getBoundingClientRect();
            const newWidth = e.clientX - rect.left;
            const minWidth = 280;
            const maxWidth = rect.width * 0.75;
            const widthPx = Math.max(minWidth, Math.min(maxWidth, newWidth));
            panelPdf.style.width = widthPx + 'px';
            window.lastPdfWidth = widthPx;
        });

        document.addEventListener('mouseup', () => {
            if (!isDragging) return;
            isDragging = false;
            divider.classList.remove('dragging');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        });
    })();

    let pdfVisible = true;
    let rubricaVisible = true;
    window.lastPdfWidth = 0;

    function updatePanelLayout() {
        const panelPdf = document.getElementById('panel-pdf');
        const panelRubrica = document.getElementById('panel-rubrica');
        const divider = document.getElementById('divider');
        const togglePdfBtn = document.getElementById('toggle-pdf-btn');
        const toggleRubricaBtn = document.getElementById('toggle-rubrica-btn');

        if (!panelPdf || !panelRubrica || !divider) return;

        if (pdfVisible && rubricaVisible) {
            divider.style.display = 'block';
            panelPdf.style.display = 'flex';
            panelRubrica.style.display = 'block';
            panelPdf.style.flex = '0 0 auto';
            panelRubrica.style.flex = '1 1 auto';
            panelPdf.style.width = window.lastPdfWidth && window.lastPdfWidth > 0 ? window.lastPdfWidth + 'px' : '50%';
            panelRubrica.style.width = 'auto';
        } else if (pdfVisible) {
            divider.style.display = 'none';
            panelPdf.style.display = 'flex';
            panelRubrica.style.display = 'none';
            panelPdf.style.flex = '1 1 auto';
            panelPdf.style.width = '100%';
        } else if (rubricaVisible) {
            divider.style.display = 'none';
            panelPdf.style.display = 'none';
            panelRubrica.style.display = 'block';
            panelRubrica.style.flex = '1 1 auto';
            panelRubrica.style.width = '100%';
        } else {
            divider.style.display = 'none';
            panelPdf.style.display = 'none';
            panelRubrica.style.display = 'none';
        }

        if (togglePdfBtn) {
            togglePdfBtn.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${pdfVisible ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'}" />
                </svg>
                ${pdfVisible ? 'Ocultar PDF' : 'Mostrar PDF'}
            `;
        }

        if (toggleRubricaBtn) {
            toggleRubricaBtn.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${rubricaVisible ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'}" />
                </svg>
                ${rubricaVisible ? 'Ocultar Rúbrica' : 'Mostrar Rúbrica'}
            `;
        }
    }

    function togglePanel(panel) {
        if (panel === 'pdf') {
            pdfVisible = !pdfVisible;
        } else if (panel === 'rubrica') {
            rubricaVisible = !rubricaVisible;
        }
        updatePanelLayout();
    }

    // MANEJO DE CANVAS DE FIRMA
    let canvas, ctx, isDrawing = false;
    let firmaValida = false; // Solo se marca true cuando el usuario realmente dibuja
    const signatureCanvasId = '{{ $miSlot === 1 ? "signature-canvas-1" : "signature-canvas-2" }}';
    
    function initSignature() {
        canvas = document.getElementById(signatureCanvasId);
        if (!canvas) return;
        ctx = canvas.getContext('2d');
        firmaValida = false;

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        // Eventos mouse
        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            firmaValida = true;
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.stroke();
        });

        document.addEventListener('mouseup', () => {
            isDrawing = false;
        });

        // Eventos touch para dispositivos móviles
        canvas.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            isDrawing = true;
            firmaValida = true;
            ctx.beginPath();
            ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            e.preventDefault();
        });

        canvas.addEventListener('touchmove', (e) => {
            if (!isDrawing) return;
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
            ctx.stroke();
            e.preventDefault();
        });
    }

    function resizeCanvas() {
        if (!canvas) return;
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        ctx.scale(ratio, ratio);
        ctx.strokeStyle = '#07321e';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
    }

    document.addEventListener('DOMContentLoaded', () => {
        initSignature();
        updatePanelLayout();
        window.addEventListener('resize', updatePanelLayout);
    });
    // Respaldo por si DOMContentLoaded ya ocurrió
    setTimeout(() => {
        initSignature();
        updatePanelLayout();
    }, 300);

    function clearSignature() {
        if (ctx && canvas) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            firmaValida = false;
        }
    }

    // EXPORTACIÓN A PDF CON HTML2PDF
    function exportarComoPDF() {
        const element = document.getElementById('contenido-a-exportar');
        
        // Ocultar botones interactivos temporalmente para el PDF
        const buttonsToHide = element.querySelectorAll('button');
        buttonsToHide.forEach(b => b.style.display = 'none');

        const opt = {
            margin:       10,
            filename:     'Evaluacion_Trabajo_CECAR.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            buttonsToHide.forEach(b => b.style.display = '');
        });
    }

    // GUARDAR CALIFICACIÓN (ENVIAR AL SERVIDOR)
    async function guardarEvaluacionCompleta() {
        const tipoPlantilla = '{{ $trabajo->plantilla_rubrica }}';
        let resultadoVal = '';
        let notaFinal = null;
        let criterios = [];

        if (tipoPlantilla === 'propuesta_de_grado') {
            const descs = [
                'El título está acorde con el problema a resolver.',
                'La formulación y justificación del problema responden al trabajo planteado.',
                'El cumplimiento del objetivo general garantiza la solución al problema planteado.',
                'El cumplimiento de los objetivos específicos asegura el logro del objetivo general.',
                'El marco referencial presentado da respuesta al problema planteado.',
                'La metodología planteada permite el cumplimiento de los objetivos.',
                'El cronograma es conforme con el alcance planteado y las referencias son actualizadas.',
            ];

            const pesos = [0.05, 0.20, 0.20, 0.20, 0.10, 0.20, 0.05];
            let suma = 0;
            let todosLlenos = true;

            for (let i = 1; i <= 7; i++) {
                const inputNota = document.getElementById('nota_propuesta_' + i);
                const inputObs = document.getElementById('obs_propuesta_' + i);
                const nota = inputNota ? parseFloat(inputNota.value) : null;
                
                if (nota === null || isNaN(nota) || inputNota.value === '') {
                    todosLlenos = false;
                } else {
                    suma += Math.max(0, Math.min(5, nota)) * pesos[i - 1];
                }
                
                criterios.push({
                    id: i,
                    descripcion: descs[i - 1],
                    calificacion: (nota === null || isNaN(nota)) ? null : Math.max(0, Math.min(5, nota)),
                    comentario: inputObs ? inputObs.value : '',
                });
            }

            // Determinar resultado y nota final directamente desde las notas numéricas
            if (todosLlenos) {
                notaFinal = Math.round(suma * 100) / 100;
                if (suma >= 4.2) {
                    resultadoVal = 'aceptada';
                } else if (suma >= 3.0) {
                    resultadoVal = 'aceptada_con_mejoras';
                } else {
                    resultadoVal = 'rechazada';
                }
            }

            if (!resultadoVal) {
                showToast('⚠ Debe calificar todos los criterios (0.0 a 5.0) para calcular el resultado.', true);
                return;
            }
        } else {
            // Trabajo de grado / Pasantía
            const radioResultado = document.querySelector('input[name="resultado_evaluacion"]:checked');
            if (!radioResultado) {
                showToast('⚠ Debe seleccionar el resultado de la evaluación.', true);
                return;
            }
            resultadoVal = radioResultado.value;

            const descs = tipoPlantilla === 'pasantia' ? [
                'Desempeño general durante la pasantía.',
                'Aplicación de conocimientos académicos.',
                'Calidad del informe final.',
                'Relación con el proyecto de grado.',
                'Impacto y resultados obtenidos.',
                'Satisfacción de la empresa/organización.',
            ] : [
                'El título está acorde con las expectativas planteadas en la investigación.',
                'Introducción: planteamiento del problema y justificación.',
                'Marco referencial: revisión bibliográfica apropiada y coherente.',
                'Cumplimiento de objetivos en los resultados obtenidos.',
                'Metodología: métodos reconocidos, claramente descrita.',
                'Novedad y pertinencia de los resultados.',
                'Conclusiones acordes a los resultados obtenidos.',
            ];

            const totalCriterios = tipoPlantilla === 'pasantia' ? 6 : 7;

            for (let i = 1; i <= totalCriterios; i++) {
                const inputObs = document.getElementById('observacion_' + i);
                const radioValoracion = document.querySelector('input[name="valoracion_' + i + '"]:checked');
                criterios.push({
                    id: i,
                    descripcion: descs[i - 1],
                    valoracion: radioValoracion ? radioValoracion.value : null,
                    comentario: inputObs ? inputObs.value : '',
                });
            }
        }

        if (!resultadoVal) {
            showToast('⚠ Debe seleccionar o calcular el resultado de la evaluación.', true);
            return;
        }

        if (!canvas || !firmaValida) {
            showToast('⚠ Debe firmar antes de guardar la evaluación.', true);
            return;
        }

        const data = {
            tipo_plantilla: tipoPlantilla,
            nota_final: notaFinal,
            resultado: resultadoVal,
            observaciones_globales: document.getElementById('observacion_final') ? document.getElementById('observacion_final').value : '',
            criterios: criterios,
            firma: canvas.toDataURL(),
        };

        try {
            const response = await fetch('{{ route("evaluador.guardar-evaluacion", $trabajo->id_trabajo) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                if (result.evaluacion_completada) {
                    showToast('✔ Evaluación completada por AMBOS evaluadores.');
                } else {
                    showToast('✔ Calificación guardada correctamente.');
                }
                setTimeout(() => {
                    window.location.href = '{{ route("evaluador.dashboard") }}';
                }, 1500);
            } else {
                showToast('⚠ Error al guardar: ' + (result.message || 'Error desconocido'), true);
            }
        } catch (error) {
            showToast('⚠ Error de conexión al guardar la evaluación.', true);
        }
    }

    async function guardarProgreso() {
        const tipoPlantilla = '{{ $trabajo->plantilla_rubrica }}';
        let resultadoVal = '';
        let notaFinal = null;
        let criterios = [];

        if (tipoPlantilla === 'propuesta_de_grado') {
            const descs = [
                'El título está acorde con el problema a resolver.',
                'La formulación y justificación del problema responden al trabajo planteado.',
                'El cumplimiento del objetivo general garantiza la solución al problema planteado.',
                'El cumplimiento de los objetivos específicos asegura el logro del objetivo general.',
                'El marco referencial presentado da respuesta al problema planteado.',
                'La metodología planteada permite el cumplimiento de los objetivos.',
                'El cronograma es conforme con el alcance planteado y las referencias son actualizadas.',
            ];

            const pesos = [0.05, 0.20, 0.20, 0.20, 0.10, 0.20, 0.05];
            let suma = 0;
            let todosLlenos = true;

            for (let i = 1; i <= 7; i++) {
                const inputNota = document.getElementById('nota_propuesta_' + i);
                const inputObs = document.getElementById('obs_propuesta_' + i);
                const nota = inputNota ? parseFloat(inputNota.value) : null;
                
                if (nota === null || isNaN(nota) || inputNota.value === '') {
                    todosLlenos = false;
                } else {
                    suma += Math.max(0, Math.min(5, nota)) * pesos[i - 1];
                }
                
                criterios.push({
                    id: i,
                    descripcion: descs[i - 1],
                    calificacion: (nota === null || isNaN(nota)) ? null : Math.max(0, Math.min(5, nota)),
                    comentario: inputObs ? inputObs.value : '',
                });
            }

            // Determinar resultado y nota final directamente desde las notas numéricas
            if (todosLlenos) {
                notaFinal = Math.round(suma * 100) / 100;
                if (suma >= 4.2) {
                    resultadoVal = 'aceptada';
                } else if (suma >= 3.0) {
                    resultadoVal = 'aceptada_con_mejoras';
                } else {
                    resultadoVal = 'rechazada';
                }
            }
        } else {
            // Trabajo de grado / Pasantía
            const radioResultado = document.querySelector('input[name="resultado_evaluacion"]:checked');
            resultadoVal = radioResultado ? radioResultado.value : '';

            const descs = tipoPlantilla === 'pasantia' ? [
                'Desempeño general durante la pasantía.',
                'Aplicación de conocimientos académicos.',
                'Calidad del informe final.',
                'Relación con el proyecto de grado.',
                'Impacto y resultados obtenidos.',
                'Satisfacción de la empresa/organización.',
            ] : [
                'El título está acorde con las expectativas planteadas en la investigación.',
                'Introducción: planteamiento del problema y justificación.',
                'Marco referencial: revisión bibliográfica apropiada y coherente.',
                'Cumplimiento de objetivos en los resultados obtenidos.',
                'Metodología: métodos reconocidos, claramente descrita.',
                'Novedad y pertinencia de los resultados.',
                'Conclusiones acordes a los resultados obtenidos.',
            ];

            const totalCriterios = tipoPlantilla === 'pasantia' ? 6 : 7;

            for (let i = 1; i <= totalCriterios; i++) {
                const inputObs = document.getElementById('observacion_' + i);
                const radioValoracion = document.querySelector('input[name="valoracion_' + i + '"]:checked');
                criterios.push({
                    id: i,
                    descripcion: descs[i - 1],
                    valoracion: radioValoracion ? radioValoracion.value : null,
                    comentario: inputObs ? inputObs.value : '',
                });
            }
        }

        const data = {
            tipo_plantilla: tipoPlantilla,
            nota_final: notaFinal,
            resultado: resultadoVal || null,
            observaciones_globales: document.getElementById('observacion_final') ? document.getElementById('observacion_final').value : '',
            criterios: criterios,
        };

        try {
            const response = await fetch('{{ route("evaluador.guardar-progreso", $trabajo->id_trabajo) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                showToast('✔ Progreso guardado correctamente.');
            } else {
                showToast('⚠ Error al guardar el progreso: ' + (result.message || 'Error desconocido'), true);
            }
        } catch (error) {
            showToast('⚠ Error de conexión al guardar el progreso.', true);
        }
    }

    function showToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        toastMsg.textContent = msg;
        toast.style.background = isError ? '#7f1d1d' : '#07321e';
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
        }, 3000);
    }
</script>
@endpush
