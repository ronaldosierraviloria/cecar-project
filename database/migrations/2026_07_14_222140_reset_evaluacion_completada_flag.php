<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('evaluaciones', 'evaluacion_completada')) {
            DB::table('evaluaciones')->update(['evaluacion_completada' => false]);
        }

        // Limpiar firmas que se guardaron sin calificación real (via guardarProgreso)
        if (Schema::hasColumn('evaluaciones', 'firma_evaluador_2')) {
            DB::table('evaluaciones')
                ->whereNull('nota_final')
                ->whereNull('resultado')
                ->update([
                    'firma' => null,
                    'firma_evaluador_2' => null,
                    'celular' => null,
                    'celular_evaluador_2' => null,
                ]);
        }
    }

    public function down(): void
    {
        // No revertir - el flag se recalcula con la nueva lógica
    }
};
