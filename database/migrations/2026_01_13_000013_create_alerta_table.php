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
        Schema::create('alerta', function (Blueprint $table) {
            $table->id('id_alerta');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->timestamp('fecha_envio')->nullable();
            $table->string('tipo_alerta', 100)->nullable();
            $table->boolean('leido')->nullable();

            $table->foreign('id_trabajo')->references('id_trabajo')->on('trabajo');
            $table->foreign('id_profesor')->references('id_profesor')->on('profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerta');
    }
};
