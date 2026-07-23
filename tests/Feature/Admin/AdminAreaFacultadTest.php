<?php

namespace Tests\Feature\Admin;

use App\Models\Usuario;
use App\Models\Facultad;
use App\Models\Area;
use App\Models\Profesor;
use Tests\TestCase;

class AdminAreaFacultadTest extends TestCase
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

    // ── Facultad CRUD ──

    public function test_create_facultad(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/agregar-facultad', [
            'nombre_facultad' => 'Ingeniería',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('facultad', ['nombre_facultad' => 'Ingeniería']);
    }

    public function test_update_facultad(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ciencias']);
        $response = $this->actingAs($this->admin)->put("/admin/facultad/{$facultad->id_facultad}", [
            'nombre_facultad' => 'Ciencias Exactas',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('facultad', ['nombre_facultad' => 'Ciencias Exactas']);
    }

    public function test_delete_facultad_without_areas(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Medicina']);
        $response = $this->actingAs($this->admin)->delete("/admin/eliminar-facultad/{$facultad->id_facultad}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('facultad', ['id_facultad' => $facultad->id_facultad]);
    }

    // ── Area CRUD ──

    public function test_create_area(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ing.']);
        $response = $this->actingAs($this->admin)->post('/admin/agregar-area', [
            'nombre_area' => 'Sistemas',
            'id_facultad' => $facultad->id_facultad,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('area', ['nombre_area' => 'Sistemas']);
    }

    public function test_update_area(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ing.']);
        $area = Area::create(['nombre_area' => 'Civil', 'id_facultad' => $facultad->id_facultad]);
        $response = $this->actingAs($this->admin)->put("/admin/area/{$area->id_area}", [
            'nombre_area' => 'Civil Actualizado',
            'id_facultad' => $facultad->id_facultad,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('area', ['nombre_area' => 'Civil Actualizado']);
    }

    public function test_delete_area_without_profesores(): void
    {
        $area = Area::create(['nombre_area' => 'Matemáticas']);
        $response = $this->actingAs($this->admin)->delete("/admin/eliminar-area/{$area->id_area}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('area', ['id_area' => $area->id_area]);
    }

    public function test_delete_area_with_profesores_fails(): void
    {
        $area = Area::create(['nombre_area' => 'Física']);
        Profesor::create(['id_usuario' => $this->admin->id_usuario, 'id_area' => $area->id_area]);

        $response = $this->actingAs($this->admin)->delete("/admin/eliminar-area/{$area->id_area}");

        $response->assertRedirect();
        $this->assertDatabaseHas('area', ['id_area' => $area->id_area]);
    }
}
