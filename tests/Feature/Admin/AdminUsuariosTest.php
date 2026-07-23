<?php

namespace Tests\Feature\Admin;

use App\Models\Usuario;
use App\Models\Gestor;
use App\Models\Profesor;
use Tests\TestCase;

class AdminUsuariosTest extends TestCase
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
            $table->unsignedBigInteger('id_facultad')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('gestor', function ($table) {
            $table->id('id_gestor');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('area', function ($table) {
            $table->id('id_area');
            $table->string('nombre_area', 100);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('facultad', function ($table) {
            $table->id('id_facultad');
            $table->string('nombre_facultad', 150);
            $table->timestamps();
        });
    }

    public function test_create_admin_user(): void
    {
        $facultad = \App\Models\Facultad::create(['nombre_facultad' => 'Ing.']);
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'nombre' => 'New',
            'apellido' => 'Admin',
            'correo' => 'newadmin@test.com',
            'contraseña' => 'password',
            'contraseña_confirmation' => 'password',
            'rol' => 'Administrador',
            'id_facultad' => $facultad->id_facultad,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuario', ['correo' => 'newadmin@test.com', 'rol' => 'Administrador']);
    }

    public function test_create_gestor_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'nombre' => 'Gestor',
            'apellido' => 'User',
            'correo' => 'gestor@test.com',
            'contraseña' => 'password',
            'contraseña_confirmation' => 'password',
            'rol' => 'Gestor',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuario', ['correo' => 'gestor@test.com', 'rol' => 'Gestor']);
        $this->assertDatabaseHas('gestor', ['id_usuario' => Usuario::where('correo', 'gestor@test.com')->first()->id_usuario]);
    }

    public function test_create_evaluador_user(): void
    {
        $area = \App\Models\Area::create(['nombre_area' => 'Sistemas']);
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'nombre' => 'Eval',
            'apellido' => 'User',
            'correo' => 'eval@test.com',
            'contraseña' => 'password',
            'contraseña_confirmation' => 'password',
            'rol' => 'Evaluador',
            'id_area' => $area->id_area,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuario', ['correo' => 'eval@test.com', 'rol' => 'Evaluador']);
    }

    public function test_update_user(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Old', 'apellido' => 'Name',
            'correo' => 'old@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Gestor', 'activo' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/usuarios/{$usuario->id_usuario}", [
            'nombre' => 'New',
            'apellido' => 'Name',
            'correo' => 'old@test.com',
            'rol' => 'Gestor',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuario', ['nombre' => 'New', 'id_usuario' => $usuario->id_usuario]);
    }

    public function test_toggle_active_user(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Test', 'apellido' => 'User',
            'correo' => 'test@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Evaluador', 'activo' => true,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/usuarios/{$usuario->id_usuario}/toggle");

        $response->assertRedirect();
        $this->assertEquals(0, $usuario->fresh()->activo);
    }
}
