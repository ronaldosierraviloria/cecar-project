<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // Skip ENUM creation on SQLite (used for testing) — SQLite does not support DO $$ blocks
    if (DB::getDriverName() === 'sqlite') {
        return;
    }

    // Crear ENUMs solo si no existen, para evitar errores en migraciones repetidas
    DB::statement("DO $$ BEGIN
        CREATE TYPE rol_usuario AS ENUM ('Administrador', 'Evaluador', 'Gestor');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");

    DB::statement("DO $$ BEGIN
        CREATE TYPE tipo_trabajo_enum AS ENUM ('Investigación', 'Emprendimiento', 'Pasantía');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");

    DB::statement("DO $$ BEGIN
        CREATE TYPE estado_trabajo_enum AS ENUM ('Aceptado','Aceptado con Mejoras','Rechazado');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");

    DB::statement("DO $$ BEGIN
        CREATE TYPE estado_revision_enum AS ENUM ('Pendiente','En revisión','Finalizado');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");

    DB::statement("DO $$ BEGIN
        CREATE TYPE estado_calificacion_enum AS ENUM ('Borrador','Enviada');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");

    DB::statement("DO $$ BEGIN
        CREATE TYPE estado_visualizacion_enum AS ENUM ('Visto','Leído','No visto');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");

    DB::statement("DO $$ BEGIN
        CREATE TYPE tipo_alerta_enum AS ENUM ('Recordatorio 5 días','Plazo vencido');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END $$;");
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Eliminar ENUMs en orden inverso
        DB::statement("DROP TYPE IF EXISTS tipo_alerta_enum;");
        DB::statement("DROP TYPE IF EXISTS estado_visualizacion_enum;");
        DB::statement("DROP TYPE IF EXISTS estado_calificacion_enum;");
        DB::statement("DROP TYPE IF EXISTS estado_revision_enum;");
        DB::statement("DROP TYPE IF EXISTS estado_trabajo_enum;");
        DB::statement("DROP TYPE IF EXISTS tipo_trabajo_enum;");
        DB::statement("DROP TYPE IF EXISTS rol_usuario;");
    }
};
