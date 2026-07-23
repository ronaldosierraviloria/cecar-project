<?php

namespace Tests\Unit\Models;

use App\Models\Estudiante;
use App\Models\Trabajo;
use App\Models\Area;
use App\Models\Facultad;
use Tests\TestCase;

class EstudianteTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
    }

    private function createDefaultTables(): void
    {
        \Illuminate\Support\Facades\Schema::create('estudiante', function ($table) {
            $table->id('id_estudiante');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->nullable();
            $table->unsignedBigInteger('id_trabajo')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->timestamps();
        });

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
    }

    public function test_fillable_attributes(): void
    {
        $estudiante = new Estudiante();
        $this->assertSame(
            ['id_trabajo', 'nombre', 'apellido', 'correo', 'id_area'],
            $estudiante->getFillable()
        );
    }

    public function test_belongs_to_trabajo(): void
    {
        $trabajo = Trabajo::create(['titulo' => 'Proyecto Z']);
        $estudiante = Estudiante::create([
            'nombre' => 'Ana', 'apellido' => 'Lopez',
            'id_trabajo' => $trabajo->id_trabajo,
        ]);

        $this->assertInstanceOf(Trabajo::class, $estudiante->trabajo);
        $this->assertSame('Proyecto Z', $estudiante->trabajo->titulo);
    }

    public function test_belongs_to_area(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ing.']);
        $area = Area::create(['nombre_area' => 'Civil', 'id_facultad' => $facultad->id_facultad]);
        $estudiante = Estudiante::create([
            'nombre' => 'Ana', 'apellido' => 'Lopez',
            'id_area' => $area->id_area,
        ]);

        $this->assertInstanceOf(Area::class, $estudiante->area);
        $this->assertSame('Civil', $estudiante->area->nombre_area);
    }

    public function test_timestamps_disabled(): void
    {
        $estudiante = new Estudiante();
        $this->assertFalse($estudiante->timestamps);
    }

    public function test_correo_nullable(): void
    {
        $estudiante = Estudiante::create([
            'nombre' => 'Luis', 'apellido' => 'Garcia',
        ]);
        $this->assertNull($estudiante->correo);
    }

    public function test_nombre_apellido_fillable(): void
    {
        $estudiante = Estudiante::create([
            'nombre' => 'Maria', 'apellido' => 'Torres',
        ]);
        $this->assertSame('Maria', $estudiante->nombre);
        $this->assertSame('Torres', $estudiante->apellido);
    }
}
