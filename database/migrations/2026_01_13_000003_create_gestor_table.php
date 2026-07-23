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
        Schema::create('gestor', function (Blueprint $table) {
            $table->id('id_gestor');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('dependencia', 100)->nullable();
            $table->timestamps();

            // FK hacia usuario
            $table->foreign('id_usuario')
                  ->references('id_usuario')->on('usuario')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestor');
    }
};
