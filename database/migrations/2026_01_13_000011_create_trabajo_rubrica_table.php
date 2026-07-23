<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajo_rubrica', function (Blueprint $table) {
            $table->id('id_trabajo_rubrica');

            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_rubrica');
            $table->timestamp('fecha_asignacion')->nullable();

            $table->timestamps();

            $table->foreign('id_trabajo')
                ->references('id_trabajo')->on('trabajo')
                ->onDelete('cascade');

            $table->foreign('id_rubrica')
                ->references('id_rubrica')->on('rubrica')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trabajo_rubrica');
    }
};
