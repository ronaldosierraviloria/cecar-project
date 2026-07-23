<?php

namespace Tests\Unit\Models;

use App\Models\TrabajoProfesor;
use Carbon\Carbon;
use Tests\TestCase;

class TrabajoProfesorTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createDefaultTables();
    }

    private function createDefaultTables(): void
    {
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
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_limite_revision')->nullable();
            $table->string('estado_revision')->nullable();
            $table->boolean('retroalimentacion_finalizada')->default(false);
            $table->timestamps();
            $table->primary(['id_trabajo', 'id_profesor']);
        });
    }

    public function test_esta_vencida_returns_false_when_no_deadline(): void
    {
        $pivot = new TrabajoProfesor();
        $pivot->fecha_limite_revision = null;

        $this->assertFalse($pivot->estaVencida);
    }

    public function test_esta_vencida_returns_false_when_deadline_in_future(): void
    {
        $pivot = new TrabajoProfesor();
        $pivot->fecha_limite_revision = Carbon::now()->addDays(5);

        $this->assertFalse($pivot->estaVencida);
    }

    public function test_esta_vencida_returns_true_when_deadline_passed(): void
    {
        $pivot = new TrabajoProfesor();
        $pivot->fecha_limite_revision = Carbon::now()->subDay();

        $this->assertTrue($pivot->estaVencida);
    }

    public function test_dias_restantes_returns_null_when_no_deadline(): void
    {
        $pivot = new TrabajoProfesor();
        $pivot->fecha_limite_revision = null;

        $this->assertNull($pivot->diasRestantes);
    }

    public function test_dias_restantes_returns_positive_in_future(): void
    {
        $pivot = new TrabajoProfesor();
        $pivot->fecha_limite_revision = Carbon::now()->addDays(10);

        $this->assertEqualsWithDelta(10, $pivot->diasRestantes, 1);
    }

    public function test_dias_restantes_returns_negative_when_overdue()
    {
        $pivot = new TrabajoProfesor();
        $pivot->fecha_limite_revision = Carbon::now()->subDays(3);

        $this->assertEqualsWithDelta(-3, $pivot->diasRestantes, 1);
    }

    public function test_fillable_attributes(): void
    {
        $pivot = new TrabajoProfesor();
        $this->assertSame(
            ['id_trabajo', 'id_profesor', 'fecha_asignacion', 'fecha_limite_revision', 'estado_revision', 'retroalimentacion_finalizada'],
            $pivot->getFillable()
        );
    }

    public function test_retroalimentacion_finalizada_default(): void
    {
        $trabajoProfesor = new TrabajoProfesor();
        $this->assertFalse($trabajoProfesor->retroalimentacion_finalizada);
    }
}
