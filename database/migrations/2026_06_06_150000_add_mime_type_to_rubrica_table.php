<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rubrica', function (Blueprint $table) {
            $table->string('mime_type', 100)->nullable()->after('archivo');
        });
    }

    public function down(): void
    {
        Schema::table('rubrica', function (Blueprint $table) {
            $table->dropColumn('mime_type');
        });
    }
};
