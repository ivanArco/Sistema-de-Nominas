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
        Schema::create('periodo_nominas', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('anio');
            $table->smallInteger('numero_periodo');
            $table->enum('tipo_periodo', ['SEMANAL', 'CATORCENAL', 'QUINCENAL', 'MENSUAL']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_pago');
            $table->enum('estatus', ['ABIERTO', 'CALCULADO', 'CERRADO', 'TIMBRADO'])->default('ABIERTO');
            $table->timestamps();

            $table->unique(['anio', 'numero_periodo', 'tipo_periodo'], 'uq_periodo_nomina');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo_nominas');
    }
};
