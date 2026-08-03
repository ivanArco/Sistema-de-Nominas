<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->decimal('porcentaje_infonavit', 5, 3)->default(0)->after('sal_int');
            $table->decimal('porcentaje_afore', 5, 3)->default(1.125)->after('porcentaje_infonavit');
            $table->boolean('usa_fondo_ahorro')->default(false)->after('porcentaje_afore');
            $table->decimal('porcentaje_fondo_ahorro', 5, 3)->default(0)->after('usa_fondo_ahorro');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'porcentaje_infonavit',
                'porcentaje_afore',
                'usa_fondo_ahorro',
                'porcentaje_fondo_ahorro',
            ]);
        });
    }
};
