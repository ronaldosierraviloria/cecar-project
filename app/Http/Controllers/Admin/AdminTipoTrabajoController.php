<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoTrabajo;
use App\Models\Rubrica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminTipoTrabajoController extends Controller
{
    public function index()
    {
        $tipos = TipoTrabajo::with('rubrica')->get();
        return view('admin.listaTipoTrabajo', compact('tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_tipo' => 'required|string|max:100|unique:tipo_trabajo,nombre_tipo',
            'archivo_rubrica' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ], [
            'nombre_tipo.required' => 'El nombre del tipo de trabajo es obligatorio.',
            'nombre_tipo.unique' => 'Este tipo de trabajo ya existe.',
            'archivo_rubrica.mimes' => 'La rúbrica debe ser un archivo PDF, Word o Excel.',
        ]);

        DB::beginTransaction();
        try {
            $tipo = TipoTrabajo::create([
                'nombre_tipo' => $request->nombre_tipo,
            ]);

            if ($request->hasFile('archivo_rubrica')) {
                $path = $request->file('archivo_rubrica')->store('rubricas', 'public');
                
                Rubrica::create([
                    'id_tipo' => $tipo->id_tipo,
                    'archivo' => $path,
                    'mime_type' => $request->file('archivo_rubrica')->getMimeType(),
                    'activo' => true,
                    'fecha_creacion' => now(),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Tipo de trabajo agregado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el tipo de trabajo: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_tipo' => 'required|string|max:100|unique:tipo_trabajo,nombre_tipo,' . $id . ',id_tipo',
            'archivo_rubrica' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ], [
            'nombre_tipo.required' => 'El nombre del tipo de trabajo es obligatorio.',
            'nombre_tipo.unique' => 'Este tipo de trabajo ya existe.',
            'archivo_rubrica.mimes' => 'La rúbrica debe ser un archivo PDF, Word o Excel.',
        ]);

        DB::beginTransaction();
        try {
            $tipo = TipoTrabajo::findOrFail($id);
            $tipo->update([
                'nombre_tipo' => $request->nombre_tipo,
            ]);

            if ($request->hasFile('archivo_rubrica')) {
                // Desactivar rúbricas anteriores
                $tipo->rubrica()->update(['activo' => false]);

                $path = $request->file('archivo_rubrica')->store('rubricas', 'public');
                
                Rubrica::create([
                    'id_tipo' => $tipo->id_tipo,
                    'archivo' => $path,
                    'mime_type' => $request->file('archivo_rubrica')->getMimeType(),
                    'activo' => true,
                    'fecha_creacion' => now(),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Tipo de trabajo actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $tipo = TipoTrabajo::findOrFail($id);
            
            if ($tipo->trabajos()->count() > 0) {
                return back()->with('error', 'No se puede eliminar porque existen trabajos asociados a este tipo.');
            }

            // Eliminar rúbricas asociadas
            foreach ($tipo->rubrica as $rubrica) {
                if ($rubrica->archivo) {
                    Storage::disk('supabase')->delete($rubrica->archivo);
                }
                $rubrica->delete();
            }

            $tipo->delete();
            return back()->with('success', 'Tipo de trabajo eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        try {
            $tipo = TipoTrabajo::findOrFail($id);
            $tipo->activo = !$tipo->activo;
            $tipo->save();

            $estado = $tipo->activo ? 'activado' : 'desactivado';
            return back()->with('success', "Tipo de trabajo {$estado} exitosamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }
}
