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
        Schema::create('nomina_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->foreignId('concepto_nomina_id')->constrained('concepto_nominas');
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('importe', 14, 2);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->index('nomina_id', 'idx_nomina_detalle_nomina');
            $table->index('concepto_nomina_id', 'idx_nomina_detalle_concepto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_detalles');
    }
};
