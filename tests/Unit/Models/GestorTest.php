<?php

namespace Tests\Unit\Models;

use App\Models\Gestor;
use App\Models\Usuario;
use App\Models\Trabajo;
use Tests\TestCase;

class GestorTest extends TestCase
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

        \Illuminate\Support\Facades\Schema::create('gestor', function ($table) {
            $table->id('id_gestor');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->unsignedBigInteger('id_gestor')->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $gestor = new Gestor();
        $this->assertSame(['id_usuario'], $gestor->getFillable());
    }

    public function test_belongs_to_usuario(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Gestor', 'apellido' => 'Test',
            'correo' => 'gestor@test.com', 'password' => bcrypt('pass'), 'rol' => 'Gestor',
        ]);
        $gestor = Gestor::create(['id_usuario' => $usuario->id_usuario]);

        $this->assertInstanceOf(Usuario::class, $gestor->usuario);
        $this->assertSame('Gestor', $gestor->usuario->nombre);
    }

    public function test_has_many_trabajos(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'G', 'apellido' => 'T', 'correo' => 'g@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Gestor',
        ]);
        $gestor = Gestor::create(['id_usuario' => $usuario->id_usuario]);
        $gestor->trabajos()->create(['titulo' => 'A']);
        $gestor->trabajos()->create(['titulo' => 'B']);

        $this->assertCount(2, $gestor->trabajos);
    }
}
