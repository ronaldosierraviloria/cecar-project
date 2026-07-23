<?php

namespace Tests\Unit\Models;

use App\Models\Evaluacion;
use App\Models\Trabajo;
use App\Models\Profesor;
use App\Models\Usuario;
use Tests\TestCase;

class EvaluacionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
    }

    private function createDefaultTables(): void
    {
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

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('evaluaciones', function ($table) {
            $table->id();
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->string('tipo_plantilla', 50);
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->string('resultado', 50)->nullable();
            $table->text('observaciones_globales')->nullable();
            $table->json('criterios')->nullable();
            $table->text('firma')->nullable();
            $table->string('celular', 20)->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $evaluacion = new Evaluacion();
        $this->assertSame(
            ['id_trabajo', 'id_profesor', 'tipo_plantilla', 'nota_final', 'resultado',
            'observaciones_globales', 'criterios', 'firma', 'celular'],
            $evaluacion->getFillable()
        );
    }

    public function test_belongs_to_trabajo(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Proyecto X']);
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Test',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $evaluacion = Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Aprobado',
        ]);

        $this->assertInstanceOf(Trabajo::class, $evaluacion->trabajo);
        $this->assertSame('Proyecto X', $evaluacion->trabajo->titulo);
    }

    public function test_belongs_to_profesor(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Proyecto X']);
        $usuario = Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Test',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $evaluacion = Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Aprobado',
        ]);

        $this->assertInstanceOf(Profesor::class, $evaluacion->profesor);
    }

    public function test_criteria_cast_to_array(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);

        $criterios = [
            ['nombre' => 'Calidad', 'puntaje' => 4.5],
            ['nombre' => 'Presentación', 'puntaje' => 5.0],
        ];

        $evaluacion = Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Aprobado',
            'criterios' => $criterios,
        ]);

        $this->assertIsArray($evaluacion->criterios);
        $this->assertCount(2, $evaluacion->criterios);
        $this->assertSame('Calidad', $evaluacion->criterios[0]['nombre']);
    }

    public function test_nota_final_can_be_set(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $evaluacion = Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Aprobado',
            'nota_final' => 4.75,
        ]);

        $this->assertEquals(4.75, $evaluacion->nota_final);
    }

    public function test_resultado_can_be_rechazado(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $evaluacion = Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Rechazado',
        ]);

        $this->assertSame('Rechazado', $evaluacion->resultado);
    }
}
