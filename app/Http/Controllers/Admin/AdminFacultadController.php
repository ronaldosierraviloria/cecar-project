<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facultad;

class AdminFacultadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre_facultad' => 'required|string|max:255|unique:facultad,nombre_facultad',
        ], [
            'nombre_facultad.required' => 'El nombre de la facultad es obligatorio.',
            'nombre_facultad.unique' => 'Esta facultad ya existe en el sistema.',
        ]);

        try {
            Facultad::create([
                'nombre_facultad' => $request->nombre_facultad,
            ]);

            return back()->with('success', 'La facultad ha sido agregada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al agregar la facultad: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_facultad' => 'required|string|max:255|unique:facultad,nombre_facultad,' . $id . ',id_facultad',
        ], [
            'nombre_facultad.required' => 'El nombre de la facultad es obligatorio.',
            'nombre_facultad.unique' => 'Esta facultad ya existe en el sistema.',
        ]);

        try {
            $facultad = Facultad::findOrFail($id);
            $facultad->update([
                'nombre_facultad' => $request->nombre_facultad,
            ]);

            return back()->with('success', 'Facultad actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al actualizar la facultad: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $facultad = Facultad::findOrFail($id);
            
            // Verificar si hay áreas vinculadas antes de eliminar
            if ($facultad->areas()->count() > 0) {
                return back()->with('error', 'No se puede eliminar la facultad porque tiene áreas asociadas.');
            }

            $facultad->delete();
            return back()->with('success', 'Facultad eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al eliminar la facultad: ' . $e->getMessage());
        }
    }
}
