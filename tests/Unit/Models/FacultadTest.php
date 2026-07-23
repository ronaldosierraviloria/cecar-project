<?php

namespace Tests\Unit\Models;

use App\Models\Facultad;
use App\Models\Area;
use Tests\TestCase;

class FacultadTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::create('facultad', function ($table) {
            $table->id('id_facultad');
            $table->string('nombre_facultad', 150);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('area', function ($table) {
            $table->id('id_area');
            $table->string('nombre_area', 100);
            $table->unsignedBigInteger('id_facultad')->nullable();
            $table->timestamps();
        });
    }

    public function test_fillable_attributes(): void
    {
        $facultad = new Facultad();
        $this->assertSame(['nombre_facultad'], $facultad->getFillable());
    }

    public function test_has_many_areas(): void
    {
        $facultad = Facultad::create(['nombre_facultad' => 'Ciencias']);
        Area::create(['nombre_area' => 'Física', 'id_facultad' => $facultad->id_facultad]);
        Area::create(['nombre_area' => 'Química', 'id_facultad' => $facultad->id_facultad]);

        $this->assertCount(2, $facultad->areas);
        $this->assertInstanceOf(Area::class, $facultad->areas->first());
    }
}
