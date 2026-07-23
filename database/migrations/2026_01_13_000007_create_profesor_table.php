<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profesor', function (Blueprint $table) {
            $table->id('id_profesor'); // id_profesor SERIAL PRIMARY KEY

            $table->unsignedBigInteger('id_usuario'); // FK a usuario
            $table->unsignedBigInteger('id_area');    // FK a area

            $table->timestamps();

            // Relaciones
            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuario')
                ->onDelete('cascade'); // si se borra el usuario, se elimina el profesor

            $table->foreign('id_area')
                ->references('id_area')->on('area')
                ->onDelete('restrict'); // no permitir eliminar un área con profesores
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesor');
    }
};
