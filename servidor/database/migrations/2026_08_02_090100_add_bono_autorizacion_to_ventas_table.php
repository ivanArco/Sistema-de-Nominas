<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('bono_estatus', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])
                ->default('APROBADO')
                ->after('bono_desempeno');
            $table->foreignId('bono_autorizado_por')->nullable()->after('bono_estatus')->constrained('users')->nullOnDelete();
            $table->timestamp('bono_autorizado_fecha')->nullable()->after('bono_autorizado_por');
            $table->text('bono_autorizado_comentario')->nullable()->after('bono_autorizado_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bono_autorizado_por');
            $table->dropColumn(['bono_estatus', 'bono_autorizado_fecha', 'bono_autorizado_comentario']);
        });
    }
};
