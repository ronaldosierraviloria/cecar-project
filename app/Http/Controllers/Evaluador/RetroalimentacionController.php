<?php

namespace App\Http\Controllers\Evaluador;

use App\Http\Controllers\Controller;
use App\Models\Trabajo;
use App\Models\Retroalimentacion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\RetroalimentacionFinalizada;

class RetroalimentacionController extends Controller
{
    /**
     * Muestra la interfaz de retroalimentación para un trabajo específico.
     */
    public function show($id)
    {
        $usuario = Auth::user();
        $trabajo = Trabajo::with(['estudiante.area', 'tipo', 'evaluadores.usuario'])->findOrFail($id);

        // Validar que el usuario sea evaluador asignado al trabajo
        if (!$usuario->profesor || !$trabajo->evaluadores->contains('id_profesor', $usuario->profesor->id_profesor)) {
            abort(403, 'No estás asignado como evaluador para este trabajo de grado.');
        }

        // Obtener el otro evaluador asignado a este trabajo
        $otroProfesor = $trabajo->evaluadores()
            ->where('profesor.id_profesor', '!=', $usuario->profesor->id_profesor)
            ->first();
        $otroEvaluador = $otroProfesor ? $otroProfesor->usuario : null;

        // Obtener comentarios guardados de este evaluador
        $misComentarios = Retroalimentacion::with('usuario')
            ->where('trabajo_grado_id', $id)
            ->where('user_id', $usuario->id_usuario)
            ->orderBy('created_at', 'asc')
            ->get();

        // Obtener comentarios guardados del otro evaluador (si existen)
        $comentariosOtros = [];
        if ($otroEvaluador) {
            $comentariosOtros = Retroalimentacion::with('usuario')
                ->where('trabajo_grado_id', $id)
                ->where('user_id', $otroEvaluador->id_usuario)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('evaluador.retroalimentacion', compact(
            'trabajo',
            'usuario',
            'misComentarios',
            'comentariosOtros',
            'otroEvaluador'
        ));
    }

    /**
     * Retorna los comentarios del otro evaluador en formato JSON.
     */
    public function getOtherComments($id)
    {
        $usuario = Auth::user();
        $trabajo = Trabajo::with('evaluadores.usuario')->findOrFail($id);

        if (!$usuario->profesor || !$trabajo->evaluadores->contains('id_profesor', $usuario->profesor->id_profesor)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $otroProfesor = $trabajo->evaluadores()
            ->where('profesor.id_profesor', '!=', $usuario->profesor->id_profesor)
            ->first();
        $otroEvaluador = $otroProfesor ? $otroProfesor->usuario : null;

        $comentariosOtros = [];
        if ($otroEvaluador) {
            $comentariosOtros = Retroalimentacion::with('usuario')
                ->where('trabajo_grado_id', $id)
                ->where('user_id', $otroEvaluador->id_usuario)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json([
            'comentarios' => $comentariosOtros,
            'nombre' => $otroEvaluador ? $otroEvaluador->nombre . ' ' . $otroEvaluador->apellido : null,
        ]);
    }

    /**
     * Guarda una lista de comentarios (borradores) de una sola vez.
     */
    public function store(Request $request, $id)
    {
        $usuario = Auth::user();

        if (!$usuario->profesor) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $trabajo = Trabajo::findOrFail($id);

        $esEvaluador = $trabajo->evaluadores()
            ->where('profesor.id_profesor', $usuario->profesor->id_profesor)
            ->exists();

        if (!$esEvaluador) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'comentarios' => 'required|array|min:1',
            'comentarios.*' => 'required|string|max:5000',
        ]);

        $now = now();
        $rows = array_map(fn (string $comentarioText) => [
            'trabajo_grado_id' => (int) $id,
            'user_id' => $usuario->id_usuario,
            'comentario' => $comentarioText,
            'version_documento' => 'v1',
            'created_at' => $now,
            'updated_at' => $now,
        ], $validated['comentarios']);

        Retroalimentacion::insert($rows);

        $destinatarios = Usuario::whereIn('rol', ['Administrador', 'Gestor'])
            ->where('activo', true)
            ->get();

        foreach ($destinatarios as $destinatario) {
            $destinatario->notify(new \App\Notifications\RetroalimentacionEmitida($trabajo, $usuario));
        }

        $comentariosGuardados = array_map(fn (string $comentarioText) => [
            'comentario' => $comentarioText,
            'created_at' => $now->toIso8601String(),
        ], $validated['comentarios']);

        return response()->json([
            'success' => true,
            'comentarios' => $comentariosGuardados,
        ]);
    }

    /**
     * Finaliza la retroalimentación de un evaluador.
     * Solo cambia el estado del trabajo a 'retroalimentacion_emitida'
     * cuando AMBOS evaluadores hayan finalizado su retroalimentación.
     */
    public function finalizarRetroalimentacion(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->profesor) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $trabajo = Trabajo::with('evaluadores')->findOrFail($id);

        // Verificar que el usuario autenticado es evaluador de este trabajo
        $esEvaluador = $trabajo->evaluadores
            ->contains('id_profesor', $usuario->profesor->id_profesor);

        if (!$esEvaluador) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Marcar la retroalimentación del evaluador actual como finalizada en la tabla pivote
        DB::table('trabajo_profesor')
            ->where('id_trabajo', $trabajo->id_trabajo)
            ->where('id_profesor', $usuario->profesor->id_profesor)
            ->update(['retroalimentacion_finalizada' => true]);

        // Verificar si AMBOS evaluadores ya finalizaron su retroalimentación
        $totalEvaluadores = $trabajo->evaluadores->count(); // siempre 2
        $evaluadoresFinalizados = DB::table('trabajo_profesor')
            ->where('id_trabajo', $trabajo->id_trabajo)
            ->where('retroalimentacion_finalizada', true)
            ->count();

        if ($evaluadoresFinalizados >= $totalEvaluadores) {
            // Ambos finalizaron → cambiar estado del trabajo
            $trabajo->update(['estado' => 'retroalimentacion_emitida']);

            // Registrar en historial
            \App\Models\HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado'           => 'retroalimentacion_emitida',
                'version_documento' => $trabajo->version_actual ?? 'v1',
                'user_id'          => $usuario->id_usuario,
                'observacion_estado' => 'Retroalimentación finalizada por ambos jurados evaluadores. El gestor puede subir la versión corregida.',
            ]);

            // ── Notificar a administradores y gestores ──
            $destinatarios = Usuario::whereIn('rol', ['Administrador', 'Gestor'])
                ->where('activo', true)
                ->get();

            foreach ($destinatarios as $destinatario) {
                $destinatario->notify(new RetroalimentacionFinalizada($trabajo));
            }

            return response()->json([
                'success'    => true,
                'ambos'      => true,
                'message'    => 'Retroalimentación finalizada. El gestor ha sido notificado para subir la versión corregida.',
            ]);
        }

        // Solo este evaluador finalizó, falta el otro
        return response()->json([
            'success' => true,
            'ambos'   => false,
            'message' => 'Tu retroalimentación fue registrada. El estado del proyecto cambiará cuando el otro jurado también finalice.',
        ]);
    }

    /**
     * Finaliza la revisión completa del evaluador, cambiando el estado a Finalizado.
     */
    public function finalizarRevision(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->profesor) {
            return response()->json(['success' => false, 'message' => 'Usuario no es profesor.'], 403);
        }

        $trabajo = Trabajo::findOrFail($id);
        
        // Actualizamos el estado_revision en la pivote a 'Finalizado'
        DB::table('trabajo_profesor')
            ->where('id_trabajo', $trabajo->id_trabajo)
            ->where('id_profesor', $usuario->profesor->id_profesor)
            ->update([
                'estado_revision' => 'Finalizado'
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Revisión finalizada correctamente.'
        ]);
    }
}
