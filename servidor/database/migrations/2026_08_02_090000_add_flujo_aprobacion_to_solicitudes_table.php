<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->enum('etapa_supervisor_estatus', ['PENDIENTE', 'APROBADA', 'RECHAZADA'])
                ->default('PENDIENTE')
                ->after('estatus');
            $table->enum('etapa_jefe_estatus', ['PENDIENTE', 'APROBADA', 'RECHAZADA'])
                ->default('PENDIENTE')
                ->after('etapa_supervisor_estatus');

            $table->foreignId('aprobado_supervisor_por')->nullable()->after('revisado_por')->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion_supervisor')->nullable()->after('aprobado_supervisor_por');
            $table->text('comentario_supervisor')->nullable()->after('fecha_aprobacion_supervisor');

            $table->foreignId('aprobado_jefe_por')->nullable()->after('comentario_supervisor')->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion_jefe')->nullable()->after('aprobado_jefe_por');
            $table->text('comentario_jefe')->nullable()->after('fecha_aprobacion_jefe');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aprobado_jefe_por');
            $table->dropColumn(['fecha_aprobacion_jefe', 'comentario_jefe']);

            $table->dropConstrainedForeignId('aprobado_supervisor_por');
            $table->dropColumn(['fecha_aprobacion_supervisor', 'comentario_supervisor']);

            $table->dropColumn(['etapa_supervisor_estatus', 'etapa_jefe_estatus']);
        });
    }
};
