<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE incidencias MODIFY COLUMN tipo ENUM('FALTA','RETARDO','HORA_EXTRA','BONO','INCAPACIDAD','VACACIONES','VACACIONES_PAGADAS','DESCANSO','OTRO') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE incidencias MODIFY COLUMN tipo ENUM('FALTA','RETARDO','HORA_EXTRA','BONO','INCAPACIDAD','VACACIONES','OTRO') NOT NULL");
    }
};
