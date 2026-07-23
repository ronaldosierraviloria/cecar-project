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
        Schema::table('trabajo', function (Blueprint $table) {
            // Plantilla de rúbrica web a usar en la evaluación
            // Valores: 'propuesta_de_grado', 'trabajo_de_grado', etc.
            $table->string('plantilla_rubrica', 50)->nullable()->after('id_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trabajo', function (Blueprint $table) {
            $table->dropColumn('plantilla_rubrica');
        });
    }
};
