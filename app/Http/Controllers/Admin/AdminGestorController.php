<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Gestor;

class AdminGestorController extends Controller
{
    public function index()
    {
        // Traemos a los gestores ordenados alfabéticamente por nombre
        $gestores = Gestor::join('usuario', 'gestor.id_usuario', '=', 'usuario.id_usuario')
            ->orderBy('usuario.nombre', 'asc')
            ->select('gestor.*')
            ->with('usuario')
            ->get();

        return view('admin.listaGestor', compact('gestores'));
    }

    public function create()
    {
        return redirect()->route('admin.listaGestor');
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo',
            'contraseña' => 'required|string|min:8|confirmed',
        ];

        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no debe exceder los :max caracteres.',
            'email' => 'El campo :attribute debe ser una dirección de correo válida.',
            'correo.unique' => 'Este correo ya está registrado en el sistema.',
            'contraseña.min' => 'La contraseña debe tener al menos :min caracteres.',
            'contraseña.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];

        $attributes = [
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'correo' => 'Correo Electrónico',
            'contraseña' => 'Contraseña',
        ];

        $request->validate($rules, $messages, $attributes);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
                'password' => Hash::make($request->contraseña),
                'rol' => 'Gestor',
            ]);

            Gestor::create([
                'id_usuario' => $usuario->id_usuario,
            ]);

            DB::commit();

            return back()
                ->with('success', 'El Gestor ha sido agregado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Ocurrió un error al registrar el Gestor: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $gestor = Gestor::findOrFail($id);
        $usuario = $gestor->usuario;

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario',
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

            DB::commit();
            return back()->with('success', 'Gestor actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $gestor = Gestor::findOrFail($id);
            $usuario = $gestor->usuario;

            $gestor->delete();
            $usuario->delete();

            DB::commit();
            return back()->with('success', 'Gestor eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
