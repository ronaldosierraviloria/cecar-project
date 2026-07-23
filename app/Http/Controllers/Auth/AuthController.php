<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Mostrar login
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    // Mostrar registro
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        $areas = \App\Models\Area::orderBy('nombre_area')->get();
        $facultades = \App\Models\Facultad::orderBy('nombre_facultad')->get();

        return view('auth.register', compact('areas', 'facultades'));
    }

    // Procesar registro
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuario,correo',
            'contraseña' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:Administrador,Gestor,Evaluador',
            'id_area' => 'required_if:rol,Evaluador|nullable|exists:area,id_area',
            'id_facultad' => 'nullable|exists:facultad,id_facultad',
        ]);

        try {
            DB::beginTransaction();

            $usuario = \App\Models\Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'correo' => $request->correo,
                'password' => Hash::make($request->contraseña),
                'rol' => $request->rol,
                'activo' => true,
                'id_facultad' => $request->rol === 'Administrador' ? $request->id_facultad : null,
            ]);

            if ($request->rol === 'Gestor') {
                \App\Models\Gestor::create([
                    'id_usuario' => $usuario->id_usuario,
                ]);
            } elseif ($request->rol === 'Evaluador') {
                \App\Models\Profesor::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_area' => $request->id_area,
                ]);
            }

            DB::commit();
            return redirect()->route('login')->with('success', 'Usuario registrado exitosamente. Inicie sesión.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar usuario: ' . $e->getMessage());
        }
    }

    // Procesar login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => 'required|email',
            'contraseña' => 'required|string',
        ]);

        // Intentar autenticar
        $valid = Auth::attempt([
            'correo' => $credentials['correo'],
            'password' => $credentials['contraseña']
        ]);

        if (!$valid) {
            return back()
                ->with('error', 'Credenciales inválidas.')
                ->withInput();
        }

        $request->session()->regenerate();
        return $this->redirectByRole();
    }

    // Redirigir según el rol
    private function redirectByRole()
    {
        $rol = Auth::user()->rol;

        return match ($rol) {
            'Administrador' => redirect()->route('admin.dashboard'),
            'Evaluador' => redirect()->route('evaluador.dashboard'),
            'Gestor' => redirect()->route('gestor.dashboard'),
            default => redirect()->route('login')->with('error', 'Rol no autorizado.'),
        };
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->query('expired') === '1') {
            return redirect()->route('login')->with('error', 'Su sesión ha expirado por inactividad. Inicie sesión nuevamente.');
        }

        return redirect()->route('login');
    }
}
