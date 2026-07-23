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
        Schema::create('calificacion', function (Blueprint $table) {
            $table->id('id_calificacion');
            $table->unsignedBigInteger('id_rubrica');
            $table->unsignedBigInteger('id_profesor');
            $table->integer('puntaje_total')->nullable();
            $table->text('observacion_final')->nullable();
            $table->text('comentarios')->nullable();
            $table->string('estado', 50)->nullable();
            $table->timestamp('fecha_calificacion')->nullable();

            $table->foreign('id_rubrica')->references('id_rubrica')->on('rubrica');
            $table->foreign('id_profesor')->references('id_profesor')->on('profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacion');
    }
};
