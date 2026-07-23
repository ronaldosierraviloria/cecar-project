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
        Schema::table('trabajo_profesor', function (Blueprint $table) {
            $table->string('decision_evaluador', 20)->nullable()->after('retroalimentacion_finalizada');
            $table->text('motivo_rechazo')->nullable()->after('decision_evaluador');
        });
    }

    public function down(): void
    {
        Schema::table('trabajo_profesor', function (Blueprint $table) {
            $table->dropColumn(['decision_evaluador', 'motivo_rechazo']);
        });
    }
};
