<?php

namespace App\Http\Controllers\Gestor;

use App\Models\Facultad;
use App\Models\TipoTrabajo;
use App\Models\Profesor;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use App\Models\Rubrica;
use Illuminate\Support\Facades\Storage;

class GestorController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $trabajos = Trabajo::with(['estudiante', 'evaluadores' => function ($q) {
            $q->withPivot('estado_revision');
        }])->get();
        $tipos = TipoTrabajo::all();
        return view('gestor.dashboard', compact('usuario', 'trabajos', 'tipos'));
    }

    public function listaEvaluadores(Request $request)
    {
        $usuario = Auth::user();

        $query = Profesor::whereHas('usuario', fn($q) => $q->where('rol', 'Evaluador'))
            ->with(['usuario', 'area.facultad'])
            ->withCount('trabajos');

        if ($request->filled('id_facultad')) {
            $query->whereHas('area', fn($q) => $q->where('id_facultad', $request->id_facultad));
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->whereHas('usuario', function ($q) use ($busqueda) {
                $q->where(function ($sub) use ($busqueda) {
                    $sub->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellido', 'like', "%{$busqueda}%")
                        ->orWhere('correo', 'like', "%{$busqueda}%");
                });
            });
        }

        $evaluadores = $query->orderBy('id_profesor')->get();
        $facultades = Facultad::orderBy('nombre_facultad')->get();

        return view('gestor.listaEvaluadores', compact('usuario', 'evaluadores', 'facultades'));
    }

    public function crearProyecto()
    {
        $usuario = Auth::user();
        $tiposTrabajo = TipoTrabajo::where('activo', true)->where('nombre_tipo', '!=', 'Trabajo De Grado')->get();
        $facultades = Facultad::with('areas')->orderBy('nombre_facultad')->get();
        $rubricas = Rubrica::where('activo', true)->with('tipo')->get();
        return view('gestor.creartrabajo', compact('usuario','tiposTrabajo', 'facultades', 'rubricas'));
    }

    // El resto de tus funciones...
    
    public function eliminarAjax($id)
    {
        $trabajo = Trabajo::findOrFail($id);
        
        // Eliminar archivo PDF
        $relative = preg_replace('#^storage/#', '', $trabajo->archivo_pdf);
        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }

        // Eliminar estudiantes
        foreach ($trabajo->estudiante as $est) {
            $est->delete();
        }

        $trabajo->delete();

        return response()->json(['success' => true]);
    }
}