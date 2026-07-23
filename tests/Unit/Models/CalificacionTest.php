<?php

namespace Tests\Unit\Models;

use App\Models\Calificacion;
use App\Models\Profesor;
use App\Models\Rubrica;
use App\Models\Usuario;
use App\Models\TipoTrabajo;
use Tests\TestCase;

class CalificacionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::create('rubrica', function ($table) {
            $table->id('id_rubrica');
            $table->string('archivo', 255)->nullable();
            $table->boolean('activo')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
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
        $cal = new Calificacion();
        $this->assertSame(
            ['id_rubrica', 'id_profesor', 'puntaje_total', 'observacion_final', 'comentarios', 'estado', 'fecha_calificacion'],
            $cal->getFillable()
        );
    }

    public function test_belongs_to_rubrica(): void
    {
        $rubrica = Rubrica::create(['archivo' => 'test.docx']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $cal = Calificacion::create([
            'id_rubrica' => $rubrica->id_rubrica,
            'id_profesor' => $profesor->id_profesor,
            'puntaje_total' => 85,
        ]);

        $this->assertInstanceOf(Rubrica::class, $cal->rubrica);
    }

    public function test_belongs_to_profesor(): void
    {
        $rubrica = Rubrica::create(['archivo' => 'test.docx']);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $cal = Calificacion::create([
            'id_rubrica' => $rubrica->id_rubrica,
            'id_profesor' => $profesor->id_profesor,
            'puntaje_total' => 85,
        ]);

        $this->assertInstanceOf(Profesor::class, $cal->profesor);
    }

    public function test_timestamps_disabled(): void
    {
        $cal = new Calificacion();
        $this->assertFalse($cal->timestamps);
    }
}
