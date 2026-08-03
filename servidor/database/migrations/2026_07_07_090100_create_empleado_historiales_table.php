<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->date('fecha_movimiento');
            $table->string('tipo_movimiento', 40);
            $table->decimal('salario_diario', 12, 2)->default(0);
            $table->foreignId('puesto_id')->nullable()->constrained('puestos');
            $table->decimal('semanas_cotizadas', 10, 2)->default(0);
            $table->decimal('fondo_retiro_acumulado', 14, 2)->default(0);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->index(['empleado_id', 'fecha_movimiento'], 'idx_historial_empleado_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_historiales');
    }
};
