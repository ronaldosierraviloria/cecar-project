<?php

namespace Tests\Feature\Gestor;

use App\Models\Trabajo;
use App\Models\TipoTrabajo;
use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Retroalimentacion;
use App\Models\HistorialEstado;
use App\Models\Rubrica;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TrabajoControllerTest extends TestCase
{
    private Usuario $gestor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
        $this->gestor = Usuario::create([
            'nombre' => 'Gestor', 'apellido' => 'Test',
            'correo' => 'gestor@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Gestor', 'activo' => true,
        ]);
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

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->boolean('terminos_aceptados')->default(false);
            $table->boolean('datos_aceptados')->default(false);
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

        \Illuminate\Support\Facades\Schema::create('estudiante', function ($table) {
            $table->id('id_estudiante');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->nullable();
            $table->unsignedBigInteger('id_trabajo')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
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

        \Illuminate\Support\Facades\Schema::create('notifications', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_gestor_dashboard_returns_200(): void
    {
        $response = $this->actingAs($this->gestor)->get('/gestor');
        $response->assertStatus(200);
    }

    public function test_crear_trabajo_form_returns_200(): void
    {
        $response = $this->actingAs($this->gestor)->get('/gestor/crear-trabajo');
        $response->assertStatus(200);
    }

    public function test_subir_nueva_version_borra_retroalimentaciones(): void
    {
        Notification::fake();
        Storage::fake('public');

        $trabajo = Trabajo::create([
            'titulo' => 'Proyecto Demo',
            'estado' => 'retroalimentacion_emitida',
            'version_actual' => 'v1',
            'archivo_pdf' => 'old.pdf',
        ]);

        Retroalimentacion::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'user_id' => $this->gestor->id_usuario,
            'comentario' => 'Comentario anterior',
            'version_documento' => 'v1',
        ]);

        $this->actingAs($this->gestor);

        $response = $this->post("/gestor/trabajo/{$trabajo->id_trabajo}/subir-nueva-version", [
            'archivo_pdf' => UploadedFile::fake()->create('nuevo.pdf', 100, 'application/pdf'),
            'observacion_estado' => 'Nueva versión',
        ]);

        $response->assertRedirect();

        $this->assertSame(0, Retroalimentacion::where('trabajo_grado_id', $trabajo->id_trabajo)->count());
        $trabajo->refresh();
        $this->assertSame('version_corregida_subida', $trabajo->estado);
        $this->assertSame('v2', $trabajo->version_actual);
    }

    public function test_gestor_lista_evaluadores_returns_200(): void
    {
        $response = $this->actingAs($this->gestor)->get('/gestor/lista-evaluadores');
        $response->assertStatus(200);
    }

    public function test_gestor_trabajo_detalles_returns_200(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test Proyecto']);
        $response = $this->actingAs($this->gestor)->get("/gestor/trabajo/{$trabajo->id_trabajo}");
        $response->assertStatus(200);
    }

    public function test_gestor_trabajo_archivo_returns_404_if_no_file(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Test', 'archivo_pdf' => 'storage/pdf/nonexistent.pdf']);
        $response = $this->actingAs($this->gestor)->get("/gestor/trabajo/archivo/{$trabajo->id_trabajo}");
        $response->assertStatus(404);
    }
}
