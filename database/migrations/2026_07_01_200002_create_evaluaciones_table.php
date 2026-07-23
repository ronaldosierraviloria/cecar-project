<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->string('tipo_plantilla', 50);
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->string('resultado', 50);
            $table->text('observaciones_globales')->nullable();
            $table->json('criterios')->nullable();
            $table->text('firma')->nullable();
            $table->string('celular', 20)->nullable();
            $table->timestamps();

            $table->foreign('id_trabajo')->references('id_trabajo')->on('trabajo')->onDelete('cascade');
            $table->foreign('id_profesor')->references('id_profesor')->on('profesor')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones');
    }
};
