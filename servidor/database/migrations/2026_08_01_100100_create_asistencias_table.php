<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('estado', ['ASISTENCIA', 'RETARDO', 'FALTA', 'PERMISO', 'VACACIONES'])->default('ASISTENCIA');
            $table->decimal('horas_trabajadas', 5, 2)->default(8);
            $table->string('origen', 40)->default('CAPTURA_MANUAL');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['empleado_id', 'fecha'], 'uq_asistencias_empleado_fecha');
            $table->index(['fecha', 'estado'], 'idx_asistencias_fecha_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
