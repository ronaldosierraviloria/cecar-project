<?php

namespace Tests\Unit\Models;

use App\Models\Seguimiento;
use App\Models\Trabajo;
use App\Models\Usuario;
use Tests\TestCase;

class SeguimientoTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('usuario', function ($table) {
            $table->id('id_usuario');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->unique();
            $table->string('password');
            $table->string('rol');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('gestor', function ($table) {
            $table->id('id_gestor');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('seguimiento', function ($table) {
            $table->id('id_seguimiento');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_admin');
            $table->string('estado_visualizacion', 50)->nullable();
            $table->timestamp('fecha_revision')->nullable();
        });
    }

    public function test_belongs_to_trabajo(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Administrador',
        ]);
        $gestor = \App\Models\Gestor::create(['id_usuario' => $usuario->id_usuario]);
        $seg = Seguimiento::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_admin' => $gestor->id_gestor,
        ]);

        $this->assertInstanceOf(Trabajo::class, $seg->trabajo);
    }

    public function test_belongs_to_admin(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'User', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Administrador',
        ]);
        $gestor = \App\Models\Gestor::create(['id_usuario' => $usuario->id_usuario]);
        $seg = Seguimiento::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_admin' => $gestor->id_gestor,
        ]);

        $this->assertInstanceOf(Usuario::class, $seg->admin);
    }
}
