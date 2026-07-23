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
        Schema::create('retroalimentaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comentario');
            $table->string('version_documento')->default('v1');
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
        Schema::dropIfExists('retroalimentaciones');
    }
};
