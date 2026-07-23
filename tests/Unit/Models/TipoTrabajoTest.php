<?php

namespace Tests\Unit\Models;

use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use App\Models\Rubrica;
use Tests\TestCase;

class TipoTrabajoTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_fillable_attributes(): void
    {
        $tipo = new TipoTrabajo();
        $this->assertSame(['nombre_tipo', 'activo'], $tipo->getFillable());
    }

    public function test_has_many_trabajos(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Tesis']);
        Trabajo::create(['titulo' => 'A', 'id_tipo' => $tipo->id_tipo]);
        Trabajo::create(['titulo' => 'B', 'id_tipo' => $tipo->id_tipo]);

        $this->assertCount(2, $tipo->trabajos);
    }

    public function test_has_many_rubrica(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Tesis']);
        Rubrica::create(['id_tipo' => $tipo->id_tipo, 'archivo' => 'r1.docx']);
        Rubrica::create(['id_tipo' => $tipo->id_tipo, 'archivo' => 'r2.docx']);

        $this->assertCount(2, $tipo->rubrica);
    }

    public function test_activo_default_true(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Test']);
        $this->assertTrue($tipo->fresh()->activo);
    }
}
