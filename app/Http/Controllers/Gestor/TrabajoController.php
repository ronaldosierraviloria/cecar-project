<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trabajo;
use App\Models\TipoTrabajo;
use App\Models\Estudiante;
use App\Models\Rubrica;
use App\Models\Usuario;
use App\Models\Director;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrabajoSubidoEstudiante;
use App\Models\Retroalimentacion;
use App\Notifications\NuevoTrabajoSubido;
use App\Notifications\NuevaVersionDisponible;
use App\Notifications\TrabajoRetirado;
use App\Notifications\TrabajoReactivado;
use App\Notifications\TrabajoEliminado;
use App\Notifications\TrabajoRetiradoEvaluador;
use App\Notifications\TrabajoEliminadoEvaluador;
use App\Notifications\InformeFinalSubido;

class TrabajoController extends Controller
{
    // Mostrar el formulario para crear un nuevo trabajo
    public function crear()
    {
        $tipos = TipoTrabajo::all(); // Obtener tipos de trabajo de la BD
        $rubricas = Rubrica::where('activo', true)->with('tipo')->get();
        $usuario = Auth::user();      // Para pasar nombre del gestor
        return view('gestor.creartrabajo', compact('tipos', 'usuario', 'rubricas'));
    }

    // Mostrar o descargar archivo PDF
    public function archivo($id)
    {
        $trabajo = \App\Models\Trabajo::findOrFail($id);

        $relative = preg_replace('#^storage/#', '', $trabajo->archivo_pdf);

        if (!Storage::disk('supabase')->exists($relative)) {
            abort(404);
        }

        $url = Storage::disk('supabase')->temporaryUrl($relative, now()->addMinutes(10));

        if (request()->has('download')) {
            return redirect($url);
        }

        return redirect($url);
    }

    // Guardar el trabajo en la base de datos
    public function guardar(Request $request)
    {
        $request->validate([
            'titulo'              => 'required|string|max:255',
            'id_tipo'             => 'required|exists:tipo_trabajo,id_tipo',
            'plantilla_rubrica'   => 'required|in:propuesta_de_grado,pasantia',
            'archivo_pdf'         => 'required|mimes:pdf|max:51200', // Máx 50MB
            'estudiantes'         => 'required|array|min:1|max:3',
            'estudiantes.*.nombre'    => 'required|string|max:100',
            'estudiantes.*.apellido'  => 'required|string|max:100',
            'estudiantes.*.correo'    => 'nullable|email|max:100',
            'estudiantes.*.id_area'   => 'required|exists:area,id_area',
            'director.nombre'     => 'required|string|max:100',
            'director.apellido'   => 'required|string|max:100',
            'director.correo'     => 'required|email|max:100',
            'subdirector.nombre'  => 'nullable|required_with:subdirector.apellido,subdirector.correo|string|max:100',
            'subdirector.apellido'=> 'nullable|required_with:subdirector.nombre,subdirector.correo|string|max:100',
            'subdirector.correo'  => 'nullable|required_with:subdirector.nombre,subdirector.apellido|email|max:100',
        ]);

        // **Verificar que cada estudiante no esté asignado ya a un trabajo**
        foreach ($request->estudiantes as $estudiante) {
            $existe = Estudiante::where('nombre', $estudiante['nombre'])
                ->where('apellido', $estudiante['apellido'])
                ->where('id_area', $estudiante['id_area'])
                ->exists();

            if ($existe) {
                return redirect()->back()
                    ->with('error', "El estudiante {$estudiante['nombre']} {$estudiante['apellido']} ya está asignado a un trabajo de grado.")
                    ->withInput();
            }
        }

        // 🔹 Guardar el archivo PDF con el mismo nombre original (limpiado)
        $archivo = $request->file('archivo_pdf');
        $nombreLimpio = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $nombreLimpio . '.' . $extension;

        // Si ya existe un archivo con ese nombre, agregarle un sufijo
        $contador = 1;
        while (Storage::disk('supabase')->exists("pdf/{$nombreArchivo}")) {
            $nombreArchivo = "{$nombreLimpio}-{$contador}.{$extension}";
            $contador++;
        }

        $rutaArchivo = $archivo->storeAs('pdf', $nombreArchivo, 'supabase');

        // Guardar todo en una transacción: trabajo, historial, estudiantes, rúbrica y pivote
        DB::beginTransaction();
        try {
            $trabajo = Trabajo::create([
                'titulo'            => $request->titulo,
                'fecha_subida'      => now()->toDateString(),
                'id_tipo'           => $request->id_tipo,
                'plantilla_rubrica' => $request->plantilla_rubrica,
                'archivo_pdf'       => 'storage/' . $rutaArchivo,
                'estado'            => 'subido',
                'version_actual'    => 'v1',
            ]);

            // Crear el registro de historial_estados inicial
            \App\Models\HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado' => 'subido',
                'version_documento' => 'v1',
                'user_id' => Auth::id(),
                'observacion_estado' => 'Documento inicial subido al sistema.',
            ]);

            // Guardar los estudiantes relacionados
            foreach ($request->estudiantes as $estudiante) {
                Estudiante::create([
                    'id_trabajo' => $trabajo->id_trabajo,
                    'nombre' => $estudiante['nombre'],
                    'apellido' => $estudiante['apellido'],
                    'correo' => $estudiante['correo'] ?? null,
                    'id_area' => $estudiante['id_area'],
                ]);
            }

            // Guardar o buscar Director
            $director = Director::firstOrCreate(
                ['correo_electronico' => $request->director['correo']],
                [
                    'nombre' => $request->director['nombre'],
                    'apellido' => $request->director['apellido'],
                ]
            );
            $trabajo->directores()->attach($director->id_director, ['rol' => 'director']);

            // Guardar o buscar Subdirector (si se ingresó)
            if (!empty($request->subdirector['nombre']) && !empty($request->subdirector['correo'])) {
                $subdirector = Director::firstOrCreate(
                    ['correo_electronico' => $request->subdirector['correo']],
                    [
                        'nombre' => $request->subdirector['nombre'],
                        'apellido' => $request->subdirector['apellido'],
                    ]
                );
                $trabajo->directores()->attach($subdirector->id_director, ['rol' => 'subdirector']);
            }

            // Vincular la rúbrica existente con el trabajo (si se proporcionó)
            if ($request->filled('id_rubrica')) {
                $trabajo->rubricas()->attach($request->id_rubrica, ['fecha_asignacion' => now()]);
            }

            DB::commit();

            // ── Notificar a todos los Administradores ──
            $nombreGestor = Auth::user()->nombre . ' ' . Auth::user()->apellido;
            $admins = Usuario::where('rol', 'Administrador')->where('activo', true)->get();
            foreach ($admins as $admin) {
                $admin->notify(new NuevoTrabajoSubido($trabajo, $nombreGestor));
            }

             return redirect()->route('gestor.dashboard')->with('success', 'Trabajo y estudiantes guardados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Limpiar archivo subido si existiera
            if (isset($rutaArchivo) && Storage::disk('supabase')->exists($rutaArchivo)) {
                Storage::disk('supabase')->delete($rutaArchivo);
            }

            return redirect()->back()->with('error', 'Ocurrió un error al guardar el trabajo: ' . $e->getMessage());
        }
    }

    public function detalles($id)
    {
        $usuario = Auth::user();
        $trabajo = Trabajo::with([
            'estudiante.area',
            'estudiante.area.facultad',
            'tipo',
            'directores',
            'evaluadores' => fn($q) => $q->withPivot('estado_revision'),
            'evaluaciones.profesor.usuario',
        ])->findOrFail($id);
        return view('gestor.detallesTrabajo', compact('usuario', 'trabajo'));
    }

    public function actualizarArchivo(Request $request, $id)
    {
        $request->validate([
            'archivo_pdf' => 'required|mimes:pdf|max:51200',
        ]);

        $trabajo = Trabajo::findOrFail($id);

        // Eliminar archivo anterior
        $relative = preg_replace('#^storage/#', '', $trabajo->archivo_pdf);
        if (Storage::disk('supabase')->exists($relative)) {
            Storage::disk('supabase')->delete($relative);
        }

        // Guardar el nuevo archivo
        $archivo = $request->file('archivo_pdf');
        $nombreLimpio = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $nombreLimpio . '.' . $extension;

        $contador = 1;
        while (Storage::disk('supabase')->exists("pdf/{$nombreArchivo}")) {
            $nombreArchivo = "{$nombreLimpio}-{$contador}.{$extension}";
            $contador++;
        }

        $rutaArchivo = $archivo->storeAs('pdf', $nombreArchivo, 'supabase');
        $trabajo->update(['archivo_pdf' => 'storage/' . $rutaArchivo]);

        // Borrar retroalimentaciones anteriores al reemplazar el documento
        Retroalimentacion::where('trabajo_grado_id', $trabajo->id_trabajo)->delete();

        return redirect()->route('gestor.trabajo.detalles', $id)
            ->with('success', 'Archivo PDF actualizado correctamente.');
    }

    // Subir una nueva versión del trabajo de grado
    public function subirNuevaVersion(Request $request, $id)
    {
        $request->validate([
            'archivo_pdf' => 'required|mimes:pdf|max:51200', // Máx 50MB
            'observacion_estado' => 'nullable|string|max:1000',
        ]);

        $trabajo = Trabajo::findOrFail($id);

        // Solo se puede subir una nueva versión cuando los evaluadores hayan finalizado su retroalimentación
        if ($trabajo->estado !== 'retroalimentacion_emitida') {
            return redirect()->route('gestor.trabajo.detalles', $id)
                ->with('error', 'No es posible subir una nueva versión en este momento. Solo se permite cuando ambos jurados hayan finalizado la retroalimentación.');
        }

        $versionActual = strtolower($trabajo->version_actual ?? 'v1');
        preg_match('/v(\d+)/', $versionActual, $matches);
        $numeroVersion = isset($matches[1]) ? (int)$matches[1] : 1;
        $nuevaVersion = 'v' . ($numeroVersion + 1);

        // Guardar el nuevo archivo
        $archivo = $request->file('archivo_pdf');
        $nombreLimpio = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $nombreLimpio . '-' . $nuevaVersion . '.' . $extension;

        // Evitar colisión de nombres
        $contador = 1;
        while (Storage::disk('supabase')->exists("pdf/{$nombreArchivo}")) {
            $nombreArchivo = "{$nombreLimpio}-{$nuevaVersion}-{$contador}.{$extension}";
            $contador++;
        }

        $rutaArchivo = $archivo->storeAs('pdf', $nombreArchivo, 'supabase');

        // Actualizar el modelo del trabajo con el nuevo archivo y la nueva versión
        $trabajo->update([
            'archivo_pdf' => 'storage/' . $rutaArchivo,
            'version_actual' => $nuevaVersion,
            'estado' => 'version_corregida_subida',
        ]);

        // Borrar comentarios/retroalimentaciones anteriores
        Retroalimentacion::where('trabajo_grado_id', $trabajo->id_trabajo)->delete();

        // Resetear a false el flag de retroalimentación finalizada para que los evaluadores puedan volver a revisar
        DB::table('trabajo_profesor')
            ->where('id_trabajo', $trabajo->id_trabajo)
            ->update(['retroalimentacion_finalizada' => false]);

        // Crear registro en historial_estados
        \App\Models\HistorialEstado::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'estado' => 'version_corregida_subida',
            'version_documento' => $nuevaVersion,
            'user_id' => Auth::id(),
            'observacion_estado' => $request->observacion_estado ?? 'Nueva versión corregida subida por el gestor.',
        ]);

        // ── Notificar a evaluadores asignados que hay nueva versión ──
        try {
            $trabajo->load('evaluadores.usuario');
            foreach ($trabajo->evaluadores as $evaluador) {
                if ($evaluador->usuario) {
                    $evaluador->usuario->notify(new NuevaVersionDisponible($trabajo, $nuevaVersion));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Error al notificar evaluadores: ' . $e->getMessage());
        }

        // ── Notificar al Admin de la nueva versión ──
        try {
            $admins = Usuario::where('rol', 'Administrador')->where('activo', true)->get();
            foreach ($admins as $admin) {
                $admin->notify(new NuevaVersionDisponible($trabajo, $nuevaVersion));
            }
        } catch (\Throwable $e) {
            \Log::error('Error al notificar admin: ' . $e->getMessage());
        }

        return redirect()->route('gestor.trabajo.detalles', $id)
            ->with('success', 'Nueva versión ' . strtoupper($nuevaVersion) . ' cargada correctamente.');
    }

    public function eliminarAjax($id)
    {
        $trabajo = Trabajo::findOrFail($id);

        // ── Notificar a todos los Administradores antes de eliminar ──
        $nombreActor = 'El gestor ' . Auth::user()->nombre . ' ' . Auth::user()->apellido;
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
            if (Storage::disk('supabase')->exists($relative)) {
                Storage::disk('supabase')->delete($relative);
            }

            // Finally, delete the project
            $trabajo->delete();
        });

        return response()->json(['success' => true]);
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
                ? 'Proyecto retirado por el gestor.'
                : 'Proyecto reactivado por el gestor.',
        ]);

        // ── Notificar a todos los Administradores ──
        $nombreActor = 'El gestor ' . Auth::user()->nombre . ' ' . Auth::user()->apellido;
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

        return redirect()->route('gestor.trabajo.detalles', $id)
            ->with('success', $mensaje);
    }

    public function subirInformeFinalForm($id)
    {
        $usuario = Auth::user();
        $trabajo = Trabajo::with(['evaluadores' => function ($q) {
            $q->withPivot('estado_revision');
        }, 'tipo', 'estudiante'])->findOrFail($id);

        // Verificar que sea una propuesta
        if ($trabajo->plantilla_rubrica !== 'propuesta_de_grado') {
            return redirect()->route('gestor.dashboard')->with('error', 'Este trabajo no es una propuesta de grado.');
        }

        // Verificar que al menos un evaluador haya finalizado
        if ($trabajo->evaluadores->isEmpty() || !$trabajo->evaluadores->contains(fn($e) => $e->pivot->estado_revision === 'Finalizado')) {
            return redirect()->route('gestor.dashboard')->with('error', 'Aún ningún evaluador ha finalizado su evaluación.');
        }

        return view('gestor.subirInformeFinal', compact('usuario', 'trabajo'));
    }

    public function subirInformeFinal(Request $request, $id)
    {
        $request->validate([
            'archivo_pdf' => 'required|mimes:pdf|max:51200',
        ]);

        $trabajo = Trabajo::with(['evaluadores' => function ($q) {
            $q->withPivot('estado_revision');
        }])->findOrFail($id);

        // Verificar que sea una propuesta
        if ($trabajo->plantilla_rubrica !== 'propuesta_de_grado') {
            return redirect()->route('gestor.dashboard')->with('error', 'Este trabajo no es una propuesta de grado.');
        }

        // Verificar que al menos un evaluador haya finalizado
        if ($trabajo->evaluadores->isEmpty() || !$trabajo->evaluadores->contains(fn($e) => $e->pivot->estado_revision === 'Finalizado')) {
            return redirect()->route('gestor.dashboard')->with('error', 'Aún ningún evaluador ha finalizado su evaluación.');
        }

        // Obtener el ID del tipo "Trabajo de Grado"
        $tipoTG = TipoTrabajo::where('nombre_tipo', 'ILIKE', '%Trabajo de Grado%')->first();
        if (!$tipoTG) {
            return redirect()->back()->with('error', 'No se encontró el tipo "Trabajo de Grado". Contacta al administrador.');
        }

        DB::beginTransaction();
        try {
            // Guardar el nuevo archivo
            $archivo = $request->file('archivo_pdf');
            $nombreLimpio = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $archivo->getClientOriginalExtension();
            $nombreArchivo = time() . '_' . $nombreLimpio . '.' . $extension;

            $rutaArchivo = $archivo->storeAs('pdf', $nombreArchivo, 'supabase');

            // Convertir propuesta a trabajo de grado
            $trabajo->update([
                'archivo_pdf'       => 'storage/' . $rutaArchivo,
                'id_tipo'           => $tipoTG->id_tipo,
                'plantilla_rubrica' => 'trabajo_de_grado',
                'estado'            => 'subido',
                'version_actual'    => 'v1',
            ]);

            // Registrar en historial
            \App\Models\HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado' => 'subido',
                'version_documento' => 'v1',
                'user_id' => Auth::id(),
                'observacion_estado' => 'Informe final subido. La propuesta ha sido convertida a Trabajo de Grado.',
            ]);

            // Resetear estado_revision de todos los evaluadores para que puedan evaluar el TG
            DB::table('trabajo_profesor')
                ->where('id_trabajo', $trabajo->id_trabajo)
                ->update(['estado_revision' => 'Asignado']);

            DB::commit();

            // Notificar a los evaluadores
            try {
                $trabajo->load('evaluadores.usuario');
                foreach ($trabajo->evaluadores as $evaluador) {
                    if ($evaluador->usuario) {
                        $evaluador->usuario->notify(new InformeFinalSubido($trabajo));
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error al notificar evaluadores: ' . $e->getMessage());
            }

            // Notificar a los administradores
            try {
                $admins = Usuario::where('rol', 'Administrador')->where('activo', true)->get();
                foreach ($admins as $admin) {
                    $admin->notify(new InformeFinalSubido($trabajo));
                }
            } catch (\Throwable $e) {
                \Log::error('Error al notificar admins: ' . $e->getMessage());
            }

            return redirect()->route('gestor.dashboard')
                ->with('success', 'Informe final subido correctamente. El trabajo ahora es un Trabajo de Grado y se ha reasignado a los evaluadores.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($rutaArchivo) && Storage::disk('supabase')->exists($rutaArchivo)) {
                Storage::disk('supabase')->delete($rutaArchivo);
            }
            return redirect()->back()->with('error', 'Error al subir el informe final: ' . $e->getMessage());
        }
    }
}
