<?php

namespace Tests\Feature\Evaluador;

use App\Models\Trabajo;
use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Area;
use App\Models\Facultad;
use App\Models\TipoTrabajo;
use App\Models\Evaluacion;
use App\Models\Retroalimentacion;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EvaluadorDashboardTest extends TestCase
{
    private Usuario $evaluador;
    private Profesor $profesor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
        $this->seedData();
        $this->registerTestRoutes();
    }

    private function registerTestRoutes(): void
    {
        Route::get('/evaluador/retroalimentacion/{id}', [
            \App\Http\Controllers\Evaluador\RetroalimentacionController::class, 'show',
        ])->name('evaluador.retroalimentacion.show');
        Route::get('/evaluador/retroalimentacion/{id}/otros', [
            \App\Http\Controllers\Evaluador\RetroalimentacionController::class, 'getOtherComments',
        ])->name('evaluador.retroalimentacion.otros');
        Route::post('/evaluador/retroalimentacion/{id}/guardar', [
            \App\Http\Controllers\Evaluador\RetroalimentacionController::class, 'store',
        ])->name('evaluador.retroalimentacion.store');
        Route::post('/evaluador/retroalimentacion/{id}/finalizar', [
            \App\Http\Controllers\Evaluador\RetroalimentacionController::class, 'finalizarRetroalimentacion',
        ])->name('evaluador.retroalimentacion.finalizar');
        Route::post('/evaluador/revision/{id}/finalizar', [
            \App\Http\Controllers\Evaluador\RetroalimentacionController::class, 'finalizarRevision',
        ])->name('evaluador.revision.finalizar');

        // Refresh name/action lookups so the new routes are findable by name
        app('router')->getRoutes()->refreshNameLookups();
        app('router')->getRoutes()->refreshActionLookups();
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

        \Illuminate\Support\Facades\Schema::create('estudiante', function ($table) {
            $table->id('id_estudiante');
            $table->unsignedBigInteger('id_trabajo');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150);
            $table->unsignedBigInteger('id_area')->nullable();
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
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('notifications', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    private function seedData(): void
    {
        $this->evaluador = Usuario::create([
            'nombre' => 'Evaluador', 'apellido' => 'Test',
            'correo' => 'eval@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador', 'activo' => true,
        ]);
        $this->profesor = Profesor::create(['id_usuario' => $this->evaluador->id_usuario]);
    }

    public function test_evaluador_dashboard_returns_200(): void
    {
        $response = $this->actingAs($this->evaluador)->get('/evaluador');
        $response->assertStatus(200);
    }

    public function test_evaluador_evaluacion_show_returns_200(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'plantilla_rubrica' => 'trabajo_de_grado']);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, ['fecha_asignacion' => now()]);

        $response = $this->actingAs($this->evaluador)->get("/evaluador/evaluacion/{$trabajo->id_trabajo}");
        $response->assertStatus(200);
    }

    public function test_guardar_evaluacion(): void
    {
        $trabajo = Trabajo::create([
            'titulo' => 'Test',
            'plantilla_rubrica' => 'trabajo_de_grado',
            'estado' => 'en_revision',
        ]);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, [
            'fecha_asignacion' => now(),
            'estado_revision' => 'Pendiente',
        ]);

        $this->actingAs($this->evaluador);

        $response = $this->post("/trabajos/{$trabajo->id_trabajo}/guardar-evaluacion", [
            'tipo_plantilla' => 'trabajo_de_grado',
            'nota_final' => 4.5,
            'resultado' => 'Aprobado',
            'observaciones_globales' => 'Excelente trabajo',
            'criterios' => [
                ['nombre' => 'Calidad', 'puntaje' => 4.5],
                ['nombre' => 'Originalidad', 'puntaje' => 4.0],
            ],
            'firma' => 'Firma digital',
            'celular' => '3001234567',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('evaluaciones', [
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $this->profesor->id_profesor,
            'nota_final' => 4.5,
        ]);
    }

    public function test_guardar_progreso_evaluacion(): void
    {
        $trabajo = Trabajo::create([
            'titulo' => 'Test',
            'plantilla_rubrica' => 'trabajo_de_grado',
            'estado' => 'en_revision',
        ]);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, ['fecha_asignacion' => now()]);

        $this->actingAs($this->evaluador);

        $response = $this->post("/trabajos/{$trabajo->id_trabajo}/guardar-progreso", [
            'tipo_plantilla' => 'trabajo_de_grado',
            'nota_final' => 3.5,
            'resultado' => 'Aprobado',
            'observaciones_globales' => 'En progreso',
        ]);

        $response->assertStatus(200);
    }

    public function test_aceptar_terminos(): void
    {
        $this->actingAs($this->evaluador);

        $response = $this->post('/evaluador/aceptar-terminos', [
            'terminos_aceptados' => '1',
            'datos_aceptados' => '1',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $this->profesor->fresh()->terminos_aceptados);
        $this->assertEquals(1, $this->profesor->fresh()->datos_aceptados);
    }

    public function test_retroalimentacion_show_403_if_not_assigned(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test']);
        $response = $this->actingAs($this->evaluador)->get(
            "/evaluador/retroalimentacion/{$trabajo->id_trabajo}"
        );
        $response->assertStatus(403);
    }

    public function test_retroalimentacion_show_200_if_assigned(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'en_revision']);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, ['fecha_asignacion' => now()]);

        $response = $this->actingAs($this->evaluador)->get(
            "/evaluador/retroalimentacion/{$trabajo->id_trabajo}"
        );
        $response->assertStatus(200);
    }

    public function test_store_retroalimentacion(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'en_revision']);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, ['fecha_asignacion' => now()]);

        $this->actingAs($this->evaluador);

        $response = $this->postJson(
            "/evaluador/retroalimentacion/{$trabajo->id_trabajo}/guardar",
            ['comentarios' => ['Buen trabajo', 'Mejorar metodología']]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertCount(2, Retroalimentacion::where('trabajo_grado_id', $trabajo->id_trabajo)->get());
    }

    public function test_finalizar_retroalimentacion_un_solo_evaluador(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'estado' => 'en_revision']);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, ['fecha_asignacion' => now()]);

        $this->actingAs($this->evaluador);

        $response = $this->postJson(
            "/evaluador/retroalimentacion/{$trabajo->id_trabajo}/finalizar"
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'ambos' => true]);
        $this->assertSame('retroalimentacion_emitida', $trabajo->fresh()->estado);
    }

    public function test_evaluacion_detalles_returns_200(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'plantilla_rubrica' => 'trabajo_de_grado']);
        $trabajo->evaluadores()->attach($this->profesor->id_profesor, ['fecha_asignacion' => now()]);

        Evaluacion::create([
            'id_trabajo' => $trabajo->id_trabajo,
            'id_profesor' => $this->profesor->id_profesor,
            'tipo_plantilla' => 'trabajo_de_grado',
            'resultado' => 'Aprobado',
            'nota_final' => 4.0,
        ]);

        $response = $this->actingAs($this->evaluador)->get(
            "/evaluador/evaluacion/{$trabajo->id_trabajo}/detalles"
        );
        $response->assertStatus(200);
    }
}
