<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class UserController extends Controller
{
    public function perfil()
    {
        $usuario = Auth::user();
        $view = match($usuario->rol) {
            'Administrador' => 'admin.perfil',
            'Gestor' => 'gestor.perfil',
            'Evaluador' => 'evaluador.perfil',
            default => 'user.perfil'
        };
        return view($view, compact('usuario'));
    }


    public function update(Request $request)
    {
        $usuario = Auth::user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario',
            'contraseña' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $usuario->update([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
            ]);

            if ($request->filled('contraseña')) {
                $usuario->update([
                    'password' => \Illuminate\Support\Facades\Hash::make($request->contraseña)
                ]);
            }

            return back()->with('success', 'Perfil actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }
}
