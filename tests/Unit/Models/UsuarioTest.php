<?php

namespace Tests\Unit\Models;

use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Retroalimentacion;
use App\Models\Trabajo;
use App\Models\Facultad;
use App\Models\TrabajoProfesor;
use Tests\TestCase;

class UsuarioTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
    }

    private function createDefaultTables(): void
    {
        \Illuminate\Support\Facades\Schema::create('usuario', function ($table) {
            $table->id('id_usuario');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->unique();
            $table->string('password');
            $table->string('rol');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_facultad')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->boolean('terminos_aceptados')->default(false);
            $table->boolean('datos_aceptados')->default(false);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->string('estado', 50)->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo_profesor', function ($table) {
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_limite_revision')->nullable();
            $table->string('estado_revision')->nullable();
            $table->boolean('retroalimentacion_finalizada')->default(false);
            $table->timestamps();
            $table->primary(['id_trabajo', 'id_profesor']);
        });

        \Illuminate\Support\Facades\Schema::create('retroalimentaciones', function ($table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comentario')->nullable();
            $table->string('version_documento')->default('v1');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('facultad', function ($table) {
            $table->id('id_facultad');
            $table->string('nombre_facultad', 150);
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $usuario = new Usuario();
        $this->assertSame(
            ['nombre', 'apellido', 'correo', 'password', 'rol', 'activo', 'id_facultad'],
            $usuario->getFillable()
        );
    }

    public function test_table_and_primary_key(): void
    {
        $usuario = new Usuario();
        $this->assertSame('usuario', $usuario->getTable());
        $this->assertSame('id_usuario', $usuario->getKeyName());
    }

    public function test_hidden_password(): void
    {
        $usuario = new Usuario();
        $this->assertContains('password', $usuario->getHidden());
    }

    public function test_has_one_profesor(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Juan', 'apellido' => 'Perez',
            'correo' => 'juan@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador',
        ]);
        Profesor::create(['id_usuario' => $usuario->id_usuario]);

        $this->assertInstanceOf(Profesor::class, $usuario->profesor);
    }

    public function test_belongs_to_facultad(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ingeniería']);
        $usuario = Usuario::create([
            'nombre' => 'Juan', 'apellido' => 'Perez',
            'correo' => 'juan@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'id_facultad' => $facultad->id_facultad,
        ]);

        $this->assertInstanceOf(Facultad::class, $usuario->facultad);
        $this->assertSame('Ingeniería', $usuario->facultad->nombre_facultad);
    }

    public function test_has_many_retroalimentaciones(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Dor',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador',
        ]);
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        Retroalimentacion::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'user_id' => $usuario->id_usuario,
            'comentario' => 'Bien',
        ]);

        $this->assertCount(1, $usuario->retroalimentaciones);
    }

    public function test_rol_administrador(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test',
            'correo' => 'admin@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador',
        ]);
        $this->assertSame('Administrador', $usuario->rol);
    }

    public function test_rol_evaluador(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Test',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador',
        ]);
        $this->assertSame('Evaluador', $usuario->rol);
    }

    public function test_rol_gestor(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Gestor', 'apellido' => 'Test',
            'correo' => 'gestor@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Gestor',
        ]);
        $this->assertSame('Gestor', $usuario->rol);
    }

    public function test_activo_default_true(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'User', 'apellido' => 'Test',
            'correo' => 'user@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Gestor',
        ]);
        $this->assertTrue($usuario->fresh()->activo);
    }

    public function test_correo_unique(): void
    {
        Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'dup@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Gestor',
        ]);
        $this->expectException(\Illuminate\Database\QueryException::class);
        Usuario::create([
            'nombre' => 'C', 'apellido' => 'D', 'correo' => 'dup@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Gestor',
        ]);
    }

    public function test_get_auth_identifier_name(): void
    {
        $usuario = new Usuario();
        $this->assertSame('id_usuario', $usuario->getAuthIdentifierName());
    }

    public function test_get_auth_password(): void
    {
        $usuario = new Usuario();
        $raw = 'plain_password';
        $usuario->password = $raw;
        $this->assertNotSame($raw, $usuario->getAuthPassword());
    }
}
