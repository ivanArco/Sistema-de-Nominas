<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('tipo_documento', 80);
            $table->string('nombre_archivo', 180);
            $table->string('ruta_archivo', 255);
            $table->date('fecha_documento')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('cargado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['empleado_id', 'tipo_documento'], 'idx_expedientes_empleado_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
