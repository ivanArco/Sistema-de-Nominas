<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los campos faltantes para el modulo de usuarios de la entrega 1.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre_usuario', 50)->nullable()->after('id');
            $table->char('curp', 18)->nullable()->after('nombre_usuario');
            $table->string('telefono_contacto_1', 20)->nullable()->after('email');
            $table->string('telefono_contacto_2', 20)->nullable()->after('telefono_contacto_1');
            $table->date('fecha_contratacion')->nullable()->after('telefono_contacto_2');
            $table->string('area_contratacion', 100)->nullable()->after('fecha_contratacion');
            $table->string('numero_seguro_social', 20)->nullable()->after('area_contratacion');
            $table->date('fecha_alta_servicio_salud')->nullable()->after('numero_seguro_social');
            $table->string('direccion', 200)->nullable()->after('fecha_alta_servicio_salud');
            $table->string('colonia', 120)->nullable()->after('direccion');
            $table->string('codigo_postal', 10)->nullable()->after('colonia');
            $table->string('ciudad', 120)->nullable()->after('codigo_postal');
            $table->string('estado', 120)->nullable()->after('ciudad');

            $table->unique('nombre_usuario');
            $table->unique('curp');
            $table->unique('numero_seguro_social');
        });
    }

    /**
     * Revierte los campos agregados para la entrega 1.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nombre_usuario']);
            $table->dropUnique(['curp']);
            $table->dropUnique(['numero_seguro_social']);
            $table->dropColumn([
                'nombre_usuario',
                'curp',
                'telefono_contacto_1',
                'telefono_contacto_2',
                'fecha_contratacion',
                'area_contratacion',
                'numero_seguro_social',
                'fecha_alta_servicio_salud',
                'direccion',
                'colonia',
                'codigo_postal',
                'ciudad',
                'estado',
            ]);
        });
    }
};
