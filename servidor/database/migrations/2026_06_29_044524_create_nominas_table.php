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
        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('periodo_nomina_id')->constrained('periodo_nominas');
            $table->decimal('dias_pagados', 6, 2)->default(0);
            $table->decimal('total_percepciones', 14, 2)->default(0);
            $table->decimal('total_deducciones', 14, 2)->default(0);
            $table->decimal('neto_pagado', 14, 2)->default(0);
            $table->enum('estatus', ['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'])->default('BORRADOR');
            $table->timestamps();

            $table->unique(['empleado_id', 'periodo_nomina_id'], 'uq_nominas_empleado_periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};
