<?php

namespace Tests\Unit\Models;

use App\Models\Alerta;
use App\Models\TrabajoProfesor;
use Tests\TestCase;

class AlertaTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
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

        \Illuminate\Support\Facades\Schema::create('profesor', function ($table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo_profesor', function ($table) {
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->timestamps();
            $table->primary(['id_trabajo', 'id_profesor']);
        });

        \Illuminate\Support\Facades\Schema::create('alerta', function ($table) {
            $table->id('id_alerta');
            $table->unsignedBigInteger('id_trabajo_profesor')->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->string('tipo_alerta', 100)->nullable();
            $table->boolean('leido')->nullable();
        });
    }

    public function test_fillable_attributes(): void
    {
        $alerta = new Alerta();
        $this->assertSame(
            ['id_trabajo_profesor', 'fecha_envio', 'tipo_alerta', 'leido'],
            $alerta->getFillable()
        );
    }

    public function test_belongs_to_trabajo_profesor(): void
    {
        $alerta = new Alerta();
        $this->assertTrue(method_exists($alerta, 'trabajoProfesor'));
    }

    public function test_timestamps_disabled(): void
    {
        $alerta = new Alerta();
        $this->assertFalse($alerta->timestamps);
    }
}
