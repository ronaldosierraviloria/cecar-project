<?php

namespace Tests\Feature\Admin;

use App\Models\Usuario;
use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use App\Models\Rubrica;
use Tests\TestCase;

class AdminTipoTrabajoTest extends TestCase
{
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
        $this->admin = Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'Test',
            'correo' => 'admin@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
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
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('rubrica', function ($table) {
            $table->id('id_rubrica');
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->boolean('activo')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
        });
    }

    public function test_create_tipo_trabajo(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/agregar-tipo-trabajo', [
            'nombre_tipo' => 'Tesis Doctoral',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tipo_trabajo', ['nombre_tipo' => 'Tesis Doctoral']);
    }

    public function test_update_tipo_trabajo(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Ensayo']);
        $response = $this->actingAs($this->admin)->put("/admin/tipo-trabajo/{$tipo->id_tipo}", [
            'nombre_tipo' => 'Ensayo Académico',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tipo_trabajo', ['nombre_tipo' => 'Ensayo Académico']);
    }

    public function test_delete_tipo_trabajo_without_trabajos(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Monografía']);
        $response = $this->actingAs($this->admin)->delete("/admin/eliminar-tipo-trabajo/{$tipo->id_tipo}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tipo_trabajo', ['id_tipo' => $tipo->id_tipo]);
    }

    public function test_toggle_active_tipo_trabajo(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Tesis', 'activo' => true]);
        $response = $this->actingAs($this->admin)->post("/admin/tipo-trabajo/{$tipo->id_tipo}/toggle");

        $response->assertRedirect();
        $this->assertEquals(0, $tipo->fresh()->activo);
    }
}
