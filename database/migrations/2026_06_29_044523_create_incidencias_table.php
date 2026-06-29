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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('periodo_nomina_id')->constrained('periodo_nominas');
            $table->enum('tipo', ['FALTA', 'RETARDO', 'HORA_EXTRA', 'BONO', 'INCAPACIDAD', 'VACACIONES', 'OTRO']);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('cantidad', 10, 2)->default(0);
            $table->decimal('monto', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['empleado_id', 'periodo_nomina_id'], 'idx_incidencias_empleado_periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
