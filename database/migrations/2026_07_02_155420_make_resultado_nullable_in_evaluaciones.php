<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE evaluaciones ALTER COLUMN resultado DROP NOT NULL');
        } else {
            DB::statement('ALTER TABLE evaluaciones MODIFY COLUMN resultado VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE evaluaciones ALTER COLUMN resultado SET NOT NULL');
        } else {
            DB::statement('ALTER TABLE evaluaciones MODIFY COLUMN resultado VARCHAR(255) NOT NULL');
        }
    }
};
