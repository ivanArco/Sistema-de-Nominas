<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('periodo', 20);
            $table->decimal('meta_monto', 14, 2)->default(0);
            $table->decimal('meta_ventas', 10, 2)->default(0);
            $table->decimal('avance_monto', 14, 2)->default(0);
            $table->decimal('avance_ventas', 10, 2)->default(0);
            $table->enum('estatus', ['ABIERTA', 'CUMPLIDA', 'VENCIDA'])->default('ABIERTA');
            $table->timestamps();

            $table->unique(['empleado_id', 'periodo'], 'uq_venta_metas_empleado_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_metas');
    }
};
