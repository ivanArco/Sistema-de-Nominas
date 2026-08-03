<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de clientes para la entrega 1.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial', 120);
            $table->string('razon_social', 160)->nullable();
            $table->string('rfc', 13)->nullable()->unique();
            $table->string('nombre_contacto', 120);
            $table->string('correo_electronico', 120)->nullable();
            $table->string('telefono_contacto_1', 20);
            $table->string('telefono_contacto_2', 20)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('colonia', 120)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('ciudad', 120)->nullable();
            $table->string('estado', 120)->nullable();
            $table->enum('estatus', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla de clientes.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
