<?php

namespace Tests\Unit\Models;

use App\Models\Rubrica;
use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use Tests\TestCase;

class RubricaTest extends TestCase
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

        \Illuminate\Support\Facades\Schema::create('rubrica', function ($table) {
            $table->id('id_rubrica');
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->boolean('activo')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo_rubrica', function ($table) {
            $table->id('id_trabajo_rubrica');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_rubrica');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $rubrica = new Rubrica();
        $this->assertSame(
            ['id_tipo', 'archivo', 'mime_type', 'activo', 'fecha_creacion'],
            $rubrica->getFillable()
        );
    }

    public function test_belongs_to_tipo(): void
    {
        $tipo = TipoTrabajo::create(['nombre_tipo' => 'Tesis']);
        $rubrica = Rubrica::create(['id_tipo' => $tipo->id_tipo, 'archivo' => 'rubrica.docx']);

        $this->assertInstanceOf(TipoTrabajo::class, $rubrica->tipo);
        $this->assertSame('Tesis', $rubrica->tipo->nombre_tipo);
    }

    public function test_belongs_to_many_trabajos(): void
    {
        $rubrica = Rubrica::create(['archivo' => 'rubrica.docx']);
        $trabajo = Trabajo::create(['titulo' => 'Proyecto']);

        $rubrica->trabajos()->attach($trabajo->id_trabajo, ['fecha_asignacion' => now()]);

        $this->assertCount(1, $rubrica->trabajos);
        $this->assertInstanceOf(Trabajo::class, $rubrica->trabajos->first());
    }

    public function test_timestamps_disabled(): void
    {
        $rubrica = new Rubrica();
        $this->assertFalse($rubrica->timestamps);
    }

    public function test_mime_type_nullable(): void
    {
        $rubrica = Rubrica::create(['archivo' => 'test.docx']);
        $this->assertNull($rubrica->mime_type);
    }
}
