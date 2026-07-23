<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Trabajo;
use App\Models\Rubrica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignarRubricaController extends Controller
{
    public function form($id_trabajo)
    {
        $trabajo = Trabajo::with(['tipo', 'rubricas', 'estudiante'])->findOrFail($id_trabajo);
        $rubricas = Rubrica::with('tipo')->where('activo', true)->get();

        return view('gestor.asignar_rubrica', compact('trabajo', 'rubricas'));
    }

    public function store(Request $request, $id_trabajo)
    {
        $request->validate([
            'id_rubrica' => 'required|exists:rubrica,id_rubrica'
        ]);

        // eliminar asignaciones anteriores
        DB::table('trabajo_rubrica')
            ->where('id_trabajo', $id_trabajo)
            ->delete();

        // nueva asignación
        DB::table('trabajo_rubrica')->insert([
            'id_trabajo' => $id_trabajo,
            'id_rubrica' => $request->id_rubrica,
            'fecha_asignacion' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Rúbrica asignada correctamente.');
    }
}
