<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->enum('tipo', ['VACACIONES', 'PERMISO']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('motivo')->nullable();
            $table->enum('estatus', ['PENDIENTE', 'APROBADA', 'RECHAZADA'])->default('PENDIENTE');
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_revision')->nullable();
            $table->text('comentario_revision')->nullable();
            $table->timestamps();

            $table->index(['estatus', 'tipo'], 'idx_solicitudes_estatus_tipo');
            $table->index(['empleado_id', 'fecha_inicio'], 'idx_solicitudes_empleado_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
