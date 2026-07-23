<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificacionControllerTest extends TestCase
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

    public function test_index_returns_notifications_json(): void
    {
        $user = Usuario::create([
            'nombre' => 'Test', 'apellido' => 'User',
            'correo' => 'test@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['mensaje' => 'Test'],
        ]);

        $response = $this->actingAs($user)->get('/notificaciones');
        $response->assertStatus(200);
        $response->assertJsonStructure(['notificaciones', 'noLeidas']);
    }

    public function test_marcar_leida(): void
    {
        $user = Usuario::create([
            'nombre' => 'Test', 'apellido' => 'User',
            'correo' => 'test@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $notification = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['mensaje' => 'Test'],
        ]);

        $response = $this->actingAs($user)->post("/notificaciones/{$notification->id}/leida");
        $response->assertStatus(200);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_marcar_todas_leidas(): void
    {
        $user = Usuario::create([
            'nombre' => 'Test', 'apellido' => 'User',
            'correo' => 'test@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['mensaje' => 'Test 1'],
        ]);
        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['mensaje' => 'Test 2'],
        ]);

        $response = $this->actingAs($user)->post('/notificaciones/todas-leidas');
        $response->assertStatus(200);

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_destroy_all(): void
    {
        $user = Usuario::create([
            'nombre' => 'Test', 'apellido' => 'User',
            'correo' => 'test@test.com', 'password' => bcrypt('pass'),
            'rol' => 'Administrador', 'activo' => true,
        ]);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['mensaje' => 'Test'],
        ]);

        $response = $this->actingAs($user)->delete('/notificaciones/todas');
        $response->assertStatus(200);

        $this->assertSame(0, $user->notifications()->count());
    }
}
