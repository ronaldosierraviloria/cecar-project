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
        Schema::create('trabajo_profesor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_limite_revision')->nullable();
            $table->string('estado_revision', 50)->nullable();

            $table->primary(['id_trabajo', 'id_profesor']);
            $table->foreign('id_trabajo')->references('id_trabajo')->on('trabajo');
            $table->foreign('id_profesor')->references('id_profesor')->on('profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajo_profesor');
    }
};
