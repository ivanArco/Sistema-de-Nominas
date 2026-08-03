<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 60)->unique();
            $table->string('nombre', 120);
            $table->timestamps();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 120)->unique();
            $table->string('nombre', 150);
            $table->timestamps();
        });

        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rol_id', 'permiso_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')->nullable()->after('rol')->constrained('roles')->nullOnDelete();
        });

        $roles = [
            ['clave' => 'EMPLEADO', 'nombre' => 'Empleado'],
            ['clave' => 'VENDEDOR', 'nombre' => 'Vendedor'],
            ['clave' => 'SUPERVISOR', 'nombre' => 'Supervisor'],
            ['clave' => 'JEFE_AREA', 'nombre' => 'Jefe de Area'],
            ['clave' => 'CONTADOR', 'nombre' => 'Contador'],
            ['clave' => 'SECRETARIA', 'nombre' => 'Secretaria'],
        ];

        $permisosPorRol = [
            'EMPLEADO' => [
                'autoservicio.ver',
                'autoservicio.password',
                'mi-cuenta.editar',
                'solicitudes.propias',
                'nominas.propias',
            ],
            'VENDEDOR' => [
                'autoservicio.ver',
                'autoservicio.password',
                'mi-cuenta.editar',
                'solicitudes.propias',
                'nominas.propias',
                'ventas.propias',
                'ventas.metas.ver',
            ],
            'SECRETARIA' => [
                'autoservicio.ver',
                'autoservicio.password',
                'mi-cuenta.editar',
                'empleados.gestionar',
                'asistencias.gestionar',
                'solicitudes.aprobar',
                'incidencias.gestionar',
                'expedientes.gestionar',
                'contratos.gestionar',
                'reportes.ver',
            ],
            'SUPERVISOR' => [
                'autoservicio.ver',
                'autoservicio.password',
                'mi-cuenta.editar',
                'empleados.gestionar',
                'asistencias.gestionar',
                'solicitudes.aprobar',
                'incidencias.gestionar',
                'ventas.metas.ver',
                'ventas.metas.gestionar',
                'reportes.ver',
            ],
            'JEFE_AREA' => [
                'dashboard.ver',
                'autoservicio.ver',
                'autoservicio.password',
                'mi-cuenta.editar',
                'empleados.gestionar',
                'asistencias.gestionar',
                'solicitudes.aprobar',
                'incidencias.gestionar',
                'reportes.ver',
                'usuarios.gestionar',
                'usuarios.eliminar',
                'expedientes.gestionar',
                'ventas.gestionar',
                'contratos.gestionar',
                'evaluaciones.gestionar',
                'solicitudes.aprobar.final',
                'bonos.autorizar',
                'ventas.metas.ver',
                'ventas.metas.gestionar',
                'nominas.cierre.autorizar',
            ],
            'CONTADOR' => [
                'dashboard.ver',
                'autoservicio.ver',
                'autoservicio.password',
                'mi-cuenta.editar',
                'catalogos_nomina.gestionar',
                'incidencias.gestionar',
                'nominas.gestionar',
                'nominas.cierre.autorizar',
                'reportes.ver',
            ],
        ];

        $ahora = now();

        DB::table('roles')->upsert(
            array_map(static fn (array $rol) => $rol + ['created_at' => $ahora, 'updated_at' => $ahora], $roles),
            ['clave'],
            ['nombre', 'updated_at']
        );

        $permisosUnicos = [];
        foreach ($permisosPorRol as $permisos) {
            foreach ($permisos as $permiso) {
                $permisosUnicos[$permiso] = [
                    'clave' => $permiso,
                    'nombre' => str_replace('.', ' ', strtoupper($permiso)),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        DB::table('permisos')->upsert(array_values($permisosUnicos), ['clave'], ['nombre', 'updated_at']);

        $rolesId = DB::table('roles')->pluck('id', 'clave');
        $permisosId = DB::table('permisos')->pluck('id', 'clave');

        foreach ($permisosPorRol as $rolClave => $permisos) {
            $rolId = $rolesId[$rolClave] ?? null;
            if (!$rolId) {
                continue;
            }

            $filasPivot = [];
            foreach ($permisos as $permisoClave) {
                $permisoId = $permisosId[$permisoClave] ?? null;
                if (!$permisoId) {
                    continue;
                }

                $filasPivot[] = [
                    'rol_id' => $rolId,
                    'permiso_id' => $permisoId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }

            if ($filasPivot !== []) {
                DB::table('permiso_rol')->insertOrIgnore($filasPivot);
            }
        }

        $usuarios = DB::table('users')->select(['id', 'rol'])->get();
        foreach ($usuarios as $usuario) {
            $rolActual = strtoupper(trim((string) $usuario->rol));
            if ($rolActual === 'ADMIN') {
                $rolActual = 'JEFE_AREA';
            } elseif ($rolActual === 'NOMINISTA') {
                $rolActual = 'CONTADOR';
            } elseif ($rolActual === 'CONSULTA') {
                $rolActual = 'EMPLEADO';
            }

            $rolId = $rolesId[$rolActual] ?? ($rolesId['EMPLEADO'] ?? null);

            if ($rolId) {
                DB::table('users')->where('id', $usuario->id)->update(['rol_id' => $rolId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rol_id');
        });

        Schema::dropIfExists('permiso_rol');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};
