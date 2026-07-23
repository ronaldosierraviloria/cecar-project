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
        Schema::create('rubrica', function (Blueprint $table) {
            $table->id('id_rubrica');
            $table->unsignedBigInteger('id_tipo')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->boolean('activo')->nullable();
            $table->timestamp('fecha_creacion')->nullable();

            $table->foreign('id_tipo')->references('id_tipo')->on('tipo_trabajo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrica');
    }
};
