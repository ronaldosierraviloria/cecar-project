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
        Schema::create('seguimiento', function (Blueprint $table) {
            $table->id('id_seguimiento');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_admin');
            $table->string('estado_visualizacion', 50)->nullable();
            $table->timestamp('fecha_revision')->nullable();

            $table->foreign('id_trabajo')->references('id_trabajo')->on('trabajo');
            $table->foreign('id_admin')->references('id_gestor')->on('gestor'); // Match SQL: id_admin REFERENCE gestor(id_gestor)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento');
    }
};
