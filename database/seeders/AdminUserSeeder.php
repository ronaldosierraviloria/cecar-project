<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['correo' => 'administrador@sistema.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'password' => Hash::make('Cecar2026'), // contraseña fácil para pruebas
                'rol' => 'Administrador',
            ]
        );
    }
}
