<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE UNIQUE INDEX rubrica_id_tipo_activo_unique
            ON rubrica (id_tipo)
            WHERE activo = true
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS rubrica_id_tipo_activo_unique');
    }
};
