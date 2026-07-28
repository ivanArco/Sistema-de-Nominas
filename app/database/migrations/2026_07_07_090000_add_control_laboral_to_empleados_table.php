<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->decimal('semanas_cotizadas', 10, 2)->default(0)->after('porcentaje_fondo_ahorro');
            $table->decimal('fondo_retiro_acumulado', 14, 2)->default(0)->after('semanas_cotizadas');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'semanas_cotizadas',
                'fondo_retiro_acumulado',
            ]);
        });
    }
};
