<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Gestor\GestorController;
use App\Http\Controllers\Gestor\TrabajoController;
use App\Http\Controllers\Evaluador\EvaluadorController;
use App\Http\Controllers\Evaluador\ControllerEvaluador;
use App\Http\Controllers\Gestor\AsignarRubricaController;
use App\Http\Controllers\Admin\AdminGestorController;
use App\Http\Controllers\Admin\AdminAreaController;
use App\Http\Controllers\Admin\AdminFacultadController;
use App\Http\Controllers\Admin\AdminTipoTrabajoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificacionController;


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'check.activo'])->group(function () {
    Route::post('/session/ping', function () {
        return response()->json(['status' => 'alive']);
    })->name('session.ping');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/trabajos', [AdminController::class, 'trabajos'])->name('admin.trabajos');
    Route::get('/admin/asignar-evaluador/{trabajo_id}', [AdminController::class, 'asignarEvaluador'])->name('admin.asignarEvaluador');
    Route::post('/admin/guardar-evaluadores/{trabajo_id}', [AdminController::class, 'guardarEvaluadores'])->name('admin.guardarEvaluador');
    Route::post('/evaluadores/guardar/{id}', [EvaluadorController::class, 'guardarEvaluadores'])->name('evaluadores.guardar');
    
    // Usuarios (Admin, Gestor, Evaluador)
    Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::post('/admin/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
    Route::put('/admin/usuarios/{id}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');
    Route::post('/admin/usuarios/{id}/toggle', [UsuarioController::class, 'toggleActive'])->name('admin.usuarios.toggle');
    
    // Proyectos (Admin)
    Route::get('/admin/proyecto/{id}', [AdminController::class, 'detallesTrabajo'])->name('admin.detallesTrabajo');
    Route::delete('/admin/estudiante/eliminar/{id}', [AdminController::class, 'eliminarEstudiante'])->name('admin.eliminarEstudiante');
    Route::post('/admin/trabajo-evaluador/prorrogar', [AdminController::class, 'prorrogarPlazo'])->name('admin.evaluador.prorrogar');
    Route::post('/admin/trabajo/{id}/aprobar', [AdminController::class, 'aprobarTrabajo'])->name('admin.trabajo.aprobar');
    Route::post('/admin/trabajo/{id}/retirar', [AdminController::class, 'retirar'])->name('admin.trabajo.retirar');
    Route::post('/admin/trabajo/{id}/quitar-evaluadores', [AdminController::class, 'quitarEvaluadores'])->name('admin.trabajo.quitarEvaluadores');
    Route::delete('/admin/trabajo/{id}/eliminar', [AdminController::class, 'eliminarTrabajo'])->name('admin.trabajo.eliminar');
    
    // Lista de Estudiantes
    Route::get('/admin/lista-estudiantes', [AdminController::class, 'listaEstudiantes'])->name('admin.listaEstudiantes');

    // Facultades y Áreas de Especialidad
    Route::get('/admin/facultades-areas', [AdminAreaController::class, 'index'])->name('admin.listaAreas');
    Route::post('/admin/agregar-area', [AdminAreaController::class, 'store'])->name('admin.area.store');
    Route::put('/admin/area/{id}', [AdminAreaController::class, 'update'])->name('admin.area.update');
    Route::delete('/admin/eliminar-area/{id}', [AdminAreaController::class, 'destroy'])->name('admin.area.destroy');
    
    // Rutas para Facultades
    Route::post('/admin/agregar-facultad', [AdminFacultadController::class, 'store'])->name('admin.facultad.store');
    Route::put('/admin/facultad/{id}', [AdminFacultadController::class, 'update'])->name('admin.facultad.update');
    Route::delete('/admin/eliminar-facultad/{id}', [AdminFacultadController::class, 'destroy'])->name('admin.facultad.destroy');

    // Gestión de Tipo de Trabajo
    Route::get('/admin/lista-tipo-trabajo', [AdminTipoTrabajoController::class, 'index'])->name('admin.listaTipoTrabajo');
    Route::post('/admin/agregar-tipo-trabajo', [AdminTipoTrabajoController::class, 'store'])->name('admin.tipoTrabajo.store');
    Route::put('/admin/tipo-trabajo/{id}', [AdminTipoTrabajoController::class, 'update'])->name('admin.tipoTrabajo.update');
    Route::delete('/admin/eliminar-tipo-trabajo/{id}', [AdminTipoTrabajoController::class, 'destroy'])->name('admin.tipoTrabajo.destroy');
    Route::post('/admin/tipo-trabajo/{id}/toggle', [AdminTipoTrabajoController::class, 'toggleActive'])->name('admin.tipoTrabajo.toggle');

    // Notificaciones
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.todas.leidas');
    Route::delete('/notificaciones/todas', [NotificacionController::class, 'destroyAll'])->name('notificaciones.destroyAll');

    // Perfil y Usuarios
    Route::get('/perfil', [UserController::class, 'perfil'])->name('user.perfil');
    Route::put('/perfil', [UserController::class, 'update'])->name('user.perfil.update');

    // Ruta de Prueba para Mailtrap
    Route::get('/test-mailtrap', function () {
        try {
            $destinatario = auth()->user()->nombre . ' ' . auth()->user()->apellido;
            $correo = auth()->user()->correo;
            
            \Illuminate\Support\Facades\Mail::to($correo)->send(
                new \App\Mail\EjemploMailtrap($destinatario, 'Esta es una prueba de integración con Mailtrap desde la ruta de pruebas de la plataforma.')
            );
            
            return response()->json([
                'success' => true,
                'message' => '¡Correo de prueba enviado con éxito a ' . $correo . '! Revisa tu bandeja de entrada en Mailtrap.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    })->name('test.mailtrap');
});

Route::middleware(['auth', 'check.activo'])->group(function () {
    Route::get('/gestor', [GestorController::class, 'index'])->name('gestor.dashboard');
    Route::get('/gestor/lista-evaluadores', [GestorController::class, 'listaEvaluadores'])->name('gestor.listaEvaluadores');
    Route::get('/gestor/crear-trabajo', [GestorController::class, 'crearProyecto'])->name('gestor.crear');
    Route::post('/gestor/crear-trabajo', [TrabajoController::class, 'guardar'])->name('trabajo.guardar');
    Route::get('/gestor/trabajo/archivo/{id}', [TrabajoController::class, 'archivo'])->name('gestor.trabajo.archivo'); 
    Route::delete('/gestor/trabajo/eliminar/{id}', [TrabajoController::class, 'eliminarAjax'])->name('trabajo.eliminar.ajax');
    Route::get('/gestor/trabajo/{id}/rubrica', [AsignarRubricaController::class, 'form'] )->name('gestor.rubrica.asignar');
    Route::post('/gestor/trabajo/{id}/rubrica', [AsignarRubricaController::class, 'store'])->name('gestor.rubrica.asignar.store');
    Route::get('/gestor/trabajo/{id}', [TrabajoController::class, 'detalles'])->name('gestor.trabajo.detalles');
    Route::post('/gestor/trabajo/{id}/actualizar-archivo', [TrabajoController::class, 'actualizarArchivo'])->name('gestor.trabajo.actualizarArchivo');
    Route::post('/gestor/trabajo/{id}/subir-nueva-version', [TrabajoController::class, 'subirNuevaVersion'])->name('gestor.trabajo.subirNuevaVersion');
    Route::post('/gestor/trabajo/{id}/retirar', [TrabajoController::class, 'retirar'])->name('gestor.trabajo.retirar');
    Route::get('/gestor/trabajo/{id}/subir-informe-final', [TrabajoController::class, 'subirInformeFinalForm'])->name('gestor.trabajo.informe-final');
    Route::post('/gestor/trabajo/{id}/subir-informe-final', [TrabajoController::class, 'subirInformeFinal'])->name('gestor.trabajo.informe-final.store');
});

Route::middleware(['auth', 'check.activo'])->group(function () {
    Route::get('/evaluador', [ControllerEvaluador::class, 'index'])->name('evaluador.dashboard');
    Route::get('/evaluador/calificados', [ControllerEvaluador::class, 'trabajosCalificados'])->name('evaluador.calificados');
    Route::get('/evaluador/evaluacion/{id}', function($id) {
        $usuario = auth()->user();
        $trabajo = \App\Models\Trabajo::with(['tipo', 'estudiante', 'evaluadores'])->findOrFail($id);
        
        // Cargar evaluación compartida (única por id_trabajo)
        $evaluacionPrevia = null;
        $miSlot = 1; // Por defecto evaluador 1
        
        if ($usuario->profesor) {
            $profesorId = $usuario->profesor->id_profesor;

            // Verificar que el evaluador haya aceptado el trabajo
            $miDecision = \Illuminate\Support\Facades\DB::table('trabajo_profesor')
                ->where('id_trabajo', $id)
                ->where('id_profesor', $profesorId)
                ->value('decision_evaluador');

            if ($miDecision !== 'aceptado') {
                return redirect()->route('evaluador.dashboard')
                    ->with('error', 'Debe aceptar el trabajo antes de evaluarlo.');
            }

            // Buscar evaluación existente para este trabajo (compartida)
            $evaluacionPrevia = \App\Models\Evaluacion::where('id_trabajo', $id)
                ->first();
            
            // Determinar el slot del evaluador actual (1 o 2) - se calcula del lado del servidor
            $evaluadoresAsignados = \Illuminate\Support\Facades\DB::table('trabajo_profesor')
                ->where('id_trabajo', $id)
                ->orderBy('fecha_asignacion', 'asc')
                ->orderBy('id_profesor', 'asc')
                ->pluck('id_profesor')
                ->toArray();
            
            $posicion = array_search($profesorId, $evaluadoresAsignados);
            $miSlot = ($posicion !== false) ? $posicion + 1 : 1;
        }
        
        return view('evaluador.evaluacion', compact('trabajo', 'evaluacionPrevia', 'miSlot'));
    })->name('evaluador.evaluacion.show');
    Route::get('/trabajo/archivo/{id}', [TrabajoController::class, 'archivo'])->name('trabajo.archivo');
    Route::get('/trabajos/{id}/rubrica', [ControllerEvaluador::class, 'getRubrica']);
    Route::post('/trabajos/{id}/guardar-evaluacion', [ControllerEvaluador::class, 'guardarEvaluacion'])->name('evaluador.guardar-evaluacion');
    Route::post('/trabajos/{id}/guardar-progreso', [ControllerEvaluador::class, 'guardarProgreso'])->name('evaluador.guardar-progreso');
    Route::post('/evaluador/aceptar-terminos', [ControllerEvaluador::class, 'aceptarTerminos'])->name('evaluador.aceptar-terminos');
    Route::get('/evaluador/evaluacion/{id}/detalles', [ControllerEvaluador::class, 'detallesEvaluacion'])->name('evaluador.detalles-evaluacion');
    Route::get('/evaluador/evaluacion/{id}/rubrica-pdf', [ControllerEvaluador::class, 'rubricaPDF'])->name('evaluador.rubrica-pdf');
    Route::get('/evaluador/revisar/{id}', [ControllerEvaluador::class, 'revisarTrabajo'])->name('evaluador.revisar-trabajo');
    Route::post('/evaluador/aceptar/{id}', [ControllerEvaluador::class, 'aceptarTrabajo'])->name('evaluador.aceptar-trabajo');
    Route::post('/evaluador/rechazar/{id}', [ControllerEvaluador::class, 'rechazarTrabajo'])->name('evaluador.rechazar-trabajo');
});

