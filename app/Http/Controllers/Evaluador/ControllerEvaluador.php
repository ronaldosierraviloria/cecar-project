<?php

namespace App\Http\Controllers\Evaluador;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Profesor;
use App\Models\Trabajo;
use App\Models\Evaluacion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use App\Notifications\PropuestaEvaluada;
use App\Notifications\TrabajoRechazado;
use App\Notifications\TrabajoAceptado;



class ControllerEvaluador extends Controller
{
    /**
     * Muestra el dashboard con los trabajos de grado asignados al evaluador logueado.
     * Esta función reemplaza al método index() que estaba en EvaluadorController.
     */
    public function index()
    {
        $usuario = Auth::user();
        
        // 1. Verificación de Rol y Obtención de ID del Profesor
        if (!$usuario->profesor) {
            return redirect('/')->with('error', 'Tu cuenta no está vinculada correctamente. Contacta al administrador.');
        }

        $profesorId = $usuario->profesor->id_profesor;

        // 2. Carga de Trabajos Asignados
        // Usamos la relación 'trabajos()' definida en Profesor.php
        $evaluador = Profesor::with([
            'trabajos' => function ($query) {
                // Seleccionamos los datos del pivote para mostrar estado y fechas límite
                $query->withPivot('fecha_asignacion', 'fecha_limite_revision', 'estado_revision', 'decision_evaluador', 'motivo_rechazo');
            },
            'trabajos.estudiante', // Cargamos los datos de los estudiantes
            'trabajos.tipo',       // Cargamos el tipo de trabajo
            'trabajos.evaluadores', // Necesario para verificar si el otro jurado ya finalizó
            'trabajos.evaluaciones', // Cargar evaluaciones para mostrar nota y resultado
            'trabajos.directores'  // Cargar directores
        ])->findOrFail($profesorId);

        $trabajosAsignados = $evaluador->trabajos->filter(function ($trabajo) {
            $miEvaluacion = $trabajo->evaluadores->where('id_profesor', auth()->user()->profesor->id_profesor)->first();
            $miRevisionFinalizada = $miEvaluacion && $miEvaluacion->pivot->estado_revision === 'Finalizado';

            $otroEvaluador = $trabajo->evaluadores->where('id_profesor', '!=', auth()->user()->profesor->id_profesor)->first();
            $otroRevisionFinalizada = $otroEvaluador && $otroEvaluador->pivot->estado_revision === 'Finalizado';

            $ambosFinalizados = $miRevisionFinalizada && $otroRevisionFinalizada;

            $evaluacion = $trabajo->evaluaciones->first();
            $completada = $evaluacion && $evaluacion->evaluacion_completada;

            return !($ambosFinalizados && $completada);
        })->values();

        // 3. Devuelve la vista (evaluador.dashboard)
        // La interfaz seguirá leyéndola porque recibe las mismas variables ($usuario, $trabajosAsignados).
        return view('evaluador.dashboard', compact('usuario', 'trabajosAsignados'));
    }

    public function trabajosCalificados()
    {
        $usuario = Auth::user();
        if (!$usuario->profesor) {
            return redirect('/')->with('error', 'Tu cuenta no está vinculada correctamente. Contacta al administrador.');
        }

        $profesorId = $usuario->profesor->id_profesor;

        $evaluador = Profesor::with([
            'trabajos' => function ($query) {
                $query->withPivot('fecha_asignacion', 'fecha_limite_revision', 'estado_revision', 'decision_evaluador', 'motivo_rechazo');
            },
            'trabajos.estudiante',
            'trabajos.tipo',
            'trabajos.evaluadores',
            'trabajos.evaluaciones',
            'trabajos.directores'
        ])->findOrFail($profesorId);

        $trabajosCalificados = $evaluador->trabajos->filter(function ($trabajo) {
            $miEvaluacion = $trabajo->evaluadores->where('id_profesor', auth()->user()->profesor->id_profesor)->first();
            $miRevisionFinalizada = $miEvaluacion && $miEvaluacion->pivot->estado_revision === 'Finalizado';

            $otroEvaluador = $trabajo->evaluadores->where('id_profesor', '!=', auth()->user()->profesor->id_profesor)->first();
            $otroRevisionFinalizada = $otroEvaluador && $otroEvaluador->pivot->estado_revision === 'Finalizado';

            $ambosFinalizados = $miRevisionFinalizada && $otroRevisionFinalizada;

            $evaluacion = $trabajo->evaluaciones->first();
            $completada = $evaluacion && $evaluacion->evaluacion_completada;

            return $ambosFinalizados && $completada;
        })->values();

        return view('evaluador.calificados', compact('usuario', 'trabajosCalificados'));
    }
    public function getRubrica($id_trabajo)
    {
    $trabajo = Trabajo::findOrFail($id_trabajo);
    $filePath = storage_path('app/rubricas/' . $trabajo->archivo_rubrica);

    $phpWord = IOFactory::load($filePath);
    $criterios = [];

    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if (method_exists($element, 'getRows')) {
                foreach ($element->getRows() as $row) {
                    $cells = $row->getCells();
                    if(count($cells) >= 2){
                        $criterios[] = [
                            'criterio' => $cells[0]->getText(),
                            'puntaje_max' => floatval($cells[1]->getText()),
                            'calificacion' => null,
                            'comentario' => ''
                        ];
                    }
                }
            }
        }
    }

    return response()->json($criterios);
    }   
    /**
     * Determinar el slot del evaluador (1 o 2) según el orden de asignación
     */
    private function getEvaluadorSlot($id_trabajo, $profesorId): int
    {
        $evaluadores = DB::table('trabajo_profesor')
            ->where('id_trabajo', $id_trabajo)
            ->orderBy('fecha_asignacion', 'asc')
            ->orderBy('id_profesor', 'asc')
            ->pluck('id_profesor')
            ->toArray();

        $posicion = array_search($profesorId, $evaluadores);
        return ($posicion !== false) ? $posicion + 1 : 1;
    }

    public function guardarEvaluacion(Request $request, $id_trabajo)
{
    $trabajo = Trabajo::findOrFail($id_trabajo);

    $data = $request->validate([
        'tipo_plantilla' => 'required|in:propuesta_de_grado,pasantia,trabajo_de_grado',
        'nota_final' => 'nullable|numeric|min:0|max:5',
        'resultado' => 'required|string|max:50',
        'observaciones_globales' => 'nullable|string',
        'criterios' => 'nullable|array',
        'criterios.*.id' => 'nullable|integer',
        'criterios.*.descripcion' => 'nullable|string',
        'criterios.*.calificacion' => 'nullable|numeric|min:0|max:5',
        'criterios.*.comentario' => 'nullable|string',
        'criterios.*.valoracion' => 'nullable|string|in:excelente,aceptable,deficiente',
        'firma' => 'nullable|string',
    ]);

    $usuario = Auth::user();
    if (!$usuario->profesor) {
        return response()->json(['success' => false, 'message' => 'Evaluador no encontrado.'], 400);
    }

    $profesorId = $usuario->profesor->id_profesor;

    // Verificar que el evaluador haya aceptado el trabajo
    $miDecision = DB::table('trabajo_profesor')
        ->where('id_trabajo', $id_trabajo)
        ->where('id_profesor', $profesorId)
        ->value('decision_evaluador');

    if ($miDecision !== 'aceptado') {
        return response()->json(['success' => false, 'message' => 'Debe aceptar el trabajo antes de evaluarlo.'], 403);
    }

    // Determinar slot del lado del servidor (seguro) - ignoramos el que envía el cliente
    $slot = $this->getEvaluadorSlot($trabajo->id_trabajo, $profesorId);

    // Buscar evaluación existente por id_trabajo (compartida) o crear una nueva
    $evaluacion = Evaluacion::where('id_trabajo', $trabajo->id_trabajo)->first();

    if (!$evaluacion) {
        $evaluacion = new Evaluacion();
        $evaluacion->id_trabajo = $trabajo->id_trabajo;
    }

    // Si la evaluación anterior estaba completada (nuevo ciclo: propuesta→trabajo_de_grado, etc.)
    // limpiar firmas y datos del ciclo anterior
    if ($evaluacion->evaluacion_completada) {
        $evaluacion->firma = null;
        $evaluacion->firma_evaluador_2 = null;
        $evaluacion->evaluacion_completada = false;
    }

    // Actualizar campos compartidos
    $evaluacion->tipo_plantilla = $data['tipo_plantilla'];
    $evaluacion->id_profesor = $profesorId; // Último evaluador que guardó
    $evaluacion->nota_final = $data['nota_final'];
    $evaluacion->resultado = $data['resultado'];
    $evaluacion->observaciones_globales = $data['observaciones_globales'];
    $evaluacion->criterios = $data['criterios'] ?? [];

    // Guardar firma según el slot del evaluador
    // Validar que la firma sea un data URL con contenido real (no solo el prefijo)
    $firmaValida = !empty($data['firma']) && strlen($data['firma']) > 100;
    if ($firmaValida) {
        if ($slot === 1) {
            $evaluacion->firma = $data['firma'];
        } else {
            $evaluacion->firma_evaluador_2 = $data['firma'];
        }
    }

    // Marcar estado_revision como Finalizado en la pivot
    DB::table('trabajo_profesor')
        ->where('id_trabajo', $trabajo->id_trabajo)
        ->where('id_profesor', $profesorId)
        ->update(['estado_revision' => 'Finalizado']);

    // Verificar si AMBOS evaluadores finalizaron (fuente de verdad: estado_revision en pivot)
    $totalEvaluadores = DB::table('trabajo_profesor')
        ->where('id_trabajo', $trabajo->id_trabajo)
        ->count();
    $finalizados = DB::table('trabajo_profesor')
        ->where('id_trabajo', $trabajo->id_trabajo)
        ->where('estado_revision', 'Finalizado')
        ->count();
    $ambosFinalizados = $totalEvaluadores >= 2 && $finalizados >= $totalEvaluadores;

    // evalucion_completada se basa en AMBOS evaluadores con firma Y estado_revision Finalizado
    $evaluacion->evaluacion_completada = $ambosFinalizados
        && !empty($evaluacion->firma)
        && !empty($evaluacion->firma_evaluador_2);
    $evaluacion->save();

    // Registrar en el historial de estados
    $tipoLabel = match($data['tipo_plantilla']) {
        'propuesta_de_grado' => 'Propuesta',
        'trabajo_de_grado' => 'Trabajo de Grado',
        'pasantia' => 'Pasantía',
        default => 'Documento'
    };
    \App\Models\HistorialEstado::create([
        'trabajo_grado_id' => $trabajo->id_trabajo,
        'estado' => $ambosFinalizados ? 'evaluacion_completada' : 'evaluado',
        'version_documento' => $trabajo->version_actual ?? 'v1',
        'user_id' => $usuario->id_usuario,
        'observacion_estado' => $ambosFinalizados
            ? "{$tipoLabel} evaluada por AMBOS evaluadores. Resultado: {$data['resultado']} (Nota: {$data['nota_final']})"
            : "{$tipoLabel} evaluada por {$usuario->nombre} {$usuario->apellido} (pendiente firma del otro evaluador). Resultado: {$data['resultado']} (Nota: {$data['nota_final']})",
    ]);

    if ($totalEvaluadores > 0 && $finalizados >= $totalEvaluadores) {
        try {
            $gestores = Usuario::where('rol', 'Gestor')->where('activo', true)->get();
            foreach ($gestores as $gestor) {
                $gestor->notify(new PropuestaEvaluada($trabajo));
            }
        } catch (\Throwable $e) {
            \Log::error('Error al notificar gestores: ' . $e->getMessage());
        }
    }

    return response()->json([
        'success' => true,
        'nota_final' => $data['nota_final'],
        'resultado' => $data['resultado'],
        'evaluacion_completada' => $evaluacion->evaluacion_completada,
    ]);
}

public function guardarProgreso(Request $request, $id_trabajo)
{
    $trabajo = Trabajo::findOrFail($id_trabajo);

    $data = $request->validate([
        'tipo_plantilla' => 'required|in:propuesta_de_grado,pasantia,trabajo_de_grado',
        'nota_final' => 'nullable|numeric|min:0|max:5',
        'resultado' => 'nullable|string|max:50',
        'observaciones_globales' => 'nullable|string',
        'criterios' => 'nullable|array',
        'criterios.*.id' => 'nullable|integer',
        'criterios.*.descripcion' => 'nullable|string',
        'criterios.*.calificacion' => 'nullable|numeric|min:0|max:5',
        'criterios.*.comentario' => 'nullable|string',
        'criterios.*.valoracion' => 'nullable|string|in:excelente,aceptable,deficiente',
    ]);

    $usuario = Auth::user();
    if (!$usuario->profesor) {
        return response()->json(['success' => false, 'message' => 'Evaluador no encontrado.'], 400);
    }

    $profesorId = $usuario->profesor->id_profesor;

    // Verificar que el evaluador haya aceptado el trabajo
    $miDecision = DB::table('trabajo_profesor')
        ->where('id_trabajo', $id_trabajo)
        ->where('id_profesor', $profesorId)
        ->value('decision_evaluador');

    if ($miDecision !== 'aceptado') {
        return response()->json(['success' => false, 'message' => 'Debe aceptar el trabajo antes de guardar progreso.'], 403);
    }

    // Buscar evaluación existente por id_trabajo (compartida) o crear una nueva
    $evaluacion = Evaluacion::where('id_trabajo', $trabajo->id_trabajo)->first();

    if (!$evaluacion) {
        $evaluacion = new Evaluacion();
        $evaluacion->id_trabajo = $trabajo->id_trabajo;
    }

    // Actualizar campos compartidos (sin cambiar estado_revision ni firmas)
    $evaluacion->tipo_plantilla = $data['tipo_plantilla'];
    $evaluacion->id_profesor = $profesorId;
    $evaluacion->nota_final = $data['nota_final'] ?? null;
    $evaluacion->resultado = $data['resultado'] ?? null;
    $evaluacion->observaciones_globales = $data['observaciones_globales'] ?? null;
    $evaluacion->criterios = $data['criterios'] ?? [];

    $evaluacion->save();

    return response()->json(['success' => true, 'message' => 'Progreso guardado correctamente.']);
}

public function aceptarTerminos(Request $request)
{
    $usuario = Auth::user();
    if (!$usuario->profesor) {
        return response()->json(['success' => false, 'message' => 'Evaluador no encontrado.'], 400);
    }

    $usuario->profesor->update([
        'terminos_aceptados' => true,
        'datos_aceptados' => true,
    ]);

    return response()->json(['success' => true]);
}

public function detallesEvaluacion($id)
{
    $usuario = Auth::user();
    if (!$usuario->profesor) {
        return redirect('/')->with('error', 'Tu cuenta no está vinculada correctamente.');
    }

    // Cargar la evaluación compartida (única por id_trabajo)
    $evaluacion = Evaluacion::where('id_trabajo', $id)
        ->with(['trabajo.tipo', 'trabajo.estudiante', 'trabajo.directores'])
        ->firstOrFail();

    // Cargar evaluadores asignados
    $trabajo = $evaluacion->trabajo;
    $trabajo->load('evaluadores.usuario');

    // Ordenar evaluadores por fecha de asignación
    $evaluadoresOrdenados = $trabajo->evaluadores->sortBy('pivot.fecha_asignacion')->values();
    $evaluador1 = $evaluadoresOrdenados->get(0);
    $evaluador2 = $evaluadoresOrdenados->get(1);

    return view('evaluador.detallesEvaluacion', compact('usuario', 'evaluacion', 'evaluador1', 'evaluador2'));
}

public function rubricaPDF($id)
{
    $usuario = Auth::user();
    if (!$usuario->profesor) {
        abort(403);
    }

    // Cargar la evaluación compartida (única por id_trabajo)
    $evaluacion = Evaluacion::where('id_trabajo', $id)
        ->with(['trabajo.tipo', 'trabajo.estudiante', 'trabajo.directores'])
        ->firstOrFail();

    // Cargar evaluadores asignados
    $trabajo = $evaluacion->trabajo;
    $trabajo->load('evaluadores.usuario');

    $evaluadoresOrdenados = $trabajo->evaluadores->sortBy('pivot.fecha_asignacion')->values();
    $evaluador1 = $evaluadoresOrdenados->get(0);
    $evaluador2 = $evaluadoresOrdenados->get(1);

    return view('evaluador.rubrica_pdf', compact('usuario', 'evaluacion', 'evaluador1', 'evaluador2'));
}

public function revisarTrabajo($id)
{
    $usuario = Auth::user();
    if (!$usuario->profesor) {
        abort(403);
    }

    $trabajo = Trabajo::with(['tipo', 'estudiante', 'directores'])
        ->where('id_trabajo', $id)
        ->firstOrFail();

    $trabajo->load('evaluadores.usuario');

    // Obtener datos del pivot del evaluador actual
    $miPivot = DB::table('trabajo_profesor')
        ->where('id_trabajo', $id)
        ->where('id_profesor', $usuario->profesor->id_profesor)
        ->first();

    $miDecision = $miPivot->decision_evaluador ?? null;
    $fechaLimite = $miPivot->fecha_limite_revision ?? null;

    return view('evaluador.revisarTrabajo', compact('usuario', 'trabajo', 'miDecision', 'fechaLimite'));
}

public function aceptarTrabajo($id)
{
    $usuario = Auth::user();
    if (!$usuario->profesor) {
        return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
    }

    $updated = DB::table('trabajo_profesor')
        ->where('id_trabajo', $id)
        ->where('id_profesor', $usuario->profesor->id_profesor)
        ->update(['decision_evaluador' => 'aceptado']);

    if (!$updated) {
        return response()->json(['success' => false, 'message' => 'No se encontró la asignación.'], 404);
    }

    $trabajo = Trabajo::findOrFail($id);
    $nombreEvaluador = $usuario->profesor->nombre . ' ' . $usuario->profesor->apellido;

    // Notificar a gestores y administradores
    $destinatarios = Usuario::where('activo', true)
        ->whereIn('rol', ['Gestor', 'Administrador'])
        ->get();
    foreach ($destinatarios as $destinatario) {
        $url = $destinatario->rol === 'Gestor'
            ? route('gestor.trabajo.detalles', $trabajo->id_trabajo)
            : route('admin.detallesTrabajo', $trabajo->id_trabajo);
        $destinatario->notify(new TrabajoAceptado($trabajo, $nombreEvaluador, $url));
    }

    return response()->json(['success' => true, 'message' => 'Trabajo aceptado.']);
}

public function rechazarTrabajo(Request $request, $id)
{
    $usuario = Auth::user();
    if (!$usuario->profesor) {
        return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
    }

    $request->validate([
        'motivo' => 'required|string|max:500',
    ]);

    $updated = DB::table('trabajo_profesor')
        ->where('id_trabajo', $id)
        ->where('id_profesor', $usuario->profesor->id_profesor)
        ->update([
            'decision_evaluador' => 'rechazado',
            'motivo_rechazo' => $request->motivo,
        ]);

    if (!$updated) {
        return response()->json(['success' => false, 'message' => 'No se encontró la asignación.'], 404);
    }

    $trabajo = Trabajo::with('evaluadores')->findOrFail($id);
    $nombreEvaluador = $usuario->profesor->nombre . ' ' . $usuario->profesor->apellido;

    // Notificar a gestores y administradores
    $destinatarios = Usuario::where('activo', true)
        ->whereIn('rol', ['Gestor', 'Administrador'])
        ->get();
    foreach ($destinatarios as $destinatario) {
        $url = $destinatario->rol === 'Gestor'
            ? route('gestor.trabajo.detalles', $trabajo->id_trabajo)
            : route('admin.detallesTrabajo', $trabajo->id_trabajo);
        $destinatario->notify(new TrabajoRechazado($trabajo, $nombreEvaluador, $request->motivo, $url));
    }

    return response()->json(['success' => true, 'message' => 'Trabajo rechazado. Se ha notificado al administrador y gestor.']);
}

}