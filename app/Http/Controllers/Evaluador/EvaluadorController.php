<?php

namespace App\Http\Controllers\Evaluador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Area;
use App\Models\Facultad;
use App\Models\Trabajo;

class EvaluadorController extends Controller
{
    public function index(Request $request)
    {
        $query = Profesor::join('usuario', 'profesor.id_usuario', '=', 'usuario.id_usuario')
            ->orderBy('usuario.nombre', 'asc')
            ->select('profesor.*')
            ->with(['usuario', 'area'])
            ->withCount('trabajos');

        if ($request->filled('id_facultad')) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('id_facultad', $request->id_facultad);
            });

            if ($request->filled('id_area')) {
                $query->where('profesor.id_area', $request->id_area);
            }
        }

        $profesores = $query->paginate(10);

        $facultades = Facultad::orderBy('nombre_facultad')->get();
        $areas = Area::orderBy('nombre_area')->get();
        return view('admin.listaEvaluador', compact('profesores', 'facultades', 'areas'));
    }

    public function create()
    {
        return redirect()->route('admin.listaEvaluador');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo',
            'contraseña' => 'required|string|min:8|confirmed',
            'id_area' => 'required|integer|exists:area,id_area',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
                'password' => Hash::make($request->contraseña),
                'rol' => 'Evaluador',
            ]);

            Profesor::create([
                'id_usuario' => $usuario->id_usuario,
                'id_area' => $request->id_area,
            ]);

            DB::commit();

            return back()->with('success', 'Tutor agregado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function asignarEvaluador($trabajo_id)
    {
        $usuario = Auth::user();
        $trabajo = Trabajo::with(['estudiante', 'tipo', 'evaluadores.usuario'])->findOrFail($trabajo_id);
        $evaluadores = Profesor::with('usuario')->get();
        $evaluadoresAsignadosIds = $trabajo->evaluadores->pluck('id_profesor')->all();

        return view('admin.asignarEvaluador', compact('usuario', 'trabajo', 'evaluadores', 'evaluadoresAsignadosIds'));
    }

    public function guardarEvaluadores(Request $request, $trabajo_id)
    {
        $request->validate([
            'evaluadores' => 'required|array|max:2',
            'evaluadores.*' => 'exists:profesor,id_profesor',
        ]);

        $trabajo = Trabajo::findOrFail($trabajo_id);
        $evaluadoresIds = $request->input('evaluadores', []);
        $dataToSync = [];
        $fechaHoy = Carbon::now();
        $fechaLimite = $fechaHoy->copy()->addWeeks(3)->toDateString();

        foreach ($evaluadoresIds as $idProfesor) {
            $dataToSync[$idProfesor] = [
                'fecha_asignacion' => $fechaHoy->toDateString(),
                'fecha_limite_revision' => $fechaLimite,
                'estado_revision' => 'Asignado',
            ];
        }

        $trabajo->evaluadores()->sync($dataToSync);

        return redirect()->route('admin.dashboard')->with('success', 'Asignacion actualizada.');
    }

    public function update(Request $request, $id)
    {
        $profesor = Profesor::findOrFail($id);
        $usuario = $profesor->usuario;

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario',
            'id_area' => 'required|integer|exists:area,id_area',
        ]);

        try {
            DB::beginTransaction();

            $usuario->update([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
            ]);

            if ($request->filled('contraseña')) {
                $usuario->update(['password' => Hash::make($request->contraseña)]);
            }

            $profesor->update(['id_area' => $request->id_area]);

            DB::commit();
            return back()->with('success', 'Evaluador actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $profesor = Profesor::withCount('trabajos')->findOrFail($id);

            if ($profesor->trabajos_count > 0) {
                return back()->with('error', 'No se puede eliminar el evaluador porque está asociado a uno o más proyectos.');
            }

            DB::beginTransaction();
            $usuario = $profesor->usuario;
            $profesor->delete();
            $usuario->delete();

            DB::commit();
            return back()->with('success', 'Evaluador eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}