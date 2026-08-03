<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('folio', 50)->unique();
            $table->date('fecha_venta');
            $table->string('cliente_nombre', 120)->nullable();
            $table->decimal('monto_bruto', 14, 2);
            $table->decimal('porcentaje_comision', 5, 2)->default(0);
            $table->decimal('comision_calculada', 14, 2)->default(0);
            $table->decimal('bono_desempeno', 14, 2)->default(0);
            $table->enum('estatus', ['REGISTRADA', 'CERRADA', 'CANCELADA'])->default('REGISTRADA');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['fecha_venta', 'estatus'], 'idx_ventas_fecha_estatus');
            $table->index(['empleado_id', 'fecha_venta'], 'idx_ventas_empleado_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
