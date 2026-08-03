<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluacion_desempenos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('evaluador_id')->constrained('users')->cascadeOnDelete();
            $table->string('periodo', 20);
            $table->decimal('calificacion', 5, 2);
            $table->text('fortalezas')->nullable();
            $table->text('areas_mejora')->nullable();
            $table->enum('estatus', ['BORRADOR', 'FINAL'])->default('BORRADOR');
            $table->timestamps();

            $table->index(['empleado_id', 'periodo'], 'idx_eval_empleado_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion_desempenos');
    }
};
