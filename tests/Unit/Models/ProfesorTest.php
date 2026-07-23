<?php

namespace Tests\Unit\Models;

use App\Models\Profesor;
use App\Models\Usuario;
use App\Models\Area;
use App\Models\Facultad;
use App\Models\Trabajo;
use App\Models\Calificacion;
use Tests\TestCase;

class ProfesorTest extends TestCase
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
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('area', function ($table) {
            $table->id('id_area');
            $table->string('nombre_area', 100);
            $table->unsignedBigInteger('id_facultad')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('facultad', function ($table) {
            $table->id('id_facultad');
            $table->string('nombre_facultad', 150);
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

        \Illuminate\Support\Facades\Schema::create('rubrica', function ($table) {
            $table->id('id_rubrica');
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->boolean('activo')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('calificacion', function ($table) {
            $table->id('id_calificacion');
            $table->unsignedBigInteger('id_rubrica');
            $table->unsignedBigInteger('id_profesor');
            $table->integer('puntaje_total')->nullable();
            $table->text('observacion_final')->nullable();
            $table->text('comentarios')->nullable();
            $table->string('estado', 50)->nullable();
            $table->timestamp('fecha_calificacion')->nullable();
        });
    }

    public function test_fillable_attributes(): void
    {
        $profesor = new Profesor();
        $this->assertSame(
            ['id_usuario', 'id_area', 'terminos_aceptados', 'datos_aceptados'],
            $profesor->getFillable()
        );
    }

    public function test_belongs_to_usuario(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Carlos', 'apellido' => 'Mendez',
            'correo' => 'carlos@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);

        $this->assertInstanceOf(Usuario::class, $profesor->usuario);
        $this->assertSame('Carlos', $profesor->usuario->nombre);
    }

    public function test_belongs_to_area(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ing.']);
        $area = Area::create(['nombre_area' => 'Sistemas', 'id_facultad' => $facultad->id_facultad]);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario, 'id_area' => $area->id_area]);

        $this->assertInstanceOf(Area::class, $profesor->area);
        $this->assertSame('Sistemas', $profesor->area->nombre_area);
    }

    public function test_belongs_to_many_trabajos(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Dor',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $trabajo = Trabajo::create(['titulo' => 'Proyecto A']);
        $trabajo2 = Trabajo::create(['titulo' => 'Proyecto B']);

        $profesor->trabajos()->attach($trabajo->id_trabajo, ['fecha_asignacion' => now()]);
        $profesor->trabajos()->attach($trabajo2->id_trabajo, ['fecha_asignacion' => now()]);

        $this->assertCount(2, $profesor->trabajos);
    }

    public function test_has_many_calificaciones(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Dor',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);

        Calificacion::create([
            'id_rubrica' => 1, 'id_profesor' => $profesor->id_profesor,
            'puntaje_total' => 90, 'estado' => 'Enviada',
        ]);

        $this->assertCount(1, $profesor->calificaciones);
        $this->assertEquals(90, $profesor->calificaciones->first()->puntaje_total);
    }

    public function test_terminos_aceptados_default_false(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $this->assertFalse($profesor->fresh()->terminos_aceptados);
    }

    public function test_terminos_aceptados_true(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario, 'terminos_aceptados' => true]);
        $this->assertTrue($profesor->terminos_aceptados);
    }
}
