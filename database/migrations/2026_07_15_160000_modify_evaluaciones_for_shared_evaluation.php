<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            // Hacer id_profesor nullable para permitir evaluación compartida
            $table->unsignedBigInteger('id_profesor')->nullable()->change();

            // Campos para la firma del segundo evaluador
            $table->text('firma_evaluador_2')->nullable()->after('firma');
            $table->string('celular_evaluador_2', 20)->nullable()->after('celular');

            // Estado de completitud: true cuando ambos evaluadores han firmado
            $table->boolean('evaluacion_completada')->default(false)->after('resultado');

            // Quitar unique implícito de (id_trabajo, id_profesor) 
            // y permitir una sola evaluación por id_trabajo
        });
    }

    public function down(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropColumn(['firma_evaluador_2', 'celular_evaluador_2', 'evaluacion_completada']);
            $table->unsignedBigInteger('id_profesor')->nullable(false)->change();
        });
    }
};
