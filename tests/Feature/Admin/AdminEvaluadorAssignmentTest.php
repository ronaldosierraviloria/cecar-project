<?php

namespace Tests\Feature\Admin;

use App\Models\Trabajo;
use App\Models\TipoTrabajo;
use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Area;
use App\Models\Facultad;
use App\Models\Estudiante;
use App\Models\HistorialEstado;
use App\Models\TrabajoProfesor;
use Tests\TestCase;

class AdminEvaluadorAssignmentTest extends TestCase
{
    private Usuario $admin;
    private Trabajo $trabajo;
    private Profesor $profesor1;
    private Profesor $profesor2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
        $this->seedData();
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

        \Illuminate\Support\Facades\Schema::create('tipo_trabajo', function ($table) {
            $table->id('id_tipo');
            $table->string('nombre_tipo', 100);
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

        \Illuminate\Support\Facades\Schema::create('historial_estados', function ($table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->string('estado');
            $table->string('version_documento')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('observacion_estado')->nullable();
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

        \Illuminate\Support\Facades\Schema::create('notifications', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('rubrica', function ($table) {
            $table->id('id_rubrica');
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->date('fecha_creacion')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo_rubrica', function ($table) {
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_rubrica');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->primary(['id_trabajo', 'id_rubrica']);
        });

        \Illuminate\Support\Facades\Schema::create('directors', function ($table) {
            $table->id('id_director');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo_electronico', 150)->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('director_trabajo', function ($table) {
            $table->unsignedBigInteger('id_director');
            $table->unsignedBigInteger('id_trabajo');
            $table->primary(['id_director', 'id_trabajo']);
        });
    }

    private function seedData(): void
    {
        $this->admin = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test',
            'correo' => 'admin@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $facultad = Facultad::create(['nombre_facultad' => 'Ing.']);
        $area = Area::create(['nombre_area' => 'Sistemas', 'id_facultad' => $facultad->id_facultad]);

        $this->trabajo = Trabajo::create([
            'titulo' => 'Proyecto de Grado',
            'estado' => 'subido',
        ]);

        // Crear un estudiante con área para que el filtro lo tome
        Estudiante::create([
            'nombre' => 'Juan', 'apellido' => 'Perez',
            'id_trabajo' => $this->trabajo->id_trabajo,
            'id_area' => $area->id_area,
        ]);

        $usuario1 = Usuario::create([
            'nombre' => 'Eval1', 'apellido' => 'Uno',
            'correo' => 'eval1@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador', 'activo' => true,
        ]);
        $this->profesor1 = Profesor::create(['id_usuario' => $usuario1->id_usuario, 'id_area' => $area->id_area]);

        $usuario2 = Usuario::create([
            'nombre' => 'Eval2', 'apellido' => 'Dos',
            'correo' => 'eval2@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador', 'activo' => true,
        ]);
        $this->profesor2 = Profesor::create(['id_usuario' => $usuario2->id_usuario, 'id_area' => $area->id_area]);
    }

    public function test_asignar_evaluador_page_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/asignar-evaluador/{$this->trabajo->id_trabajo}");
        $response->assertStatus(200);
    }

    public function test_guardar_evaluadores_asigna_correctamente(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/guardar-evaluadores/{$this->trabajo->id_trabajo}", [
            'evaluadores' => [$this->profesor1->id_profesor, $this->profesor2->id_profesor],
        ]);

        $response->assertRedirect();

        $this->trabajo->refresh();
        $this->assertSame('en_revision', $this->trabajo->estado);

        $this->assertDatabaseHas('trabajo_profesor', [
            'id_trabajo' => $this->trabajo->id_trabajo,
            'id_profesor' => $this->profesor1->id_profesor,
        ]);
        $this->assertDatabaseHas('trabajo_profesor', [
            'id_trabajo' => $this->trabajo->id_trabajo,
            'id_profesor' => $this->profesor2->id_profesor,
        ]);
    }

    public function test_guardar_single_evaluador(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/guardar-evaluadores/{$this->trabajo->id_trabajo}", [
            'evaluadores' => [$this->profesor1->id_profesor],
        ]);

        $response->assertRedirect();
        $this->assertSame('en_revision', $this->trabajo->fresh()->estado);
    }

    public function test_guardar_more_than_two_evaluators_fails(): void
    {
        $usuario3 = Usuario::create([
            'nombre' => 'Eval3', 'apellido' => 'Tres',
            'correo' => 'eval3@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador', 'activo' => true,
        ]);
        $profesor3 = Profesor::create(['id_usuario' => $usuario3->id_usuario]);

        $response = $this->actingAs($this->admin)->post("/admin/guardar-evaluadores/{$this->trabajo->id_trabajo}", [
            'evaluadores' => [$this->profesor1->id_profesor, $this->profesor2->id_profesor, $profesor3->id_profesor],
        ]);

        $response->assertSessionHasErrors(['evaluadores']);
    }

    public function test_quitar_evaluadores(): void
    {
        $this->trabajo->evaluadores()->attach($this->profesor1->id_profesor, ['fecha_asignacion' => now()]);
        $this->trabajo->update(['estado' => 'en_revision']);

        $response = $this->actingAs($this->admin)->post("/admin/trabajo/{$this->trabajo->id_trabajo}/quitar-evaluadores");
        $response->assertRedirect();

        $this->trabajo->refresh();
        $this->assertSame('subido', $this->trabajo->estado);
        $this->assertCount(0, $this->trabajo->evaluadores);
    }

    public function test_aprobar_trabajo(): void
    {
        $this->trabajo->update(['estado' => 'version_corregida_subida']);

        $response = $this->actingAs($this->admin)->post("/admin/trabajo/{$this->trabajo->id_trabajo}/aprobar");
        $response->assertRedirect();

        $this->assertSame('aprobado', $this->trabajo->fresh()->estado);
    }

    public function test_retirar_trabajo(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/trabajo/{$this->trabajo->id_trabajo}/retirar");
        $response->assertRedirect();

        $this->assertTrue((bool) $this->trabajo->fresh()->retirado);
    }

    public function test_prorrogar_plazo(): void
    {
        $this->trabajo->evaluadores()->attach($this->profesor1->id_profesor, [
            'fecha_asignacion' => now()->subDays(10),
            'fecha_limite_revision' => now()->addDays(11),
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/trabajo-evaluador/prorrogar', [
            'id_trabajo' => $this->trabajo->id_trabajo,
            'id_profesor' => $this->profesor1->id_profesor,
            'dias' => 15,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_detalles_trabajo(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/proyecto/{$this->trabajo->id_trabajo}");
        $response->assertStatus(200);
    }
}
