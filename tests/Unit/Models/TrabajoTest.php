<?php

namespace Tests\Unit\Models;

use App\Models\Trabajo;
use App\Models\TipoTrabajo;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Retroalimentacion;
use App\Models\HistorialEstado;
use App\Models\Evaluacion;
use App\Models\Rubrica;
use App\Models\Director;
use Tests\TestCase;

class TrabajoTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
    }

    private function createDefaultTables(): void
    {
        \Illuminate\Support\Facades\Schema::create('tipo_trabajo', function ($table) {
            $table->id('id_tipo');
            $table->string('nombre_tipo');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->timestamp('fecha_subida')->nullable();
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('plantilla_rubrica', 50)->nullable();
            $table->string('archivo_pdf', 255)->nullable();
            $table->string('version_actual', 50)->default('v1');
            $table->string('estado', 50)->nullable();
            $table->boolean('retirado')->default(false);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('estudiante', function ($table) {
            $table->id('id_estudiante');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->nullable();
            $table->unsignedBigInteger('id_trabajo')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->boolean('terminos_aceptados')->default(false);
            $table->boolean('datos_aceptados')->default(false);
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

        \Illuminate\Support\Facades\Schema::create('historial_estados', function ($table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->string('estado');
            $table->string('version_documento')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('observacion_estado')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('rubrica', function ($table) {
            $table->id('id_rubrica');
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->boolean('activo')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo_rubrica', function ($table) {
            $table->id('id_trabajo_rubrica');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_rubrica');
            $table->timestamp('fecha_asignacion')->nullable();
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

        \Illuminate\Support\Facades\Schema::create('directors', function ($table) {
            $table->id('id_director');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo_electronico')->unique();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('director_trabajo', function ($table) {
            $table->id();
            $table->unsignedBigInteger('id_director');
            $table->unsignedBigInteger('id_trabajo');
            $table->string('rol')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('facultad', function ($table) {
            $table->id('id_facultad');
            $table->string('nombre_facultad', 150);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('area', function ($table) {
            $table->id('id_area');
            $table->string('nombre_area', 100);
            $table->unsignedBigInteger('id_facultad')->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $trabajo = new Trabajo();
        $fillable = [
            'titulo', 'fecha_subida', 'id_tipo', 'plantilla_rubrica',
            'archivo_pdf', 'version_actual', 'estado', 'retirado',
        ];
        $this->assertSame($fillable, $trabajo->getFillable());
    }

    public function test_table_name_and_primary_key(): void
    {
        $trabajo = new Trabajo();
        $this->assertSame('trabajo', $trabajo->getTable());
        $this->assertSame('id_trabajo', $trabajo->getKeyName());
    }

    public function test_has_many_estudiantes(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        Estudiante::create(['nombre' => 'Juan', 'apellido' => 'Perez', 'id_trabajo' => $trabajo->id_trabajo]);
        Estudiante::create(['nombre' => 'Ana', 'apellido' => 'Lopez', 'id_trabajo' => $trabajo->id_trabajo]);

        $this->assertCount(2, $trabajo->estudiante);
        $this->assertInstanceOf(Estudiante::class, $trabajo->estudiante->first());
    }

    public function test_belongs_to_tipo(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Tesis']);
        $trabajo = Trabajo::create(['titulo' => 'Test', 'id_tipo' => $tipo->id_tipo]);

        $this->assertInstanceOf(TipoTrabajo::class, $trabajo->tipo);
        $this->assertSame('Tesis', $trabajo->tipo->nombre_tipo);
    }

    public function test_belongs_to_many_evaluadores(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = \App\Models\Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'Dor', 'correo' => 'eval@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);
        $trabajo->evaluadores()->attach($profesor->id_profesor, ['fecha_asignacion' => now()]);

        $this->assertCount(1, $trabajo->evaluadores);
        $this->assertInstanceOf(Profesor::class, $trabajo->evaluadores->first());
    }

    public function test_belongs_to_many_rubricas(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Test']);
        $rubrica = Rubrica::create(['id_tipo' => $tipo->id_tipo, 'archivo' => 'rubrica.docx']);
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $trabajo->rubricas()->attach($rubrica->id_rubrica, ['fecha_asignacion' => now()]);

        $this->assertCount(1, $trabajo->rubricas);
        $this->assertInstanceOf(Rubrica::class, $trabajo->rubricas->first());
    }

    public function test_has_many_retroalimentaciones(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = \App\Models\Usuario::create([
            'nombre' => 'User', 'apellido' => 'Test', 'correo' => 'user@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        Retroalimentacion::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'user_id' => $usuario->id_usuario,
            'comentario' => 'Buen trabajo',
        ]);

        $this->assertCount(1, $trabajo->retroalimentaciones);
        $this->assertSame('Buen trabajo', $trabajo->retroalimentaciones->first()->comentario);
    }

    public function test_has_many_historial_estados(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $usuario = \App\Models\Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test', 'correo' => 'admin@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Administrador',
        ]);

        HistorialEstado::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'estado' => 'subido',
            'version_documento' => 'v1',
            'user_id' => $usuario->id_usuario,
        ]);

        $this->assertCount(1, $trabajo->historialEstados);
        $this->assertSame('subido', $trabajo->historialEstados->first()->estado);
    }

    public function test_has_many_evaluaciones(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $profesor = Profesor::create(['id_usuario' => \App\Models\Usuario::create([
            'nombre' => 'Prof', 'apellido' => 'Test', 'correo' => 'prof@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ])->id_usuario]);

        Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Aprobado',
            'nota_final' => 4.5,
        ]);

        $this->assertCount(1, $trabajo->evaluaciones);
        $this->assertEquals(4.5, $trabajo->evaluaciones->first()->nota_final);
    }

    public function test_belongs_to_many_directores(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $director = Director::create([
            'nombre' => 'Dr.',
            'apellido' => 'Smith',
            'correo_electronico' => 'smith@uni.com',
        ]);
        $trabajo->directores()->attach($director->id_director, ['rol' => 'director']);

        $this->assertCount(1, $trabajo->directores);
        $this->assertSame('Dr.', $trabajo->directores->first()->nombre);
    }

    public function test_default_version_is_v1(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $this->assertSame('v1', $trabajo->fresh()->version_actual);
    }

    public function test_default_retirado_is_false(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $this->assertFalse($trabajo->fresh()->retirado);
    }

    public function test_estado_can_be_subido(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'subido']);
        $this->assertSame('subido', $trabajo->estado);
    }

    public function test_estado_can_be_en_revision(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'en_revision']);
        $this->assertSame('en_revision', $trabajo->estado);
    }

    public function test_estado_can_be_aprobado(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'aprobado']);
        $this->assertSame('aprobado', $trabajo->estado);
    }

    public function test_estado_can_be_retroalimentacion_emitida(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'retroalimentacion_emitida']);
        $this->assertSame('retroalimentacion_emitida', $trabajo->estado);
    }

    public function test_estado_can_be_version_corregida_subida(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'version_corregida_subida']);
        $this->assertSame('version_corregida_subida', $trabajo->estado);
    }
}
