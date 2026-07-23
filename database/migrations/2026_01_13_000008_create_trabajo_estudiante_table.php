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
        Schema::create('trabajo_estudiante', function (Blueprint $table) {
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_estudiante');

            // PK compuesta
            $table->primary(['id_trabajo', 'id_estudiante']);

            // FK Trabajo
            $table->foreign('id_trabajo')
                ->references('id_trabajo')->on('trabajo')
                ->onDelete('cascade');

            // FK Estudiante
            $table->foreign('id_estudiante')
                ->references('id_estudiante')->on('estudiante')
                ->onDelete('cascade');

            // Restricción opcional:
            // Un estudiante solo puede estar en 1 trabajo (lo reforzaremos en lógica)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajo_estudiante');
    }
};
