<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->unsignedBigInteger('id_facultad')->nullable()->after('activo');
            $table->foreign('id_facultad')->references('id_facultad')->on('facultad')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropForeign(['id_facultad']);
            $table->dropColumn('id_facultad');
        });
    }
};
