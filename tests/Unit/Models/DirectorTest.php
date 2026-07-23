<?php

namespace Tests\Unit\Models;

use App\Models\Director;
use App\Models\Trabajo;
use Tests\TestCase;

class DirectorTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::create('directors', function ($table) {
            $table->id('id_director');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo_electronico')->unique();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('trabajo', function ($table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('director_trabajo', function ($table) {
            $table->id();
            $table->unsignedBigInteger('id_director');
            $table->unsignedBigInteger('id_trabajo');
            $table->string('rol')->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $director = new Director();
        $this->assertSame(
            ['nombre', 'apellido', 'correo_electronico'],
            $director->getFillable()
        );
    }

    public function test_belongs_to_many_trabajos(): void
    {
        $director = Director::create([
            'nombre' => 'Dr. Juan',
            'apellido' => 'Perez',
            'correo_electronico' => 'jperez@uni.com',
        ]);
        $trabajo1 = Trabajo::create(['titulo' => 'Tesis A']);
        $trabajo2 = Trabajo::create(['titulo' => 'Tesis B']);

        $director->trabajos()->attach($trabajo1->id_trabajo, ['rol' => 'director']);
        $director->trabajos()->attach($trabajo2->id_trabajo, ['rol' => 'subdirector']);

        $this->assertCount(2, $director->trabajos);
        $this->assertSame('Tesis A', $director->trabajos->first()->titulo);
    }

    public function test_correo_electronico_unique(): void
    {
        Director::create([
            'nombre' => 'A', 'apellido' => 'B',
            'correo_electronico' => 'dup@uni.com',
        ]);
        $this->expectException(\Illuminate\Database\QueryException::class);
        Director::create([
            'nombre' => 'C', 'apellido' => 'D',
            'correo_electronico' => 'dup@uni.com',
        ]);
    }
}
