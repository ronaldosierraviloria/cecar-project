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
        Schema::create('historial_estados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->string('estado'); // 'subido', 'en_revision', 'retroalimentacion_emitida', 'version_corregida_subida', 'aprobado'
            $table->string('version_documento'); // 'v1', 'v2', etc.
            $table->unsignedBigInteger('user_id'); // Gestor o Evaluador que realiza el cambio
            $table->text('observacion_estado')->nullable();
            $table->timestamps();

            $table->foreign('trabajo_grado_id')
                ->references('id_trabajo')
                ->on('trabajo')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estados');
    }
};
