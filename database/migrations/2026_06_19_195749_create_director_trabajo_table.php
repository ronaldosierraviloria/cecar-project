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
        Schema::create('director_trabajo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_director');
            $table->unsignedBigInteger('id_trabajo');
            $table->timestamps();

            $table->foreign('id_director')->references('id_director')->on('directors')->onDelete('cascade');
            $table->foreign('id_trabajo')->references('id_trabajo')->on('trabajo')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('director_trabajo');
    }
};
