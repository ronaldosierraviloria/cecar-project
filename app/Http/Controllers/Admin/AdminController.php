<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Trabajo;
use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Facultad;
use App\Models\Area;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Notifications\EvaluadorAsignado;
use App\Notifications\PlazoExtendido;
use App\Notifications\TrabajoAprobado;
use App\Notifications\TrabajoRetirado;
use App\Notifications\TrabajoReactivado;
use App\Notifications\TrabajoEliminado;
use App\Notifications\TrabajoRetiradoEvaluador;
use App\Notifications\TrabajoEliminadoEvaluador;

class AdminController extends Controller
{
    /**
     * Muestra el dashboard principal del administrador.
     */
    public function index()
    {
        $usuario = Auth::user();
        $trabajos = Trabajo::with(['estudiante.area.facultad', 'tipo', 'evaluadores'])->get();
        $facultades = Facultad::orderBy('nombre_facultad')->get();
        $areas = Area::orderBy('nombre_area')->get();

        // ── KPIs adicionales ──
        $totalEstudiantes = \App\Models\Estudiante::count();
        $totalEvaluadores = Profesor::whereHas('usuario', fn($q) => $q->where('rol', 'Evaluador'))->count();
        $totalGestores = Usuario::where('rol', 'Gestor')->count();
        $totalDirectores = \App\Models\Director::count();
        $totalFacultades = Facultad::count();
        $totalAreas = Area::count();

        $aprobados = $trabajos->where('estado', 'aprobado')->count();
        $enRevision = $trabajos->where('estado', 'en_revision')->count();
        $subidos = $trabajos->where('estado', 'subido')->count();

        // ── Datos para gráficos ──
        $chartTipoLabels = [];
        $chartTipoData = [];
        $tiposAgrupados = $trabajos->groupBy(fn($t) => optional($t->tipo)->nombre_tipo ?? 'Sin tipo');
        foreach ($tiposAgrupados as $tipo => $items) {
            $chartTipoLabels[] = $tipo;
            $chartTipoData[] = $items->count();
        }

        $chartMesesLabels = [];
        $chartMesesData = [];
        $mesesAgrupados = $trabajos->groupBy(fn($t) => Carbon::parse($t->fecha_subida)->format('Y-m'));
        $mesesOrdenados = $mesesAgrupados->sortKeys();
        foreach ($mesesOrdenados as $mes => $items) {
            $chartMesesLabels[] = \Carbon\Carbon::createFromFormat('Y-m', $mes)->locale('es')->isoFormat('MMM YYYY');
            $chartMesesData[] = $items->count();
        }

        $sinAsignar = $trabajos->filter(fn($t) => $t->evaluadores->count() === 0)->count();
        $conAsignar = $trabajos->count() - $sinAsignar;

        $chartEstadoLabels = ['Con Evaluadores', 'Sin Evaluadores'];
        $chartEstadoData = [$conAsignar, $sinAsignar];

        // ── Gráfico: Trabajos por estado ──
        $chartStatusLabels = ['Subidos', 'En Revisión', 'Aprobados'];
        $chartStatusData = [$subidos, $enRevision, $aprobados];

        return view('admin.dashboard', compact(
            'usuario',
            'trabajos',
            'chartTipoLabels',
            'chartTipoData',
            'chartMesesLabels',
            'chartMesesData',
            'chartEstadoLabels',
            'chartEstadoData',
            'chartStatusLabels',
            'chartStatusData',
            'facultades',
            'areas',
            'totalEstudiantes',
            'totalEvaluadores',
            'totalGestores',
            'totalDirectores',
            'totalFacultades',
            'totalAreas',
            'aprobados',
            'enRevision',
            'subidos'
        ));
    }

    /**
     * Muestra la tabla de trabajos y filtros.
     */
    public function trabajos()
    {
        $usuario = Auth::user();
        $trabajos = Trabajo::with(['estudiante.area.facultad', 'tipo', 'evaluadores'])->get();
        $facultades = Facultad::orderBy('nombre_facultad')->get();
        $areas = Area::orderBy('nombre_area')->get();

        return view('admin.trabajos', compact(
            'usuario',
            'trabajos',
            'facultades',
            'areas'
        ));
    }

    /**
     * Muestra la interfaz para asignar evaluadores (profesores) a un trabajo de grado.
     */
    public function asignarEvaluador($trabajo_id)
    {
        $usuario = Auth::user();

        $trabajo = Trabajo::with([
            'estudiante.area.facultad',
            'tipo',
            'evaluadores' => function ($query) {
                $query->withCount('trabajos');
            },
            'evaluadores.usuario',
            'evaluadores.area.facultad',
        ])->findOrFail($trabajo_id);

        // IDs de áreas de los estudiantes para auto-filtrado
        $studentAreaIds = $trabajo->estudiante
            ->map(fn($est) => optional($est->area)->id_area)
            ->filter()->unique()->values();

        // IDs de evaluadores ya asignados a este trabajo
        $evaluadoresAsignadosIds = $trabajo->evaluadores->pluck('id_profesor')->all();

        // Todos los evaluadores con rol 'Evaluador'
        $evaluadores = Profesor::whereHas('usuario', function ($query) {
            $query->where('rol', 'Evaluador');
        })->with(['usuario', 'area.facultad'])->withCount('trabajos')->orderBy('id_profesor')->get();

        // Separar asignados, y filtrar por área del estudiante el resto
        $evaluadoresSinAsignar = $evaluadores->reject(
            fn($ev) => in_array($ev->id_profesor, $evaluadoresAsignadosIds)
        );

        if ($studentAreaIds->isNotEmpty()) {
            $evaluadoresSinAsignar = $evaluadoresSinAsignar->whereIn('id_area', $studentAreaIds->all());
        }

        // Asignados: los que ya están en este trabajo (con pivot data)
        $evaluadoresAsignados = $trabajo->evaluadores;
        $isEditing = $evaluadoresAsignados->isNotEmpty();

        $evaluadoresDisponibles = $evaluadoresSinAsignar->values();

        $evaluadoresNoDisponibles = $evaluadores
            ->reject(fn($ev) => in_array($ev->id_profesor, $evaluadoresAsignadosIds))
            ->values();

        if ($studentAreaIds->isNotEmpty()) {
            $evaluadoresNoDisponibles = $evaluadoresNoDisponibles
                ->reject(fn($ev) => in_array($ev->id_area, $studentAreaIds->all()))
                ->values();
        }

        $evaluadoresCatalogo = $evaluadoresDisponibles
            ->merge($evaluadoresAsignados)
            ->keyBy('id_profesor')
            ->map(function ($ev) use ($evaluadoresAsignadosIds) {
                return [
                    'id' => $ev->id_profesor,
                    'nombre' => trim(($ev->usuario->nombre ?? '') . ' ' . ($ev->usuario->apellido ?? '')),
                    'correo' => $ev->usuario->correo ?? '',
                    'iniciales' => strtoupper(substr($ev->usuario->nombre ?? 'N', 0, 1) . substr($ev->usuario->apellido ?? '', 0, 1)),
                    'facultad' => optional(optional($ev->area)->facultad)->nombre_facultad ?? 'N/A',
                    'area' => $ev->area->nombre_area ?? 'N/A',
                    'carga' => $ev->trabajos_count,
                    'ya_asignado' => in_array($ev->id_profesor, $evaluadoresAsignadosIds, true),
                    'fecha_asignacion' => $ev->pivot->fecha_asignacion ?? null,
                    'decision_evaluador' => $ev->pivot->decision_evaluador ?? null,
                    'motivo_rechazo' => $ev->pivot->motivo_rechazo ?? null,
                ];
            })
            ->values();

        $facultades = \App\Models\Facultad::with('areas')->get();

        return view('admin.asignarEvaluador', compact(
            'usuario', 'trabajo', 'evaluadoresDisponibles', 'evaluadoresNoDisponibles',
            'evaluadoresAsignados', 'evaluadoresAsignadosIds', 'evaluadoresCatalogo',
            'isEditing', 'facultades', 'studentAreaIds'
        ));
    }

    /**
     * Guarda la asignación de evaluadores (profesores) para un trabajo de grado.
     */
    public function guardarEvaluadores(Request $request, $trabajo_id)
    {
        $trabajo = Trabajo::with('evaluadores')->findOrFail($trabajo_id);
        $teniaEvaluadores = $trabajo->evaluadores->isNotEmpty();
        $evaluadoresAnteriores = $trabajo->evaluadores->keyBy('id_profesor');
        $evaluadoresSeleccionados = array_values(array_unique($request->input('evaluadores', [])));

        $rules = [
            'evaluadores' => $teniaEvaluadores ? 'nullable|array|max:2' : 'required|array|min:1|max:2',
            'evaluadores.*' => 'exists:profesor,id_profesor',
        ];

        $request->validate($rules, [
            'evaluadores.required' => 'Debes seleccionar al menos un evaluador.',
            'evaluadores.min' => 'Debes seleccionar al menos un evaluador.',
            'evaluadores.max' => 'Solo puedes seleccionar un máximo de 2 evaluadores.',
            'evaluadores.*.exists' => 'Uno de los evaluadores seleccionados no existe como Profesor.',
        ]);

        try {
            DB::beginTransaction();

            $fechaAsignacion = Carbon::now();
            $fechaLimite = $fechaAsignacion->copy()->addDays(21);
            $defaultPivotData = [
                'fecha_asignacion' => $fechaAsignacion,
                'fecha_limite_revision' => $fechaLimite,
                'estado_revision' => 'Pendiente',
            ];

            if (empty($evaluadoresSeleccionados)) {
                $trabajo->evaluadores()->detach();

                if ($trabajo->estado === 'en_revision') {
                    $trabajo->update(['estado' => 'subido']);
                }

                \App\Models\HistorialEstado::create([
                    'trabajo_grado_id' => $trabajo->id_trabajo,
                    'estado' => $trabajo->estado,
                    'version_documento' => $trabajo->version_actual ?? 'v1',
                    'user_id' => Auth::id(),
                    'observacion_estado' => 'Evaluadores removidos del proyecto.',
                ]);

                DB::commit();

                return redirect()->route('admin.detallesTrabajo', $trabajo_id)
                    ->with('success', 'Evaluadores removidos correctamente.');
            }

            $dataToSync = [];
            $nuevosEvaluadores = [];

            foreach ($evaluadoresSeleccionados as $profesorId) {
                if ($evaluadoresAnteriores->has($profesorId)) {
                    $existente = $evaluadoresAnteriores->get($profesorId);
                    $dataToSync[$profesorId] = [
                        'fecha_asignacion' => $existente->pivot->fecha_asignacion,
                        'fecha_limite_revision' => $existente->pivot->fecha_limite_revision,
                        'estado_revision' => $existente->pivot->estado_revision,
                    ];
                } else {
                    $dataToSync[$profesorId] = $defaultPivotData;
                    $nuevosEvaluadores[] = $profesorId;
                }
            }

            $trabajo->evaluadores()->sync($dataToSync);
            $trabajo->update(['estado' => 'en_revision']);

            $observacion = $teniaEvaluadores
                ? 'Asignación de evaluadores actualizada.'
                : 'Evaluadores asignados. Proyecto entra en revisión.';

            \App\Models\HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado' => 'en_revision',
                'version_documento' => $trabajo->version_actual ?? 'v1',
                'user_id' => Auth::id(),
                'observacion_estado' => $observacion,
            ]);

            DB::commit();

            $trabajo->load('evaluadores.usuario');
            $idsNuevosEvaluadores = array_map('intval', $nuevosEvaluadores);

            foreach ($trabajo->evaluadores as $evaluador) {
                $idEvaluador = (int) $evaluador->id_profesor;

                if (in_array($idEvaluador, $idsNuevosEvaluadores, true) && $evaluador->usuario) {
                    $evaluador->usuario->notify(new EvaluadorAsignado($trabajo, $fechaLimite));
                }
            }

            $mensaje = $teniaEvaluadores
                ? 'Asignación de evaluadores actualizada correctamente.'
                : 'Evaluadores asignados correctamente. La fecha límite de revisión es el ' . $fechaLimite->format('d/m/Y') . '.';

            return redirect()->route('admin.detallesTrabajo', $trabajo_id)->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Hubo un error al guardar la asignación: ' . $e->getMessage());
        }
    }
    public function listaEstudiantes(Request $request)
    {
        $usuario = Auth::user();

        $query = Estudiante::with('area.facultad', 'trabajo');

        if ($request->filled('id_facultad')) {
            $query->whereHas('area', fn($q) => $q->where('id_facultad', $request->id_facultad));
        }

        if ($request->filled('id_area')) {
            $query->where('id_area', $request->id_area);
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('apellido', 'like', "%{$busqueda}%")
                  ->orWhere('correo', 'like', "%{$busqueda}%");
            });
        }

        $estudiantes = $query->orderBy('apellido')->orderBy('nombre')->get();
        $facultades = Facultad::orderBy('nombre_facultad')->get();
        $areas = Area::orderBy('nombre_area')->get();

        return view('admin.listaEstudiantes', compact('usuario', 'estudiantes', 'facultades', 'areas'));
    }

    public function detallesTrabajo($id)
    {
        $usuario = Auth::user();
        $trabajo = Trabajo::with(['estudiante.area', 'tipo', 'evaluadores.usuario', 'rubricas', 'historialEstados.usuario', 'directores', 'evaluaciones.profesor.usuario'])->findOrFail($id);
        
        // Inicializar el historial de estados para proyectos creados antes de esta implementación (datos legados)
        if ($trabajo->historialEstados->isEmpty()) {
            // 1. Hito 'subido' inicial
            \App\Models\HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado' => 'subido',
                'version_documento' => 'v1',
                'user_id' => $usuario->id_usuario, // Administrador que visualiza y corrige el registro
                'observacion_estado' => 'Proyecto registrado en la plataforma (Historial inicializado automáticamente).',
                'created_at' => $trabajo->created_at ?? now(),
                'updated_at' => $trabajo->created_at ?? now(),
            ]);

            // 2. Hito 'en_revision' si ya cuenta con evaluadores
            if ($trabajo->evaluadores->count() > 0) {
                $fechaAsignacion = $trabajo->evaluadores->first()->pivot->fecha_asignacion ?? now();
                \App\Models\HistorialEstado::create([
                    'trabajo_grado_id' => $trabajo->id_trabajo,
                    'estado' => 'en_revision',
                    'version_documento' => 'v1',
                    'user_id' => $usuario->id_usuario,
                    'observacion_estado' => 'Evaluadores asignados para revisión (Historial inicializado automáticamente).',
                    'created_at' => $fechaAsignacion,
                    'updated_at' => $fechaAsignacion,
                ]);
            }

            // Recargar la relación para mostrarla en la vista inmediatamente
            $trabajo->load('historialEstados.usuario');
        }
        
        return view('admin.detallesTrabajo', compact('usuario', 'trabajo'));
    }

    public function eliminarEstudiante($id)
    {
        try {
            $estudiante = \App\Models\Estudiante::findOrFail($id);
            $estudiante->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Prorroga el plazo de revisión para un evaluador de un trabajo específico.
     */
    public function prorrogarPlazo(Request $request)
    {
        $request->validate([
            'id_trabajo' => 'required|exists:trabajo,id_trabajo',
            'id_profesor' => 'required|exists:profesor,id_profesor',
            'dias' => 'required|integer|min:1|max:90',
        ]);

        try {
            $trabajo = Trabajo::findOrFail($request->id_trabajo);
            $evaluador = $trabajo->evaluadores()
                ->where('trabajo_profesor.id_profesor', $request->id_profesor)
                ->firstOrFail();

            // Usar la fecha actual si la fecha límite ya venció, sino añadir sobre la fecha límite actual
            $currentDeadline = Carbon::parse($evaluador->pivot->fecha_limite_revision);
            if ($currentDeadline->isPast()) {
                $newDeadline = Carbon::now()->addDays($request->dias);
            } else {
                $newDeadline = $currentDeadline->addDays($request->dias);
            }

            $trabajo->evaluadores()->updateExistingPivot($request->id_profesor, [
                'fecha_limite_revision' => $newDeadline
            ]);

            // ── Notificar al evaluador del plazo extendido ──
            $profesor = Profesor::find($request->id_profesor);
            if ($profesor && $profesor->usuario) {
                $profesor->usuario->notify(new PlazoExtendido($trabajo, $newDeadline));
            }

            $diasRestantes = (int) Carbon::now()->diffInDays($newDeadline, false);

            return response()->json([
                'success' => true,
                'nueva_fecha' => $newDeadline->format('d/m/Y'),
                'nueva_fecha_larga' => $newDeadline->format('d \d\e F, Y'),
                'dias_restantes' => $diasRestantes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al prorrogar el plazo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprueba oficialmente el trabajo de grado y registra el hito.
     */
    public function aprobarTrabajo(Request $request, $id)
    {
        $trabajo = Trabajo::findOrFail($id);

        $trabajo->update(['estado' => 'aprobado']);

        \App\Models\HistorialEstado::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'estado' => 'aprobado',
            'version_documento' => $trabajo->version_actual ?? 'v1',
            'user_id' => Auth::id(),
            'observacion_estado' => $request->input('observacion_estado') ?? 'El proyecto de grado ha sido aprobado oficialmente.',
        ]);

        // ── Notificar a todos los Gestores ──
        $gestores = Usuario::where('rol', 'Gestor')->where('activo', true)->get();
        foreach ($gestores as $gestor) {
            $gestor->notify(new TrabajoAprobado($trabajo));
        }

        return redirect()->route('admin.detallesTrabajo', $id)->with('success', 'El proyecto de grado ha sido aprobado con éxito.');
    }

    public function quitarEvaluadores($id)
    {
        $trabajo = Trabajo::findOrFail($id);

        if ($trabajo->evaluadores->count() === 0) {
            return redirect()->route('admin.detallesTrabajo', $id)
                ->with('error', 'El proyecto no tiene evaluadores asignados.');
        }

        DB::beginTransaction();
        try {
            $trabajo->evaluadores()->detach();

            if ($trabajo->estado === 'en_revision') {
                $trabajo->update(['estado' => 'subido']);
            }

            \App\Models\HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado' => $trabajo->estado,
                'version_documento' => $trabajo->version_actual ?? 'v1',
                'user_id' => Auth::id(),
                'observacion_estado' => 'Evaluadores removidos del proyecto.',
            ]);

            DB::commit();

            return redirect()->route('admin.detallesTrabajo', $id)
                ->with('success', 'Evaluadores removidos correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.detallesTrabajo', $id)
                ->with('error', 'Error al remover evaluadores: ' . $e->getMessage());
        }
    }

    public function retirar($id)
    {
        $trabajo = Trabajo::findOrFail($id);

        $retirado = !$trabajo->retirado;
        $trabajo->update(['retirado' => $retirado]);

        \App\Models\HistorialEstado::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'estado' => $trabajo->estado,
            'version_documento' => $trabajo->version_actual ?? 'v1',
            'user_id' => Auth::id(),
            'observacion_estado' => $retirado
                ? 'Proyecto retirado por el administrador.'
                : 'Proyecto reactivado por el administrador.',
        ]);

        // ── Notificar a todos los Administradores ──
        $nombreActor = 'El administrador ' . Auth::user()->nombre . ' ' . Auth::user()->apellido;
        $admins = Usuario::where('rol', 'Administrador')->where('activo', true)->get();
        foreach ($admins as $admin) {
            if ($retirado) {
                $admin->notify(new TrabajoRetirado($trabajo, $nombreActor));
            } else {
                $admin->notify(new TrabajoReactivado($trabajo, $nombreActor));
            }
        }

        // ── Notificar a los evaluadores asignados si el proyecto fue retirado ──
        if ($retirado) {
            $evaluadores = $trabajo->evaluadores;
            foreach ($evaluadores as $evaluador) {
                if ($evaluador->usuario) {
                    $evaluador->usuario->notify(new TrabajoRetiradoEvaluador($trabajo, $nombreActor));
                }
            }
        }

        $mensaje = $retirado
            ? 'Proyecto retirado correctamente.'
            : 'Proyecto reactivado correctamente.';

        return redirect()->route('admin.detallesTrabajo', $id)
            ->with('success', $mensaje);
    }

    public function eliminarTrabajo($id)
    {
        $trabajo = Trabajo::findOrFail($id);

        if (!$trabajo->retirado) {
            return redirect()->route('admin.detallesTrabajo', $id)
                ->with('error', 'No se puede eliminar un proyecto activo. Debe retirarlo primero.');
        }

        // ── Notificar a todos los Administradores antes de eliminar ──
        $nombreActor = 'El administrador ' . Auth::user()->nombre . ' ' . Auth::user()->apellido;
        $admins = Usuario::where('rol', 'Administrador')->where('activo', true)->get();
        foreach ($admins as $admin) {
            $admin->notify(new TrabajoEliminado($trabajo, $nombreActor));
        }

        // ── Notificar a los evaluadores asignados antes de eliminar ──
        $evaluadores = $trabajo->evaluadores;
        foreach ($evaluadores as $evaluador) {
            if ($evaluador->usuario) {
                $evaluador->usuario->notify(new TrabajoEliminadoEvaluador($trabajo, $nombreActor));
            }
        }

        DB::transaction(function () use ($trabajo) {
            // Detach pivot relationships
            $trabajo->evaluadores()->detach();
            $trabajo->rubricas()->detach();
            $trabajo->directores()->detach();

            // Delete other related records that don't cascade delete
            DB::table('trabajo_estudiante')->where('id_trabajo', $trabajo->id_trabajo)->delete();
            DB::table('alerta')->where('id_trabajo', $trabajo->id_trabajo)->delete();
            DB::table('seguimiento')->where('id_trabajo', $trabajo->id_trabajo)->delete();

            // Delete retroalimentaciones and historialEstados
            $trabajo->retroalimentaciones()->delete();
            $trabajo->historialEstados()->delete();

            // Delete students
            DB::table('estudiante')->where('id_trabajo', $trabajo->id_trabajo)->delete();

            // Delete PDF file
            $relative = preg_replace('#^storage/#', '', $trabajo->archivo_pdf);
            if (\Illuminate\Support\Facades\Storage::disk('supabase')->exists($relative)) {
                \Illuminate\Support\Facades\Storage::disk('supabase')->delete($relative);
            }

            // Finally, delete the project
            $trabajo->delete();
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Proyecto eliminado permanentemente.');
    }
}

