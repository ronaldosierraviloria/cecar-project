<?php

namespace Tests\Unit\Models;

use App\Models\HistorialEstado;
use App\Models\Trabajo;
use App\Models\Usuario;
use Tests\TestCase;

class HistorialEstadoTest extends TestCase
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

        \Illuminate\Support\Facades\Schema::create('historial_estados', function ($table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->string('estado');
            $table->string('version_documento')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('observacion_estado')->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $historial = new HistorialEstado();
        $this->assertSame(
            ['trabajo_grado_id', 'estado', 'version_documento', 'user_id', 'observacion_estado'],
            $historial->getFillable()
        );
    }

    public function test_belongs_to_trabajo(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test',
            'correo' => 'admin@test.com', 'password' => bcrypt('pass'), 'rol' => 'Administrador',
        ]);
        $historial = HistorialEstado::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'estado' => 'subido',
            'version_documento' => 'v1',
            'user_id' => $usuario->id_usuario,
        ]);

        $this->assertInstanceOf(Trabajo::class, $historial->trabajo);
        $this->assertSame('Test', $historial->trabajo->titulo);
    }

    public function test_belongs_to_usuario(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test',
            'correo' => 'admin@test.com', 'password' => bcrypt('pass'), 'rol' => 'Administrador',
        ]);
        $historial = HistorialEstado::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'estado' => 'subido',
            'version_documento' => 'v1',
            'user_id' => $usuario->id_usuario,
        ]);

        $this->assertInstanceOf(Usuario::class, $historial->usuario);
    }

    public function test_all_estados(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Administrador',
        ]);

        $estados = ['subido', 'en_revision', 'retroalimentacion_emitida', 'version_corregida_subida', 'aprobado'];
        foreach ($estados as $estado) {
            HistorialEstado::create([
                'trabajo_grado_id' => $trabajo->id_trabajo,
                'estado' => $estado,
                'version_documento' => 'v1',
                'user_id' => $usuario->id_usuario,
            ]);
        }

        $this->assertCount(5, HistorialEstado::where('trabajo_grado_id', $trabajo->id_trabajo)->get());
    }
}
