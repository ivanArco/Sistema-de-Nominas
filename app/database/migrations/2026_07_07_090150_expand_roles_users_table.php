<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMIN','NOMINISTA','SUPERVISOR','CONSULTA','EMPLEADO','VENDEDOR','JEFE_AREA','CONTADOR','SECRETARIA') NOT NULL DEFAULT 'EMPLEADO'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMIN','NOMINISTA','SUPERVISOR','CONSULTA') NOT NULL DEFAULT 'CONSULTA'");
    }
};
