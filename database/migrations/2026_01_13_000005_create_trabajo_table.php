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
        Schema::create('trabajo', function (Blueprint $table) {
            $table->id('id_trabajo');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_subida')->nullable();
            $table->string('estado', 50)->nullable();
            $table->unsignedBigInteger('id_tipo'); // Keep unsignedBigInteger for FKs usually, unless explicitly Integer. SQL says INT. I'll stick to unsignedBigInteger to match typical Laravel id(). But wait, tipo_trabajo uses id() which is bigInt. So bigInt is correct.
            $table->string('archivo_pdf', 255)->nullable();
            $table->string('nombre_archivo', 255)->nullable();
            $table->string('tipo_archivo', 50)->nullable();

            $table->timestamps();

            $table->foreign('id_tipo')
                ->references('id_tipo')->on('tipo_trabajo')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajo');
    }
};
