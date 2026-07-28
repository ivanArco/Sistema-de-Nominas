<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('rol', 'ADMIN')->update(['rol' => 'JEFE_AREA']);
        DB::table('users')->where('rol', 'NOMINISTA')->update(['rol' => 'CONTADOR']);
        DB::table('users')->where('rol', 'CONSULTA')->update(['rol' => 'EMPLEADO']);
    }

    public function down(): void
    {
        DB::table('users')->where('rol', 'JEFE_AREA')->update(['rol' => 'ADMIN']);
        DB::table('users')->where('rol', 'CONTADOR')->update(['rol' => 'NOMINISTA']);
        DB::table('users')->where('rol', 'EMPLEADO')->update(['rol' => 'CONSULTA']);
    }
};
