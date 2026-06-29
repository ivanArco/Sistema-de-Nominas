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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('num_empleado', 30)->unique();
            $table->string('nombre', 80);
            $table->string('ap_paterno', 80);
            $table->string('ap_materno', 80)->nullable();
            $table->char('curp', 18)->unique();
            $table->string('rfc', 13)->unique();
            $table->string('nss', 20)->unique();
            $table->string('correo', 120)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->date('f_ingreso');
            $table->date('f_baja')->nullable();
            $table->string('tipo_cont', 50);
            $table->string('jornada', 50);
            $table->decimal('sal_dia', 12, 2);
            $table->decimal('sal_int', 12, 2);
            $table->foreignId('depto_id')->constrained('departamentos');
            $table->foreignId('puesto_id')->constrained('puestos');
            $table->enum('estatus', ['ACTIVO', 'BAJA'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
