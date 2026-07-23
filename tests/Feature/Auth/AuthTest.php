<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
    }

    public function test_login_redirects_to_admin_dashboard(): void
    {
        Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'User',
            'correo' => 'admin@test.com', 'password' => bcrypt('12345'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $response = $this->post('/login', [
            'correo' => 'admin@test.com',
            'contraseña' => '12345',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_redirects_to_gestor_dashboard(): void
    {
        Usuario::create([
            'nombre' => 'Gestor', 'apellido' => 'User',
            'correo' => 'gestor@test.com', 'password' => bcrypt('12345'),
            'rol' => 'Gestor', 'activo' => true,
        ]);

        $response = $this->post('/login', [
            'correo' => 'gestor@test.com',
            'contraseña' => '12345',
        ]);

        $response->assertRedirect(route('gestor.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_redirects_to_evaluador_dashboard(): void
    {
        Usuario::create([
            'nombre' => 'Eval', 'apellido' => 'User',
            'correo' => 'eval@test.com', 'password' => bcrypt('12345'),
            'rol' => 'Evaluador', 'activo' => true,
        ]);

        $response = $this->post('/login', [
            'correo' => 'eval@test.com',
            'contraseña' => '12345',
        ]);

        $response->assertRedirect(route('evaluador.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'correo' => 'nonexistent@test.com',
            'contraseña' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        Usuario::create([
            'nombre' => 'User', 'apellido' => 'Test',
            'correo' => 'user@test.com', 'password' => bcrypt('correct'),
            'rol' => 'Gestor', 'activo' => true,
        ]);

        $response = $this->post('/login', [
            'correo' => 'user@test.com',
            'contraseña' => 'wrong',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        Usuario::create([
            'nombre' => 'User', 'apellido' => 'Test',
            'correo' => 'user@test.com', 'password' => bcrypt('12345'),
            'rol' => 'Gestor', 'activo' => true,
        ]);

        $this->post('/login', [
            'correo' => 'user@test.com',
            'contraseña' => '12345',
        ]);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_requires_validation(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['correo', 'contraseña']);
    }

    public function test_login_form_shows_when_not_authenticated(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_authenticated_user_redirects_from_login(): void
    {
        Usuario::create([
            'nombre' => 'Admin', 'apellido' => 'User',
            'correo' => 'admin@test.com', 'password' => bcrypt('12345'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $this->actingAs(Usuario::first());

        $response = $this->get('/login');
        $response->assertRedirect(route('admin.dashboard'));
    }
}
