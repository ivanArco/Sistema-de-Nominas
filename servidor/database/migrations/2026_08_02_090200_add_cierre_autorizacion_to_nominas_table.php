<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nominas', function (Blueprint $table) {
            $table->boolean('cierre_autorizado')->default(false)->after('estatus');
            $table->foreignId('cierre_autorizado_por')->nullable()->after('cierre_autorizado')->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cierre_autorizado')->nullable()->after('cierre_autorizado_por');
            $table->text('cierre_observaciones')->nullable()->after('fecha_cierre_autorizado');
        });
    }

    public function down(): void
    {
        Schema::table('nominas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cierre_autorizado_por');
            $table->dropColumn(['cierre_autorizado', 'fecha_cierre_autorizado', 'cierre_observaciones']);
        });
    }
};
