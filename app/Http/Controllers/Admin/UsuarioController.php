<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Gestor;
use App\Models\Profesor;
use App\Models\Area;
use App\Models\Facultad;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::query()->with('profesor.area');

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        $usuarios = $query->orderBy('nombre', 'asc')->paginate(15);
        $areas = Area::orderBy('nombre_area')->get();
        $facultades = Facultad::orderBy('nombre_facultad')->get();

        return view('admin.usuarios.index', compact('usuarios', 'areas', 'facultades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo',
            'contraseña' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:Administrador,Gestor,Evaluador',
            'id_area' => 'required_if:rol,Evaluador|nullable|exists:area,id_area',
            'id_facultad' => 'required_if:rol,Administrador|nullable|exists:facultad,id_facultad',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
                'password' => Hash::make($request->contraseña),
                'rol' => $request->rol,
                'activo' => true,
                'id_facultad' => $request->rol === 'Administrador' ? $request->id_facultad : null,
            ]);

            if ($request->rol === 'Gestor') {
                Gestor::create([
                    'id_usuario' => $usuario->id_usuario,
                ]);
            } elseif ($request->rol === 'Evaluador') {
                Profesor::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_area' => $request->id_area,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Usuario agregado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario',
            'id_area' => 'required_if:rol,Evaluador|nullable|exists:area,id_area',
            'id_facultad' => 'nullable|exists:facultad,id_facultad',
        ]);

        try {
            DB::beginTransaction();

            $usuario->update([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
                'id_facultad' => $usuario->rol === 'Administrador' ? $request->id_facultad : $usuario->id_facultad,
            ]);

            if ($request->filled('contraseña')) {
                $usuario->update(['password' => Hash::make($request->contraseña)]);
            }

            if ($usuario->rol === 'Evaluador' && $usuario->profesor) {
                $usuario->profesor->update(['id_area' => $request->id_area]);
            }

            DB::commit();
            return back()->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            
            // No permitir que el admin se desactive a sí mismo por error (opcional pero recomendado)
            if ($usuario->id_usuario === auth()->id()) {
                return back()->with('error', 'No puedes desactivar tu propia cuenta.');
            }

            $usuario->activo = !$usuario->activo;
            $usuario->save();

            $estado = $usuario->activo ? 'activado' : 'desactivado';
            return back()->with('success', "Usuario {$estado} exitosamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar estado del usuario: ' . $e->getMessage());
        }
    }
}
