<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE empleados ADD COLUMN tipo_pago ENUM('SEMANAL','QUINCENAL','MENSUAL') NOT NULL DEFAULT 'QUINCENAL' AFTER jornada");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE empleados DROP COLUMN tipo_pago");
    }
};
