<?php

namespace Tests\Unit\Models;

use App\Models\Area;
use App\Models\Facultad;
use App\Models\Profesor;
use App\Models\Usuario;
use Tests\TestCase;

class AreaTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::create('area', function ($table) {
            $table->id('id_area');
            $table->string('nombre_area', 100);
            $table->unsignedBigInteger('id_facultad')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('facultad', function ($table) {
            $table->id('id_facultad');
            $table->string('nombre_facultad', 150);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->timestamps();
        });

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
    }

    public function test_fillable_attributes(): void
    {
        $area = new Area();
        $this->assertSame(['nombre_area', 'id_facultad'], $area->getFillable());
    }

    public function test_belongs_to_facultad(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ingeniería']);
        $area = Area::create(['nombre_area' => 'Sistemas', 'id_facultad' => $facultad->id_facultad]);

        $this->assertInstanceOf(Facultad::class, $area->facultad);
        $this->assertSame('Ingeniería', $area->facultad->nombre_facultad);
    }

    public function test_has_many_profesores(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ing.']);
        $area = Area::create(['nombre_area' => 'Electrónica', 'id_facultad' => $facultad->id_facultad]);
        $usuario = Usuario::create([
            'nombre' => 'A', 'apellido' => 'B', 'correo' => 'a@test.com',
            'password' => bcrypt('pass'), 'rol' => 'Evaluador',
        ]);
        Profesor::create(['id_usuario' => $usuario->id_usuario, 'id_area' => $area->id_area]);

        $this->assertCount(1, $area->profesores);
        $this->assertInstanceOf(Profesor::class, $area->profesores->first());
    }
}
