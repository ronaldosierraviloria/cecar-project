<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Facultad;
use Illuminate\Support\Facades\DB;

class AdminAreaController extends Controller
{
    public function index()
    {
        $facultades = Facultad::with('areas')->orderBy('nombre_facultad')->get();
        $areasSinFacultad = Area::whereNull('id_facultad')->orderBy('nombre_area')->get();
        return view('admin.listaAreas', compact('facultades', 'areasSinFacultad'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_area' => 'required|string|max:255|unique:area,nombre_area',
            'id_facultad' => 'required|exists:facultad,id_facultad',
        ], [
            'nombre_area.required' => 'El nombre del área es obligatorio.',
            'nombre_area.unique' => 'Esta área ya existe en el sistema.',
            'id_facultad.required' => 'La facultad es obligatoria.',
            'id_facultad.exists' => 'La facultad seleccionada no existe.',
        ]);

        try {
            Area::create([
                'nombre_area' => $request->nombre_area,
                'id_facultad' => $request->id_facultad,
            ]);

            return back()->with('success', 'El área ha sido agregada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al agregar el área: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_area' => 'required|string|max:255|unique:area,nombre_area,' . $id . ',id_area',
            'id_facultad' => 'required|exists:facultad,id_facultad',
        ], [
            'nombre_area.required' => 'El nombre del área es obligatorio.',
            'nombre_area.unique' => 'Esta área ya existe en el sistema.',
            'id_facultad.required' => 'La facultad es obligatoria.',
            'id_facultad.exists' => 'La facultad seleccionada no existe.',
        ]);

        try {
            $area = Area::findOrFail($id);
            $area->update([
                'nombre_area' => $request->nombre_area,
                'id_facultad' => $request->id_facultad,
            ]);

            return back()->with('success', 'Área actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al actualizar el área: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $area = Area::findOrFail($id);
            
            // Opcional: Verificar si hay profesores vinculados antes de eliminar
            if ($area->profesores()->count() > 0) {
                return back()->with('error', 'No se puede eliminar el área porque tiene profesores asociados.');
            }

            $area->delete();
            return back()->with('success', 'Área eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al eliminar el área: ' . $e->getMessage());
        }
    }
}
