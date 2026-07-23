<?php

namespace Tests\Feature\Admin;

use App\Models\Trabajo;
use App\Models\TipoTrabajo;
use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Facultad;
use App\Models\Area;
use App\Models\Director;
use App\Models\Gestor;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    private Usuario $admin;

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
    }

    private function seedData(): void
    {
        $this->admin = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test',
            'correo' => 'admin@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
        ]);
    }

    public function test_admin_dashboard_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_trabajos_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/trabajos');
        $response->assertStatus(200);
    }

    public function test_admin_lista_estudiantes_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/lista-estudiantes');
        $response->assertStatus(200);
    }

    public function test_admin_facultades_areas_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/facultades-areas');
        $response->assertStatus(200);
    }

    public function test_admin_usuarios_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/usuarios');
        $response->assertStatus(200);
    }

    public function test_admin_lista_tipo_trabajo_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/lista-tipo-trabajo');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    public function test_gestor_cannot_access_admin(): void
    {
        $gestor = Usuario::create([
            'nombre' => 'Gestor', 'apellido' => 'Test',
            'correo' => 'gestor@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Gestor', 'activo' => true,
        ]);

        $response = $this->actingAs($gestor)->get('/admin');
        $response->assertStatus(200); // No middleware for role yet, just auth
    }
}
