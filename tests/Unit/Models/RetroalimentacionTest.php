<?php

namespace Tests\Unit\Models;

use App\Models\Retroalimentacion;
use App\Models\Trabajo;
use App\Models\Usuario;
use Tests\TestCase;

class RetroalimentacionTest extends TestCase
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

        \Illuminate\Support\Facades\Schema::create('retroalimentaciones', function ($table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comentario')->nullable();
            $table->string('version_documento')->default('v1');
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $retro = new Retroalimentacion();
        $this->assertSame(
            ['trabajo_grado_id', 'user_id', 'comentario', 'version_documento'],
            $retro->getFillable()
        );
    }

    public function test_belongs_to_trabajo(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Dor',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $retro = Retroalimentacion::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'user_id' => $usuario->id_usuario,
            'comentario' => 'Excelente',
        ]);

        $this->assertInstanceOf(Trabajo::class, $retro->trabajo);
        $this->assertSame('Test', $retro->trabajo->titulo);
    }

    public function test_belongs_to_usuario(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Dor',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $retro = Retroalimentacion::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'user_id' => $usuario->id_usuario,
            'comentario' => 'Excelente',
        ]);

        $this->assertInstanceOf(Usuario::class, $retro->usuario);
        $this->assertSame('Eval', $retro->usuario->nombre);
    }

    public function test_default_version(): void
    {
        $retro = new Retroalimentacion();
        $retro->trabajo_grado_id = 1;
        $retro->user_id = 1;
        $retro->comentario = 'Test';
        $retro->save();

        $this->assertSame('v1', $retro->fresh()->version_documento);
    }
}
