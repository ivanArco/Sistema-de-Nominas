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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre', 80)->nullable()->after('id');
            $table->string('apellido_paterno', 80)->nullable()->after('nombre');
            $table->string('apellido_materno', 80)->nullable()->after('apellido_paterno');
            $table->enum('rol', ['ADMIN', 'NOMINISTA', 'SUPERVISOR', 'CONSULTA'])->default('CONSULTA')->after('password');
            $table->boolean('activo')->default(true)->after('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'apellido_paterno', 'apellido_materno', 'rol', 'activo']);
        });
    }
};
